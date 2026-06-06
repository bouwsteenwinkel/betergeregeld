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

    $payload = @{
        collected_at = (Get-Date).ToString('o')
        cpu_percent = [double]$cpu
        mem_used_mb = $memUsedMb
        mem_total_mb = $memTotalMb
        disk_used_gb = $diskUsedGb
        disk_total_gb = $diskTotalGb
        uptime_seconds = $uptimeSeconds
        agent_version = 'ps-1.0'
    } | ConvertTo-Json -Compress

    $headers = @{ Authorization = "Bearer $Token" }
    $resp = Invoke-RestMethod -Uri $Endpoint -Method Post -Body $payload -ContentType 'application/json' -Headers $headers -TimeoutSec 20
    Write-Output ("OK - {0} @ {1}" -f $resp.server, $resp.received_at)
}
catch {
    Write-Error ("Monitor push mislukt: {0}" -f $_.Exception.Message)
    exit 1
}
