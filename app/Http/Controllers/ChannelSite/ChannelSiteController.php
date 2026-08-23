<?php

namespace App\Http\Controllers\ChannelSite;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Blog\BlogPost;
use App\Models\WebsiteLead;
use App\Support\ChannelSite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Serveert alle pagina's van een channel-site. De actieve ChannelSite is door
 * de ResolveChannelSite-middleware in de container gezet + als $site gedeeld.
 */
class ChannelSiteController extends Controller
{
    private function site(): ChannelSite
    {
        return app(ChannelSite::class);
    }

    public function home(Request $request): View
    {
        $site = $this->site();

        // Twee-lagen-model: als er een betergeregeld-verkooppagina voor deze site
        // bestaat, is DÍE de hoofdpagina (pitch aan de ondernemer). De blok-gedreven
        // demo ("zo zou jouw site eruitzien") verhuist dan naar /voorbeeld.
        if ($salesView = $site->salesHomeView()) {
            $facet   = WebsiteLead::normalizeFacet($request->route('facet'));
            // Facet-content per channel: channel_landings mapt de site-key naar het
            // juiste landings-config-bestand (badkamer, rijschool, …).
            $landingCfg = config('channel_landings.' . $site->key);
            $landing    = ($request->route('facet') && $landingCfg) ? config($landingCfg . '.' . $facet) : null;

            // /{facet} met een bestaande productlanding + landing-view → de
            // toegespitste trigger-pagina voor dat product (waar ads/SEO op landen).
            if ($landing && ($landingView = $site->landingView())) {
                return view($landingView, [
                    'site'    => $site,
                    'facet'   => $facet,
                    'landing' => $landing,
                    'facets'  => (array) config('groeidiamant.facets', []),
                ]);
            }

            // / (of onbekende facet) → de brede overzichts-home.
            return view($salesView, [
                'site'   => $site,
                'facets' => (array) config('groeidiamant.facets', []),
            ]);
        }

        // Anders: de site is zelf de triggersite (legacy) — demo op de hoofd-URL.
        return $this->renderDemo($request, '');
    }

    /** De blok-/config-gedreven demo op /voorbeeld (framing-balk + facet-switcher). */
    public function demo(Request $request): View
    {
        return $this->renderDemo($request, 'voorbeeld/');
    }

    /**
     * Rendert de demo-pagina. $facetBase bepaalt onder welke URL de facet-links
     * lopen ('' als de demo zélf de home is, 'voorbeeld/' als aparte pagina).
     */
    private function renderDemo(Request $request, string $facetBase): View
    {
        $site  = $this->site();
        $facet = WebsiteLead::normalizeFacet($request->route('facet'));
        $home  = array_replace((array) $site->get('home', []), (array) $site->get('facets.' . $facet, []));

        return view($site->homeView(), [
            'site'      => $site,
            'facet'     => $facet,
            'home'      => $home,
            'facets'    => (array) config('groeidiamant.facets', []),
            'facetBase' => $facetBase,
            'isDemo'    => $facetBase !== '',
        ]);
    }

    /** Alleen de facet-afhankelijke blokken — voor de live (AJAX) fase-switch. */
    public function homeFragment(Request $request): View
    {
        return $this->demoFragment($request, '');
    }

    public function demoFragmentRoute(Request $request): View
    {
        return $this->demoFragment($request, 'voorbeeld/');
    }

    private function demoFragment(Request $request, string $facetBase): View
    {
        $site  = $this->site();
        $facet = WebsiteLead::normalizeFacet($request->route('facet'));
        $facets = (array) config('groeidiamant.facets', []);

        // Blok-gedreven site → de DB-blokken (zonder wizard); anders de config-zone.
        if ($site->hasBlocks()) {
            return view('channels.partials.blocks-fragment', compact('site', 'facet', 'facets', 'facetBase'));
        }

        $home = array_replace((array) $site->get('home', []), (array) $site->get('facets.' . $facet, []));

        return view('channels.partials.facet-zone', compact('site', 'facet', 'home', 'facets', 'facetBase'));
    }

    public function about(): View
    {
        return view('channels.about', ['site' => $this->site()]);
    }

    public function contact(): View
    {
        return view('channels.contact', ['site' => $this->site()]);
    }

