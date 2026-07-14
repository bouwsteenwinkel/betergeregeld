<?php

namespace App\Services\ChannelSites;

use App\Models\Channel\Site;
use App\Services\Ai\AnthropicClient;
use Illuminate\Support\Str;

/**
 * Self-service voorbeeldsite-generator (de "60 seconden"-tool).
 *
 * Neemt vrije bezoeker-invoer (bedrijfsnaam, type bedrijf, primaire kleur, doel)
 * en zet binnen een halve minuut een tijdelijke, herkleurde voorbeeldsite neer in
 * ons DB-blokkensysteem. De site rendert daarna gratis via /_site/{key} met de
 * bestaande layout, blokken en facet-switcher.
 *
 * Anders dan NicheSiteGenerator:
 *   - vrije invoer i.p.v. een vaste branche;
 *   - een TIJDELIJKE site (key preview-..., meta.preview.expires_at) i.p.v. een
 *     permanente branche-site;
 *   - een sneller model (Sonnet) + alleen de hoofdpagina, zodat het < 60s blijft.
 *     De 5 Groeidiamant-facetten worden pas op verzoek bijgemaakt (lazy).
 */
class PreviewSiteGenerator
{
    /** Hoe lang een niet-opgeëiste preview blijft staan voordat cleanup 'm opruimt. */
    public const TTL_HOURS = 48;

    /**
     * @param  array{company:string,business_type:string,color:string,goal:string}  $input
     * @return array{ok:bool,error?:string,key?:string,site?:Site,usage?:array}
     */
    /**
     * Volledige generatie in één keer (tekst + site). Behouden voor CLI/tinker; de
     * web-tool splitst dit in startSite() + fillContent() zodat tekst en hero-beeld
     * PARALLEL kunnen lopen.
     */
    public function generate(array $input): array
    {
        $site = $this->startSite($input);
        $res  = $this->fillContent($site);

        if (empty($res['ok'])) {
            return ['ok' => false, 'error' => $res['error'] ?? 'onbekend'];
        }

        return ['ok' => true, 'key' => $site->key, 'site' => $site, 'usage' => $res['usage'] ?? null];
    }

    /**
     * Fase 1 (snel, GEEN AI): maak de tijdelijke preview-site (thema, branding, meta
     * met de ruwe invoer), nog zonder content-blokken. Zo geeft de tool meteen de key
     * terug en kunnen de tekst-call (fillContent) en de beeld-call (generateHeroImage)
     * daarna PARALLEL lopen i.p.v. na elkaar.
     */
    public function startSite(array $input): Site
    {
        $company = trim((string) ($input['company'] ?? ''));
        $type    = trim((string) ($input['business_type'] ?? ''));
        $goal    = $this->normalizeGoal((string) ($input['goal'] ?? 'website'));
        $sfeer   = $this->normalizeSfeer((string) ($input['sfeer'] ?? 'fris-modern'));
        $source  = trim((string) ($input['source_channel'] ?? ''));

        // Extra (optionele) content-input; verrijkt alleen de Claude-prompt.
        $place       = trim((string) ($input['place'] ?? ''));
        $keyServices = trim((string) ($input['key_services'] ?? ''));
        $usp         = trim((string) ($input['usp'] ?? ''));

        // Kleur is nu OPTIONEEL: leeg = het curated sfeer-palet; ingevuld = eigen-kleur-
        // override waaruit accent-2 en de getinte varianten worden afgeleid.
        $colorRaw = trim((string) ($input['color'] ?? ''));
        $ownColor = $colorRaw !== '' ? $this->normalizeHex($colorRaw) : null;
        $theme    = $this->themeFromPalette($sfeer, $ownColor);
        // Effectieve primaire kleur (voor het hero-beeld-accent), zodat het beeld bij
        // het gekozen palet past ook zonder eigen kleur.
        $effectiveColor = $ownColor ?? self::SFEREN[$sfeer]['primary'];

        $brandName = $company !== '' ? $company : 'Voorbeeld';
        $key       = 'preview-' . Str::lower(Str::random(12));
        $profile   = $this->goalProfile($goal);

        return Site::create([
            'channel_branche_id' => null,   // los van elke branche: puur uit site.theme
            'key'    => $key,
            'name'   => $brandName,
            'domain' => null,
            'status' => 'draft',
            'locale' => 'nl',
            'theme'  => $theme,
            'brand'  => [
                'logo_text'    => $brandName,
                'logo_tagline' => '',
                'trustline'    => 'Voorbeeld, in een halve minuut gemaakt',
            ],
            'header' => [
                // Nav-labels + header-CTA per doel (anchors blijven gelijk).
                'menu' => array_map(fn ($m) => ['label' => $m[0], 'href' => $m[1]], $profile['nav']),
                'cta'  => $profile['nav_cta'],
            ],
            'meta' => [
                'home_title'       => $brandName,
                'home_description' => '',
                'preview' => [
                    'is_preview'     => true,
                    'input'          => [
                        'company' => $company, 'business_type' => $type, 'color' => $effectiveColor, 'goal' => $goal,
                        'sfeer' => $sfeer, 'place' => $place, 'key_services' => $keyServices, 'usp' => $usp,
                    ],
                    'source_channel' => $source !== '' ? $source : null,
                    'expires_at'     => now()->addHours(self::TTL_HOURS)->toIso8601String(),
                    'claimed'        => false,
                    'facets_filled'  => [],
                ],
            ],
        ]);
    }

