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
# het TOTAAL, dus wel dat de schijf volliep maar niet waardoor.
#
# EEN ronde over de schijf, niet drie. De eerste versie riep per hoofdmap, per
# vhost en nog eens voor de grootste bestanden een eigen Get-ChildItem -Recurse
# aan. Dat is drie keer dezelfde 345 GB doorlopen, en liep richting een half uur
# op een machine die het toch al zwaar heeft. Nu wordt elk bestand een keer
# gezien en meteen aan alle drie de tellingen toegekend.
#
# Ook System.IO in plaats van Get-ChildItem: dat bouwt geen PowerShell-object per
# bestand, en de omvang komt uit de mapopgave mee zonder extra schijftoegang.
# Op miljoenen bestanden scheelt dat een veelvoud.
#
# Zonder -Push drukt hij het alleen af. Met -Push gaat de meting naar het
# monitoring-dashboard, waar de reeks bewaard blijft: dan zie je naast de stand
# ook de GROEI per map, en dat is de vraag die je eigenlijk hebt.
#
# Draaien:  powershell -NoProfile -ExecutionPolicy Bypass -File schijfgebruik.ps1 -Push

$ErrorActionPreference = 'Continue'

if (-not $Endpoint) { $Endpoint = 'https://betergeregeld.com/monitor/ingest-disk' }

