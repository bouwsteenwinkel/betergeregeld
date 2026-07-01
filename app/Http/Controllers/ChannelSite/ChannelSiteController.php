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
        // Groeidiamant-fase (SEO-instap). De homepage-content kan per fase worden
        // overschreven via config: channels.<key>.facets.<facet>.
        $facet = WebsiteLead::normalizeFacet($request->route('facet'));
        $home  = array_replace((array) $site->get('home', []), (array) $site->get('facets.' . $facet, []));

        return view($site->homeView(), [
            'site'   => $site,
            'facet'  => $facet,
            'home'   => $home,
            'facets' => (array) config('groeidiamant.facets', []),
        ]);
    }

    /** Alleen de facet-afhankelijke blokken — voor de live (AJAX) fase-switch. */
    public function homeFragment(Request $request): View
    {
        $site  = $this->site();
        $facet = WebsiteLead::normalizeFacet($request->route('facet'));
        $facets = (array) config('groeidiamant.facets', []);

        // Blok-gedreven site → de DB-blokken (zonder wizard); anders de config-zone.
        if ($site->hasBlocks()) {
            return view('channels.partials.blocks-fragment', compact('site', 'facet', 'facets'));
        }

        $home = array_replace((array) $site->get('home', []), (array) $site->get('facets.' . $facet, []));

        return view('channels.partials.facet-zone', compact('site', 'facet', 'home', 'facets'));
    }

    public function about(): View
    {
        return view('channels.about', ['site' => $this->site()]);
    }

    /* ───────────────────────────── Plaatsen ──────────────────────────────── */

    public function places(): View
    {
        return view('channels.places.index', ['site' => $this->site()]);
    }

    public function place(Request $request): View
    {
        // By-name lezen i.p.v. method-injectie: de preview-route heeft een extra
        // {channelKey}-param vóór {place}, wat anders positioneel zou binden.
        $place = (string) $request->route('place');
        $site  = $this->site();
        $name  = app(\App\Services\ChannelSiteResolver::class)->placeName($place);
        abort_if($name === null, 404);

        return view('channels.places.show', [
            'site'      => $site,
            'placeName' => $name,
            'placeSlug' => $place,
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

        $urls = [$site->url(''), $site->url('over-ons'), $site->url('plaatsen'), $site->url('blog')];
        foreach (array_keys(app(\App\Services\ChannelSiteResolver::class)->places()) as $slug) {
            $urls[] = $site->url('plaatsen/' . $slug);
        }
        try {
            foreach (BlogPost::query()->forChannel($site->key)->published()->pluck('slug') as $slug) {
                $urls[] = $site->url('blog/' . $slug);
            }
        } catch (\Throwable $e) {
            // Blog (nog) niet beschikbaar — sitemap blijft geldig zonder blogposts.
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= '  <url><loc>' . htmlspecialchars($u, ENT_XML1) . '</loc></url>' . "\n";
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
