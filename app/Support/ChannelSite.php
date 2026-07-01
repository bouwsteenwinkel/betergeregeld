<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Eén channel-site (uit config/channel_sites.php). Read-only wrapper met
 * accessors + helpers zodat views niet rechtstreeks in de config graven.
 */
class ChannelSite
{
    /** @param array<string,mixed> $cfg */
    public function __construct(
        public readonly string $key,
        public readonly array $cfg,
    ) {
    }

    public function get(string $path, mixed $default = null): mixed
    {
        return data_get($this->cfg, $path, $default);
    }

    public function name(): string
    {
        return (string) ($this->cfg['name'] ?? $this->key);
    }

    public function branche(): string
    {
        return (string) ($this->cfg['branche'] ?? 'overig');
    }

    public function locale(): string
    {
        return (string) ($this->cfg['locale'] ?? 'nl');
    }

    public function domain(): ?string
    {
        $d = $this->cfg['domain'] ?? null;
        return $d ? strtolower(preg_replace('/^https?:\/\//', '', trim($d))) : null;
    }

    public function isLive(): bool
    {
        return ($this->cfg['status'] ?? 'draft') === 'live' && $this->domain() !== null;
    }

    /** Basis-URL: op het eigen domein als live, anders de preview-prefix. */
    public function baseUrl(): string
    {
        if ($this->isLive()) {
            return 'https://' . $this->domain();
        }
        return url('/_site/' . $this->key);
    }

    public function url(string $path = ''): string
    {
        $path = ltrim($path, '/');
        return rtrim($this->baseUrl(), '/') . ($path ? '/' . $path : '');
    }

    /** @return array<string,string> kleur/font-tokens met defaults */
    public function theme(): array
    {
        return array_merge([
            'primary' => '#0f172a',
            'accent'  => '#2563eb',
            'ink'     => '#0f172a',
            'muted'   => '#64748b',
            'bg'      => '#ffffff',
            'surface' => '#ffffff',
            'font'    => "system-ui, -apple-system, sans-serif",
            'font_display' => null,   // display-font voor koppen/wordmark; valt terug op 'font'
            'font_url'=> null,
            'radius'  => '14px',
            // Tekstkleur op de accentkleur. Standaard de donkere ink, want de
            // meeste accenten zijn warm/licht; per thema te overschrijven.
            'on_accent' => null,
            // Primaire-CTA-kleur (knoppen). Standaard = accent, maar per thema los
            // te zetten als de CTA harder moet contrasteren met het beeld.
            'cta'    => null,
            'on_cta' => null,
        ], (array) ($this->cfg['theme'] ?? []));
    }

    /** CSS-variabelen voor in de <style> van de layout. */
    public function cssVars(): string
    {
        $t = $this->theme();
        $map = [
            '--c-primary' => $t['primary'],
            '--c-accent'  => $t['accent'],
            '--c-on-accent' => $t['on_accent'] ?? $t['ink'],
            '--c-cta'     => $t['cta'] ?? $t['accent'],
            '--c-on-cta'  => $t['on_cta'] ?? ($t['cta'] ? '#ffffff' : ($t['on_accent'] ?? $t['ink'])),
            '--c-ink'     => $t['ink'],
            '--c-muted'   => $t['muted'],
            '--c-bg'      => $t['bg'],
            '--c-surface' => $t['surface'],
            '--font'         => $t['font'],
            '--font-display' => $t['font_display'] ?: $t['font'],
            '--radius'    => $t['radius'],
        ];
        return implode(';', array_map(fn ($k, $v) => "$k:$v", array_keys($map), $map));
    }

    public function brand(string $key, mixed $default = null): mixed
    {
        return data_get($this->cfg, 'brand.' . $key)
            ?? config('channel_sites.defaults.' . $key, $default);
    }

    /**
     * Monogram voor het auto-gegenereerde logo-embleem: 1 tot 2 letters,
     * afgeleid van de sitenaam (of brand.logo_monogram-override).
     */
    public function monogram(): string
    {
        if ($m = $this->brand('logo_monogram')) {
            return mb_strtoupper((string) $m);
        }

        $name = trim(strip_tags((string) $this->name()));

        // Twee of meer hoofdletters (bv. "BarberSite" -> "BS", "HorecaSites" -> "HS").
        if (preg_match_all('/\p{Lu}/u', $name, $caps) && count($caps[0]) >= 2) {
            return mb_strtoupper($caps[0][0] . $caps[0][1]);
        }

        // Anders: eerste letters van de eerste twee woorden, of de eerste twee tekens.
        $words = preg_split('/[\s\-]+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($words) >= 2) {
            return mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
        }

        return mb_strtoupper(mb_substr($name, 0, 2));
    }

