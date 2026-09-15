@extends('layouts.app')

{{--
	Gratis backup-check (15-09-2026): het instapaanbod.

	Waarom: de blog trekt IT-verantwoordelijken in het MKB (backup, M365, toegang; 94% van
	de vertoningen), maar die kopen geen maatwerk na één artikel. Via het contactformulier
	kwam sinds april 2026 geen enkele echte aanvraag binnen. Een gratis check met een
	vast, klein resultaat is een stap die ze wél zetten — en een aanvraag die we kunnen
	opvolgen.

	Het formulier post naar dezelfde ContactController als /nl/contact, dus honeypot,
	Turnstile, spamscore en de [WEBFORM]-mail naar dennis@ liften mee. Onderwerp is vast
	'backup-check' zodat hij in de admin en de mail herkenbaar is.
--}}

@php
	$hreflangLocales = ['nl'];
@endphp

@section('title', \App\Support\PaginaTitel::met('Gratis backup-check voor uw bedrijf'))
@section('description', 'Weet u zeker dat uw backup werkt? Wij controleren binnen vijf werkdagen wat er wordt bewaard, waar, hoe vaak en of het terug te zetten is. Gratis, zonder verplichting.')

@push('head')
	@php
		$ld = [
			'@context' => 'https://schema.org',
			'@type' => 'Service',
			'name' => 'Gratis backup-check',
			'serviceType' => 'Backup-controle',
			'provider' => ['@type' => 'Organization', 'name' => 'Beter Geregeld', 'url' => 'https://betergeregeld.com'],
			'areaServed' => 'NL',
			'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'],
			'url' => 'https://betergeregeld.com/nl/backup-check',
		];
	@endphp
	<script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1100px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="/nl" class="hover:text-white">Home</a>
			<span class="opacity-40">/</span>
			<a href="/nl/backup-en-monitoring" class="hover:text-white">Backup en monitoring</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Gratis backup-check</span>
		</nav>
		<span class="pill pill-dark mb-5">Gratis, zonder verplichting</span>
		<h1 class="display-1 mb-5">
			Werkt uw backup? <span class="accent-word">Wij zoeken het uit.</span>
		</h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			Vertel in twee minuten wat u nu heeft. Binnen vijf werkdagen krijgt u een kort rapport met vijf antwoorden, en een eerlijk advies of er iets moet gebeuren.
		</p>
	</div>
</section>

