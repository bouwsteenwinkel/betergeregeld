<?php

namespace Database\Seeders;

use App\Models\Channel\Branche;
use App\Models\Channel\Site;
use Illuminate\Database\Seeder;

/**
 * Generieke "bedrijfswebsite"-channel (jouw-bedrijfswebsite.nl): een branche-neutrale
 * versie van de niche-sites die voor élk mkb-bedrijf werkt. Zelfde blok-structuur en
 * funnel als bijv. bakkerij, maar met neutrale content. Beelden levert Dennis aan in
 * public/channel-media/bedrijfswebsite/ (site-key = image-fallbackpad). Idempotent:
 * bestaande blokken blijven staan (designer-edits sparen).
 */
class BedrijfswebsiteSiteSeeder extends Seeder
{
    public function run(): void
    {
        $branche = Branche::updateOrCreate(['key' => 'bedrijfswebsite'], [
            'name'         => 'Bedrijfswebsite',
            'lead_branche' => 'zakelijke-dienstverlening',
            'active'       => true,
            'theme'        => [
                'primary'      => '#16324f',
                'accent'       => '#ef6033',
                'cta'          => '#1685c4',   // diamant-blauw (Groeidiamant), primaire CTA
                'on_cta'       => '#ffffff',
                'ink'          => '#1c2530',
                'muted'        => '#5b6b78',
                'bg'           => '#f7f9fb',
                'surface'      => '#ffffff',
                'radius'       => '12px',
                'font'         => "'Inter', system-ui, sans-serif",
                'font_display' => "'Sora', system-ui, sans-serif",
                'font_url'     => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sora:wght@600;700;800&display=swap',
            ],
            'blueprint' => array_map(
                fn ($t) => ['type' => $t['type'], 'status' => $t['status'], 'locked' => $t['locked']],
                $this->blockSpecs(),
            ),
            'places' => [
                'trade'   => 'bedrijf',
                'trades'  => 'bedrijven',
                'niche'   => 'bedrijf',
                'niches'  => 'bedrijven',
                'service' => 'website',
                // Index-pagina (/plaatsen): owner-pitch, net als badkamer.
                'h1'      => 'Bedrijven door heel Nederland online laten groeien',
                'intro'   => 'Heb je een bedrijf en wil je meer klanten binnenhalen? We helpen ondernemers door heel Nederland aan een website, webshop en slimme tools die aanvragen opleveren. Kies je plaats en zie wat we in jouw regio doen.',
                'business' => [
                    'label'       => 'Bedrijven in :city',
                    'intro'       => 'Zo ziet het ondernemerslandschap in :city en omgeving eruit. Zorg dat jouw bedrijf hiertussen opvalt met een sterke website en goede vindbaarheid.',
                    'query'       => 'bedrijf in :city',
                    'limit'       => 8,
                    'min_reviews' => 3,
                ],
            ],
        ]);

        $site = Site::updateOrCreate(['key' => 'bedrijfswebsite'], [
            'channel_branche_id' => $branche->id,
            'name'    => 'Bedrijfswebsite',
            'domain'  => 'jouw-bedrijfswebsite.nl',
            'status'  => 'draft',   // preview via /_site/bedrijfswebsite; live zetten via channel:go-live
            'locale'  => 'nl',
            'theme'   => null,      // erft het branche-thema
            'brand'   => [
                'logo_text'    => 'Jouw Website',
                'logo_tagline' => 'Website, webshop en klantenportaal voor jouw bedrijf',
                'trustline'    => 'Betrouwbaar vakwerk · 4,8 ★',
                'logo_image'   => 'channel-media/bedrijfswebsite/logo.webp',
                'footer_logo'  => '/channel-media/bedrijfswebsite/logo.webp',
            ],
            'meta'    => [
                'home_title'       => 'Jouw Website | Professionele website voor jouw bedrijf',
                'home_description' => 'Een strakke website, webshop of klantenportaal die klanten binnenhaalt terwijl jij aan het werk bent. Vraag een gratis voorbeeld aan met jouw bedrijfsnaam.',
            ],
            'header'  => [
                'pitch_strip' => false,   // geen pitch-strip boven de header op dit kanaal
            ],
        ]);

        if ($site->blocks()->exists()) {
            $this->command?->info('bedrijfswebsite: blokken bestaan al, overgeslagen.');
            return;
        }

        foreach ($this->blockSpecs() as $b) {
            $site->blocks()->create([
                'type'      => $b['type'],
                'block_key' => $b['block_key'],
                'facet'     => $b['facet'] ?? null,   // leeg = altijd tonen; anders alleen onder die groeistap
                'sort'      => $b['sort'],
                'enabled'   => true,
                'locked'    => $b['locked'],
                'status'    => $b['status'],
                'content'   => $b['content'] ?? null,
            ]);
        }

        $this->command?->info('bedrijfswebsite: branche + site + ' . count($this->blockSpecs()) . ' blokken aangemaakt.');
    }