    /** Eigen logo als afbeelding (public/-pad of URL), of null → tekst-wordmark. */
    public function logoImage(): ?string
    {
        $img = $this->brand('logo_image');
        if (! $img) {
            return null;
        }
        return preg_match('#^https?://#', $img) ? $img : asset(ltrim($img, '/'));
    }

    /** Gegenereerd channel-beeld voor een slot (hero/detail), of null. */
    public function image(string $slot): ?string
    {
        return app(\App\Services\ChannelSites\ChannelImageGenerator::class)->url($this->key, $slot);
    }

    /** Responsive srcset voor een channel-beeld, of leeg. */
    public function imageSrcset(string $slot): string
    {
        return app(\App\Services\ChannelSites\ChannelImageGenerator::class)->srcset($this->key, $slot);
    }

    /** Unieke (AI-)plaats-intro, met de sjabloon-tekst als fallback. */
    public function placeIntro(string $slug, string $city): string
    {
        $unique = app(\App\Services\ChannelSites\ChannelContentGenerator::class)->placeIntroText($this->key, $slug);
        if ($unique) {
            return $unique;
        }
        return str_replace(':city', $city, (string) $this->get('places.city_intro', ''));
    }

    /**
     * FAQ-items voor dit kanaal als [vraag, antwoord]-paren, of null.
     * Een handgeschreven `home.faq` uit config wint (expliciete override),
     * anders de (AI-)gegenereerde JSON.
     */
    public function faq(): ?array
    {
        if ($curated = $this->get('home.faq')) {
            return $curated;
        }

        return app(\App\Services\ChannelSites\ChannelContentGenerator::class)->faqItems($this->key) ?: null;
    }

    /**
     * JSON-LD (schema.org) als <script>-tag voor in de <head>, voor rich results
     * én GEO (geciteerd worden door AI-antwoordmachines). Een @graph met de
     * dienstverlener, de site en, indien aanwezig, een FAQPage uit de FAQ-content.
     */
    public function jsonLd(): string
    {
        $service = array_filter([
            '@type'       => 'ProfessionalService',
            '@id'         => $this->baseUrl() . '#org',
            'name'        => $this->name(),
            'url'         => $this->baseUrl(),
            'telephone'   => $this->brand('phone'),
            'email'       => $this->brand('email'),
            'image'       => $this->image('hero'),
            'areaServed'  => ['@type' => 'Country', 'name' => 'Nederland'],
            'description' => $this->homeDescription() ?: null,
            'sameAs'      => $this->brand('sameas') ?: null,   // array van socials/GBP, indien geconfigureerd
        ]);

        $website = array_filter([
            '@type'     => 'WebSite',
            '@id'       => $this->baseUrl() . '#website',
            'url'       => $this->baseUrl(),
            'name'      => $this->name(),
            'publisher' => ['@id' => $this->baseUrl() . '#org'],
            'inLanguage'=> $this->locale(),
        ]);

        $graph = [$service, $website];

        // FAQPage: alleen als er FAQ-content is (die ook zichtbaar op de pagina staat,
        // anders is het schema in strijd met de richtlijnen).
        if ($faq = $this->faq()) {
            $graph[] = [
                '@type'      => 'FAQPage',
                '@id'        => $this->baseUrl() . '#faq',
                'mainEntity' => array_map(fn ($qa) => [
                    '@type'          => 'Question',
                    'name'           => $qa[0] ?? '',
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1] ?? ''],
                ], $faq),
            ];
        }

        $data = ['@context' => 'https://schema.org', '@graph' => $graph];

        return '<script type="application/ld+json">'
            . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            . '</script>';
    }

    /** Discrete moeder-endorsement onderaan, bv. "Volgens de Groeidiamant …". */
    public function endorsement(): ?string
    {
        return $this->brand('endorsement') ?: null;
    }

    public function endorsementUrl(): ?string
    {
        return $this->brand('endorsement_url') ?: null;
    }

    /** Keurmerk-logo (moedermerk) als afbeelding voor in de footer, of null. */
    public function endorsementLogo(): ?string
    {
        $img = $this->brand('endorsement_logo');
        if (! $img) {
            return null;
        }
        return preg_match('#^https?://#', $img) ? $img : asset(ltrim($img, '/'));
    }

    public function homeTitle(): string
    {
        return (string) ($this->cfg['meta']['home_title'] ?? $this->name());
    }

    public function homeDescription(): string
    {
        return (string) ($this->cfg['meta']['home_description'] ?? '');
    }

    /** Optionele bespoke homepage-view, of de generieke. */
    public function homeView(): string
    {
        $view = $this->cfg['view'] ?? null;
        return ($view && view()->exists($view)) ? $view : 'channels.home';
    }

    public static function slug(string $place): string
    {
        return Str::slug($place);
    }
}
