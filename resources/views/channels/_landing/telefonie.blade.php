@php
    /** @var \App\Support\ChannelSite $site */
    // Landingspagina AI-telefonie van een kanaal (/ai-telefonie-{key}). Inhoud uit
    // config/{key}_telefonie.php samengevoegd met config/telefonie_basis.php, zie
    // App\Support\TelefonieConfig. De bakkerij was de eerste (15-09-2026); sinds 16-09 delen
    // alle kanalen deze view en verschilt alleen de inhoud.
    $c        = \App\Support\TelefonieConfig::for($site);
    $hero     = (array) ($c['hero'] ?? []);
    $prijs    = (array) ($c['prijs'] ?? []);
    $demo     = trim((string) ($c['demo_nummer'] ?? ''));
    $demoTel  = preg_replace('/[^0-9+]/', '', $demo);
    $kantoor  = (string) ($c['kantoor_nummer'] ?? '');
    $afspraak = $site->url('afspraak');
    $contact  = $site->url('contact');
    $heroImg  = $site->image('facet-ai') ?: $site->image('ai-preview');
    $heroSet  = $site->image('facet-ai') ? $site->imageSrcset('facet-ai') : $site->imageSrcset('ai-preview');
    $pad      = 'ai-telefonie-' . $site->key;
    // Infoblad (PDF) bij de demo: alleen als er een demonummer én een blad is.
    $infoblad = ($demo && ! empty($c['infoblad'])) ? $site->url($pad . '/infoblad.pdf') : '';
    $blad     = $infoblad ? (array) config('telefonie_infobladen.' . $c['infoblad'], []) : [];

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
        'name'        => $c['service_naam'] ?? ('AI-telefonie voor ' . ($c['woorden']['bedrijven'] ?? 'bedrijven')),
        'serviceType' => 'Telefonische AI-assistent',
        'description' => $hero['sub'] ?? '',
        'provider'    => ['@id' => rtrim($site->baseUrl(), '/') . '#org'],
        'areaServed'  => ['@type' => 'Country', 'name' => 'Nederland'],
        'audience'    => ['@type' => 'BusinessAudience', 'name' => ucfirst((string) ($c['woorden']['bedrijven'] ?? 'Bedrijven'))],
        'url'         => $site->url($pad),
    ];
    if (! empty($prijs['vanaf'])) {
        $svcLd['offers'] = ['@type' => 'Offer', 'price' => preg_replace('/[^0-9,]/', '', $prijs['vanaf']), 'priceCurrency' => 'EUR', 'description' => 'vanaf, ' . ($prijs['periode'] ?? 'per maand')];
    }
@endphp
@extends('channels.layout')

