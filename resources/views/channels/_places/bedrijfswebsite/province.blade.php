{{-- Bespoke provincie-pagina voor bedrijfswebsite, in homepage-stijl.
     Data: $site, $provName, $provSlug, $provPlaces (slug => naam).
     A: live filter + A-Z-groepering · B: unieke intro + FAQ(-schema) · C: strak raster. --}}
@extends('channels.layout')

@section('title', 'Website laten maken in ' . $provName)
@section('description', 'Website, webshop of klantenportaal laten maken in ' . $provName . '? Bekijk alle ' . count($provPlaces) . ' plaatsen waar we voor ondernemers werken, of vraag gratis een voorbeeld aan.')

@section('content')
    @include('channels._sales._bg-page-styles')

    @include('channels._sales._bg-tool-hero', [
        'heroEyebrow' => 'Provincie ' . $provName,
        'heroTitle'   => 'Website laten maken in ' . $provName,
        'heroSub'     => 'Meer klanten uit ' . $provName . '. Typ je bedrijfsnaam en zie meteen een voorbeeld van jouw website.',
    ])

    @php
        // A-Z: groepeer plaatsen op eerste letter (scanbaarheid).
        $groups = [];
        foreach ($provPlaces as $slug => $naam) {
            $letter = mb_strtoupper(mb_substr((string) $naam, 0, 1));
            $groups[$letter][$slug] = $naam;
        }
        ksort($groups);

        // B: unieke intro. Bereik (eerste tot laatste plaats) + count + provincienaam
        // maken elke provincie-pagina anders; provSlug kiest deterministisch een
        // zinstructuur, zodat het geen 12× identieke templatetekst is.
        $sortedNames = array_values($provPlaces);
        sort($sortedNames, SORT_NATURAL | SORT_FLAG_CASE);
        $eersteP  = $sortedNames[0] ?? '';
        $laatsteP = end($sortedNames) ?: '';
        $aantal   = count($provPlaces);
        $v = abs(crc32($provSlug)) % 3;
        $intros = [
            'In ' . $provName . ' helpen we ondernemers online groeien, van ' . $eersteP . ' tot ' . $laatsteP . '. In totaal ' . $aantal . ' plaatsen. Kies hieronder je plaats, of typ direct je bedrijfsnaam voor een gratis voorbeeld.',
            'Van ' . $eersteP . ' tot ' . $laatsteP . ': in ' . $aantal . ' plaatsen in ' . $provName . ' maken we websites, webshops en klantenportalen voor lokale ondernemers. Zoek je plaats of start meteen met een gratis voorbeeld.',
            'Ondernemers in ' . $provName . ' vinden hun klanten steeds vaker online. In alle ' . $aantal . ' plaatsen, van ' . $eersteP . ' tot ' . $laatsteP . ', zetten we je professioneel op de kaart. Kies hieronder je plaats.',
        ];
        $introText = $intros[$v];

        $faq = [
            ['Werken jullie ook in ' . $provName . '?', 'Ja. We maken websites, webshops en klantenportalen voor ondernemers in heel ' . $provName . ', in alle ' . $aantal . ' plaatsen hierboven. Alles gaat online en telefonisch, dus je locatie maakt niet uit.'],
            ['Wat kost een website laten maken in ' . $provName . '?', 'Een vaste, duidelijke prijs zonder verrassingen achteraf. Je ziet eerst gratis een voorbeeld van jouw website; pas als je tevreden bent, beslis je.'],
            ['Hoe snel sta ik online?', 'Vaak binnen een week. Typ je bedrijfsnaam bovenaan, dan zie je meteen een voorbeeld. Daarna maken we het samen af in een korte, vrijblijvende videoafspraak.'],
            ['Moet ik technische kennis hebben?', 'Nee. Wij regelen alles: techniek, hosting en onderhoud. Jij hebt één vaste contactpersoon en houdt je bezig met ondernemen.'],
        ];
    @endphp

    <style>
        .bg-plwrap { max-width: 1280px; margin: 0 auto; padding: 0 24px; }
        .bg-plfilter { width: 100%; max-width: 420px; padding: 13px 16px; font-size: 16px; border: 1.5px solid #E5E3DF; border-radius: 10px; background: #fff; color: #1A1A1A; margin: 0 0 30px; }
        .bg-plfilter:focus { outline: none; border-color: #12386B; }
        .bg-lettergroup { margin: 0 0 22px; }
        .bg-letter { font-size: 13px; font-weight: 900; letter-spacing: .08em; color: #12386B; margin: 0 0 8px; text-transform: uppercase; }
        .bg-placegrid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(160px, 100%), 1fr)); gap: 8px; }
        .bg-placegrid > .bg-card { overflow-wrap: anywhere; padding: 11px 13px; background: #fff; border: 1.5px solid #E5E3DF; border-radius: 8px; text-decoration: none; font-size: 15px; font-weight: 700; color: #1A1A1A; transition: border-color .12s, background .12s; }
        .bg-placegrid > .bg-card:hover { border-color: #12386B; background: #F7F6F3; }
        .bg-faq { max-width: 760px; margin: 0 auto; }
        .bg-faqitem { border-bottom: 1px solid #E5E3DF; padding: 18px 0; }
        .bg-faqitem:last-child { border-bottom: 0; }
        .bg-faqq { font-size: 18px; font-weight: 800; color: #1A1A1A; margin: 0 0 6px; }
        .bg-faqa { font-size: 16px; color: #6B6864; margin: 0; line-height: 1.55; }
        @media (max-width: 560px) {
            .bg-placegrid { grid-template-columns: repeat(auto-fill, minmax(min(120px, 100%), 1fr)); gap: 8px; }
            .bg-provback { display: inline-block; padding: 12px 0; min-height: 44px; }
        }
    </style>

    <section style="padding: 64px calc(50vw - 50%); margin: 0 calc(50% - 50vw); width: 100vw; background: #fff; border-top: 1px solid #E5E3DF;">
        <div class="bg-plwrap">
            <h2 style="font-size: clamp(26px, 3.4vw, 38px); line-height: 1.1; letter-spacing: -0.02em; font-weight: 900; margin: 0 0 12px; color: #1A1A1A;">Plaatsen in {{ $provName }}</h2>
            <p style="font-size: 18px; color: #6B6864; margin: 0 0 26px; max-width: 62ch;">{{ $introText }}</p>

            <input type="search" id="bg-plfilter" class="bg-plfilter" placeholder="Zoek je plaats in {{ $provName }}…" autocomplete="off" aria-label="Zoek je plaats">

            <div id="bg-placelist">
                @foreach ($groups as $letter => $places)
                    <div class="bg-lettergroup">
                        <div class="bg-letter">{{ $letter }}</div>
                        <div class="bg-placegrid">
                            @foreach ($places as $slug => $naam)
                                <a href="{{ $site->url('plaatsen/' . $slug) }}" class="bg-card" data-name="{{ mb_strtolower((string) $naam) }}">{{ $naam }}</a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <p id="bg-plnoresult" style="display: none; font-size: 16px; color: #6B6864; margin: 8px 0 0;">Geen plaats gevonden. Typ je bedrijfsnaam bovenaan, we werken in heel {{ $provName }}.</p>

            <p style="margin: 28px 0 0;"><a href="{{ $site->url('plaatsen') }}" class="bg-alink bg-provback" style="font-size: 16px; font-weight: 700; color: #12386B; text-decoration: none;">&larr; Alle provincies</a></p>
        </div>
    </section>

    <section style="padding: 64px calc(50vw - 50%); margin: 0 calc(50% - 50vw); width: 100vw; background: #F7F6F3; border-top: 1px solid #E5E3DF;">
        <div class="bg-plwrap">
            <h2 style="font-size: clamp(24px, 3vw, 32px); font-weight: 900; letter-spacing: -0.02em; color: #1A1A1A; margin: 0 0 24px; text-align: center;">Veelgestelde vragen over {{ $provName }}</h2>
            <div class="bg-faq">
                @foreach ($faq as [$q, $a])
                    <div class="bg-faqitem">
                        <p class="bg-faqq">{{ $q }}</p>
                        <p class="bg-faqa">{{ $a }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <script type="application/ld+json">
    {!! json_encode([
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => array_map(fn ($qa) => [
            '@type'          => 'Question',
            'name'           => $qa[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]],
        ], $faq),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <script>
    (function () {
        var input = document.getElementById('bg-plfilter');
        if (!input) return;
        var groups = [].slice.call(document.querySelectorAll('#bg-placelist .bg-lettergroup'));
        var noRes  = document.getElementById('bg-plnoresult');
        input.addEventListener('input', function () {
            var q = input.value.trim().toLowerCase();
            var anyGlobal = false;
            groups.forEach(function (g) {
                var any = false;
                [].slice.call(g.querySelectorAll('.bg-card')).forEach(function (c) {
                    var match = c.getAttribute('data-name').indexOf(q) !== -1;
                    c.style.display = match ? '' : 'none';
                    if (match) any = true;
                });
                g.style.display = any ? '' : 'none';
                if (any) anyGlobal = true;
            });
            if (noRes) noRes.style.display = anyGlobal ? 'none' : '';
        });
    })();
    </script>

    @include('channels._sales._bg-cta', [
        'ctaTitle' => 'Meer klanten in ' . $provName . '?',
        'ctaSub'   => 'Bekijk gratis en vrijblijvend hoe jouw website eruit kan zien.',
    ])
@endsection
