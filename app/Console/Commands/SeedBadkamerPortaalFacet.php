<?php

namespace App\Console\Commands;

use App\Models\Channel\Site;
use Illuminate\Console\Command;

/**
 * Werkt de KLANTENPORTAAL-fase ("portaal / afspraken") van de badkamerspecialist-
 * triggersite uit. Zelfde patroon als channel:seed-badkamer-webshop: de
 * basisblokken krijgen een content.facets.klantenportaal-override zodat
 * /voorbeeld/klantenportaal als een echt klantenportaal leest (klant plant
 * afspraken, volgt het project, vindt documenten terug) in plaats van de
 * renovatie-website met één los afsprakenblok.
 *
 * Idempotent: herzet alleen de facets.klantenportaal-tak. Blauwdruk voor
 * sector-brede uitrol.
 */
class SeedBadkamerPortaalFacet extends Command
{
    protected $signature = 'channel:seed-badkamer-portaal {--site=badkamerspecialist : channel-site key}';

    protected $description = 'Werk de klantenportaal-facet (portaal/afspraken) van de badkamerspecialist uit';

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
            $content['facets']['klantenportaal'] = $overrides[$block->block_key];
            $block->content = $content;
            $block->save();
            $touched++;
            $this->line("  ✓ facet-override: {$block->type}/{$block->block_key}");
        }

        $this->newLine();
        $this->info("Klaar: {$touched} blok(ken) bijgewerkt op '{$key}'. Preview: /_site/{$key}/klantenportaal");
        return self::SUCCESS;
    }

    /** content.facets.klantenportaal-overrides per basisblok. */
    private function facetOverrides(): array
    {
        return [
            'hero' => [
                'eyebrow'    => 'Klantenportaal',
                'title'      => 'Je klant regelt afspraken en volgt de badkamer in een eigen omgeving',
                'sub'        => 'Klanten plannen zelf een inmeting in, bekijken de planning en het 3D-ontwerp en vinden alle documenten op één plek. Jij hoeft minder te bellen en te mailen.',
                'cta_label'  => 'Bekijk je portaal-voorbeeld',
                'cta2_label' => 'Wat kan de klant?',
                'cta2_href'  => '#diensten',
                'note'       => 'Eigen inlog voor elke klant, gekoppeld aan hun project',
            ],

            'usps' => [
                'items' => [
                    ['icon' => '📅', 'text' => 'Afspraken zelf inplannen, ook buiten kantooruren'],
                    ['icon' => '📍', 'text' => 'Altijd inzicht in de planning en de voortgang'],
                    ['icon' => '📁', 'text' => 'Offerte, facturen en garantie op één plek'],
                    ['icon' => '📞', 'text' => 'Minder telefoontjes en heen-en-weer gemail'],
                ],
            ],

            // Diensten-grid → wat de klant zelf kan in het portaal (ankerdoel #diensten).
            'diensten-grid' => [
                'heading' => 'Wat je klant zelf kan in het portaal',
                'sub'     => 'Een eigen online omgeving, gekoppeld aan hun badkamerproject.',
                'items'   => [
                    ['icon' => '📆', 'title' => 'Afspraken inplannen', 'text' => 'Zelf een moment kiezen voor inmeting, advies of oplevering.'],
                    ['icon' => '🛠️', 'title' => 'Voortgang volgen', 'text' => 'Live de status van sloop, leidingwerk, tegels en afwerking.'],
                    ['icon' => '🎨', 'title' => '3D-ontwerp bekijken', 'text' => 'Het ontwerp inzien en met één klik goedkeuren.'],
                    ['icon' => '🧾', 'title' => 'Facturen en documenten', 'text' => 'Offerte, termijnfacturen en garantiebewijs altijd bij de hand.'],
                    ['icon' => '📷', 'title' => "Foto's van de bouw", 'text' => 'Elke dag een update, ook als de klant er niet bij kan zijn.'],
                    ['icon' => '💬', 'title' => 'Berichten met je team', 'text' => 'Een korte vraag stel je direct, zonder te bellen.'],
                ],
            ],

            // Tarieven-prijslijst → de projecttijdlijn zoals de klant die in het portaal ziet.
            'tarieven' => [
                'eyebrow' => 'Je project',
                'heading' => 'Zo verloopt je project in het portaal',
                'items'   => [
                    ['name' => 'Kick-off en inmeting', 'desc' => 'Afspraak ingepland, ontwerp en planning staan klaar in je omgeving.', 'price' => 'week 1'],
                    ['name' => 'Uitvoering', 'desc' => "Dagelijkse status en foto's van de voortgang.", 'price' => 'week 2-3'],
                    ['name' => 'Oplevering', 'desc' => 'Checklist, facturen en garantie in je omgeving.', 'price' => 'oplevering'],
                    ['name' => 'Nazorg', 'desc' => 'Vragen of service achteraf? Alles blijft bewaard.', 'price' => 'daarna'],
                ],
            ],

            // Werkwijze-stappen → hoe de eigen omgeving werkt.
            'werkwijze' => [
                'heading' => 'Zo werkt je eigen omgeving',
                'items'   => [
                    ['title' => 'Je krijgt een inlog', 'text' => 'Na je aanvraag zetten we een persoonlijke omgeving voor je klaar.'],
                    ['title' => 'Plan en volg', 'text' => 'Plan je afspraak, bekijk de planning en keur het ontwerp goed.'],
                    ['title' => 'Alles op één plek', 'text' => 'Van eerste ontwerp tot garantie vind je alles overzichtelijk terug.'],
                ],
            ],

            'galerij' => [
                'heading' => 'Je project in beeld',
            ],

            'reviews' => [
                'heading' => 'Wat klanten van de eigen omgeving vinden',
                'items'   => [
                    ['stars' => 5, 'text' => "Ik kon de afspraak 's avonds inplannen en zag elke dag foto's van de voortgang. Heel prettig.", 'author' => 'Karin uit Zeist'],
                    ['stars' => 5, 'text' => 'Alle facturen en het garantiebewijs netjes op één plek. Nooit meer zoeken in mijn mail.', 'author' => 'Johan uit Houten'],
                    ['stars' => 5, 'text' => 'Het 3D-ontwerp kon ik rustig thuis bekijken en goedkeuren. Dat scheelde een hoop overleg.', 'author' => 'Meike uit Bilthoven'],
                ],
            ],

            'faq' => [
                'heading' => 'Veelgestelde vragen over het klantenportaal',
                'items'   => [
                    ['q' => 'Heb ik een app nodig?', 'a' => 'Nee, je logt gewoon in via de website op je telefoon, tablet of computer.'],
                    ['q' => 'Kan ik zelf mijn afspraak verzetten?', 'a' => 'Ja, je verzet of annuleert een afspraak met één klik, zonder te bellen.'],
                    ['q' => 'Zie ik de voortgang van mijn badkamer?', 'a' => 'Je ziet de planning en per fase de status, vaak met foto\'s van de bouw.'],
                    ['q' => 'Blijven mijn documenten bewaard?', 'a' => 'Ja, offerte, facturen en garantiebewijs blijven ook na oplevering beschikbaar.'],
                ],
            ],

            'contact' => [
                'heading' => 'Vragen over je afspraak of project?',
                'sub'     => 'Log in voor je afspraken en documenten, of neem gerust contact op.',
            ],
        ];
    }
}
