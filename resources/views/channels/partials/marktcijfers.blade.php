@php
    /** @var \App\Support\ChannelSite $site */
    /*
     * Echte cijfers over de eigen markt, per branche uit channel_place_listings.
     *
     * Waarom dit blok bestaat: op /prijzen was 95% van de tekst woordelijk gelijk
     * aan die van de zestien andere channel-sites, op /vergelijken 86%. Alleen de
     * plaatspagina's gebruikten de lokale data al, en dat zijn precies de enige
     * pagina's die klikken opleveren. Dit blok brengt diezelfde data naar de
     * verkooppagina's. Zie docs/SEO-channels-eigenheid-2026-09-05.md.
     *
     * Twee varianten, bewust met een andere invalshoek, zodat /prijzen en
     * /vergelijken niet elkaars duplicaat worden:
     *   prijzen      → wat het kost, afgezet tegen hoe druk de markt is
     *   vergelijken  → welke lat er ligt als je het zelf gaat doen
     *
     * Valt de data weg, dan verdwijnt het blok geruisloos.
     */
    $cijfers = app(\App\Services\ChannelSites\BrancheMarktcijfers::class)->voor((string) $site->brancheKey());

    $t      = array_merge((array) config('channel_places.defaults', []), array_filter((array) $site->get('places', []), fn ($v) => is_scalar($v) && $v !== ''));
    $trade  = $t['trade']  ?? 'bedrijf';
    $trades = $t['trades'] ?? 'bedrijven';

    $variant = $variant ?? 'prijzen';

    $nl = fn ($n) => number_format((int) $n, 0, ',', '.');
@endphp
@if ($cijfers)
<section data-section="marktcijfers" style="padding:56px 0">
    <style>
        [data-section="marktcijfers"] .mc-head{max-width:62ch;margin-bottom:1.8rem}
        [data-section="marktcijfers"] .mc-head h2{margin:.3rem 0 .6rem}
        [data-section="marktcijfers"] .mc-head p{margin:0;color:var(--c-muted)}
        [data-section="marktcijfers"] .mc-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;align-items:stretch}
        [data-section="marktcijfers"] .mc-cel{padding:1.15rem 1.2rem;border-radius:var(--radius);
            border:1px solid color-mix(in srgb,var(--c-ink) 10%,transparent);background:var(--c-bg);display:flex;flex-direction:column;gap:.35rem}
        [data-section="marktcijfers"] .mc-getal{font-size:1.85rem;font-weight:800;line-height:1.05;font-variant-numeric:tabular-nums;color:var(--c-primary)}
        [data-section="marktcijfers"] .mc-label{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--c-muted)}
        [data-section="marktcijfers"] .mc-uitleg{font-size:.88rem;line-height:1.45;margin:0}
        [data-section="marktcijfers"] .mc-bron{font-size:.78rem;color:var(--c-muted);margin:1.1rem 0 0}
        @media(max-width:900px){[data-section="marktcijfers"] .mc-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:520px){[data-section="marktcijfers"] .mc-grid{grid-template-columns:1fr}}
    </style>
    <div class="wrap">
        <div class="mc-head">
            <span class="kicker"><span class="kicker-line"></span> De markt voor {{ $trades }}</span>
            @if ($variant === 'vergelijken')
                <h2>Dit is de lat waar je overheen moet</h2>
                <p>Voor je kiest tussen zelf bouwen en laten maken, is het handig te weten waar je tegenop kijkt. We hebben {{ $nl($cijfers['aanbieders']) }} {{ $trades }} in {{ $nl($cijfers['plaatsen']) }} Nederlandse plaatsen in kaart.</p>
            @else
                <h2>Wat je ervoor terugkrijgt in jouw markt</h2>
                <p>Een prijs zegt weinig zonder de markt eromheen. Daarom eerst de cijfers: {{ $nl($cijfers['aanbieders']) }} {{ $trades }} in {{ $nl($cijfers['plaatsen']) }} Nederlandse plaatsen, geteld uit openbare bedrijfsgegevens.</p>
            @endif
        </div>

        <div class="mc-grid">
            <div class="mc-cel">
                <span class="mc-getal">{{ number_format($cijfers['per_plaats'], 1, ',', '.') }}</span>
                <span class="mc-label">{{ $trades }} per plaats</span>
                <p class="mc-uitleg">Zoveel {{ $trades }} staan er gemiddeld in één plaats ingeschreven. In {{ $nl($cijfers['plaatsen_vol']) }} plaatsen ({{ $cijfers['plaatsen_vol_pct'] }}%) zijn het er vijf of meer.</p>
            </div>

            @if ($cijfers['waardering'])
            <div class="mc-cel">
                <span class="mc-getal">{{ number_format($cijfers['waardering'], 2, ',', '.') }}</span>
                <span class="mc-label">gemiddelde waardering</span>
                <p class="mc-uitleg">Het gemiddelde cijfer van een {{ $trade }} in Nederland. Wie daaronder zit, valt op — en niet gunstig.</p>
            </div>
            @endif

            @if ($cijfers['reviews_mediaan'] !== null)
            <div class="mc-cel">
                <span class="mc-getal">{{ $nl($cijfers['reviews_mediaan']) }}</span>
                <span class="mc-label">beoordelingen (mediaan)</span>
                <p class="mc-uitleg">De helft van de {{ $trades }} heeft er minder, de helft meer.@if ($cijfers['koploper_mediaan']) De best beoordeelde in een plaats zit meestal rond de {{ $nl($cijfers['koploper_mediaan']) }}.@endif</p>
            </div>
            @endif

            <div class="mc-cel">
                <span class="mc-getal">{{ $cijfers['zonder_site_pct'] }}%</span>
                <span class="mc-label">zonder eigen website</span>
                <p class="mc-uitleg">{{ $nl($cijfers['zonder_site']) }} {{ $trades }} zijn alleen via een kaartvermelding te vinden. De rest heeft een site — daar concurreer je dus mee.</p>
            </div>
        </div>

        <p class="mc-bron">
            @if ($variant === 'vergelijken')
                Zelf bouwen kan prima. Maar met {{ number_format($cijfers['per_plaats'], 1, ',', '.') }} {{ $trades }} per plaats en een gemiddelde van {{ number_format((float) $cijfers['waardering'], 2, ',', '.') }} sterren is "af" niet hetzelfde als "beter dan de buren".
            @else
                Cijfers uit openbare bedrijfsgegevens over {{ $nl($cijfers['plaatsen']) }} plaatsen, periodiek ververst. Bedoeld als beeld van de markt, niet als ranglijst.
            @endif
        </p>
    </div>
</section>
@endif
