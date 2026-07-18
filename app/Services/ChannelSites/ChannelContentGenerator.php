<?php

namespace App\Services\ChannelSites;

use App\Services\Ai\AnthropicClient;
use App\Support\ChannelSite;
use Illuminate\Support\Facades\File;

/**
 * Genereert unieke teksten per channel-site via Claude, tegen duplicate content.
 *
 * Probleem: 213 plaats-pagina's met dezelfde sjabloon-tekst (alleen de stadsnaam
 * gewisseld) concurreren in Google met elkaar. Oplossing: per (kanaal, stad) een
 * unieke intro, met de huisstijl-regels (geen em-dashes, geen holle taal) in de
 * prompt. Opslag als JSON onder storage/app/channel-content/ (geen DB).
 *
 * Fake-mode (geen ANTHROPIC_API_KEY): deterministisch gevarieerde placeholder,
 * zodat de pijplijn + de variatie per stad testbaar is zonder kosten.
 */
class ChannelContentGenerator
{
    public function isFake(): bool
    {
        try {
            return trim((string) app(AnthropicClient::class)->apiKey()) === '';
        } catch (\Throwable $e) {
            return true;
        }
    }

    /** Gecachete unieke intro voor (kanaal, stad), of null. */
    public function placeIntroText(string $channelKey, string $slug): ?string
    {
        $f = $this->path($channelKey, 'places', $slug);
        if (File::exists($f)) {
            $d = json_decode(File::get($f), true);
            return $d['text'] ?? null;
        }
        return null;
    }

    /**
     * Genereert (of hergebruikt) een unieke plaats-intro.
     *
     * @return array{status:string,text:string,fake:bool}
     */
    public function placeIntro(ChannelSite $site, string $slug, string $city, bool $force = false): array
    {
        if (! $force && ($t = $this->placeIntroText($site->key, $slug)) !== null) {
            return ['status' => 'bestaat-al', 'text' => $t, 'fake' => $this->isFake()];
        }

        $service = (string) $site->get('places.service', 'website');
        [$system, $user] = $this->placePrompt($site, $city, $service);

        $text = null;
        try {
            $text = app(AnthropicClient::class)->chat([
                'user' => $user, 'system' => $system, 'max_tokens' => 300,
            ]);
        } catch (\Throwable $e) {
            $text = null;
        }

        $fake = ($text === null || trim($text) === '');
        $text = $fake ? $this->fakeIntro($site, $city, $service) : trim($text);

        $this->store($site->key, 'places', $slug, ['text' => $text, 'city' => $city, 'system' => $system, 'user' => $user]);

        return ['status' => $fake ? 'fake' : 'gegenereerd', 'text' => $text, 'fake' => $fake];
    }

    /* ───────────────────────────── FAQ ──────────────────────────────────── */

    /** Gecachete FAQ-items voor een kanaal, of null. @return array<array{0:string,1:string}>|null */
    public function faqItems(string $channelKey): ?array
    {
        $f = $this->path($channelKey, '', 'faq');
        if (File::exists($f)) {
            $d = json_decode(File::get($f), true);
            return $d['items'] ?? null;
        }
        return null;
    }

    /**
     * Genereert een branche-specifieke FAQ (vraag/antwoord) voor een kanaal.
     *
     * @return array{status:string,items:array<array{0:string,1:string}>,fake:bool}
     */
    public function faq(ChannelSite $site, bool $force = false): array
    {
        if (! $force && ($items = $this->faqItems($site->key)) !== null) {
            return ['status' => 'bestaat-al', 'items' => $items, 'fake' => $this->isFake()];
        }

        $system = 'Je schrijft FAQ-content voor specialist-websites die diensten verkopen aan ondernemers. '
            . 'Toon: helder, eerlijk, vertrouwenwekkend. Regels: Nederlands, GEEN em-dashes, geen holle taal, concreet en in de jij-vorm. '
            . 'Geef ALLEEN geldige JSON terug: een array van objecten met de velden "q" (vraag) en "a" (antwoord, 1 tot 3 zinnen).';
        $user = "Branche: {$site->branche()}. Sitenaam: {$site->name()}. "
            . 'Schrijf 6 veelgestelde vragen die een ondernemer in deze branche stelt over het laten maken van een website, met eerlijke antwoorden. '
            . 'Onderwerpen o.a.: prijs, snelheid/doorlooptijd, wat de klant zelf moet doen, contractduur, bestaande website, werkgebied.';

        $items = null;
        try {
            $raw = app(AnthropicClient::class)->chat(['user' => $user, 'system' => $system, 'max_tokens' => 1200]);
            if ($raw) {
                $json = json_decode($this->stripFences($raw), true);
                if (is_array($json)) {
                    $items = array_values(array_filter(array_map(
                        fn ($x) => (isset($x['q'], $x['a'])) ? [(string) $x['q'], (string) $x['a']] : null,
                        $json
                    )));
                }
            }
        } catch (\Throwable $e) {
            $items = null;
        }

        $fake = empty($items);
        $items = $fake ? $this->fakeFaq($site) : $items;

        $this->store($site->key, '', 'faq', ['items' => $items, 'branche' => $site->branche()]);

        return ['status' => $fake ? 'fake' : 'gegenereerd', 'items' => $items, 'fake' => $fake];
    }

