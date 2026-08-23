[CmdletBinding()]
param(
    [string]$Schijf = 'C:\',
    [int]$Top = 25
)

# Beter Geregeld — eenmalige schijfdiagnose.
#
# Aanleiding (23-08-2026): C: groeit sinds 24-07 met ~1,65 GB per dag
# (294,8 -> 344,4 GB), nog 55,6 GB vrij. De monitoring-agent meet alleen het
# TOTAAL van de schijf, dus welke map die groei veroorzaakt is op afstand niet
# te zien. Dit script beantwoordt dat: alleen lezen, schrijft niets.
#
# Draaien:  powershell -NoProfile -ExecutionPolicy Bypass -File schijfgebruik.ps1
# Duurt op een volle schijf enkele minuten.

$ErrorActionPreference = 'Continue'

function Get-MapGrootte {
    param([string]$Pad)
    try {
        $som = Get-ChildItem -LiteralPath $Pad -Recurse -File -Force -ErrorAction SilentlyContinue |
               Measure-Object -Property Length -Sum
        if ($null -eq $som.Sum) { return 0 }
        return [double]$som.Sum
    } catch {
        return 0
    }
}

Write-Output "== Hoofdmappen van $Schijf =="
$mappen = Get-ChildItem -LiteralPath $Schijf -Directory -Force -ErrorAction SilentlyContinue
$rijen = foreach ($m in $mappen) {
    [pscustomobject]@{
        Map = $m.FullName
        GB  = [math]::Round((Get-MapGrootte $m.FullName) / 1GB, 2)
    }
}
$rijen | Sort-Object GB -Descending | Select-Object -First $Top | Format-Table -AutoSize

# De twee plekken waar het op een Plesk/IIS-machine meestal zit: logbestanden
# per vhost, en backups. Apart uitsplitsen, want die verdwijnen in het totaal
# van hun bovenliggende map.
$vhosts = Join-Path $Schijf 'inetpub\vhosts'
if (Test-Path -LiteralPath $vhosts) {
    Write-Output ''
    Write-Output '== Per vhost (totaal, en waarvan logs) =='
    $vrijen = foreach ($v in (Get-ChildItem -LiteralPath $vhosts -Directory -Force -ErrorAction SilentlyContinue)) {
        $logpad = Join-Path $v.FullName 'logs'
        [pscustomobject]@{
            Vhost   = $v.Name
            Tot_GB  = [math]::Round((Get-MapGrootte $v.FullName) / 1GB, 2)
            Logs_GB = if (Test-Path -LiteralPath $logpad) { [math]::Round((Get-MapGrootte $logpad) / 1GB, 2) } else { 0 }
        }
    }
    $vrijen | Sort-Object Tot_GB -Descending | Format-Table -AutoSize
}

Write-Output ''
Write-Output '== 25 grootste losse bestanden =='
Get-ChildItem -LiteralPath $Schijf -Recurse -File -Force -ErrorAction SilentlyContinue |
    Sort-Object Length -Descending |
    Select-Object -First 25 @{n='GB';e={[math]::Round($_.Length/1GB,2)}}, LastWriteTime, FullName |
    Format-Table -AutoSize
