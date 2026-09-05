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
        // Preview-sites (self-service voorbeeldtool) zijn de site van de ondernemer
        // zelf: geen verkoop-/pitch-chrome, net als de /voorbeeld-demo.
        if ($this->get('meta.preview.is_preview')) {
            return true;
        }
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

    /** Bestaat er een dedicated 1200x630 social-kaart (channel:og) voor deze site? */
    public function hasSocialCard(): bool
    {
        return is_file(public_path('channel-media/' . $this->key . '/og.png'));
    }

    /**
     * Heeft dit kanaal een eigen, op maat gemaakte favicon-set in
     * public/channel-media/<key>/ (favicon.svg + favicon.ico + apple-touch-icon)?
     * Zo niet, dan valt channels.partials.favicon terug op het gegenereerde
     * monogram. favicon.svg is de kanarie: die maken we altijd als eerste.
     */
    public function hasIconSet(): bool
    {
        return is_file(public_path('channel-media/' . $this->key . '/favicon.svg'));
    }

    /** URL-pad naar een bestand in de channel-media-map van dit kanaal. */
    public function mediaUrl(string $file): string
    {
        return '/channel-media/' . $this->key . '/' . ltrim($file, '/');
    }

    /**
     * OG-/social-afbeelding, in volgorde van voorkeur:
     * 1) dedicated 1200x630 social-kaart (og.png), 2) site-logo, 3) hero-beeld.
     */
    public function ogImage(): ?string
    {
        $base = rtrim($this->baseUrl(), '/');
        if ($this->hasSocialCard()) {
            return $base . '/channel-media/' . $this->key . '/og.png';
        }
        if ($logo = $this->brand('footer_logo')) {
            return $base . '/' . ltrim((string) $logo, '/');
        }
        return $this->image('hero');
    }

    public function branche(): string
    {
        return (string) ($this->cfg['branche'] ?? 'overig');
    }

    /**
     * Doelgroep-zelfstandignaamwoord (meervoud) voor de pitch-strip, bv.
     * "badkamerspecialisten". Volgorde: expliciete override (config-kanaal via
     * cfg['pitch_audience']) → branche-brede map (config/channel_sites.php →
     * branche_audience, op branche-key of lead-branche) → meervoud van de
     * nette branche-naam (of, als die ontbreekt, van de key).
     */
    public function pitchAudience(): string
    {
        // 1) expliciete per-kanaal-override.
        if ($a = ($this->cfg['pitch_audience'] ?? null)) {
            return (string) $a;
        }

        $map = (array) config('channel_sites.branche_audience', []);

        // 2) specifieke niche-override op branche-key (bv. 'restaurant').
        $bk = $this->brancheKey();
        if ($bk !== null && $bk !== '' && isset($map[$bk])) {
            return (string) $map[$bk];
        }

        // 3) het meervoud dat al in de branche-tokens staat.
        //
        // Dat is met de hand nagelopen; pluralizeNl() hieronder is een gok, en
        // die zat op 05-09-2026 bij 37 van de 204 branches mis. Zichtbaar
        // gevolg: "Afgestemd op klusbedrijfen" in de pitch-strip, op elke
        // pagina van jouw-klusbedrijf-website.nl. Het Nederlands kent te veel
        // uitzonderingen (bedrijf → bedrijven, huis → huizen) en de leenwoorden
        // helemaal (escape room, consultant, camping), dus beter geraden wordt
        // het niet. Er ís goede data — gebruik die.
        if ($trades = $this->get('places.trades')) {
            return mb_strtolower(trim((string) $trades));
        }

        // 4) meervoud van de nette branche-naam per niche ("Badkamerspecialist").
        if ($base = ($this->cfg['branche_name'] ?? null)) {
            return self::pluralizeNl(mb_strtolower(trim((string) $base)));
        }

        // 5) groeps-fallback op lead-branche (config-kanalen zonder branche-naam).
        $b = $this->branche();
        if ($b !== '' && isset($map[$b])) {
            return (string) $map[$b];
        }

        // 6) laatste redmiddel: meervoud van de key.
        return self::pluralizeNl(mb_strtolower(str_replace(['-', '_'], ' ', (string) ($bk ?: $b))));
    }

    /**
     * USP-items voor de pitch-strip (verkoop van ónze niche-website aan de
     * ondernemer). Het eerste item is branche-specifiek; de rest is vast. Per
     * kanaal te overschrijven via header.pitch_strip (lijst van
     * ['icon' => '…', 'text' => '…']).
     * @return array<int,array{icon:string,text:string}>
     */
    public function pitchStripItems(): array
    {
        $custom = $this->header()['pitch_strip'] ?? null;
        if (is_array($custom) && $custom) {
            return array_values(array_filter(array_map(function ($i) {
                if (! is_array($i)) {
                    return null;
                }
                return [
                    'icon'  => (string) ($i['icon'] ?? 'check'),
                    'title' => (string) ($i['title'] ?? ($i['text'] ?? '')),
                    'sub'   => (string) ($i['sub'] ?? ''),
                ];
            }, $custom)));
        }

        return [
            ['icon' => 'shield', 'title' => 'Speciaal ontwikkeld voor jou', 'sub' => 'Afgestemd op ' . $this->pitchAudience()],
            ['icon' => 'globe',  'title' => 'Eigen domeinnaam',  'sub' => 'Jouw eigen naam online'],
            ['icon' => 'mobile', 'title' => 'Volledig mobiel',   'sub' => 'Perfect op elke telefoon'],
            ['icon' => 'rocket', 'title' => 'Snel online',       'sub' => 'Live binnen enkele dagen'],
        ];
    }

    /**
     * Eenvoudig Nederlands meervoud — goed genoeg voor branche-namen. Voor de
     * gevallen waar dit misgaat (leenwoorden e.d.) is er de branche_audience-
     * override-map in config/channel_sites.php.
     */
    private static function pluralizeNl(string $w): string
    {
        $w = trim($w);
        if ($w === '') {
            return $w;
        }

        // Vaste, lastige uitgangen (langste eerst).
        foreach (['school' => 'scholen', 'salon' => 'salons', 'bureau' => 'bureaus', 'oog' => 'ogen'] as $s => $rep) {
            if (str_ends_with($w, $s)) {
                return substr($w, 0, -strlen($s)) . $rep;
            }
        }
        // Engelse -ing-leenwoorden (camping) → +s.
        if (str_ends_with($w, 'ing')) {
            return $w . 's';
        }
        // Klinker-einde → 's (foto's, sauna's).
        if (preg_match('/[aiouy]$/u', $w)) {
            return $w . "'s";
        }
        // Onbeklemtoonde/vaste uitgangen → +s (loodgieter → loodgieters).
        if (preg_match('/(e|el|em|en|er|erd|aar|eur|ier|ien|air)$/u', $w)) {
            return $w . 's';
        }
        // Standaard → +en, met inkorting van de lange klinker in een gesloten
        // lettergreep (zaak → zaken, rijschool is hierboven al afgevangen).
        if (preg_match('/(aa|ee|oo|uu)[bcdfgklmnprst]$/u', $w)) {
            $w = preg_replace_callback('/(aa|ee|oo|uu)([bcdfgklmnprst])$/u', fn ($m) => $m[1][0] . $m[2], $w);
        }

        // Slotmedeklinker verstemlozing: bedrijf → bedrijven, huis → huizen.
        // Zonder deze regel werd elk van de tientallen "...bedrijf"-branches
        // "...bedrijfen".
        //
        // Alleen na een lange klinker of tweeklank. Na een korte verdubbelt de
        // medeklinker juist (bus → bussen, stof → stoffen), dus een bredere
        // regel maakt het slechter in plaats van beter.
        if (preg_match('/(aa|ee|oo|uu|ie|oe|ui|ei|ij|au|ou|eu)f$/u', $w)) {
            return substr($w, 0, -1) . 'ven';
        }
        if (preg_match('/(aa|ee|oo|uu|ie|oe|ui|ei|ij|au|ou|eu)s$/u', $w)) {
            return substr($w, 0, -1) . 'zen';
        }

        return $w . 'en';
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
            'font_face'=> null,       // self-hosted-font: partial-naam met @font-face (i.p.v. font_url); AVG/CWV
            'radius'  => '14px',
            // Tekstkleur op de accentkleur. Standaard de donkere ink, want de
            // meeste accenten zijn warm/licht; per thema te overschrijven.
            'on_accent' => null,
            // Primaire-CTA-kleur (knoppen). Standaard = accent, maar per thema los
            // te zetten als de CTA harder moet contrasteren met het beeld.
            'cta'    => null,
            'on_cta' => null,
            // Tweede accent + levendigheids-tokens (voorbeeld-tool sfeer-paletten).
            // Allemaal null-default; cssVars() valt veilig terug op het oude gedrag,
            // dus channel-sites die deze keys niet zetten renderen byte-identiek.
            'accent_2'    => null,   // 2e accentkleur (feature-badges, golflijnen, verlopen)
            'on_accent_2' => null,   // tekstkleur op accent_2
            'tint'        => null,   // getinte sectie-achtergrond (i.p.v. plat surface)
            'hero_grad_b' => null,   // 2e stop van het hero-verloop
            'step_grad_b' => null,   // 2e stop van het step-num-verloop
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
            // Tweede accent + levendigheids-tokens. Elke fallback = exact het oude,
            // hardgecodeerde gedrag uit de block-blades, dus sites zonder deze theme-
            // keys renderen ongewijzigd.
            '--c-accent-2'    => $t['accent_2'] ?? $t['accent'],
            '--c-on-accent-2' => $t['on_accent_2'] ?? ($t['on_accent'] ?? $t['ink']),
            '--c-tint'        => $t['tint'] ?? $t['surface'],
            '--c-hero-grad-b' => $t['hero_grad_b'] ?? 'color-mix(in srgb,var(--c-primary) 62%,#0b1020)',
            '--c-step-grad-b' => $t['step_grad_b'] ?? 'color-mix(in srgb,var(--c-accent) 55%,#000)',
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

        // Preview-one-pager: het menu bestaat uit on-page ankers naar secties op
        // dezelfde pagina. Geen page-rewrite en geen 'Prijzen'-injectie (dat zijn
        // verkoop-subpagina's die op de klant-site niet thuishoren).
        if ($this->get('meta.preview.is_preview')) {
            return $menu;
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

        // Zorg dat elke site een 'Prijzen'-link heeft. Injecteer vóór 'Contact' als 'ie ontbreekt.
        foreach ([['label' => 'Prijzen', 'href' => 'prijzen']] as $inject) {
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

        // Reviews, Plaatsen en Blog niet in het hoofdmenu (top + mobiel); die staan
        // wel in de footer. Geldt voor alle channel-sites.
        $menu = array_values(array_filter($menu, function ($m) {
            $h = strtolower(trim((string) ($m['href'] ?? ''), '/#'));
            return ! in_array($h, ['cases', 'reviews', 'plaatsen', 'blog'], true);
        }));

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
            // Het aanvraagformulier staat op ELKE pagina → same-page anker. Op een
            // preview-one-pager zijn ALLE menu-ankers secties op dezelfde pagina.
            if (in_array($href, ['#contact', '#gratis-voorbeeld'], true) || $this->get('meta.preview.is_preview')) {
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
        // Terugval op het conventionele channel-logo als er geen expliciet
        // merk-logo is ingesteld: public/channel-media/<key>/logo.webp. Het
        // bestand deployt via git, dus het logo overleeft een brand-regeneratie
        // (die het DB-veld brand.logo_image kan wissen).
        if (! $img) {
            $conv = 'channel-media/' . $this->key . '/logo.webp';
            if (is_file(public_path($conv))) {
                $img = $conv;
            }
        }
        if (! $img) {
            return null;
        }
        if (preg_match('#^https?://#', $img)) {
            return $img;
        }
        $rel = ltrim($img, '/');
        $url = asset($rel);
        // Cache-buster op bestandstijd: een vervangen logo (zelfde bestandsnaam)
        // is meteen zichtbaar, zonder harde browser-refresh.
        $path = public_path($rel);
        if (is_file($path)) {
            $url .= '?v=' . filemtime($path);
        }
        return $url;
    }

    /**
     * Gegenereerd channel-beeld voor een slot (hero/detail), of null.
     * Eigen site-beeld wint; anders het gedeelde sector-beeld (per lead-branche).
     */
    public function image(string $slot): ?string
    {
        $gen = app(\App\Services\ChannelSites\ChannelImageGenerator::class);
        // Preview-sites (self-service voorbeeldtool) hebben geen branche en mogen
        // NOOIT terugvallen op een sector-beeld (dat gaf o.a. een timmerman bij een
        // nagelsalon). Alleen het eigen, per-branche gegenereerde beeld telt.
        if ($this->get('meta.preview.is_preview')) {
            return $gen->url($this->key, $slot);
        }
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
        // Logo als ImageObject met afmetingen (beter voor knowledge panel/logo-resultaat).
        $logo = null;
        $logoUrl = null;
        if ($rel = $this->brand('footer_logo')) {
            $logoUrl = rtrim($this->baseUrl(), '/') . '/' . ltrim((string) $rel, '/');
            $path = public_path(ltrim((string) $rel, '/'));
            $dim  = is_file($path) ? @getimagesize($path) : null;
            $logo = $dim
                ? ['@type' => 'ImageObject', 'url' => $logoUrl, 'width' => $dim[0], 'height' => $dim[1]]
                : $logoUrl;
        }

        // Adres (PostalAddress) uit brand.address, als geconfigureerd.
        $addr    = (array) $this->brand('address');
        $address = $addr ? array_filter([
            '@type'           => 'PostalAddress',
            'streetAddress'   => $addr['street']  ?? null,
            'postalCode'      => $addr['postal']  ?? null,
            'addressLocality' => $addr['city']    ?? null,
            'addressCountry'  => $addr['country'] ?? 'NL',
        ]) : null;

        // AggregateRating: ALLEEN met echte, verifieerbare review-data (brand.rating).
        // Nooit met een verzonnen score — dat is een Google-richtlijnschending.
        $rating    = (array) $this->brand('rating');
        $aggRating = (! empty($rating['value']) && ! empty($rating['count'])) ? [
            '@type'       => 'AggregateRating',
            'ratingValue' => (string) $rating['value'],
            'reviewCount' => (int) $rating['count'],
        ] : null;

        $service = array_filter([
            '@type'           => 'ProfessionalService',
            '@id'             => $this->baseUrl() . '#org',
            'name'            => $this->displayName(),
            'url'             => $this->baseUrl(),
            'logo'            => $logo,
            'telephone'       => $this->brand('phone'),
            'email'           => $this->brand('email'),
            'image'           => $this->image('hero') ?: $logoUrl,
            'address'         => $address,
            'areaServed'      => ['@type' => 'Country', 'name' => 'Nederland'],
            'description'     => $this->metaDescription() ?: null,
            'aggregateRating' => $aggRating,
            'sameAs'          => $this->brand('sameas') ?: null,   // array van socials/GBP, indien geconfigureerd
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

    /**
     * Titel voor een productlandings-pagina (/website, /automatisering, ...).
     *
     * De landingsviews zetten er een achtervoegsel achter ("voor je badkamerbedrijf")
     * omdat de fallback-titel alleen het facet-label is ("Website"). Maar zodra er een
     * echte hero-titel is, staat de branche daar al in — en dan leverde dat koppen op
     * als "Minder papierwerk in je badkamerbedrijf voor je badkamerbedrijf" (gemeten op
     * de live sites, 23-08-2026). Deze helper plakt het achtervoegsel er alleen achter
     * als het brancheword nog niet in de titel voorkomt.
     */
    public function landingTitle(?string $titel, string $fallbackLabel, string $achtervoegsel): string
    {
        $basis         = trim((string) ($titel ?: $fallbackLabel));
        $achtervoegsel = trim($achtervoegsel);

        if ($basis === '' || $achtervoegsel === '') {
            return $basis !== '' ? $basis : $achtervoegsel;
        }

        // Kernwoord uit het achtervoegsel: "voor je badkamerbedrijf" → "badkamerbedrijf".
        $kern = trim(preg_replace('/^voor\s+(je|jouw|een|uw)?\s*/iu', '', $achtervoegsel));

        if ($kern !== '' && mb_stripos($basis, $kern) !== false) {
            return $basis;
        }

        return $basis . ' ' . $achtervoegsel;
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

    /**
     * Stuurt dit kanaal een verkeerde/verwijderde URL naar de eigen homepage
     * i.p.v. een 404-foutpagina te tonen? Opt-in via config/channel_soft_404.php.
     */
    public function redirectsNotFoundToHome(): bool
    {
        return in_array($this->key, (array) config('channel_soft_404', []), true);
    }

    /**
     * Bespoke plaatsen-view voor deze site (index|province|show), of de gedeelde
     * fallback. Opt-in per site via het bestaan van
     * channels/_places/{key}/{name}.blade.php — laat andere kanalen ongemoeid.
     */
    public function placeView(string $name): string
    {
        $bespoke = "channels._places.{$this->key}.{$name}";

        return view()->exists($bespoke) ? $bespoke : "channels.places.{$name}";
    }

    /**
     * Bespoke blog-view voor deze site (index|show), of de gedeelde fallback.
     * Opt-in via channels/_blog/{key}/{name}.blade.php.
     */
    public function blogView(string $name): string
    {
        $bespoke = "channels._blog.{$this->key}.{$name}";

        return view()->exists($bespoke) ? $bespoke : "channels.blog.{$name}";
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
