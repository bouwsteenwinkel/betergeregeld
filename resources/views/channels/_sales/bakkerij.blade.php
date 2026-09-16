@php
    /** @var \App\Support\ChannelSite $site */
    // Homepage van jouw-bakkerij-website.nl. Herschreven 15-09-2026 (docs/bakkerij/SEO-CONTENT-AUDIT.md):
    // de H1 noemt nu wat we bouwen en voor wie, de "Herken je dit?"-kaarten zijn bakkerijsituaties
    // (geen klussen en offertes), er is een AI-telefonie-sectie met eigen CTA's, en de
    // Groeidiamant-taglines zijn per stap in bakkerijtaal. De vijf facet-URL's en de rest van de
    // opbouw zijn ongewijzigd.
    $facets   = $facets ?? (array) config('groeidiamant.facets', []);
    $landings = (array) config('bakkerij_landings', []);
    $heroImg  = $site->image('hero');
    $heroSet  = $site->imageSrcset('hero');

    // Taglines in het groeipad per stap in bakkerijtaal; de globale config blijft voor de
    // andere kanalen zoals hij is.
    $bakkerijTaglines = [
        'website'        => 'Gevonden worden, assortiment en openingstijden, bestellen of bellen',
        'webshop'        => 'Brood, banket en taarten online bestellen, met afhaaltijd en vooraf betalen',
        'klantenportaal' => 'Zakelijke klanten bestellen en herhalen zelf, facturen op één plek',
        'automatisering' => 'Bestellingen vanzelf op de productielijst en in de administratie',
        'ai'             => 'De telefoon wordt opgenomen, ook als jij in de bakkerij staat',
    ];
    foreach ($bakkerijTaglines as $k => $tl) {
        if (isset($facets[$k])) { $facets[$k]['tagline'] = $tl; }
    }

    $tel      = (array) config('bakkerij_telefonie', []);
    $demo     = trim((string) ($tel['demo_nummer'] ?? ''));
    $demoTel  = preg_replace('/[^0-9+]/', '', $demo);
@endphp
@extends('channels.layout')

@section('title', 'Website, webshop en AI-telefonie voor bakkerijen laten maken')
@section('description', 'Website of webshop voor je bakkerij laten maken, met online bestellen, een klantenportaal voor zakelijke klanten, automatisering en AI-telefonie die opneemt als jij bakt. Begin met wat je nu nodig hebt.')

@if ($heroImg)
    @push('head')
        <link rel="preload" as="image" href="{{ $heroImg }}" @if ($heroSet) imagesrcset="{{ $heroSet }}" imagesizes="(max-width:760px) 92vw, 46vw" @endif fetchpriority="high">
    @endpush
@endif

@section('content')

    <section class="hero" data-section="hero">
        <div class="wrap">
            <div @if ($heroImg) class="grid cols-2" style="align-items:start;gap:2.6rem" @endif>
                <div>
                    <span class="eyebrow">Voor bakkerijen</span>
                    <h1>Website, webshop en slimme automatisering voor jouw bakkerij</h1>
                    <p class="lead">Van een website waarop klanten je vinden en je assortiment zien, tot online bestellen, een portaal voor zakelijke klanten en een telefoon die wordt opgenomen terwijl jij bakt. Begin met wat je nu nodig hebt en breid later eenvoudig uit.</p>
                    <a href="#gratis-voorbeeld" class="btn">Gratis voorbeeld aanvragen</a>
                    <p class="muted" style="margin-top:.8rem;font-size:.9rem">Gratis &middot; vrijblijvend &middot; voorbeeld van jóuw site, vaak binnen 1 à 2 dagen</p>
                    <ul class="hero-usps">
                        <li>Gevonden worden op "bakkerij" plus jouw plaats</li>
                        <li>Taarten en bestellingen online in plaats van via briefjes en WhatsApp</li>
                        <li>De telefoon opgenomen als jij klanten helpt of in de bakkerij staat</li>
                    </ul>
                </div>
                @if ($heroImg)
                    <div>
                        <img src="{{ $heroImg }}"
                             @if ($heroSet) srcset="{{ $heroSet }}" sizes="(max-width:760px) 92vw, 46vw" @endif
                             alt="Voorbeeld van een bakkerij-website met assortiment en online bestellen" loading="eager" decoding="async" width="1536" height="1024"
                             style="width:100%;height:auto;border-radius:var(--radius);display:block;box-shadow:0 24px 60px -24px rgba(0,0,0,.4)">
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- AI-telefonie: inkomend gesprek als CTA (config bakkerij_telefonie.home_cta); verving op 16-09 de tekstsectie verderop. --}}
    @include('channels.partials.telefonie-home-cta', ['site' => $site])

    <section data-section="herkenning">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Herken je dit?</span>
            <h2>Goed brood bakken is niet genoeg als de rest van de dag uit bellen en overtypen bestaat</h2>
            <div class="grid cols-2 feature-grid" style="margin-top:1.8rem">
                <div class="feature-card">
                    <h3>De telefoon gaat tijdens de ochtenddrukte</h3>
                    <span class="feature-rule"></span>
                    <p>Rij tot de deur, en iemand belt of jullie vandaag open zijn. Opnemen kost een klant aan de balie, niet opnemen kost een klant aan de telefoon.</p>
                </div>
                <div class="feature-card">
                    <h3>Taartbestellingen via WhatsApp, mail en briefjes</h3>
                    <span class="feature-rule"></span>
                    <p>Wie heeft wat besteld voor zaterdag, en is het al betaald? Dat hoort op één lijst te staan, niet in drie apps en een schrift naast de kassa.</p>
                </div>
                <div class="feature-card">
                    <h3>Het restaurant belt elke avond de bestelling door</h3>
                    <span class="feature-rule"></span>
                    <p>Zes stokbroden, twintig bolletjes, morgen twee extra. Zakelijke klanten kunnen dat zelf invoeren, en dan staat het meteen op de productielijst.</p>
                </div>
                <div class="feature-card">
                    <h3>Je site laat niet zien wat je verkoopt</h3>
                    <span class="feature-rule"></span>
                    <p>Geen openingstijden, geen assortiment, geen foto's van je taarten. Wie zoekt op "bakkerij" en jouw plaats, vindt de concurrent die dat wél heeft.</p>
                </div>
            </div>
        </div>
    </section>

    <div id="diensten" class="scroll-anchor" aria-hidden="true"></div>
    @include('channels.partials.groeipad', [
        'site'   => $site,
        'facets' => $facets,
        'kicker' => 'Wat we voor je bouwen',
        'title'  => 'Begin waar jouw bakkerij het meeste aan heeft, en breid later uit',
        'lead'   => 'Website, webshop, klantenportaal, automatisering en AI-telefonie. Je hoeft de stappen niet in deze volgorde te doen: elke dienst is ook los af te nemen. Heb je al een website en wil je alleen dat de telefoon wordt opgenomen, dan begin je bij AI-telefonie.',
    ])

    {{-- AI-telefonie als zelfstandige dienst, niet alleen als vijfde stap (15-09-2026). --}}

    @include('channels.partials.sales-trust', ['site' => $site, 'ctaTitle' => 'Benieuwd hoe jouw bakkerij er online uit zou zien?'])

    <div id="contact" class="scroll-anchor" aria-hidden="true"></div>
    @include('channels.partials.lead-wizard', ['site' => $site, 'facet' => 'website'])

@endsection
