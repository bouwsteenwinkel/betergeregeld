<?php

namespace App\Console\Commands;

use App\Services\Ads\GoogleAdsManager;
use Illuminate\Console\Command;

/**
 * Vervangt het structured-snippet-fragment ("Website-informatie") van een
 * bestaande campagne. Bedoeld om een afgekeurd fragment (bv. een false-positive)
 * te herstellen zonder de campagne opnieuw op te bouwen — snippet-assets zijn bij
 * Google immutable, dus de oude wordt ontkoppeld en een nieuwe gekoppeld.
 *
 * Deelt de logica met het admin-paneel via GoogleAdsManager.
 *
 *   php artisan ads:snippet 24046650881 --profile=bouwverrassing
 *   php artisan ads:snippet 24046650881 --header=Types --values="Kleine sets,Grote sets,Themasets,Verrassingssets"
 */
class AdsSnippet extends Command
{
    protected $signature = 'ads:snippet
        {campaign : campagne-ID (zie ads:campaign --list)}
        {--profile= : profiel-key uit config/ads_campaigns.php; kop+waarden komen daaruit}
        {--header= : fragment-kop (alleen als geen --profile), bv. Types}
        {--values= : komma-gescheiden waarden (alleen als geen --profile)}';

    protected $description = 'Vervangt het structured-snippet-fragment van een campagne (herstel afgekeurd fragment).';

    public function handle(GoogleAdsManager $mgr): int
    {
        if (! $mgr->connected()) {
            $this->error('Niet gekoppeld. Zie ads:status.');

            return self::FAILURE;
        }

        $id      = (string) $this->argument('campaign');
        $profile = $this->option('profile');

        if ($profile) {
            $res = $mgr->syncSnippetFromProfile($id, $profile);
        } else {
            $header = (string) ($this->option('header') ?? '');
            $values = array_filter(array_map('trim', explode(',', (string) ($this->option('values') ?? ''))), fn ($v) => $v !== '');

            if ($header === '' || $values === []) {
                $this->error('Geef --profile, óf --header met --values op.');

                return self::FAILURE;
            }

            $res = $mgr->syncStructuredSnippet($id, $header, $values);
        }

        if (! $res['ok']) {
            $this->error('Mislukt: ' . $res['error']);

            return self::FAILURE;
        }

        $this->info('Fragment bijgewerkt op campagne ' . $id . '.'
            . ($res['removed'] > 0 ? ' (' . $res['removed'] . ' oud fragment ontkoppeld)' : ''));

        return self::SUCCESS;
    }
}
