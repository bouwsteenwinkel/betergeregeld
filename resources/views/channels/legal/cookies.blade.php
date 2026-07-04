@php
    /** @var \App\Support\ChannelSite $site */
    $l  = (array) config('legal', []);
    $op = $l['operator'] ?? 'Betergeregeld ICT';
@endphp
@extends('channels.layout')

@section('title', 'Cookiebeleid')
@section('description', 'Welke cookies ' . $site->displayName() . ' gebruikt en hoe je je toestemming beheert.')
@section('robots', 'noindex,follow')

@section('content')
    <section class="hero">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Juridisch</span>
            <h1>Cookiebeleid</h1>
            <p class="muted">Laatst bijgewerkt: {{ $l['updated'] ?? '' }}</p>
        </div>
    </section>

    <section>
        <div class="wrap prose" style="max-width:760px">
            <p>Deze website ({{ $site->displayName() }}), geëxploiteerd door {{ $op }}, gebruikt cookies en vergelijkbare technieken. Op deze pagina lees je welke dat zijn en hoe je je toestemming beheert.</p>

            <h2>Wat zijn cookies?</h2>
            <p>Cookies zijn kleine tekstbestandjes die bij een bezoek aan de website op je apparaat worden opgeslagen. Ze zorgen er onder andere voor dat de site goed werkt en, met je toestemming, dat we kunnen meten hoe de site gebruikt wordt.</p>

            <h2>Welke cookies gebruiken we?</h2>
            <h3>Functionele en noodzakelijke cookies</h3>
            <p>Deze zijn nodig om de website goed te laten werken en om formulieren veilig te versturen (bijvoorbeeld een sessie- en beveiligingscookie). Hiervoor is geen toestemming nodig; zonder deze cookies werkt de site niet goed.</p>

            <h3>Analytische cookies</h3>
            <p>Met deze cookies meten we, alleen met jouw toestemming, hoe bezoekers de site gebruiken, zodat we hem kunnen verbeteren. Deze cookies worden pas geplaatst nadat je ze in de cookiemelding hebt geaccepteerd.</p>

            <h3>Marketingcookies</h3>
            <p>Als we in de toekomst marketing- of trackingcookies inzetten, gebeurt dat uitsluitend nadat je daar in de cookiemelding toestemming voor hebt gegeven.</p>

            <h2>Je toestemming beheren</h2>
            <p>Bij je eerste bezoek vragen we via een cookiemelding om je keuze. Je kunt die keuze op elk moment aanpassen of intrekken via de knop hieronder. Daarnaast kun je cookies altijd verwijderen of blokkeren via de instellingen van je browser.</p>
            <p><button type="button" class="btn btn-ghost" data-cmp-open-prefs>Cookievoorkeuren aanpassen</button></p>

            <h2>Vragen?</h2>
            <p>Neem gerust contact op via de gegevens in ons <a href="{{ $site->url('privacybeleid') }}">privacybeleid</a>.</p>
        </div>
    </section>
@endsection
