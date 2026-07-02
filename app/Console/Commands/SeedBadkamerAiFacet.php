<?php

namespace App\Console\Commands;

use App\Models\Channel\Site;
use Illuminate\Console\Command;

/**
 * Werkt de AI-fase van de badkamerspecialist-triggersite uit. Zelfde patroon als
 * de webshop-/portaal-/automatisering-seeder: content.facets.ai op de
 * basisblokken, zodat /voorbeeld/ai leest als een slimme assistent die opneemt,
 * meedenkt en offertes voorbereidt, i.p.v. de renovatie-website. Naast de
 * bestaande ai-blokken (#ai-uitleg = capaciteiten, #ai-cta).
 *
 * AI raakt zowel de klant (chat/telefoon 24/7) als de ondernemer (nooit een
 * aanvraag missen). Idempotent. Blauwdruk voor sector-brede uitrol.
 */
class SeedBadkamerAiFacet extends Command
{
    protected $signature = 'channel:seed-badkamer-ai {--site=badkamerspecialist : channel-site key}';

    protected $description = 'Werk de AI-facet van de badkamerspecialist uit';

    public function handle(): int
    {
        $key  = (string) $this->option('site');
        $site = Site::where('key', $key)->first();
        if (! $site) {
            $this->error("Onbekende channel-site: {$key}");
            return self::FAILURE;
        }

        $overrides = $this->facetOverrides();

        $touched = 0;
        foreach ($site->blocks()->get() as $block) {
            if (! isset($overrides[$block->block_key])) {
                continue;
            }
            $content = (array) $block->content;
            $content['facets'] = (array) ($content['facets'] ?? []);
            $content['facets']['ai'] = $overrides[$block->block_key];
            $block->content = $content;
            $block->save();
            $touched++;
            $this->line("  ✓ facet-override: {$block->type}/{$block->block_key}");
        }

        $this->newLine();
        $this->info("Klaar: {$touched} blok(ken) bijgewerkt op '{$key}'. Preview: /_site/{$key}/voorbeeld/ai");
        return self::SUCCESS;
    }

