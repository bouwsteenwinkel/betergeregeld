@php
    /** @var \App\Support\ChannelSite $site */
    // Landingspagina AI-telefonie voor bakkerijen (15-09-2026). Inhoud uit
    // config/bakkerij_telefonie.php; zie de toelichting daar over wat wel/niet geclaimd wordt.
    $c        = (array) config('bakkerij_telefonie', []);
    $hero     = (array) ($c['hero'] ?? []);
    $prijs    = (array) ($c['prijs'] ?? []);
    $demo     = trim((string) ($c['demo_nummer'] ?? ''));
    $demoTel  = preg_replace('/[^0-9+]/', '', $demo);
    $kantoor  = (string) ($c['kantoor_nummer'] ?? '');
    $afspraak = $site->url('afspraak');
    $contact  = $site->url('contact');
    $heroImg  = $site->image('facet-ai') ?: $site->image('ai-preview');
    $heroSet  = $site->image('facet-ai') ? $site->imageSrcset('facet-ai') : $site->imageSrcset('ai-preview');

    $faqLd = [
        '@context' => 'https://schema.org',
        '@type'    => 'FAQPage',
        'mainEntity' => array_values(array_map(fn ($f) => [
            '@type' => 'Question', 'name' => $f['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
        ], (array) ($c['faq'] ?? []))),
    ];
    $svcLd = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Service',
        'name'        => 'AI-telefonie voor bakkerijen',
        'serviceType' => 'Telefonische AI-assistent',
        'description' => $hero['sub'] ?? '',
        'provider'    => ['@id' => rtrim($site->baseUrl(), '/') . '#org'],
        'areaServed'  => ['@type' => 'Country', 'name' => 'Nederland'],
        'audience'    => ['@type' => 'BusinessAudience', 'name' => 'Bakkerijen'],
        'url'         => $site->url('ai-telefonie-bakkerij'),
    ];
    if (! empty($prijs['vanaf'])) {
        $svcLd['offers'] = ['@type' => 'Offer', 'price' => preg_replace('/[^0-9,]/', '', $prijs['vanaf']), 'priceCurrency' => 'EUR', 'description' => 'vanaf, ' . ($prijs['periode'] ?? 'per maand')];
    }
@endphp
@extends('channels.layout')

@section('title', 'AI telefonie voor bakkerijen | AI telefoonassistent die opneemt als jij bakt')
@section('description', 'AI-telefonie voor je bakkerij: neemt op, beantwoordt vragen over openingstijden en assortiment, legt bestel- en terugbelverzoeken vast en verbindt door. Ook buiten openingstijden. Vanaf ' . ($prijs['vanaf'] ?? '') . ' ' . ($prijs['periode'] ?? '') . '.')

