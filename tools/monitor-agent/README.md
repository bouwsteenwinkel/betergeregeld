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

## Schijfgebruik per map

`collect.ps1` meet elke minuut alleen het TOTAAL van C:. Daarmee zie je wel dat
de schijf volloopt, niet waardoor. `schijfgebruik.ps1` meet per map en stuurt dat
met `-Push` naar `/monitor/ingest-disk`, waar het als tijdreeks bewaard blijft --
zichtbaar onder **Monitoring -> VPS Servers -> (server) -> Schijfgebruik**, met een
kolom Groei over zeven dagen.

Zonder `-Push` drukt het script alleen af en bewaart het niets.

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File "C:\Inetpubhostsetergeregeld.com\httpdocs	ools\monitor-agent\schijfgebruik.ps1" -Push
```

Als dagelijkse taak (eenmalig, als administrator). Bewust 's ochtends en niet
's nachts: het doorlopen van een volle schijf kost minuten aan schijf-IO, en
tussen 03:00 en 05:00 is die machine al het drukst.

```powershell
$actie = New-ScheduledTaskAction -Execute 'powershell.exe' -Argument '-NoProfile -ExecutionPolicy Bypass -File "C:\Inetpubhostsetergeregeld.com\httpdocs	ools\monitor-agent\schijfgebruik.ps1" -Push'
$trigger = New-ScheduledTaskTrigger -Daily -At 08:00
Register-ScheduledTask -TaskName 'BG-Monitor-Schijf' -Action $actie -Trigger $trigger -User 'SYSTEM' -RunLevel Highest
```

Alleen mappen vanaf 100 MB gaan mee, maximaal 200 regels per meting -- kleinere
mappen zijn ruis en zouden de tabel vullen zonder iets te vertellen.

## Gemiste metingen worden nageleverd

Elke meting gaat eerst naar `%ProgramData%\BG-Monitoruffer.jsonl` en wordt
daarna pas verstuurd. Lukt versturen niet, dan blijft de regel staan en gaat hij
de volgende ronde alsnog mee -- oudste eerst, zodat de laatste push de actuele
stand achterlaat in `last_cpu/mem/disk`. Zo is de reeks achteraf compleet, ook
over een storing heen. De buffer is begrensd op 720 metingen (twaalf uur).

Wat dit **niet** oplost: start de geplande taak zelf niet, dan is er niets om te
bufferen. Het onderscheid is wel meteen zichtbaar -- komen er metingen binnen met
een tijdstempel dat ver voor de ontvangst ligt, dan liep de agent en kon hij
alleen niet weg; blijft het gat helemaal leeg, dan is de taak niet gestart.

## Velden in de payload

| Veld | Betekenis |
|---|---|
| `cpu_percent` | gemiddelde CPU-load 0-100 |
| `mem_used_mb` / `mem_total_mb` | geheugen in MB |
| `disk_used_gb` / `disk_total_gb` | systeemschijf C: in GB |
| `uptime_seconds` | tijd sinds laatste boot |
| `collected_at` | ISO-8601 tijdstip van meting |
