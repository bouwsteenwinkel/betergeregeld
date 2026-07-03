@php
    /** @var \App\Support\ChannelSite $site */
    $facets   = $facets ?? (array) config('groeidiamant.facets', []);
    $landings = (array) config('badkamer_landings', []);
    $heroImg  = $site->image('hero');
    $heroSet  = $site->imageSrcset('hero');
@endphp
@extends('channels.layout')

@section('title', 'Website of webshop voor je badkamerbedrijf laten maken')
@section('description', 'Meer badkamerklussen uit je eigen regio met een professionele website, webshop of klantenportaal. Vraag gratis en vrijblijvend een voorbeeld van jouw site aan.')

@section('content')

    {{-- Hero: spreekt de ondernemer aan, niet z'n klanten --}}
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

    {{-- Herkenning / pijn --}}
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

    {{-- Wat we voor je bouwen = de 5 producten, elk met een eigen landingspagina --}}
    <section data-section="producten" style="background:color-mix(in srgb,var(--c-accent) 6%,transparent)">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Wat we voor je bouwen</span>
            <h2>Kies waar je nu staat, de rest komt later</h2>
            <p class="section-lead muted">Van een eerste professionele site tot online verkopen en slimme automatisering. Klik op een product voor het volledige verhaal en een echt voorbeeld.</p>
            <div class="grid cols-3 feature-grid">
                @foreach ($facets as $key => $f)
                    @php $lead = $landings[$key]['hero']['sub'] ?? ($f['tagline'] ?? ''); @endphp
                    <a href="{{ isset($landings[$key]) ? $site->url($key) : $site->url('voorbeeld/' . $key) }}" class="feature-card" style="display:block;color:inherit">
                        <div class="feature-ico">@include('channels.partials.icon', ['name' => $f['icon'] ?? 'check'])</div>
                        <h3>{{ $f['nr'] ?? '' }}. {{ $f['label'] ?? $key }}</h3>
                        <span class="feature-rule"></span>
                        <p>{{ $lead }}</p>
                        <p style="margin-top:1rem;font-weight:700;color:var(--c-cta)">Meer over dit product →</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Géén "Zo kan het worden"-blokken op de home: één blok per groeistap staat
         op de eigen landingspagina (/{facet}). De home funnelt via de kaarten hierboven. --}}

    {{-- Gedeelde aanpak / waarom / CTA --}}
    @include('channels.partials.sales-trust', ['site' => $site, 'ctaTitle' => 'Benieuwd hoe jouw site eruit zou zien?'])

    {{-- Funnel: gratis-voorbeeld-wizard --}}
    @include('channels.partials.lead-wizard', ['site' => $site, 'facet' => 'website'])

@endsection
