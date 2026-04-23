# Deployment checklist — betergeregeld.com (Plesk / Windows IIS)

The critical one-time and per-deploy steps to keep prod fast and correct.
Platform assumptions: Plesk-Windows, PHP 8.3, MariaDB, IIS/FastCGI.
Run commands from the project root unless noted.

> **Last updated:** reflects fases 1–13 (bookkeeping, shipping rates, LEGO lookup,
> AccessGuard including AI, first-run + demo seed).

## 1. `.env` — complete reference

```ini
APP_NAME="Beter Geregeld"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://betergeregeld.com
APP_KEY=base64:...              # generated once, never regenerate — sessions break
APP_LOCALE=nl
APP_FALLBACK_LOCALE=en

# Database
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_betergeregeld
DB_USERNAME=betergeregeld
DB_PASSWORD=...

# Sessions + cache + queue (all DB-backed for a single-server deploy)
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database       # schedule:run drives the cron, no worker needed

# Mail — real SMTP, NOT "log"
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS="no-reply@betergeregeld.com"
MAIL_FROM_NAME="Beter Geregeld"

# Billing (Mollie). Leave empty or "fake_..." to use the local
# fake-mode fallback (for smoke-tests without a real Mollie account).
MOLLIE_KEY=live_...

# External lookups
REBRICKABLE_API_KEY=...         # LEGO Element Finder

# AccessGuard AI. Empty key or "fake_..." keeps canned responses.
# When populated, used for: ai-explain, CSV smart-map, screenshot
# ingest (vision), review suggestions, executive summary.
OPENAI_API_KEY=sk-...
ACCESSGUARD_AI_ENABLED=true
ACCESSGUARD_AI_MODEL=gpt-4o-mini
```

Verify in one shot:
```powershell
php artisan about
```
Look for: Environment=production, Debug=false, Config/Routes/Views Cached=true.

## 2. Cache everything (run after every code deploy)

```powershell
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```
If `route:cache` fails because a route uses a closure, skip it — the app still runs, just a little slower.

## 3. Database

```powershell
# Backup BEFORE migrating — migrations are irreversible in prod
mysqldump -u betergeregeld -p db_betergeregeld > backup-%date:~-4%%date:~3,2%%date:~0,2%.sql

php artisan migrate --force
php artisan db:seed --class=ToolsPlansSeeder --force
php artisan db:seed --class=AccessGuardTemplatesSeeder --force  # per-tenant onboarding/offboarding templates
```

`ToolsPlansSeeder` + `AccessGuardTemplatesSeeder` are idempotent — safe to re-run on every deploy.
Re-seed `ToolsPlansSeeder` whenever plan features change (new tool added, limit adjusted).

## 4. Storage symlink

Invoice logos, receipts, PDF exports and (from fase 8) AccessGuard evidence uploads live under `storage/app/`:
```powershell
php artisan storage:link
```
Windows note: if symlink creation fails, use an IIS virtual directory instead — map `public/storage` → `storage/app/public`.

## 5. OPcache

Plesk → Domains → betergeregeld.com → PHP Settings:
- `opcache.enable=1`
- `opcache.memory_consumption=192`
- `opcache.max_accelerated_files=20000`
- `opcache.validate_timestamps=0`   **← only after deploy; force invalidation by restarting FastCGI**

Without OPcache the Filament admin is painfully slow. After each deploy: Plesk → Services → restart FastCGI.

## 6. Scheduler — 5 daily cron jobs

Laravel's `schedule:run` drives every time-based feature. Single cron-task runs every minute:
```
php C:\inetpub\vhosts\betergeregeld.com\httpdocs\artisan schedule:run
```

The scheduler itself dispatches:

| Time  | Command                                      | Purpose                                                      |
|-------|----------------------------------------------|--------------------------------------------------------------|
| 03:00 | `accessguard:scan-risks`                     | Scan all tenants for 7 risk patterns (stale_admin, orphan…) |
| 03:15 | `accessguard:build-reminders`                | Build deadline reminders for cycles/processes/actions        |
| 06:00 | `bookkeeping:generate-recurring-invoices`    | Auto-create + email recurring invoices                       |
| 08:00 | `accessguard:send-digests`                   | Daily AccessGuard digest email per opted-in user             |
| 08:00 | `bookkeeping:send-invoice-reminders`         | Reminder mails for unpaid invoices                           |

