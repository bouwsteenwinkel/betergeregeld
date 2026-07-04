@php
    /** @var \App\Support\ChannelSite $site */
    $cases = (array) config('cases_' . $site->key . '.cases', []);
    $imgGen = app(\App\Services\ChannelSites\ChannelImageGenerator::class);
@endphp
@extends('channels.layout')

@section('title', 'Voorbeeldcases')
@section('description', 'Zo ziet groei met de Groeidiamant eruit: voorbeeldcases van een website, webshop en slimme tools voor badkamerbedrijven.')

@section('content')
    @include('channels.partials.breadcrumb', ['items' => [['label' => 'Home', 'url' => $site->url('')], ['label' => 'Cases']]])
    <section class="hero">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Cases</span>
            <h1>Zo ziet groei eruit</h1>
            <p class="lead" style="max-width:60ch">Voorbeelden van wat een sterke online aanpak oplevert voor een badkamerbedrijf, van een eerste website tot webshop en slimme tools. Zo zou het ook voor jou kunnen werken.</p>
        </div>
    </section>

    <style>
        .case{display:grid;grid-template-columns:1fr 1fr;gap:2.6rem;align-items:center;margin:0 0 1rem}
        .case:nth-child(even) .case-media{order:2}
        .case-media img{width:100%;aspect-ratio:3/2;object-fit:cover;border-radius:var(--radius);box-shadow:0 24px 60px -30px rgba(0,0,0,.45);display:block}
        .case-badge{display:inline-block;background:color-mix(in srgb,var(--c-accent) 16%,transparent);color:var(--c-primary);font-weight:700;font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;padding:.3rem .75rem;border-radius:999px;margin-bottom:.8rem}
        .case h2{margin-bottom:.2rem}
        .case-place{color:var(--c-muted);font-weight:600;margin-bottom:1rem}
        .case-body p{margin:0 0 .8rem}
        .case-stat{font-family:var(--font-display);font-size:1.5rem;font-weight:800;color:var(--c-primary);margin:1rem 0 .2rem}
        .case-quote{border-left:3px solid var(--c-accent);padding:.4rem 0 .4rem 1rem;margin-top:1.2rem;font-style:italic;color:var(--c-ink)}
        .case-quote cite{display:block;font-style:normal;font-weight:600;color:var(--c-muted);margin-top:.4rem;font-size:.9rem}
        @media(max-width:820px){.case{grid-template-columns:1fr;gap:1.4rem}.case:nth-child(even) .case-media{order:0}}
    </style>

    <section>
        <div class="wrap" style="display:grid;gap:3.4rem">
            @foreach ($cases as $case)
                @php $img = $imgGen->url($site->key, 'case-' . ($case['slug'] ?? '')); @endphp
                <div class="case">
                    <div class="case-media">
                        @if ($img)<img src="{{ $img }}" alt="Resultaat voor {{ $case['company'] ?? '' }}" loading="lazy" decoding="async">@endif
                    </div>
                    <div class="case-content">
                        @if (!empty($case['product']))<span class="case-badge">{{ $case['product'] }}</span>@endif
                        <h2>{{ $case['company'] ?? '' }}</h2>
                        @if (!empty($case['place']))<div class="case-place">{{ $case['place'] }}</div>@endif
                        <div class="case-body">
                            @if (!empty($case['challenge']))<p><strong>De uitdaging.</strong> {{ $case['challenge'] }}</p>@endif
                            @if (!empty($case['solution']))<p><strong>Wat we deden.</strong> {{ $case['solution'] }}</p>@endif
                            @if (!empty($case['result']))<p><strong>Het resultaat.</strong> {{ $case['result'] }}</p>@endif
                        </div>
                        @if (!empty($case['stat']))<div class="case-stat">{{ $case['stat'] }}</div>@endif
                        @if (!empty($case['quote']))
                            <blockquote class="case-quote">"{{ $case['quote'] }}"@if (!empty($case['author']))<cite>{{ $case['author'] }}</cite>@endif</blockquote>
                        @endif
                    </div>
                </div>
            @endforeach
            <p class="muted" style="font-size:.82rem;text-align:center;max-width:70ch;margin:0 auto">Dit zijn illustratieve voorbeeldcases die laten zien wat mogelijk is. Benieuwd wat we voor jouw bedrijf kunnen betekenen? Vraag een gratis voorbeeld aan.</p>
        </div>
    </section>

    @include('channels.partials.sales-trust', ['site' => $site, 'ctaTitle' => 'Ook zulke resultaten voor jouw bedrijf?'])

    <div id="contact" class="scroll-anchor" aria-hidden="true"></div>
    @include('channels.partials.lead-wizard', ['site' => $site, 'facet' => 'website'])
@endsection
