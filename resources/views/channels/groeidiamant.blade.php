@php
    /** @var \App\Support\ChannelSite $site */
    /** @var array $c  uit App\Support\GroeidiamantConfig::for($site) */
    // De Groeidiamant als keuzehulp: "waar sta ik, wat is de logische volgende stap, waar
    // klik ik door". Eén view voor alle kanalen; alle tekst komt uit $c (basis + landings-
    // terugval + branche-laag). Zie docs/GROEIDIAMANT-PLAN.md voor de opbouw.
    $c      = $c ?? \App\Support\GroeidiamantConfig::for($site);
    $fasen  = (array) $c['fasen'];
    $tel    = (array) $c['telefonie'];
    $telNr  = (string) ($tel['demo_nummer'] ?? '');
    $telHref = 'tel:' . preg_replace('/[^0-9+]/', '', $telNr);
    $gdLogo = asset('channel-media/_brand/groeidiamant.jpg');

    // Structured data: de pagina zelf met de vijf diensten waar hij naar verwijst, en de
    // FAQ precies zoals hij zichtbaar op de pagina staat. Breadcrumb komt uit de partial.
    $paginaUrl = $site->url('groeidiamant');
    $webLd = [
        '@context' => 'https://schema.org',
        '@type'    => 'WebPage',
        '@id'      => $paginaUrl,
        'url'      => $paginaUrl,
        'name'     => $c['seo_titel'],
        'description' => $c['seo_omschrijving'],
        'inLanguage'  => 'nl-NL',
        'isPartOf'    => ['@id' => $site->url('') . '#org'],
        'about'       => array_values(array_map(fn ($f) => [
            '@type' => 'Service',
            'name'  => $f['titel'],
            'url'   => $f['url'],
            'description' => $f['voor'],
        ], $fasen)),
    ];
    $faqLd = [
        '@context' => 'https://schema.org',
        '@type'    => 'FAQPage',
        'mainEntity' => array_values(array_map(fn ($qa) => [
            '@type' => 'Question',
            'name'  => $qa['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa['a']],
        ], (array) $c['faq'])),
    ];
@endphp
@extends('channels.layout')

@section('title', $c['seo_titel'])
@section('description', $c['seo_omschrijving'])

@push('head')
    <script type="application/ld+json">{!! json_encode($webLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <style>
        /* Groeidiamant-pagina. Alles onder .gd om de layout-CSS niet te raken. */
        .gd .hero{padding-bottom:40px}
        .gd-hero{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(0,.9fr);gap:3rem;align-items:center}
        .gd-hero h1{max-width:22ch}
        .gd-hero .lead{max-width:52ch}
        .gd-hero-knoppen{display:flex;flex-wrap:wrap;gap:.7rem;margin-top:1.4rem}
        .gd-motto{display:inline-flex;align-items:center;gap:.6rem;margin-top:1.3rem;font-weight:700;color:var(--c-primary)}
        .gd-motto::before{content:"";width:10px;height:10px;transform:rotate(45deg);background:var(--c-cta);border-radius:2px;flex:none}
        @media(max-width:860px){.gd-hero{grid-template-columns:1fr;gap:2rem}}

        /* De diamant: vijf facetten, van onder (website) naar de kroon (AI). Elk facet is
           een link naar de fase-kaart; hover/focus laat de branchezin eronder zien. */
        .gd-steen{position:relative;max-width:420px;margin:0 auto;width:100%}
        .gd-steen svg{width:100%;height:auto;display:block;overflow:visible}
        .gd-steen a{cursor:pointer}
        .gd-steen a polygon{fill:color-mix(in srgb,var(--c-primary) 14%,#fff);stroke:#fff;stroke-width:2;transition:fill .2s ease,transform .2s ease;transform-box:fill-box;transform-origin:center}
        .gd-steen a text{font-family:var(--font);font-weight:800;font-size:15px;fill:var(--c-ink);pointer-events:none;text-anchor:middle}
        .gd-steen a .nr{font-size:11px;font-weight:700;fill:var(--c-muted);letter-spacing:.08em}
        .gd-steen a:hover polygon,.gd-steen a:focus-visible polygon,.gd-steen a.is-aan polygon{fill:var(--c-primary);transform:scale(1.03)}
        .gd-steen a:hover text,.gd-steen a:focus-visible text,.gd-steen a.is-aan text{fill:#fff}
        .gd-steen a:hover .nr,.gd-steen a:focus-visible .nr,.gd-steen a.is-aan .nr{fill:rgba(255,255,255,.85)}
        .gd-steen a:focus{outline:none}
        .gd-steen a:focus-visible polygon{stroke:var(--c-cta);stroke-width:3}
        .gd-steen-glow{position:absolute;inset:8% 12% 6%;z-index:-1;background:radial-gradient(ellipse at 50% 40%,color-mix(in srgb,var(--c-primary) 30%,transparent),transparent 70%);filter:blur(28px)}
        .gd-steen-tekst{margin-top:.9rem;min-height:5.2rem;padding:.9rem 1.1rem;border-radius:var(--radius);background:var(--c-surface);border:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent);font-size:.95rem}
        .gd-steen-tekst strong{display:block;color:var(--c-primary);margin-bottom:.15rem}
        .gd-steen-tekst .muted{font-size:.85rem}
        @media(prefers-reduced-motion:reduce){.gd-steen a polygon{transition:none}.gd-steen a:hover polygon,.gd-steen a.is-aan polygon{transform:none}}

        /* Waar sta je nu? */
        .gd-situaties{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:.9rem;margin-top:1.6rem}
        .gd-situatie{display:flex;flex-direction:column;gap:.5rem;padding:1.2rem 1.1rem 1rem;border-radius:calc(var(--radius) + 2px);background:var(--c-surface);border:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent);text-decoration:none;color:inherit;transition:transform .15s ease,border-color .15s ease,box-shadow .15s ease}
        .gd-situatie:hover{transform:translateY(-3px);border-color:color-mix(in srgb,var(--c-accent) 45%,transparent);box-shadow:0 18px 36px -24px rgba(0,0,0,.4)}
        .gd-situatie .ico{display:inline-flex;width:38px;height:38px;border-radius:10px;align-items:center;justify-content:center;background:color-mix(in srgb,var(--c-accent) 18%,transparent);color:var(--c-primary)}
        .gd-situatie .ico svg{width:20px;height:20px}
        .gd-situatie h3{font-size:1rem;line-height:1.3;margin:0}
        .gd-situatie p{font-size:.88rem;color:var(--c-muted);margin:0;flex:1}
        .gd-situatie .naar{font-size:.88rem;font-weight:700;color:var(--c-primary);margin-top:.3rem}
        .gd-situatie .naar::after{content:" →"}
        @media(max-width:1000px){.gd-situaties{grid-template-columns:repeat(3,minmax(0,1fr))}}
        @media(max-width:640px){.gd-situaties{grid-template-columns:1fr;gap:.7rem}.gd-situatie{flex-direction:row;flex-wrap:wrap;align-items:center}.gd-situatie h3{flex:1;min-width:0}.gd-situatie p{flex-basis:100%}}

        /* De vijf stappen */
        .gd-fase{display:grid;grid-template-columns:72px minmax(0,1fr);gap:1.4rem;padding:2rem 0;border-top:1px solid color-mix(in srgb,var(--c-ink) 10%,transparent);scroll-margin-top:90px}
        .gd-fase:first-of-type{border-top:0;padding-top:.5rem}
        .gd-fase-nr{width:56px;height:56px;border-radius:50%;background:var(--c-primary);color:#fff;font-weight:800;font-size:1.25rem;display:grid;place-items:center;box-shadow:0 12px 26px -14px color-mix(in srgb,var(--c-primary) 70%,transparent)}
        .gd-fase h3{font-size:1.35rem;margin:.35rem 0 1rem}
        .gd-fase-kolommen{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1.2rem 1.6rem}
        .gd-fase-kolommen h4{font-size:.74rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--c-cta);margin:0 0 .4rem}
        .gd-fase-kolommen p{margin:0;font-size:.95rem}
        .gd-fase-kolommen ul{margin:.5rem 0 0;padding:0;list-style:none;font-size:.9rem}
        .gd-fase-kolommen li{position:relative;padding-left:1.2rem;margin-bottom:.25rem}
        .gd-fase-kolommen li::before{content:"✓";position:absolute;left:0;color:var(--c-primary);font-weight:800}
        .gd-fase-res{background:color-mix(in srgb,var(--c-accent) 10%,transparent);border-radius:var(--radius);padding:1rem 1.1rem}
        .gd-fase-res p{font-weight:700}
        .gd-fase .btn{margin-top:1.2rem}
        @media(max-width:860px){.gd-fase-kolommen{grid-template-columns:1fr}.gd-fase{grid-template-columns:48px minmax(0,1fr);gap:1rem}.gd-fase-nr{width:44px;height:44px;font-size:1.05rem}}

        /* Wat levert iedere stap op */
        .gd-res{display:grid;gap:.6rem;margin-top:1.4rem}
        .gd-res a{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:1rem;align-items:center;padding:.9rem 1.1rem;border-radius:var(--radius);background:var(--c-surface);border:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent);text-decoration:none;color:inherit}
        .gd-res a:hover{border-color:color-mix(in srgb,var(--c-accent) 45%,transparent)}
        .gd-res .nr{width:30px;height:30px;border-radius:50%;background:var(--c-primary);color:#fff;font-weight:800;font-size:.85rem;display:grid;place-items:center}
        .gd-res strong{display:block}
        .gd-res span.uit{font-size:.92rem;color:var(--c-muted)}
        .gd-res .pijl{font-weight:700;color:var(--c-primary);white-space:nowrap}
        @media(max-width:640px){.gd-res a{grid-template-columns:auto minmax(0,1fr)}.gd-res .pijl{grid-column:2}}

        /* Praktijk: tijdlijn */
        .gd-tijdlijn{position:relative;display:grid;gap:1.1rem;margin-top:1.6rem;padding-left:1.9rem}
        .gd-tijdlijn::before{content:"";position:absolute;left:9px;top:12px;bottom:12px;width:2px;background:color-mix(in srgb,var(--c-primary) 30%,transparent)}
        .gd-tijdlijn li{position:relative;list-style:none;background:var(--c-surface);border-radius:var(--radius);padding:1rem 1.2rem;border:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent)}
        .gd-tijdlijn li::before{content:"";position:absolute;left:-1.9rem;top:1.2rem;width:20px;height:20px;border-radius:50%;background:var(--c-primary);border:4px solid var(--c-bg)}
        .gd-tijdlijn li:last-child::before{background:var(--c-cta)}
        .gd-tijdlijn .wanneer{font-size:.74rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--c-cta)}
        .gd-tijdlijn h3{font-size:1.05rem;margin:.15rem 0 .3rem}
        .gd-tijdlijn p{margin:0;font-size:.95rem}
        .gd-tijdlijn a{font-weight:700;color:var(--c-primary);font-size:.9rem}

        /* AI-telefonie uitgelicht */
        .gd-tel{background:var(--c-ink);color:#e5e7eb;padding:64px 0}
        .gd-tel .kicker,.gd-tel .kicker-line{color:var(--c-accent);background-color:transparent}
        .gd-tel .kicker-line{background:var(--c-accent)}
        .gd-tel h2{color:#fff}
        .gd-tel-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:2.5rem;align-items:center}
        .gd-tel p.lead{color:#cbd5e1;max-width:50ch}
        .gd-bubbels{display:grid;gap:.7rem}
        .gd-bubbel{max-width:34ch;padding:.75rem 1rem;border-radius:16px 16px 16px 4px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);font-size:.98rem}
        .gd-bubbel:nth-child(2){margin-left:2.2rem}.gd-bubbel:nth-child(3){margin-left:4.4rem}.gd-bubbel:nth-child(4){margin-left:2.2rem}
        @media(max-width:480px){.gd-bubbel{margin-left:0!important;max-width:none}}
        .gd-tel-knoppen{display:flex;flex-wrap:wrap;gap:.7rem;margin-top:1.4rem;align-items:center}
        .gd-tel .btn-ghost{color:#fff;border-color:rgba(255,255,255,.5)}
        .gd-tel .demo{font-size:.9rem;color:#94a3b8}
        .gd-tel .demo a{color:#fff;font-weight:700}
        @media(max-width:860px){.gd-tel-grid{grid-template-columns:1fr}}

        /* Instap */
        .gd-instap{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;margin-top:1.4rem}
        @media(max-width:900px){.gd-instap{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:560px){.gd-instap{grid-template-columns:1fr}}

        .gd-slot .cta-band-inner{grid-template-columns:minmax(0,1fr) auto}
        .gd-slot .knoppen{display:flex;flex-wrap:wrap;gap:.7rem}
        .gd-slot .btn-ghost{color:#fff;border-color:rgba(255,255,255,.55)}
        @media(max-width:760px){.gd-slot .cta-band-inner{grid-template-columns:1fr}}
    </style>
@endpush

@section('content')
<div class="gd">
    @include('channels.partials.breadcrumb', ['items' => [['label' => 'Home', 'url' => $site->url('')], ['label' => 'De Groeidiamant']]])

    {{-- 1. Hero --}}
    <section class="hero" data-section="hero">
        <div class="wrap">
            <div class="gd-hero">
                <div>
                    <span class="eyebrow">{{ $c['hero']['eyebrow'] }}</span>
                    <h1>{{ $c['hero']['titel'] }}</h1>
                    <p class="lead">{{ $c['hero']['lead'] }}</p>
                    <div class="gd-hero-knoppen">
                        <a href="#waar-sta-je" class="btn">{{ $c['hero']['cta'] }}</a>
                        <a href="#gratis-voorbeeld" class="btn btn-ghost">{{ $c['hero']['cta2'] }}</a>
                    </div>
                </div>
                <div class="gd-steen" id="gd-steen">
                    <div class="gd-steen-glow" aria-hidden="true"></div>
                    {{-- Vijf facetten, van de punt (1, website) naar de kroon (5, AI). --}}
                    <svg viewBox="0 0 400 360" role="list" aria-label="De vijf stappen van de Groeidiamant">
                        @php
                            // y-grenzen per laag (van onder naar boven) en halve breedte op die hoogte.
                            $lagen = [
                                'website'        => ['y1' => 350, 'y0' => 262, 'x1' => 6,   'x0' => 92],
                                'webshop'        => ['y1' => 262, 'y0' => 194, 'x1' => 92,  'x0' => 150],
                                'klantenportaal' => ['y1' => 194, 'y0' => 136, 'x1' => 150, 'x0' => 190],
                                'automatisering' => ['y1' => 136, 'y0' => 92,  'x1' => 190, 'x0' => 190],
                                'ai'             => ['y1' => 92,  'y0' => 10,  'x1' => 190, 'x0' => 130],
                            ];
                        @endphp
                        @foreach ($lagen as $key => $l)
                            @php $f = $fasen[$key]; $ym = ($l['y0'] + $l['y1']) / 2; @endphp
                            <a href="#fase-{{ $key }}" role="listitem" data-fase="{{ $key }}" aria-label="Stap {{ $f['nr'] }}: {{ $f['titel'] }}">
                                <polygon points="{{ 200 - $l['x0'] }},{{ $l['y0'] }} {{ 200 + $l['x0'] }},{{ $l['y0'] }} {{ 200 + $l['x1'] }},{{ $l['y1'] }} {{ 200 - $l['x1'] }},{{ $l['y1'] }}"/>
                                <text x="200" y="{{ $ym - 4 }}" class="nr">STAP {{ $f['nr'] }}</text>
                                <text x="200" y="{{ $ym + 13 }}">{{ $f['titel'] === 'AI-telefonie & slimme assistentie' ? 'AI-telefonie' : $f['titel'] }}</text>
                            </a>
                        @endforeach
                    </svg>
                    <div class="gd-steen-tekst" id="gd-steen-tekst" aria-live="polite">
                        <strong>{{ $c['hero']['motto'] }}</strong>
                        <span class="muted">Ga met je muis over een stap, of tik erop, om te zien wat die voor {{ $c['w']['bedrijf'] }} betekent.</span>
                    </div>
                    <script type="application/json" id="gd-steen-data">{!! json_encode(array_map(fn ($f) => ['t' => 'Stap ' . $f['nr'] . ': ' . $f['titel'], 'b' => $f['voor']], $fasen), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. Waar sta je nu? --}}
    <section id="waar-sta-je" data-section="waar-sta-je" style="background:var(--c-surface);scroll-margin-top:80px">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Keuzehulp</span>
            <h2>Waar sta je nu?</h2>
            <p class="section-lead muted">Kies de zin die het meest op jou lijkt. Je komt bij de stap die daarbij hoort, met wat die voor {{ $c['w']['bedrijven'] }} betekent en wat hij oplevert.</p>
            <div class="gd-situaties">
                @php $icons = ['website' => 'globe', 'webshop' => 'cart', 'klantenportaal' => 'lock', 'automatisering' => 'gear', 'ai' => 'phone']; @endphp
                @foreach ($c['situaties'] as $key => $s)
                    <a class="gd-situatie" href="#fase-{{ $key }}">
                        <span class="ico">@include('channels.partials.icon', ['name' => $icons[$key] ?? 'check'])</span>
                        <h3>"{{ $s['t'] }}"</h3>
                        <p>{{ $s['b'] }}</p>
                        <span class="naar">{{ $s['naar'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 3. De vijf stappen --}}
    <section data-section="stappen">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Het model</span>
            <h2>De vijf stappen van de Groeidiamant</h2>
            <p class="section-lead muted">Vijf onderdelen die op elkaar aansluiten, maar ook los af te nemen zijn. Per stap: wat het is, wat het voor {{ $c['w']['bedrijven'] }} betekent en wat het oplevert.</p>
            @foreach ($fasen as $key => $f)
                <article class="gd-fase" id="fase-{{ $key }}">
                    <div class="gd-fase-nr" aria-hidden="true">{{ $f['nr'] }}</div>
                    <div>
                        <span class="muted" style="font-size:.8rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase">Stap {{ $f['nr'] }}</span>
                        <h3>{{ $f['titel'] }}</h3>
                        <div class="gd-fase-kolommen">
                            <div>
                                <h4>Wat is het?</h4>
                                <p>{{ $f['wat'] }}</p>
                            </div>
                            <div>
                                <h4>Wat betekent dit voor {{ $c['w']['bedrijven'] }}?</h4>
                                <p>{{ $f['voor'] }}</p>
                                @if (! empty($f['voorbeelden']))
                                    <ul>@foreach ((array) $f['voorbeelden'] as $vb)<li>{{ $vb }}</li>@endforeach</ul>
                                @endif
                            </div>
                            <div class="gd-fase-res">
                                <h4>Wat levert het op?</h4>
                                <p>{{ $f['resultaat'] }}</p>
                            </div>
                        </div>
                        <a href="{{ $f['url'] }}" class="btn {{ $key === 'ai' ? '' : 'btn-secondary' }}">{{ $f['cta'] }}</a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- 4. Wat levert iedere stap op? --}}
    <section data-section="resultaat" style="background:var(--c-surface)">
        <div class="wrap" style="max-width:860px">
            <span class="kicker"><span class="kicker-line"></span> In één oogopslag</span>
            <h2>Wat levert iedere stap op?</h2>
            <div class="gd-res">
                @foreach ($fasen as $key => $f)
                    <a href="{{ $f['url'] }}">
                        <span class="nr" aria-hidden="true">{{ $f['nr'] }}</span>
                        <span><strong>{{ $f['titel'] }}</strong><span class="uit">{{ $f['resultaat'] }}</span></span>
                        <span class="pijl">{{ $f['cta'] }} →</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 5. Praktijkvoorbeeld --}}
    <section data-section="praktijk">
        <div class="wrap" style="max-width:860px">
            <span class="kicker"><span class="kicker-line"></span> Een voorbeeld</span>
            <h2>Zo kan dit er in de praktijk uitzien</h2>
            <p class="section-lead muted">{{ $c['praktijk']['intro'] }}</p>
            <ol class="gd-tijdlijn">
                @foreach ((array) $c['praktijk']['stappen'] as $i => $st)
                    @php $fz = $fasen[$st['fase']] ?? null; @endphp
                    <li>
                        <span class="wanneer">{{ $i === 0 ? 'Begin' : ($loop->last ? 'Later' : 'Daarna') }}</span>
                        <h3>{{ $st['t'] }}</h3>
                        <p>{{ $st['b'] }}</p>
                        @if ($fz)<a href="{{ $fz['url'] }}">{{ $fz['cta'] }} →</a>@endif
                    </li>
                @endforeach
            </ol>
            <p class="muted" style="margin-top:1.2rem;font-size:.9rem">Dit is een voorbeeldvolgorde, geen vaste route. Wie al een goede website heeft, slaat de eerste stap over.</p>
        </div>
    </section>

    {{-- 6. Je hoeft niet bij stap 1 te beginnen --}}
    <section data-section="instap" style="background:color-mix(in srgb,var(--c-accent) 6%,transparent)">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Overal instappen</span>
            <h2>Je hoeft niet bij stap 1 te beginnen</h2>
            <p class="section-lead muted">{{ $c['instap']['lead'] }}</p>
            <div class="gd-instap">
                @foreach ((array) $c['instap']['punten'] as $p)
                    <div class="feature-card"><h3>{{ $p['t'] }}</h3><span class="feature-rule"></span><p>{{ $p['b'] }}</p></div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 7. AI-telefonie uitgelicht --}}
    <section class="gd-tel" data-section="telefonie" id="ai-telefonie">
        <div class="wrap">
            <div class="gd-tel-grid">
                <div>
                    <span class="kicker"><span class="kicker-line"></span> Stap 5, ook los af te nemen</span>
                    <h2>{{ $tel['titel'] }}</h2>
                    <p class="lead">{{ $tel['tekst'] }}</p>
                    <div class="gd-tel-knoppen">
                        <a href="{{ $tel['url'] }}" class="btn">{{ $tel['cta'] }}</a>
                        @if ($telNr)
                            <a href="{{ $telHref }}" class="btn btn-ghost">Bel de demo: {{ $telNr }}</a>
                        @endif
                    </div>
                    @if ($telNr)
                        <p class="demo" style="margin-top:.9rem">De demo is een verzonnen bedrijf uit jouw vak. Bel en vraag wat je wilt; je hoort waar hij stopt.</p>
                    @endif
                </div>
                <div class="gd-bubbels" aria-label="Voorbeelden van vragen die de assistent opvangt">
                    @foreach ((array) $tel['vragen'] as $v)
                        <div class="gd-bubbel">"{{ $v }}"</div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- 8. Waarom de Groeidiamant --}}
    <section data-section="waarom">
        <div class="wrap" style="max-width:760px">
            <div style="display:flex;gap:1.6rem;align-items:center;flex-wrap:wrap">
                <img src="{{ $gdLogo }}" alt="Groeidiamant by Betergeregeld ICT" width="120" height="120" loading="lazy" decoding="async" style="border-radius:14px;flex:none">
                <div style="flex:1 1 320px;min-width:0">
                    <h2 style="margin-bottom:.5rem">{{ $c['waarom']['titel'] }}</h2>
                    <p style="margin:0">{{ $c['waarom']['tekst'] }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 9. FAQ --}}
    <section data-section="faq" style="background:var(--c-surface)">
        <div class="wrap" style="max-width:860px">
            <span class="kicker"><span class="kicker-line"></span> Veelgestelde vragen</span>
            <h2>Veelgestelde vragen</h2>
            @include('channels.partials.faq-accordion', ['items' => (array) $c['faq']])
        </div>
    </section>

    {{-- 10. Eind-CTA --}}
    <section class="cta-band gd-slot" data-section="cta">
        <div class="wrap">
            <div class="cta-band-inner">
                <div>
                    <h2>{{ $c['slot']['titel'] }}</h2>
                    <p>{{ $c['slot']['tekst'] }}</p>
                </div>
                <div class="knoppen">
                    <a href="{{ $c['afspraakUrl'] }}?onderwerp=groeidiamant" class="btn">{{ $c['slot']['cta'] }}</a>
                    <a href="#gratis-voorbeeld" class="btn btn-ghost">{{ $c['slot']['cta2'] }}</a>
                </div>
            </div>
        </div>
    </section>

    {{-- 11. Formulier: zelfde wizard als overal, alleen de eerste vraag is "Waar wil je mee beginnen?". --}}
    <div id="contact" class="scroll-anchor" aria-hidden="true"></div>
    @include('channels.partials.lead-wizard', [
        'site'      => $site,
        'facet'     => 'website',
        'goalVraag' => $c['formulier']['vraag'],
        'goals'     => (array) $c['formulier']['opties'],
    ])
