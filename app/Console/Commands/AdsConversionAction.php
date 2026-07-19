<?php

namespace App\Console\Commands;

use App\Services\Ads\GoogleAdsManager;
use Illuminate\Console\Command;

/**
 * Maakt (idempotent) een UPLOAD_CLICKS-conversie-actie voor server-side import.
 * De resource-naam die dit teruggeeft, gebruikt de shop (V3) bij het uploaden
 * van nieuwe abonnementen.
 *
 *   php artisan ads:conversion-action "Nieuw abonnement" --value=10
 */
class AdsConversionAction extends Command
{
    protected $signature = 'ads:conversion-action
        {name=Nieuw abonnement : naam van de conversie-actie}
        {--value=10 : standaardwaarde in euro}
        {--category=SIGNUP : Google-categorie (SIGNUP, PURCHASE, …)}';

    protected $description = 'Maakt/vindt een server-side (UPLOAD_CLICKS) conversie-actie en toont de resource-naam.';

    public function handle(GoogleAdsManager $mgr): int
    {
        if (! $mgr->connected()) {
            $this->error('Niet gekoppeld. Draai eerst ads:connect (zie ads:status).');

            return self::FAILURE;
        }

        $name = (string) $this->argument('name');
        $res  = $mgr->ensureConversionAction($name, (float) $this->option('value'), (string) $this->option('category'));

        if (! $res['ok']) {
            $this->error('Mislukt: ' . $res['error']);

            return self::FAILURE;
        }

        $this->info(($res['created'] ? 'Aangemaakt' : 'Bestond al') . ': ' . $name);
        $this->line('  Resource-naam : ' . $res['resource']);
        $this->line('  Standaardwaarde: € ' . number_format((float) $this->option('value'), 2, ',', '.') . ' (EUR)');
        $this->line('');
        $this->line('Zet deze resource-naam in de shop-config (V3) als GOOGLE_ADS_CONVERSION_ABO.');

        return self::SUCCESS;
    }
}
