<?php

namespace App\Console\Commands;

use App\Models\Channel\Branche;
use App\Services\ChannelSiteResolver;
use App\Services\ChannelSites\PlaceBusinessFinder;
use Illuminate\Console\Command;

/**
 * Voorwarmt de "bedrijven in de regio"-cache (Google Places) voor een branche,
 * zodat bezoekers/crawlers geen trage cold-fetch krijgen. Respecteert het
 * dag-plafond (config services.google_places.daily_cap) en slaat al-gecachete
 * plaatsen over. Zo houd je grip op de API-kosten.
 *
 * Voorbeelden:
 *   php artisan channel:places:warm badkamerspecialist --province=utrecht
 *   php artisan channel:places:warm badkamerspecialist --limit=100 --sleep=250
 */
class ChannelPlacesWarm extends Command
{
    protected $signature = 'channel:places:warm
        {branche : branche-key (bv. badkamerspecialist)}
        {--province= : alleen deze provincie-slug (bv. noord-holland)}
        {--limit=0 : max. aantal nieuwe fetches deze run (0 = alles binnen het plafond)}
        {--sleep=200 : ms wachten tussen calls}
        {--force : ook al-gecachete plaatsen opnieuw ophalen}';

    protected $description = 'Voorwarmt de Places-bedrijvencache voor een branche (met kostenplafond)';

    public function handle(ChannelSiteResolver $resolver, PlaceBusinessFinder $finder): int
    {
        if (! $finder->enabled()) {
            $this->error('Google Places staat uit of GOOGLE_PLACES_KEY ontbreekt.');
            return self::FAILURE;
        }

        $branche = Branche::where('key', $this->argument('branche'))->first();
        if (! $branche) {
            $this->error('Branche niet gevonden: ' . $this->argument('branche'));
            return self::FAILURE;
        }

        $tokens   = (array) $branche->places;
        $business = array_merge((array) config('channel_places.business', []), (array) ($tokens['business'] ?? []));
        // Branche-tokens (niet plaats) in de query invullen; :city doet de finder.
        $business['query'] = \App\Support\ChannelTokens::vul(
            (string) ($business['query'] ?? ':niche :city'), $tokens, $branche->key
        );

        $prov   = (string) $this->option('province');
        $places = $prov ? $resolver->provincePlaces($prov) : $resolver->places();
        if (! $places) {
            $this->error('Geen plaatsen gevonden' . ($prov ? ' voor provincie ' . $prov : '') . '.');
            return self::FAILURE;
        }
        $regions = $resolver->regions();

        $limit   = (int) $this->option('limit');
        $sleepMs = max(0, (int) $this->option('sleep'));
        $force   = (bool) $this->option('force');
        $brKey   = (string) $branche->key;

        $this->info("Warm '{$brKey}' — {$business['query']}  (cap: {$finder->dailyCap()}/dag, vandaag: {$finder->todayCalls()})");

        $fetched = 0;
        $skipped = 0;
        foreach ($places as $slug => $name) {
            if ($finder->capReached()) {
                $this->warn("Dagplafond bereikt ({$finder->todayCalls()}/{$finder->dailyCap()}) — gestopt. Draai morgen verder.");
                break;
            }
            if ($limit > 0 && $fetched >= $limit) {
                $this->line("Limiet van {$limit} deze run bereikt — gestopt.");
                break;
            }
            if (! $force && $finder->isCached($brKey, $slug)) {
                $skipped++;
                continue;
            }

            $region = $regions[$slug] ?? 'Nederland';
            $res    = $finder->forPlace($brKey, $slug, $name, $region, $business);
            $fetched++;
            if ($fetched % 25 === 0) {
                $this->line("  {$fetched} opgehaald… (laatste: {$name}, " . count($res) . ' bedrijven)');
            }
            if ($sleepMs > 0) {
                usleep($sleepMs * 1000);
            }
        }

        $this->info("Klaar. {$fetched} opgehaald, {$skipped} overgeslagen (al gecachet). Calls vandaag: {$finder->todayCalls()}/{$finder->dailyCap()}.");

        // De marktcijfers op /prijzen en /vergelijken worden uit deze rijen
        // gedicht. Hier meteen opnieuw uitrekenen, anders wacht de eerste
        // bezoeker na het verlopen van de cache ~1,3 seconde op de optelsom.
        $cijfers = app(\App\Services\ChannelSites\BrancheMarktcijfers::class)->warm($brKey);
        $this->line($cijfers
            ? sprintf('Marktcijfers ververst: %d aanbieders in %d plaatsen.', $cijfers['aanbieders'], $cijfers['plaatsen'])
            : 'Marktcijfers: nog te weinig data voor deze branche.');

        return self::SUCCESS;
    }
}