Verify they're registered:
```powershell
php artisan schedule:list
```

No queue worker needed — everything runs inline from the scheduled command. If you ever add a long-running job (email sending at scale, PDF batch export), switch `QUEUE_CONNECTION` from `database` to `redis` and add a `queue:work` supervisor task.

## 7. Mollie — going live

1. Swap `MOLLIE_KEY=test_...` → `MOLLIE_KEY=live_...` in `.env` and re-cache config.
2. Verify: `php artisan tinker --execute="echo app(App\Services\Billing\MollieGateway::class)->isFake() ? 'FAKE' : 'REAL';"` → expect `REAL`.
3. In Mollie dashboard: webhook URL = `https://betergeregeld.com/webhooks/mollie`. This endpoint is CSRF-exempt (see `bootstrap/app.php`).
4. Do a real €0.01 test subscription once to confirm the full flow (checkout → webhook → feature activation).

## 8. OpenAI — enabling AI features

Without a key, AccessGuard AI features return canned responses via the fake-mode fallback — safe for first deploy, but customers won't see real AI value.

When you're ready:
1. Set `OPENAI_API_KEY=sk-...` in `.env`.
2. Run `php artisan config:clear` + `php artisan config:cache`.
3. Verify: `php artisan tinker --execute="echo app(App\Services\AccessGuard\Ai\OpenAiClient::class)->isFake() ? 'FAKE' : 'REAL';"` → expect `REAL`.
4. Smoke-test by triggering the "🤖 Uitleg" button on a risk flag in the Demo BV tenant.

Default model is `gpt-4o-mini` — cheap and fast. Switch via `ACCESSGUARD_AI_MODEL` if you need higher quality.

## 9. File permissions

`storage/` and `bootstrap/cache/` must be writable by the IIS app-pool identity (usually `IUSR` or the pool's virtual account). On Plesk: Hosting Settings → Additional write/modify permissions on those two trees.

Evidence uploads land under `storage/app/accessguard/evidence/{tenant}/{item}/` — make sure the parent is writable.

## 10. Post-deploy verification

Run in this order:

```powershell
# 1. App is alive
curl https://betergeregeld.com/up            # expect 200

# 2. Locale redirect works
curl -I https://betergeregeld.com/           # expect 302 to /nl

# 3. Route list compiles (catches bad imports)
php artisan route:list --path=tools | measure -Line
php artisan route:list --path=accessguard | measure -Line

# 4. Scheduler registered
php artisan schedule:list                    # expect 5 entries

# 5. Migrations all ran
php artisan migrate:status | select-string "Pending" | measure -Line    # expect 0

# 6. Plan features seeded
php artisan tinker --execute="echo App\Models\PlanFeature::count();"    # expect ~60+

# 7. AccessGuard templates for every tenant
php artisan tinker --execute="echo App\Models\AccessGuard\ChecklistTemplate::count();"

# 8. Mollie + OpenAI status
php artisan tinker --execute="echo 'Mollie: ' . (app(App\Services\Billing\MollieGateway::class)->isFake() ? 'FAKE' : 'REAL') . PHP_EOL . 'OpenAI: ' . (app(App\Services\AccessGuard\Ai\OpenAiClient::class)->isFake() ? 'FAKE' : 'REAL');"
```

## 11. First trial customer flow

When you're pointing a real prospect at the app:
1. They register at `/nl/register`.
2. Pick Pro (14-day trial, no credit card).
3. Land at dashboard → click AccessGuard card → lands at `/nl/tools/accessguard`.
4. Since the tenant is empty, the first-run splash shows.
5. Recommend they click **Start met demo-data** first to see the product in action.
6. Once they've clicked around, they can wipe demo-data and import their own via CSV, screenshot ingest, or manual entry.

**Feedback channel:** the floating "💬 Feedback" button on every AccessGuard page writes to `accessguard_feedback`. Monitor via Filament admin (`/admin/accessguard-feedback`) or a SQL query.

## 12. Rollback

Laravel is stateless — keep the previous release folder on disk and swap the IIS document root back on failure. After swap: `php artisan config:cache` + `php artisan route:cache`.

Database migrations are the only thing you can't easily roll back; always take a MariaDB dump before `migrate --force` (step 3 above). If a migration breaks, restore the dump before un-swapping.
