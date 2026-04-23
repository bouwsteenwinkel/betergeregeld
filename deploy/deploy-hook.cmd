@echo off
REM ============================================================
REM Betergeregeld v2 — Plesk Git post-deploy hook (Windows/IIS)
REM
REM Paste the body of this file into Plesk:
REM   Domains > betergeregeld.com > Git > Repository > Settings
REM   -> "Additional deployment actions"
REM
REM Runs after every successful `git pull` by Plesk. Order matters:
REM composer first (generates optimised autoload), then build, then
REM migrations, then cache artisan commands.
REM
REM Any failure here leaves the site on the previous `php artisan
REM config:cache` snapshot — users see stale but working code
REM until you push a fix.
REM ============================================================

REM Fail fast: any non-zero exit code aborts the hook.
setlocal enabledelayedexpansion

echo [deploy] composer install
call composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
if errorlevel 1 exit /b 1

echo [deploy] npm build
call npm ci --silent --no-audit --no-fund
if errorlevel 1 exit /b 1
call npm run build
if errorlevel 1 exit /b 1

echo [deploy] storage link
call php artisan storage:link --force

echo [deploy] migrate
call php artisan migrate --force --no-interaction
if errorlevel 1 exit /b 1

echo [deploy] seed idempotent plans
call php artisan db:seed --class=ToolsPlansSeeder --force --no-interaction
call php artisan db:seed --class=AccessGuardTemplatesSeeder --force --no-interaction

echo [deploy] cache
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache
call php artisan event:cache

echo [deploy] done
