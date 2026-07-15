<?php

namespace Database\Seeders;

use App\Models\Channel\Branche;
use App\Models\Channel\Site;
use Illuminate\Database\Seeder;

/**
 * Marketing-flagshipsite voor TANDARTSEN ("Website voor je tandartspraktijk").
 *
 * Blok-gedreven + Groeidiamant-bewust: de meeste blokken staan op facet = null
 * (alle fases), maar per groeifase is er een EIGEN features-blok
 * (website|webshop|klantenportaal|automatisering|ai). Fase-exclusief: een pricing-
 * blok (alleen webshop) en een extra proof-quote (alleen ai). De hero past zijn
 * kop/subkop per fase aan via content.facets.{facet} (Block::c()).
 *
 * Idempotent: branche + site via updateOrCreate; blokken alleen als de site er nog
 * geen heeft. Zelfde patroon als ElektricienSiteSeeder.
 */
class TandartsSiteSeeder extends Seeder
{
    private const SITE_KEY    = 'tandarts';
    private const BRANCHE_KEY = 'tandarts';

    public function run(): void
    {
        $branche = Branche::updateOrCreate(['key' => self::BRANCHE_KEY], [
            'name'         => 'Tandarts',
            'lead_branche' => 'zorg',   // WebsiteLead::BRANCHES (Zorg / praktijk)
            'theme'        => $this->theme(),
            'active'       => true,
        ]);

        if ($branche->blueprintBlocks()->doesntExist()) {
            $sort = 0;
            foreach ($this->blueprint() as $b) {
                $branche->blueprintBlocks()->create([
                    'type'   => $b['type'],
                    'sort'   => $sort += 10,
                    'status' => $b['status'],
                    'locked' => $b['locked'],
                ]);
            }
        }

        $site = Site::updateOrCreate(['key' => self::SITE_KEY], [
            'channel_branche_id' => $branche->id,
            'name'   => 'TandartsWebsite',
            'status' => 'draft',
            'locale' => 'nl',
            'theme'  => null,
            'brand'  => [
                'logo_text' => 'Tandarts<span>Website</span>',
                'phone'     => '085 1303 600',
                'email'     => 'tandarts@betergeregeld.nl',
            ],
            'meta'   => [
                'home_title'       => 'Website voor je tandartspraktijk — meer nieuwe patiënten, minder telefoon',
                'home_description' => 'Word gevonden door patiënten die een tandarts zoeken, en laat ze zelf online een afspraak maken. Een rustige, vertrouwde praktijk-website die meegroeit van basissite tot webshop, patiëntportaal, automatisering en AI. Vooraf een gratis voorbeeld.',
                // Plaatsen-SEO: kop + per-stad sjabloonteksten (:city). De unieke intro
                // per stad wordt door de content-generator overschreven (AI).
                'places' => [
                    'h1'         => 'Tandartswebsites door heel Nederland',
                    'intro'      => 'In elke plaats van Nederland maken wij rustige, vindbare websites voor tandartspraktijken. Kies je plaats of vraag direct een gratis voorbeeld aan.',
                    'city_h1'    => 'Website voor je tandartspraktijk in :city',
                    'city_intro' => 'Ben je tandarts in :city? Wij maken een rustige, vindbare website voor je praktijk in :city, met online afspraken en vooraf een gratis voorbeeld.',
                    'service'    => 'tandartswebsite',
                ],
            ],
        ]);

        if ($site->blocks()->exists()) {
            return;
        }

        $sort = 0;
        foreach ($this->blocks() as $b) {
            $site->blocks()->create([
                'type'      => $b['type'],
                'facet'     => $b['facet'] ?? null,
                'block_key' => $b['key'],
                'sort'      => $sort += 10,
                'enabled'   => true,
                'locked'    => (bool) ($b['locked'] ?? false),
                'status'    => $b['status'] ?? 'klaar',
                'content'   => $b['content'] ?? null,
            ]);
        }
    }

    /** Clean, klinisch-vertrouwd thema: medisch blauw + fris mint-teal. */
    private function theme(): array
    {
        return [
            'primary'  => '#0284c7',   // medisch blauw
            'accent'   => '#22c1a6',   // fris mint-teal
            'ink'      => '#0f2b3a',
            'muted'    => '#5b7183',
            'bg'       => '#f4fafc',
            'surface'  => '#ffffff',
            'font'     => "'Manrope', system-ui, -apple-system, sans-serif",
            'font_url' => 'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap',
            'radius'   => '16px',
        ];
    }