@push('head')
    <script type="application/ld+json">{!! json_encode($svcLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @if ($heroImg)
        <link rel="preload" as="image" href="{{ $heroImg }}" @if ($heroSet) imagesrcset="{{ $heroSet }}" imagesizes="(max-width:760px) 92vw, 46vw" @endif fetchpriority="high">
    @endif
@endpush

@section('content')

    @include('channels.partials.breadcrumb', ['items' => [
        ['label' => 'Home', 'url' => $site->url('')],
        ['label' => 'Diensten', 'url' => $site->url('diensten')],
        ['label' => 'AI-telefonie'],
    ]])

    <section class="hero" data-section="hero">
        <div class="wrap">
            <div @if ($heroImg) class="grid cols-2" style="align-items:start;gap:2.6rem" @endif>
                <div>
                    <span class="eyebrow">{{ $hero['eyebrow'] ?? 'AI-telefonie' }}</span>
                    <h1>{{ $hero['title'] ?? 'AI-telefonie voor je bakkerij' }}</h1>
                    <p class="lead">{{ $hero['sub'] ?? '' }}</p>
                    <div style="display:flex;flex-wrap:wrap;gap:.7rem;margin:.4rem 0 .9rem">
                        @if ($demo)
                            <a href="tel:{{ $demoTel }}" class="btn">Bel de demo: {{ $demo }}</a>
                            <a href="{{ $afspraak }}?onderwerp=ai-telefonie" class="btn btn-ghost">Vraag een demo aan</a>
                        @else
                            <a href="{{ $afspraak }}?onderwerp=ai-telefonie" class="btn">Vraag een demo aan</a>
                            <a href="{{ $contact }}" class="btn btn-ghost">Stel een vraag</a>
                        @endif
                    </div>
                    @if ($demo)
                        <p class="muted" style="font-size:.9rem">Het demonummer is {{ $c['demo_bakkerij'] ?? 'een verzonnen bakkerij' }}: een bakkerij die niet bestaat, zodat je vrij kunt vragen wat je wilt. Je sluit niets af door te bellen.</p>
                    @endif
                    @if (! empty($hero['usps']))
                        <ul class="hero-usps">
                            @foreach ((array) $hero['usps'] as $usp)<li>{{ $usp }}</li>@endforeach
                        </ul>
                    @endif
                </div>
                @if ($heroImg)
                    <div>
                        <img src="{{ $heroImg }}" @if ($heroSet) srcset="{{ $heroSet }}" sizes="(max-width:760px) 92vw, 46vw" @endif
                             alt="De telefonische assistent van een bakkerij" loading="eager" decoding="async" width="1536" height="1024"
                             style="width:100%;height:auto;border-radius:var(--radius);display:block;box-shadow:0 24px 60px -24px rgba(0,0,0,.4)">
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section data-section="wanneer">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Herken je dit?</span>
            <h2>{{ $c['wanneer_titel'] ?? '' }}</h2>
            <div class="grid cols-2 feature-grid" style="margin-top:1.6rem">
                @foreach ((array) ($c['wanneer'] ?? []) as $w)
                    <div class="feature-card">
                        <h3>{{ $w['t'] }}</h3>
                        <span class="feature-rule"></span>
                        <p>{{ $w['b'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section data-section="gesprekken" style="background:var(--c-tint,var(--c-surface))">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Voorbeeldgesprekken</span>
            <h2>{{ $c['gesprekken_titel'] ?? '' }}</h2>
            <p class="section-lead">De antwoorden hieronder komen uit de gegevens die de bakkerij zelf heeft aangeleverd. Zo praat de assistent ook over jouw bakkerij: alleen wat jij hebt doorgegeven.</p>
            <div class="grid cols-2" style="gap:1rem">
                @foreach ((array) ($c['gesprekken'] ?? []) as $g)
                    <div class="card">
                        <p style="margin:0 0 .5rem;font-weight:700">"{{ $g['vraag'] }}"</p>
                        <p style="margin:0;color:var(--c-muted)">{{ $g['antwoord'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- De demo zelf: pas zichtbaar als er een nummer is (config demo_nummer). --}}
    @if ($demo && ! empty($c['demo_kaartjes']))
        <section data-section="demo" id="demo">
            <div class="wrap">
                <span class="kicker"><span class="kicker-line"></span> Probeer het zelf</span>
                <h2>Bel {{ $c['demo_bakkerij'] ?? 'de demo' }} op {{ $demo }}</h2>
                <p class="lead">{{ $c['demo_bakkerij'] ?? 'De demobakkerij' }} bestaat niet, het assortiment wel. Bestel iets, of bel met een van deze bestelnummers en vraag of je bestelling klaar is. Er wordt niets gebakken en niets afgerekend.</p>
                <div class="grid cols-2 feature-grid" style="margin-top:1.6rem">
                    @foreach ((array) $c['demo_kaartjes'] as $k)
                        <div class="feature-card">
                            <h3>Bestelnummer {{ $k['nummer'] }} &middot; {{ $k['naam'] }}</h3>
                            <span class="feature-rule"></span>
                            <p>{{ $k['hoor'] }}</p>
                        </div>
                    @endforeach
                </div>
                @if (! empty($c['demo_probeer']))
                    <div class="card" style="margin-top:2rem">
                        <h3 style="margin-top:0">Wat je verder kunt proberen</h3>
                        <ul style="margin:0;padding-left:1.2rem;color:var(--c-muted);line-height:1.6">
                            @foreach ((array) $c['demo_probeer'] as $p)<li>{{ $p }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                <p style="margin-top:1.4rem"><a href="tel:{{ $demoTel }}" class="btn">Bel de demo: {{ $demo }}</a></p>
            </div>
        </section>
    @endif

    <section data-section="kan">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Wat hij doet</span>
            <h2>{{ $c['kan_titel'] ?? '' }}</h2>
            <div class="grid cols-2 feature-grid" style="margin-top:1.6rem">
                @foreach ((array) ($c['kan'] ?? []) as $k)
                    <div class="feature-card">
                        <h3>{{ $k['t'] }}</h3>
                        <span class="feature-rule"></span>
                        <p>{{ $k['b'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="card" style="margin-top:2rem">
                <h3 style="margin-top:0">{{ $c['niet_titel'] ?? 'Wat hij niet doet' }}</h3>
                <ul style="margin:0;padding-left:1.2rem;color:var(--c-muted);line-height:1.6">
                    @foreach ((array) ($c['niet'] ?? []) as $n)<li>{{ $n }}</li>@endforeach
                </ul>
            </div>
        </div>
    </section>

    <section data-section="nummer" style="background:var(--c-tint,var(--c-surface))">
        <div class="wrap">
            <div class="grid cols-2" style="gap:2rem;align-items:start">
                <div>
                    <span class="kicker"><span class="kicker-line"></span> Je eigen nummer</span>
                    <h2>{{ $c['nummer_titel'] ?? '' }}</h2>
                    <p>{{ $c['nummer_tekst'] ?? '' }}</p>
                </div>
                <div>
                    <span class="kicker"><span class="kicker-line"></span> Privacy</span>
                    <h2>{{ $c['privacy_titel'] ?? '' }}</h2>
                    <p>{{ $c['privacy_tekst'] ?? '' }}</p>
                </div>
            </div>
        </div>
    </section>

    <section data-section="werkwijze">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Werkwijze</span>
            <h2>{{ $c['stappen_titel'] ?? '' }}</h2>
            <div class="steps">
                @foreach ((array) ($c['stappen'] ?? []) as $i => $s)
                    <div class="step">
                        <span class="step-num">{{ $i + 1 }}</span>
                        <h3>{{ $s['t'] }}</h3>
                        <p class="muted">{{ $s['b'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if ($prijs)
        <section data-section="prijs" style="background:var(--c-tint,var(--c-surface))">
            <div class="wrap">
                <span class="kicker"><span class="kicker-line"></span> Wat het kost</span>
                <h2>Vanaf {{ $prijs['vanaf'] ?? '' }} {{ $prijs['periode'] ?? '' }}</h2>
                <div class="grid cols-2" style="gap:1rem;align-items:start">
                    <div class="card">
                        <ul style="margin:0;padding-left:1.2rem;line-height:1.7">
                            <li>Inbegrepen: {{ $prijs['inbegrepen'] ?? '' }}</li>
                            <li>Daarboven: {{ $prijs['extra'] ?? '' }}</li>
                            <li>Eenmalig: {{ $prijs['eenmalig'] ?? '' }}</li>
                            <li>{{ ucfirst($prijs['opzeg'] ?? '') }}</li>
                        </ul>
                    </div>
                    <p class="muted">{{ $prijs['toelichting'] ?? '' }} Wil je AI-telefonie samen met een <a href="{{ $site->url('webshop') }}">webshop</a> of <a href="{{ $site->url('automatisering') }}">automatisering</a>, dan maken we één prijs voor het geheel.</p>
                </div>
            </div>
        </section>
    @endif

    <section data-section="faq">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Veelgestelde vragen</span>
            <h2>Wat bakkers ons vragen over AI-telefonie</h2>
            @include('channels.partials.faq-accordion', ['items' => (array) ($c['faq'] ?? [])])
        </div>
    </section>

    <section data-section="verder" style="background:var(--c-tint,var(--c-surface))">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Hoort erbij</span>
            <h2>Telefoon, webshop en administratie op elkaar aansluiten</h2>
            <div class="grid cols-3" style="gap:1rem">
                <a class="card" href="{{ $site->url('webshop') }}"><strong>Webshop voor bakkerijen</strong><br><span class="muted">Laat klanten die nu bellen om te bestellen, dat online doen met een afhaaltijd en vooraf betalen.</span></a>
                <a class="card" href="{{ $site->url('automatisering') }}"><strong>Bestellingen automatisch verwerken</strong><br><span class="muted">Een verzoek dat de assistent vastlegt, komt op dezelfde productielijst als een webshopbestelling.</span></a>
                <a class="card" href="{{ $site->url('klantenportaal') }}"><strong>Klantenportaal voor zakelijke klanten</strong><br><span class="muted">Horeca en kantoren die nu elke avond bellen, bestellen zelf.</span></a>
            </div>
        </div>
    </section>

    <section data-section="cta">
        <div class="wrap" style="text-align:center">
            <h2>{{ $c['cta_titel'] ?? '' }}</h2>
            <p class="lead" style="max-width:56ch;margin:0 auto 1.4rem">{{ $c['cta_tekst'] ?? '' }}</p>
            <div style="display:flex;flex-wrap:wrap;gap:.7rem;justify-content:center">
                @if ($demo)<a href="tel:{{ $demoTel }}" class="btn">Bel de demo: {{ $demo }}</a>@endif
                <a href="{{ $afspraak }}?onderwerp=ai-telefonie" class="btn {{ $demo ? 'btn-ghost' : '' }}">Vraag een demo aan</a>
            </div>
            @if ($kantoor)<p class="muted" style="margin-top:1rem">Liever eerst iemand spreken? Bel {{ $kantoor }}.</p>@endif
        </div>
    </section>

@endsection
