# SocketLabs e-mail-monitoring — status

_Laatste update: 2026-06-13 · commit `94eff24`_

Monitoring van uitgaande e-mail (SocketLabs) binnen de bestaande Betergeregeld
monitoring-tool. Realtime via event-webhooks, met een API-poll als vangnet.
Alerts lopen via het bestaande `monitor:check-alerts` (state-based: één mail per
overgang, naar `config('monitor.alert_email')`).

## Status: gebouwd & getest, nog NIET geactiveerd
- Code compleet en gecommit, migratie **live op prod** (additieve tabellen).
- Lokaal gelint + evaluator/poll smoke-getest.
- **Wacht op**: SocketLabs-credentials in `.env` + webhook-registratie in het
  SocketLabs-dashboard (zie "Activeren"). Zolang dat niet gebeurd is, ontvangt
  het systeem geen events en blijft alles stil/`ok` (geen valse alerts).

## Wat wordt bewaakt (5 dimensies)
| Dimensie   | Signaal | Bron |
|------------|---------|------|
| `queue`    | Deferred/Queued-backlog (queue loopt vol) | webhook |
| `failure`  | Failed-aandeel boven drempel (bij voldoende volume) | webhook |
| `complaint`| Spam-klachten (reputatie) | webhook |
| `silence`  | Geen events meer (dead-man, **opt-in**) | webhook |
| `api`      | SocketLabs v2 API onbereikbaar / creds kapot | poll |

## Componenten
- **Webhook**: `POST /monitor/socketlabs/webhook` → `App\Http\Controllers\Monitor\SocketLabsWebhookController`
  (CSRF-exempt in `bootstrap/app.php`). Valideert `SecretKey` + `ServerID` uit de
  payload (401 bij mismatch). Slaat deliverability-events op; `Tracking` (opens/
  clicks) wordt genegeerd.
- **Opslag**: `socketlabs_events` (ruwe events) + `socketlabs_status` (single-row
  alert-state per dimensie). Migratie: `database/migrations/2026_06_13_100000_create_socketlabs_tables.php`.
- **Evaluatie**: `App\Services\Monitor\SocketLabsEvaluator::conditions()` (gedeeld
  door alerting én Filament-widget).
- **Alerting**: `checkSocketLabs()` in `App\Console\Commands\MonitorCheckAlerts.php`
  (draait elke 5 min via `routes/console.php`).
- **Poll (vangnet)**: `monitor:socketlabs-poll` (elke 15 min) → `App\Services\Monitor\SocketLabsApi`
  → `GET {base}/servers/{serverId}/reports/messages/detail` met `Authorization: Bearer`.
- **Filament**: read-only resource "SocketLabs e-mail" onder *Monitoring*
  (`app/Filament/Resources/SocketLabsEvents/`) + `SocketLabsStatusWidget`.
- **Config**: `config/socketlabs.php`. **Prune**: in `monitor:prune-metrics`.

## Activeren (TODO)
1. Prod `.env`:
   ```
   SOCKETLABS_SERVER_ID=...
   SOCKETLABS_WEBHOOK_SECRET=<zelf kiezen>
   SOCKETLABS_API_KEY=<v2-key met rapportage-leesrechten>
   ```
2. SocketLabs-dashboard → **Account-level Event Webhook** registreren:
   - Endpoint: `https://betergeregeld.com/monitor/socketlabs/webhook`
   - Zelfde `SecretKey` + `ServerID` als in `.env`.
   - (Validatie-IP 52.152.150.178; endpoint moet 200 OK geven.)
3. Deploy betergeregeld (`git pull`) + `php artisan config:cache`. Migratie draaide
   al op prod.

## Drempels (env, met defaults)
`SOCKETLABS_WINDOW_MINUTES=30` · `SOCKETLABS_DEFERRED_THRESHOLD=50` ·
`SOCKETLABS_FAILURE_RATE_PCT=20` (`SOCKETLABS_MIN_VOLUME=20`) ·
`SOCKETLABS_COMPLAINT_THRESHOLD=1` · `SOCKETLABS_SILENCE_MINUTES=0` (uit) ·
`SOCKETLABS_RETENTION_DAYS=30`.

## Open / later
- Drempels fijn-afstemmen op het echte mailvolume zodra events binnenkomen.
- Eventueel een trend-grafiek (events per type over tijd) in Filament.
- `silence`-dimensie aanzetten (`SOCKETLABS_SILENCE_MINUTES`) als er een
  betrouwbare baseline van constant mailverkeer is.
- API v2 "overview/aggregate"-rapport benutten voor exacte dagtotalen (nu pollt
  de poll alleen op bereikbaarheid).

## Referentie (SocketLabs docs)
- Event Webhooks — auth: alleen `SecretKey` + `ServerID` in de body (geen HMAC/
  handshake), endpoint moet 200 geven.
- Event-types: Queued, Deferred, Delivered, Failed, Tracking, Complaint.
- API v2: `https://api.socketlabs.com/v2`, `Authorization: Bearer`.