    /**
     * Fase 2 (de ~35s Claude-call): schrijf de content en vul de blokken van een
     * reeds aangemaakte preview-site. Idempotent: bestaan er al blokken (dubbele
     * submit), dan niets doen. Schrijft bewust NIET naar velden die generateHeroImage
     * ook aanraakt, zodat de parallelle beeld-call niet met deze update botst.
     *
     * @return array{ok:bool,error?:string,usage?:array,already?:bool}
     */
    public function fillContent(Site $site): array
    {
        $preview = (array) data_get($site->meta, 'preview', []);
        if (empty($preview['is_preview'])) {
            return ['ok' => false, 'error' => 'geen-preview'];
        }
        if ($site->blocks()->exists()) {
            return ['ok' => true, 'already' => true];
        }

        $company     = (string) data_get($preview, 'input.company', '');
        $type        = (string) data_get($preview, 'input.business_type', '');
        $goal        = $this->normalizeGoal((string) data_get($preview, 'input.goal', 'website'));
        $sfeer       = $this->normalizeSfeer((string) data_get($preview, 'input.sfeer', 'fris-modern'));
        $place       = (string) data_get($preview, 'input.place', '');
        $keyServices = (string) data_get($preview, 'input.key_services', '');
        $usp         = (string) data_get($preview, 'input.usp', '');

        $client = app(AnthropicClient::class);
        $data = $client->structuredCall([
            'model'             => $this->model($client),
            'max_tokens'        => 3200,
            'system'            => $this->systemPrompt(),
            'user'              => $this->userPrompt($company, $type, $goal, $sfeer, $place, $keyServices, $usp),
            'tool_name'         => 'lever_voorbeeldsite',
            'tool_description'  => 'Lever de voorbeeldsite-content via dit gereedschap. Antwoord niet in chat-tekst.',
            'tool_input_schema' => $this->schema(),
        ]);

        if (! is_array($data)) {
            return ['ok' => false, 'error' => $client->lastError ?: 'geen-data'];
        }

        // SEO-titels + tagline bijwerken met de gegenereerde content. generateHeroImage
        // schrijft NIET naar meta/brand, dus geen lost-update-race met deze schrijf.
        $meta = (array) $site->meta;
        $meta['home_title']       = $data['meta_title'] ?? $site->name;
        $meta['home_description'] = $data['meta_description'] ?? '';
        $brand = (array) $site->brand;
        $brand['logo_tagline'] = $data['tagline'] ?? ($brand['logo_tagline'] ?? '');
        $site->update(['meta' => $meta, 'brand' => $brand]);

        foreach ($this->blocksFrom($data, $goal) as $b) {
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

        return ['ok' => true, 'usage' => $client->lastUsage()];
    }

    /** Sonnet: balans tussen snelheid en kwaliteit. Overschrijfbaar via env. */
    private function model(AnthropicClient $client): string
    {
        return (string) env('ANTHROPIC_MODEL_PREVIEW', $client->translatorModel());
    }

    /* ─────────────────────────── Prompt + schema ─────────────────────────── */

    private function systemPrompt(): string
    {
        return <<<'SYS'
            Je schrijft de content voor een VOORBEELDwebsite die een ondernemer live ziet ontstaan. Doel: de ondernemer denkt binnen 5 seconden "hé, dit is mijn bedrijf, zo zou mijn site eruit kunnen zien" en wil verder.

            Huisstijl (STRIKT):
            - Nederlands, jij-vorm, concreet en eerlijk.
            - GEEN em-dashes. Gebruik een komma of een punt.
            - Geen holle marketingtaal. Vermijd: ontzorgen, naadloos, op maat, dé specialist, marktleider, totaaloplossing.
            - Schrijf vakspecifiek: gebruik de echte diensten, termen en realistische NL-prijsindicaties die bij DIT type bedrijf passen. Past een vaste prijs niet, gebruik "vanaf ..." of "op aanvraag".
            - Reviews klinken als echte klanten: kort, concreet, met een voornaam.
            - Icon-velden: precies 1 passende emoji.
            - Stem de nadruk af op het gekozen doel van de ondernemer, zonder de rest weg te laten.
            - De hero is kort en SEO/CTR-gericht: de kop (hero_title) benoemt in weinig woorden de winst voor de klant; de subtekst (hero_sub) is EEN korte zin met de kern-dienst als natuurlijke zoekterm en een reden om nu te boeken. Geen lange opsommingen in de hero.
            - Dienst-kaarten (services) en de intro erboven (services_sub) zijn kort en CRO-gericht: elke dienst-tekst is maximaal 1 zin (~12 woorden) die het concrete resultaat of voordeel voor de klant benoemt, niet alleen de dienst omschrijft. services_sub is 1 korte zin.
            SYS;
    }

    private function userPrompt(string $company, string $type, string $goal, string $sfeer = 'fris-modern', string $place = '', string $keyServices = '', string $usp = ''): string
    {
        $name = $company !== '' ? $company : '(verzin een korte, passende naam)';
        $goalLabel = self::GOALS[$goal] ?? 'meer klanten en aanvragen';

        // Optionele, door de ondernemer aangeleverde context; alleen toevoegen als
        // gevuld, zodat een leeg formulier de prompt (en de snelheid) ongemoeid laat.
        $extra = '';
        if ($keyServices !== '') {
            $extra .= "Kerndiensten of producten in de woorden van de ondernemer: {$keyServices}. Baseer de services, prices en steps hierop.\n";
        }
        if ($place !== '') {
            $extra .= "Werkgebied of plaats: {$place}. Laat hero_sub, faq en reviews hier natuurlijk naar verwijzen voor lokale vindbaarheid.\n";
        }
        if ($usp !== '') {
            $extra .= "Wat dit bedrijf onderscheidt: {$usp}. Laat dit terugkomen in hero_sub en in een van de usps.\n";
        }

        return "Bedrijfsnaam: {$name}.\n"
            . "Type bedrijf: {$type}.\n"
            . "Belangrijkste doel van de ondernemer: {$goalLabel}.\n"
            . 'Toon van de teksten: ' . $this->sfeerTone($sfeer) . ".\n"
            . $extra
            . "\n" . $this->goalGuidance($goal) . "\n\n"
            . 'Genereer de content voor de voorbeeld-hoofdpagina. Vul ALLE velden, toegespitst op DIT type bedrijf EN het bovenstaande doel. '
            . 'De velden services, prices, steps en faq krijgen een andere invulling per doel (zie hierboven); volg dat strikt.';
    }

    /**
     * Per-doel CONCRETE inhoudelijke sturing. Zonder dit levert het model voor elk
     * doel bijna dezelfde site; hiermee verschillen hero, services, prices, steps en
     * faq echt per gekozen doel. Werkt samen met goalProfile() (structuur/CTA's).
     */
    private function goalGuidance(string $goal): string
    {
        return match ($goal) {
            'webshop' => "Het doel is producten of diensten ONLINE VERKOPEN (een webshop). Richt de content daarop:\n"
                . "- hero_title/hero_sub gaan over online kopen, bestellen en bezorgd/afgehaald krijgen, NIET over een afspraak maken.\n"
                . "- services = de belangrijkste PRODUCTCATEGORIEEN of bestsellers van dit type bedrijf (geen diensten). services_heading bijvoorbeeld 'Ons assortiment' of 'Populair in de winkel'.\n"
                . "- prices = concrete PRODUCTEN met realistische prijs (name = productnaam, desc = korte productregel, price = bedrag). prices_heading bijvoorbeeld 'Populaire producten'.\n"
                . "- steps = het BESTELPROCES: kiezen, afrekenen, bezorgd of afhalen.\n"
                . "- faq = over verzendkosten, levertijd, retourneren en betaalmethoden.",
            'klantenportaal' => "Het doel is een KLANTENPORTAAL waar klanten zelf zaken online regelen. Richt de content daarop:\n"
                . "- hero gaat over zelf online regelen via een eigen account (afspraken, documenten, facturen), 24/7.\n"
                . "- services = de FUNCTIES van het portaal (bv. afspraken plannen, documenten inzien, facturen betalen, voortgang volgen). services_heading bijvoorbeeld 'Wat je zelf kunt regelen'.\n"
                . "- prices = pakket-/abonnementsvormen of algemene tarieven.\n"
                . "- steps = account aanmaken, inloggen, zelf regelen.\n"
                . "- faq = over inloggen, veiligheid en privacy van het portaal.",
            'automatisering' => "Het doel is ADMINISTRATIE EN PROCESSEN AUTOMATISEREN. Richt de content daarop:\n"
                . "- hero gaat over tijd besparen en fouten voorkomen door automatisering.\n"
                . "- services = concrete TAKEN die geautomatiseerd worden (offertes, facturen, herinneringen, urenregistratie, planning). services_heading bijvoorbeeld 'Wat we voor je automatiseren'.\n"
                . "- prices = pakketten, meestal per maand.\n"
                . "- steps = koppelen, instellen, automatisch laten lopen.\n"
                . "- faq = over koppelingen met bestaande software, veiligheid en of het past bij dit bedrijf.",
            'ai' => "Het doel is AI INZETTEN (telefoon, chat, offertes). Richt de content daarop:\n"
                . "- hero gaat over 24/7 bereikbaar zijn en sneller reageren met AI.\n"
                . "- services = AI-TOEPASSINGEN (telefoonassistent die opneemt, chatbot op de site, automatische offertes, e-mails beantwoorden). services_heading bijvoorbeeld 'Wat AI voor je doet'.\n"
                . "- prices = pakketten, meestal per maand.\n"
                . "- steps = kennismaken, de AI trainen op jouw bedrijf, live gaan.\n"
                . "- faq = over betrouwbaarheid, wanneer een mens overneemt en de kosten.",
            default => "Het doel is MEER KLANTEN EN AANVRAGEN via een professionele website. Richt de content daarop:\n"
                . "- hero gaat over gevonden worden en makkelijk een afspraak of aanvraag doen.\n"
                . "- services = de belangrijkste DIENSTEN van dit bedrijf.\n"
                . "- prices = tarieven of pakketten.\n"
                . "- steps = de werkwijze: contact, uitvoering, resultaat.\n"
                . "- faq = over werkwijze, prijzen en planning.",
        };
    }

    /**
     * Per-doel STRUCTUUR: nav-labels, hero-CTA's en de afsluitende sectie. Zo krijgt
     * elk doel een herkenbaar andere pagina (webshop eindigt met een winkel-CTA i.p.v.
     * de afspraak-widget; portaal/AI/automatisering hebben eigen CTA-teksten). Anchors
     * blijven gelijk (#diensten/#tarieven/#werkwijze/#contact) zodat de on-page links
     * op de one-pager blijven werken; alleen de LABELS en de laatste sectie verschillen.
     *
     * @return array<string,mixed>
     */
    private function goalProfile(string $goal): array
    {
        return match ($goal) {
            'webshop' => [
                'nav'       => [['Assortiment', '#diensten'], ['Producten', '#tarieven'], ['Bestellen', '#werkwijze'], ['Contact', '#contact']],
                'nav_cta'   => ['label' => 'Bekijk de webshop', 'href' => '#tarieven'],
                'hero_cta'  => ['label' => 'Bekijk de webshop', 'href' => '#tarieven'],
                'hero_cta2' => ['label' => 'Ons assortiment', 'href' => '#diensten'],
                'last'      => 'cta',
                'cta'       => ['title' => 'Klaar om te bestellen?', 'sub' => 'Bekijk het volledige assortiment en bestel eenvoudig online.', 'label' => 'Naar de webshop', 'href' => '#tarieven'],
            ],
            'klantenportaal' => [
                'nav'       => [['Functies', '#diensten'], ['Zo werkt het', '#werkwijze'], ['Tarieven', '#tarieven'], ['Inloggen', '#contact']],
                'nav_cta'   => ['label' => 'Inloggen', 'href' => '#contact'],
                'hero_cta'  => ['label' => 'Naar mijn account', 'href' => '#contact'],
                'hero_cta2' => ['label' => 'Zo werkt het', 'href' => '#werkwijze'],
                'last'      => 'booking',
                'booking'   => ['heading' => 'Plan een kennismaking', 'sub' => 'Kies een moment, dan laten we je persoonlijke portaal zien.', 'deposit' => 0],
            ],
            'automatisering' => [
                'nav'       => [['Wat we automatiseren', '#diensten'], ['Zo werkt het', '#werkwijze'], ['Pakketten', '#tarieven'], ['Contact', '#contact']],
                'nav_cta'   => ['label' => 'Plan een demo', 'href' => '#contact'],
                'hero_cta'  => ['label' => 'Plan een gratis demo', 'href' => '#contact'],
                'hero_cta2' => ['label' => 'Wat kan ik automatiseren?', 'href' => '#diensten'],
                'last'      => 'booking',
                'booking'   => ['heading' => 'Plan een gratis demo', 'sub' => 'Kies een moment, dan laten we zien wat je kunt automatiseren.', 'deposit' => 0],
            ],
            'ai' => [
                'nav'       => [['Wat AI doet', '#diensten'], ['Zo werkt het', '#werkwijze'], ['Pakketten', '#tarieven'], ['Contact', '#contact']],
                'nav_cta'   => ['label' => 'Plan een demo', 'href' => '#contact'],
                'hero_cta'  => ['label' => 'Plan een gratis demo', 'href' => '#contact'],
                'hero_cta2' => ['label' => 'Wat doet de AI?', 'href' => '#diensten'],
                'last'      => 'booking',
                'booking'   => ['heading' => 'Plan een gratis demo', 'sub' => 'Kies een moment, dan laten we de AI-assistent live zien.', 'deposit' => 0],
            ],
            default => [
                'nav'       => [['Diensten', '#diensten'], ['Werkwijze', '#werkwijze'], ['Prijzen', '#tarieven'], ['Contact', '#contact']],
                'nav_cta'   => ['label' => 'Maak een afspraak', 'href' => '#contact'],
                'hero_cta'  => ['label' => 'Maak een afspraak', 'href' => '#contact'],
                'hero_cta2' => ['label' => 'Bekijk diensten', 'href' => '#diensten'],
                'last'      => 'booking',
                'booking'   => ['heading' => 'Maak een afspraak', 'sub' => 'Kies een datum en tijd, vul je gegevens in en je plek staat vast.', 'deposit' => 10],
            ],
        };
    }

    /** JSON-schema (alleen de hoofdpagina, voor snelheid). */
    private function schema(): array
    {
        $str = ['type' => 'string'];
        $iconItems = fn (int $n) => [
            'type' => 'array', 'minItems' => $n, 'maxItems' => $n,
            'items' => ['type' => 'object', 'required' => ['icon', 'title', 'text'], 'properties' => [
                'icon' => $str, 'title' => $str, 'text' => $str,
            ]],
        ];

        return [
            'type' => 'object',
            'required' => [
                'tagline', 'meta_title', 'meta_description',
                'hero_title', 'hero_sub',
                'usps', 'services_heading', 'services_sub', 'services',
                'prices_heading', 'prices', 'steps', 'reviews', 'faq',
            ],
            'properties' => [
                'tagline'          => $str,
                'meta_title'       => $str,
                'meta_description' => $str,
                'hero_title'       => ['type' => 'string', 'description' => 'Korte, wervende H1 (max ~45 tekens). Benoem de winst voor de klant, geen bedrijfsnaam. Verwerk waar natuurlijk mogelijk de kern-dienst als zoekterm.'],
                'hero_sub'         => ['type' => 'string', 'description' => 'Eén korte zin (max ~130 tekens). De belangrijkste belofte + de kern-dienst als natuurlijke zoekterm, eindigend met een reden om nu een afspraak te maken. Geen opsomming, geen gestapelde bijzinnen.'],
                'usps' => ['type' => 'array', 'minItems' => 3, 'maxItems' => 3,
                    'items' => ['type' => 'object', 'required' => ['icon', 'text'], 'properties' => ['icon' => $str, 'text' => $str]]],
                'services_heading' => $str,
                'services_sub'     => $str,
                'services'         => $iconItems(4),
                'prices_heading'   => $str,
                'prices' => ['type' => 'array', 'minItems' => 3, 'maxItems' => 4,
                    'items' => ['type' => 'object', 'required' => ['name', 'desc', 'price'], 'properties' => [
                        'name' => $str, 'desc' => $str, 'price' => $str,
                    ]]],
                'steps' => ['type' => 'array', 'minItems' => 3, 'maxItems' => 3,
                    'items' => ['type' => 'object', 'required' => ['title', 'text'], 'properties' => ['title' => $str, 'text' => $str]]],
                'reviews' => ['type' => 'array', 'minItems' => 3, 'maxItems' => 3,
                    'items' => ['type' => 'object', 'required' => ['text', 'author'], 'properties' => ['text' => $str, 'author' => $str]]],
                'faq' => ['type' => 'array', 'minItems' => 4, 'maxItems' => 4,
                    'items' => ['type' => 'object', 'required' => ['q', 'a'], 'properties' => ['q' => $str, 'a' => $str]]],
            ],
        ];
    }

    /* ─────────────────────────── Site opbouwen ───────────────────────────── */

    /**
     * Mapt de gegenereerde JSON naar de hoofdpagina-blokken. Het gekozen doel bepaalt
     * de hero-CTA's (uit goalProfile) en de AFSLUITENDE sectie: een webshop eindigt met
     * een winkel-CTA i.p.v. de afspraak-widget; overige doelen met de boek-/demo-widget.
     */
    private function blocksFrom(array $d, string $goal = 'website'): array
    {
        $profile = $this->goalProfile($goal);
        $reviews = array_map(fn ($r) => ['stars' => 5, 'text' => $r['text'] ?? '', 'author' => $r['author'] ?? ''], $d['reviews'] ?? []);

        $blocks = [
            ['type' => 'hero', 'key' => 'hero', 'sort' => 10, 'content' => [
                'title' => $d['hero_title'] ?? null,
                'sub' => $d['hero_sub'] ?? null,
                // Hero-CTA's per doel: afspraak, webshop bekijken, inloggen, demo plannen.
                'cta_label' => $profile['hero_cta']['label'], 'cta_href' => $profile['hero_cta']['href'],
                'cta2_label' => $profile['hero_cta2']['label'], 'cta2_href' => $profile['hero_cta2']['href'],
                // Full-width hero: kleur-achtergrond tot het AI-branchebeeld is
                // gegenereerd (parallel, zie fillContent + generateHeroImage). Bewust
                // GEEN eyebrow-pill en GEEN note onder de knoppen.
                'full_bleed' => true,
            ]],
            ['type' => 'features', 'key' => 'diensten-grid', 'sort' => 30, 'content' => [
                'heading' => $d['services_heading'] ?? 'Wat we voor je doen', 'sub' => $d['services_sub'] ?? null,
                'items' => $d['services'] ?? [], 'connected' => true, 'anchor' => 'diensten',
            ]],
            ['type' => 'pricelist', 'key' => 'tarieven', 'sort' => 40, 'content' => [
                'eyebrow' => $goal === 'webshop' ? 'Assortiment' : 'Tarieven',
                'heading' => $d['prices_heading'] ?? 'Onze tarieven',
                'items' => $d['prices'] ?? [], 'punchy' => true, 'anchor' => 'tarieven',
            ]],
            ['type' => 'steps', 'key' => 'werkwijze', 'sort' => 50, 'content' => [
                'heading' => 'Zo werkt het', 'items' => $d['steps'] ?? [], 'playful' => true, 'anchor' => 'werkwijze',
            ]],
            ['type' => 'reviews', 'key' => 'reviews', 'sort' => 70, 'content' => [
                'heading' => 'Wat klanten zeggen', 'items' => $reviews,
                // Google-stijl review-sectie. Score/aantal zijn PLACEHOLDER (net als de
                // reviews zelf) en worden vervangen zodra de ondernemer koppelt.
                'google' => true, 'rating' => '4,9', 'review_count' => 68,
            ]],
            ['type' => 'faq', 'key' => 'faq', 'sort' => 80, 'content' => [
                'heading' => 'Veelgestelde vragen', 'items' => $d['faq'] ?? [], 'punchy' => true,
            ]],
        ];

        // Afsluitende sectie per doel. Webshop = winkel-CTA (de datum/tijd/aanbetaling-
        // widget past niet bij online verkopen); overige doelen = boek-/demo-widget.
        if (($profile['last'] ?? 'booking') === 'cta') {
            $c = $profile['cta'];
            $blocks[] = ['type' => 'cta', 'key' => 'contact', 'sort' => 90, 'content' => [
                'title' => $c['title'], 'sub' => $c['sub'], 'cta_label' => $c['label'], 'cta_href' => $c['href'],
            ]];
        } else {
            $b = $profile['booking'];
            $blocks[] = ['type' => 'booking', 'key' => 'contact', 'sort' => 90, 'content' => [
                'heading' => $b['heading'],
                'sub' => $b['sub'],
                'services' => array_values(array_filter(array_map(fn ($p) => $p['name'] ?? null, $d['prices'] ?? []))),
                'deposit' => $b['deposit'] ?? 0,
            ]];
        }

        // Bewust GEEN wizard/groeipad-blokken: dit is de site van de ondernemer zelf
        // (one-pager), niet onze verkoop-/lead-funnel voor websites.
        return $blocks;
    }

    /* ─────────────────────────── Hero-beeld (AI) ─────────────────────────── */

    /**
     * Genereert asynchroon (vanaf de preview-pagina) een branche-specifiek,
     * full-width hero-beeld via gpt-image-1 en slaat het op onder de preview-key.
     * Medium quality = sneller/goedkoper dan de standaard 'high'. Best-effort: bij
     * een fout blijft de kleur-hero staan (nooit een verkeerd fallback-beeld).
     *
     * @return array{ok:bool,url?:string,error?:string}
     */
    public function generateHeroImage(Site $site): array
    {
        $preview = (array) data_get($site->meta, 'preview', []);
        if (empty($preview['is_preview'])) {
            return ['ok' => false, 'error' => 'geen-preview'];
        }

        $type  = (string) data_get($preview, 'input.business_type', '');
        $color = (string) data_get($preview, 'input.color', '#2563eb');

        $gen = app(ChannelImageGenerator::class);
        $res = $gen->generateRaw($site->key, 'hero', $this->heroPrompt($type, $color), '1536x1024', false, 'medium');

        if (empty($res['file'])) {
            return ['ok' => false, 'error' => $res['status'] ?? 'mislukt'];
        }

        // Bewust GEEN meta-schrijf hier: of het beeld er is, blijkt uit het bestaan
        // van het beeldbestand (ChannelImageGenerator::url → hero rendert 'has-img').
        // Zo botst deze parallelle beeld-call niet met de meta-update van fillContent.
        return ['ok' => true, 'url' => $res['file']];
    }

    /** Art-directed prompt voor een geloofwaardig, branche-specifiek hero-beeld. */
    private function heroPrompt(string $type, string $color): string
    {
        $type  = trim($type) !== '' ? trim($type) : 'lokaal dienstverlenend bedrijf';
        $accent = $this->colorName($color);

        return "A professional, photorealistic wide-angle hero photograph for the website of a Dutch business of this exact type: \"{$type}\". "
            . 'Show a realistic, directly relevant scene for this specific profession: the real workspace, the work being done, or the typical product or service, authentic and modern, in the Netherlands. '
            . 'Editorial lifestyle photography, soft natural daylight, shallow depth of field, warm and inviting, real people at work where fitting. '
            . "Tasteful, subtle {$accent} accents in the scene so it matches the brand, but keep the overall palette natural. "
            . 'Absolutely no text, no words, no letters, no logos, no watermarks, no signage. '
            . '3:2 landscape composition with clear negative space on the left for a website headline overlay.';
    }

    /** Grove hex → Engelse kleurnaam voor in de beeld-prompt. */
    private function colorName(string $hex): string
    {
        [$r, $g, $b] = $this->rgb($hex);
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        if ($max - $min < 30) {
            return $max > 180 ? 'light grey' : ($max < 80 ? 'charcoal' : 'grey');
        }
        // Tint bepalen via hue.
        $h = 0;
        $d = $max - $min;
        if ($max === $r) {
            $h = fmod(60 * (($g - $b) / $d) + 360, 360);
        } elseif ($max === $g) {
            $h = 60 * (($b - $r) / $d) + 120;
        } else {
            $h = 60 * (($r - $g) / $d) + 240;
        }
        return match (true) {
            $h < 20 || $h >= 330 => 'red',
            $h < 45  => 'orange',
            $h < 70  => 'yellow',
            $h < 160 => 'green',
            $h < 200 => 'teal',
            $h < 255 => 'blue',
            $h < 290 => 'purple',
            default  => 'magenta',
        };
    }

    /* ─────────────────────────── Kleur → thema ───────────────────────────── */

    /**
     * Bouwt een compleet, leesbaar thema rond één gekozen primaire kleur. De kleur
     * wordt de accent-/CTA-kleur; tekstkleur op die kleur wordt automatisch wit of
     * donker gekozen op basis van helderheid, zodat het altijd leesbaar blijft.
     */
    private function themeFromColor(string $hex): array
    {
        $onColor = $this->luminance($hex) > 0.55 ? '#0f172a' : '#ffffff';

        return [
            'primary'   => $hex,
            'accent'    => $hex,
            'cta'       => $hex,
            'on_accent' => $onColor,
            'on_cta'    => $onColor,
            'ink'       => '#0f172a',
            'muted'     => '#64748b',
            'bg'        => '#ffffff',
            'surface'   => '#f8fafc',
            'footer_bg' => $this->darken($hex, 0.72),
            'radius'    => '14px',
        ];
    }

    /**
     * Curated sfeer-paletten: elk een harmonieus MULTI-kleuren schema (primaire kleur
     * + tweede accent + getinte surface/tint + donkere footer) met een bijpassende
     * copy-toon en system-font-stack. Deze vervangen de platte 1-kleur-keuze en geven
     * de voorbeeldsites echte levendigheid. on_accent2 is per palet WCAG-gecheckt.
     */
    public const SFEREN = [
        'fris-modern' => [
            'primary' => '#2563eb', 'accent2' => '#06b6d4', 'on_accent2' => '#0f172a',
            'bg' => '#ffffff', 'surface' => '#eff6ff', 'tint' => '#e0f2fe', 'footer' => '#0b1e3b',
            'font_display' => "system-ui, -apple-system, 'Segoe UI', sans-serif",
            'tone' => 'helder, direct en to-the-point, zonder opsmuk',
        ],
        'warm-persoonlijk' => [
            'primary' => '#ea580c', 'accent2' => '#e11d48', 'on_accent2' => '#ffffff',
            'bg' => '#fffdf9', 'surface' => '#fff5ed', 'tint' => '#ffe9dc', 'footer' => '#3d1e12',
            'font_display' => "'Iowan Old Style', Georgia, 'Times New Roman', serif",
            'tone' => 'warm, uitnodigend en persoonlijk, jij-en-ik',
        ],
        'premium-rustig' => [
            'primary' => '#0f766e', 'accent2' => '#b08968', 'on_accent2' => '#241a12',
            'bg' => '#ffffff', 'surface' => '#f5f3ef', 'tint' => '#ece7df', 'footer' => '#08201d',
            'font_display' => "'Iowan Old Style', Georgia, 'Times New Roman', serif",
            'tone' => 'rustig, zelfverzekerd en verzorgd, weinig uitroeptekens',
        ],
        'speels-kleurrijk' => [
            'primary' => '#7c3aed', 'accent2' => '#f59e0b', 'on_accent2' => '#3a2600',
            'bg' => '#ffffff', 'surface' => '#faf5ff', 'tint' => '#f3e8ff', 'footer' => '#2a1350',
            'font_display' => "system-ui, -apple-system, 'Segoe UI', sans-serif",
            'tone' => 'energiek en luchtig, mag een knipoog hebben',
        ],
        'degelijk-betrouwbaar' => [
            'primary' => '#1d4ed8', 'accent2' => '#0f766e', 'on_accent2' => '#ffffff',
            'bg' => '#ffffff', 'surface' => '#f1f5f9', 'tint' => '#e2e8f0', 'footer' => '#0f1e38',
            'font_display' => "system-ui, -apple-system, 'Segoe UI', sans-serif",
            'tone' => 'nuchter en feitelijk, zonder superlatieven',
        ],
        'natuurlijk-rustig' => [
            'primary' => '#15803d', 'accent2' => '#84cc16', 'on_accent2' => '#14290a',
            'bg' => '#ffffff', 'surface' => '#f0fdf4', 'tint' => '#dcfce7', 'footer' => '#0b3d24',
            'font_display' => "'Iowan Old Style', Georgia, 'Times New Roman', serif",
            'tone' => 'natuurlijk, nuchter en betrokken',
        ],
    ];

    /**
     * Bouwt het volledige thema uit een curated sfeer-palet. Optioneel overschrijft de
     * bezoeker de primaire kleur (eigen huisstijl); dan wordt de rest (2e accent, getinte
     * varianten, footer) daaruit afgeleid. Zet ALLE nieuwe tokens (accent_2, on_accent_2,
     * tint, hero_grad_b, step_grad_b), zodat de site levendig en leesbaar rendert.
     */
    public function themeFromPalette(string $sfeer, ?string $ownColor = null): array
    {
        $p = self::SFEREN[$this->normalizeSfeer($sfeer)];

        $primary = $p['primary'];
        $accent2 = $p['accent2'];
        $onAcc2  = $p['on_accent2'];
        $bg      = $p['bg'];
        $surface = $p['surface'];
        $tint    = $p['tint'];
        $footer  = $p['footer'];

        // Eigen-kleur-override: neem de primaire kleur over, leid de rest deterministisch af.
        if ($ownColor !== null && $ownColor !== '') {
            $primary = $this->normalizeHex($ownColor);
            $accent2 = $this->deriveAccent2($primary, $p['accent2']);
            $onAcc2  = $this->luminance($accent2) > 0.55 ? '#0f172a' : '#ffffff';
            $surface = $this->mix($primary, 6);
            $tint    = $this->mix($primary, 12);
            $footer  = $this->darken($primary, 0.7);
            $bg      = '#ffffff';
        }

        $onColor = $this->luminance($primary) > 0.55 ? '#0f172a' : '#ffffff';

        return [
            'primary'      => $primary,
            'accent'       => $primary,
            'cta'          => $primary,
            'on_accent'    => $onColor,
            'on_cta'       => $onColor,
            'accent_2'     => $accent2,
            'on_accent_2'  => $onAcc2,
            'ink'          => '#0f172a',
            'muted'        => '#64748b',
            'bg'           => $bg,
            'surface'      => $surface,
            'tint'         => $tint,
            'footer_bg'    => $footer,
            'hero_grad_b'  => $accent2,   // hero-verloop loopt primary -> 2e accent
            'step_grad_b'  => $accent2,   // step-num-verloop loopt accent -> 2e accent
            'font'         => 'system-ui, -apple-system, sans-serif',
            'font_display' => $p['font_display'],
            'radius'       => '14px',
        ];
    }

    /** Whitelist op geldige sfeer-keys; default = fris-modern. */
    public function normalizeSfeer(string $sfeer): string
    {
        $sfeer = strtolower(trim($sfeer));
        return array_key_exists($sfeer, self::SFEREN) ? $sfeer : 'fris-modern';
    }

    /** Toon-instructie voor de content-prompt, per sfeer. */
    private function sfeerTone(string $sfeer): string
    {
        return self::SFEREN[$this->normalizeSfeer($sfeer)]['tone'];
    }

    /**
     * Leidt een levendig 2e accent af uit de eigen kleur (hue +28 graden, S en L
     * geclampt zodat het altijd fris en leesbaar is). Te grijze bronkleuren (weinig
     * chroma) leveren geen goed 2e accent op; dan valt het terug op het palet-accent.
     */
    private function deriveAccent2(string $hex, string $fallback): string
    {
        [$h, $s, $l] = $this->hexToHsl($hex);
        if ($s < 0.15) {
            return $fallback;
        }
        $h = fmod($h + 28, 360);
        $s = max(0.50, min(0.85, $s));
        $l = max(0.42, min(0.52, $l));
        return $this->hslToHex($h, $s, $l);
    }

    /** Mengt $hex met wit; $pct = percentage kleur (rest wit). Voor getinte surfaces. */
    private function mix(string $hex, float $pct): string
    {
        [$r, $g, $b] = $this->rgb($hex);
        $f = max(0.0, min(1.0, $pct / 100));
        $m = fn ($c) => (int) round($c * $f + 255 * (1 - $f));
        return sprintf('#%02x%02x%02x', $m($r), $m($g), $m($b));
    }

    /** @return array{0:float,1:float,2:float} hue(0-360), sat(0-1), light(0-1) */
    private function hexToHsl(string $hex): array
    {
        [$r, $g, $b] = array_map(fn ($c) => $c / 255, $this->rgb($hex));
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $d = $max - $min;
        $l = ($max + $min) / 2;
        if ($d == 0.0) {
            return [0.0, 0.0, $l];
        }
        $s = $d / (1 - abs(2 * $l - 1));
        $h = match (true) {
            $max === $r => fmod(($g - $b) / $d, 6),
            $max === $g => (($b - $r) / $d) + 2,
            default     => (($r - $g) / $d) + 4,
        };
        return [fmod($h * 60 + 360, 360), $s, $l];
    }

    private function hslToHex(float $h, float $s, float $l): string
    {
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;
        [$r, $g, $b] = match (true) {
            $h < 60  => [$c, $x, 0.0],
            $h < 120 => [$x, $c, 0.0],
            $h < 180 => [0.0, $c, $x],
            $h < 240 => [0.0, $x, $c],
            $h < 300 => [$x, 0.0, $c],
            default  => [$c, 0.0, $x],
        };
        return sprintf('#%02x%02x%02x', (int) round(($r + $m) * 255), (int) round(($g + $m) * 255), (int) round(($b + $m) * 255));
    }

    /** Normaliseert naar #rrggbb; valt terug op een nette blauw bij onzin. */
    private function normalizeHex(string $hex): string
    {
        $hex = ltrim(trim($hex), '#');
        if (preg_match('/^[0-9a-fA-F]{3}$/', $hex)) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return '#2563eb';
        }
        return '#' . strtolower($hex);
    }