    /** content.facets.ai-overrides per basisblok. */
    private function facetOverrides(): array
    {
        return [
            'hero' => [
                'eyebrow'    => 'AI-assistent',
                'title'      => 'Een assistent die opneemt en meedenkt, ook als jij aan het werk bent',
                'sub'        => 'De assistent beantwoordt telefoon en chat, vraagt door naar wat je nodig hebt en bereidt een eerste offerte voor. Zo mis je nooit meer een klus omdat je handen onder de kit zitten.',
                'cta_label'  => 'Bekijk je AI-voorbeeld',
                'cta2_label' => 'Wat doet de assistent?',
                'cta2_href'  => '#diensten',
                'note'       => 'Praat gewoon Nederlands, in jouw eigen toon',
            ],

            'usps' => [
                'items' => [
                    ['icon' => '📞', 'text' => 'Neemt telefoon en chat aan, 24 uur per dag'],
                    ['icon' => '✅', 'text' => 'Nooit meer een aanvraag missen buiten kantooruren'],
                    ['icon' => '📐', 'text' => 'Bereidt een eerste offerte voor uit een paar foto\'s'],
                    ['icon' => '⭐', 'text' => 'Vraagt na oplevering automatisch om een review'],
                ],
            ],

            // Diensten-grid → de uitkomsten (ankerdoel #diensten). Bewust een
            // andere hoek dan #ai-uitleg (dat toont de capaciteiten zelf).
            'diensten-grid' => [
                'heading' => 'Wat het je oplevert',
                'sub'     => 'Een assistent die het simpele werk afvangt, zodat jij tijd houdt voor de bouw.',
                'items'   => [
                    ['icon' => '📵', 'title' => 'Geen gemiste aanvragen', 'text' => 'Ook buiten kantooruren staat er iemand klaar voor je klant.'],
                    ['icon' => '🎯', 'title' => 'Betere aanvragen', 'text' => 'De assistent vraagt door, zodat je alleen serieuze klussen terugbelt.'],
                    ['icon' => '⚡', 'title' => 'Sneller een offerte', 'text' => 'De eerste inschatting ligt er al voordat je hebt teruggebeld.'],
                    ['icon' => '⭐', 'title' => 'Meer 5-sterren reviews', 'text' => 'Automatisch gevraagd op het beste moment na oplevering.'],
                    ['icon' => '🔕', 'title' => 'Minder onderbrekingen', 'text' => 'De simpele vragen worden afgevangen, jij werkt door.'],
                    ['icon' => '🌙', 'title' => 'Altijd bereikbaar', 'text' => 'Ook als jij onder de kit zit of even vrij bent.'],
                ],
            ],

            // Tarieven-prijslijst → wat de assistent oppakt, met het moment als tag.
            'tarieven' => [
                'eyebrow' => 'De assistent',
                'heading' => 'Wat de assistent voor je oppakt',
                'items'   => [
                    ['name' => 'Inkomende telefoon', 'desc' => 'Neemt op, noteert de klus en schakelt door indien nodig.', 'price' => '24/7'],
                    ['name' => 'Chatvraag op de site', 'desc' => 'Direct antwoord op prijs, planning en werkgebied.', 'price' => 'direct'],
                    ['name' => 'Nieuwe aanvraag', 'desc' => 'Vraagt door en zet een nette samenvatting in je mail.', 'price' => 'meteen'],
                    ['name' => 'Na oplevering', 'desc' => 'Vraagt netjes om een review met een directe link.', 'price' => 'automatisch'],
                ],
            ],

            // Werkwijze-stappen → hoe je de assistent aanzet.
            'werkwijze' => [
                'heading' => 'Zo zet je de assistent aan',
                'items'   => [
                    ['title' => 'We voeren jouw kennis in', 'text' => 'Prijzen, werkgebied, doorlooptijden en veelgestelde vragen.'],
                    ['title' => 'De assistent gaat live', 'text' => 'Op je telefoonlijn en als chat op je site.'],
                    ['title' => 'Jij krijgt de samenvatting', 'text' => 'Elke aanvraag netjes samengevat, jij belt alleen de serieuze terug.'],
                ],
            ],

            'galerij' => [
                'heading' => 'Jij bouwt door, de assistent regelt de rest',
            ],

            // Reviews → klantgericht: de klant ervaart de assistent als snel en behulpzaam.
            'reviews' => [
                'heading' => 'Wat klanten van de snelle reactie vinden',
                'items'   => [
                    ['stars' => 5, 'text' => "'s Avonds via de chat meteen antwoord op mijn vraag over de doorlooptijd. Top.", 'author' => 'Wendy uit Soest'],
                    ['stars' => 5, 'text' => 'Belde buiten kantooruren en werd netjes teruggebeld met een voorstel.', 'author' => 'Ramon uit Baarn'],
                    ['stars' => 5, 'text' => "Kreeg snel een eerste prijsindicatie op basis van mijn foto's.", 'author' => 'Ingrid uit Hilversum'],
                ],
            ],

            'faq' => [
                'heading' => 'Veelgestelde vragen over de AI-assistent',
                'items'   => [
                    ['q' => 'Merkt de klant dat het een assistent is?', 'a' => 'De assistent is duidelijk en behulpzaam. Voor lastige vragen schakelt hij netjes door naar jou.'],
                    ['q' => 'Neemt de AI beslissingen voor mij?', 'a' => 'Nee. Hij verzamelt en bereidt voor. Jij bevestigt de offerte en de afspraak.'],
                    ['q' => 'Wat als de assistent iets niet weet?', 'a' => 'Dan noteert hij de vraag en zorgt dat jij of je team contact opneemt.'],
                    ['q' => 'Werkt het in het Nederlands?', 'a' => 'Ja, gewoon in normaal Nederlands en in jouw eigen toon.'],
                ],
            ],

            'contact' => [
                'heading' => 'Liever direct een mens?',
                'sub'     => 'De assistent helpt je 24/7. Wil je iemand spreken? Bel of mail gerust.',
            ],
        ];
    }
}
