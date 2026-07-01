<?php

namespace App\Services\ChannelSites;

use App\Models\Channel\Branche;
use App\Models\Channel\Site;
use App\Models\WebsiteLead;
use App\Services\Ai\AnthropicClient;
use Illuminate\Support\Str;

/**
 * Genereert per niche-branche een complete voorbeeldsite in ons DB-blokkensysteem,
 * met vakspecifieke content via Claude (structuredCall → gegarandeerd valide JSON).
 * Levert naast de standaard website-content ook de Groeidiamant-fase-secties
 * (webshop, afspraken/klantenportaal, automatisering, ai), net als de handmatige
 * loodgieter-site — maar automatisch en op maat per branche.
 */
class NicheSiteGenerator
{
    /** @return array{ok:bool,error?:string,site?:Site,usage?:array,brand?:string} */
    public function generateForBranche(Branche $branche, bool $force = false, ?string $model = null): array
    {
        if (! $force && $branche->sites()->exists()) {
            return ['ok' => false, 'error' => 'heeft-al-site'];
        }

        $client = app(AnthropicClient::class);
        $sector = WebsiteLead::BRANCHES[$branche->lead_branche] ?? $branche->lead_branche;

        $data = $client->structuredCall([
            'model'             => $model ?: $client->writerModel(),
            'max_tokens'        => 8000,
            'system'            => $this->systemPrompt(),
            'user'              => "Niche: {$branche->name}. Sector: {$sector}. "
                . 'Genereer de volledige, vakspecifieke voorbeeldsite-content. Vul ALLE velden.',
            'tool_name'         => 'lever_site_content',
            'tool_description'  => 'Lever de volledige site-content via dit gereedschap. Antwoord niet in chat-tekst.',
            'tool_input_schema' => $this->schema(),
        ]);

        if (! is_array($data)) {
            return ['ok' => false, 'error' => $client->lastError ?: 'geen-data'];
        }

        $site = $this->buildSite($branche, $data, $force);

        return ['ok' => true, 'site' => $site, 'usage' => $client->lastUsage(), 'brand' => $data['brand_name'] ?? $branche->name];
    }

    /* ─────────────────────────── Prompt + schema ─────────────────────────── */

    private function systemPrompt(): string
    {
        return <<<'SYS'
            Je schrijft marketing-content voor "voorbeeldsites" waarmee Betergeregeld ICT ondernemers per branche benadert. Elke site is een nichesite die via SEO en advertenties leads binnenhaalt met een gratis-voorbeeld-aanbod. Doel: een ondernemer in de branche herkent zich meteen ("deze partij snapt mijn vak") en vraagt een gratis voorbeeld aan.

            Huisstijl (STRIKT):
            - Nederlands, jij-vorm, concreet en eerlijk.
            - GEEN em-dashes. Gebruik komma of punt.
            - Geen holle marketingtaal. Vermijd: ontzorgen, naadloos, op maat, dé specialist, marktleider, totaaloplossing.
            - Vakspecifiek: gebruik de echte diensten, termen en realistische NL-prijsindicaties van DEZE branche. Past een vaste prijs niet, gebruik "vanaf ..." of "op aanvraag".
            - Reviews klinken als echte klanten: kort, concreet, met een voornaam.
            - Icon-velden: precies 1 passende emoji.

            De site heeft Groeidiamant-groeifases. Naast de standaard website-content lever je ook fase-secties, telkens toegespitst op DEZE branche:
            - webshop: wat deze ondernemer online kan verkopen (producten, onderdelen, bonnen, pakketten die bij de niche passen).
            - afspraken: het concrete voordeel van online afspraken/planning voor deze ondernemer.
            - automatisering: welke administratie/processen automatisch kunnen (offertes, facturen, planning, herinneringen).
            - ai: een concreet, geloofwaardig AI-scenario voor deze branche (telefoon-assistent, chat, offerte-voorbereiding).

            Verzin een korte, brandbare bedrijfsnaam (1 tot 2 woorden, geen rechtsvorm) die bij de niche past.
            SYS;
    }