$Schijf = $Schijf.TrimEnd('\') + '\'
$vhostWortel = (Join-Path $Schijf 'inetpub\vhosts').ToLowerInvariant().TrimEnd('\')

$mapTotaal   = @{}   # hoofdmap van de schijf   -> bytes
$vhostTotaal = @{}   # C:\inetpub\vhosts\<site> -> bytes
$logTotaal   = @{}   # idem, maar alleen de logs-map eronder
$grootste    = New-Object System.Collections.Generic.List[object]

$aantalBestanden = 0
$aantalMappen = 0
$begin = Get-Date

# Elke map gaat met zijn context op de stapel: bij welke hoofdmap hij hoort, bij
# welke vhost, en of hij onder een logs-map zit. Zo hoeft er per bestand niet
# opnieuw in het pad gezocht te worden; dat zou op miljoenen bestanden duurder
# zijn dan het uitlezen zelf.
$stapel = New-Object System.Collections.Generic.Stack[object]
$stapel.Push([pscustomobject]@{ Pad = $Schijf; Hoofdmap = $null; Vhost = $null; InLogs = $false })

while ($stapel.Count -gt 0) {
    $huidig = $stapel.Pop()
    $aantalMappen++

    if (($aantalMappen % 2000) -eq 0) {
        Write-Progress -Activity 'Schijf doorlopen' -Status ("{0} mappen, {1} bestanden" -f $aantalMappen, $aantalBestanden)
    }

    $map = $null
    try { $map = New-Object System.IO.DirectoryInfo $huidig.Pad } catch { continue }

    try {
        foreach ($f in $map.EnumerateFiles()) {
            $lengte = 0
            try { $lengte = [long]$f.Length } catch { continue }

            $aantalBestanden++

            # Bestanden los in de hoofdmap van de schijf hebben geen bovenliggende
            # map en zouden anders nergens meetellen -- op C: is dat onder meer
            # pagefile.sys, en dat is geen ruis.
            $sleutel = $huidig.Hoofdmap
            if (-not $sleutel) { $sleutel = $Schijf }
            $mapTotaal[$sleutel] = [long]$mapTotaal[$sleutel] + $lengte
            if ($huidig.Vhost)    { $vhostTotaal[$huidig.Vhost]  = [long]$vhostTotaal[$huidig.Vhost] + $lengte }
            if ($huidig.InLogs)   { $logTotaal[$huidig.Vhost]    = [long]$logTotaal[$huidig.Vhost] + $lengte }

            # Grootste bestanden bijhouden zonder alles te bewaren: pas sorteren
            # als de lijst te lang wordt, dan afkappen.
            if ($lengte -ge 104857600) {
                $grootste.Add([pscustomobject]@{ Pad = $f.FullName; Bytes = $lengte; Gewijzigd = $f.LastWriteTime })
                if ($grootste.Count -gt ($Top * 4)) {
                    $bewaard = $grootste | Sort-Object Bytes -Descending | Select-Object -First $Top
                    $grootste = New-Object System.Collections.Generic.List[object]
                    foreach ($b in $bewaard) { $grootste.Add($b) }
                }
            }
        }
    } catch { }

    try {
        foreach ($d in $map.EnumerateDirectories()) {
            # Junctions en symlinks overslaan: die wijzen naar iets dat elders al
            # geteld wordt, en kunnen de doorloop in een kring laten lopen.
            if (($d.Attributes -band [System.IO.FileAttributes]::ReparsePoint) -ne 0) { continue }

            $hoofdmap = $huidig.Hoofdmap
            if (-not $hoofdmap) { $hoofdmap = $d.FullName }

            $vhost = $huidig.Vhost
            $inLogs = $huidig.InLogs

            if (-not $vhost -and $d.Parent -and $d.Parent.FullName.ToLowerInvariant().TrimEnd('\') -eq $vhostWortel) {
                $vhost = $d.FullName
            } elseif ($vhost -and -not $inLogs -and $d.Name -ieq 'logs') {
                $inLogs = $true
            }

            $stapel.Push([pscustomobject]@{ Pad = $d.FullName; Hoofdmap = $hoofdmap; Vhost = $vhost; InLogs = $inLogs })
        }
    } catch { }
}

Write-Progress -Activity 'Schijf doorlopen' -Completed
$duur = [int]((Get-Date) - $begin).TotalSeconds

function Toon-Gb { param([double]$Bytes) return [math]::Round($Bytes / 1GB, 2) }

Write-Output ("Doorlopen in {0}s: {1} mappen, {2} bestanden." -f $duur, $aantalMappen, $aantalBestanden)
Write-Output ''
Write-Output "== Hoofdmappen van $Schijf =="
$mapTotaal.GetEnumerator() | Sort-Object Value -Descending | Select-Object -First $Top |
    Format-Table @{n='GB';e={Toon-Gb $_.Value}}, @{n='Map';e={$_.Key}} -AutoSize

if ($vhostTotaal.Count -gt 0) {
    Write-Output ''
    Write-Output '== Per vhost (totaal, en waarvan logs) =='
    $vhostTotaal.GetEnumerator() | Sort-Object Value -Descending |
        Format-Table @{n='GB';e={Toon-Gb $_.Value}},
                     @{n='Logs_GB';e={Toon-Gb ([long]$logTotaal[$_.Key])}},
                     @{n='Vhost';e={Split-Path $_.Key -Leaf}} -AutoSize
}

$topBestanden = @($grootste | Sort-Object Bytes -Descending | Select-Object -First $Top)
if ($topBestanden.Count -gt 0) {
    Write-Output ''
    Write-Output ("== {0} grootste losse bestanden ==" -f $Top)
    $topBestanden | Format-Table @{n='GB';e={Toon-Gb $_.Bytes}}, Gewijzigd, Pad -AutoSize
}

if (-not $Push) {
    Write-Output ''
    Write-Output 'Niet verstuurd (geef -Push mee om dit in het dashboard te bewaren).'
    return
}

if (-not $Token) {
    Write-Error 'MONITOR_TOKEN ontbreekt (zet de env-var of geef -Token mee).'
    exit 1
}

$metingen = New-Object System.Collections.Generic.List[object]
foreach ($e in $mapTotaal.GetEnumerator())   { $metingen.Add(@{ soort = 'map';     pad = $e.Key; bytes = [long]$e.Value }) }
foreach ($e in $vhostTotaal.GetEnumerator()) { $metingen.Add(@{ soort = 'vhost';   pad = $e.Key; bytes = [long]$e.Value }) }
foreach ($e in $logTotaal.GetEnumerator())   { $metingen.Add(@{ soort = 'logs';    pad = (Join-Path $e.Key 'logs'); bytes = [long]$e.Value }) }
foreach ($b in $topBestanden)                { $metingen.Add(@{ soort = 'bestand'; pad = $b.Pad; bytes = [long]$b.Bytes }) }

# Alleen wat ertoe doet opsturen: onder 100 MB is ruis, en de ontvangstkant
# neemt maximaal 200 regels per meting aan.
$teSturen = @($metingen | Where-Object { $_.bytes -ge 104857600 } |
    Sort-Object { $_.bytes } -Descending | Select-Object -First 200)

if ($teSturen.Count -eq 0) {
    Write-Output 'Niets om te versturen.'
    return
}

$body = @{ measured_at = (Get-Date).ToString('o'); entries = $teSturen } | ConvertTo-Json -Depth 4 -Compress

try {
    $kop = @{ Authorization = "Bearer $Token" }
    $resp = Invoke-RestMethod -Uri $Endpoint -Method Post -Body $body -ContentType 'application/json' -Headers $kop -TimeoutSec 120
    Write-Output ''
    Write-Output ("OK - {0} mappen bewaard voor {1}." -f $resp.opgeslagen, $resp.server)
} catch {
    Write-Error ("Versturen mislukt: {0}" -f $_.Exception.Message)
    exit 1
}
