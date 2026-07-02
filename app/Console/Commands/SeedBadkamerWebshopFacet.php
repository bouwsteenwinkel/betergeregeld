<?php

namespace App\Console\Commands;

use App\Models\Channel\Block;
use App\Models\Channel\Site;
use Illuminate\Console\Command;

/**
 * Werkt de WEBSHOP-fase van de badkamerspecialist-triggersite volledig uit.
 *
 * De site is een triggersite (SEO/ads) die aan een badkamerspecialist een van
 * onze hoofdproducten verkoopt. In de standaard (website-)fase toont-ie een
 * renovatie-lead-site; in de webshop-fase moet-ie een geloofwaardige webshop
 * demonstreren ("zo ziet jouw online showroom eruit").
 *
 * Mechaniek: de basisblokken (facet leeg = alle fases) krijgen een
 * content.facets.webshop-override per veld, zodat Block::c() ze in de
 * webshop-fase toont; de al bestaande webshop-only blokken worden aangescherpt
 * zodat het verhaal (assortiment → waarom hier → producten → pakketten →
 * zo bestel je → reviews → faq) sluit. Idempotent: opnieuw draaien herzet
 * alleen de facets.webshop-tak, de rest van de content blijft ongemoeid.
 *
 * Bedoeld als blauwdruk om daarna sector-breed te hergebruiken.
 */
class SeedBadkamerWebshopFacet extends Command
{
    protected $signature = 'channel:seed-badkamer-webshop {--site=badkamerspecialist : channel-site key}';

    protected $description = 'Werk de webshop-facet van de badkamerspecialist-triggersite volledig uit';

    public function handle(): int
    {
        $key  = (string) $this->option('site');
        $site = Site::where('key', $key)->first();
        if (! $site) {
            $this->error("Onbekende channel-site: {$key}");
            return self::FAILURE;
        }

        // Per basisblok (op block_key) de webshop-override; en per webshop-only
        // blok de nieuwe basis-content (die geldt zonder facet-tak).
        $facetOverrides = $this->facetOverrides();
        $baseRewrites   = $this->baseRewrites();

        $touched = 0;
        foreach ($site->blocks()->get() as $block) {
            $bk = $block->block_key;

            if (isset($facetOverrides[$bk])) {
                $content = (array) $block->content;
                $content['facets'] = (array) ($content['facets'] ?? []);
                $content['facets']['webshop'] = $facetOverrides[$bk];
                $block->content = $content;
                $block->save();
                $touched++;
                $this->line("  ✓ facet-override: {$block->type}/{$bk}");
            }

            if (isset($baseRewrites[$bk])) {
                $content = array_replace((array) $block->content, $baseRewrites[$bk]);
                $block->content = $content;
                $block->save();
                $touched++;
                $this->line("  ✓ webshop-blok bijgewerkt: {$block->type}/{$bk}");
            }
        }

        $this->newLine();
        $this->info("Klaar: {$touched} blok(ken) bijgewerkt op '{$key}'. Preview: /_site/{$key}/webshop");
        return self::SUCCESS;
    }

