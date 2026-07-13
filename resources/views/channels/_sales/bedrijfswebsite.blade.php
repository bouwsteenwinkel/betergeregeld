@php
    /** @var \App\Support\ChannelSite $site */
    $facets  = $facets ?? (array) config('groeidiamant.facets', []);
    $heroImg = $site->image('herobg');
    $heroSet = $site->imageSrcset('herobg');
@endphp
@extends('channels.layout')

@section('title', 'Website of webshop voor je bedrijf laten maken')
@section('description', 'Meer klanten en omzet uit je eigen regio met een professionele website, webshop of klantenportaal. Vraag gratis en vrijblijvend een voorbeeld van jouw site aan.')

@if ($heroImg)
    @push('head')
        <link rel="preload" as="image" href="{{ $heroImg }}" @if ($heroSet) imagesrcset="{{ $heroSet }}" imagesizes="100vw" @endif fetchpriority="high">
    @endpush
@endif

@section('content')

    {{-- Full-bleed hero: beeld over de volledige breedte, tekst overlaid op de
         donkere linkerzone (.hero--shot = foto + links-donkere gradient-overlay). --}}
    @if ($heroImg)
        <style>
            /* Tekst dichter naar de linker viewport-rand: op desktop de lege ruimte
               links halveren t.o.v. de gecentreerde container (~17vw i.p.v. ~34vw).
               Tekstkolom blijft begrensd zodat de regels niet te lang worden. */
            .hero--shot .hero-text{max-width:600px}
            @media(min-width:1000px){
                .hero--shot .wrap{max-width:none;padding-left:17vw;padding-right:22px}
            }
        </style>
    @endif
    <section class="hero @if ($heroImg) hero--shot @endif" data-section="hero">
        @if ($heroImg)
            <div class="hero-bg">
                <img src="{{ $heroImg }}" @if ($heroSet) srcset="{{ $heroSet }}" sizes="100vw" @endif
                     alt="Ondernemer bekijkt zijn nieuwe bedrijfswebsite op een tablet" loading="eager" decoding="async" fetchpriority="high">
            </div>
        @endif
        <div class="wrap">
            <div class="hero-text">
                <h1>Zie jouw nieuwe website in 60 seconden</h1>
                <p class="lead">Meer klanten uit je eigen regio, zonder gedoe. Vertel kort wat je doet en bekijk meteen een gratis voorbeeld van jouw site.</p>
                <a href="#gratis-voorbeeld" class="btn">Maak mijn gratis voorbeeld</a>
                <p class="muted" style="margin-top:.8rem;font-size:.9rem">Klaar in 60 seconden, geen account, geen kosten, nergens aan vast</p>
                <ul class="hero-usps">
                    <li>Meteen resultaat, geen dagen wachten</li>
                    <li>Gevonden worden zodra iemand jouw dienst zoekt</li>
                    <li>Gratis en vrijblijvend, stop wanneer je wilt</li>
                </ul>
            </div>
        </div>
    </section>

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
                    <p>Wie jouw dienst in {plaats} zoekt, moet jóu vinden. Nu vissen andere bedrijven die klanten voor je neus weg.</p>
                </div>
                <div class="feature-card">
                    <h3>Aanvragen blijven uit</h3>
                    <span class="feature-rule"></span>
                    <p>Zonder duidelijke aanvraagknop haakt een geïnteresseerde af. Elke gemiste aanvraag is een misgelopen klant.</p>
                </div>
                <div class="feature-card">
                    <h3>Administratie vreet je tijd</h3>
                    <span class="feature-rule"></span>
                    <p>Bellen, mailen, offertes typen, facturen sturen. Techniek kan een groot deel daarvan van je overnemen.</p>
                </div>
            </div>
        </div>
    </section>

    <div id="diensten" class="scroll-anchor" aria-hidden="true"></div>
    @include('channels.partials.groeipad', [
        'site'   => $site,
        'facets' => $facets,
        'kicker' => 'Wat we voor je bouwen',
        'title'  => 'Kies waar je nu staat, de rest komt later',
        'lead'   => 'Van een eerste professionele site tot online verkopen en slimme automatisering. Klik op een fase voor het volledige verhaal en een echt voorbeeld.',
    ])

    @include('channels.partials.sales-trust', ['site' => $site, 'ctaTitle' => 'Benieuwd hoe jouw site eruit zou zien?'])

    <div id="contact" class="scroll-anchor" aria-hidden="true"></div>
    @include('channels.partials.lead-wizard', ['site' => $site, 'facet' => 'website'])

@endsection