    /* ───────────────────────────── Blog ─────────────────────────────────── */

    /**
     * Genereert een branche-specifiek blog-concept (raakt de DB NIET; de
     * command beslist of het als concept-post wordt weggeschreven).
     *
     * @return array{title:string,slug:string,excerpt:string,body:string,model:string,fake:bool}
     */
    public function blogDraft(ChannelSite $site): array
    {
        $system = 'Je schrijft grondige, praktische blogartikelen voor specialist-websites die diensten aan ondernemers verkopen. '
            . 'Toon: helder, praktisch, vertrouwenwekkend, jij-vorm. '
            . 'Regels: Nederlands. GEEN em-dashes. Geen holle marketingtaal (vermijd ontzorgen, naadloos, op-maat). Vermijd clichématige openers ("Ontdek hoe", "niet alleen ... maar ook") en varieer je zinsopeningen. Concreet met echte voorbeelden, getallen en stappen. '
            . 'Structuur: een korte inleiding met het probleem van de ondernemer, daarna 4 tot 5 <h2>-secties met praktische inhoud, en een korte afsluiting. '
            . 'Geef ALLEEN geldige JSON terug met de velden "title", "excerpt" (1 pakkende zin) en "body" (geldige HTML met <h2> en <p>, 600 tot 800 woorden).';
        $user = "Branche: {$site->branche()}. Sitenaam: {$site->name()}. "
            . 'Schrijf een grondig blogartikel dat een ondernemer in deze branche echt verder helpt, bijvoorbeeld over online gevonden worden, meer klanten krijgen, of een veelgemaakte fout. Geen verkooppraatje, wel diepgang en concrete tips.';

        $model = 'fake';
        $json = null;
        try {
            $model = app(AnthropicClient::class)->writerModel();
            $raw = app(AnthropicClient::class)->chat(['user' => $user, 'system' => $system, 'max_tokens' => 3500]);
            if ($raw) {
                $json = json_decode($this->stripFences($raw), true);
            }
        } catch (\Throwable $e) {
            $json = null;
        }

        $fake = ! (is_array($json) && ! empty($json['title']) && ! empty($json['body']));
        if ($fake) {
            $json = $this->fakeBlog($site);
        }

        $title = (string) $json['title'];
        return [
            'title'   => $title,
            'slug'    => \Illuminate\Support\Str::slug($title) . '-' . substr(md5($site->key . $title), 0, 6),
            'excerpt' => (string) ($json['excerpt'] ?? ''),
            'body'    => (string) $json['body'],
            'model'   => $fake ? 'fake' : (string) $model,
            'fake'    => $fake,
        ];
    }

