<?php

namespace Database\Seeders;

use App\Models\Channel\Site;
use Illuminate\Database\Seeder;

/**
 * Zet de bespoke demo-sites (Salon Lumière, Brink Barbers) om van een losse blade
 * naar blok-gedreven + DB-content, zodat ze in de admin bewerkbaar zijn. De
 * bespoke look blijft via de per-site override-views (channels/_blocks/{key}/*).
 * Idempotent: een site die al blokken heeft wordt overgeslagen.
 */
class DemoBlocksSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->demos() as $key => $demo) {
            $site = Site::where('key', $key)->first();
            if (! $site || $site->blocks()->exists()) {
                continue;
            }

            $site->update([
                'header'      => $demo['header'],
                'legacy_view' => null,   // voortaan blok-gedreven
            ]);

            $sort = 0;
            foreach ($demo['blocks'] as $b) {
                $site->blocks()->create([
                    'type'      => $b['type'],
                    'block_key' => $b['type'],
                    'sort'      => $sort += 10,
                    'enabled'   => true,
                    'locked'    => ($b['type'] === 'wizard'),
                    'status'    => $b['status'] ?? 'klaar',
                    'content'   => $b['content'] ?? null,
                ]);
            }
        }
    }

    private function demos(): array
    {
        return [
            'dameskapper' => [
                'header' => [
                    'menu' => [
                        ['label' => 'Behandelingen', 'href' => '#behandelingen'],
                        ['label' => 'Galerij', 'href' => '#galerij'],
                        ['label' => 'Over ons', 'href' => '#over'],
                        ['label' => 'Contact', 'href' => '#contact'],
                    ],
                    'cta' => ['label' => 'Afspraak maken', 'href' => '#gratis-voorbeeld'],
                ],
                'blocks' => [
                    ['type' => 'hero', 'content' => [
                        'eyebrow'    => 'Dameskapper · Bussum',
                        'title'      => 'Jouw haar, met aandacht en oog voor detail',
                        'sub'        => 'Knippen, kleuren en balayage in een warme, persoonlijke salon. Maak in een paar tikken zelf online een afspraak — wanneer het jou uitkomt.',
                        'cta_label'  => 'Online afspraak maken',
                        'cta2_label' => 'Bekijk behandelingen',
                        'cta2_href'  => '#behandelingen',
                    ]],
                    ['type' => 'pricelist', 'content' => [
                        'eyebrow' => 'Behandelingen',
                        'heading' => 'Vakwerk voor elke gelegenheid',
                        'sub'     => 'Een greep uit ons aanbod. Twijfel je wat past? We adviseren je graag.',
                        'items'   => [
                            ['name' => 'Knippen & stylen', 'desc' => 'Wassen, knippen, föhnen of stylen', 'price' => '€ 39'],
                            ['name' => 'Kleuren', 'desc' => 'Uitgroei of volledige kleuring', 'price' => 'vanaf € 55'],
                            ['name' => 'Highlights / Balayage', 'desc' => 'Natuurlijke, zongekuste tinten', 'price' => 'vanaf € 95'],
                            ['name' => 'Föhnen & opsteken', 'desc' => 'Voor een gelegenheid of gewoon zin', 'price' => '€ 29'],
                            ['name' => 'Bruidskapsel', 'desc' => 'Inclusief proefkapsel op afspraak', 'price' => 'op aanvraag'],
                            ['name' => 'Knippen tot 12 jaar', 'desc' => 'Voor de kleintjes', 'price' => '€ 22'],
                        ],
                    ]],
                    ['type' => 'gallery', 'content' => [
                        'eyebrow' => 'Galerij',
                        'heading' => 'Een indruk van ons werk',
                        'sub'     => "Hier komen straks jouw eigen foto's — coupes, kleuren en opgestoken kapsels.",
                        'tiles'   => [
                            ['label' => 'Balayage'], ['label' => 'Coupe'], ['label' => 'Kleur'],
                            ['label' => 'Opsteken'], ['label' => 'Krullen'], ['label' => 'Bruid'],
                        ],
                    ]],
                    ['type' => 'about', 'content' => [
                        'eyebrow' => 'Over Salon Lumière',
                        'heading' => 'Een salon waar je tot rust komt',
                        'body'    => "Bij Salon Lumière nemen we de tijd voor je. We luisteren naar wat je wilt, denken mee en zorgen dat je met een goed gevoel de deur uitloopt — of het nu om een frisse coupe of een complete kleurmetamorfose gaat.\n\nOnze kapsters zijn gespecialiseerd in kleurtechnieken zoals balayage, en in stijlvolle opgestoken kapsels voor bruiloften en feesten.",
                        'stats'   => [
                            ['value' => '15+', 'label' => 'jaar ervaring'],
                            ['value' => '4,9', 'label' => 'gemiddelde review'],
                            ['value' => '3', 'label' => 'vaste kapsters'],
                        ],
                    ]],
                    ['type' => 'reviews', 'content' => [
                        'heading' => 'Wat klanten zeggen',
                        'items'   => [
                            ['stars' => 5, 'text' => 'Sinds jaren mijn vaste salon. Altijd een prachtige kleur en echt de tijd voor je.', 'author' => 'Marloes'],
                            ['stars' => 5, 'text' => 'De balayage is precies wat ik wilde — natuurlijk en stijlvol. Aanrader!', 'author' => 'Yvonne'],
                            ['stars' => 5, 'text' => 'Fijne sfeer, vakwerk en je kunt zó online een afspraak maken.', 'author' => 'Sanne'],
                        ],
                    ]],
                    ['type' => 'location', 'content' => [
                        'heading' => 'Maak je afspraak',
                        'sub'     => 'Boek eenvoudig online, of bel ons even. We zien je graag bij Salon Lumière.',
                        'hours'   => [
                            ['day' => 'Maandag', 'time' => 'Gesloten'],
                            ['day' => 'Dinsdag', 'time' => '09:00 – 18:00'],
                            ['day' => 'Woensdag', 'time' => '09:00 – 18:00'],
                            ['day' => 'Donderdag', 'time' => '09:00 – 21:00'],
                            ['day' => 'Vrijdag', 'time' => '09:00 – 18:00'],
                            ['day' => 'Zaterdag', 'time' => '08:30 – 16:00'],
                            ['day' => 'Zondag', 'time' => 'Gesloten'],
                        ],
                    ]],
                    ['type' => 'wizard'],
                ],
            ],
        ];
    }
}
