@php
    /** @var \App\Support\ChannelSite $site */
    $t = array_merge((array) config('channel_places.defaults', []), array_filter((array) $site->get('places', []), fn ($v) => is_scalar($v) && $v !== ''));
    $map = \App\Support\ChannelTokens::map((array) $site->get('places', []), $site->brancheKey());
    $r = fn ($s) => strtr((string) $s, $map);
    $items = array_map(fn ($x) => ['q' => $r($x['q'] ?? ''), 'a' => $r($x['a'] ?? '')], (array) config('channel_faq.items', []));
    // Per branche extra vragen (config/channel_faq_per_branche.php), bv. webshop en telefonie bij bakkerij.
    foreach ((array) config('channel_faq_per_branche.' . $site->brancheKey(), []) as $x) {
        $items[] = ['q' => $r($x['q'] ?? ''), 'a' => $r($x['a'] ?? '')];
    }
    // Heeft dit kanaal een AI-telefoniepagina, dan de twee telefonievragen erbij (16-09-2026).
    foreach ((array) (\App\Support\TelefonieConfig::for($site)['faq_site'] ?? []) as $x) {
        $items[] = ['q' => $r($x['q'] ?? ''), 'a' => $r($x['a'] ?? '')];
    }

    $ld = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn ($f) => [
        '@type' => 'Question', 'name' => $f['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
    ], $items)];
@endphp
@extends('channels.layout')

@section('title', $r('Veelgestelde vragen van :trades'))
@section('description', 'Antwoorden op de vragen die ' . ($t['trades'] ?? 'ondernemers') . ' stellen over een website, webshop of slimme tools voor hun bedrijf.')

@push('head')
    <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
    @include('channels.partials.breadcrumb', ['items' => [['label' => 'Home', 'url' => $site->url('')], ['label' => 'Veelgestelde vragen']]])
    <section class="hero hero--slim">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Vragen</span>
            <h1>{{ $r('Veelgestelde vragen van :trades') }}</h1>
            <p class="lead" style="max-width:56ch">Geen kleine lettertjes. Hieronder de eerlijke antwoorden op wat {{ $t['trades'] ?? 'ondernemers' }} het vaakst vragen.</p>
        </div>
    </section>

    <section style="padding-top:1rem">
        <div class="wrap">
            @include('channels.partials.faq-accordion', ['items' => $items])
        </div>
    </section>

    @include('channels.partials.sales-trust', ['site' => $site, 'ctaTitle' => 'Nog een vraag? Vraag gerust een gratis voorbeeld aan.'])

    <div id="contact" class="scroll-anchor" aria-hidden="true"></div>
    @include('channels.partials.lead-wizard', ['site' => $site, 'facet' => 'website'])
@endsection
