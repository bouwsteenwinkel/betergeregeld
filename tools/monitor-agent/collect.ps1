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
        agent_version = 'ps-1.2'
    } | ConvertTo-Json -Depth 5 -Compress

    $headers = @{ Authorization = "Bearer $Token" }
    $resp = Invoke-RestMethod -Uri $Endpoint -Method Post -Body $payload -ContentType 'application/json' -Headers $headers -TimeoutSec 20
    Write-Output ("OK - {0} @ {1}" -f $resp.server, $resp.received_at)
}
catch {
    Write-Error ("Monitor push mislukt: {0}" -f $_.Exception.Message)
    exit 1
}
