<?php

namespace App\Console\Commands;

use App\Services\Ads\GoogleAdsManager;
use Illuminate\Console\Command;

/**
 * Werkt een BESTAANDE campagne bij naar het profiel (config/ads_campaigns.php)
 * zónder 'm te slopen: advertenties (RSA per advertentiegroep), sitelinks en/of
 * het structured-snippet-fragment. Bedoeld om na profiel-verbeteringen een live
 * campagne op orde te brengen — bv. een "Slechte" Advertentiekwaliteit of een
 * "voeg sitelinks toe"-aanbeveling. Status en historie blijven behouden.
 *
 * Zonder deel-vlaggen worden alle onderdelen gesynct (--all).
 *
 *   php artisan ads:sync 24052006207 --profile=bouwsteenwinkel
 *   php artisan ads:sync 24041879208 --profile=bedrijfswebsite --ads --validate
 *   php artisan ads:sync 24052006207 --profile=bouwsteenwinkel --sitelinks
 */
class AdsSync extends Command
{
    protected $signature = 'ads:sync
        {campaign : campagne-ID (zie ads:campaign --list)}
        {--profile= : profiel-key uit config/ads_campaigns.php (verplicht)}
        {--ads : alleen de advertenties (RSA per groep) syncen}
        {--sitelinks : alleen de sitelinks syncen}
        {--snippet : alleen het fragment syncen}
        {--all : alles syncen (standaard als geen deel-vlag is gegeven)}
        {--validate : toets bij Google (validateOnly) zonder iets te wijzigen}';

    protected $description = 'Werkt een live campagne bij naar het profiel: advertenties, sitelinks en/of fragment.';

    public function handle(GoogleAdsManager $mgr): int
    {
        if (! $mgr->connected()) {
            $this->error('Niet gekoppeld. Zie ads:status.');

            return self::FAILURE;
        }

        $id      = (string) $this->argument('campaign');
        $profile = (string) ($this->option('profile') ?? '');
        if ($profile === '' || ! $mgr->profile($profile)) {
            $this->error('Geef een geldig --profile op. Beschikbaar: ' . implode(', ', array_keys($mgr->profiles())) . '.');

            return self::FAILURE;
        }

        $validate = (bool) $this->option('validate');

        // Geen deel-vlag → alles.
        $all       = (bool) $this->option('all') || ! ($this->option('ads') || $this->option('sitelinks') || $this->option('snippet'));
        $doAds     = $all || (bool) $this->option('ads');
        $doSite    = $all || (bool) $this->option('sitelinks');
        $doSnippet = $all || (bool) $this->option('snippet');

        if ($validate) {
            $this->line('Toetsen bij Google (validateOnly, er wordt niets gewijzigd) …');
        }

        $fail = false;

        if ($doAds) {
            $r = $mgr->syncAdsFromProfile($id, $profile, $validate);
            $this->regel('Advertenties', $r['ok'], $r['error'], $r['ok'] ? "{$r['groups']} groep(en), {$r['replaced']} RSA vervangen" : null);
            $fail = $fail || ! $r['ok'];
        }

        if ($doSite) {
            $r = $mgr->syncSitelinksFromProfile($id, $profile, $validate);
            $this->regel('Sitelinks', $r['ok'], $r['error'], $r['ok'] ? "{$r['removed']} weg, {$r['added']} toegevoegd" : null);
            $fail = $fail || ! $r['ok'];
        }

        if ($doSnippet) {
            $r = $mgr->syncSnippetFromProfile($id, $profile, $validate);
            $this->regel('Fragment', $r['ok'], $r['error'], $r['ok'] ? "{$r['removed']} oud ontkoppeld" : null);
            $fail = $fail || ! $r['ok'];
        }

        return $fail ? self::FAILURE : self::SUCCESS;
    }

    private function regel(string $label, bool $ok, ?string $error, ?string $detail): void
    {
        if ($ok) {
            $this->info(sprintf('  %-12s OK%s', $label, $detail ? ' — ' . $detail : ''));
        } else {
            $this->error(sprintf('  %-12s MISLUKT — %s', $label, $error ?? 'onbekende fout'));
        }
    }
}
