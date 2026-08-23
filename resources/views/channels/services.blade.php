@php
    /** @var \App\Support\ChannelSite $site */
    $cfg      = (array) config('channel_services', []);
    $services = (array) ($cfg['services'] ?? []);

    // Trade-tokens (zelfde bron als de plaatsen-content) zodat de teksten per niche kloppen.
    $t   = array_merge((array) config('channel_places.defaults', []), array_filter((array) $site->get('places', []), fn ($v) => is_scalar($v) && $v !== ''));
    $map = [':trades' => $t['trades'] ?? 'bedrijven', ':trade' => $t['trade'] ?? 'bedrijf', ':niches' => $t['niches'] ?? 'diensten', ':niche' => $t['niche'] ?? 'vak', ':service' => $t['service'] ?? 'website'];
    $r   = fn ($s) => strtr((string) $s, $map);
@endphp
@extends('channels.layout')

@section('title', $r('Wat we bouwen voor :trades: website, webshop, portaal, automatisering en AI'))
@section('description', $r($cfg['intro'] ?? 'Alles wat we voor je bouwen, van een professionele website tot slimme automatisering en AI.'))

@push('head')
    @php
        $svcLd = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Service',
            'serviceType' => $r($cfg['h1'] ?? 'Diensten'),
            'provider'    => ['@id' => rtrim($site->baseUrl(), '/') . '#org'],
            'areaServed'  => ['@type' => 'Country', 'name' => 'Nederland'],
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name'  => 'Diensten',
                'itemListElement' => array_values(array_map(fn ($s) => [
                    '@type'       => 'Offer',
                    'itemOffered' => ['@type' => 'Service', 'name' => $s['label'] ?? '', 'description' => $r($s['tagline'] ?? '')],
                ], $services)),
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($svcLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
    @include('channels.partials.breadcrumb', ['items' => [['label' => 'Home', 'url' => $site->url('')], ['label' => 'Diensten']]])
    <style>
        /* Diensten als accordion: compact, mobiel-vriendelijk, en SEO-veilig
           (alle tekst blijft in de HTML; native <details> werkt ook zonder JS). */
        .svc-acc{max-width:880px;margin:.4rem auto 0;display:grid;gap:.9rem}
        .svc-item{background:var(--c-bg);border:1px solid color-mix(in srgb,var(--c-ink) 10%,transparent);
            border-radius:calc(var(--radius) + 2px);overflow:hidden;box-shadow:0 16px 42px -34px rgba(0,0,0,.5);transition:border-color .2s,box-shadow .2s}
        .svc-item[open]{border-color:color-mix(in srgb,var(--c-primary) 32%,transparent);box-shadow:0 26px 56px -30px rgba(0,0,0,.4)}
        .svc-sum{list-style:none;display:flex;align-items:center;gap:1rem;padding:1.05rem 1.3rem;cursor:pointer;user-select:none}
        .svc-sum::-webkit-details-marker{display:none}
        .svc-sum:hover{background:color-mix(in srgb,var(--c-ink) 3%,transparent)}
        .svc-ic{display:inline-flex;align-items:center;justify-content:center;width:46px;height:46px;border-radius:12px;flex:0 0 auto;
            background:color-mix(in srgb,var(--c-primary) 12%,transparent);color:var(--c-primary);transition:background .2s,color .2s}
        .svc-ic svg{width:24px;height:24px}
        .svc-item[open] .svc-ic{background:var(--c-primary);color:#fff}
        .svc-sum-txt{display:flex;flex-direction:column;min-width:0;flex:1}
        .svc-step{text-transform:uppercase;letter-spacing:.14em;font-size:.68rem;font-weight:700;color:var(--c-muted)}
        .svc-sum-title{font-weight:700;font-size:1.12rem;line-height:1.25;margin-top:.05rem}
        .svc-sum-tag{color:var(--c-muted);font-size:.92rem;line-height:1.3;margin-top:.1rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .svc-item[open] .svc-sum-tag{display:none}
        .svc-chev{width:22px;height:22px;flex:0 0 auto;color:var(--c-muted);transition:transform .25s ease,color .2s}
        .svc-item[open] .svc-chev{transform:rotate(180deg);color:var(--c-primary)}
        .svc-panel{padding:.2rem 1.4rem 1.5rem;padding-left:calc(1.3rem + 46px + 1rem)}
        .svc-item[open] .svc-panel{animation:svcFade .28s ease}
        @keyframes svcFade{from{opacity:0;transform:translateY(-5px)}to{opacity:1;transform:none}}
        .svc-tagline{font-weight:600;color:var(--c-primary);margin:0 0 .7rem}
        .svc-list{list-style:none;margin:1rem 0 0;display:grid;gap:.55rem}
        .svc-list li{padding-left:1.7rem;position:relative}
        .svc-list li:before{content:"";position:absolute;left:0;top:.15rem;width:18px;height:18px;border-radius:50%;
            background:color-mix(in srgb,var(--c-primary) 16%,transparent)}
        .svc-list li:after{content:"";position:absolute;left:6px;top:.42rem;width:6px;height:3px;border-left:2px solid var(--c-primary);
            border-bottom:2px solid var(--c-primary);transform:rotate(-45deg)}
        .svc-example{margin-top:1.2rem;background:var(--c-surface);border:1px solid color-mix(in srgb,var(--c-ink) 10%,transparent);
            border-radius:var(--radius);padding:1.1rem 1.3rem}
        .svc-example-label{display:inline-flex;align-items:center;gap:.5rem;text-transform:uppercase;letter-spacing:.12em;
            font-size:.7rem;font-weight:700;color:var(--c-cta);margin-bottom:.5rem}
        .svc-example p{font-size:1rem;line-height:1.6;margin:0}
        @media(max-width:620px){
            .svc-panel{padding-left:1.4rem}
            .svc-sum-tag{white-space:normal}
        }
    </style>

    <section class="hero hero--slim">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> {{ $cfg['eyebrow'] ?? 'Onze diensten' }}</span>
            <h1>{{ $r($cfg['h1'] ?? 'Wat we voor je bouwen') }}</h1>
            <p class="lead" style="max-width:60ch">{{ $r($cfg['intro'] ?? '') }}</p>
        </div>
    </section>

    {{-- Snelnavigatie naar de facet-landingspagina's --}}
    @include('channels.partials.groeipad', [
        'site'   => $site,
        'facets' => (array) config('groeidiamant.facets', []),
        'kicker' => 'In het kort',
        'title'  => 'Vijf stappen, één aanpak',
        'lead'   => 'Klik op een dienst voor de volledige landingspagina met een echt voorbeeld, of lees hieronder rustig door.',
    ])

    <section style="padding:8px 0 56px">
        <div class="wrap">
            <div class="svc-acc">
                @foreach ($services as $key => $s)
                    <details class="svc-item" id="{{ $key }}" @if ($loop->first) open @endif>
                        <summary class="svc-sum">
                            <span class="svc-ic">@include('channels.partials.icon', ['name' => $s['icon'] ?? 'check'])</span>
                            <span class="svc-sum-txt">
                                <span class="svc-step">Stap {{ $loop->iteration }} van {{ $loop->count }}</span>
                                <span class="svc-sum-title">{{ $s['label'] ?? $key }}</span>
                                <span class="svc-sum-tag">{{ $r($s['tagline'] ?? '') }}</span>
                            </span>
                            <svg class="svc-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </summary>
                        <div class="svc-panel">
                            <p class="svc-tagline">{{ $r($s['tagline'] ?? '') }}</p>
                            <p>{{ $r($s['intro'] ?? '') }}</p>
                            @if (!empty($s['bullets']))
                                <ul class="svc-list">
                                    @foreach ($s['bullets'] as $b)<li>{{ $r($b) }}</li>@endforeach
                                </ul>
                            @endif
                            @if (!empty($s['example']))
                                <div class="svc-example">
                                    <span class="svc-example-label">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px"><path d="M9 18h6M10 22h4M12 2a7 7 0 0 0-4 12.7c.6.5 1 1.2 1 2h6c0-.8.4-1.5 1-2A7 7 0 0 0 12 2Z"/></svg>
                                        Voorbeeld uit de praktijk
                                    </span>
                                    <p>{{ $r($s['example']) }}</p>
                                </div>
                            @endif
                            <p style="margin:1.3rem 0 0">
                                <a href="{{ $site->url($key) }}" class="btn btn-ghost">Bekijk {{ strtolower($s['label'] ?? $key) }} in detail →</a>
                            </p>
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <script>
    (function () {
        var acc = document.querySelector('.svc-acc');
        if (!acc) return;
        var items = acc.querySelectorAll('details.svc-item');
        // Accordion-gedrag: er staat er hooguit één open.
        items.forEach(function (d) {
            d.addEventListener('toggle', function () {
                if (!d.open) return;
                items.forEach(function (o) { if (o !== d && o.open) o.open = false; });
            });
        });
        // Diepe link (#website vanuit de snelnavigatie) opent het juiste item.
        function openFromHash() {
            var h = location.hash.slice(1);
            if (!h) return;
            var el = document.getElementById(h);
            if (el && el.tagName.toLowerCase() === 'details') el.open = true;
        }
        window.addEventListener('hashchange', openFromHash);
        openFromHash();
    })();
    </script>

    @include('channels.partials.sales-trust', ['site' => $site, 'ctaTitle' => $r($cfg['cta_title'] ?? 'Benieuwd wat er voor jou mogelijk is?')])

    <div id="contact" class="scroll-anchor" aria-hidden="true"></div>
    @include('channels.partials.lead-wizard', ['site' => $site, 'facet' => 'website'])
@endsection
