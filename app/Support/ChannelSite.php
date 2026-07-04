<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Eén channel-site (uit config/channel_sites.php). Read-only wrapper met
 * accessors + helpers zodat views niet rechtstreeks in de config graven.
 */
class ChannelSite
{
    /**
     * @param array<string,mixed> $cfg
     * @param iterable<\App\Models\Channel\Block>|null $blocks DB-blokken (null = config-site)
     */
    public function __construct(
        public readonly string $key,
        public readonly array $cfg,
        public readonly ?iterable $blocks = null,
    ) {
    }

    public function get(string $path, mixed $default = null): mixed
    {
        return data_get($this->cfg, $path, $default);
    }

    /** Channel-branche-key (bv. 'kapper') voor branche-brede blok-overrides. */
    public function brancheKey(): ?string
    {
        return $this->cfg['branche_key'] ?? null;
    }

    /** @return iterable<\App\Models\Channel\Block> */
    public function blocks(): iterable
    {
        return $this->blocks ?? [];
    }

    public function hasBlocks(): bool
    {
        return $this->blocks !== null && count($this->blocks) > 0;
    }

    /**
     * Blokken voor een fase-presentatie: basis-blokken (facet leeg = alle fases)
     * + de blokken van deze fase, in sort-volgorde.
     * @return \Illuminate\Support\Collection<int,\App\Models\Channel\Block>
     */
    public function blocksForFacet(string $facet): \Illuminate\Support\Collection
    {
        return collect($this->blocks())
            ->filter(fn ($b) => blank($b->facet) || $b->facet === $facet)
            ->values();
    }

    /**
     * Resolveert de view voor een blok, in volgorde van specifiek → generiek:
     *   1. channels._blocks.{site}.{block_key}    (bespoke per blok)
     *   2. channels._blocks.{site}.{type}         (bespoke per type, deze site)
     *   3. channels._blocks.branche-{branche}.{type} (branche-breed)
     *   4. channels.blocks.{type}                 (generieke bibliotheek)
     *   5. channels.blocks._generic               (universele placeholder)
     */
    public function blockView(string $type, string $blockKey): string
    {
        $candidates = [
            "channels._blocks.{$this->key}.{$blockKey}",
            "channels._blocks.{$this->key}.{$type}",
        ];
        if ($bk = $this->brancheKey()) {
            $candidates[] = "channels._blocks.branche-{$bk}.{$type}";
        }
        $candidates[] = "channels.blocks.{$type}";
        $candidates[] = 'channels.blocks._generic';

        foreach ($candidates as $view) {
            if (view()->exists($view)) {
                return $view;
            }
        }
        return 'channels.blocks._generic';
    }

    public function name(): string
    {
        return (string) ($this->cfg['name'] ?? $this->key);
    }

