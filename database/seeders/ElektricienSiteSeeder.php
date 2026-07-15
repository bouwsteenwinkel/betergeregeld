<?php

namespace Database\Seeders;

use App\Models\Channel\Branche;
use App\Models\Channel\Site;
use Illuminate\Database\Seeder;

/**
 * Marketing-flagshipsite voor ELEKTRICIENS ("Website voor je elektriciensbedrijf").
 *
 * Blok-gedreven + Groeidiamant-bewust: de meeste blokken staan op facet = null
 * (zichtbaar in ALLE fases), maar per groeifase is er een EIGEN features-blok
 * (facet = website|webshop|klantenportaal|automatisering|ai) zodat elke fase zijn
 * eigen inhoud toont. Twee blokken zijn fase-exclusief: een pricing-blok (alleen
 * webshop) en een extra proof-quote (alleen ai). De hero past zijn kop/subkop per
 * fase aan via content.facets.{facet} (zie Block::c()).
 *
 * Idempotent: branche + site worden ge-updateOrCreate; blokken worden alleen
 * aangemaakt als de site er nog geen heeft (zo blijven designer-edits gespaard).
 */
class ElektricienSiteSeeder extends Seeder
{
    private const SITE_KEY    = 'elektricien';
    private const BRANCHE_KEY = 'elektricien';

