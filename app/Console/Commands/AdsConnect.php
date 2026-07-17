<?php

namespace App\Console\Commands;

use App\Services\Ads\GoogleAdsClient;
use Illuminate\Console\Command;

/**
 * OAuth-koppeling voor Google Ads (scope adwords), los van de Agenda-koppeling.
 *
 *   php artisan ads:connect                 → toont de toestemmings-URL
 *   php artisan ads:connect --code=DE_CODE   → wisselt de code in voor een refresh-token
 *
 * Later vervangt een "Koppelen"-knop in het admin dit; de CLI blijft als fallback.
 */
class AdsConnect extends Command
{
    protected $signature = 'ads:connect {--code= : OAuth-code uit de redirect-URL}';

    protected $description = 'Koppelt Google Ads via OAuth. Zonder --code toont het de toestemmings-URL.';

    public function handle(GoogleAdsClient $ads): int
    {
        $code = trim((string) $this->option('code'));

        if ($code === '') {
            $this->line('');
            $this->line('1) Open deze URL en geef toestemming met het Google-account dat het manager-account beheert:');
            $this->line('');
            $this->line('   ' . $ads->authUrl());
            $this->line('');
            $this->line('2) Je komt daarna op de redirect-URL terug met ?code=... . Kopieer die code en draai:');
            $this->line('   php artisan ads:connect --code=DE_CODE');
            $this->line('');
            $this->warn('De redirect-URI moet EXACT zo in de Google Cloud OAuth-app staan:');
            $this->warn('   ' . config('google_ads.redirect_uri'));

            return self::SUCCESS;
        }

        $res = $ads->exchangeCode($code);

        if (! $res['ok']) {
            $this->error('Koppelen mislukt: ' . $res['error']);

            return self::FAILURE;
        }

        $this->info('Gekoppeld. Refresh-token opgeslagen. Controleer met: php artisan ads:status');

        return self::SUCCESS;
    }
}
