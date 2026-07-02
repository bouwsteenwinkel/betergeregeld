@php
    /** @var \App\Support\ChannelSite $site */
    $facets   = $facets ?? (array) config('groeidiamant.facets', []);
    $heroImg  = $site->image('hero');
    $heroSet  = $site->imageSrcset('hero');

    // Per product (Groeidiamant-facet) een badkamer-specifieke verkoopregel +
    // de link naar het live voorbeeld van dát product op de demolaag.
    $productCopy = [
        'website'        => 'Een site die je vak laat zien en klussen binnenhaalt. Gevonden worden in je regio, offerteaanvragen in je mailbox.',
        'webshop'        => 'Verkoop sanitair, tegels en pakketten online. Je showroom 24/7 open, met iDEAL en bezorgen of afhalen.',
        'klantenportaal' => 'Laat klanten hun project volgen: 3D-ontwerp, planning en materiaalkeuzes op één plek.',
        'automatisering' => 'Offertes, planning en facturen die zichzelf doen. Minder papierwerk, meer tijd op de bouw.',
        'ai'             => 'Een assistent die de telefoon opneemt en offertes voorbereidt terwijl jij aan het werk bent.',
    ];
@endphp
@extends('channels.layout')

@section('title', 'Website of webshop voor je badkamerbedrijf laten maken')
@section('description', 'Meer badkamerklussen uit je eigen regio met een professionele website, webshop of klantenportaal. Vraag gratis en vrijblijvend een voorbeeld van jouw site aan.')

