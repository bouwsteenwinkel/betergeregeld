<?php

namespace Database\Seeders;

use App\Models\Channel\Branche;
use App\Models\Channel\Site;
use Illuminate\Database\Seeder;

/**
 * Voorbeeldsite voor loodgietersbedrijven ("Waterwerk"), in ons DB-blokkensysteem.
 * Toont bewust de Groeidiamant-verschillen per fase: standaard website (basis),
 * webshop-secties, een afspraken-voordeel-sectie (klantenportaal), en aparte
 * automatiserings- en AI-blokken. Idempotent.
 */
class LoodgieterSiteSeeder extends Seeder
{
    public function run(): void
    {
        $branche = Branche::updateOrCreate(['key' => 'loodgieter'], [
            'name'         => 'Loodgieter',
            'lead_branche' => 'bouw_installatie',
            'theme'        => $this->theme(),
            'active'       => true,
        ]);

        $site = Site::updateOrCreate(['key' => 'loodgieter'], [
            'channel_branche_id' => $branche->id,
            'name'   => 'Waterwerk',
            'status' => 'draft',
            'locale' => 'nl',
            'theme'  => null,   // erft het branche-thema
            'brand'  => [
                'logo_mark'    => 'plumber',
                'logo_text'    => 'Waterwerk',
                'logo_tagline' => 'Loodgieter & installatie',
                'trustline'    => 'Erkend & verzekerd · 4,9 ★',
                'phone'        => '085 800 12 34',
                'email'        => 'info@waterwerk.nl',
                'address'      => 'Bussum',
            ],
            'header' => [
                'menu' => [
                    ['label' => 'Diensten', 'href' => '#diensten'],
                    ['label' => 'Werkwijze', 'href' => '#werkwijze'],
                    ['label' => 'Reviews', 'href' => '#reviews'],
                    ['label' => 'Contact', 'href' => '#contact'],
                ],
                'cta' => ['label' => 'Gratis voorbeeld', 'href' => '#gratis-voorbeeld'],
            ],
            'meta'   => [
                'home_title'       => 'Loodgieter-website laten maken — meer klanten, minder gemiste calls',
                'home_description' => 'Een vertrouwde, goed vindbare website voor je loodgietersbedrijf. 24/7 spoed, vast tarief en online afspraken. Vooraf een gratis voorbeeld.',
            ],
        ]);

        if ($site->blocks()->exists()) {
            return;
        }

        foreach ($this->blocks() as $b) {
            $site->blocks()->create([
                'type'      => $b['type'],
                'facet'     => $b['facet'] ?? null,
                'block_key' => $b['key'],
                'sort'      => $b['sort'],
                'enabled'   => true,
                'locked'    => ($b['type'] === 'wizard'),
                'status'    => in_array($b['type'], ['wizard', 'groeipad'], true) ? 'klaar' : 'placeholder',
                'content'   => $b['content'] ?? null,
            ]);
        }
    }

    private function theme(): array
    {
        return [
            'primary'      => '#15507d',
            'accent'       => '#2ba9c9',
            'ink'          => '#16222e',
            'muted'        => '#5b6b78',
            'bg'           => '#f4f8fb',
            'surface'      => '#ffffff',
            'cta'          => '#f0871f',   // warme oranje CTA-knoppen (contrast met blauw)
            'on_cta'       => '#ffffff',
            'font'         => "'Inter', system-ui, sans-serif",
            'font_display' => "'Sora', system-ui, sans-serif",
            'font_url'     => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sora:wght@600;700;800&display=swap',
            'radius'       => '12px',
        ];
    }

