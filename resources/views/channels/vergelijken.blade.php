@php
    /** @var \App\Support\ChannelSite $site */
    $t = array_merge((array) config('channel_places.defaults', []), array_filter((array) $site->get('places', []), fn ($v) => is_scalar($v) && $v !== ''));
    $trade = $t['trade'] ?? 'bedrijf';

    // [onderwerp, zelf bouwen, laten maken]
    $rows = [
        ['Tijd die het jou kost', 'Avonden en weekenden uitzoeken en bouwen', 'Bijna niets, wij doen het werk'],
        ['Vindbaar in Google', 'Vaak matig, je moet het zelf uitvogelen', 'Lokaal geoptimaliseerd, gericht op aanvragen'],
        ['Professionele uitstraling', 'Wisselend, afhankelijk van je eigen tijd', 'Strak en vertrouwenwekkend, gemaakt voor je vak'],
        ['Techniek, hosting, updates', 'Jouw verantwoordelijkheid', 'Volledig geregeld, inbegrepen'],
        ['Hulp als er iets is', 'Zelf zoeken op forums', 'Een Nederlands team dat opneemt'],
        ['Meegroeien (webshop, portaal, AI)', 'Vaak opnieuw beginnen', 'Bouwt voort op wat er staat'],
        ['Kosten', 'Lijkt goedkoop, kost vooral veel tijd', 'Vast, laag maandbedrag zonder verrassingen'],
    ];
@endphp
@extends('channels.layout')

@section('title', 'Zelf een website bouwen of laten maken?')
@section('description', 'Zelf bouwen met een website-bouwer of je website laten maken? Een eerlijke vergelijking voor je ' . $trade . '.')

@section('content')
    @include('channels.partials.breadcrumb', ['items' => [['label' => 'Home', 'url' => $site->url('')], ['label' => 'Zelf bouwen of laten maken']]])
    <section class="hero">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Vergelijken</span>
            <h1>Zelf bouwen of laten maken?</h1>
            <p class="lead" style="max-width:60ch">Je kunt zelf een site in elkaar zetten met een website-bouwer, of het laten doen. Beide kan. Hieronder een eerlijke vergelijking, zodat je weet wat bij jou past.</p>
        </div>
    </section>

    <section>
        <div class="wrap">
            <style>
                .cmp{width:100%;border-collapse:collapse;font-size:.97rem;background:var(--c-surface);border-radius:var(--radius);overflow:hidden}
                .cmp th,.cmp td{padding:.95rem 1.1rem;text-align:left;border-bottom:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent);vertical-align:top}
                .cmp thead th{background:color-mix(in srgb,var(--c-ink) 5%,transparent);font-size:.82rem;text-transform:uppercase;letter-spacing:.05em}
                .cmp thead th:last-child{background:var(--c-primary);color:#fff}
                .cmp td:first-child{font-weight:700;width:26%}
                .cmp td:last-child{font-weight:600}
                .cmp-wrap{overflow-x:auto}
                @media(max-width:620px){.cmp td:first-child{width:auto}}
            </style>
            <div class="cmp-wrap">
                <table class="cmp">
                    <thead><tr><th></th><th>Zelf bouwen</th><th>Laten maken door ons</th></tr></thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr><td>{{ $row[0] }}</td><td class="muted">{{ $row[1] }}</td><td>{{ $row[2] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section style="background:var(--c-surface)">
        <div class="wrap" style="max-width:760px">
            <span class="kicker"><span class="kicker-line"></span> Kort gezegd</span>
            <h2>Wanneer kies je wat?</h2>
            <div class="prose" style="margin-top:1rem">
                <p>Wil je vooral tijd besparen en zeker weten dat je gevonden wordt en aanvragen binnenhaalt, dan is laten maken de logische keuze. Je houdt tijd over voor je echte werk en betaalt een vast bedrag per maand.</p>
                <p>Vind je het leuk om er zelf mee te stoeien en heb je de tijd, dan kun je met een website-bouwer een eind komen. Houd er rekening mee dat vindbaarheid, onderhoud en een professionele uitstraling dan jouw taak blijven.</p>
                <p>Twijfel je? Vraag een gratis voorbeeld aan. Dan zie je vrijblijvend wat wij voor je {{ $trade }} zouden maken, en kun je zelf vergelijken.</p>
            </div>
            <a href="#contact" class="btn" style="margin-top:1.2rem">Gratis voorbeeld aanvragen</a>
        </div>
    </section>

    <div id="contact" class="scroll-anchor" aria-hidden="true"></div>
    @include('channels.partials.lead-wizard', ['site' => $site, 'facet' => 'website'])
@endsection