@section('content')

    {{-- ── Hero: spreekt de ondernemer aan, niet z'n klanten ──────────────── --}}
    <section class="hero" data-section="hero">
        <div class="wrap">
            <div @if ($heroImg) class="grid cols-2" style="align-items:center;gap:2.6rem" @endif>
                <div>
                    <span class="eyebrow">Voor badkamerspecialisten</span>
                    <h1>Meer badkamerklussen uit je eigen regio</h1>
                    <p class="lead">Een strakke website, webshop of klantenportaal die klanten binnenhaalt terwijl jij op de bouw staat. Wij bouwen 'm, jij bepaalt hoe ver je gaat.</p>
                    <a href="#gratis-voorbeeld" class="btn">Gratis voorbeeld aanvragen</a>
                    <a href="{{ $site->url('voorbeeld') }}" class="btn btn-ghost" style="margin-left:.6rem">Bekijk een voorbeeld →</a>
                    <p class="muted" style="margin-top:.8rem;font-size:.9rem">Gratis &middot; vrijblijvend &middot; voorbeeld van jóuw site, vaak binnen 1 à 2 dagen</p>
                    <ul class="hero-usps">
                        <li>Gevonden worden als iemand een badkamer zoekt in jouw regio</li>
                        <li>Offerteaanvragen rechtstreeks in je mailbox</li>
                        <li>Begin klein en breid later uit, je hoeft nooit opnieuw te beginnen</li>
                    </ul>
                </div>
                @if ($heroImg)
                    <div style="position:relative">
                        <img src="{{ $heroImg }}"
                             @if ($heroSet) srcset="{{ $heroSet }}" sizes="(max-width:760px) 92vw, 46vw" @endif
                             alt="Voorbeeld van een badkamerwebsite" loading="eager" decoding="async"
                             style="width:100%;height:auto;border-radius:var(--radius);display:block;box-shadow:0 24px 60px -24px rgba(0,0,0,.4)">
                        <a href="{{ $site->url('voorbeeld') }}" style="position:absolute;left:50%;bottom:14px;transform:translateX(-50%);background:rgba(255,255,255,.94);color:var(--c-ink);font-weight:700;font-size:.85rem;padding:.5rem 1rem;border-radius:999px;box-shadow:0 8px 24px -8px rgba(0,0,0,.5);white-space:nowrap">🔍 Bekijk dit voorbeeld</a>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ── Herkenning / pijn ──────────────────────────────────────────────── --}}
    <section data-section="herkenning">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Herken je dit?</span>
            <h2>Goed werk leveren is niet genoeg als niemand je online vindt</h2>
            <div class="grid cols-2 feature-grid" style="margin-top:1.8rem">
                <div class="feature-card">
                    <h3>Je site is verouderd of je hebt er geen</h3>
                    <span class="feature-rule"></span>
                    <p>Alleen een Facebook-pagina of een site van jaren terug? Dan kiest een klant sneller voor een concurrent die er strak en betrouwbaar uitziet.</p>
                </div>
                <div class="feature-card">
                    <h3>Je bent niet vindbaar in Google</h3>
                    <span class="feature-rule"></span>
                    <p>Wie “badkamer renoveren {{ '{plaats}' }}” zoekt, moet jóu vinden. Nu vissen andere bedrijven die klanten voor je neus weg.</p>
                </div>
                <div class="feature-card">
                    <h3>Aanvragen blijven uit</h3>
                    <span class="feature-rule"></span>
                    <p>Zonder duidelijke aanvraagknop haakt een geïnteresseerde af. Elke gemiste aanvraag is een misgelopen klus van duizenden euro's.</p>
                </div>
                <div class="feature-card">
                    <h3>Administratie vreet je tijd</h3>
                    <span class="feature-rule"></span>
                    <p>Bellen, mailen, offertes typen, facturen sturen. Techniek kan een groot deel daarvan van je overnemen.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Wat we voor je bouwen = de 5 producten, elk met live voorbeeld ──── --}}
    <section data-section="producten" style="background:color-mix(in srgb,var(--c-accent) 6%,transparent)">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Wat we voor je bouwen</span>
            <h2>Kies waar je nu staat, de rest komt later</h2>
            <p class="section-lead muted">Van een eerste professionele site tot online verkopen en slimme automatisering. Bekijk van elk een echt voorbeeld in de badkamerbranche.</p>
            <div class="grid cols-3 feature-grid">
                @foreach ($facets as $key => $f)
                    <a href="{{ $site->url('voorbeeld/' . $key) }}" class="feature-card" style="display:block;color:inherit">
                        <div class="feature-ico" style="font-size:1.4rem;line-height:1">{{ $f['icon'] ?? '•' }}</div>
                        <h3>{{ $f['nr'] ?? '' }}. {{ $f['label'] ?? $key }}</h3>
                        <span class="feature-rule"></span>
                        <p>{{ $productCopy[$key] ?? ($f['tagline'] ?? '') }}</p>
                        <p style="margin-top:1rem;font-weight:700;color:var(--c-cta)">Bekijk voorbeeld →</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- "Zo kan het worden": website (herbruikbaar blok, later per product).
         Afgestemd op de niche: een badkamer is techniek en stijl. De mockup laat
         beide zien: een vertrouwenwekkende hero (vakwerk) plus een galerij van
         afgewerkte badkamers (het mooie resultaat). --}}
    @include('channels.partials.zo-kan-het-worden', [
        'site'         => $site,
        'facet'        => 'website',
        'label'        => 'Website',
        'title'        => 'Zo kan het worden: je vakwerk én je mooiste badkamers online',
        'intro'        => 'Een badkamer is techniek én stijl. Je site laat allebei zien: de vakman achter het werk en de afgewerkte badkamers waar klanten blij van worden. We hebben een compleet voorbeeld klaargezet. Klik erdoorheen en stel je voor dat het je eigen bedrijf is.',
        'brand'        => 'BadkamerBloem',
        'heroTitle'    => 'Van oude badkamer naar afgewerkte ruimte in 10 werkdagen',
        'urlLabel'     => 'jouw-badkamerbedrijf.nl',
        'ctaLabel'     => 'Bekijk het volledige voorbeeld',
        'galleryLabel' => 'Recent afgeleverde badkamers',
        'gallery'      => array_values(array_filter([
            $site->image('gallery1'), $site->image('gallery2'), $site->image('gallery3'),
        ])),
        'bullets'      => [
            ['title' => 'Je mooiste badkamers in beeld', 'text' => 'Een galerij van afgewerkte projecten die klanten over de streep trekt.'],
            ['title' => 'Vertrouwen door vakmanschap', 'text' => 'Garanties, een vast team en een heldere werkwijze. Daar durven klanten op te bouwen.'],
            ['title' => 'Gevonden in je regio', 'text' => 'Vindbaar als iemand een badkamer zoekt bij jou in de buurt.'],
            ['title' => 'Dag en nacht aanvragen', 'text' => 'Een duidelijke knop die ook \'s avonds nieuwe aanvragen oplevert, op mobiel en desktop.'],
        ],
    ])

    {{-- ── Aanpak ─────────────────────────────────────────────────────────── --}}
    <section data-section="aanpak">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Zo werkt het</span>
            <h2>Van aanvraag naar een site die voor je werkt</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-num">1</div>
                    <h3>Gratis voorbeeld</h3>
                    <p>Beantwoord een paar korte vragen. Wij zetten een voorbeeld van jóuw site klaar, vaak al binnen 1 à 2 dagen. Gratis en vrijblijvend.</p>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <h3>Samen scherpstellen</h3>
                    <p>We bespreken het voorbeeld bij jou op locatie of online. Jij bepaalt wat er wel en niet in moet, tot het klopt.</p>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <h3>Live en groeit mee</h3>
                    <p>We zetten 'm live. Later uitbreiden met een webshop, portaal of automatisering? Dat bouwen we er gewoon op voort. Je hoeft nooit opnieuw te beginnen.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Waarom betergeregeld ───────────────────────────────────────────── --}}
    <section data-section="waarom" style="background:color-mix(in srgb,var(--c-accent) 6%,transparent)">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Waarom betergeregeld</span>
            <div class="grid cols-4 feature-grid" style="margin-top:1.4rem">
                <div class="feature-card"><h3>Vaste prijs</h3><span class="feature-rule"></span><p>Duidelijke prijs vooraf, geen verrassingen achteraf.</p></div>
                <div class="feature-card"><h3>Echt bereikbaar</h3><span class="feature-rule"></span><p>Een Nederlands team dat opneemt en meedenkt.</p></div>
                <div class="feature-card"><h3>Groeit met je mee</h3><span class="feature-rule"></span><p>De Groeidiamant: begin klein, breid uit wanneer je eraan toe bent.</p></div>
                <div class="feature-card"><h3>Bij jou langs</h3><span class="feature-rule"></span><p>Bezoek aan huis in de regio, of gewoon online. Net wat jou uitkomt.</p></div>
            </div>
        </div>
    </section>

    {{-- ── CTA-band ───────────────────────────────────────────────────────── --}}
    <section class="cta-band" data-section="cta">
        <div class="wrap">
            <div class="cta-band-inner">
                <div>
                    <h2>Benieuwd hoe jouw site eruit zou zien?</h2>
                    <p>Vraag gratis en vrijblijvend een voorbeeld aan. Je zit nergens aan vast.</p>
                </div>
                <a href="#gratis-voorbeeld" class="btn">Gratis voorbeeld aanvragen</a>
            </div>
        </div>
    </section>

    {{-- ── Funnel: gratis-voorbeeld-wizard (hergebruikt bouwblok) ─────────── --}}
    @include('channels.partials.lead-wizard', ['site' => $site, 'facet' => 'website'])

@endsection