    /** @return array<int,array> */
    private function blocks(): array
    {
        return [
            /* ───── BASIS (fase website — standaard) ───── */
            ['type' => 'hero', 'key' => 'hero', 'sort' => 10, 'content' => [
                'eyebrow'    => 'Voor loodgietersbedrijven',
                'title'      => 'De loodgieter die klanten zó vinden',
                'sub'        => 'Vertrouwd, goed vindbaar in Google en 24/7 bereikbaar bij spoed — een strakke website die de telefoon laat rinkelen.',
                'cta_label'  => 'Gratis voorbeeld aanvragen',
                'cta2_label' => 'Bekijk diensten',
                'cta2_href'  => '#diensten',
                'note'       => 'Binnen 2 werkdagen een voorbeeld van jóuw bedrijf. Vrijblijvend.',
            ]],
            ['type' => 'uspbar', 'key' => 'usps', 'sort' => 20, 'content' => ['items' => [
                ['icon' => '🚨', 'text' => '24/7 spoedservice'],
                ['icon' => '✅', 'text' => 'Vast tarief vooraf'],
                ['icon' => '🛡️', 'text' => 'Erkend & verzekerd'],
            ]]],
            ['type' => 'features', 'key' => 'diensten-grid', 'sort' => 30, 'content' => [
                'heading' => 'Waarvoor je ons belt',
                'sub'     => 'Van een druppende kraan tot een complete badkamer.',
                'items'   => [
                    ['icon' => '🔧', 'title' => 'Spoed & storing', 'text' => 'Lekkage, verstopping of geen warm water — we komen snel.'],
                    ['icon' => '🚿', 'title' => 'Sanitair & badkamer', 'text' => 'Kranen, toilet, douche en complete badkamers.'],
                    ['icon' => '🔥', 'title' => 'CV & verwarming', 'text' => 'Onderhoud, storing en vervanging van je ketel.'],
                    ['icon' => '💧', 'title' => 'Loodgieterswerk', 'text' => 'Leidingwerk, dakgoten en waterschade.'],
                ],
            ]],
            ['type' => 'pricelist', 'key' => 'tarieven', 'sort' => 40, 'content' => [
                'eyebrow' => 'Tarieven',
                'heading' => 'Altijd een vast tarief vooraf',
                'sub'     => 'Geen verrassingen achteraf.',
                'items'   => [
                    ['name' => 'Voorrijden + eerste uur', 'desc' => 'Spoed of gepland', 'price' => '€ 79'],
                    ['name' => 'Lekkage opsporen & verhelpen', 'desc' => 'Incl. kleine materialen', 'price' => 'vanaf € 95'],
                    ['name' => 'Ontstoppen', 'desc' => 'Gootsteen, wc of afvoer', 'price' => 'vanaf € 89'],
                    ['name' => 'Kraan vervangen', 'desc' => 'Inclusief montage', 'price' => 'vanaf € 65'],
                    ['name' => 'CV-ketel onderhoud', 'desc' => 'Jaarlijkse beurt', 'price' => '€ 99'],
                ],
            ]],
            ['type' => 'steps', 'key' => 'werkwijze', 'sort' => 50, 'content' => [
                'heading' => 'Zo werkt het',
                'items'   => [
                    ['title' => 'Bel of app', 'text' => 'Vertel wat er speelt, wij plannen direct in.'],
                    ['title' => 'Vast tarief vooraf', 'text' => 'Je weet precies waar je aan toe bent.'],
                    ['title' => 'Netjes opgelost', 'text' => 'Vakwerk, en we ruimen alles weer op.'],
                ],
            ]],
            ['type' => 'reviews', 'key' => 'reviews', 'sort' => 70, 'content' => [
                'heading' => 'Wat klanten zeggen',
                'items'   => [
                    ['stars' => 5, 'text' => 'Binnen een uur er en de lekkage was zo verholpen. Top.', 'author' => 'Mevr. Jansen'],
                    ['stars' => 5, 'text' => 'Netjes gewerkt en een eerlijk, vast tarief. Aanrader.', 'author' => 'Peter'],
                    ['stars' => 5, 'text' => 'Nieuwe cv-ketel vakkundig geplaatst. Zeer tevreden.', 'author' => 'Familie de Groot'],
                ],
            ]],
            ['type' => 'faq', 'key' => 'faq', 'sort' => 80, 'content' => [
                'heading' => 'Veelgestelde vragen',
                'items'   => [
                    ['q' => 'Wat kost een loodgieter?', 'a' => 'Je krijgt altijd vooraf een vast tarief, dus geen verrassingen achteraf.'],
                    ['q' => 'Kom je ook met spoed?', 'a' => 'Ja, 24/7. Bij spoed zijn we er meestal binnen een uur.'],
                    ['q' => 'In welk gebied werken jullie?', 'a' => 'In Bussum en de omgeving. Vraag het gerust even na.'],
                    ['q' => 'Krijg ik garantie op het werk?', 'a' => 'Ja, op ons werk én op de gebruikte materialen.'],
                ],
            ]],
            ['type' => 'location', 'key' => 'contact', 'sort' => 90, 'content' => [
                'heading' => 'Bel of maak een afspraak',
                'sub'     => 'Spoed? Bel direct. Anders plannen we je snel in.',
                'hours'   => [
                    ['day' => 'Maandag t/m vrijdag', 'time' => '08:00 – 18:00'],
                    ['day' => 'Zaterdag', 'time' => '09:00 – 16:00'],
                    ['day' => 'Zondag', 'time' => 'Alleen spoed'],
                ],
            ]],
            ['type' => 'groeipad', 'key' => 'groeipad', 'sort' => 95],
            ['type' => 'wizard', 'key' => 'wizard', 'sort' => 100],

            /* ───── WEBSHOP (fase 2) — 2 extra secties ───── */
            ['type' => 'features', 'facet' => 'webshop', 'key' => 'webshop-uitleg', 'sort' => 34, 'content' => [
                'heading' => 'Een webshop naast je bedrijf',
                'sub'     => 'Laat klanten zelf onderdelen bestellen — extra omzet, ook buiten je werkuren.',
                'items'   => [
                    ['icon' => '🛒', 'title' => 'Onderdelen & materialen', 'text' => 'Kranen, sifons en fittingen — klanten bestellen zelf.'],
                    ['icon' => '💳', 'title' => 'Veilig betalen', 'text' => 'iDEAL en achteraf, netjes gekoppeld.'],
                    ['icon' => '🚚', 'title' => 'Afhalen of bezorgen', 'text' => 'De klant kiest zelf.'],
                    ['icon' => '📦', 'title' => 'Voorraad zichtbaar', 'text' => 'Geen misgrepen of teleurstellingen.'],
                ],
            ]],
            ['type' => 'pricelist', 'facet' => 'webshop', 'key' => 'webshop-producten', 'sort' => 38, 'content' => [
                'eyebrow' => 'Webshop',
                'heading' => 'Populaire producten',
                'sub'     => 'Een greep uit wat je klanten online kunnen bestellen.',
                'items'   => [
                    ['name' => 'Thermostaatkraan', 'desc' => 'Merk-kwaliteit, incl. keerkleppen', 'price' => '€ 39'],
                    ['name' => 'Sifon-set', 'desc' => 'Compleet met dichtingen', 'price' => '€ 14'],
                    ['name' => 'Perstang huren', 'desc' => 'Per dag, borg vereist', 'price' => '€ 25'],
                ],
            ]],

            /* ───── AFSPRAKEN / KLANTENPORTAAL (fase 3) — 1 voordeel-sectie ───── */
            ['type' => 'features', 'facet' => 'klantenportaal', 'key' => 'afspraken-voordeel', 'sort' => 45, 'content' => [
                'heading' => 'Online afspraken: minder bellen, vollere agenda',
                'sub'     => 'Wat een online afsprakensysteem jou als loodgieter oplevert.',
                'items'   => [
                    ['icon' => '📅', 'title' => 'Klanten plannen zelf', 'text' => '24/7 — ook ’s avonds. Jij hoeft niet steeds op te nemen.'],
                    ['icon' => '🔔', 'title' => 'Automatische herinneringen', 'text' => 'Minder no-shows en lege plekken.'],
                    ['icon' => '🗓️', 'title' => 'Altijd een gevulde agenda', 'text' => 'Vrije momenten vullen zichzelf op.'],
                    ['icon' => '⏱️', 'title' => 'Uren terug per week', 'text' => 'Geen telefoon-tetris meer tijdens het werk.'],
                ],
            ]],

            /* ───── AUTOMATISERING (fase 4) — 2 blokken ───── */
            ['type' => 'features', 'facet' => 'automatisering', 'key' => 'auto-uitleg', 'sort' => 55, 'content' => [
                'heading' => 'Minder papierwerk, meer draaien',
                'sub'     => 'Laat de administratie vanzelf meelopen met je werk.',
                'items'   => [
                    ['icon' => '🧾', 'title' => 'Offertes & facturen automatisch', 'text' => 'Vanuit de afspraak, in één klik verstuurd.'],
                    ['icon' => '🗺️', 'title' => 'Slimme routeplanning', 'text' => 'Meer klussen per dag, minder rijden.'],
                    ['icon' => '📥', 'title' => 'Betalingen die binnenkomen', 'text' => 'Automatische herinneringen bij te laat.'],
                    ['icon' => '📊', 'title' => 'Overzicht zonder gedoe', 'text' => 'Je administratie loopt vanzelf mee.'],
                ],
            ]],
            ['type' => 'cta', 'facet' => 'automatisering', 'key' => 'auto-cta', 'sort' => 58, 'content' => [
                'title'     => 'Je administratie op de automatische piloot?',
                'sub'       => 'We laten je in het voorbeeld precies zien wat er voor jouw bedrijf kan.',
                'cta_label' => 'Gratis voorbeeld aanvragen',
            ]],

            /* ───── AI (fase 5) — 2 blokken ───── */
            ['type' => 'features', 'facet' => 'ai', 'key' => 'ai-uitleg', 'sort' => 62, 'content' => [
                'heading' => 'Een AI-collega die nooit slaapt',
                'sub'     => 'Slimme hulp die klanten te woord staat en werk voorbereidt.',
                'items'   => [
                    ['icon' => '📞', 'title' => 'AI-telefoonassistent', 'text' => 'Neemt spoed aan buiten kantooruren en plant meteen in.'],
                    ['icon' => '💬', 'title' => 'AI-chat op je site', 'text' => 'Beantwoordt vragen en boekt afspraken, 24/7.'],
                    ['icon' => '✍️', 'title' => 'AI bereidt offertes voor', 'text' => 'Jij checkt en verstuurt — scheelt een hoop typwerk.'],
                    ['icon' => '🔎', 'title' => 'Slimme opvolging', 'text' => 'Geen aanvraag blijft liggen.'],
                ],
            ]],
            ['type' => 'cta', 'facet' => 'ai', 'key' => 'ai-cta', 'sort' => 66, 'content' => [
                'title'     => 'Benieuwd wat AI voor je bedrijf doet?',
                'sub'       => 'In het gratis voorbeeld tonen we een concreet AI-scenario voor loodgieters.',
                'cta_label' => 'Gratis voorbeeld aanvragen',
            ]],
        ];
    }
}