    /** content.facets.webshop-overrides per basisblok (block_key => [veld => waarde]). */
    private function facetOverrides(): array
    {
        return [
            // Hero — verkoopt de webshop i.p.v. de renovatie.
            'hero' => [
                'eyebrow'    => 'Badkamer webshop',
                'title'      => 'Je showroom is 24/7 open: verkoop sanitair en tegels online',
                'sub'        => 'Laat klanten zelf kranen, tegels en complete pakketten bestellen. Veilig betalen met iDEAL, live voorraad en bezorgen of afhalen. Jij verkoopt door terwijl je op de bouw staat.',
                'cta_label'  => 'Bekijk je webshop-voorbeeld',
                'cta2_label' => 'Bekijk het assortiment',
                'cta2_href'  => '#diensten',
                'note'       => 'Gekoppeld aan je site en voorraad, dus geen dubbele administratie',
            ],

            // USP-balk — e-commerce vertrouwenssignalen.
            'usps' => [
                'items' => [
                    ['icon' => '💳', 'text' => 'Veilig afrekenen met iDEAL, creditcard of op rekening'],
                    ['icon' => '📦', 'text' => 'Klanten zien direct wat er op voorraad ligt'],
                    ['icon' => '🚚', 'text' => 'Bezorgen door heel Nederland of gratis afhalen'],
                    ['icon' => '↩️', 'text' => '14 dagen retour op ongebruikte artikelen'],
                ],
            ],

            // Diensten-grid → assortiment per categorie (ankerdoel #diensten).
            'diensten-grid' => [
                'heading' => 'Je complete assortiment online',
                'sub'     => 'Alles wat je in de showroom hebt, ook in de webshop. Overzichtelijk per categorie.',
                'items'   => [
                    ['icon' => '🚿', 'title' => 'Kranen & douches', 'text' => 'Thermostaatkranen, regendouches en inbouwsets van A-merken.'],
                    ['icon' => '🧱', 'title' => 'Tegels & natuursteen', 'text' => 'Wand- en vloertegels per m², inclusief voegen en kimband.'],
                    ['icon' => '🚽', 'title' => 'Sanitair', 'text' => 'Toiletten, wastafels, inbouwreservoirs en badmeubels.'],
                    ['icon' => '🛁', 'title' => 'Baden & douchewanden', 'text' => 'Ligbaden, inloopdouches en glazen wanden op maat.'],
                    ['icon' => '🧰', 'title' => 'Montagematerialen', 'text' => 'Kit, lijm, profielen en gereedschap voor de klus.'],
                    ['icon' => '✨', 'title' => 'Accessoires', 'text' => 'Planchets, haakjes, spiegels en verlichting om af te maken.'],
                ],
            ],

            // Tarieven-prijslijst → complete badkamerpakketten (upsell naast losse producten).
            'tarieven' => [
                'eyebrow' => 'Pakketten',
                'heading' => 'Complete badkamerpakketten',
                'items'   => [
                    ['name' => 'Basispakket compleet', 'desc' => 'Toilet, wastafel, thermostaatkraan en spiegel, op elkaar afgestemd.', 'price' => '€ 1.295'],
                    ['name' => 'Comfortpakket inloopdouche', 'desc' => 'Inloopdouche met glaswand, regendouche, wastafelmeubel en toilet.', 'price' => '€ 2.450'],
                    ['name' => 'Luxepakket met ligbad', 'desc' => 'Vrijstaand bad, dubbele wastafel, inbouwkranen en designradiator.', 'price' => '€ 4.890'],
                    ['name' => 'Stel je eigen pakket samen', 'desc' => 'Kies losse onderdelen in de configurator en zie direct de totaalprijs.', 'price' => 'op maat'],
                ],
            ],

            // Werkwijze-stappen → het bestelproces.
            'werkwijze' => [
                'heading' => 'Zo bestel je in 3 stappen',
                'items'   => [
                    ['title' => 'Kies je producten', 'text' => 'Blader per categorie of stel een pakket samen in de configurator. Zie live of het op voorraad ligt.'],
                    ['title' => 'Reken veilig af', 'text' => 'Betaal met iDEAL, creditcard of zakelijk op rekening. Je krijgt direct een bevestiging.'],
                    ['title' => 'Bezorgen of afhalen', 'text' => 'Op voorraad? Binnen 2 werkdagen bezorgd of klaargezet om op te halen. Montage optioneel bij te boeken.'],
                ],
            ],

            // Galerij — koptekst naar assortiment.
            'galerij' => [
                'heading' => 'Een greep uit het assortiment',
            ],

            // Reviews — over de online-bestelervaring.
            'reviews' => [
                'heading' => 'Wat online-klanten zeggen',
                'items'   => [
                    ['stars' => 5, 'text' => 'Kraan besteld op dinsdag, woensdag al in huis. Netjes verpakt en precies zoals op de foto.', 'author' => 'Peter uit Amersfoort'],
                    ['stars' => 5, 'text' => 'Handig dat ik montage kon bijboeken. Zelf besteld, zij hebben het vakkundig geplaatst.', 'author' => 'Linda uit Nijkerk'],
                    ['stars' => 5, 'text' => 'Als aannemer bestel ik alles op rekening met mijn eigen prijzen. Scheelt me enorm veel tijd.', 'author' => 'Bouwbedrijf Van Dijk'],
                ],
            ],

            // FAQ — webshop-specifiek.
            'faq' => [
                'heading' => 'Veelgestelde vragen over de webshop',
                'items'   => [
                    ['q' => 'Hoe snel wordt mijn bestelling bezorgd?', 'a' => 'Artikelen op voorraad bezorgen we binnen 2 werkdagen. Bij grotere of speciale artikelen zie je de verwachte levertijd in de webshop voordat je afrekent.'],
                    ['q' => 'Kan ik ook afhalen?', 'a' => 'Ja, kies bij het afrekenen voor afhalen. Je krijgt bericht zodra je bestelling klaarstaat in onze loods, meestal nog dezelfde dag.'],
                    ['q' => 'Kan ik de montage laten doen?', 'a' => 'Bij veel producten kun je "inclusief montage" aanvinken. We nemen dan contact op om een moment in te plannen dat jou uitkomt.'],
                    ['q' => 'Ik ben zelf aannemer, kan ik op rekening bestellen?', 'a' => 'Zeker. Vraag een zakelijk account aan, dan bestel je op factuur met jouw eigen prijsafspraken en een maandoverzicht.'],
                    ['q' => 'Wat als een artikel niet bevalt?', 'a' => 'Ongebruikt en in originele verpakking retourneer je binnen 14 dagen. Je krijgt het aankoopbedrag terug zodra we de retour hebben ontvangen.'],
                ],
            ],

            // Contact — koptekst naar de webshop-context.
            'contact' => [
                'heading' => 'Vragen over je bestelling?',
                'sub'     => 'Twijfel je over een product of je bestelling? We helpen je graag verder.',
            ],
        ];
    }

