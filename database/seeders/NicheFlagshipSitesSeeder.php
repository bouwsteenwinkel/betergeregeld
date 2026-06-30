<?php

namespace Database\Seeders;

use App\Models\Channel\Branche;
use App\Models\Channel\Site;
use Illuminate\Database\Seeder;

/**
 * Maakt een marketing-flagshipsite per niche zonder site (barbier, nagelsalon,
 * schoonheidssalon). Conversie-gerichte blokset (hero → trust-balk → features →
 * stappen → reviews → FAQ → groeipad → wizard), thema geërfd van de niche-branche.
 * Domein blijft leeg → in de admin invullen + in Plesk koppelen. Idempotent.
 */
class NicheFlagshipSitesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->flagships() as $key => $f) {
            $branche = Branche::where('key', $f['branche'])->first();
            if (! $branche) {
                continue;
            }

            $site = Site::updateOrCreate(['key' => $key], [
                'channel_branche_id' => $branche->id,
                'name'   => $f['name'],
                'status' => 'draft',
                'locale' => 'nl',
                'theme'  => null,          // erft het niche-thema
                'brand'  => ['logo_text' => $f['name']],
                'meta'   => $f['meta'],
            ]);

            if ($site->blocks()->exists()) {
                continue;   // designer-edits sparen
            }