    /** @return array<int,array{type:string,status:string,locked:bool}> */
    private function blueprint(): array
    {
        return [
            ['type' => 'hero',     'status' => 'klaar',       'locked' => false],
            ['type' => 'uspbar',   'status' => 'klaar',       'locked' => false],
            ['type' => 'groeipad', 'status' => 'klaar',       'locked' => false],
            ['type' => 'features', 'status' => 'placeholder', 'locked' => false],
            ['type' => 'gallery',  'status' => 'placeholder', 'locked' => false],
            ['type' => 'proof',    'status' => 'placeholder', 'locked' => false],
            ['type' => 'faq',      'status' => 'placeholder', 'locked' => false],
            ['type' => 'wizard',   'status' => 'klaar',       'locked' => true],
        ];
    }

    /** @return array<int,array<string,mixed>> */
    private function blocks(): array
    {
        return [
            // 1. HERO — altijd; kop/subkop wisselen per fase.
            [
                'type' => 'hero', 'key' => 'hero', 'facet' => null,
                'content' => [
                    'eyebrow'   => 'Voor tandartspraktijken',
                    'title'     => 'Nieuwe patiënten vinden je, en boeken zelf',
                    'sub'       => 'Patiënten kiezen de praktijk die ze vertrouwen en makkelijk kunnen bereiken. Met een rustige, professionele website sta jij vooraan.',
                    'cta_label' => 'Gratis voorbeeld aanvragen',
                    'note'      => 'Binnen 2 werkdagen een voorbeeld van jóuw praktijk. Geen verplichtingen.',
                    'usps'      => [
                        'Gevonden door patiënten in jouw regio',
                        'Zelf online een afspraak maken',
                        'Rust en vertrouwen vóór het eerste bezoek',
                    ],
                    'facets' => [
                        'webshop' => [
                            'eyebrow' => 'Tandarts + webshop',
                            'title'   => 'Verkoop mondzorg en cadeaubonnen online',
                            'sub'     => 'Van whitening-sets tot mondzorgproducten en cadeaubonnen — patiënten rekenen direct online af, gekoppeld aan je site.',
                        ],
                        'klantenportaal' => [
                            'eyebrow' => 'Tandarts + afspraken',
                            'title'   => 'Patiënten maken zelf hun afspraak',
                            'sub'     => 'Controle of behandeling? Patiënten kiezen zelf een moment in jullie agenda, met hun dossier en facturen op één plek. Minder telefoon aan de balie.',
                        ],
                        'automatisering' => [
                            'eyebrow' => 'Tandarts + automatisering',
                            'title'   => 'Herinneringen en recalls die vanzelf gaan',
                            'sub'     => 'Automatische afspraakherinneringen en halfjaarlijkse recall-oproepen — minder no-shows, een vollere agenda, geen handwerk.',
                        ],
                        'ai' => [
                            'eyebrow' => 'Tandarts + AI',
                            'title'   => 'Een slimme assistent die altijd opneemt',
                            'sub'     => 'AI die patiënten te woord staat, spoed herkent (kies, zwelling) en alvast de juiste vragen stelt — ook \'s avonds en in het weekend.',
                        ],
                    ],
                ],
            ],

            // 2. USP-balk — altijd.
            [
                'type' => 'uspbar', 'key' => 'uspbar', 'facet' => null,
                'content' => ['items' => [
                    ['icon' => '✅', 'text' => 'Gratis voorbeeld vooraf'],
                    ['icon' => '⚡', 'text' => 'Live binnen 2 weken'],
                    ['icon' => '📍', 'text' => 'Gevonden in jouw regio'],
                ]],
            ],

            // 3. GROEIDIAMANT-SELECTOR — altijd.
            ['type' => 'groeipad', 'key' => 'groeipad', 'facet' => null],

            // 4. FEATURES — één per fase.
            [
                'type' => 'features', 'key' => 'features-website', 'facet' => 'website',
                'content' => [
                    'heading' => 'Je professionele praktijk-website',
                    'sub'     => 'Alles wat een tandartspraktijk nodig heeft om gevonden en vertrouwd te worden.',
                    'items'   => [
                        ['icon' => '🔎', 'title' => 'Goed vindbaar',      'text' => 'Bovenaan in Google als iemand een tandarts zoekt in jouw regio.'],
                        ['icon' => '😌', 'title' => 'Rust & vertrouwen',   'text' => 'Een kalme, verzorgde uitstraling die de drempel voor het eerste bezoek verlaagt.'],
                        ['icon' => '🦷', 'title' => 'Je behandelingen',    'text' => 'Duidelijk overzicht van controle, mondhygiëne, kronen, implantaten en meer.'],
                        ['icon' => '📱', 'title' => 'Top op mobiel',       'text' => 'Snel en strak op elke telefoon — waar de meeste patiënten je vinden.'],
                    ],
                ],
            ],
            [
                'type' => 'features', 'key' => 'features-webshop', 'facet' => 'webshop',
                'content' => [
                    'heading' => 'Verkoop mondzorg online',
                    'sub'     => 'Extra service voor je patiënten, extra omzet voor de praktijk.',
                    'items'   => [
                        ['icon' => '🪥', 'title' => 'Mondzorgproducten', 'text' => 'Tandenborstels, ragers en pasta die je zelf aanraadt, direct te bestellen.'],
                        ['icon' => '✨', 'title' => 'Whitening-sets',    'text' => 'Verkoop je thuisbleek-behandelingen en nazorgproducten online.'],
                        ['icon' => '🎁', 'title' => 'Cadeaubonnen',      'text' => 'Laat patiënten een controle of bleekbehandeling cadeau geven.'],
                        ['icon' => '💳', 'title' => 'iDEAL & facturen',  'text' => 'Veilig betalen, automatische factuur, btw netjes geregeld.'],
                    ],
                ],
            ],
            [
                'type' => 'features', 'key' => 'features-klantenportaal', 'facet' => 'klantenportaal',
                'content' => [
                    'heading' => 'Patiënten regelen het zelf',
                    'sub'     => 'Minder telefoon aan de balie, meer overzicht — jullie agenda leidend.',
                    'items'   => [
                        ['icon' => '📅', 'title' => 'Online afspraken', 'text' => 'Patiënten kiezen zelf een moment uit jullie beschikbare tijden.'],
                        ['icon' => '🚨', 'title' => 'Spoed apart',      'text' => 'Spoedvragen (kies, zwelling, pijn) krijgen voorrang in de intake.'],
                        ['icon' => '🔔', 'title' => 'Herinneringen',    'text' => 'Minder no-shows door herinneringen per mail of sms.'],
                        ['icon' => '📂', 'title' => 'Patiëntportaal',   'text' => 'Afspraken, facturen en formulieren op één veilige plek.'],
                    ],
                ],
            ],
            [
                'type' => 'features', 'key' => 'features-automatisering', 'facet' => 'automatisering',
                'content' => [
                    'heading' => 'Laat je praktijk zichzelf herinneren',
                    'sub'     => 'Van recall tot factuur zonder handwerk.',
                    'items'   => [
                        ['icon' => '🔁', 'title' => 'Recall-oproepen', 'text' => 'Halfjaarlijkse controles worden automatisch opgeroepen.'],
                        ['icon' => '📨', 'title' => 'Afspraakflow',    'text' => 'Bevestiging, herinnering en nazorg gaan vanzelf de deur uit.'],
                        ['icon' => '🧾', 'title' => 'Facturatie',      'text' => 'Koppeling met je praktijksoftware — facturen automatisch.'],
                        ['icon' => '📊', 'title' => 'Vollere agenda',  'text' => 'Minder gaten en no-shows, meer stoeltijd benut.'],
                    ],
                ],
            ],
            [
                'type' => 'features', 'key' => 'features-ai', 'facet' => 'ai',
                'content' => [
                    'heading' => 'Slimme AI die altijd bereikbaar is',
                    'sub'     => 'Ook als de balie dicht is.',
                    'items'   => [
                        ['icon' => '🤖', 'title' => 'AI-telefoniste',  'text' => 'Neemt op, stelt de juiste vragen en plant of verwijst door.'],
                        ['icon' => '🦷', 'title' => 'Spoed-triage',    'text' => 'Herkent kies, zwelling of trauma en geeft de juiste urgentie.'],
                        ['icon' => '💬', 'title' => 'Chat op je site', 'text' => 'Beantwoordt veelgestelde vragen over kosten, verzekering en zorg.'],
                        ['icon' => '🧠', 'title' => 'Kent je praktijk','text' => 'Getraind op jullie behandelingen, tarieven en openingstijden.'],
                    ],
                ],
            ],

            // 5. PRICING — FASE-EXCLUSIEF: alleen in de webshop-fase.
            [
                'type' => 'pricing', 'key' => 'pricing-webshop', 'facet' => 'webshop',
                'content' => [
                    'heading' => 'Pakketten die je online aanbiedt',
                    'sub'     => 'Voorbeeld — helemaal naar jouw praktijk in te richten.',
                    'plans'   => [
                        ['name' => 'Mondzorg-box', 'price' => '€ 24', 'period' => '', 'features' => ['Borstel, pasta & ragers', 'Op advies van je mondhygiënist', 'Thuisbezorgd'], 'cta' => 'Voorbeeld aanvragen'],
                        ['name' => 'Whitening thuis', 'price' => '€ 149', 'period' => '', 'highlight' => true, 'features' => ['Bleekhoezen op maat', 'Professionele gel', 'Nazorgproducten'], 'cta' => 'Voorbeeld aanvragen'],
                        ['name' => 'Cadeaubon', 'price' => 'vrij bedrag', 'period' => '', 'features' => ['Controle of behandeling', 'Digitaal verstuurd', 'In te wisselen in de praktijk'], 'cta' => 'Voorbeeld aanvragen'],
                    ],
                ],
            ],

            // 6. GALLERY — "een kijkje in de praktijk" (bespoke view), altijd.
            [
                'type' => 'gallery', 'key' => 'gallery', 'facet' => null,
                'content' => [
                    'heading' => 'Een kijkje in de praktijk',
                    'sub'     => 'Een lichte, rustige praktijk waar patiënten zich op hun gemak voelen.',
                ],
            ],

            // 7. PROOF — altijd (algemeen).
            [
                'type' => 'proof', 'key' => 'proof', 'facet' => null,
                'content' => [
                    'quote'  => 'Nieuwe patiënten boeken nu zelf online in — de balie is veel rustiger en onze agenda voller.',
                    'author' => 'praktijkmanager, tandartspraktijk in Utrecht',
                ],
            ],

            // 8. PROOF — FASE-EXCLUSIEF: extra bewijs alleen in de AI-fase.
            [
                'type' => 'proof', 'key' => 'proof-ai', 'facet' => 'ai',
                'content' => [
                    'quote'  => 'De AI vangt \'s avonds de telefoon en herkent spoed meteen. Patiënten voelen zich gehoord, wij worden niet gestoord.',
                    'author' => 'tandarts in Zwolle',
                ],
            ],

            // 9. FAQ — altijd.
            [
                'type' => 'faq', 'key' => 'faq', 'facet' => null,
                'content' => [
                    'heading' => 'Veelgestelde vragen',
                    'items'   => [
                        ['q' => 'Wat kost een website voor mijn tandartspraktijk?', 'a' => 'Je krijgt vooraf een helder maandbedrag, zonder eenmalige bouwkosten en zonder verrassingen. Vraag een gratis voorbeeld aan, dan hoor je meteen wat het voor jouw praktijk kost.'],
                        ['q' => 'Werkt online afspraken maken samen met onze praktijksoftware?', 'a' => 'Ja. We koppelen waar mogelijk met je bestaande agenda/praktijksoftware, zodat afspraken op één plek samenkomen.'],
                        ['q' => 'Voldoet de site aan privacy (AVG)?', 'a' => 'Ja. Patiëntgegevens en formulieren verwerken we AVG-proof, met veilige opslag en verwerkersafspraken.'],
                        ['q' => 'Kan de site later uitbreiden?', 'a' => 'Ja. Je website groeit mee: van basissite naar webshop, patiëntportaal, automatisering en zelfs AI — stap voor stap, je hoeft nooit opnieuw te beginnen.'],
                    ],
                ],
            ],

            // 10. WIZARD — funnel, locked. Buiten de facet-zone.
            ['type' => 'wizard', 'key' => 'wizard', 'facet' => null, 'locked' => true],
        ];
    }
}
