@php
    /** @var \App\Support\ChannelSite $site */
    $p        = (array) config('channel_pricing', []);
    $packages = (array) ($p['packages'] ?? []);
@endphp
@extends('channels.layout')

@section('title', $p['h1'] ?? 'Prijzen')
@section('description', $p['intro'] ?? 'Duidelijke richtprijzen en pakketten. Vooraf een vaste prijs en een gratis voorbeeld van jouw bedrijf.')

@section('content')

    <section class="hero">
        <div class="wrap">
            <span class="eyebrow">{{ $p['eyebrow'] ?? 'Prijzen' }}</span>
            <h1>{{ $p['h1'] ?? 'Duidelijke prijzen, geen verrassingen' }}</h1>
            @if (!empty($p['intro']))<p class="lead" style="max-width:58ch">{{ $p['intro'] }}</p>@endif
        </div>
    </section>

    <section>
        <div class="wrap">
            <style>
                .price-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.2rem;align-items:stretch}
                .price-card{display:flex;flex-direction:column;background:var(--c-surface);border:1px solid color-mix(in srgb,var(--c-ink) 10%,transparent);
                    border-radius:calc(var(--radius) + 2px);padding:1.8rem 1.6rem;position:relative}
                .price-card.is-featured{border:2px solid var(--c-primary);box-shadow:0 24px 50px -28px color-mix(in srgb,var(--c-primary) 55%,transparent)}
                .price-badge{position:absolute;top:-.8rem;left:1.6rem;background:var(--c-primary);color:#fff;font-size:.72rem;font-weight:800;
                    text-transform:uppercase;letter-spacing:.08em;padding:.3rem .7rem;border-radius:999px}
                .price-name{font-family:var(--font-display);font-size:1.5rem;font-weight:700}
                .price-tag{color:var(--c-muted);font-size:.92rem;margin-bottom:1rem}
                .price-amount{font-size:2rem;font-weight:800;line-height:1.1;color:var(--c-ink)}
                .price-period{font-size:.9rem;color:var(--c-muted);font-weight:600}
                .price-setup{font-size:.85rem;color:var(--c-muted);margin-top:.2rem}
                .price-feats{list-style:none;margin:1.2rem 0 1.6rem;padding:0;display:grid;gap:.6rem}
                .price-feats li{display:flex;gap:.6rem;align-items:flex-start;font-size:.95rem}
                .price-feats li span{display:inline-flex;color:var(--c-primary);flex:0 0 auto;margin-top:.1rem}
                .price-card .btn{margin-top:auto;width:100%}
                @media(max-width:820px){.price-grid{grid-template-columns:1fr;max-width:440px;margin:0 auto}}
            </style>
            <div class="price-grid">
                @foreach ($packages as $pk)
                    <div class="price-card {{ !empty($pk['featured']) ? 'is-featured' : '' }}">
                        @if (!empty($pk['featured']))<span class="price-badge">Populair</span>@endif
                        <div class="price-name">{{ $pk['name'] ?? '' }}</div>
                        <div class="price-tag">{{ $pk['tagline'] ?? '' }}</div>
                        <div class="price-amount">{{ $pk['price'] ?? '' }} @if (!empty($pk['period']))<span class="price-period">{{ $pk['period'] }}</span>@endif</div>
                        @if (!empty($pk['setup']))<div class="price-setup">{{ $pk['setup'] }}</div>@endif
                        <ul class="price-feats">
                            @foreach ((array) ($pk['features'] ?? []) as $feat)
                                <li><span>@include('channels.partials.icon', ['name' => 'check'])</span>{{ $feat }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ $site->navHref('#gratis-voorbeeld') }}" class="btn {{ empty($pk['featured']) ? 'btn-ghost' : '' }}">Gratis voorbeeld aanvragen</a>
                    </div>
                @endforeach
            </div>
            @if (!empty($p['note']))<p class="muted" style="font-size:.88rem;margin-top:1.6rem;max-width:70ch">{{ $p['note'] }}</p>@endif
        </div>
    </section>

    {{-- Koppeling met de Groeidiamant --}}
    @include('channels.partials.facet-strip', [
        'site'   => $site,
        'facets' => (array) config('groeidiamant.facets', []),
        'kicker' => 'De Groeidiamant',
        'title'  => 'Groei stap voor stap',
        'lead'   => 'Elk pakket sluit aan op een fase van de Groeidiamant. Je begint klein en breidt uit wanneer je eraan toe bent. Je gooit nooit iets weg.',
        'bg'     => 'var(--c-surface)',
    ])

    @include('channels.partials.sales-trust', ['site' => $site, 'ctaTitle' => 'Benieuwd wat het voor jouw bedrijf kost?'])

    <div id="contact" class="scroll-anchor" aria-hidden="true"></div>
    @include('channels.partials.lead-wizard', ['site' => $site, 'facet' => 'website'])

@endsection
