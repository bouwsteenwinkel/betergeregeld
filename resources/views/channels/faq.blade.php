@php
    /** @var \App\Support\ChannelSite $site */
    $t = array_merge((array) config('channel_places.defaults', []), array_filter((array) $site->get('places', []), fn ($v) => is_scalar($v) && $v !== ''));
    $map = [':trades' => $t['trades'] ?? 'bedrijven', ':trade' => $t['trade'] ?? 'bedrijf', ':niches' => $t['niches'] ?? 'diensten', ':niche' => $t['niche'] ?? 'vak'];
    $r = fn ($s) => strtr((string) $s, $map);
    $items = array_map(fn ($x) => ['q' => $r($x['q'] ?? ''), 'a' => $r($x['a'] ?? '')], (array) config('channel_faq.items', []));

    $ld = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn ($f) => [
        '@type' => 'Question', 'name' => $f['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
    ], $items)];
@endphp
@extends('channels.layout')

@section('title', 'Veelgestelde vragen')
@section('description', 'Antwoorden op de vragen die ' . ($t['trades'] ?? 'ondernemers') . ' stellen over een website, webshop of slimme tools voor hun bedrijf.')

@push('head')
    <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
    @include('channels.partials.breadcrumb', ['items' => [['label' => 'Home', 'url' => $site->url('')], ['label' => 'Veelgestelde vragen']]])
    <style>
        /* Geanimeerde accordion, altijd max. 1 open. Antwoord-hoogte via JS. */
        .faq-acc details > .faq-a{display:block;overflow:hidden;height:0;transition:height .3s ease}
        .faq-acc details p{margin:0}
    </style>
    <section class="hero hero--slim">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Vragen</span>
            <h1>Veelgestelde vragen</h1>
            <p class="lead" style="max-width:56ch">Geen kleine lettertjes. Hieronder de eerlijke antwoorden op wat {{ $t['trades'] ?? 'ondernemers' }} het vaakst vragen.</p>
        </div>
    </section>

    <section style="padding-top:1rem">
        <div class="wrap">
            <div class="faq faq-acc" style="max-width:820px">
                @foreach ($items as $i => $qa)
                    <details @if($i === 0) open @endif>
                        <summary>{{ $qa['q'] }}</summary>
                        <div class="faq-a"><p>{{ $qa['a'] }}</p></div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <script>
    (function () {
        var acc = document.querySelector('.faq-acc');
        if (!acc) return;
        var items = [].slice.call(acc.querySelectorAll('details'));
        function expand(d) {
            var a = d.querySelector('.faq-a');
            d.open = true;
            a.style.height = a.scrollHeight + 'px';
            a.addEventListener('transitionend', function te(ev) {
                if (ev.propertyName === 'height') { a.style.height = 'auto'; a.removeEventListener('transitionend', te); }
            });
        }
        function collapse(d) {
            var a = d.querySelector('.faq-a');
            a.style.height = a.scrollHeight + 'px';
            requestAnimationFrame(function () { a.style.height = '0px'; });
            d.open = false;
        }
        items.forEach(function (d) {
            var a = d.querySelector('.faq-a');
            if (d.open) { a.style.height = 'auto'; }
            d.querySelector('summary').addEventListener('click', function (e) {
                e.preventDefault();
                var willOpen = !d.open;
                items.forEach(function (o) { if (o !== d && o.open) collapse(o); });
                willOpen ? expand(d) : collapse(d);
            });
        });
    })();
    </script>

    @include('channels.partials.sales-trust', ['site' => $site, 'ctaTitle' => 'Nog een vraag? Vraag gerust een gratis voorbeeld aan.'])

    <div id="contact" class="scroll-anchor" aria-hidden="true"></div>
    @include('channels.partials.lead-wizard', ['site' => $site, 'facet' => 'website'])
@endsection