    /** Relatieve helderheid (0 = zwart, 1 = wit) volgens sRGB. */
    private function luminance(string $hex): float
    {
        [$r, $g, $b] = $this->rgb($hex);
        $lin = fn ($c) => ($c <= 0.03928) ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        return 0.2126 * $lin($r / 255) + 0.7152 * $lin($g / 255) + 0.0722 * $lin($b / 255);
    }

    /** Maakt een kleur donkerder (factor 0..1 = fractie van het origineel). */
    private function darken(string $hex, float $factor): string
    {
        [$r, $g, $b] = $this->rgb($hex);
        $f = max(0.0, min(1.0, $factor));
        return sprintf('#%02x%02x%02x', (int) round($r * $f), (int) round($g * $f), (int) round($b * $f));
    }

    /** @return array{0:int,1:int,2:int} */
    private function rgb(string $hex): array
    {
        $hex = ltrim($this->normalizeHex($hex), '#');
        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    /* ──────────────────────────────── Doelen ─────────────────────────────── */

    /** Doel-key => label (stuurt de nadruk in de content + latere facet-lazy-load). */
    public const GOALS = [
        'website'        => 'meer klanten en aanvragen via een professionele website',
        'webshop'        => 'producten of diensten online verkopen',
        'klantenportaal' => 'klanten zelf afspraken en documenten laten regelen',
        'automatisering' => 'administratie en processen automatiseren',
        'ai'             => 'AI inzetten (telefoon, chat, offertes)',
    ];

    private function normalizeGoal(string $goal): string
    {
        $goal = strtolower(trim($goal));
        return array_key_exists($goal, self::GOALS) ? $goal : 'website';
    }
}