    /** Geordende blokken met neutrale mkb-content (spiegelt de bakkerij-structuur). */
    private function blockSpecs(): array
    {
        return [
            ['type' => 'hero', 'block_key' => 'hero', 'sort' => 10, 'locked' => false, 'status' => 'placeholder', 'content' => [
                'eyebrow'    => 'Voor ondernemers en mkb',
                'title'      => 'Meer klanten en omzet uit je eigen regio',
                'sub'        => 'Wij bouwen websites, webshops en klantenportalen die nieuwe klanten binnenhalen terwijl jij aan het werk bent. Jij bepaalt hoe ver je gaat.',
                'cta_label'  => 'Gratis voorbeeld aanvragen',
                'cta2_label' => 'Bekijk diensten',
                'cta2_href'  => '#diensten',
                'note'       => 'Gratis voorbeeldwebsite met jouw bedrijfsnaam, vaak binnen 1 à 2 dagen.',
            ]],
            ['type' => 'uspbar', 'block_key' => 'usps', 'sort' => 20, 'locked' => false, 'status' => 'placeholder', 'content' => [
                'items' => [
                    ['icon' => '🌐', 'text' => 'Gevonden worden als iemand jouw dienst zoekt in de regio'],
                    ['icon' => '📩', 'text' => 'Offerte- en contactaanvragen rechtstreeks in je mailbox'],
                    ['icon' => '🚀', 'text' => 'Begin klein en breid later uit, je hoeft nooit opnieuw te beginnen'],
                ],
            ]],
            ['type' => 'features', 'block_key' => 'diensten-grid', 'sort' => 30, 'locked' => false, 'status' => 'placeholder', 'content' => [
                'heading' => 'Wat wij voor jouw bedrijf bouwen',
                'sub'     => 'Een site die past bij jouw vak. Overzichtelijk, snel en met beeld dat je werk eer aandoet.',
                'items'   => [
                    ['icon' => '💻', 'title' => 'Website', 'text' => 'Een strakke, snelle website met jouw diensten, werk en contactgegevens. Goed vindbaar in Google.'],
                    ['icon' => '🛒', 'title' => 'Webshop', 'text' => 'Verkoop producten of diensten online met iDEAL en Apple Pay. Van één product tot een compleet assortiment.'],
                    ['icon' => '🔐', 'title' => 'Klantenportaal', 'text' => 'Klanten regelen zelf hun offertes, afspraken en documenten in een eigen beveiligde omgeving.'],
                    ['icon' => '🎨', 'title' => 'Beeld en huisstijl', 'text' => 'Nette fotografie en een herkenbare huisstijl die vertrouwen wekt bij nieuwe klanten.'],
                ],
            ]],
            ['type' => 'features', 'block_key' => 'webshop-uitleg', 'facet' => 'webshop', 'sort' => 34, 'locked' => false, 'status' => 'placeholder', 'content' => [
                'heading' => 'Verkoop online, ook als je winkel of kantoor dicht is',
                'sub'     => 'Van een enkel product tot een compleet assortiment of servicepakket. Klanten bestellen en betalen zelf, jij levert.',
                'items'   => [
                    ['icon' => '🛒', 'title' => 'Producten en diensten', 'text' => 'Zet je aanbod online met foto, prijs en voorraad. Van één artikel tot een volledige catalogus.'],
                    ['icon' => '💳', 'title' => 'Direct betaald', 'text' => 'Klanten rekenen meteen af met iDEAL, Apple Pay of creditcard. Geen facturen achteraf najagen.'],
                    ['icon' => '🎟️', 'title' => 'Bonnen en pakketten', 'text' => 'Verkoop cadeaubonnen, strippenkaarten of servicepakketten die klanten zelf inwisselen.'],
                    ['icon' => '📦', 'title' => 'Verzenden of afhalen', 'text' => 'De klant kiest verzending of afhalen op een gekozen moment. Jij krijgt een duidelijke orderbon.'],
                ],
            ]],
            ['type' => 'pricelist', 'block_key' => 'webshop-producten', 'facet' => 'webshop', 'sort' => 38, 'locked' => false, 'status' => 'placeholder', 'content' => [
                'eyebrow' => 'Webshop',
                'heading' => 'Populaire producten',
                'items'   => [
                    ['name' => 'Bestverkochte product', 'desc' => 'Je populairste artikel met foto, voorraad en directe betaling via iDEAL. Klaar om te verzenden of af te halen.', 'price' => '€ 49,95'],
                    ['name' => 'Servicepakket', 'desc' => 'Een terugkerende dienst als vast pakket, bijvoorbeeld onderhoud of een strippenkaart voor tien beurten.', 'price' => '€ 149'],
                    ['name' => 'Cadeaubon', 'desc' => 'Laat klanten een tegoedbon kopen die ze zelf gebruiken of cadeau geven. Direct per e-mail geleverd.', 'price' => '€ 25'],
                ],
            ]],
            ['type' => 'pricelist', 'block_key' => 'tarieven', 'sort' => 40, 'locked' => false, 'status' => 'placeholder', 'content' => [
                'eyebrow' => 'Tarieven',
                'heading' => 'Prijsindicatie',
                'items'   => [
                    ['name' => 'Etalage', 'desc' => 'Nette website met je diensten, werk en contact. Ideaal om online gevonden en vertrouwd te worden.', 'price' => 'vanaf € 895'],
                    ['name' => 'Groeisite', 'desc' => 'Website met online aanvragen, afspraken plannen en iDEAL. Voor bedrijven die leads willen binnenhalen.', 'price' => 'vanaf € 1.750'],
                    ['name' => 'Compleet', 'desc' => 'Webshop of klantenportaal met betalingen, koppelingen met je systemen en automatische administratie.', 'price' => 'op aanvraag'],
                ],
            ]],
            ['type' => 'features', 'block_key' => 'afspraken-voordeel', 'facet' => 'klantenportaal', 'sort' => 45, 'locked' => false, 'status' => 'placeholder', 'content' => [
                'heading' => 'Afspraken en aanvragen slim inplannen',
                'sub'     => 'Verdeel je werk over de week en houd overzicht. Klanten kiezen zelf een moment dat past.',
                'items'   => [
                    ['icon' => '⏰', 'title' => 'Tijdvakken', 'text' => 'Bied afspraken aan in blokken die passen bij jouw planning, met een maximum per dag.'],
                    ['icon' => '🗓️', 'title' => 'Online agenda', 'text' => 'Beschikbaarheid synchroniseert met je eigen agenda, zodat dubbele boekingen niet kunnen.'],
                    ['icon' => '🔔', 'title' => 'Herinneringen', 'text' => 'Automatische herinnering per mail of sms een dag voor de afspraak. Minder no-shows.'],
                    ['icon' => '🚫', 'title' => 'Maximum per dag', 'text' => 'Op drukke dagen sluit je vanzelf zodra het maximum bereikt is, zodat je agenda niet overvol raakt.'],
                ],
            ]],
            ['type' => 'steps', 'block_key' => 'werkwijze', 'sort' => 50, 'locked' => false, 'status' => 'placeholder', 'content' => [
                'heading' => 'Zo werkt het',
                'items'   => [
                    ['title' => 'Aanvraag', 'text' => 'Je vraagt een gratis voorbeeld aan. We kijken naar je huidige site, je diensten en je concurrenten in de omgeving.'],
                    ['title' => 'Voorbeeld', 'text' => 'Binnen 1 à 2 dagen zie je een demo met jouw bedrijfsnaam, kleuren en echte inhoud. Zo weet je precies wat je krijgt.'],
                    ['title' => 'Live', 'text' => 'Ben je enthousiast, dan maken we het af, koppelen we betalingen en gaat de site live. Meestal binnen twee weken.'],
                ],
            ]],
            ['type' => 'features', 'block_key' => 'auto-uitleg', 'facet' => 'automatisering', 'sort' => 55, 'locked' => false, 'status' => 'placeholder', 'content' => [
                'heading' => 'Automatiseer je administratie',
                'sub'     => 'Jij bent ondernemer, geen boekhouder. Wij zetten repeterend werk op de automatische piloot.',
                'items'   => [
                    ['icon' => '🧾', 'title' => 'Offertes en facturen', 'text' => 'Vanuit een aanvraag rolt automatisch een nette offerte of factuur met btw, op naam en met je huisstijl.'],
                    ['icon' => '📨', 'title' => 'Klantberichten', 'text' => 'Bevestigingen, herinneringen en een bedankje na afloop, automatisch verstuurd in je eigen stijl.'],
                    ['icon' => '🔗', 'title' => 'Koppelingen', 'text' => 'Koppel je boekhouding, agenda of kassa, zodat gegevens niet dubbel ingevoerd hoeven te worden.'],
                    ['icon' => '📊', 'title' => 'Overzicht', 'text' => 'Een dagoverzicht van aanvragen, afspraken en omzet in één helder scherm.'],
                ],
            ]],
            ['type' => 'cta', 'block_key' => 'auto-cta', 'facet' => 'automatisering', 'sort' => 58, 'locked' => false, 'status' => 'placeholder', 'content' => [
                'title'     => 'Minder administratie, meer overzicht',
                'sub'       => 'We laten in je gratis voorbeeld zien hoe een aanvraag, offerte en factuur er voor jouw bedrijf uitzien.',
                'cta_label' => 'Gratis voorbeeld aanvragen',
            ]],
            ['type' => 'gallery', 'block_key' => 'galerij', 'sort' => 60, 'locked' => false, 'status' => 'placeholder', 'content' => [
                'heading' => 'Een indruk van ons werk',
            ]],
            ['type' => 'features', 'block_key' => 'ai-uitleg', 'facet' => 'ai', 'sort' => 62, 'locked' => false, 'status' => 'placeholder', 'content' => [
                'heading' => 'AI voor jouw bedrijf',
                'sub'     => 'Concrete inzet van AI, geen fratsen. Voor als je handen vol zitten en de telefoon toch gaat.',
                'items'   => [
                    ['icon' => '📞', 'title' => 'Telefoon-assistent', 'text' => 'Een AI-assistent neemt op als jij aan het werk bent en beantwoordt vragen over diensten en openingstijden.'],
                    ['icon' => '📝', 'title' => 'Offertes opstellen', 'text' => 'AI helpt bij het opstellen van een offerte op basis van wat de klant doorgeeft. Jij controleert en verstuurt.'],
                    ['icon' => '💬', 'title' => 'Website-chat', 'text' => 'Een chat op je site beantwoordt veelgestelde vragen direct, dag en nacht.'],
                    ['icon' => '✍️', 'title' => 'Teksten schrijven', 'text' => 'Automatische teksten voor je website, nieuwsbrief en social media, in je eigen stijl.'],
                ],
            ]],
            ['type' => 'cta', 'block_key' => 'ai-cta', 'facet' => 'ai', 'sort' => 66, 'locked' => false, 'status' => 'placeholder', 'content' => [
                'title'     => 'Laat de telefoon aan, ook als je aan het werk bent',
                'sub'       => 'Vraag een demo aan en hoor zelf hoe de AI-assistent jouw bedrijf aan de lijn te woord staat.',
                'cta_label' => 'Gratis voorbeeld aanvragen',
            ]],
            ['type' => 'reviews', 'block_key' => 'reviews', 'sort' => 70, 'locked' => false, 'status' => 'placeholder', 'content' => [
                'heading' => 'Wat klanten zeggen',
                'items'   => [
                    ['stars' => 5, 'text' => 'Sinds de nieuwe site komen aanvragen netjes via het formulier binnen. Veel minder heen-en-weer gebel.', 'author' => 'Mark, installatiebedrijf'],
                    ['stars' => 5, 'text' => 'Klanten plannen nu zelf hun afspraak in. Scheelt me elke week uren telefoneren.', 'author' => 'Sanne, adviespraktijk'],
                    ['stars' => 5, 'text' => 'We worden eindelijk goed gevonden in onze eigen regio. De offertes stromen binnen.', 'author' => 'Youssef, hoveniersbedrijf'],
                ],
            ]],
            ['type' => 'faq', 'block_key' => 'faq', 'sort' => 80, 'locked' => false, 'status' => 'placeholder', 'content' => [
                'heading' => 'Veelgestelde vragen',
                'items'   => [
                    ['q' => 'Kan ik zelf teksten en foto’s aanpassen?', 'a' => 'Ja. Je logt in en past teksten, prijzen en afbeeldingen aan. Handig als je iets wilt bijwerken of een dienst toevoegt.'],
                    ['q' => 'Wat kost het en zit ik ergens aan vast?', 'a' => 'Je krijgt vooraf een heldere prijsindicatie. Je begint klein en breidt uit wanneer je er klaar voor bent. Je zit nergens onnodig aan vast.'],
                    ['q' => 'Kan de site koppelen met mijn systemen?', 'a' => 'Voor veel boekhoud-, agenda- en kassasystemen is een koppeling mogelijk. Anders krijg je overzichten netjes per mail.'],
                    ['q' => 'Hoe snel staat mijn site online?', 'a' => 'Een gratis voorbeeld zie je vaak binnen 1 à 2 dagen. Een complete site staat meestal binnen twee weken live.'],
                ],
            ]],
            ['type' => 'location', 'block_key' => 'contact', 'sort' => 90, 'locked' => false, 'status' => 'placeholder', 'content' => [
                'heading' => 'Bel of maak een afspraak',
                'sub'     => 'Vraag of spoed? Neem gerust contact op.',
                'hours'   => [
                    ['day' => 'Maandag t/m vrijdag', 'time' => '09:00 - 17:30'],
                    ['day' => 'Zaterdag', 'time' => 'Op afspraak'],
                    ['day' => 'Zondag', 'time' => 'Gesloten'],
                ],
            ]],
            ['type' => 'groeipad', 'block_key' => 'groeipad', 'sort' => 95, 'locked' => false, 'status' => 'klaar', 'content' => null],
            ['type' => 'wizard', 'block_key' => 'wizard', 'sort' => 100, 'locked' => true, 'status' => 'klaar', 'content' => null],
        ];
    }
}