    /**
     * Blogconcept voor één topic uit de matrix (title + invalshoek + product),
     * uniek per niche via Claude. Fake-mode (geen key) geeft een placeholder.
     *
     * @param array<string,mixed> $topic  slug/title/angle/product
     * @param array<string,mixed> $tokens branche.places (trade/niche)
     */
    public function blogDraftForTopic(ChannelSite $site, array $topic, array $tokens = []): array
    {
        $map = [
            ':trades' => (string) ($tokens['trades'] ?? 'bedrijven'),
            ':trade'  => (string) ($tokens['trade'] ?? 'bedrijf'),
            ':niches' => (string) ($tokens['niches'] ?? 'diensten'),
            ':niche'  => (string) ($tokens['niche'] ?? 'vak'),
        ];
        $title = strtr((string) ($topic['title'] ?? ''), $map);
        $angle = strtr((string) ($topic['angle'] ?? ''), $map);
        $trade = $map[':trade'];

        // Pillar (cornerstone) = langer, diepgaander artikel op een head-term.
        $isPillar  = ! empty($topic['pillar']);
        $product   = (string) ($topic['product'] ?? '');
        $facetUrl  = $product !== '' ? $site->url($product) : '';
        $words     = $isPillar ? '900 tot 1300 woorden' : '600 tot 850 woorden';
        $sections  = $isPillar ? '5 tot 7' : '4 tot 5';
        $tokensMax = $isPillar ? 5000 : 3500;

        $system = 'Je schrijft grondige, praktische blogartikelen voor specialist-websites die websites, webshops, klantenportalen, automatisering en AI verkopen aan ondernemers. '
            . 'Toon: helder, praktisch, vertrouwenwekkend, jij-vorm. '
            . 'Regels: Nederlands. GEEN em-dashes. Geen holle marketingtaal (vermijd ontzorgen, naadloos, op-maat, "in de wereld van"). Vermijd clichématige openers ("Ontdek hoe", "niet alleen ... maar ook") en varieer je zinsopeningen. Wees concreet en branche-specifiek met echte voorbeelden, getallen en stappen. '
            . "Structuur: een korte inleiding die het probleem van de ondernemer benoemt, daarna {$sections} <h2>-secties met praktische inhoud (stappen, voorbeelden, wat het kost of oplevert), en een korte afsluiting. "
            . "Geef ALLEEN geldige JSON terug met \"title\", \"excerpt\" (1 pakkende zin) en \"body\" (geldige HTML met <h2> en <p>, {$words}). Eindig met een zin die uitnodigt tot een gratis voorbeeld.";
        $user = "Branche: {$site->branche()} (een {$trade}). Onderwerp: \"{$title}\". Invalshoek: {$angle}. "
            . 'Schrijf hierover een grondig, branche-specifiek artikel dat deze ondernemer echt verder helpt en subtiel het bijbehorende product laat zien. Geen verkooppraatje, wel diepgang en concrete tips.'
            . ($facetUrl !== '' ? " Verwijs precies één keer, op een natuurlijke plek in de body, naar de productpagina met een link: <a href=\"{$facetUrl}\">passende ankertekst</a>." : '');

        $model = 'fake';
        $json = null;
        try {
            $model = app(AnthropicClient::class)->writerModel();
            $raw = app(AnthropicClient::class)->chat(['user' => $user, 'system' => $system, 'max_tokens' => $tokensMax]);
            if ($raw) {
                $json = json_decode($this->stripFences($raw), true);
            }
        } catch (\Throwable $e) {
            $json = null;
        }

        $fake = ! (is_array($json) && ! empty($json['title']) && ! empty($json['body']));
        if ($fake) {
            $json = ['title' => $title, 'excerpt' => '', 'body' => "<h2>{$title}</h2><p>Concept nog te genereren (geen Anthropic-key in deze omgeving).</p>"];
        }

        return [
            'slug'    => (string) ($topic['slug'] ?? \Illuminate\Support\Str::slug($title)),
            'title'   => (string) $json['title'],
            'excerpt' => (string) ($json['excerpt'] ?? ''),
            'body'    => (string) $json['body'],
            'model'   => $fake ? 'fake' : (string) $model,
            'fake'    => $fake,
        ];
    }

    private function fakeBlog(ChannelSite $site): array
    {
        $b = $site->branche();
        return [
            'title'   => 'Zo word je online gevonden in jouw vak',
            'excerpt' => 'Drie praktische stappen waarmee je als ondernemer meer klanten via Google binnenhaalt.',
            'body'    => "<h2>Begin bij wat klanten echt zoeken</h2><p>Klanten zoeken niet op vaktermen, maar op hun probleem en hun plaats. Schrijf je website zoals zij praten, en noem de plaatsen waar je werkt.</p>"
                . "<h2>Maak bellen en aanvragen makkelijk</h2><p>Een duidelijke belknop en een kort formulier op elke pagina zorgen dat mensen je bereiken op het moment dat het telt, ook buiten kantoortijd.</p>"
                . "<h2>Bouw vertrouwen op</h2><p>Laat je werk, je werkgebied en echte reviews zien. Wie je vooraf vertrouwt, belt sneller. (Concept gegenereerd voor branche: {$b}.)</p>",
        ];
    }

