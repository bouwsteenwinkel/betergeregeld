[CmdletBinding()]
param(
    [string]$Schijf = 'C:\',
    [int]$Top = 25,
    [switch]$Push,
    [string]$Endpoint = $env:MONITOR_DISK_ENDPOINT,
    [string]$Token = $env:MONITOR_TOKEN
)

# Beter Geregeld — schijfmeting per map.
#
# Aanleiding (23-08-2026): C: groeide sinds 24-07 met ~1,45 GB per dag
# (294,8 -> 344,4 GB, nog 55,6 GB vrij). De gewone agent meet elke minuut alleen
# het TOTAAL, dus wél dát het volliep maar niet waardoor.
#
# Zonder -Push drukt hij het af. Met -Push stuurt hij de meting naar het
# monitoring-dashboard, waar de reeks bewaard blijft: dan zie je naast de stand
# ook de GROEI per map, en dat is de vraag die je eigenlijk hebt.
#
# Draaien:  powershell -NoProfile -ExecutionPolicy Bypass -File schijfgebruik.ps1 -Push
# Duurt op een volle schijf enkele minuten; bedoeld als dagelijkse taak, niet
# als minuutmeting -- een map uitrekenen betekent hem volledig doorlopen.

$ErrorActionPreference = 'Continue'

if (-not $Endpoint) { $Endpoint = 'https://betergeregeld.com/monitor/ingest-disk' }

function Get-MapGrootte {
    param([string]$Pad)
    try {
        $som = Get-ChildItem -LiteralPath $Pad -Recurse -File -Force -ErrorAction SilentlyContinue |
               Measure-Object -Property Length -Sum
        if ($null -eq $som.Sum) { return [double]0 }
        return [double]$som.Sum
    } catch {
        return [double]0
    }
}

function Toon-Gb {
    param([double]$Bytes)
    return [math]::Round($Bytes / 1GB, 2)
}

$metingen = New-Object System.Collections.Generic.List[object]

Write-Output "== Hoofdmappen van $Schijf =="
foreach ($m in (Get-ChildItem -LiteralPath $Schijf -Directory -Force -ErrorAction SilentlyContinue)) {
    $b = Get-MapGrootte $m.FullName
    $metingen.Add([pscustomobject]@{ soort = 'map'; pad = $m.FullName; bytes = $b })
}
$metingen | Where-Object { $_.soort -eq 'map' } |
    Sort-Object bytes -Descending | Select-Object -First $Top |
    Format-Table @{n='GB';e={Toon-Gb $_.bytes}}, pad -AutoSize

# De twee plekken waar het op een Plesk/IIS-machine meestal zit: logbestanden
# per vhost, en backups. Apart uitsplitsen, want die verdwijnen in het totaal
# van hun bovenliggende map.
$vhosts = Join-Path $Schijf 'inetpub\vhosts'
if (Test-Path -LiteralPath $vhosts) {
    Write-Output ''
    Write-Output '== Per vhost (totaal, en waarvan logs) =='
    $rijen = New-Object System.Collections.Generic.List[object]

    foreach ($v in (Get-ChildItem -LiteralPath $vhosts -Directory -Force -ErrorAction SilentlyContinue)) {
        $tot = Get-MapGrootte $v.FullName
        $metingen.Add([pscustomobject]@{ soort = 'vhost'; pad = $v.FullName; bytes = $tot })

        $logpad = Join-Path $v.FullName 'logs'
        $log = 0
        if (Test-Path -LiteralPath $logpad) {
            $log = Get-MapGrootte $logpad
            $metingen.Add([pscustomobject]@{ soort = 'logs'; pad = $logpad; bytes = $log })
        }

        $rijen.Add([pscustomobject]@{ Vhost = $v.Name; Tot_GB = (Toon-Gb $tot); Logs_GB = (Toon-Gb $log) })
    }

    $rijen | Sort-Object Tot_GB -Descending | Format-Table -AutoSize
}

Write-Output ''
Write-Output '== 25 grootste losse bestanden =='
$groot = Get-ChildItem -LiteralPath $Schijf -Recurse -File -Force -ErrorAction SilentlyContinue |
    Sort-Object Length -Descending | Select-Object -First 25
foreach ($f in $groot) {
    $metingen.Add([pscustomobject]@{ soort = 'bestand'; pad = $f.FullName; bytes = [double]$f.Length })
}
$groot | Select-Object @{n='GB';e={Toon-Gb $_.Length}}, LastWriteTime, FullName | Format-Table -AutoSize

if (-not $Push) {
    Write-Output ''
    Write-Output 'Niet verstuurd (geef -Push mee om dit in het dashboard te bewaren).'
    return
}

if (-not $Token) {
    Write-Error 'MONITOR_TOKEN ontbreekt (zet de env-var of geef -Token mee).'
    exit 1
}

# Alleen wat ertoe doet opsturen: mappen onder 100 MB zijn ruis en de tabel is
# begrensd op 200 regels aan de ontvangstkant.
$teSturen = $metingen |
    Where-Object { $_.bytes -ge 100MB } |
    Sort-Object bytes -Descending |
    Select-Object -First 200 |
    ForEach-Object { @{ soort = $_.soort; pad = $_.pad; bytes = [long]$_.bytes } }

if (@($teSturen).Count -eq 0) {
    Write-Output 'Niets om te versturen.'
    return
}

$body = @{
    measured_at = (Get-Date).ToString('o')
    entries     = @($teSturen)
} | ConvertTo-Json -Depth 4 -Compress

try {
    $kop = @{ Authorization = "Bearer $Token" }
    $resp = Invoke-RestMethod -Uri $Endpoint -Method Post -Body $body -ContentType 'application/json' -Headers $kop -TimeoutSec 60
    Write-Output ''
    Write-Output ("OK - {0} mappen bewaard voor {1}." -f $resp.opgeslagen, $resp.server)
} catch {
    Write-Error ("Versturen mislukt: {0}" -f $_.Exception.Message)
    exit 1
}