    /**
     * Zelf een kennismaking inplannen. Losse pagina omdat /contact op sommige
     * kanalen (o.a. bedrijfswebsite) bewust geblokkeerd is, terwijl de
     * afsprakenwidget daar juist wél de bestemming is van CTA's, zoals die
     * onderaan een gegenereerde preview.
     */
    public function appointment(): View
    {
        return view('channels.afspraak', ['site' => $this->site()]);
    }
    public function appointmentConfirmed(): View
    {
        // 'confirmed' = kwam hier na een echte boeking (server-flash uit AppointmentController::book),
        // niet via refresh of direct bezoek. Zo telt de widget de conversie precies één keer.
        return view('channels.afspraak-bevestigd', [
            'site'      => $this->site(),
            'confirmed' => (bool) session('appointment_confirmed'),
        ]);
    }

    public function services(): View
    {
        return view('channels.services', ['site' => $this->site()]);
    }

    public function groeidiamant(): View
    {
        return view('channels.groeidiamant', ['site' => $this->site()]);
    }

    public function pricing(): View
    {
        return view('channels.pricing', ['site' => $this->site()]);
    }

    public function werkwijze(): View
    {
        return view('channels.werkwijze', ['site' => $this->site()]);
    }

    public function cases(): View
    {
        return view('channels.cases', ['site' => $this->site()]);
    }

    public function faq(): View
    {
        return view('channels.faq', ['site' => $this->site()]);
    }

    public function vergelijken(): View
    {
        return view('channels.vergelijken', ['site' => $this->site()]);
    }

    public function privacy(): View
    {
        return view('channels.legal.privacy', ['site' => $this->site()]);
    }

    public function cookies(): View
    {
        return view('channels.legal.cookies', ['site' => $this->site()]);
    }

    public function terms(): View
    {
        return view('channels.legal.terms', ['site' => $this->site()]);
    }

    /* ───────────────────────────── Plaatsen ──────────────────────────────── */

    public function places(): View
    {
        $site = $this->site();

        return view($site->placeView('index'), [
            'site'      => $site,
            'provinces' => app(\App\Services\ChannelSiteResolver::class)->provinces(),
        ]);
    }

    public function province(Request $request): View
    {
        $prov     = (string) $request->route('prov');
        $resolver = app(\App\Services\ChannelSiteResolver::class);
        $name     = $resolver->provinceName($prov);
        abort_if($name === null, 404);

        return view($this->site()->placeView('province'), [
            'site'       => $this->site(),
            'provName'   => $name,
            'provSlug'   => $prov,
            'provPlaces' => $resolver->provincePlaces($prov),
        ]);
    }

