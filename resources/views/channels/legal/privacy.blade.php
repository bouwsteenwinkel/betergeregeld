@php
    /** @var \App\Support\ChannelSite $site */
    $l    = (array) config('legal', []);
    $op   = $l['operator'] ?? 'Betergeregeld ICT';
    $mail = $l['email'] ?? $site->brand('email');
    $tel  = $l['phone'] ?? $site->brand('phone');

    // Bedrijfsdetails + bereikbaarheid als vooraf opgebouwde stukjes (voorkomt
    // fragiele inline-@if-ketens).
    $details = [];
    if (! empty($l['legal_name'])) $details[] = 'statutaire naam ' . $l['legal_name'];
    if (! empty($l['address']))    $details[] = 'gevestigd te ' . $l['address'];
    if (! empty($l['kvk']))        $details[] = 'KvK ' . $l['kvk'];
    $detailStr = $details ? ' (' . implode(', ', $details) . ')' : '';

    $mailLink = $mail ? '<a href="mailto:' . e($mail) . '">' . e($mail) . '</a>' : '';
    $reach    = trim($mailLink . ($tel ? ($mailLink ? ' of ' : '') . e($tel) : ''));
@endphp
@extends('channels.layout')

@section('title', 'Privacybeleid')
@section('description', 'Hoe ' . $op . ' omgaat met je persoonsgegevens.')
@section('robots', 'noindex,follow')

@section('content')
    <section class="hero">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Juridisch</span>
            <h1>Privacybeleid</h1>
            <p class="muted">Laatst bijgewerkt: {{ $l['updated'] ?? '' }}</p>
        </div>
    </section>

    <section>
        <div class="wrap prose" style="max-width:760px">
            <p>Deze website ({{ $site->displayName() }}) wordt geëxploiteerd door <strong>{{ $op }}</strong>. Wij hechten veel waarde aan je privacy en gaan zorgvuldig om met je persoonsgegevens, in lijn met de Algemene Verordening Gegevensbescherming (AVG). In dit beleid lees je welke gegevens we verwerken, waarom en wat je rechten zijn.</p>

            <h2>1. Wie is verantwoordelijk?</h2>
            <p>{{ $op }}{{ $detailStr }} is de verwerkingsverantwoordelijke.@if ($reach) Bereikbaar via {!! $reach !!}.@endif</p>

            <h2>2. Welke gegevens verwerken we?</h2>
            <p>Wanneer je een gratis voorbeeld of contact aanvraagt via het formulier, verwerken we de gegevens die je zelf invult: je naam, bedrijfsnaam, e-mailadres, telefoonnummer, plaats en een eventueel bericht. Daarnaast verwerken we bij een aanvraag welke fase/dienst je aanklikte, zodat we je gericht kunnen helpen.</p>
            <p>Als je onze website bezoekt, worden daarnaast automatisch technische gegevens verwerkt, zoals je IP-adres, browser- en apparaattype en de pagina's die je bekijkt. Dit gebeurt deels via cookies (zie ons <a href="{{ $site->url('cookiebeleid') }}">cookiebeleid</a>).</p>

            <h2>3. Waarvoor en op welke grondslag?</h2>
            <ul>
                <li><strong>Contact opnemen en een voorbeeld/offerte maken</strong> — om je aanvraag te beantwoorden en onze dienst te leveren. Grondslag: uitvoering van (de aanloop naar) een overeenkomst.</li>
                <li><strong>Verbeteren en beveiligen van de website</strong> — om de site goed te laten werken en misbruik te voorkomen. Grondslag: gerechtvaardigd belang.</li>
                <li><strong>Analyse (indien geplaatst)</strong> — om te begrijpen hoe de site gebruikt wordt. Grondslag: jouw toestemming (via de cookiemelding).</li>
            </ul>

            <h2>4. Hoe lang bewaren we je gegevens?</h2>
            <p>We bewaren je gegevens niet langer dan nodig. Aanvragen die niet tot een klant leiden bewaren we maximaal 12 maanden. Word je klant, dan gelden de wettelijke bewaartermijnen (o.a. 7 jaar voor de administratie). Daarna verwijderen we je gegevens of maken we ze anoniem.</p>

            <h2>5. Delen met anderen</h2>
            <p>We verkopen je gegevens nooit. We schakelen wel dienstverleners in die namens ons gegevens verwerken (verwerkers), zoals onze hostingpartij en de partij die onze e-mail verstuurt. Met hen maken we verwerkersafspraken. We verstrekken gegevens alleen aan derden als dat wettelijk verplicht is.</p>

            <h2>6. Cookies</h2>
            <p>We gebruiken functionele cookies (nodig om de site te laten werken) en, met je toestemming, analytische cookies. Meer hierover lees je in ons <a href="{{ $site->url('cookiebeleid') }}">cookiebeleid</a>.</p>

            <h2>7. Beveiliging</h2>
            <p>We nemen passende technische en organisatorische maatregelen om je gegevens te beschermen, waaronder een beveiligde (https-)verbinding en toegang alleen voor wie dat nodig heeft.</p>

            <h2>8. Jouw rechten</h2>
            <p>Je hebt het recht om je gegevens in te zien, te laten corrigeren of verwijderen, de verwerking te beperken, bezwaar te maken en je gegevens over te laten dragen. Ook kun je een gegeven toestemming altijd intrekken. Stuur je verzoek naar {!! $mailLink ?: 'ons contactadres' !!}. Ben je het ergens niet mee eens? Dan kun je een klacht indienen bij de <a href="{{ $l['ap_url'] ?? 'https://www.autoriteitpersoonsgegevens.nl' }}" target="_blank" rel="noopener">Autoriteit Persoonsgegevens</a>.</p>

            <h2>9. Wijzigingen</h2>
            <p>We kunnen dit privacybeleid van tijd tot tijd aanpassen. De meest recente versie staat altijd op deze pagina.</p>

            <p style="margin-top:2rem"><a href="{{ $site->navHref('#gratis-voorbeeld') }}" class="btn">Gratis voorbeeld aanvragen</a></p>
        </div>
    </section>
@endsection
