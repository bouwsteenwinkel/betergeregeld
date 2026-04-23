# Plesk Git deployment — first-time setup

One-time setup for continuous deploy via `git push`. After these steps,
every new release is: `git push prod main` from your laptop.

**Assumption:** Plesk Obsidian on Windows, IIS hosting. `betergeregeld.com`
is already a registered domain with PHP 8.2+ enabled.

## 1. Prepare the target directory in Plesk

**Plesk UI** → Domains → `betergeregeld.com` → Files

- Existing `httpdocs/` contains v1. Rename it to `httpdocs.v1-backup/`
  (keeps v1 restorable while v2 bakes).
- Create a new empty `httpdocs/`.

**Plesk UI** → Domains → `betergeregeld.com` → Hosting & DNS → Hosting Settings

- **Document root**: change from `httpdocs` to `httpdocs/public`.
  Laravel needs to serve from `public/`; everything else in the repo
  (app/, config/, vendor/, …) must sit above the web root.

## 2. Create the Plesk Git repository

**Plesk UI** → Domains → `betergeregeld.com` → Git → **Add Repository**

- **Repository source**: *Local repository on the server* (Plesk will
  host the repo and you push to it).
- **Deployment mode**: *Automatic deployment* (Plesk auto-deploys on
  every push).
- **Deployment path**: `httpdocs` (Laravel root — Plesk deploys the
  full repo here; docroot is set to `httpdocs/public` from step 1).
- **Additional deployment actions**: paste the entire body of
  `deploy/deploy-hook.cmd` (without the `@echo off` header if Plesk
  rejects it — you can trim to just the `call` lines).

After saving, Plesk shows you the **Remote repository URL**. It'll look
like `ssh+git://betergeregeld@betergeregeld.com:/.../repository.git`.
Copy it. This is the URL you push to.

## 3. Create the database + service DB user

**Plesk UI** → Domains → `betergeregeld.com` → Databases → **Add Database**

- Type: MariaDB
- Database name: `betergeregeld`
- Username: `betergeregeld`
- Password: generate a strong one, copy it to `DB_PASSWORD` in step 5

## 4. Drop in the `.env` (before the first push)

The deploy hook expects `.env` to already exist. Via **File Manager**:

- Create `/httpdocs/.env` (the Plesk Git deployment path, one level
  above `public/`).
- Paste the body of `deploy/.env.production.example`.
- Fill in every `REPLACE_WITH_…` placeholder.
- For `APP_KEY`: on your laptop run `php artisan key:generate --show`
  and paste the output (format `base64:…`). Set this once, never
  rotate it — the AccessGuard Vault and M365 token store depend on it.
- Save.

File permissions: ensure the IIS application pool identity can READ the
`.env` file (usually inherited from `httpdocs/` so nothing to do).

## 5. First push from your laptop

From `C:\Users\thuis\betergeregeldv2`:

```powershell
git remote add prod ssh+git://PLESK_URL_FROM_STEP_2
git push prod main
```

Plesk will:
1. Accept the push
2. Pull into `httpdocs/`
3. Run `deploy-hook.cmd` (composer + npm + migrate + cache)

Watch the deploy log in Plesk → Git → Logs. First push takes 3–5 min
(cold composer + npm cache). Subsequent deploys are 30–60 seconds.

## 6. Run the scheduler

The 6 daily cron jobs (sync directories, scan risks, build reminders,
send digests, recurring invoices, invoice reminders) need a Windows
Scheduled Task:

**Plesk UI** → Tools & Settings → Scheduled Tasks → **Add Task**

- Task type: *Run a command*
- Command:
  `"C:\Program Files\PHP\php.exe" "C:\inetpub\vhosts\betergeregeld.com\httpdocs\artisan" schedule:run`
  (adjust PHP path to match the Plesk PHP installation)
- Run: *Every minute*

Verify after 2 minutes with:
```powershell
php artisan schedule:list
```
(shown via Plesk's SSH/RDP console). Should list 6 jobs with next-run
times in the future.

## 7. Post-deploy smoke tests

Hit each of these in the browser and check there's no 500:

- `https://betergeregeld.com/nl` — homepage
- `https://betergeregeld.com/nl/accessguard` — AccessGuard landing
- `https://betergeregeld.com/nl/accessguard/demo` — public demo (seeds
  demo tenant on first hit, takes ~2s)
- `https://betergeregeld.com/admin` — Filament login
- `https://betergeregeld.com/nl/login` — customer login

If any 500s: check `/httpdocs/storage/logs/laravel-YYYY-MM-DD.log`.

## Next deploys

```powershell
git add .
git commit -m "…"
git push prod main
```

Done. Plesk re-runs the deploy hook; site stays up during the pull
(IIS serves stale PHP from OPcache while files replace).

## Rollback

If a deploy breaks prod:

```powershell
git revert HEAD
git push prod main
```

Reverse-engineers the previous good state and re-runs the deploy hook.
For a broken migration: take a MariaDB dump FIRST (via Plesk →
Databases → betergeregeld → Export Dump), then `php artisan migrate:rollback`
on the server before reverting the code.
