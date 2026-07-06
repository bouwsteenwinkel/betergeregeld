@php
    /** @var \App\Support\ChannelSite $site */
    $facets   = $facets ?? (array) config('groeidiamant.facets', []);
    $landings = (array) config('rijschool_landings', []);
    $heroImg  = $site->image('hero');
    $heroSet  = $site->imageSrcset('hero');
@endphp
@extends('channels.layout')

@section('title', 'Website of webshop voor je rijschool laten maken')
@section('description', 'Meer leerlingen uit je eigen regio met een professionele website, online aanmelding of leerlingportaal. Vraag gratis en vrijblijvend een voorbeeld van jouw site aan.')

{{-- LCP: hero-afbeelding vroeg laden zodat de grootste afbeelding sneller in beeld staat. --}}
@if ($heroImg)
    @push('head')
        <link rel="preload" as="image" href="{{ $heroImg }}" @if ($heroSet) imagesrcset="{{ $heroSet }}" imagesizes="(max-width:760px) 92vw, 46vw" @endif fetchpriority="high">
    @endpush
@endif

@section('content')

    {{-- Hero: spreekt de ondernemer aan, niet z'n leerlingen --}}
    <section class="hero" data-section="hero">
        <div class="wrap">
            <div @if ($heroImg) class="grid cols-2" style="align-items:start;gap:2.6rem" @endif>
                <div>
                    <span class="eyebrow">Voor rijscholen</span>
                    <h1>Meer leerlingen uit je eigen regio</h1>
                    <p class="lead">Een strakke website, online aanmelding of leerlingportaal die leerlingen binnenhaalt terwijl jij lesgeeft. Wij bouwen 'm, jij bepaalt hoe ver je gaat.</p>
                    <a href="#gratis-voorbeeld" class="btn">Gratis voorbeeld aanvragen</a>
                    <p class="muted" style="margin-top:.8rem;font-size:.9rem">Gratis &middot; vrijblijvend &middot; voorbeeld van jóuw site, vaak binnen 1 à 2 dagen</p>
                    <ul class="hero-usps">
                        <li>Gevonden worden als iemand een rijschool zoekt in jouw regio</li>
                        <li>Aanmeldingen rechtstreeks in je mailbox</li>
                        <li>Begin klein en breid later uit, je hoeft nooit opnieuw te beginnen</li>
                    </ul>
                </div>
                @if ($heroImg)
                    <div>
                        <img src="{{ $heroImg }}"
                             @if ($heroSet) srcset="{{ $heroSet }}" sizes="(max-width:760px) 92vw, 46vw" @endif
                             alt="Voorbeeld van een rijschool-website" loading="eager" decoding="async"
                             style="width:100%;height:auto;border-radius:var(--radius);display:block;box-shadow:0 24px 60px -24px rgba(0,0,0,.4)">
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Herkenning / pijn --}}
    <section data-section="herkenning">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Herken je dit?</span>
            <h2>Goed lesgeven is niet genoeg als niemand je online vindt</h2>
            <div class="grid cols-2 feature-grid" style="margin-top:1.8rem">
                <div class="feature-card">
                    <h3>Je site is verouderd of je hebt er geen</h3>
                    <span class="feature-rule"></span>
                    <p>Alleen een Facebook-pagina of een site van jaren terug? Dan kiest een leerling sneller voor een rijschool die er strak en betrouwbaar uitziet.</p>
                </div>
                <div class="feature-card">
                    <h3>Je bent niet vindbaar in Google</h3>
                    <span class="feature-rule"></span>
                    <p>Wie “rijschool {{ '{plaats}' }}” zoekt, moet jóu vinden. Nu vissen andere rijscholen die leerlingen voor je neus weg.</p>
                </div>
                <div class="feature-card">
                    <h3>Aanmeldingen blijven uit</h3>
                    <span class="feature-rule"></span>
                    <p>Zonder duidelijke aanmeldknop haakt een geïnteresseerde af. Elke gemiste aanmelding is een leerling die je maanden had kunnen lesgeven.</p>
                </div>
                <div class="feature-card">
                    <h3>Administratie vreet je tijd</h3>
                    <span class="feature-rule"></span>
                    <p>Bellen, appen, facturen sturen, planning bijhouden. Techniek kan een groot deel daarvan van je overnemen.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Wat we voor je bouwen = de Groeidiamant-facetstrip. Elk facet linkt naar z'n landingspagina. --}}
    <div id="diensten" class="scroll-anchor" aria-hidden="true"></div>
    @include('channels.partials.groeipad', [
        'site'   => $site,
        'facets' => $facets,
        'kicker' => 'Wat we voor je bouwen',
        'title'  => 'Kies waar je nu staat, de rest komt later',
        'lead'   => 'Van een eerste professionele site tot online aanmelden en slimme automatisering. Klik op een fase voor het volledige verhaal en een echt voorbeeld.',
    ])

    {{-- Gedeelde aanpak / waarom / CTA --}}
    @include('channels.partials.sales-trust', ['site' => $site, 'ctaTitle' => 'Benieuwd hoe jouw rijschool-site eruit zou zien?'])

    {{-- Funnel: gratis-voorbeeld-wizard --}}
    <div id="contact" class="scroll-anchor" aria-hidden="true"></div>
    @include('channels.partials.lead-wizard', ['site' => $site, 'facet' => 'website'])

@endsection