    /** Verwijdert ```json ... ``` codefences die modellen er soms omheen zetten. */
    private function stripFences(string $s): string
    {
        return trim(preg_replace('/^```[a-z]*\s*|\s*```$/m', '', trim($s)));
    }

    /** Branche-neutrale degelijke FAQ als fallback (fake-mode). */
    private function fakeFaq(ChannelSite $site): array
    {
        return [
            ['Wat kost een website?', 'Je betaalt een vast, laag maandbedrag inclusief hosting, onderhoud en updates. Je weet de prijs vooraf, geen verrassingen.'],
            ['Hoe snel sta ik online?', 'Binnen 2 werkdagen zie je een gratis voorbeeld. Na je akkoord staat de site doorgaans binnen 2 weken live.'],
            ['Moet ik zelf iets met techniek doen?', 'Nee. Wij regelen techniek, hosting en vindbaarheid. Jij levert wat foto\'s en info aan, de rest doen wij.'],
            ['Zit ik vast aan een lang contract?', 'Nee, geen meerjarencontract. Een vast bedrag per maand, maandelijks opzegbaar.'],
            ['Ik heb al een website. Kan dat beter?', 'Ja. We maken een voorbeeld van hoe het scherper en beter vindbaar kan, zonder dat je opnieuw begint.'],
            ['Werken jullie in mijn regio?', 'We werken door heel Nederland en stemmen je werkgebied precies af zodat je lokaal goed gevonden wordt.'],
        ];
    }

    /** @return array{0:string,1:string} [system, user] */
    private function placePrompt(ChannelSite $site, string $city, string $service): array
    {
        $system = 'Je schrijft korte, unieke, lokale marketingteksten voor specialist-websites. '
            . 'Toon: helder, zakelijk-warm, vertrouwenwekkend. Regels: Nederlands, GEEN em-dashes (gebruik komma of punt), '
            . 'geen gate-keeping, geen holle marketingtaal (vermijd ontzorgen, naadloos, op-maat-clichés). '
            . 'Concreet, jij-vorm, lokaal (noem de plaats natuurlijk). Twee korte zinnen, maximaal 45 woorden. Geef ALLEEN de tekst terug.';
        $user = "Branche: {$site->branche()}. Sitenaam: {$site->name()}. Dienst: {$service}. Plaats: {$city}. "
            . "Schrijf een unieke intro voor de plaatspagina van {$city}, duidelijk anders dan voor andere steden. "
            . 'Verwerk iets lokaals of concreets en eindig met de gratis-voorbeeld-belofte.';
        return [$system, $user];
    }

    /** Deterministisch gevarieerde placeholder per stad (fake-mode). */
    private function fakeIntro(ChannelSite $site, string $city, string $service): string
    {
        $angles = [
            'Ondernemers in {city} die online gevonden willen worden, kiezen voor een {service} die werkt. Vraag vrijblijvend een gratis voorbeeld aan voor je zaak in {city}.',
            'In {city} draait het om vertrouwen en vindbaarheid. Wij maken een {service} die nieuwe klanten naar je toe stuurt, met vooraf een gratis voorbeeld.',
            'Een {service} voor je bedrijf in {city}, gemaakt voor de lokale markt en goed vindbaar in Google. Bekijk eerst een gratis voorbeeld, zonder verplichting.',
        ];
        $i = crc32($site->key . '|' . $city) % count($angles);
        return strtr($angles[$i], ['{city}' => $city, '{service}' => $service]);
    }

    private function path(string $key, string $type, string $slug): string
    {
        $sub = $type !== '' ? "{$type}/" : '';
        return storage_path("app/channel-content/{$key}/{$sub}{$slug}.json");
    }

    private function store(string $key, string $type, string $slug, array $data): void
    {
        $f = $this->path($key, $type, $slug);
        File::ensureDirectoryExists(dirname($f));
        File::put($f, json_encode($data + ['generated_at' => now()->toIso8601String()], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
