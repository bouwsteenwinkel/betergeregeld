@extends('layouts.app')

@php
	$l    = (array) config('legal', []);
	$op   = $l['operator'] ?? 'Betergeregeld ICT';
	$mail = $l['email'] ?? null;
	$tel  = $l['phone'] ?? null;

	$details = [];
	if (! empty($l['legal_name'])) $details[] = 'statutaire naam ' . $l['legal_name'];
	if (! empty($l['address']))    $details[] = 'gevestigd te ' . $l['address'];
	if (! empty($l['kvk']))        $details[] = 'KvK ' . $l['kvk'];
	$detailStr = $details ? ' (' . implode(', ', $details) . ')' : '';

	$mailLink = $mail ? '<a href="mailto:' . e($mail) . '">' . e($mail) . '</a>' : '';
	$reach    = trim($mailLink . ($tel ? ($mailLink ? ' of ' : '') . e($tel) : ''));
@endphp

@section('title', 'Privacybeleid | Beter Geregeld ICT')
@section('description', 'Hoe ' . $op . ' omgaat met je persoonsgegevens.')
@section('robots', 'noindex,follow')

@include('pages.legal._prose-head')

@section('content')
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1100px] mx-auto px-6 py-16">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-5 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">Home</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Privacybeleid</span>
		</nav>
		<span class="pill pill-dark mb-4">Juridisch</span>
		<h1 class="display-1 mb-3">Privacybeleid</h1>
		<p class="text-[color:var(--color-on-dark-muted)]">Laatst bijgewerkt: {{ $l['updated'] ?? '' }}</p>
	</div>
</section>

<section class="py-16">
	<div class="legal-prose px-6">
		<p>Deze website wordt geëxploiteerd door <strong>{{ $op }}</strong>. Wij hechten veel waarde aan je privacy en gaan zorgvuldig om met je persoonsgegevens, in lijn met de Algemene Verordening Gegevensbescherming (AVG). In dit beleid lees je welke gegevens we verwerken, waarom en wat je rechten zijn.</p>

		<h2>1. Wie is verantwoordelijk?</h2>
		<p>{{ $op }}{{ $detailStr }} is de verwerkingsverantwoordelijke.@if ($reach) Bereikbaar via {!! $reach !!}.@endif</p>

		<h2>2. Welke gegevens verwerken we?</h2>
		<p>Wanneer je contact of een aanvraag doet via het formulier, verwerken we de gegevens die je zelf invult: je naam, bedrijfsnaam, e-mailadres, telefoonnummer, plaats en een eventueel bericht. Daarnaast verwerken we welke dienst je aanklikte, zodat we je gericht kunnen helpen.</p>
		<p>Als je onze website bezoekt, worden daarnaast automatisch technische gegevens verwerkt, zoals je IP-adres, browser- en apparaattype en de pagina's die je bekijkt. Dit gebeurt deels via cookies (zie ons <a href="{{ route('legal.cookies') }}">cookiebeleid</a>).</p>

		<h2>3. Waarvoor en op welke grondslag?</h2>
		<ul>
			<li><strong>Contact opnemen en een offerte maken</strong> — om je aanvraag te beantwoorden en onze dienst te leveren. Grondslag: uitvoering van (de aanloop naar) een overeenkomst.</li>
			<li><strong>Verbeteren en beveiligen van de website</strong> — om de site goed te laten werken en misbruik te voorkomen. Grondslag: gerechtvaardigd belang.</li>
			<li><strong>Analyse (indien geplaatst)</strong> — om te begrijpen hoe de site gebruikt wordt. Grondslag: jouw toestemming (via de cookiemelding).</li>
		</ul>

		<h2>4. Hoe lang bewaren we je gegevens?</h2>
		<p>We bewaren je gegevens niet langer dan nodig. Aanvragen die niet tot een klant leiden bewaren we maximaal 12 maanden. Word je klant, dan gelden de wettelijke bewaartermijnen (o.a. 7 jaar voor de administratie). Daarna verwijderen we je gegevens of maken we ze anoniem.</p>

		<h2>5. Delen met anderen</h2>
		<p>We verkopen je gegevens nooit. We schakelen wel dienstverleners in die namens ons gegevens verwerken (verwerkers), zoals onze hostingpartij en de partij die onze e-mail verstuurt. Met hen maken we verwerkersafspraken. We verstrekken gegevens alleen aan derden als dat wettelijk verplicht is.</p>

		<h2>6. Cookies</h2>
		<p>We gebruiken functionele cookies (nodig om de site te laten werken) en, met je toestemming, analytische cookies. Meer hierover lees je in ons <a href="{{ route('legal.cookies') }}">cookiebeleid</a>.</p>

		<h2>7. Beveiliging</h2>
		<p>We nemen passende technische en organisatorische maatregelen om je gegevens te beschermen, waaronder een beveiligde (https-)verbinding en toegang alleen voor wie dat nodig heeft.</p>

		<h2>8. Jouw rechten</h2>
		<p>Je hebt het recht om je gegevens in te zien, te laten corrigeren of verwijderen, de verwerking te beperken, bezwaar te maken en je gegevens over te laten dragen. Ook kun je een gegeven toestemming altijd intrekken. Stuur je verzoek naar {!! $mailLink ?: 'ons contactadres' !!}. Ben je het ergens niet mee eens? Dan kun je een klacht indienen bij de <a href="{{ $l['ap_url'] ?? 'https://www.autoriteitpersoonsgegevens.nl' }}" target="_blank" rel="noopener">Autoriteit Persoonsgegevens</a>.</p>

		<h2>9. Wijzigingen</h2>
		<p>We kunnen dit privacybeleid van tijd tot tijd aanpassen. De meest recente versie staat altijd op deze pagina.</p>

		<p style="margin-top:2rem"><a href="{{ route('contact') }}" class="btn btn-primary">Neem contact op</a></p>
	</div>
</section>
@endsection
