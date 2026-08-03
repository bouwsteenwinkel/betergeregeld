{{--
    Werkgebied-blok voor de facetpagina's (/website, /webshop, /klantenportaal,
    /automatisering, /ai).

    WAAROM DIT ER IS. De vijf facetpagina's zijn de commerciële pagina's van dit
    kanaal, maar ze stonden er los bij: geen enkele link naar de plaatspagina's,
    terwijl de plaatspagina's wél naar hun buurplaatsen linken. Gemeten 02-08-2026
    stond /klantenportaal op gemiddelde positie 98 met dertien vertoningen.

    We linken naar de GROOTSTE plaatsen (aantal adressen uit de BAG, zie
    channel_place_facts.adressen) en niet naar een willekeurige greep. Dat is
    precies de verzameling die na de nieuwe gating overblijft, dus we sturen
    bezoekers en zoekmachines naar pagina's die het waard zijn — en niet naar de
    honderden dorpspagina's die Google toch al afwees.

    Vereist: $site, $facet. Optioneel: $aantal (standaard 12).
--}}
@php
    $aantal = $aantal ?? 12;
    $facetLabel = (array) config('groeidiamant.facets.' . $facet, []);
    $wat = $facetLabel['label'] ?? 'website';
    $wat = $wat === mb_strtoupper($wat) ? $wat : mb_strtolower($wat);

    // Eén query, een dag gecacht: dit staat op vijf pagina's en de lijst verandert
    // alleen als de BAG-verrijking opnieuw draait.
    //
    // We cachen PLATTE ARRAYS en geen stdClass-rijen. Met objecten in de bestandscache
    // komt de tweede pagina terug met "tried to access a property on an incomplete
    // object": de eerste render vult de cache en werkt, de volgende leest 'm terug en
    // valt om met een 500. Precies dat gebeurde hier op /ai terwijl /website nog goed
    // ging (03-08-2026).
    $plaatsen = \Illuminate\Support\Facades\Cache::remember(
        'facet_werkgebied_' . $site->key . '_' . $aantal,
        86400,
        function () use ($aantal) {
            return \Illuminate\Support\Facades\DB::table('channel_place_facts')
                ->whereNotNull('adressen')
                ->where('adressen', '>=', (int) config('channel_places.index_min_addresses', 0))
                ->orderByDesc('adressen')
                ->limit($aantal)
                ->get(['slug', 'naam'])
                ->map(fn ($r) => ['slug' => (string) $r->slug, 'naam' => (string) $r->naam])
                ->all();
        }
    );
@endphp

@if ($plaatsen)
    <section class="section" id="werkgebied">
        <div class="wrap">
            <h2 style="margin:.3rem 0 .6rem">Een {{ $wat }} laten maken in jouw plaats</h2>
            <p class="muted" style="max-width:46rem;margin:0 0 1.1rem">
                We werken door het hele land. Kijk wat er in jouw plaats speelt en welke bedrijven
                er al actief zijn.
            </p>
            <div style="display:flex;flex-wrap:wrap;gap:.5rem">
                @foreach ($plaatsen as $p)
                    <a href="{{ $site->url('plaatsen/' . $p['slug']) }}"
                       style="display:inline-block;padding:.4rem .8rem;border:1px solid var(--c-line, rgba(0,0,0,.12));border-radius:999px;font-size:.92rem;text-decoration:none">{{ $p['naam'] }}</a>
                @endforeach
                <a href="{{ $site->url('plaatsen') }}"
                   style="display:inline-block;padding:.4rem .8rem;border-radius:999px;font-size:.92rem;font-weight:600;text-decoration:none">Alle plaatsen →</a>
            </div>
        </div>
    </section>
@endif
