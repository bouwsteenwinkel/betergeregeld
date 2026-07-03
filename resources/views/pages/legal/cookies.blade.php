@extends('layouts.app')

@php
	$l  = (array) config('legal', []);
	$op = $l['operator'] ?? 'Betergeregeld ICT';
@endphp

@section('title', 'Cookiebeleid | Beter Geregeld ICT')
@section('description', 'Welke cookies ' . $op . ' gebruikt en hoe je je toestemming beheert.')
@section('robots', 'noindex,follow')

@include('pages.legal._prose-head')

@section('content')
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1100px] mx-auto px-6 py-16">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-5 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">Home</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Cookiebeleid</span>
		</nav>
		<span class="pill pill-dark mb-4">Juridisch</span>
		<h1 class="display-1 mb-3">Cookiebeleid</h1>
		<p class="text-[color:var(--color-on-dark-muted)]">Laatst bijgewerkt: {{ $l['updated'] ?? '' }}</p>
	</div>
</section>

<section class="py-16">
	<div class="legal-prose px-6">
		<p>Deze website, geëxploiteerd door {{ $op }}, gebruikt cookies en vergelijkbare technieken. Op deze pagina lees je welke dat zijn en hoe je je toestemming beheert.</p>

		<h2>Wat zijn cookies?</h2>
		<p>Cookies zijn kleine tekstbestandjes die bij een bezoek aan de website op je apparaat worden opgeslagen. Ze zorgen er onder andere voor dat de site goed werkt en, met je toestemming, dat we kunnen meten hoe de site gebruikt wordt.</p>

		<h2>Welke cookies gebruiken we?</h2>
		<h3>Functionele en noodzakelijke cookies</h3>
		<p>Deze zijn nodig om de website goed te laten werken en om formulieren veilig te versturen (bijvoorbeeld een sessie- en beveiligingscookie). Hiervoor is geen toestemming nodig; zonder deze cookies werkt de site niet goed.</p>

		<h3>Analytische cookies</h3>
		<p>Met deze cookies meten we — alleen met jouw toestemming — hoe bezoekers de site gebruiken, zodat we hem kunnen verbeteren. Deze cookies worden pas geplaatst nadat je ze in de cookiemelding hebt geaccepteerd.</p>

		<h3>Marketingcookies</h3>
		<p>Als we in de toekomst marketing- of trackingcookies inzetten, gebeurt dat uitsluitend nadat je daar in de cookiemelding toestemming voor hebt gegeven.</p>

		<h2>Je toestemming beheren</h2>
		<p>Bij je eerste bezoek vragen we via een cookiemelding om je keuze. Je kunt die keuze op elk moment aanpassen of intrekken via de knop hieronder. Daarnaast kun je cookies altijd verwijderen of blokkeren via de instellingen van je browser.</p>
		<p><button type="button" class="btn btn-ghost" data-cmp-open>Cookievoorkeuren aanpassen</button></p>

		<h2>Vragen?</h2>
		<p>Neem gerust contact op via de gegevens in ons <a href="{{ route('legal.privacy') }}">privacybeleid</a>.</p>
	</div>
</section>
@endsection