    public function run(): void
    {
        $branche = Branche::updateOrCreate(['key' => self::BRANCHE_KEY], [
            'name'         => 'Elektricien',
            'lead_branche' => 'bouw_installatie',   // WebsiteLead::BRANCHES (loodgieter/elektra)
            'theme'        => $this->theme(),
            'active'       => true,
        ]);

        // Blueprint (standaard-blokkenlijst voor nieuwe sites onder deze branche).
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
            'name'   => 'ElektricienWebsite',
            'status' => 'draft',          // domein later in de admin + Plesk koppelen
            'locale' => 'nl',
            'theme'  => null,             // erft het branche-thema
            'brand'  => [
                'logo_text' => 'Elektricien<span>Website</span>',
                'phone'     => '085 1303 600',
                'email'     => 'elektricien@betergeregeld.nl',
            ],
            'meta'   => [
                'home_title'       => 'Website voor je elektriciensbedrijf — meer klussen, ook bij spoed',
                'home_description' => 'Word gevonden in Google als mensen een elektricien zoeken, ook \'s avonds en in het weekend. Een nette website die meegroeit van basissite tot webshop, online afspraken, automatisering en AI. Vooraf een gratis voorbeeld.',
                // Plaatsen-SEO: kop + per-stad sjabloonteksten (:city). De unieke intro
                // per stad wordt door de content-generator overschreven (AI).
                'places' => [
                    'h1'         => 'Elektricienswebsites door heel Nederland',
                    'intro'      => 'In elke plaats van Nederland maken wij nette, vindbare websites voor elektriciens. Kies je plaats of vraag direct een gratis voorbeeld aan.',
                    'city_h1'    => 'Website voor je elektriciensbedrijf in :city',
                    'city_intro' => 'Ben je elektricien in :city? Wij maken een nette website die je vindbaar maakt bij storing en spoed in :city, met vooraf een gratis voorbeeld.',
                    'service'    => 'elektricienswebsite',
                ],
            ],
        ]);

        if ($site->blocks()->exists()) {
            return;   // designer-edits sparen
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

    /** Electric thema: diepblauw + amber ("voltage"). */
    private function theme(): array
    {
        return [
            'primary'  => '#1e40af',   // diep elektrisch blauw
            'accent'   => '#f59e0b',   // amber / voltage
            'ink'      => '#0f172a',
            'muted'    => '#5b6472',
            'bg'       => '#f7f9fc',
            'surface'  => '#ffffff',
            'font'     => "'Manrope', system-ui, -apple-system, sans-serif",
            'font_url' => 'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap',
            'radius'   => '12px',
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
            ['type' => 'proof',    'status' => 'placeholder', 'locked' => false],
            ['type' => 'faq',      'status' => 'placeholder', 'locked' => false],
            ['type' => 'wizard',   'status' => 'klaar',       'locked' => true],
        ];
    }

    /**
     * De concrete blokset. facet = null → alle fases; facet = '<slug>' → alleen die
     * groeifase. Volgorde = sort (10, 20, …). De fase-features delen bewust dezelfde
     * plek in de flow (na de groeipad-selector): per fase toont er precies één.
     *
     * @return array<int,array<string,mixed>>
     */
    private function blocks(): array
    {
        return [
            // 1. HERO — altijd zichtbaar, maar kop/subkop wisselen per fase.
            [
                'type' => 'hero', 'key' => 'hero', 'facet' => null,
                'content' => [
                    'eyebrow'   => 'Voor elektriciens',
                    'title'     => 'Word gevonden, juist als er haast bij is',
                    'sub'       => 'Bij storing of spoed bellen mensen de eerste die ze vertrouwen. Met een nette website sta jij vooraan.',
                    'cta_label' => 'Gratis voorbeeld aanvragen',
                    'note'      => 'Binnen 2 werkdagen een voorbeeld van jóuw bedrijf. Geen verplichtingen.',
                    'usps'      => [
                        'Gevonden bij storing en spoed',
                        'Aanvragen via je telefoon, dag en nacht',
                        'Vertrouwen vóór ze bellen',
                    ],
                    // Per-fase overrides (Groeidiamant): alleen de tekst verandert.
                    'facets' => [
                        'webshop' => [
                            'eyebrow' => 'Elektricien + webshop',
                            'title'   => 'Verkoop materialen en servicepakketten online',
                            'sub'     => 'Laat klanten onderdelen, keuringen of onderhoudsabonnementen direct online afrekenen — gekoppeld aan je website.',
                        ],
                        'klantenportaal' => [
                            'eyebrow' => 'Elektricien + afspraken',
                            'title'   => 'Laat klanten zelf hun afspraak inplannen',
                            'sub'     => 'Een storing of onderhoudsbeurt? Klanten kiezen zelf een moment in jouw agenda — jij houdt overzicht, minder heen-en-weer gebel.',
                        ],
                        'automatisering' => [
                            'eyebrow' => 'Elektricien + automatisering',
                            'title'   => 'Laat je administratie zichzelf doen',
                            'sub'     => 'Aanvragen, agenda, offertes en facturen aan elkaar geknoopt — zodat jij op de klus staat, niet achter de computer.',
                        ],
                        'ai' => [
                            'eyebrow' => 'Elektricien + AI',
                            'title'   => 'Een slimme assistent die meewerkt',
                            'sub'     => 'AI die aanvragen te woord staat, spoed herkent en alvast een offerte-indicatie geeft — dag en nacht.',
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

            // 3. GROEIDIAMANT-SELECTOR — altijd; hiermee wisselt de bezoeker van fase.
            ['type' => 'groeipad', 'key' => 'groeipad', 'facet' => null],

            // 4. FEATURES — één per fase. Precies één is zichtbaar afhankelijk van de fase.
            [
                'type' => 'features', 'key' => 'features-website', 'facet' => 'website',
                'content' => [
                    'heading' => 'Je professionele basissite',
                    'sub'     => 'Alles wat een elektricien nodig heeft om gevonden en gebeld te worden.',
                    'items'   => [
                        ['icon' => '⚡', 'title' => 'Altijd vindbaar',    'text' => 'Bovenaan in Google als iemand een elektricien zoekt in jouw regio.'],
                        ['icon' => '📞', 'title' => 'Meer aanvragen',     'text' => 'Duidelijke belknop en aanvraagformulier, ook buiten kantoortijden.'],
                        ['icon' => '🛡️', 'title' => 'Vertrouwen vooraf',  'text' => 'Je diensten, werkgebied, keurmerken en reviews op één nette site.'],
                        ['icon' => '📱', 'title' => 'Top op mobiel',      'text' => 'Snel en strak op elke telefoon — waar de meeste spoedaanvragen vandaan komen.'],
                    ],
                ],
            ],
            [
                'type' => 'features', 'key' => 'features-webshop', 'facet' => 'webshop',
                'content' => [
                    'heading' => 'Verkoop online, naast je klussen',
                    'sub'     => 'Van losse materialen tot onderhoudsabonnementen — direct afrekenen op je eigen site.',
                    'items'   => [
                        ['icon' => '🛒', 'title' => 'Materialen & onderdelen', 'text' => 'Verkoop kabels, schakelmateriaal of laadpalen rechtstreeks online.'],
                        ['icon' => '🔁', 'title' => 'Onderhoudsabonnementen',  'text' => 'Terugkerende inkomsten via periodieke keuringen en checks.'],
                        ['icon' => '💳', 'title' => 'iDEAL & facturen',        'text' => 'Veilig betalen, automatische factuur en btw netjes geregeld.'],
                        ['icon' => '📦', 'title' => 'Voorraad in beeld',       'text' => 'Wat op is, is op — gekoppeld aan je website.'],
                    ],
                ],
            ],
            [
                'type' => 'features', 'key' => 'features-klantenportaal', 'facet' => 'klantenportaal',
                'content' => [
                    'heading' => 'Klanten plannen zelf hun afspraak',
                    'sub'     => 'Minder gebel, meer overzicht — jouw agenda leidend.',
                    'items'   => [
                        ['icon' => '📅', 'title' => 'Online agenda',        'text' => 'Klanten kiezen zelf een moment uit jouw beschikbare tijden.'],
                        ['icon' => '🚨', 'title' => 'Spoed vs. onderhoud',  'text' => 'Spoedaanvragen apart, zodat het echte spoed voorrang krijgt.'],
                        ['icon' => '🔔', 'title' => 'Automatische reminders','text' => 'Minder no-shows door herinneringen per mail of sms.'],
                        ['icon' => '📂', 'title' => 'Eigen klantomgeving',  'text' => 'Facturen, keuringsrapporten en historie op één plek.'],
                    ],
                ],
            ],
            [
                'type' => 'features', 'key' => 'features-automatisering', 'facet' => 'automatisering',
                'content' => [
                    'heading' => 'Koppel je processen aan elkaar',
                    'sub'     => 'Van aanvraag tot factuur zonder overtypen.',
                    'items'   => [
                        ['icon' => '🔗', 'title' => 'Aanvraag → agenda',    'text' => 'Elke aanvraag komt automatisch in je planning terecht.'],
                        ['icon' => '🧾', 'title' => 'Offerte & factuur',    'text' => 'Koppeling met je boekhouding — offertes en facturen automatisch.'],
                        ['icon' => '📊', 'title' => 'Overzicht & rapporten','text' => 'Zie in één oogopslag wat er binnenkomt en openstaat.'],
                        ['icon' => '⏱️', 'title' => 'Tijd terug',          'text' => 'Minder administratie, meer uren op de klus.'],
                    ],
                ],
            ],
            [
                'type' => 'features', 'key' => 'features-ai', 'facet' => 'ai',
                'content' => [
                    'heading' => 'Slimme AI die met je meewerkt',
                    'sub'     => 'Altijd bereikbaar, ook als jij op de ladder staat.',
                    'items'   => [
                        ['icon' => '🤖', 'title' => 'AI-telefoniste',      'text' => 'Neemt aanvragen aan, stelt de juiste vragen en herkent spoed.'],
                        ['icon' => '✍️', 'title' => 'Offerte-indicatie',   'text' => 'Geeft klanten alvast een richtprijs op basis van hun vraag.'],
                        ['icon' => '💬', 'title' => 'Chat op je site',     'text' => 'Beantwoordt veelgestelde vragen, dag en nacht.'],
                        ['icon' => '🧠', 'title' => 'Leert je vak',        'text' => 'Getraind op jouw diensten, werkgebied en tarieven.'],
                    ],
                ],
            ],

            // 5. PRICING — FASE-EXCLUSIEF: alleen in de webshop-fase.
            [
                'type' => 'pricing', 'key' => 'pricing-webshop', 'facet' => 'webshop',
                'content' => [
                    'heading' => 'Servicepakketten die je online verkoopt',
                    'sub'     => 'Voorbeeld — helemaal naar jouw diensten in te richten.',
                    'plans'   => [
                        ['name' => 'Los materiaal', 'price' => 'vanaf € 0', 'period' => '', 'features' => ['Onderdelen & kabels', 'iDEAL-betaling', 'Automatische factuur'], 'cta' => 'Voorbeeld aanvragen'],
                        ['name' => 'Onderhoud', 'price' => '€ 12', 'period' => '/mnd', 'highlight' => true, 'features' => ['Jaarlijkse keuring', 'Voorrang bij storing', 'Korting op materiaal'], 'cta' => 'Voorbeeld aanvragen'],
                        ['name' => 'Zakelijk', 'price' => 'op maat', 'period' => '', 'features' => ['Meerdere locaties', 'Periodieke inspectie', 'Vaste aanspreekpartner'], 'cta' => 'Voorbeeld aanvragen'],
                    ],
                ],
            ],

            // 6. GALLERY — "ons werk in beeld" (bespoke view met gegenereerde beelden), altijd.
            [
                'type' => 'gallery', 'key' => 'gallery', 'facet' => null,
                'content' => [
                    'heading' => 'Ons werk in beeld',
                    'sub'     => 'Van groepenkast tot laadpaal — een indruk van waar we goed in zijn.',
                ],
            ],

            // 7. PROOF — altijd zichtbaar (algemeen).
            [
                'type' => 'proof', 'key' => 'proof', 'facet' => null,
                'content' => [
                    'quote'  => 'Eerste week al drie spoedklussen via de site — voorheen liep dat allemaal langs me heen.',
                    'author' => 'elektricien in Amersfoort',
                ],
            ],

            // 7. PROOF — FASE-EXCLUSIEF: extra bewijs alleen in de AI-fase.
            [
                'type' => 'proof', 'key' => 'proof-ai', 'facet' => 'ai',
                'content' => [
                    'quote'  => 'De AI vangt \'s avonds de telefoon op en zet spoed meteen door. Ik mis geen klus meer.',
                    'author' => 'installatiebedrijf in Zwolle',
                ],
            ],

            // 8. FAQ — altijd.
            [
                'type' => 'faq', 'key' => 'faq', 'facet' => null,
                'content' => [
                    'heading' => 'Veelgestelde vragen',
                    'items'   => [
                        ['q' => 'Wat kost een website voor mijn elektriciensbedrijf?', 'a' => 'Je krijgt vooraf een helder maandbedrag, zonder eenmalige bouwkosten en zonder verrassingen. Vraag een gratis voorbeeld aan, dan hoor je meteen wat het voor jouw bedrijf kost.'],
                        ['q' => 'Hoe snel sta ik online?', 'a' => 'Meestal binnen twee weken na akkoord. Wij regelen techniek, hosting en de vindbaarheid in Google.'],
                        ['q' => 'Moet ik technisch zijn?', 'a' => 'Nee — wij regelen alles. Je beheert zelf alleen de inhoud als je dat wilt.'],
                        ['q' => 'Kan de site later uitbreiden?', 'a' => 'Ja. Je website groeit mee: van basissite naar webshop, online afspraken, automatisering en zelfs AI — stap voor stap, je hoeft nooit opnieuw te beginnen.'],
                    ],
                ],
            ],

            // 9. WIZARD — funnel, verplicht/locked. Staat buiten de facet-zone.
            ['type' => 'wizard', 'key' => 'wizard', 'facet' => null, 'locked' => true],
        ];
    }
}