<section class="py-20">
	<div class="max-w-[1100px] mx-auto px-6 grid md:grid-cols-[2fr_1fr] gap-8">
		<form method="POST" action="{{ route('contact.store') }}" class="card space-y-5">
			@csrf
			<input type="hidden" name="topic" value="backup-check">
			<input type="hidden" name="subject" value="Aanvraag gratis backup-check">

			@if ($errors->any())
				<div class="rounded-lg border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">
					{{ $errors->first() }}
				</div>
			@endif

			<div class="grid sm:grid-cols-2 gap-4">
				<div>
					<label for="name" class="block text-sm font-semibold mb-2">Uw naam</label>
					<input id="name" name="name" type="text" required value="{{ old('name') }}" class="field-input">
				</div>
				<div>
					<label for="company" class="block text-sm font-semibold mb-2">Bedrijf</label>
					<input id="company" name="company" type="text" required value="{{ old('company') }}" class="field-input">
				</div>
			</div>

			<div class="grid sm:grid-cols-2 gap-4">
				<div>
					<label for="email" class="block text-sm font-semibold mb-2">E-mail</label>
					<input id="email" name="email" type="email" required value="{{ old('email') }}" class="field-input">
				</div>
				<div>
					<label for="phone" class="block text-sm font-semibold mb-2">Telefoon <span class="font-normal text-[color:var(--color-ink-muted)]">(optioneel)</span></label>
					<input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="field-input">
				</div>
			</div>

			<div>
				<label for="website" class="block text-sm font-semibold mb-2">Website of omgeving die het betreft</label>
				<input id="website" name="website" type="text" value="{{ old('website') }}" placeholder="bv. https://uwbedrijf.nl, of 'onze webshop en de boekhouding'" class="field-input">
			</div>

			<div>
				<label for="message" class="block text-sm font-semibold mb-2">Wat weet u nu over uw backup?</label>
				<p class="text-sm text-[color:var(--color-ink-muted)] mb-2">"Geen idee" is een prima antwoord. Helpt wel: wie het regelt (hostingpartij, eigen IT, niemand), hoe vaak u denkt dat er een backup is, en of er ooit iets is teruggezet.</p>
				<textarea id="message" name="message" rows="5" required class="field-input" style="min-height:140px;resize:vertical">{{ old('message') }}</textarea>
			</div>

			{{-- Lokvakje: zelfde naam als op /nl/contact, ContactController weigert stil als het gevuld is. --}}
			<div class="hp-field" aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden">
				<label for="bedrijfsnaam_2">Laat dit veld leeg</label>
				<input id="bedrijfsnaam_2" name="bedrijfsnaam_2" type="text" tabindex="-1" autocomplete="off" value="">
			</div>

			<div class="cf-turnstile" data-sitekey="{{ app(\App\Services\Security\Turnstile::class)->siteKey() }}" data-language="nl" data-theme="light"></div>
			<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

			<button type="submit" class="btn-accent w-full justify-center">
				Vraag de gratis backup-check aan
				<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
			<p class="text-xs text-[color:var(--color-ink-muted)]">Geen verplichting, geen abonnement. We gebruiken uw gegevens alleen voor deze check.</p>
		</form>

		<aside class="space-y-6">
			<div class="card">
				<h2 class="font-bold text-lg mb-3">Wat u krijgt</h2>
				<p class="text-sm text-[color:var(--color-ink-muted)] mb-4">Een kort rapport, binnen vijf werkdagen, met antwoord op vijf vragen:</p>
				<ol class="space-y-2 text-sm">
					<li class="flex gap-2"><span class="font-bold text-[color:var(--color-accent)]">1</span> Wát er wordt bewaard, en wat niet.</li>
					<li class="flex gap-2"><span class="font-bold text-[color:var(--color-accent)]">2</span> Hoe vaak, en hoe lang de kopieën bewaard blijven.</li>
					<li class="flex gap-2"><span class="font-bold text-[color:var(--color-accent)]">3</span> Wáár de kopie staat, en of die buiten uw eigen server ligt.</li>
					<li class="flex gap-2"><span class="font-bold text-[color:var(--color-accent)]">4</span> Of hij terug te zetten is — waar mogelijk proberen we één bestand.</li>
					<li class="flex gap-2"><span class="font-bold text-[color:var(--color-accent)]">5</span> Wie het merkt als de backup een nacht mislukt.</li>
				</ol>
			</div>
			<div class="card">
				<h2 class="font-bold text-lg mb-3">Hoe het gaat</h2>
				<ul class="space-y-2 text-sm text-[color:var(--color-ink-muted)]">
					<li>U vult het formulier in. Wij bellen of mailen met hooguit drie vragen.</li>
					<li>Waar we toegang voor nodig hebben, vragen we dat apart — alleen leesrechten.</li>
					<li>U krijgt het rapport per mail. Wilt u daarna dat wij het inrichten en bewaken, dan kan dat: <a href="/nl/backup-en-monitoring" class="underline">backup en monitoring</a>. Wilt u dat niet, dan houdt het hier op.</li>
				</ul>
			</div>
			<div class="card">
				<h2 class="font-bold text-lg mb-3">Liever direct praten?</h2>
				<p class="text-sm text-[color:var(--color-ink-muted)] mb-3">Plan een gesprek van een half uur; dan doen we de eerste vragen meteen.</p>
				<a href="/nl/afspraak?onderwerp=backup-check" class="btn-outline">Plan een gesprek</a>
			</div>
		</aside>
	</div>
</section>

@endsection
