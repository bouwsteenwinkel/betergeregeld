<?php

namespace App\Console\Commands;

use App\Models\Channel\Site;
use Illuminate\Console\Command;

/**
 * Werkt de AUTOMATISERING-fase van de badkamerspecialist-triggersite uit.
 * Zelfde patroon als de webshop-/portaal-seeder: content.facets.automatisering
 * op de basisblokken, zodat /voorbeeld/automatisering leest als een showcase van
 * de back-office die zichzelf doet (offertes, planning, facturen, koppelingen)
 * i.p.v. de renovatie-website. Naast de bestaande auto-blokken (#auto-uitleg,
 * #auto-cta).
 *
 * Automatisering is back-office: de content spreekt de ONDERNEMER aan; alleen
 * waar de klant het merkt (bevestigingen, herinneringen, review-verzoek) blijft
 * het klantgericht. Idempotent. Blauwdruk voor sector-brede uitrol.
 */
class SeedBadkamerAutomatiseringFacet extends Command
{
    protected $signature = 'channel:seed-badkamer-automatisering {--site=badkamerspecialist : channel-site key}';

    protected $description = 'Werk de automatisering-facet van de badkamerspecialist uit';

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
            $content['facets']['automatisering'] = $overrides[$block->block_key];
            $block->content = $content;
            $block->save();
            $touched++;
            $this->line("  ✓ facet-override: {$block->type}/{$block->block_key}");
        }

        $this->newLine();
        $this->info("Klaar: {$touched} blok(ken) bijgewerkt op '{$key}'. Preview: /_site/{$key}/voorbeeld/automatisering");
        return self::SUCCESS;
    }

    /** content.facets.automatisering-overrides per basisblok. */
    private function facetOverrides(): array
    {
        return [
            'hero' => [
                'eyebrow'    => 'Automatisering',
                'title'      => 'Offertes, planning en facturen die zichzelf doen',
                'sub'        => 'Minder tijd achter de laptop, meer tijd op de bouw. Je aanvraag wordt een offerte, je planning staat gelijk, en facturen en herinneringen gaan vanzelf de deur uit.',
                'cta_label'  => 'Bekijk je automatisering-voorbeeld',
                'cta2_label' => 'Wat automatiseren we?',
                'cta2_href'  => '#diensten',
                'note'       => 'Gekoppeld aan je site, agenda en boekhouding',
            ],

            'usps' => [
                'items' => [
                    ['icon' => '📄', 'text' => 'Een offerte klaar in 10 minuten, niet in een half uur'],
                    ['icon' => '🧾', 'text' => 'Facturen en herinneringen gaan automatisch'],
                    ['icon' => '📅', 'text' => 'Planning staat direct in je agenda en die van je ploeg'],
                    ['icon' => '🔗', 'text' => 'Website, agenda en boekhouding werken met elkaar mee'],
                ],
            ],

            // Diensten-grid → de koppelingen (ankerdoel #diensten). Bewust een
            // andere hoek dan #auto-uitleg (dat gaat over de admin-taken zelf).
            'diensten-grid' => [
                'heading' => 'Alles gekoppeld, niets dubbel',
                'sub'     => 'Je systemen praten met elkaar, zodat je niets twee keer hoeft in te voeren.',
                'items'   => [
                    ['icon' => '🌐', 'title' => 'Aanvraag naar offerte', 'text' => 'Een aanvraag via je site komt binnen als concept-offerte, klaar om te versturen.'],
                    ['icon' => '📅', 'title' => 'Agenda en planning', 'text' => 'Ingeplande klussen staan direct in jouw agenda en die van je ploeg.'],
                    ['icon' => '🧮', 'title' => 'Boekhouding', 'text' => 'Facturen lopen automatisch door naar je boekhoudpakket.'],
                    ['icon' => '💬', 'title' => 'Bericht aan de klant', 'text' => 'Bevestigingen en herinneringen gaan vanzelf naar de klant.'],
                    ['icon' => '⭐', 'title' => 'Reviews', 'text' => 'Na oplevering automatisch een verzoek om een Google-review.'],
                    ['icon' => '📊', 'title' => 'Eén overzicht', 'text' => 'Offertes, planning en openstaande facturen op één dashboard.'],
                ],
            ],

            // Tarieven-prijslijst → wat het je per week bespaart.
            'tarieven' => [
                'eyebrow' => 'Tijdwinst',
                'heading' => 'Wat het je bespaart',
                'items'   => [
                    ['name' => 'Offertes maken', 'desc' => 'Standaardposten staan klaar, binnen 10 minuten verstuurd.', 'price' => '3 uur/week'],
                    ['name' => 'Facturen en herinneringen', 'desc' => 'Automatisch verstuurd op het juiste moment.', 'price' => '2 uur/week'],
                    ['name' => 'Planning doorgeven', 'desc' => 'Iedereen weet waar hij moet zijn, zonder appjes.', 'price' => '1 uur/week'],
                    ['name' => 'Reviews verzamelen', 'desc' => 'Meer reviews zonder dat je eraan hoeft te denken.', 'price' => 'meer 5 sterren'],
                ],
            ],

            // Werkwijze-stappen → hoe we het inrichten.
            'werkwijze' => [
                'heading' => 'Zo richten we het in',
                'items'   => [
                    ['title' => 'We brengen je proces in kaart', 'text' => 'Van aanvraag tot factuur: waar zit nu je handwerk?'],
                    ['title' => 'We koppelen je tools', 'text' => 'Website, agenda, boekhouding en mail gaan met elkaar praten.'],
                    ['title' => 'Jij houdt tijd over', 'text' => 'De routine loopt vanzelf, jij bent bezig met de bouw.'],
                ],
            ],

            'galerij' => [
                'heading' => 'Zo houd je tijd over voor het echte werk',
            ],

            // Reviews → klantgericht: de klant merkt de automatisering aan de
            // soepele communicatie.
            'reviews' => [
                'heading' => 'Wat klanten merken van de soepele afhandeling',
                'items'   => [
                    ['stars' => 5, 'text' => 'Meteen een bevestiging gehad en een dag vooraf een herinnering. Netjes geregeld.', 'author' => 'Erik uit Barneveld'],
                    ['stars' => 5, 'text' => 'De factuur kwam automatisch en keurig op tijd. Alles klopte precies.', 'author' => 'Petra uit Ede'],
                    ['stars' => 5, 'text' => 'Werd na afloop netjes gevraagd om een review, met één klik geregeld.', 'author' => 'Hans uit Veenendaal'],
                ],
            ],

            'faq' => [
                'heading' => 'Veelgestelde vragen over automatisering',
                'items'   => [
                    ['q' => 'Moet ik mijn huidige software vervangen?', 'a' => 'Meestal niet. We koppelen aan wat je al gebruikt, zoals je boekhoudpakket en agenda.'],
                    ['q' => 'Hoeveel tijd kost het opzetten?', 'a' => 'De basis staat vaak binnen een paar dagen. Je hoeft zelf weinig te doen.'],
                    ['q' => 'Blijft de controle bij mij?', 'a' => 'Ja. Offertes en facturen gaan pas de deur uit na jouw akkoord, tenzij je het anders wil.'],
                    ['q' => 'Werkt het ook op de bouw?', 'a' => 'Ja, alles werkt op je telefoon. Uren en foto\'s boek je direct vanaf locatie.'],
                ],
            ],

            'contact' => [
                'heading' => 'Benieuwd wat je kunt automatiseren?',
                'sub'     => 'Vraag een korte demo aan, dan laten we het live zien. Of neem gewoon even contact op.',
            ],
        ];
    }
}
