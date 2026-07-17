<?php

namespace App\Console\Commands;

use App\Services\Ads\GoogleAdsClient;
use Illuminate\Console\Command;

/**
 * Toont in één oogopslag wat de Google Ads-koppeling nog mist, en test de
 * connectiviteit zodra alles gezet is. Read-only, verandert niets.
 *
 *   php artisan ads:status
 */
class AdsStatus extends Command
{
    protected $signature = 'ads:status';

    protected $description = 'Status van de Google Ads-koppeling: config, OAuth en connectiviteit.';

    public function handle(GoogleAdsClient $ads): int
    {
        $this->table(['Instelling', 'Waarde'], [
            ['GOOGLE_CLIENT_ID', config('google_ads.client_id') ? 'gezet' : 'LEEG'],
            ['GOOGLE_ADS_DEVELOPER_TOKEN', config('google_ads.developer_token') ? 'gezet' : 'LEEG'],
            ['GOOGLE_ADS_LOGIN_CUSTOMER_ID', config('google_ads.login_customer_id') ?: 'LEEG'],
            ['GOOGLE_ADS_CUSTOMER_ID', config('google_ads.customer_id') ?: 'LEEG'],
            ['API-versie', config('google_ads.api_version')],
            ['OAuth refresh-token', $ads->hasRefreshToken() ? 'gekoppeld' : 'NIET gekoppeld → ads:connect'],
        ]);

        if (! $ads->connected()) {
            $this->warn('Nog niet volledig gekoppeld. Vul de .env-waarden aan en draai ads:connect.');

            return self::SUCCESS;
        }

        $this->line('Connectiviteit testen (listAccessibleCustomers) …');
        $res = $ads->listAccessibleCustomers();

        if (! $res['ok']) {
            $this->error('  Mislukt: ' . $res['error']);

            return self::FAILURE;
        }

        $ids = array_map(fn ($r) => str_replace('customers/', '', $r), $res['customers']);
        $this->info('  OK. Zichtbare accounts: ' . (implode(', ', $ids) ?: '(geen)'));

        return self::SUCCESS;
    }
}
