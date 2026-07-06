<?php

namespace App\Http\Controllers\ChannelSite;

use App\Http\Controllers\Controller;
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
        return view('channels.places.index', [
            'site'      => $this->site(),
            'provinces' => app(\App\Services\ChannelSiteResolver::class)->provinces(),
        ]);
    }

    public function province(Request $request): View
    {
        $prov     = (string) $request->route('prov');
        $resolver = app(\App\Services\ChannelSiteResolver::class);
        $name     = $resolver->provinceName($prov);
        abort_if($name === null, 404);

        return view('channels.places.province', [
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
        $content = app(\App\Services\ChannelSites\ChannelPlaceContent::class)->assemble($tokens, $data);

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

        // Nabije plaatsen (zelfde provincie) — zoveel mogelijk interne links.
        $names   = $resolver->places();
        $nearby  = [];
        foreach ($resolver->regions() as $pSlug => $prov) {
            if ($pSlug === $data['slug'] || $prov !== $data['provincie']) {
                continue;
            }
            $nearby[$pSlug] = $names[$pSlug] ?? $pSlug;
        }
        asort($nearby);

        // SEO-gating: alleen plaatsen met genoeg echte bedrijven indexeren.
        $indexable = count($businesses) >= (int) config('channel_places.index_min_businesses', 3);

        return view('channels.places.show', [
            'site'       => $site,
            'place'      => $data,
            'placeName'  => $data['naam'],
            'placeSlug'  => $data['slug'],
            'content'    => $content,
            'business'   => $business,
            'businesses' => $businesses,
            'nearby'     => $nearby,
            'indexable'  => $indexable,
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

        return view('channels.blog.index', ['site' => $site, 'posts' => $posts]);
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

        return view('channels.blog.show', ['site' => $site, 'post' => $post]);
    }

    /* ─────────────────────────────── SEO ─────────────────────────────────── */

    /** Per-kanaal sitemap met home, over-ons, plaatsen, plaats-pagina's en blog. */
    public function sitemap(): \Illuminate\Http\Response
    {
        $site = $this->site();

        $resolver = app(\App\Services\ChannelSiteResolver::class);

        // Vaste content-pagina's: home, de Groeidiamant-facetlandingen (belangrijkste
        // SEO-pagina's), en alle informatieve pagina's.
        $paths = ['', 'over-ons', 'contact', 'diensten', 'groeidiamant', 'prijzen', 'werkwijze', 'cases',
            'veelgestelde-vragen', 'vergelijken', 'plaatsen', 'blog'];
        foreach (array_keys((array) config('groeidiamant.facets', [])) as $facet) {
            $paths[] = $facet;
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
            'appointment_at'   => ['nullable', 'date'],
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
            'channel'            => $site->key,
            'source'             => 'channel',
            'status'             => 'new',
            'preview_status'     => 'todo',
        ]);

        // Online-via-Meet + een gekozen moment → meteen een echte afspraak boeken
        // (agenda-event + Google Meet-link + bevestigingsmail via BookingService).
        if ($appointmentType === 'meet' && filled($data['appointment_at'] ?? null)) {
            try {
                app(\App\Services\Scheduling\BookingService::class)->book([
                    'name'        => $data['contact_name'],
                    'email'       => $data['email'],
                    'phone'       => $data['phone'],
                    'starts_at'   => $data['appointment_at'],
                    'source_site' => $site->key,
                    'note'        => 'Via gratis-voorbeeld-aanvraag' . (! empty($data['company']) ? ' — ' . $data['company'] : ''),
                ]);
                $lead->update(['appointment_status' => 'booked']);
            } catch (\App\Services\Scheduling\SlotTakenException $e) {
                // Slot net bezet: lead blijft 'requested', we plannen handmatig na.
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $this->notifyInternal($lead, $site);

        return redirect($site->url('bedankt'));
    }

    public function leadSent(): View
    {
        return view('channels.lead-sent', ['site' => $this->site()]);
    }

    private function notifyInternal(WebsiteLead $lead, ChannelSite $site): void
    {
        try {
            $to = config('mail.from.address');
            if (! $to) {
                return;
            }
            $branche = WebsiteLead::BRANCHES[$lead->branche] ?? $lead->branche;
            Mail::raw(
                "Nieuwe lead via channel-site.\n\n"
                . "Site: {$site->name()} ({$site->key})\n"
                . "Branche: {$branche}\n"
                . "Naam: {$lead->contact_name}\n"
                . "Bedrijf: " . ($lead->company ?: '—') . "\n"
                . "Contact: {$lead->email} · {$lead->phone}\n"
                . "Plaats: " . ($lead->city ?: '—') . "\n"
                . "Bericht: " . ($lead->message ?: '—') . "\n\n"
                . "Opvolgen in de admin → Website-leads.",
                fn ($m) => $m->to($to)->subject("Nieuwe lead ({$site->key}): " . ($lead->company ?: $lead->contact_name))
            );
        } catch (\Throwable $e) {
            Log::warning('channel_lead_mail: ' . $e->getMessage());
        }
    }
}