    /**
     * Zit de bezoeker op een demo-pagina (/voorbeeld)? Daar is de site een mockup
     * van de klant (demo-identiteit); elders (trigger/sales/plaatsen) gaat 't over
     * óns aanbod (trigger-identiteit).
     */
    public function isDemoContext(): bool
    {
        try {
            return \Illuminate\Support\Str::contains(request()->path(), 'voorbeeld');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Weergavenaam: demo → sitenaam (mockup); trigger → brand.footer_name als gezet. */
    public function displayName(): string
    {
        if (! $this->isDemoContext() && ($n = $this->brand('footer_name'))) {
            return (string) $n;
        }
        return $this->name();
    }

    /** Meta-/og-beschrijving: demo → home_description; trigger → brand.footer_about als gezet. */
    public function metaDescription(): string
    {
        if (! $this->isDemoContext() && ($d = $this->brand('footer_about'))) {
            return (string) $d;
        }
        return $this->homeDescription();
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
            '--c-primary'   => $t['primary'],
            '--c-accent'    => $t['accent'],
            '--c-on-accent' => $t['on_accent'] ?? $t['ink'],
            '--c-cta'       => $t['cta'] ?? $t['accent'],
            '--c-on-cta'    => $t['on_cta'] ?? ($t['cta'] ? '#ffffff' : ($t['on_accent'] ?? $t['ink'])),
            '--c-ink'       => $t['ink'],
            '--c-muted'     => $t['muted'],
            '--c-bg'        => $t['bg'],
            '--c-surface'   => $t['surface'],
            // Footer-achtergrond apart, zodat donkere thema's (ink = lichte tekst)
            // geen lichte footer krijgen. Default = ink (ongewijzigd licht thema).
            '--c-footer-bg' => $t['footer_bg'] ?? $t['ink'],
            '--font'         => $t['font'],
            '--font-display' => $t['font_display'] ?: $t['font'],
            '--radius'      => $t['radius'],
        ];
        return implode(';', array_map(fn ($k, $v) => "$k:$v", array_keys($map), $map));
    }

    /** @return array<string,mixed> site-specifieke header-config */
    public function header(): array
    {
        return (array) ($this->cfg['header'] ?? []);
    }

    /** Menu-items voor de nav (site-specifiek), met een nette fallback. */
    public function navMenu(): array
    {
        $menu = $this->header()['menu'] ?? null;
        if (! is_array($menu) || ! $menu) {
            $menu = [
                ['label' => 'Home', 'href' => ''],
                ['label' => 'Plaatsen', 'href' => 'plaatsen'],
                ['label' => 'Blog', 'href' => 'blog'],
                ['label' => 'Over ons', 'href' => 'over-ons'],
            ];
        }

        // Ankers waarvoor nu een eigen pagina bestaat → naar die pagina i.p.v. een
        // (vaak niet-bestaand) home-anker.
        $pageForAnchor = ['#diensten' => 'diensten', '#werkwijze' => 'werkwijze', '#reviews' => 'cases', '#contact' => 'contact'];
        $menu = array_map(function ($m) use ($pageForAnchor) {
            $h = trim((string) ($m['href'] ?? ''));
            if (isset($pageForAnchor[$h])) {
                $m['href'] = $pageForAnchor[$h];
            }
            return $m;
        }, $menu);

        // Zorg dat elke site 'Prijzen'-, 'Plaatsen'- en 'Blog'-links heeft (deze
        // pagina's bestaan voor alle niche-sites). Injecteer vóór 'Contact' als ze
        // ontbreken.
        foreach ([['label' => 'Prijzen', 'href' => 'prijzen'], ['label' => 'Plaatsen', 'href' => 'plaatsen'], ['label' => 'Blog', 'href' => 'blog']] as $inject) {
            $has = collect($menu)->contains(fn ($m) => trim((string) ($m['href'] ?? ''), '/') === $inject['href']);
            if ($has) {
                continue;
            }
            $pos = collect($menu)->search(fn ($m) => str_contains((string) ($m['href'] ?? ''), 'contact'));
            if ($pos === false) {
                $menu[] = $inject;
            } else {
                array_splice($menu, $pos, 0, [$inject]);
            }
        }

        // Zorg dat er altijd een 'Contact'-link is (eigen pagina, geen formulier).
        $hasContact = collect($menu)->contains(fn ($m) => trim((string) ($m['href'] ?? ''), '/#') === 'contact');
        if (! $hasContact) {
            $menu[] = ['label' => 'Contact', 'href' => 'contact'];
        }

        return $menu;
    }

    /** Header-CTA-knop (label + bestemming), default naar de funnel. */
    public function navCta(): array
    {
        $cta = (array) ($this->header()['cta'] ?? []);
        return [
            'label' => $cta['label'] ?? 'Gratis voorbeeld',
            'href'  => $cta['href'] ?? '#gratis-voorbeeld',
        ];
    }

    /** Lost een menu-href op: '' = home, '#anker' = anker op home, pad of absolute. */
    public function navHref(string $href): string
    {
        $href = trim($href);
        if ($href === '') {
            return $this->url();
        }
        if (str_starts_with($href, '#')) {
            // Het aanvraagformulier staat op ELKE pagina → same-page anker.
            if (in_array($href, ['#contact', '#gratis-voorbeeld'], true)) {
                return $href;
            }
            // Overige ankers verwijzen naar een home-sectie → absoluut naar home.
            return $this->url() . $href;
        }
        if (preg_match('#^(https?://|tel:|mailto:)#', $href)) {
            return $href;
        }
        return $this->url($href);
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

    /**
     * Gegenereerd channel-beeld voor een slot (hero/detail), of null.
     * Eigen site-beeld wint; anders het gedeelde sector-beeld (per lead-branche).
     */
    public function image(string $slot): ?string
    {
        $gen = app(\App\Services\ChannelSites\ChannelImageGenerator::class);
        return $gen->url($this->key, $slot) ?? $gen->url($this->branche(), $slot);
    }

    /** Responsive srcset voor een channel-beeld (site-eigen, anders sector), of leeg. */
    public function imageSrcset(string $slot): string
    {
        $gen = app(\App\Services\ChannelSites\ChannelImageGenerator::class);
        $own = $gen->srcset($this->key, $slot);
        return $own !== '' ? $own : $gen->srcset($this->branche(), $slot);
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
        $logo = $this->brand('footer_logo')
            ? rtrim($this->baseUrl(), '/') . '/' . ltrim((string) $this->brand('footer_logo'), '/')
            : null;

        $service = array_filter([
            '@type'       => 'ProfessionalService',
            '@id'         => $this->baseUrl() . '#org',
            'name'        => $this->displayName(),
            'url'         => $this->baseUrl(),
            'logo'        => $logo,
            'telephone'   => $this->brand('phone'),
            'email'       => $this->brand('email'),
            'image'       => $this->image('hero') ?: $logo,
            'areaServed'  => ['@type' => 'Country', 'name' => 'Nederland'],
            'description' => $this->metaDescription() ?: null,
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

    /**
     * Bespoke betergeregeld-VERKOOPPAGINA voor deze site, of null.
     * Als die bestaat wordt de hoofdpagina de sales-pitch (aan de ondernemer) en
     * verhuist de blok-gedreven demo naar /voorbeeld. Opt-in per site via het
     * bestaan van channels/_sales/{key}.blade.php.
     */
    public function salesHomeView(): ?string
    {
        $view = "channels._sales.{$this->key}";

        return view()->exists($view) ? $view : null;
    }

    /**
     * Bespoke PRODUCT-LANDINGSPAGINA voor deze site, of null. Rendert één product
     * (facet) volledig op /{facet} (trigger, waar ads/SEO op landen). Opt-in per
     * site via het bestaan van channels/_landing/{key}.blade.php.
     */
    public function landingView(): ?string
    {
        $view = "channels._landing.{$this->key}";

        return view()->exists($view) ? $view : null;
    }

    /** Bespoke blade > blok-gedreven (DB) > generieke config-home. */
    public function homeView(): string
    {
        $view = $this->cfg['view'] ?? null;
        if ($view && view()->exists($view)) {
            return $view;                 // tijdelijke bespoke pagina (transitie)
        }
        if ($this->hasBlocks()) {
            return 'channels.home-blocks'; // blok-gedreven render
        }
        return 'channels.home';            // legacy config-home (facet-zone + wizard)
    }

    public static function slug(string $place): string
    {
        return Str::slug($place);
    }
}
