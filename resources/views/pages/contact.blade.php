@extends('layouts.app')

@php
	$locale = app()->getLocale();
	$isEn = $locale === 'en';

	$topics = [
		'cookie-banner-instellen' => $isEn ? 'Cookie banner setup' : 'Cookie banner instellen',
		'mail-beveiliging-fix' => $isEn ? 'Email security fix' : 'Mail beveiliging fix',
		'toegang-check' => $isEn ? 'Access review' : 'Toegang check',
		'website-meertalig-maken' => $isEn ? 'Make website multilingual' : 'Website meertalig maken',
		'2fa-implementeren' => $isEn ? 'Implement 2FA' : '2FA implementeren',
		'seo-check' => $isEn ? 'SEO check' : 'SEO check',
		'website-snelheid-verbeteren' => $isEn ? 'Improve website speed' : 'Website snelheid verbeteren',
		'website-onderhoud-uitbesteden' => $isEn ? 'Outsource maintenance' : 'Website onderhoud',
		'website-beveiligen' => $isEn ? 'Secure website' : 'Website beveiligen',
		'website-backup-en-herstel' => $isEn ? 'Backup & recovery' : 'Back-up en herstel',
		'wordpress-opschonen' => $isEn ? 'Clean up WordPress' : 'WordPress opschonen',
		'website-migratie-zonder-gedoe' => $isEn ? 'Website migration' : 'Website migratie',
		'website-structuur-check' => $isEn ? 'Website structure check' : 'Structuur check',
		'iban-check' => $isEn ? 'IBAN check tool' : 'IBAN check tool',
		'maatwerk' => $isEn ? 'Custom / advice' : 'Maatwerk / advies',
		'anders' => $isEn ? 'Other / not sure yet' : 'Anders / nog niet zeker',
	];
@endphp

@section('title', 'Contact — Beter Geregeld ICT')
@section('description', $isEn ? 'Get in touch about websites, portals, integrations, security or technical optimisation.' : 'Neem contact op over websites, portals, koppelingen, beveiliging of technische optimalisatie.')