@section('title', $c['seo_titel'] ?? ($hero['title'] ?? 'AI-telefonie'))
@section('description', ($c['seo_omschrijving'] ?? ($hero['sub'] ?? '')) . ' Vanaf ' . ($prijs['vanaf'] ?? '') . ' ' . ($prijs['periode'] ?? '') . '.')

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
                    <h1>{{ $hero['title'] ?? 'AI-telefonie' }}</h1>
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
                    @if ($infoblad)
                        @include('channels.partials.telefonie-infoblad-knop', ['url' => $infoblad])
                    @endif
                    @if ($demo && ! empty($c['demo_noot']))
                        <p class="muted" style="font-size:.9rem">{{ $c['demo_noot'] }}</p>
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
                             alt="{{ $hero['alt'] ?? 'De telefonische assistent' }}" loading="eager" decoding="async" width="1536" height="1024"
                             style="width:100%;height:auto;border-radius:var(--radius);display:block;box-shadow:0 24px 60px -24px rgba(0,0,0,.4)">
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section data-section="wanneer">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Herken je dit?</span>
            <h2>{{ $c['wanneer_titel'] ?? 'Wanneer de telefoon het meeste stoort' }}</h2>
            <div class="grid cols-2 feature-grid" style="margin-top:1.6rem">
                @foreach ((array) ($c['wanneer'] ?? []) as $w)
                    <div class="feature-card">
                        <h3>{{ $w['t'] }}</h3>
                        <span class="feature-rule"></span>
                        <p>{{ $w['b'] }}</p>
                    </div>
                @endforeach
            </div>
            {{-- Leesstuk op het demoplatform: de zeven situaties uitgeschreven (zelfde verhaal als de offertebijlage). --}}
            <a class="card" href="https://telefonie.betergeregeld.com/praktijk" style="display:flex;flex-wrap:wrap;align-items:center;gap:.5rem 1.2rem;margin-top:1.4rem">
                <strong>Lees: De telefoon dringt voor</strong>
                <span class="muted">Zeven situaties uit een gewone werkdag, ook de klant die vóór je staat en ziet dat de telefoon steeds voorgaat.</span>
                <span style="margin-left:auto;font-weight:700">Lezen &rarr;</span>
            </a>
        </div>
    </section>

    <section data-section="gesprekken" style="background:var(--c-tint,var(--c-surface))">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Voorbeeldgesprekken</span>
            <h2>{{ $c['gesprekken_titel'] ?? 'Zo klinkt dat, in gewoon Nederlands' }}</h2>
            <p class="section-lead">{{ $c['gesprekken_lead'] ?? '' }}</p>
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

    {{-- De demo zelf: pas zichtbaar als er een nummer én kaartjes zijn. --}}
    @if ($demo && ! empty($c['demo_kaartjes']))
        <section data-section="demo" id="demo">
            <div class="wrap">
                <span class="kicker"><span class="kicker-line"></span> Probeer het zelf</span>
                <h2>{{ $c['demo_titel'] ?? ('Bel de demo op ' . $demo) }}</h2>
                <p class="lead">{{ $c['demo_lead'] ?? '' }}</p>
                <div class="grid cols-2 feature-grid" style="margin-top:1.6rem">
                    @foreach ((array) $c['demo_kaartjes'] as $k)
                        <div class="feature-card">
                            <h3>{{ $k['kop'] }}</h3>
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
                <div style="margin-top:1.4rem;display:flex;flex-wrap:wrap;gap:1rem 1.4rem;align-items:center">
                    <a href="tel:{{ $demoTel }}" class="btn">Bel de demo: {{ $demo }}</a>
                    @if ($infoblad)@include('channels.partials.telefonie-infoblad-knop', ['url' => $infoblad, 'inline' => true])@endif
                </div>
            </div>
        </section>
    @endif

    <section data-section="kan">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Wat hij doet</span>
            <h2>{{ $c['kan_titel'] ?? 'Wat de assistent afhandelt' }}</h2>
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
                    <p class="muted">{{ $prijs['toelichting'] ?? '' }} Wil je AI-telefonie samen met een <a href="{{ $site->url($c['prijs_combi_facet'] ?? 'website') }}">{{ $c['prijs_combi_label'] ?? 'website' }}</a> of <a href="{{ $site->url('automatisering') }}">automatisering</a>, dan maken we één prijs voor het geheel.</p>
                </div>
            </div>
        </section>
    @endif

    @include('channels.partials.telefonie-rekenhulp', ['site' => $site, 'c' => $c])

    <section data-section="faq">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Veelgestelde vragen</span>
            <h2>{{ $c['faq_titel'] ?? 'Veelgestelde vragen over AI-telefonie' }}</h2>
            @include('channels.partials.faq-accordion', ['items' => (array) ($c['faq'] ?? [])])
        </div>
    </section>

    @if (! empty($c['verder']))
        <section data-section="verder" style="background:var(--c-tint,var(--c-surface))">
            <div class="wrap">
                <span class="kicker"><span class="kicker-line"></span> Hoort erbij</span>
                <h2>{{ $c['verder_titel'] ?? '' }}</h2>
                <div class="grid cols-3" style="gap:1rem">
                    @foreach ((array) $c['verder'] as $v)
                        <a class="card" href="{{ $site->url($v['facet'] ?? '') }}"><strong>{{ $v['t'] }}</strong><br><span class="muted">{{ $v['b'] }}</span></a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

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

    {{-- Het spiekbriefje als paneel op de pagina (lade rechts / sheet onderaan); de knoppen openen dit. --}}
    @if ($infoblad && $blad)
        @include('channels.partials.telefonie-infoblad-paneel', ['blad' => $blad, 'url' => $infoblad, 'demo' => $demo, 'demoTel' => $demoTel])
    @endif

@endsection