</div>

<script>
(function () {
    // De diamant in de hero: hover/focus op een facet toont de branchezin eronder.
    var steen = document.getElementById('gd-steen'); if (!steen) return;
    var uit = document.getElementById('gd-steen-tekst'), data;
    try { data = JSON.parse(document.getElementById('gd-steen-data').textContent); } catch (e) { return; }
    var rust = uit.innerHTML, facetten = steen.querySelectorAll('a[data-fase]'), timer = null;
    function toon(key) {
        var d = data[key]; if (!d) return;
        facetten.forEach(function (a) { a.classList.toggle('is-aan', a.dataset.fase === key); });
        uit.innerHTML = '<strong></strong><span></span>';
        uit.firstChild.textContent = d.t; uit.lastChild.textContent = d.b;
    }
    function weg() { facetten.forEach(function (a) { a.classList.remove('is-aan'); }); uit.innerHTML = rust; }
    facetten.forEach(function (a) {
        a.addEventListener('mouseenter', function () { clearTimeout(timer); toon(a.dataset.fase); });
        a.addEventListener('focus', function () { toon(a.dataset.fase); });
        a.addEventListener('mouseleave', function () { timer = setTimeout(weg, 400); });
        a.addEventListener('blur', weg);
    });
})();
</script>
@endsection
