@php
    /** @var \App\Support\ChannelSite $site */
    // $facet = actieve product-facet, $landing = config('badkamer_landings.'.$facet),
    // $facets = groeidiamant-facetten. Gezet door ChannelSiteController@home.
    $facets  = $facets ?? (array) config('groeidiamant.facets', []);
    $hero    = (array) ($landing['hero'] ?? []);
    $pains   = (array) ($landing['pains'] ?? []);
    $zkhw    = (array) ($landing['zkhw'] ?? []);
    $fLabel  = $facets[$facet]['label'] ?? ($zkhw['label'] ?? 'product');
    $landingKeys = array_keys((array) config('badkamer_landings', []));
    $heroImg = $site->image('hero');
    $heroSet = $site->imageSrcset('hero');
@endphp
@extends('channels.layout')

@section('title', ($hero['title'] ?? $fLabel) . ' voor je badkamerbedrijf')
@section('description', $hero['sub'] ?? '')

@section('content')

    {{-- Product-hero: spreekt de ondernemer aan die juist DIT product zoekt --}}
    <section class="hero" data-section="hero">
        <div class="wrap">
            <div @if ($heroImg) class="grid cols-2" style="align-items:center;gap:2.6rem" @endif>
                <div>
                    @if (! empty($hero['eyebrow']))<span class="eyebrow">{{ $hero['eyebrow'] }}</span>@endif
                    <h1>{{ $hero['title'] ?? $fLabel }}</h1>
                    @if (! empty($hero['sub']))<p class="lead">{{ $hero['sub'] }}</p>@endif
                    <a href="#gratis-voorbeeld" class="btn">Gratis voorbeeld aanvragen</a>
                    <a href="{{ $site->url('voorbeeld/' . $facet) }}" class="btn btn-ghost" style="margin-left:.6rem">Bekijk het voorbeeld →</a>
                    @if (! empty($hero['note']))<p class="muted" style="margin-top:.8rem;font-size:.9rem">{{ $hero['note'] }}</p>@endif
                    @if (! empty($hero['usps']))
                        <ul class="hero-usps">
                            @foreach ((array) $hero['usps'] as $usp)<li>{{ $usp }}</li>@endforeach
                        </ul>
                    @endif
                </div>
                @if ($heroImg)
                    <div style="position:relative">
                        <img src="{{ $heroImg }}"
                             @if ($heroSet) srcset="{{ $heroSet }}" sizes="(max-width:760px) 92vw, 46vw" @endif
                             alt="Voorbeeld voor een badkamerbedrijf" loading="eager" decoding="async"
                             style="width:100%;height:auto;border-radius:var(--radius);display:block;box-shadow:0 24px 60px -24px rgba(0,0,0,.4)">
                        <a href="{{ $site->url('voorbeeld/' . $facet) }}" style="position:absolute;left:50%;bottom:14px;transform:translateX(-50%);background:rgba(255,255,255,.94);color:var(--c-ink);font-weight:700;font-size:.85rem;padding:.5rem 1rem;border-radius:999px;box-shadow:0 8px 24px -8px rgba(0,0,0,.5);white-space:nowrap">🔍 Bekijk het voorbeeld</a>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Pijn, toegespitst op dit product --}}
    @if ($pains)
        <section data-section="herkenning">
            <div class="wrap">
                <span class="kicker"><span class="kicker-line"></span> Herken je dit?</span>
                <div class="grid cols-3 feature-grid" style="margin-top:1.4rem">
                    @foreach ($pains as $p)
                        <div class="feature-card">
                            <h3>{{ $p['title'] ?? '' }}</h3>
                            <span class="feature-rule"></span>
                            <p>{{ $p['text'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- "Zo kan het worden" voor dit product --}}
    @if ($zkhw)
        @include('channels.partials.zo-kan-het-worden', array_merge(['site' => $site, 'facet' => $facet], $zkhw))
    @endif

    {{-- Groeidiamant: alle fasen, deze fase gemarkeerd als "jouw stap", de andere
         linken door naar hun eigen triggerpagina. --}}
    @include('channels.partials.groeipad', ['site' => $site, 'facet' => $facet, 'facets' => $facets])

    {{-- Gedeelde aanpak / waarom / CTA --}}
    @include('channels.partials.sales-trust', ['site' => $site, 'ctaTitle' => 'Benieuwd hoe dit voor jou zou werken?'])

    {{-- Funnel: wizard met dit product voorgeselecteerd (lead wordt getagd) --}}
    @include('channels.partials.lead-wizard', ['site' => $site, 'facet' => $facet])

@endsection
