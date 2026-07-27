<?php

return [
    // Geheim voor de ops-endpoints (webhooks/deploy, webhooks/artisan). Leeg = endpoints uit (404).
    // Staat bewust in een config-file en niet los in de code: env() geeft null zodra config gecached
    // is, en de deploy doet zelf `config:cache`.
    'token' => env('DEPLOY_TOKEN', ''),

    // git-binary. Vol pad opgeven als 'git' niet op de PATH van de IIS-app-pool-user staat,
    // bijvoorbeeld 'C:\Program Files\Git\cmd\git.exe'.
    'git_bin' => env('DEPLOY_GIT_BIN', 'git'),

    // Branch die gepulld wordt.
    'branch' => env('DEPLOY_BRANCH', 'main'),

    // Cache-commando's die na een pull draaien, in deze volgorde. Bewust GEEN route:cache
    // (breekt op de closure-route '/') en GEEN view:cache — zie DEPLOY.md.
    //
    // filament:cache-components i.p.v. filament:optimize: die laatste roept intern ook
    // `icons:cache` aan, en blade-icons registreert dat commando alleen in een console-run
    // (runningInConsole) — via de webhook bestaat het niet en klapt filament:optimize eruit.
    // icons:cache staat er daarom los achter: bestaat het niet, dan slaat de deploy het over.
    'cache_commands' => array_values(array_filter(array_map('trim', explode(',', (string) env(
        'DEPLOY_CACHE_COMMANDS',
        'optimize:clear,config:cache,filament:cache-components,icons:cache'
    ))))),

    // Optionele IP-allowlist (comma-lijst). Leeg = alleen de token beschermt het endpoint.
    'allowed_ips' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('DEPLOY_ALLOWED_IPS', ''))
    ))),

    // Destructieve commando's die de artisan-runner weigert, ook al zijn ze geregistreerd.
    'blocked_commands' => array_values(array_filter(array_map('trim', explode(',', (string) env(
        'DEPLOY_BLOCKED_COMMANDS',
        'migrate:fresh,migrate:reset,migrate:rollback,db:wipe,tinker,serve'
    ))))),

    // Max. looptijd (seconden) voor één commando via de webhook. IIS/FastCGI kapt zelf ook af
    // (ruwweg 300s); langer werk hoort in de scheduler, niet in dit endpoint.
    'command_timeout' => (int) env('DEPLOY_COMMAND_TIMEOUT', 280),

    // OPcache-flush op Plesk-Windows: elke php-cgi.exe heeft z'n EIGEN opcache, dus
    // opcache_reset() in dit proces raakt de andere workers niet. Daarom worden de overige
    // php-cgi-processen afgeschoten (IIS respawnt ze vanzelf) — het handmatige
    // `Stop-Process -Name php-cgi -Force` uit DEPLOY.md, maar dan zonder het proces dat dit
    // verzoek afhandelt, zodat het antwoord nog terugkomt. %PID% = onze eigen process-id.
    'recycle_command' => env('DEPLOY_RECYCLE_COMMAND', 'taskkill /F /IM php-cgi.exe /FI "PID ne %PID%"'),
];
