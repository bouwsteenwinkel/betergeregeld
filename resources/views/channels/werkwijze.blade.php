@php
    /** @var \App\Support\ChannelSite $site */
    $t = array_merge((array) config('channel_places.defaults', []), array_filter((array) $site->get('places', []), fn ($v) => is_scalar($v) && $v !== ''));
    $trade = $t['trade'] ?? 'bedrijf';

    $steps = [
        ['n' => '1', 'h' => 'Gratis voorbeeld', 'p' => 'Je beantwoordt een paar korte vragen over je bedrijf. Wij zetten binnen 1 à 2 werkdagen een voorbeeld van jóuw ' . $trade . ' klaar, zodat je meteen ziet hoe het eruit kan zien. Gratis en vrijblijvend, je zit nergens aan vast.'],
        ['n' => '2', 'h' => 'Samen scherpstellen', 'p' => 'We bespreken het voorbeeld, bij jou op locatie in de regio of gewoon online. Jij bepaalt wat er wel en niet in moet, welke teksten en foto\'s erbij komen, tot het helemaal klopt.'],
        ['n' => '3', 'h' => 'Live en vindbaar', 'p' => 'We zetten je site live en zorgen dat je gevonden wordt in Google in je eigen regio. Aanvragen komen vanaf dat moment rechtstreeks in je mailbox binnen.'],
        ['n' => '4', 'h' => 'Groeit met je mee', 'p' => 'Later uitbreiden met een webshop, klantenportaal, automatisering of AI? Dat bouwen we er gewoon op voort. Je hoeft nooit opnieuw te beginnen.'],
    ];
    $garanties = [
        ['h' => 'Vaste prijs vooraf', 'p' => 'Een duidelijke prijs, geen verrassingen achteraf.'],
        ['h' => 'Geen lange contracten', 'p' => 'Een vast bedrag per maand, maandelijks opzegbaar.'],
        ['h' => 'Echt bereikbaar', 'p' => 'Een Nederlands team dat opneemt en meedenkt.'],
        ['h' => 'Techniek uit handen', 'p' => 'Hosting, onderhoud, updates en vindbaarheid regelen wij.'],
    ];
@endphp
@extends('channels.layout')

@section('title', 'Onze werkwijze')
@section('description', 'Van gratis voorbeeld tot een website die voor je werkt. Zo pakken we het aan voor je ' . $trade . ', zonder technisch gedoe.')

@section('content')
    <section class="hero">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Zo werkt het</span>
            <h1>Van gratis voorbeeld tot een site die voor je werkt</h1>
            <p class="lead" style="max-width:58ch">Geen technisch gedoe en geen dure alles-of-niets-projecten. In een paar duidelijke stappen sta je online, met een aanpak die met je meegroeit.</p>
            <a href="#contact" class="btn">Gratis voorbeeld aanvragen</a>
        </div>
    </section>

    <section>
        <div class="wrap">
            <div class="steps">
                @foreach ($steps as $s)
                    <div class="step">
                        <div class="step-num">{{ $s['n'] }}</div>
                        <h3>{{ $s['h'] }}</h3>
                        <p>{{ $s['p'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section style="background:var(--c-surface)">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Waar je op kunt rekenen</span>
            <h2>Duidelijk en zonder gedoe</h2>
            <div class="grid cols-4 feature-grid" style="margin-top:1.4rem">
                @foreach ($garanties as $g)
                    <div class="feature-card"><h3>{{ $g['h'] }}</h3><span class="feature-rule"></span><p>{{ $g['p'] }}</p></div>
                @endforeach
            </div>
        </div>
    </section>

    <section>
        <div class="wrap" style="max-width:760px">
            <h2>Wat we van je nodig hebben</h2>
            <div class="prose" style="margin-top:1rem">
                <p>Om een sterk voorbeeld te maken, hebben we een paar dingen van je nodig: wat foto's van je werk, je gegevens en werkgebied, en een idee van wat je wil bereiken. Meer niet. De teksten, techniek en vindbaarheid nemen wij voor onze rekening.</p>
                <p>Heb je al een website of foto's? Dan gebruiken we wat bruikbaar is. Je hoeft dus niet met een blanco vel te beginnen.</p>
            </div>
            <a href="#contact" class="btn" style="margin-top:1.2rem">Vraag je gratis voorbeeld aan</a>
        </div>
    </section>

    <div id="contact" class="scroll-anchor" aria-hidden="true"></div>
    @include('channels.partials.lead-wizard', ['site' => $site, 'facet' => 'website'])
@endsection