    public function place(Request $request): View
    {
        // By-name lezen i.p.v. method-injectie: de preview-route heeft een extra
        // {channelKey}-param vóór {place}, wat anders positioneel zou binden.
        $slug     = (string) $request->route('place');
        $site     = $this->site();
        $resolver = app(\App\Services\ChannelSiteResolver::class);
        $data     = $resolver->placeData($slug);
        abort_if($data === null, 404);

        // Unieke, branche-gerichte content (variatie-engine) voor deze plaats.
        $tokens  = (array) $site->get('places', []);
        $content = app(\App\Services\ChannelSites\ChannelPlaceContent::class)->assemble($tokens, $data, $site->key . '|');

        // Branche-tokens (niet plaats) alvast in de bedrijven-config invullen.
        $business = array_merge((array) config('channel_places.business', []), (array) ($tokens['business'] ?? []));
        $brancheMap = [
            ':trades'  => (string) ($tokens['trades'] ?? config('channel_places.defaults.trades')),
            ':trade'   => (string) ($tokens['trade'] ?? config('channel_places.defaults.trade')),
            ':niches'  => (string) ($tokens['niches'] ?? config('channel_places.defaults.niches')),
            ':niche'   => (string) ($tokens['niche'] ?? config('channel_places.defaults.niche')),
        ];
        $business['query'] = strtr((string) ($business['query'] ?? ':niche :city'), $brancheMap);
        $business['label'] = str_replace([':city', ':region'], [$data['naam'], $data['provincie']], strtr((string) ($business['label'] ?? ''), $brancheMap));
        $business['intro'] = str_replace([':city', ':region'], [$data['naam'], $data['provincie']], strtr((string) ($business['intro'] ?? ''), $brancheMap));

        // Echte lokale bedrijven (Google Places, cache-first). Faalt zacht → [].
        $businesses = app(\App\Services\ChannelSites\PlaceBusinessFinder::class)
            ->forPlace((string) $site->brancheKey(), $data['slug'], $data['naam'], $data['provincie'], $business);

        // Echte feiten bij deze plaats: gemeente, coördinaten, afstand tot de
        // vestiging en de werkelijke buurplaatsen (channel_place_facts, gevuld
        // door `php artisan channel:places-enrich` uit de PDOK-locatieserver).
        // Ontbreekt de rij, dan valt alles hieronder terug op het oude gedrag.
        $facts = null;
        try {
            $rij = \Illuminate\Support\Facades\DB::table('channel_place_facts')->where('slug', $data['slug'])->first();
            if ($rij && $rij->bron !== 'onbekend') {
                $facts = [
                    'gemeente'   => $rij->gemeente,
                    'provincie'  => $rij->provincie,
                    'afstand_km' => $rij->afstand_km !== null ? (int) $rij->afstand_km : null,
                    'inwoners'   => $rij->inwoners !== null ? (int) $rij->inwoners : null,
                    'buren'      => array_values(array_filter(explode(',', (string) $rij->buren))),
                ];
            }
        } catch (\Throwable $e) {
            $facts = null;   // tabel bestaat nog niet → pagina werkt gewoon door
        }

        // Nabije plaatsen — bij voorkeur de écht dichtstbijzijnde (uit de
        // coördinaten), anders het oude gedrag: alles uit dezelfde provincie.
        // Echte buren maken de interne links zinniger: Wessem verwijst dan naar
        // buurdorpen en niet naar een plaats 80 km verderop in dezelfde provincie.
        $names   = $resolver->places();
        $nearby  = [];
        if ($facts && $facts['buren']) {
            foreach ($facts['buren'] as $bSlug) {
                if (isset($names[$bSlug])) $nearby[$bSlug] = $names[$bSlug];
            }
        }
        if (! $nearby) {
            foreach ($resolver->regions() as $pSlug => $prov) {
                if ($pSlug === $data['slug'] || $prov !== $data['provincie']) {
                    continue;
                }
                $nearby[$pSlug] = $names[$pSlug] ?? $pSlug;
            }
            asort($nearby);
        }

        // SEO-gating: genoeg echte bedrijven ÉN een woonplaats van voldoende schaal.
        // Die tweede eis is de scherpe: het aantal bedrijven is door Places afgekapt op 9,
        // dus daarmee viel niets te selecteren (zie PlaceBusinessFinder::groteGenoegPlaats).
        // Dezelfde regel als in de sitemap, en bewust via dezelfde methode zodat pagina en
        // sitemap niet uiteen kunnen lopen — een pagina op noindex die tóch in de sitemap
        // staat is precies het signaal dat je niet wil geven.
        $indexable = count($businesses) >= (int) config('channel_places.index_min_businesses', 3)
            && app(\App\Services\ChannelSites\PlaceBusinessFinder::class)->groteGenoegPlaats($data['slug']);

        return view($site->placeView('show'), [
            'site'       => $site,
            'place'      => $data,
            'placeName'  => $data['naam'],
            'placeSlug'  => $data['slug'],
            'content'    => $content,
            'business'   => $business,
            'businesses' => $businesses,
            'nearby'     => $nearby,
            'indexable'  => $indexable,
            'facts'      => $facts,
        ]);
    }

    /* ─────────────────────────────── Blog ────────────────────────────────── */

    public function blogIndex(): View
    {
        $site = $this->site();
        $posts = BlogPost::query()
            ->forChannel($site->key)
            ->published()
            ->with('category')
            ->orderByDesc('featured')
            ->orderByDesc('published_at')
            ->paginate(12);

        return view($site->blogView('index'), ['site' => $site, 'posts' => $posts]);
    }

    public function blogShow(Request $request): View
    {
        $slug = (string) $request->route('slug');
        $site = $this->site();
        $post = BlogPost::query()
            ->forChannel($site->key)
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return view($site->blogView('show'), ['site' => $site, 'post' => $post]);
    }

