# Deployment checklist — betergeregeld.com (Plesk / Windows IIS)

The critical one-time and per-deploy steps to keep prod fast and correct.
Platform assumptions: Plesk-Windows, PHP 8.3, MariaDB, IIS/FastCGI.
Run commands from the project root unless noted.

## 1. `.env` — must-be-correct values

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://betergeregeld.com
APP_KEY=base64:...          # never regenerate — sessions break

DB_CONNECTION=mariadb
DB_DATABASE=db_betergeregeld
DB_USERNAME=betergeregeld
DB_PASSWORD=...

MAIL_MAILER=smtp            # NOT "log" — invoices and reminders go through this
MAIL_FROM_ADDRESS="no-reply@betergeregeld.com"
MAIL_FROM_NAME="Beter Geregeld"

MOLLIE_KEY=live_...         # swap from test_ when going live
REBRICKABLE_API_KEY=...

SESSION_DRIVER=database     # or redis if available
QUEUE_CONNECTION=database   # schedule:run drives the cron, not a worker
```

Verify in one shot:
```powershell
php artisan about
```
Look for: Environment=production, Debug=false, Config Cached=true, Routes Cached=true.

## 2. Cache everything

After every code deploy:
```powershell
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```
Skip route/view cache if any route closures or `@php` blocks break it (Laravel will tell you).

## 3. Database

```powershell
php artisan migrate --force
php artisan db:seed --class=ToolsPlansSeeder --force
```
`ToolsPlansSeeder` is idempotent — safe to re-run. Run it whenever plan features change (e.g. after adding a new tool with gating keys).

## 4. Storage link

The invoice logos, receipts and PDF exports under `storage/app/` are served via a symlink at `public/storage`:
```powershell
php artisan storage:link
```
Only needed once per deploy-host. If it fails on Windows (symlink perms), hard-copy or use a virtual directory in IIS pointing `public/storage` → `storage/app/public`.

## 5. OPcache

In Plesk → PHP settings, set:
- `opcache.enable=1`
- `opcache.memory_consumption=192`
- `opcache.max_accelerated_files=20000`
- `opcache.validate_timestamps=0`   **← only after deploy; invalidate by restarting the FastCGI pool**

Without OPcache the Filament admin is painful. After each deploy: Plesk → Services → restart FastCGI.

## 6. Scheduler (for reminders + recurring invoices)

Laravel's `schedule:run` drives:
- invoice payment reminders (daily)
- recurring invoices auto-generate + auto-email (daily)

Schedule a Plesk-Cron task every minute:
```
php C:\inetpub\vhosts\betergeregeld.com\httpdocs\artisan schedule:run
```
No separate queue worker needed as long as `QUEUE_CONNECTION=sync` or `database` with the scheduler running — reminders run inline from the scheduled command.

## 7. Mollie live mode

When going live:
1. Swap `MOLLIE_KEY=test_...` → `live_...` in `.env`
2. Verify `php artisan tinker` → `app(App\Services\Billing\MollieGateway::class)->isFake()` returns `false`
3. In Mollie dashboard: set webhook URL to `https://betergeregeld.com/webhooks/mollie` (this endpoint is CSRF-exempt already — see `bootstrap/app.php`).

## 8. File permissions

`storage/` and `bootstrap/cache/` must be writable by the IIS app-pool identity (usually `IUSR` or the pool's virtual account). On Plesk: Hosting Settings → Additional write/modify permissions on those two trees.

## 9. Post-deploy verification

```powershell
# 1. app is alive
curl https://betergeregeld.com/up

# 2. locale redirect works
curl -I https://betergeregeld.com/   # expect 302 to /nl

# 3. tools route list
php artisan route:list --path=tools

# 4. scheduled jobs would fire next
php artisan schedule:list

# 5. plan features are in DB
php artisan tinker --execute="echo App\Models\PlanFeature::count();"
```

## 10. Rollback

Keep the previous release's folder on disk. Laravel is stateless — swap the IIS document root back and run `config:cache` + `route:cache` again. Database migrations are the only thing you can't easily roll back; always take a MariaDB dump before `migrate --force`:
```powershell
mysqldump -u betergeregeld -p db_betergeregeld > backup-%date:~-4%%date:~3,2%%date:~0,2%.sql
```