@section('content')
<div class="bg-[#f5f7fb]">
	<div class="max-w-[900px] mx-auto px-4 py-10 sm:py-14">

		<nav class="text-sm text-[color:var(--color-ink-muted)] mb-6">
			<a href="{{ route('home') }}" class="hover:text-[color:var(--color-ink)]">{{ __('Home') }}</a>
			<span class="mx-2">/</span>
			<span>Contact</span>
		</nav>

		<h1 class="text-4xl sm:text-5xl font-bold tracking-tight mb-4">{{ $isEn ? 'Contact' : 'Contact' }}</h1>
		<p class="text-lg text-[color:var(--color-ink-muted)] leading-relaxed mb-10">
			{{ $isEn
				? 'Tell us briefly what you need. We\'ll get back to you personally, usually within one business day.'
				: 'Vertel kort wat je nodig hebt. We reageren persoonlijk, meestal binnen één werkdag.' }}
		</p>

		<div class="grid md:grid-cols-[1fr_280px] gap-8">
			<form method="POST" action="{{ route('contact.store') }}"
				class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-6 shadow-[var(--shadow-soft)] space-y-4">
				@csrf

				<div class="grid sm:grid-cols-2 gap-4">
					<div>
						<label for="name" class="block text-sm font-semibold mb-1.5">{{ $isEn ? 'Name' : 'Naam' }}</label>
						<input id="name" name="name" type="text" required value="{{ old('name') }}"
							class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3 focus:outline-none focus:ring-2 focus:ring-black/10">
					</div>
					<div>
						<label for="email" class="block text-sm font-semibold mb-1.5">{{ __('E-mail') }}</label>
						<input id="email" name="email" type="email" required value="{{ old('email') }}"
							class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3 focus:outline-none focus:ring-2 focus:ring-black/10">
					</div>
				</div>

				<div>
					<label for="topic" class="block text-sm font-semibold mb-1.5">{{ $isEn ? 'Topic (optional)' : 'Onderwerp (optioneel)' }}</label>
					<select id="topic" name="topic"
						class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3 focus:outline-none focus:ring-2 focus:ring-black/10">
						<option value="">{{ $isEn ? '— Choose a topic —' : '— Kies een onderwerp —' }}</option>
						@foreach ($topics as $key => $label)
							<option value="{{ $key }}" @selected(old('topic', $topic) === $key)>{{ $label }}</option>
						@endforeach
					</select>
				</div>

				<div>
					<label for="subject" class="block text-sm font-semibold mb-1.5">{{ $isEn ? 'Subject (optional)' : 'Onderwerp-titel (optioneel)' }}</label>
					<input id="subject" name="subject" type="text" value="{{ old('subject') }}"
						placeholder="{{ $isEn ? 'e.g. Need a quote for SEO check' : 'bv. Offerte voor SEO check' }}"
						class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3 focus:outline-none focus:ring-2 focus:ring-black/10">
				</div>

				<div>
					<label for="message" class="block text-sm font-semibold mb-1.5">{{ $isEn ? 'Message' : 'Bericht' }}</label>
					<textarea id="message" name="message" rows="6" required
						placeholder="{{ $isEn ? 'What would you like to know or arrange?' : 'Waar wil je meer over weten?' }}"
						class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3 focus:outline-none focus:ring-2 focus:ring-black/10">{{ old('message') }}</textarea>
				</div>

				<details class="text-sm">
					<summary class="cursor-pointer text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">
						{{ $isEn ? 'Additional details (optional)' : 'Extra details (optioneel)' }}
					</summary>
					<div class="mt-3 space-y-3">
						<input name="website" type="text" placeholder="{{ $isEn ? 'Website URL' : 'Website URL' }}" value="{{ old('website') }}"
							class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3">
						<input name="company" type="text" placeholder="{{ $isEn ? 'Company' : 'Bedrijf' }}" value="{{ old('company') }}"
							class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3">
						<input name="phone" type="tel" placeholder="{{ $isEn ? 'Phone' : 'Telefoon' }}" value="{{ old('phone') }}"
							class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3">
					</div>
				</details>

				@if ($errors->any())
					<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3 space-y-1">
						@foreach ($errors->all() as $err)
							<div>{{ $err }}</div>
						@endforeach
					</div>
				@endif

				<button type="submit"
					class="w-full rounded-[var(--radius-control)] bg-[color:var(--color-ink)] text-white font-semibold px-5 py-3 hover:opacity-90 transition">
					{{ $isEn ? 'Send message' : 'Versturen' }}
				</button>
			</form>

			<aside class="space-y-4">
				<div class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-5 shadow-[var(--shadow-soft)]">
					<h2 class="font-bold mb-3 text-sm">{{ $isEn ? 'Direct' : 'Direct' }}</h2>
					<ul class="text-sm text-[color:var(--color-ink-muted)] space-y-2">
						<li><a href="mailto:info@betergeregeld.com" class="hover:text-[color:var(--color-ink)]">info@betergeregeld.com</a></li>
						<li><a href="tel:+31352011729" class="hover:text-[color:var(--color-ink)]">+31 35 201 1729</a></li>
					</ul>
				</div>
				<div class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-5 shadow-[var(--shadow-soft)]">
					<h2 class="font-bold mb-3 text-sm">{{ $isEn ? 'Address' : 'Adres' }}</h2>
					<address class="text-sm text-[color:var(--color-ink-muted)] not-italic leading-relaxed">
						Beter Geregeld ICT<br>
						T.B. Huurmanlaan 5<br>
						1403 SL Bussum<br>
						{{ $isEn ? 'Netherlands' : 'Nederland' }}
					</address>
				</div>
			</aside>
		</div>

	</div>
</div>
@endsection