    /** JSON-schema voor de tool_use call. */
    private function schema(): array
    {
        $str = ['type' => 'string'];
        $iconItems = fn () => [
            'type' => 'array', 'minItems' => 4, 'maxItems' => 4,
            'items' => ['type' => 'object', 'required' => ['icon', 'title', 'text'], 'properties' => [
                'icon' => $str, 'title' => $str, 'text' => $str,
            ]],
        ];
        $priceItems = fn (int $min, int $max) => [
            'type' => 'array', 'minItems' => $min, 'maxItems' => $max,
            'items' => ['type' => 'object', 'required' => ['name', 'desc', 'price'], 'properties' => [
                'name' => $str, 'desc' => $str, 'price' => $str,
            ]],
        ];

        return [
            'type' => 'object',
            'required' => [
                'brand_name', 'tagline', 'meta_title', 'meta_description',
                'hero_eyebrow', 'hero_title', 'hero_sub', 'hero_note',
                'usps', 'services_heading', 'services_sub', 'services',
                'prices_heading', 'prices', 'steps', 'reviews', 'faq',
                'webshop_heading', 'webshop_sub', 'webshop_features', 'webshop_products',
                'afspraken_heading', 'afspraken_sub', 'afspraken_benefits',
                'auto_heading', 'auto_sub', 'auto_features', 'auto_cta_title', 'auto_cta_sub',
                'ai_heading', 'ai_sub', 'ai_features', 'ai_cta_title', 'ai_cta_sub',
            ],
            'properties' => [
                'brand_name'       => $str,
                'tagline'          => $str,
                'meta_title'       => $str,
                'meta_description' => $str,
                'hero_eyebrow'     => $str,
                'hero_title'       => $str,
                'hero_sub'         => $str,
                'hero_note'        => $str,
                'usps' => ['type' => 'array', 'minItems' => 3, 'maxItems' => 3,
                    'items' => ['type' => 'object', 'required' => ['icon', 'text'], 'properties' => ['icon' => $str, 'text' => $str]]],
                'services_heading' => $str,
                'services_sub'     => $str,
                'services'         => $iconItems(),
                'prices_heading'   => $str,
                'prices'           => $priceItems(3, 6),
                'steps' => ['type' => 'array', 'minItems' => 3, 'maxItems' => 3,
                    'items' => ['type' => 'object', 'required' => ['title', 'text'], 'properties' => ['title' => $str, 'text' => $str]]],
                'reviews' => ['type' => 'array', 'minItems' => 3, 'maxItems' => 3,
                    'items' => ['type' => 'object', 'required' => ['text', 'author'], 'properties' => ['text' => $str, 'author' => $str]]],
                'faq' => ['type' => 'array', 'minItems' => 4, 'maxItems' => 4,
                    'items' => ['type' => 'object', 'required' => ['q', 'a'], 'properties' => ['q' => $str, 'a' => $str]]],
                'webshop_heading'    => $str,
                'webshop_sub'        => $str,
                'webshop_features'   => $iconItems(),
                'webshop_products'   => $priceItems(3, 3),
                'afspraken_heading'  => $str,
                'afspraken_sub'      => $str,
                'afspraken_benefits' => $iconItems(),
                'auto_heading'       => $str,
                'auto_sub'           => $str,
                'auto_features'      => $iconItems(),
                'auto_cta_title'     => $str,
                'auto_cta_sub'       => $str,
                'ai_heading'         => $str,
                'ai_sub'             => $str,
                'ai_features'        => $iconItems(),
                'ai_cta_title'       => $str,
                'ai_cta_sub'         => $str,
            ],
        ];
    }

    /* ─────────────────────────── Site opbouwen ───────────────────────────── */

