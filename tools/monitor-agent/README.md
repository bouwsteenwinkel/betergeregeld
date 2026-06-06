# VPS Monitoring — collector agent

Pusht elke minuut CPU/RAM/disk/uptime van een Windows-VPS naar het Beter
Geregeld monitoring-dashboard.

## Installeren (aanbevolen)

1. Registreer de server in het admin-paneel: **Monitoring → VPS Servers → New**.
2. Open op die server-rij de **Install**-actie. Die toont een kant-en-klaar
   PowerShell-blok (token al ingevuld) dat:
   - `MONITOR_TOKEN` en `MONITOR_ENDPOINT` als machine-env-vars zet, en
   - een geplande taak `BG-Monitor-Agent` aanmaakt die elke minuut
     `collect.ps1` draait onder het SYSTEM-account.
3. Plak dat blok op de VPS in een **PowerShell als Administrator**.

`collect.ps1` staat na een `git pull` klaar onder
`...\httpdocs\tools\monitor-agent\collect.ps1`.

## Handmatig testen

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File "C:\Inetpub\vhosts\betergeregeld.com\httpdocs\tools\monitor-agent\collect.ps1" -Token "<ingest-token>"
```

Verwachte output: `OK - <servernaam> @ <timestamp>`. Daarna verschijnt de
server binnen ~1 minuut als **Online** in het dashboard.

## Hoe het werkt

- Auth = per-server `ingest_token` (Bearer-header). Eén token per VPS; te
  vernieuwen via de **Token vernieuwen**-actie (oude token vervalt direct).
- Endpoint: `POST /monitor/ingest` (CSRF-vrij, geen sessie).
- Beschikbaarheid/SLA wordt afgeleid uit de heartbeat: ontvangen samples ÷
  verwachte samples per periode (interval = `config/monitor.php`).

## Velden in de payload

| Veld | Betekenis |
|---|---|
| `cpu_percent` | gemiddelde CPU-load 0-100 |
| `mem_used_mb` / `mem_total_mb` | geheugen in MB |
| `disk_used_gb` / `disk_total_gb` | systeemschijf C: in GB |
| `uptime_seconds` | tijd sinds laatste boot |
| `collected_at` | ISO-8601 tijdstip van meting |
