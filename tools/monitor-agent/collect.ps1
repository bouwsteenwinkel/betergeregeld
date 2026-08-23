[CmdletBinding()]
param(
    [string]$Endpoint = $env:MONITOR_ENDPOINT,
    [string]$Token = $env:MONITOR_TOKEN
)

# Beter Geregeld — VPS monitoring collector.
# Verzamelt CPU/RAM/disk/uptime en pusht 1 sample naar de ingest-endpoint.
# Bedoeld om elke minuut via een geplande taak te draaien. Token + endpoint
# komen uit machine-env-vars (MONITOR_TOKEN / MONITOR_ENDPOINT) of parameters.

if (-not $Endpoint) { $Endpoint = 'https://betergeregeld.com/monitor/ingest' }
if (-not $Token) { Write-Error 'MONITOR_TOKEN ontbreekt (zet de env-var of geef -Token mee).'; exit 1 }

$ErrorActionPreference = 'Stop'

try {
    $os = Get-CimInstance Win32_OperatingSystem

    # CPU: gemiddelde load over alle processors (0-100).
    $cpu = (Get-CimInstance Win32_Processor | Measure-Object -Property LoadPercentage -Average).Average

    # Geheugen in MB.
    $memTotalMb = [int][math]::Round($os.TotalVisibleMemorySize / 1024)
    $memFreeMb = [int][math]::Round($os.FreePhysicalMemory / 1024)
    $memUsedMb = $memTotalMb - $memFreeMb

    # Systeemschijf (C:) in GB.
    $disk = Get-CimInstance Win32_LogicalDisk -Filter "DeviceID='C:'"
    $diskTotalGb = [math]::Round($disk.Size / 1GB, 2)
    $diskFreeGb = [math]::Round($disk.FreeSpace / 1GB, 2)
    $diskUsedGb = [math]::Round($diskTotalGb - $diskFreeGb, 2)

    # Uptime in seconden.
    $uptimeSeconds = [int]((Get-Date) - $os.LastBootUpTime).TotalSeconds

    # Bij druk op de machine: vastleggen WIE die druk veroorzaakt. Alleen dan,
    # want Get-Process over een paar honderd processen kost meer dan de rest van
    # de meting samen, en dit draait elke minuut. De aanleiding: sinds begin
    # augustus staat de VPS elke nacht rond 03:00 CEST tien tot twintig minuten
    # zo vol dat de geplande taken niet meer aan de beurt komen (de agent zelf
    # mist dan ook samples) en uitgaande verbindingen aflopen op een timeout —
    # waarna de waakhond een storing meldt terwijl de sites gewoon doorbedienen.
    # CPU en geheugen alleen vertellen niet welk proces het is; deze regels wel.
    $memPercent = 0
    if ($memTotalMb -gt 0) { $memPercent = [math]::Round($memUsedMb / $memTotalMb * 100, 1) }

    $load = $null
    if ($cpu -ge 50 -or $memPercent -ge 80) {
        # Optellen PER PROCESNAAM, niet per los proces. Gemeten 23-08-2026:
        # een top-6 van losse processen verklaarde maar ~5 GB van de ~10,5 GB
        # die in gebruik was — de rest zit in tientallen kleine workers
        # (php-cgi) die individueel nooit in een top-6 komen maar samen het
        # geheugen vullen. Juist die zijn de verdachte bij de nachtelijke
        # stilstand, dus tellen we ze bij elkaar op. 'n' = aantal processen.
        $top = Get-Process -ErrorAction SilentlyContinue |
            Group-Object -Property ProcessName |
            ForEach-Object {
                $mb = [int][math]::Round((($_.Group | Measure-Object -Property WorkingSet64 -Sum).Sum) / 1MB)
                $cs = [int](($_.Group | Measure-Object -Property CPU -Sum).Sum)
                [pscustomobject]@{ naam = $_.Name; n = $_.Count; mb = $mb; cpu_s = $cs }
            } |
            Sort-Object -Property mb -Descending |
            Select-Object -First 8 |
            ForEach-Object { @{ naam = $_.naam; n = $_.n; mb = $_.mb; cpu_s = $_.cpu_s } }

        $load = @{
            reden       = 'druk'
            cpu         = [double]$cpu
            mem_pct     = $memPercent
            mem_used_mb = $memUsedMb
            top         = @($top)
        }
    }

    $payload = @{
        collected_at = (Get-Date).ToString('o')
        cpu_percent = [double]$cpu
        mem_used_mb = $memUsedMb
        mem_total_mb = $memTotalMb
        disk_used_gb = $diskUsedGb
        disk_total_gb = $diskTotalGb
        uptime_seconds = $uptimeSeconds
        load = $load
        agent_version = 'ps-1.3'
    } | ConvertTo-Json -Depth 5 -Compress

    # Eerst bufferen, dan pas versturen. Een meting die niet verstuurd kan worden
    # was tot nu toe gewoon weg, en dat is precies de meting die je wilt hebben:
    # tijdens de nachtelijke stilstand van 03:00 miste de reeks 13 van de 16
    # minuten. Wat er nu ligt wordt de volgende ronde alsnog nageleverd, dus de
    # grafiek is achteraf compleet.
    #
    # LET OP wat dit NIET oplost: als de geplande taak zelf niet start (machine te
    # druk om een proces te starten), is er niets om te bufferen. Het onderscheid
    # tussen die twee is wel meteen zichtbaar geworden — komen er morgen samples
    # van 03:05 binnen met een verzendtijd van 03:16, dan liep de agent wél en kon
    # hij alleen niet weg; blijft het gat leeg, dan is de taak niet gestart.
    $bufferMap = Join-Path $env:ProgramData 'BG-Monitor'
    if (-not (Test-Path -LiteralPath $bufferMap)) {
        New-Item -ItemType Directory -Path $bufferMap -Force | Out-Null
    }
    $bufferBestand = Join-Path $bufferMap 'buffer.jsonl'
    Add-Content -LiteralPath $bufferBestand -Value $payload -Encoding utf8

    # Twee runs tegelijk zouden dezelfde regels dubbel versturen en elkaars
    # herschreven buffer overschrijven. Lukt het slot niet, dan is er al een run
    # bezig: de meting staat gebufferd en die andere run neemt 'm mee.
    $slot = New-Object System.Threading.Mutex($false, 'Global\BG-Monitor-Agent')
    $heeftSlot = $false
    try {
        $heeftSlot = $slot.WaitOne(10000)
    } catch [System.Threading.AbandonedMutexException] {
        $heeftSlot = $true
    }

    if (-not $heeftSlot) {
        Write-Output 'Andere run is bezig; meting staat in de buffer.'
        exit 0
    }

    $headers = @{ Authorization = "Bearer $Token" }
    $verzonden = 0
    $laatste = $null

    try {
        $regels = @(Get-Content -LiteralPath $bufferBestand -Encoding utf8 -ErrorAction SilentlyContinue)

        # Buffer begrenzen: bij een lange storing loopt dit anders onbeperkt vol.
        # 720 samples = twaalf uur; oudere metingen zijn voor een grafiek toch
        # niet meer interessant.
        $maxBuffer = 720
        if ($regels.Count -gt $maxBuffer) {
            $regels = $regels[($regels.Count - $maxBuffer)..($regels.Count - 1)]
        }

        $over = New-Object System.Collections.Generic.List[string]

        foreach ($regel in $regels) {
            if ([string]::IsNullOrWhiteSpace($regel)) { continue }

            # Na een mislukking niets meer proberen: dan is de endpoint of het
            # netwerk weg, en houden we de volgorde intact (oudste eerst, zodat
            # de laatste push de actuele stand achterlaat in last_cpu/mem/disk).
            if ($over.Count -gt 0) { $over.Add($regel); continue }

            try {
                $null = Invoke-RestMethod -Uri $Endpoint -Method Post -Body $regel -ContentType 'application/json' -Headers $headers -TimeoutSec 15
                $verzonden++
                $laatste = $regel
            } catch {
                # Een afgekeurde meting (400/422) komt nooit meer door en zou de
                # buffer voorgoed verstoppen — die gooien we weg. 401/403/429 en
                # alles zonder antwoord (netwerk, timeout) bewaren we wél.
                $code = 0
                if ($_.Exception.Response) { $code = [int]$_.Exception.Response.StatusCode }
                if ($code -ge 400 -and $code -lt 500 -and $code -ne 401 -and $code -ne 403 -and $code -ne 429) {
                    Write-Warning ("Meting geweigerd met HTTP {0}; weggegooid." -f $code)
                } else {
                    $over.Add($regel)
                }
            }
        }

        Set-Content -LiteralPath $bufferBestand -Value $over -Encoding utf8
    }
    finally {
        $slot.ReleaseMutex()
        $slot.Dispose()
    }

    if ($verzonden -eq 0) {
        # Bewust niet Write-Error: met ErrorActionPreference = 'Stop' gooit dat
        # binnen de try-tak een uitzondering die de catch eronder nog eens
        # inpakt, en dan staat de melding dubbel in het taaklogboek.
        [Console]::Error.WriteLine('Monitor push mislukt: niets verzonden, metingen staan in de buffer.')
        exit 1
    }

    $achterstand = $verzonden - 1
    if ($achterstand -gt 0) {
        Write-Output ("OK - {0} metingen verzonden ({1} nageleverd)." -f $verzonden, $achterstand)
    } else {
        Write-Output 'OK - 1 meting verzonden.'
    }
}
catch {
    Write-Error ("Monitor push mislukt: {0}" -f $_.Exception.Message)
    exit 1
}