    /**
     * Nieuwe basis-content voor de al bestaande webshop-only blokken (facet=webshop).
     * Deze blokken tonen alleen in de webshop-fase, dus hun basis-content ís de
     * webshop-content — geen facet-tak nodig.
     */
    private function baseRewrites(): array
    {
        return [
            // Repositioneer #webshop-uitleg zodat het niet overlapt met de USP-balk:
            // van "kenmerken" naar "waarom klanten juist bij jou online kopen".
            'webshop-uitleg' => [
                'heading' => 'Meer dan alleen een bestelknop',
                'sub'     => 'De webshop verkoopt, adviseert en werkt samen met je vakmanschap.',
                'items'   => [
                    ['icon' => '🧑‍🔧', 'title' => 'Montage erbij te boeken', 'text' => 'Klant bestelt online en vinkt \'inclusief montage\' aan. Jij plant het in.'],
                    ['icon' => '💬', 'title' => 'Advies bij twijfel', 'text' => 'Chat- en belknop bij elk product, zodat een klant niet afhaakt bij een keuze.'],
                    ['icon' => '🏢', 'title' => 'Zakelijk op rekening', 'text' => 'Aannemers en collega\'s bestellen op factuur met hun eigen prijsafspraak.'],
                    ['icon' => '🔗', 'title' => 'Gekoppeld aan je voorraad', 'text' => 'Verkocht is verkocht: voorraad en webshop lopen gelijk, geen nee-verkoop.'],
                ],
            ],

            // #webshop-producten: vierde regel erbij voor een vollere prijslijst.
            'webshop-producten' => [
                'eyebrow' => 'Webshop',
                'heading' => 'Populaire producten',
                'items'   => [
                    ['name' => 'Thermostatische douchekraan chroom', 'desc' => 'Compleet met glijstang en handdouche, 5 jaar garantie.', 'price' => '€ 179'],
                    ['name' => 'Inbouwreservoir Geberit UP320', 'desc' => 'Inclusief bedieningsplaat naar keuze en montageframe.', 'price' => '€ 385'],
                    ['name' => 'Wastafelmeubel 80 cm met spiegelkast', 'desc' => 'Greeploos, softclose laden en keramische wastafel.', 'price' => '€ 349'],
                    ['name' => 'Onderhoudsset badkamer', 'desc' => 'Kitverwijderaar, sanitairkit wit, voegreiniger en microvezeldoek.', 'price' => '€ 29,50'],
                ],
            ],
        ];
    }
}
