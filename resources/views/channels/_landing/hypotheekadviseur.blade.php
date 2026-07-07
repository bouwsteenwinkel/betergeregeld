@php
    /** @var \App\Support\ChannelSite $site */
    // $facet = actieve product-facet, $landing = config('hypotheekadviseur_landings.'.$facet),
    // $facets = groeidiamant-facetten. Gezet door ChannelSiteController@home.
    $facets  = $facets ?? (array) config('groeidiamant.facets', []);
    $hero    = (array) ($landing['hero'] ?? []);
    $pains   = (array) ($landing['pains'] ?? []);
    $zkhw    = (array) ($landing['zkhw'] ?? []);
    $fLabel  = $facets[$facet]['label'] ?? ($zkhw['label'] ?? 'product');
    $landingKeys = array_keys((array) config('hypotheekadviseur_landings', []));
    $facetSlot = 'facet-' . $facet;
    $heroImg = $site->image($facetSlot) ?: $site->image('hero');
    $heroSet = $site->image($facetSlot) ? $site->imageSrcset($facetSlot) : $site->imageSrcset('hero');
@endphp
@extends('channels.layout')

@section('title', ($hero['title'] ?? $fLabel) . ' voor je hypotheekadvieskantoor')
@section('description', $hero['sub'] ?? '')

@section('content')

    @include('channels.partials.breadcrumb', ['items' => [
        ['label' => 'Home', 'url' => $site->url('')],
        ['label' => 'Diensten', 'url' => $site->url('diensten')],
        ['label' => $fLabel],
    ]])

    <section class="hero" data-section="hero">
        <div class="wrap">
            <div @if ($heroImg) class="grid cols-2" style="align-items:start;gap:2.6rem" @endif>
                <div>
                    @if (! empty($hero['eyebrow']))<span class="eyebrow">{{ $hero['eyebrow'] }}</span>@endif
                    <h1>{{ $hero['title'] ?? $fLabel }}</h1>
                    @if (! empty($hero['sub']))<p class="lead">{{ $hero['sub'] }}</p>@endif
                    <a href="#gratis-voorbeeld" class="btn">Gratis voorbeeld aanvragen</a>
                    @if (! empty($hero['note']))<p class="muted" style="margin-top:.8rem;font-size:.9rem">{{ $hero['note'] }}</p>@endif
                    @if (! empty($hero['usps']))
                        <ul class="hero-usps">
                            @foreach ((array) $hero['usps'] as $usp)<li>{{ $usp }}</li>@endforeach
                        </ul>
                    @endif
                </div>
                @if ($heroImg)
                    <div>
                        <img src="{{ $heroImg }}"
                             @if ($heroSet) srcset="{{ $heroSet }}" sizes="(max-width:760px) 92vw, 46vw" @endif
                             alt="Voorbeeld voor een hypotheekadvieskantoor" loading="eager" decoding="async"
                             style="width:100%;height:auto;border-radius:var(--radius);display:block;box-shadow:0 24px 60px -24px rgba(0,0,0,.4)">
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if ($pains)
        <section data-section="herkenning">
            <div class="wrap">
                <span class="kicker"><span class="kicker-line"></span> Herken je dit?</span>
                <div class="grid cols-3 feature-grid" style="margin-top:1.4rem">
                    @foreach ($pains as $pn)
                        <div class="feature-card">
                            <h3>{{ $pn['title'] ?? '' }}</h3>
                            <span class="feature-rule"></span>
                            <p>{{ $pn['text'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($zkhw)
        @include('channels.partials.zo-kan-het-worden', array_merge(['site' => $site, 'facet' => $facet], $zkhw))
    @endif

    @include('channels.partials.groeipad', [
        'site'    => $site,
        'facets'  => $facets,
        'facet'   => $facet,
        'kicker'  => 'De Groeidiamant',
        'title'   => 'Je site groeit met je mee',
        'lead'    => 'Begin waar je nu staat, je hoeft nooit opnieuw te beginnen. Elke stap bouwt voort op de vorige, in je eigen tempo.',
    ])

    @include('channels.partials.sales-trust', ['site' => $site, 'ctaTitle' => 'Benieuwd hoe dit voor jou zou werken?'])

    @include('channels.partials.lead-wizard', ['site' => $site, 'facet' => $facet])

@endsection
