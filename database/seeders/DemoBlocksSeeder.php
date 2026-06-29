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
                'theme'       => array_merge((array) $site->theme, $demo['theme'] ?? []),
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

            'herenkapper' => [
                // Donker thema: layout gebruikt --c-ink als tekstkleur → licht maken,
                // en de footer-achtergrond apart donker houden.
                'theme' => ['ink' => '#ece7df', 'footer_bg' => '#0e0c08'],
                'header' => [
                    'menu' => [
                        ['label' => 'Diensten', 'href' => '#diensten'],
                        ['label' => 'Galerij', 'href' => '#galerij'],
                        ['label' => 'Over', 'href' => '#over'],
                        ['label' => 'Contact', 'href' => '#contact'],
                    ],
                    'cta' => ['label' => 'Afspraak', 'href' => '#gratis-voorbeeld'],
                ],
                'blocks' => [
                    ['type' => 'hero', 'content' => [
                        'eyebrow'    => 'Barbershop · Bussum',
                        'title'      => 'Scherp geknipt, strak in de baard',
                        'sub'        => 'Klassiek vakmanschap, moderne coupes en scheren met het mes. Boek je stoel zo online — of loop binnen.',
                        'cta_label'  => 'Online afspraak',
                        'cta2_label' => 'Bekijk diensten',
                        'cta2_href'  => '#diensten',
                    ]],
                    ['type' => 'pricelist', 'content' => [
                        'eyebrow' => 'Diensten',
                        'heading' => 'Wat we voor je doen',
                        'sub'     => 'Vakwerk voor knippen, baard en scheren. Twijfel je? We adviseren je in de stoel.',
                        'items'   => [
                            ['name' => 'Knippen', 'desc' => 'Wassen, knippen en stylen', 'price' => '€ 27'],
                            ['name' => 'Knippen + baard', 'desc' => 'De complete beurt', 'price' => '€ 39'],
                            ['name' => 'Baard trimmen', 'desc' => 'Strak in model', 'price' => '€ 18'],
                            ['name' => 'Scheren met het mes', 'desc' => 'Klassiek, met hete handdoek', 'price' => '€ 30'],
                            ['name' => 'Contouren / snor', 'desc' => 'Even bijwerken', 'price' => '€ 12'],
                            ['name' => 'Knippen tot 12 jaar', 'desc' => 'Voor de jonge mannen', 'price' => '€ 20'],
                        ],
                    ]],
                    ['type' => 'gallery', 'content' => [
                        'eyebrow' => 'Galerij',
                        'heading' => 'Ons werk',
                        'sub'     => "Hier komen straks jouw eigen foto's — fades, baarden en classics.",
                        'tiles'   => [
                            ['label' => 'Fade'], ['label' => 'Baard'], ['label' => 'Classic'],
                            ['label' => 'Scheren'], ['label' => 'Crop'], ['label' => 'Pompadour'],
                        ],
                    ]],
                    ['type' => 'about', 'content' => [
                        'eyebrow' => 'Over Brink Barbers',
                        'heading' => 'Een barbershop met karakter',
                        'body'    => "Bij Brink Barbers draait alles om vakmanschap en een goeie sfeer. Of je nu komt voor een strakke fade, een nette baard of een klassieke scheerbeurt met het mes — we nemen de tijd en zorgen dat je er scherp uitloopt.\n\nOnze barbers zijn opgeleid in zowel klassieke als moderne technieken.",
                        'stats'   => [
                            ['value' => '10+', 'label' => 'jaar ervaring'],
                            ['value' => '4,9', 'label' => 'review'],
                            ['value' => '3', 'label' => 'barbers'],
                        ],
                    ]],
                    ['type' => 'reviews', 'content' => [
                        'heading' => 'Wat klanten zeggen',
                        'items'   => [
                            ['stars' => 5, 'text' => 'Strakke fade en een scheerbeurt met het mes. Echt vakwerk.', 'author' => 'Dave'],
                            ['stars' => 5, 'text' => 'Vaste zaak geworden. Goeie sfeer en je zit zo binnen via de app.', 'author' => 'Rachid'],
                            ['stars' => 5, 'text' => 'Mijn baard is nog nooit zo netjes geweest. Top.', 'author' => 'Mark'],
                        ],
                    ]],
                    ['type' => 'location', 'content' => [
                        'heading' => 'Boek je stoel',
                        'sub'     => 'Online boeken of even bellen — je bent welkom bij Brink Barbers.',
                        'hours'   => [
                            ['day' => 'Maandag', 'time' => 'Gesloten'],
                            ['day' => 'Dinsdag', 'time' => '09:00 – 18:00'],
                            ['day' => 'Woensdag', 'time' => '09:00 – 18:00'],
                            ['day' => 'Donderdag', 'time' => '09:00 – 21:00'],
                            ['day' => 'Vrijdag', 'time' => '09:00 – 18:00'],
                            ['day' => 'Zaterdag', 'time' => '08:00 – 16:00'],
                            ['day' => 'Zondag', 'time' => 'Gesloten'],
                        ],
                    ]],
                    ['type' => 'wizard'],
                ],
            ],
        ];
    }
}
