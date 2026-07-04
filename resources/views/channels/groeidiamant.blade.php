@php
    /** @var \App\Support\ChannelSite $site */
    $facets = (array) config('groeidiamant.facets', []);
    $gdLogo = asset('channel-media/_brand/groeidiamant.jpg');

    // Per facet een heldere uitleg (wat die fase betekent). Generiek — geldt voor
    // elke niche-site die de Groeidiamant volgt.
    $uitleg = [
        'website'        => 'Een professionele website die je vakwerk laat zien en gevonden wordt in Google. De basis waarop al het andere voortbouwt.',
        'webshop'        => 'Verkoop producten of complete pakketten online, gekoppeld aan je site. Je etalage staat 24/7 open, met veilig betalen en bezorgen of afhalen.',
        'klantenportaal' => 'Laat klanten zelf afspraken plannen, hun project volgen en documenten terugvinden in een eigen omgeving. Minder gebel, meer overzicht.',
        'automatisering' => 'Offertes, facturen en planning die zichzelf doen. Koppelingen tussen je website, agenda en boekhouding, zodat je niets dubbel invoert.',
        'ai'             => 'Een slimme assistent die telefoon en chat aanneemt, aanvragen filtert en je offerte voorbereidt. Altijd bereikbaar, ook als jij aan het werk bent.',
    ];
@endphp
@extends('channels.layout')

@section('title', 'De Groeidiamant: groeien zonder opnieuw te beginnen')
@section('description', 'De Groeidiamant van Betergeregeld ICT: een groeimodel in vijf fasen, van website tot slimme AI. Begin waar je nu staat en breid uit wanneer je eraan toe bent.')

@section('content')
    @include('channels.partials.breadcrumb', ['items' => [['label' => 'Home', 'url' => $site->url('')], ['label' => 'De Groeidiamant']]])

    {{-- Hero met logo-presentatie --}}
    <section class="hero">
        <div class="wrap">
            <style>
                .gd-hero{display:flex;align-items:center;gap:3rem;flex-wrap:wrap}
                .gd-hero-txt{flex:1 1 320px;min-width:0}
                .gd-hero-logo{flex:0 0 auto;position:relative;padding:1.1rem;border-radius:24px;background:#fff;
                    box-shadow:0 30px 70px -28px color-mix(in srgb,var(--c-primary) 60%,transparent);
                    border:1px solid color-mix(in srgb,var(--c-primary) 14%,transparent)}
                .gd-hero-logo::before{content:"";position:absolute;inset:-25% -18% -25% -18%;z-index:-1;
                    background:radial-gradient(circle,color-mix(in srgb,var(--c-primary) 28%,transparent),transparent 70%);filter:blur(30px)}
                .gd-hero-logo img{display:block;width:300px;max-width:44vw;height:auto;border-radius:14px}
                @media(max-width:760px){.gd-hero{gap:1.8rem}
                    /* Logo-kaart over de volle breedte, logo gecentreerd. */
                    .gd-hero-logo{flex:1 1 100%;width:100%;text-align:center;padding:1.6rem}
                    .gd-hero-logo img{width:300px;max-width:78vw;margin:0 auto}}
            </style>
            <div class="gd-hero">
                <div class="gd-hero-txt">
                    <span class="eyebrow">Groeidiamant by Betergeregeld ICT</span>
                    <h1>Groei zonder ooit opnieuw te beginnen</h1>
                    <p class="lead" style="max-width:52ch">De Groeidiamant is ons groeimodel in vijf fasen. Je begint waar je nu staat, meestal met een sterke website, en breidt uit wanneer je eraan toe bent. Elke fase bouwt voort op de vorige, dus je gooit nooit iets weg.</p>
                    <a href="{{ $site->navHref('#gratis-voorbeeld') }}" class="btn">Gratis voorbeeld aanvragen</a>
                </div>
                <div class="gd-hero-logo">
                    <img src="{{ $gdLogo }}" alt="Groeidiamant by Betergeregeld ICT" width="300" loading="eager" decoding="async">
                </div>
            </div>
        </div>
    </section>

    {{-- Waarom een diamant --}}
    <section style="background:var(--c-surface)">
        <div class="wrap" style="max-width:760px">
            <span class="kicker"><span class="kicker-line"></span> De gedachte erachter</span>
            <h2>Waarom een diamant?</h2>
            <div class="prose" style="margin-top:1rem">
                <p>Een diamant ontstaat laag voor laag, onder druk, en wordt met elke laag waardevoller. Zo zien wij ook online groei. Je hoeft niet alles in één keer te bouwen en je hoeft nergens opnieuw te beginnen. Je zet een stevige basis neer en voegt toe wat je nodig hebt, wanneer het past bij je bedrijf.</p>
                <p>Dat betekent: geen dure alles-of-niets-projecten, geen systemen die elkaar niet begrijpen, en geen weggegooid werk. Alles wat je bouwt blijft staan en werkt met de volgende stap samen.</p>
            </div>
        </div>
    </section>

    {{-- De vijf fasen --}}
    <section>
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> De vijf fasen</span>
            <h2>Van eerste website tot slimme assistent</h2>
            <p class="section-lead muted">Elke fase is een stap verder in de Groeidiamant. Je kiest zelf hoe ver je gaat.</p>
            <div class="grid cols-3 feature-grid">
                @foreach ($facets as $key => $f)
                    <div class="feature-card">
                        <div class="feature-ico">@include('channels.partials.icon', ['name' => $f['icon'] ?? 'check'])</div>
                        <h3>{{ $f['nr'] ?? '' }}. {{ $f['label'] ?? $key }}</h3>
                        <span class="feature-rule"></span>
                        <p>{{ $uitleg[$key] ?? ($f['tagline'] ?? '') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Aanpak + CTA + wizard (gedeeld) --}}
    @include('channels.partials.sales-trust', ['site' => $site, 'ctaTitle' => 'Benieuwd waar jij nu staat in de Groeidiamant?'])

    <div id="contact" class="scroll-anchor" aria-hidden="true"></div>
    @include('channels.partials.lead-wizard', ['site' => $site, 'facet' => 'website'])

@endsection
