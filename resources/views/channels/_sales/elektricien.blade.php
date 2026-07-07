@php
    /** @var \App\Support\ChannelSite $site */
    $facets   = $facets ?? (array) config('groeidiamant.facets', []);
    $landings = (array) config('elektricien_landings', []);
    $heroImg  = $site->image('hero');
    $heroSet  = $site->imageSrcset('hero');
@endphp
@extends('channels.layout')

@section('title', 'Website of webshop voor je elektrabedrijf laten maken')
@section('description', 'Meer elektra-klussen uit je eigen regio met een professionele website, webshop of klantenportaal. Vraag gratis en vrijblijvend een voorbeeld van jouw site aan.')

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
                    <span class="eyebrow">Voor elektriciens</span>
                    <h1>Meer elektra-klussen uit je eigen regio</h1>
                    <p class="lead">Een strakke website, webshop of klantenportaal die klanten binnenhaalt terwijl jij aan het werk bent. Wij bouwen 'm, jij bepaalt hoe ver je gaat.</p>
                    <a href="#gratis-voorbeeld" class="btn">Gratis voorbeeld aanvragen</a>
                    <p class="muted" style="margin-top:.8rem;font-size:.9rem">Gratis &middot; vrijblijvend &middot; voorbeeld van jóuw site, vaak binnen 1 à 2 dagen</p>
                    <ul class="hero-usps">
                        <li>Gevonden worden als iemand een elektricien zoekt in jouw regio</li>
                        <li>Offerteaanvragen rechtstreeks in je mailbox</li>
                        <li>Begin klein en breid later uit, je hoeft nooit opnieuw te beginnen</li>
                    </ul>
                </div>
                @if ($heroImg)
                    <div>
                        <img src="{{ $heroImg }}"
                             @if ($heroSet) srcset="{{ $heroSet }}" sizes="(max-width:760px) 92vw, 46vw" @endif
                             alt="Voorbeeld van een elektrabedrijf-website" loading="eager" decoding="async"
                             style="width:100%;height:auto;border-radius:var(--radius);display:block;box-shadow:0 24px 60px -24px rgba(0,0,0,.4)">
                    </div>
                @endif
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
                    <p>Wie “elektricien {plaats}” zoekt, moet jóu vinden. Nu vissen andere bedrijven die klanten voor je neus weg.</p>
                </div>
                <div class="feature-card">
                    <h3>Aanvragen blijven uit</h3>
                    <span class="feature-rule"></span>
                    <p>Zonder duidelijke aanvraagknop haakt een geïnteresseerde af. Elke gemiste aanvraag is een misgelopen klus.</p>
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