            $sort = 0;
            foreach ($this->blocks($f) as $b) {
                $site->blocks()->create([
                    'type'      => $b['type'],
                    'block_key' => $b['type'],
                    'sort'      => $sort += 10,
                    'enabled'   => true,
                    'locked'    => ($b['type'] === 'wizard'),
                    'status'    => $b['type'] === 'wizard' || $b['type'] === 'groeipad' ? 'klaar' : 'placeholder',
                    'content'   => $b['content'] ?? null,
                ]);
            }
        }
    }

    /** Geordende blokken met content voor een flagship. */
    private function blocks(array $f): array
    {
        return [
            ['type' => 'hero', 'content' => $f['hero']],
            ['type' => 'uspbar', 'content' => ['items' => [
                ['icon' => '✅', 'text' => 'Gratis voorbeeld vooraf'],
                ['icon' => '⚡', 'text' => 'Live binnen 2 weken'],
                ['icon' => '📍', 'text' => 'Gevonden in jouw stad'],
            ]]],
            ['type' => 'features', 'content' => [
                'heading' => 'Alles wat je zaak nodig heeft',
                'sub'     => 'In één strakke website — wij regelen de techniek.',
                'items'   => $f['features'],
            ]],
            ['type' => 'steps', 'content' => [
                'heading' => 'Zo werkt het',
                'items'   => [
                    ['title' => 'Gratis voorbeeld', 'text' => 'We zetten vooraf een voorbeeld van jouw zaak klaar.'],
                    ['title' => 'Samen afstemmen', 'text' => 'In één gesprek scherpen we het aan.'],
                    ['title' => 'Live binnen 2 weken', 'text' => 'Wij regelen techniek, hosting en vindbaarheid.'],
                ],
            ]],
            ['type' => 'reviews', 'content' => [
                'heading' => 'Wat ondernemers zeggen',
                'items'   => [
                    ['stars' => 5, 'text' => 'Binnen twee weken live en het ziet er top uit.', 'author' => 'ondernemer'],
                    ['stars' => 5, 'text' => 'Klanten boeken nu zelf online — scheelt me veel bellen.', 'author' => 'salonhouder'],
                    ['stars' => 5, 'text' => 'Fijne, snelle samenwerking. Aanrader.', 'author' => 'eigenaar'],
                ],
            ]],
            ['type' => 'faq', 'content' => [
                'heading' => 'Veelgestelde vragen',
                'items'   => [
                    ['q' => 'Wat kost het?', 'a' => 'Je krijgt vooraf een helder voorstel, zonder verrassingen achteraf.'],
                    ['q' => 'Hoe snel ben ik live?', 'a' => 'Meestal binnen twee weken na akkoord.'],
                    ['q' => 'Moet ik technisch zijn?', 'a' => 'Nee — wij regelen techniek, hosting en vindbaarheid. Je beheert zelf alleen de inhoud als je wilt.'],
                    ['q' => 'Kan ik later uitbreiden?', 'a' => 'Ja. Je website groeit mee: van website naar webshop, klantenportaal en zelfs AI — stap voor stap.'],
                ],
            ]],
            ['type' => 'groeipad'],
            ['type' => 'wizard'],
        ];
    }

    /** @return array<string,array> */
    private function flagships(): array
    {
        return [
            'barbersites' => [
                'name'    => 'BarberSites',
                'branche' => 'barbier',
                'meta'    => ['home_title' => 'Barbershop-website laten maken — meer klanten in je stoel', 'home_description' => 'Strakke websites voor barbershops: online boeken, je prijzen en je werk. Goed vindbaar in Google. Vooraf een gratis voorbeeld.'],
                'hero'    => [
                    'eyebrow'   => 'Voor barbershops',
                    'title'     => 'Een barbershop-website die je stoelen vult',
                    'sub'       => 'Online boeken, je prijzen en je werk — strak en goed vindbaar in Google.',
                    'cta_label' => 'Gratis voorbeeld aanvragen',
                    'note'      => 'Binnen 2 werkdagen een voorbeeld van jóuw shop. Vrijblijvend.',
                ],
                'features' => [
                    ['icon' => '✂️', 'title' => 'Online boeken & walk-in', 'text' => 'Klanten plannen zelf, of lopen gewoon binnen.'],
                    ['icon' => '🧔', 'title' => 'Baard & scheren', 'text' => 'Laat je specialiteiten goed zien.'],
                    ['icon' => '📸', 'title' => 'Galerij', 'text' => 'Je fades en classics in beeld.'],
                    ['icon' => '📍', 'title' => 'Lokaal vindbaar', 'text' => 'Gevonden op "barbier in jouw stad".'],
                ],
            ],
            'nagelsites' => [
                'name'    => 'NagelSites',
                'branche' => 'nagelsalon',
                'meta'    => ['home_title' => 'Nagelstudio-website laten maken — vol geboekt, online', 'home_description' => 'Stijlvolle websites voor nagelstudio\'s: online afspraken, behandelingen en een galerij van je nailart. Vooraf een gratis voorbeeld.'],
                'hero'    => [
                    'eyebrow'   => 'Voor nagelstudio\'s',
                    'title'     => 'Een nagelstudio-website die je agenda vult',
                    'sub'       => 'Online afspraken, je behandelingen en een galerij van je nailart — strak en vindbaar.',
                    'cta_label' => 'Gratis voorbeeld aanvragen',
                    'note'      => 'Binnen 2 werkdagen een voorbeeld van jóuw studio. Vrijblijvend.',
                ],
                'features' => [
                    ['icon' => '💅', 'title' => 'Online afspraken', 'text' => 'Klanten boeken zelf, dag en nacht.'],
                    ['icon' => '🎨', 'title' => 'Galerij', 'text' => 'Je mooiste nailart in beeld.'],
                    ['icon' => '📋', 'title' => 'Behandelingen & prijzen', 'text' => 'Overzichtelijk en altijd actueel.'],
                    ['icon' => '⭐', 'title' => 'Reviews', 'text' => 'Bouw vertrouwen met beoordelingen.'],
                ],
            ],
            'beautysites' => [
                'name'    => 'BeautySites',
                'branche' => 'schoonheidssalon',
                'meta'    => ['home_title' => 'Website voor je schoonheidssalon laten maken', 'home_description' => 'Rustige, vertrouwde websites voor schoonheidssalons: online afspraken en je behandelingen. Vooraf een gratis voorbeeld.'],
                'hero'    => [
                    'eyebrow'   => 'Voor schoonheidssalons',
                    'title'     => 'Een salon-website die nieuwe klanten brengt',
                    'sub'       => 'Online afspraken, je behandelingen en een rustige, vertrouwde uitstraling.',
                    'cta_label' => 'Gratis voorbeeld aanvragen',
                    'note'      => 'Binnen 2 werkdagen een voorbeeld van jóuw salon. Vrijblijvend.',
                ],
                'features' => [
                    ['icon' => '🧖', 'title' => 'Online afspraken', 'text' => 'Klanten boeken zelf, 24/7.'],
                    ['icon' => '💆', 'title' => 'Behandelingen', 'text' => 'Helder overzicht van je aanbod.'],
                    ['icon' => '🌿', 'title' => 'Rustige uitstraling', 'text' => 'Een site die de sfeer van je salon ademt.'],
                    ['icon' => '⭐', 'title' => 'Reviews', 'text' => 'Bouw vertrouwen met beoordelingen.'],
                ],
            ],
        ];
    }
}