    private function buildSite(Branche $branche, array $d, bool $force): Site
    {
        $site = Site::updateOrCreate(['key' => $branche->key], [
            'channel_branche_id' => $branche->id,
            'name'   => $d['brand_name'] ?? $branche->name,
            'status' => 'draft',
            'locale' => 'nl',
            'theme'  => null,   // erft het sector/branche-thema
            'brand'  => [
                'logo_text'    => $d['brand_name'] ?? $branche->name,
                'logo_tagline' => $d['tagline'] ?? '',
                'trustline'    => 'Erkend vakwerk · 4,8 ★',
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
            'meta' => [
                'home_title'       => $d['meta_title'] ?? ($d['brand_name'] ?? $branche->name),
                'home_description' => $d['meta_description'] ?? '',
            ],
        ]);

        // Bij --force: oude gegenereerde blokken vervangen.
        $site->blocks()->delete();

        foreach ($this->blocksFrom($d) as $b) {
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

        return $site;
    }

    /** Mapt de gegenereerde JSON naar de blok-structuur (basis + fase-blokken). */
    private function blocksFrom(array $d): array
    {
        $reviews = array_map(fn ($r) => ['stars' => 5, 'text' => $r['text'] ?? '', 'author' => $r['author'] ?? ''], $d['reviews'] ?? []);

        return [
            // ── basis (website) ──
            ['type' => 'hero', 'key' => 'hero', 'sort' => 10, 'content' => [
                'eyebrow' => $d['hero_eyebrow'] ?? null, 'title' => $d['hero_title'] ?? null,
                'sub' => $d['hero_sub'] ?? null, 'cta_label' => 'Gratis voorbeeld aanvragen',
                'cta2_label' => 'Bekijk diensten', 'cta2_href' => '#diensten', 'note' => $d['hero_note'] ?? null,
            ]],
            ['type' => 'uspbar', 'key' => 'usps', 'sort' => 20, 'content' => ['items' => $d['usps'] ?? []]],
            ['type' => 'features', 'key' => 'diensten-grid', 'sort' => 30, 'content' => [
                'heading' => $d['services_heading'] ?? 'Wat we voor je doen', 'sub' => $d['services_sub'] ?? null, 'items' => $d['services'] ?? [],
            ]],
            ['type' => 'pricelist', 'key' => 'tarieven', 'sort' => 40, 'content' => [
                'eyebrow' => 'Tarieven', 'heading' => $d['prices_heading'] ?? 'Onze tarieven', 'items' => $d['prices'] ?? [],
            ]],
            ['type' => 'steps', 'key' => 'werkwijze', 'sort' => 50, 'content' => ['heading' => 'Zo werkt het', 'items' => $d['steps'] ?? []]],
            ['type' => 'reviews', 'key' => 'reviews', 'sort' => 70, 'content' => ['heading' => 'Wat klanten zeggen', 'items' => $reviews]],
            ['type' => 'faq', 'key' => 'faq', 'sort' => 80, 'content' => ['heading' => 'Veelgestelde vragen', 'items' => $d['faq'] ?? []]],
            ['type' => 'location', 'key' => 'contact', 'sort' => 90, 'content' => [
                'heading' => 'Bel of maak een afspraak', 'sub' => 'Spoed of vraag? Neem gerust contact op.',
                'hours' => [
                    ['day' => 'Maandag t/m vrijdag', 'time' => '08:00 – 18:00'],
                    ['day' => 'Zaterdag', 'time' => '09:00 – 16:00'],
                    ['day' => 'Zondag', 'time' => 'Gesloten'],
                ],
            ]],
            ['type' => 'groeipad', 'key' => 'groeipad', 'sort' => 95],
            ['type' => 'wizard', 'key' => 'wizard', 'sort' => 100],

            // ── webshop ──
            ['type' => 'features', 'facet' => 'webshop', 'key' => 'webshop-uitleg', 'sort' => 34, 'content' => [
                'heading' => $d['webshop_heading'] ?? null, 'sub' => $d['webshop_sub'] ?? null, 'items' => $d['webshop_features'] ?? [],
            ]],
            ['type' => 'pricelist', 'facet' => 'webshop', 'key' => 'webshop-producten', 'sort' => 38, 'content' => [
                'eyebrow' => 'Webshop', 'heading' => 'Populaire producten', 'items' => $d['webshop_products'] ?? [],
            ]],

            // ── afspraken (klantenportaal) ──
            ['type' => 'features', 'facet' => 'klantenportaal', 'key' => 'afspraken-voordeel', 'sort' => 45, 'content' => [
                'heading' => $d['afspraken_heading'] ?? null, 'sub' => $d['afspraken_sub'] ?? null, 'items' => $d['afspraken_benefits'] ?? [],
            ]],

            // ── automatisering ──
            ['type' => 'features', 'facet' => 'automatisering', 'key' => 'auto-uitleg', 'sort' => 55, 'content' => [
                'heading' => $d['auto_heading'] ?? null, 'sub' => $d['auto_sub'] ?? null, 'items' => $d['auto_features'] ?? [],
            ]],
            ['type' => 'cta', 'facet' => 'automatisering', 'key' => 'auto-cta', 'sort' => 58, 'content' => [
                'title' => $d['auto_cta_title'] ?? null, 'sub' => $d['auto_cta_sub'] ?? null, 'cta_label' => 'Gratis voorbeeld aanvragen',
            ]],

            // ── ai ──
            ['type' => 'features', 'facet' => 'ai', 'key' => 'ai-uitleg', 'sort' => 62, 'content' => [
                'heading' => $d['ai_heading'] ?? null, 'sub' => $d['ai_sub'] ?? null, 'items' => $d['ai_features'] ?? [],
            ]],
            ['type' => 'cta', 'facet' => 'ai', 'key' => 'ai-cta', 'sort' => 66, 'content' => [
                'title' => $d['ai_cta_title'] ?? null, 'sub' => $d['ai_cta_sub'] ?? null, 'cta_label' => 'Gratis voorbeeld aanvragen',
            ]],
        ];
    }
}
