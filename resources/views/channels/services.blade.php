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

@section('title', 'Diensten: website, webshop, portaal, automatisering en AI')
@section('description', $r($cfg['intro'] ?? 'Alles wat we voor je bouwen, van een professionele website tot slimme automatisering en AI.'))

@section('content')
    @include('channels.partials.breadcrumb', ['items' => [['label' => 'Home', 'url' => $site->url('')], ['label' => 'Diensten']]])
    <style>
        .svc{padding:56px 0}
        .svc:nth-child(even){background:var(--c-surface)}
        .svc-inner{display:grid;grid-template-columns:1.1fr .9fr;gap:2.6rem;align-items:center;max-width:1040px;margin:0 auto}
        .svc:nth-child(even) .svc-inner{grid-template-columns:.9fr 1.1fr}
        .svc:nth-child(even) .svc-text{order:2}
        .svc-ic{display:inline-flex;align-items:center;justify-content:center;width:52px;height:52px;border-radius:14px;
            background:color-mix(in srgb,var(--c-primary) 12%,transparent);color:var(--c-primary);margin-bottom:1rem}
        .svc-ic svg{width:26px;height:26px}
        .svc-step{text-transform:uppercase;letter-spacing:.14em;font-size:.72rem;font-weight:700;color:var(--c-muted)}
        .svc-text h2{font-size:clamp(1.6rem,3.4vw,2.1rem);margin:.2rem 0 .4rem}
        .svc-tagline{font-weight:600;color:var(--c-primary);margin-bottom:.9rem}
        .svc-list{list-style:none;margin:1.1rem 0 0;display:grid;gap:.55rem}
        .svc-list li{padding-left:1.7rem;position:relative}
        .svc-list li:before{content:"";position:absolute;left:0;top:.15rem;width:18px;height:18px;border-radius:50%;
            background:color-mix(in srgb,var(--c-primary) 16%,transparent)}
        .svc-list li:after{content:"";position:absolute;left:6px;top:.42rem;width:6px;height:3px;border-left:2px solid var(--c-primary);
            border-bottom:2px solid var(--c-primary);transform:rotate(-45deg)}
        .svc-example{background:var(--c-bg);border:1px solid color-mix(in srgb,var(--c-ink) 10%,transparent);
            border-radius:calc(var(--radius) + 4px);padding:1.6rem 1.8rem;box-shadow:0 20px 50px -30px rgba(0,0,0,.4)}
        .svc:nth-child(even) .svc-example{background:var(--c-bg)}
        .svc-example-label{display:inline-flex;align-items:center;gap:.5rem;text-transform:uppercase;letter-spacing:.12em;
            font-size:.72rem;font-weight:700;color:var(--c-cta);margin-bottom:.7rem}
        .svc-example p{font-size:1.05rem;line-height:1.6;margin:0}
        @media(max-width:820px){
            .svc,.svc:nth-child(even){padding:36px 0}
            .svc-inner,.svc:nth-child(even) .svc-inner{grid-template-columns:1fr;gap:1.4rem}
            .svc:nth-child(even) .svc-text{order:0}
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
    @include('channels.partials.facet-strip', [
        'site'   => $site,
        'facets' => (array) config('groeidiamant.facets', []),
        'kicker' => 'In het kort',
        'title'  => 'Vijf stappen, één aanpak',
        'lead'   => 'Klik op een dienst voor de volledige landingspagina met een echt voorbeeld, of lees hieronder rustig door.',
    ])

    @php $i = 0; @endphp
    @foreach ($services as $key => $s)
        @php $i++; @endphp
        <section class="svc" id="{{ $key }}">
            <div class="wrap">
                <div class="svc-inner">
                    <div class="svc-text">
                        <span class="svc-ic">@include('channels.partials.icon', ['name' => $s['icon'] ?? 'check'])</span>
                        <div class="svc-step">Stap {{ $i }} van {{ count($services) }}</div>
                        <h2>{{ $s['label'] ?? $key }}</h2>
                        <p class="svc-tagline">{{ $r($s['tagline'] ?? '') }}</p>
                        <p>{{ $r($s['intro'] ?? '') }}</p>
                        @if (!empty($s['bullets']))
                            <ul class="svc-list">
                                @foreach ($s['bullets'] as $b)<li>{{ $r($b) }}</li>@endforeach
                            </ul>
                        @endif
                        <p style="margin-top:1.3rem">
                            <a href="{{ $site->url($key) }}" class="btn btn-ghost">Bekijk {{ strtolower($s['label'] ?? $key) }} in detail →</a>
                        </p>
                    </div>
                    @if (!empty($s['example']))
                        <div class="svc-example">
                            <span class="svc-example-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px"><path d="M9 18h6M10 22h4M12 2a7 7 0 0 0-4 12.7c.6.5 1 1.2 1 2h6c0-.8.4-1.5 1-2A7 7 0 0 0 12 2Z"/></svg>
                                Voorbeeld uit de praktijk
                            </span>
                            <p>{{ $r($s['example']) }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endforeach

    @include('channels.partials.sales-trust', ['site' => $site, 'ctaTitle' => $r($cfg['cta_title'] ?? 'Benieuwd wat er voor jou mogelijk is?')])

    <div id="contact" class="scroll-anchor" aria-hidden="true"></div>
    @include('channels.partials.lead-wizard', ['site' => $site, 'facet' => 'website'])
@endsection
