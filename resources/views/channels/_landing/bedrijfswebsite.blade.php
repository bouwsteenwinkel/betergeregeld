@php
    /** @var \App\Support\ChannelSite $site */
    // $facet = actieve product-facet, $landing = config('bedrijfswebsite_landings.'.$facet),
    // $facets = groeidiamant-facetten. Gezet door ChannelSiteController@home.
    //
    // Indexeerbare head-term-landingspagina per facet (/website, /webshop,
    // /klantenportaal, /automatisering, /ai). De layout self-canonicaliseert op
    // het huidige pad en zet robots=index,follow, dus geen aparte meta nodig.
    // Breadcrumd bewust Home › {facet} — /diensten is op dit kanaal geblokkeerd.
    $facets  = $facets ?? (array) config('groeidiamant.facets', []);
    $hero    = (array) ($landing['hero'] ?? []);
    $pains   = (array) ($landing['pains'] ?? []);
    $zkhw    = (array) ($landing['zkhw'] ?? []);
    $fLabel  = $facets[$facet]['label'] ?? ($zkhw['label'] ?? 'product');
    $facetSlot = 'facet-' . $facet;
    $heroImg = $site->image($facetSlot) ?: $site->image('hero');
    $heroSet = $site->image($facetSlot) ? $site->imageSrcset($facetSlot) : $site->imageSrcset('hero');

    // FAQ per facet (zichtbaar + FAQPage-schema): goed voor rich results en GEO.
    $facetFaq = (array) config('bedrijfswebsite_facet_faq.' . $facet, []);
    $faqLd = $facetFaq ? [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => array_values(array_map(fn ($f) => [
            '@type'          => 'Question',
            'name'           => $f['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
        ], $facetFaq)),
    ] : null;
@endphp
@extends('channels.layout')

{{-- Head-term voorop in de title (eyebrow = "Webshop laten maken" e.d.). --}}
{{-- data_get en niet $landing['seo_title']: is $landing onverhoopt null, dan geeft een
     directe array-toegang een PHP-warning, en Laravel promoveert die tot een fatale
     ErrorException — een 500 op de commerciële pagina om een ontbrekende titel. --}}
@section('title', data_get($landing, 'seo_title') ?: (($hero['eyebrow'] ?? $fLabel) . ' voor jouw bedrijf'))
@section('description', $hero['sub'] ?? ($hero['title'] ?? ''))

@if ($faqLd)
    @push('head')
        <script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endpush
@endif

@section('content')

    @include('channels.partials.breadcrumb', ['items' => [
        ['label' => 'Home', 'url' => $site->url('')],
        ['label' => $fLabel],
    ]])

    <section class="hero" data-section="hero">
        <div class="wrap">
            <div @if ($heroImg) class="grid cols-2" style="align-items:start;gap:2.6rem" @endif>
                <div>
                    @if (! empty($hero['eyebrow']))<span class="eyebrow">{{ $hero['eyebrow'] }}</span>@endif
                    <h1>{{ $hero['title'] ?? $fLabel }}</h1>
                    @if (! empty($hero['sub']))<p class="lead">{{ $hero['sub'] }}</p>@endif
                    <a href="{{ $site->url('voorbeeld-maken') }}" class="btn">Bekijk mijn gratis voorbeeld</a>
                    @if (! empty($hero['note']))<p class="muted" style="margin-top:.8rem;font-size:.9rem">{{ $hero['note'] }}</p>@endif
                    @if (! empty($hero['usps']))
                        <ul class="hero-usps">
                            @foreach ((array) $hero['usps'] as $usp)<li>{{ $usp }}</li>@endforeach
                        </ul>
                    @endif
                </div>
                @if ($heroImg)
                    <div>
                        <img src="{{ $heroImg }}"
                             @if ($heroSet) srcset="{{ $heroSet }}" sizes="(max-width:760px) 92vw, 46vw" @endif
                             alt="Voorbeeld: {{ $fLabel }} voor je bedrijf" loading="eager" decoding="async"
                             style="width:100%;height:auto;border-radius:var(--radius);display:block;box-shadow:0 24px 60px -24px rgba(0,0,0,.4)">
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if ($pains)
        <section data-section="herkenning">
            <div class="wrap">
                <span class="kicker"><span class="kicker-line"></span> Herken je dit?</span>
                <div class="grid cols-3 feature-grid" style="margin-top:1.4rem">
                    @foreach ($pains as $pn)
                        <div class="feature-card">
                            <h3>{{ $pn['title'] ?? '' }}</h3>
                            <span class="feature-rule"></span>
                            <p>{{ $pn['text'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($zkhw)
        @include('channels.partials.zo-kan-het-worden', array_merge(['site' => $site, 'facet' => $facet], $zkhw))
    @endif

    @include('channels.partials.groeipad', [
        'site'    => $site,
        'facets'  => $facets,
        'facet'   => $facet,
        'kicker'  => 'De Groeidiamant',
        'title'   => 'Je site groeit met je mee',
        'lead'    => 'Begin waar je nu staat, je hoeft nooit opnieuw te beginnen. Elke stap bouwt voort op de vorige, in je eigen tempo.',
    ])

    @if ($facetFaq)
        <section data-section="faq">
            <div class="wrap" style="max-width:760px">
                <span class="kicker"><span class="kicker-line"></span> Veelgestelde vragen</span>
                {{-- Niet blind kleinschrijven: het facet-label "AI" werd zo "Vragen over ai",
                     wat live op de pagina stond. Afkortingen (helemaal in hoofdletters)
                     laten we staan, gewone woorden gaan wél naar kleine letters omdat ze
                     midden in een zin staan. --}}
                @php($fLabelZin = $fLabel === mb_strtoupper($fLabel) ? $fLabel : mb_strtolower($fLabel))
                <h2 style="margin:.3rem 0 1.2rem">Vragen over {{ $fLabelZin }}</h2>
                @foreach ($facetFaq as $f)
                    <details style="border-top:1px solid var(--c-line,#E5E3DF);padding:.1rem 0">
                        <summary style="cursor:pointer;font-weight:700;padding:.95rem 0;list-style:none">{{ $f['q'] }}</summary>
                        <p class="muted" style="padding:0 0 1.1rem;margin:0">{{ $f['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Werkgebied: links naar de grootste plaatsen. Stond hier niet, waardoor de
         commerciële facetpagina's geen enkele interne link naar de plaatspagina's
         hadden terwijl die onderling wél linken. --}}
    @include('channels.partials.facet-werkgebied', ['site' => $site, 'facet' => $facet])

    @include('channels.partials.sales-trust', ['site' => $site, 'ctaTitle' => 'Benieuwd hoe dit voor jou zou werken?'])

    <div id="gratis-voorbeeld" class="scroll-anchor" aria-hidden="true"></div>
    @include('channels.partials.lead-wizard', ['site' => $site, 'facet' => $facet])

@endsection