    /* ─────────────────────────────── SEO ─────────────────────────────────── */

    /** Per-kanaal sitemap met home, over-ons, plaatsen, plaats-pagina's en blog. */
    public function sitemap(): \Illuminate\Http\Response
    {
        $site = $this->site();

        $resolver = app(\App\Services\ChannelSiteResolver::class);

        // Vaste content-pagina's: home, de Groeidiamant-facetlandingen (belangrijkste
        // SEO-pagina's), en alle informatieve pagina's.
        // 'afspraak' hoort hier thuis: het is een echte, indexeerbare pagina en op dit
        // kanaal het einddoel van de trechter, maar hij stond in geen enkele sitemap.
        // (/voorbeeld-maken staat er bewust NIET in: die is noindex,nofollow.)
        $paths = ['', 'over-ons', 'contact', 'diensten', 'groeidiamant', 'prijzen', 'werkwijze', 'cases',
            'veelgestelde-vragen', 'vergelijken', 'plaatsen', 'blog', 'afspraak'];
        foreach (array_keys((array) config('groeidiamant.facets', [])) as $facet) {
            $paths[] = $facet;
        }
        // Per-kanaal geblokkeerde pagina's (config/channel_page_blocklist.php) geven
        // een 404 (BlockChannelPages) en horen dus niet in de sitemap.
        if ($blocked = (array) config('channel_page_blocklist.' . $site->key, [])) {
            $paths = array_values(array_filter($paths, fn ($p) => ! in_array($p, $blocked, true)));
        }
        $urls = array_map(fn ($p) => ['loc' => $site->url($p)], $paths);

        // Provincie-overzichten + alleen "sterke" plaatsen (genoeg echte bedrijven);
        // dunne plaatsen staan op noindex en horen dus niet in de sitemap.
        foreach ($resolver->provinces() as $prov) {
            $urls[] = ['loc' => $site->url('plaatsen/provincie/' . $prov['slug'])];
        }
        $min = (int) config('channel_places.index_min_businesses', 3);
        $strong = app(\App\Services\ChannelSites\PlaceBusinessFinder::class)->indexableSlugs($site->brancheKey(), $min);
        // Geen cache-data (nog niet gewarmd)? Val terug op alle plaatsen zodat de
        // sitemap niet leeg is; anders alleen de sterke plaatsen.
        $placeSlugs = $strong ?: array_keys($resolver->places());
        foreach ($placeSlugs as $slug) {
            $urls[] = ['loc' => $site->url('plaatsen/' . $slug)];
        }

        // Blogposts met lastmod (helpt Google verse content sneller oppikken).
        try {
            foreach (BlogPost::query()->forChannel($site->key)->published()->get(['slug', 'updated_at', 'published_at']) as $post) {
                $urls[] = [
                    'loc'     => $site->url('blog/' . $post->slug),
                    'lastmod' => optional($post->updated_at ?? $post->published_at)->toDateString(),
                ];
            }
        } catch (\Throwable $e) {
            // Blog (nog) niet beschikbaar — sitemap blijft geldig zonder blogposts.
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= '  <url><loc>' . htmlspecialchars($u['loc'], ENT_XML1) . '</loc>'
                . (! empty($u['lastmod']) ? '<lastmod>' . $u['lastmod'] . '</lastmod>' : '')
                . '</url>' . "\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /** Per-kanaal robots.txt. Concept = niet indexeren; live = indexeren + sitemap. */
    public function robots(): \Illuminate\Http\Response
    {
        $site = $this->site();

        $lines = $site->isLive()
            ? ['User-agent: *', 'Allow: /', '', 'Sitemap: ' . $site->url('sitemap.xml')]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines) . "\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /**
     * Per-kanaal llms.txt (llmstxt.org): een curated markdown-samenvatting speciaal
     * voor AI-antwoordmachines (ChatGPT, Perplexity, Google AI Overviews) — GEO.
     * Bevat de kern, de diensten (facets) met links, en contactgegevens.
     */
    public function llmsTxt(): \Illuminate\Http\Response
    {
        $site = $this->site();
        if (! $site->isLive()) {
            return response("# {$site->displayName()}\n\n> Nog niet live.\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
        }

        $base   = rtrim($site->baseUrl(), '/');
        $facets = (array) config('groeidiamant.facets', []);

        $md   = [];
        $md[] = "# {$site->displayName()}";
        $md[] = '';
        $md[] = '> ' . $site->metaDescription();
        $md[] = '';
        $md[] = 'Beter Geregeld ICT bouwt websites, webshops, klantenportalen, automatisering en AI voor mkb-ondernemers en vakmensen in heel Nederland. Vaste prijs vooraf, een vast aanspreekpunt, alles telefonisch en online geregeld. Typ je bedrijfsnaam voor een gratis voorbeeld.';
        $md[] = '';
        $md[] = '## Diensten';
        foreach ($facets as $key => $f) {
            $md[] = "- [{$f['label']}]({$base}/{$key}): " . ($f['tagline'] ?? '');
        }
        $md[] = '';
        $md[] = '## Meer informatie';
        $md[] = "- [Blog]({$base}/blog): praktische artikelen over online gevonden worden, meer klanten krijgen en slim automatiseren";
        $md[] = "- [Werkgebied]({$base}/plaatsen): actief in heel Nederland, met een pagina per plaats";
        // Levertijd volgt de modus van dit kanaal: een generator belooft een minuut,
            // een aanvraag belooft een werkdag. Beide staan in llms.txt, dus hier ook.
            $voorbeeldBelofte = in_array($site->key, (array) config('voorbeeld_aanvraag.kanalen', []), true)
                ? 'beantwoord een paar vragen en krijg ' . config('voorbeeld_aanvraag.levertijd', 'binnen 1 werkdag') . ' een voorbeeld van je eigen website'
                : 'typ je bedrijfsnaam en zie binnen een minuut een concept';
            $md[] = "- [Gratis voorbeeld]({$base}/voorbeeld-maken): {$voorbeeldBelofte}";
        $md[] = "- [Afspraak plannen]({$base}/afspraak): online of telefonisch, gratis en vrijblijvend";
        $md[] = '';
        $md[] = '## Contact';
        if ($p = $site->brand('phone')) { $md[] = "- Telefoon: {$p}"; }
        if ($e = $site->brand('email')) { $md[] = "- E-mail: {$e}"; }
        if ($addr = (array) $site->brand('address')) {
            $md[] = '- Adres: ' . trim(($addr['street'] ?? '') . ', ' . ($addr['postal'] ?? '') . ' ' . ($addr['city'] ?? ''), ', ');
        }
        $md[] = '';

        return response(implode("\n", $md), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /* ─────────────────────────────── Lead ────────────────────────────────── */

    public function leadStore(Request $request): RedirectResponse
    {
        $site = $this->site();

        // Honeypot.
        if (filled($request->input('website'))) {
            return redirect($site->url('bedankt'));
        }

        $data = $request->validate([
            'company'          => ['nullable', 'string', 'max:190'],
            'contact_name'     => ['required', 'string', 'max:120'],
            'email'            => ['required', 'email', 'max:190'],
            'phone'            => ['required', 'string', 'max:60'],
            'city'             => ['nullable', 'string', 'max:120'],
            'current_website'  => ['nullable', 'string', 'max:190'],
            'message'          => ['nullable', 'string', 'max:4000'],
            'facet'            => ['nullable', 'string', 'max:40'],
            // Conversational wizard (lead-wizard.blade): vrije kwalificatie-antwoorden.
            'goal'             => ['nullable', 'string', 'max:60'],
            'style'            => ['nullable', 'string', 'max:60'],
            'timing'           => ['nullable', 'string', 'max:60'],
            'features'         => ['nullable', 'array'],
            'features.*'       => ['string', 'max:60'],
            'appointment_type' => ['nullable', 'in:onsite,meet'],
            'appointment_note' => ['nullable', 'string', 'max:500'],
            // after:now sluit alleen het triviale geval af (oude of gemanipuleerde
            // POST met een moment in het verleden). Of het moment écht vrij is,
            // beslist BookingService, dat is het enige punt waar dat waar blijft.
            'appointment_at'   => ['nullable', 'date', 'after:now'],
        ], [], [
            'contact_name' => 'naam', 'email' => 'e-mail', 'phone' => 'telefoon',
        ]);

        // Groeidiamant-fase: expliciet meegestuurd, maar het doel "webshop" wint.
        $facet = WebsiteLead::normalizeFacet($data['facet'] ?? null);
        if (($data['goal'] ?? null) === 'webshop') {
            $facet = WebsiteLead::normalizeFacet('webshop');
        }

        // Kwalificatie-antwoorden bundelen in answers (json) voor de admin.
        $answers = array_filter([
            'goal'             => $data['goal'] ?? null,
            'style'            => $data['style'] ?? null,
            'timing'           => $data['timing'] ?? null,
            'appointment_note' => $data['appointment_note'] ?? null,
        ], fn ($v) => filled($v));

        $appointmentType = $data['appointment_type'] ?? null;

        // Self-service voorbeeldsite: komt de lead van een gegenereerde preview-site
        // (key preview-...), attribueer 'm dan aan het bron-kanaal en koppel de al
        // bestaande preview (URL + status 'ready'). Het bron-kanaal + de invoer
        // staan in de preview-meta die PreviewSiteGenerator heeft weggeschreven.
        $isPreview     = str_starts_with($site->key, 'preview-');
        $sourceChannel = $isPreview
            ? (string) ($site->get('meta.preview.source_channel') ?: $site->key)
            : $site->key;
        $previewUrl = $isPreview ? url('/_site/' . $site->key) : null;
        if ($isPreview && ($bt = $site->get('meta.preview.input.business_type'))) {
            $answers['type_bedrijf'] = $bt;
        }

        $lead = WebsiteLead::create([
            'company'            => $data['company'] ?? null,
            'branche'            => $site->branche(),
            'facet'              => $facet,
            'contact_name'       => $data['contact_name'],
            'email'              => $data['email'],
            'phone'              => $data['phone'],
            'city'               => $data['city'] ?? null,
            'current_website'    => $data['current_website'] ?? null,
            'message'            => $data['message'] ?? null,
            'answers'            => $answers ?: null,
            'features'           => ! empty($data['features']) ? array_values($data['features']) : null,
            'appointment_type'   => $appointmentType,
            'appointment_status' => $appointmentType ? 'requested' : null,
            'channel'            => $sourceChannel,
            'source'             => $isPreview ? 'preview' : 'channel',
            'status'             => 'new',
            'preview_status'     => $isPreview ? 'ready' : 'todo',
            'preview_url'        => $previewUrl,
        ]);

        // De preview is nu aangevraagd: markeer 'm claimed zodat de cleanup 'm laat
        // staan voor de opvolging.
        if ($isPreview) {
            \App\Models\Channel\Site::where('key', $site->key)
                ->get()
                ->each(function ($m) {
                    $meta = (array) $m->meta;
                    $meta['preview']['claimed'] = true;
                    $m->update(['meta' => $meta]);
                });
        }

        // Online-via-Meet + een gekozen moment → meteen een echte afspraak boeken
        // (agenda-event + Google Meet-link + bevestigingsmail via BookingService).
        $appointment = null;
        $appointmentFailed = false;
        if ($appointmentType === 'meet' && filled($data['appointment_at'] ?? null)) {
            try {
                $appointment = app(\App\Services\Scheduling\BookingService::class)->book([
                    'name'        => $data['contact_name'],
                    'email'       => $data['email'],
                    'phone'       => $data['phone'],
                    'starts_at'   => $data['appointment_at'],
                    'source_site' => $site->key,
                    'note'        => 'Via gratis-voorbeeld-aanvraag' . (! empty($data['company']) ? ' — ' . $data['company'] : ''),
                ]);
                // Het MOMENT moet mee, niet alleen de status: BookingService::leadFor()
                // koppelt een afspraak terug aan de lead op e-mail + appointment_at.
                // Zonder appointment_at vindt annuleren/verzetten deze lead nooit en
                // blijft hij op 'booked' staan terwijl de afspraak al weg is.
                $lead->update([
                    'appointment_status' => $appointment->leadAppointmentStatus(),
                    'appointment_at'     => $appointment->starts_at,
                    'meet_link'          => $appointment->meet_url,
                    'google_event_id'    => $appointment->google_event_id,
                ]);
            } catch (\App\Services\Scheduling\SlotTakenException $e) {
                // Niet terug naar het formulier met withErrors(): de lead staat er
                // hierboven al in, dus opnieuw verzenden geeft een dubbele rij. De
                // aanvraag zelf is gelukt, alleen het moment viel weg. Daarom door
                // naar de bedankpagina, maar mét de waarheid: de bezoeker kreeg
                // "Gekozen: di 21 jul om 11:00" te zien en mag niet in de veronder-
                // stelling blijven dat die afspraak staat. Lead blijft 'requested'.
                $appointmentFailed = true;
            } catch (\Throwable $e) {
                // Onverwachte fout (agenda, mail, DB): voor de bezoeker precies even
                // erg, want ook dan is er geen afspraak. Zelfde eerlijke terugkoppeling.
                report($e);
                $appointmentFailed = true;
            }
        }

        $this->notifyInternal($lead, $site, $appointment);

        // De bedankpagina zweeg over de afspraak, waardoor geslaagd en mislukt er
        // identiek uitzagen. Beide gevallen krijgen nu hun eigen boodschap mee.
        return redirect($site->url('bedankt'))->with('appointment', [
            'failed' => $appointmentFailed,
            'label'  => $appointment
                ? \App\Support\Intake\AppointmentSlots::labelFor($appointment->starts_at->format('Y-m-d H:i'))
                : null,
        ]);
    }

    public function leadSent(): View
    {
        return view('channels.lead-sent', ['site' => $this->site()]);
    }

    /**
     * Vangnet voor onbekende paden op een channel-domein. Retourneert een 404 in
     * de eigen huisstijl i.p.v. door te vallen naar de hoofd-site (betergeregeld.com);
     * zie de catch-all onderaan de channel-routegroep in routes/channels.php.
     */
    public function notFound(): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
    {
        $site = $this->site();

        // Soft-404-kanalen: onbekende URL's gaan naar de homepage i.p.v. een foutpagina.
        if ($site->redirectsNotFoundToHome()) {
            return redirect($site->url(''), 302);
        }

        return response()->view('channels.not-found', ['site' => $site], 404);
    }

    private function notifyInternal(WebsiteLead $lead, ChannelSite $site, ?Appointment $appointment = null): void
    {
        try {
            // scheduling.notify_email en niet mail.from.address: dat laatste is een
            // afzender, geen postbus. Op de voorbeeldwaarde verdween deze melding stil.
            $to = (string) config('scheduling.notify_email');
            if ($to === '') {
                return;
            }
            $branche = WebsiteLead::BRANCHES[$lead->branche] ?? $lead->branche;

            // Stond er een afspraak in? Dan is dát het nieuws, niet "er is een lead".
            // Zonder tijdstip en Meet-link moest je de admin in om te zien of je
            // vanmiddag ergens verwacht werd.
            $afspraak = '';
            if ($appointment) {
                $tz = (string) config('scheduling.timezone', 'Europe/Amsterdam');
                $afspraak = "AFSPRAAK: " . $appointment->starts_at->copy()->setTimezone($tz)->format('d-m-Y H:i') . "\n"
                    . 'Meet: ' . ($appointment->meet_url ?: '— (nog geen link!)') . "\n\n";
            }

            $onderwerp = $appointment
                ? "Nieuwe afspraak ({$site->key}): " . ($lead->company ?: $lead->contact_name)
                : "Nieuwe lead ({$site->key}): " . ($lead->company ?: $lead->contact_name);

            Mail::raw(
                ($appointment ? "Nieuwe afspraak via channel-site.\n\n" : "Nieuwe lead via channel-site.\n\n")
                . $afspraak
                . "Site: {$site->name()} ({$site->key})\n"
                . "Branche: {$branche}\n"
                . "Naam: {$lead->contact_name}\n"
                . "Bedrijf: " . ($lead->company ?: '—') . "\n"
                . "Contact: {$lead->email} · {$lead->phone}\n"
                . "Plaats: " . ($lead->city ?: '—') . "\n"
                . "Bericht: " . ($lead->message ?: '—') . "\n\n"
                . "Opvolgen in de admin → Website-leads.",
                fn ($m) => $m->to($to)->subject($onderwerp)
            );
        } catch (\Throwable $e) {
            Log::warning('channel_lead_mail: ' . $e->getMessage());
        }
    }
}
