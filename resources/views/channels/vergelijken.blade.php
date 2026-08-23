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

@section('title', 'Zelf een website bouwen of laten maken voor ' . ($t['trades'] ?? 'ondernemers') . '?')
@section('description', 'Zelf bouwen met een website-bouwer of je website laten maken? Een eerlijke vergelijking voor je ' . $trade . '.')

@section('content')
    @include('channels.partials.breadcrumb', ['items' => [['label' => 'Home', 'url' => $site->url('')], ['label' => 'Zelf bouwen of laten maken']]])
    <section class="hero">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Vergelijken</span>
            <h1>Zelf bouwen of laten maken voor {{ $t['trades'] ?? 'ondernemers' }}?</h1>
            <p class="lead" style="max-width:60ch">Je kunt zelf een site in elkaar zetten met een website-bouwer, of het laten doen. Beide kan. Hieronder een eerlijke vergelijking, zodat je weet wat bij jou past.</p>
        </div>
    </section>

    <section>
        <div class="wrap">
            <style>
                .cmp{width:100%;border-collapse:separate;border-spacing:0;font-size:.97rem;background:var(--c-bg);border-radius:var(--radius);overflow:hidden;border:1px solid color-mix(in srgb,var(--c-ink) 9%,transparent);box-shadow:0 26px 55px -34px rgba(0,0,0,.45)}
                .cmp th,.cmp td{padding:1.05rem 1.25rem;text-align:left;vertical-align:middle}
                .cmp tbody tr:not(:last-child) td{border-bottom:1px solid color-mix(in srgb,var(--c-ink) 7%,transparent)}
                .cmp thead th{font-size:.78rem;text-transform:uppercase;letter-spacing:.06em;font-weight:800;padding-top:1.15rem;padding-bottom:1.15rem}
                .cmp thead th.col-self{color:var(--c-muted)}
                /* Uitgelichte 'ons'-kolom */
                .cmp .col-us{background:color-mix(in srgb,var(--c-accent) 8%,transparent)}
                .cmp thead th.col-us{background:var(--c-primary);color:#fff}
                .cmp td.col-crit{font-weight:700;width:27%}
                .cmp td.col-self{color:var(--c-muted)}
                .cmp td.col-us{font-weight:700}
                .cmp tbody tr:hover td{background:color-mix(in srgb,var(--c-ink) 3.5%,transparent)}
                .cmp tbody tr:hover td.col-us{background:color-mix(in srgb,var(--c-accent) 14%,transparent)}
                .cmp-cell{display:inline-flex;align-items:center;gap:.6rem}
                .cmp-ic{flex:0 0 auto;width:20px;height:20px}
                .cmp-ic.yes{color:var(--c-cta)}
                .cmp-ic.no{color:color-mix(in srgb,var(--c-ink) 34%,transparent)}
                .cmp-badge{display:inline-block;font-size:.62rem;font-weight:800;letter-spacing:.07em;background:var(--c-cta);color:var(--c-on-cta);padding:.2rem .55rem;border-radius:999px;margin-right:.6rem;vertical-align:middle}
                .cmp-wrap{overflow-x:auto}
                @media(max-width:620px){.cmp td.col-crit{width:auto}.cmp th,.cmp td{padding:.85rem .9rem}}
            </style>
            <div class="cmp-wrap">
                <table class="cmp">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="col-self">Zelf bouwen</th>
                            <th class="col-us"><span class="cmp-badge">Aanbevolen</span>Laten maken door ons</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td class="col-crit">{{ $row[0] }}</td>
                                <td class="col-self"><span class="cmp-cell"><svg class="cmp-ic no" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>{{ $row[1] }}</span></td>
                                <td class="col-us"><span class="cmp-cell"><svg class="cmp-ic yes" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>{{ $row[2] }}</span></td>
                            </tr>
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
