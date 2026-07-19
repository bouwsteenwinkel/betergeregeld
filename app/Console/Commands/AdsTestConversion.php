<?php

namespace App\Console\Commands;

use App\Services\Ads\GoogleAdsManager;
use Illuminate\Console\Command;

/**
 * Test de Data Manager-conversie-import end-to-end met een (test-)gclid.
 * Een echte gclid koppelt aan een echte klik; een nep-gclid bevestigt alleen
 * dat scope/endpoint/conversie-actie kloppen (de rij wordt dan genegeerd).
 *
 *   php artisan ads:test-conversion --gclid=ECHTE_GCLID --value=10
 */
class AdsTestConversion extends Command
{
    protected $signature = 'ads:test-conversion
        {--gclid= : de gclid om als conversie in te sturen}
        {--value=10 : conversiewaarde in euro}';

    protected $description = 'Stuurt één test-abonnement-conversie via de Data Manager API.';

    public function handle(GoogleAdsManager $mgr): int
    {
        $gclid = (string) $this->option('gclid');
        if ($gclid === '') {
            $this->error('Geef een --gclid mee (bijv. uit de landings-URL ?gclid=…).');

            return self::FAILURE;
        }

        $time = gmdate('Y-m-d\TH:i:s\Z');
        $this->line('Insturen: gclid=' . substr($gclid, 0, 12) . '… waarde=€' . $this->option('value') . ' tijd=' . $time);

        $res = $mgr->ingestSubscriptionConversion($gclid, $time, (float) $this->option('value'), 'test-' . gmdate('YmdHis'));

        if (! $res['ok']) {
            $this->error('Mislukt: ' . $res['error']);
            $this->line(json_encode($res['results'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return self::FAILURE;
        }

        $this->info('Geaccepteerd door de Data Manager API.');
        $this->line(json_encode($res['results'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
