@extends('layouts.app')

@php
	$locale = app()->getLocale();
	$isEn = $locale !== 'nl';

	$topics = [
		'cookie-banner-instellen' => $isEn ? 'Cookie banner setup' : 'Cookie banner instellen',
		'mail-beveiliging-fix' => $isEn ? 'Email security fix' : 'Mail beveiliging fix',
		'toegang-check' => $isEn ? 'Access review' : 'Toegang check',
		'website-meertalig-maken' => $isEn ? 'Make website multilingual' : 'Website meertalig maken',
		'2fa-implementeren' => $isEn ? 'Implement 2FA' : '2FA implementeren',
		'seo-check' => 'SEO check',
		'website-snelheid-verbeteren' => $isEn ? 'Improve website speed' : 'Website snelheid verbeteren',
		'website-onderhoud-uitbesteden' => $isEn ? 'Outsource maintenance' : 'Website onderhoud',
		'website-beveiligen' => $isEn ? 'Secure website' : 'Website beveiligen',
		'website-backup-en-herstel' => $isEn ? 'Backup & recovery' : 'Back-up en herstel',
		'wordpress-opschonen' => $isEn ? 'Clean up WordPress' : 'WordPress opschonen',
		'website-migratie-zonder-gedoe' => $isEn ? 'Website migration' : 'Website migratie',
		'website-structuur-check' => $isEn ? 'Website structure check' : 'Structuur check',
		'iban-check' => 'IBAN check',
		'maatwerk' => $isEn ? 'Custom / advice' : 'Maatwerk / advies',
		'anders' => $isEn ? 'Other / not sure yet' : 'Anders / nog niet zeker',
	];
@endphp

@section('title', 'Contact — Beter Geregeld ICT')
@section('description', $isEn ? 'Get in touch about websites, portals, integrations, security or technical optimisation.' : 'Neem contact op over websites, portals, koppelingen, beveiliging of technische optimalisatie.')

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1100px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Contact</span>
		</nav>
		<span class="pill pill-dark mb-5">{{ $isEn ? 'Get in touch' : 'Neem contact op' }}</span>
		<h1 class="display-1 mb-5">
			{{ $isEn ? 'Let\'s build something' : 'Laten we iets bouwen dat' }} <span class="accent-word">{{ $isEn ? 'that works.' : 'werkt.' }}</span>
		</h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			{{ $isEn
				? 'Tell us briefly what you need. We\'ll get back to you personally — usually within one business day.'
				: 'Vertel kort wat je nodig hebt. We reageren persoonlijk, meestal binnen één werkdag.' }}
		</p>
	</div>
</section>

<section class="py-20">
	<div class="max-w-[1100px] mx-auto px-6 grid md:grid-cols-[2fr_1fr] gap-8">
		<form method="POST" action="{{ route('contact.store') }}" class="card space-y-5">
			@csrf

			<div class="grid sm:grid-cols-2 gap-5">
				<div>
					<label for="name" class="block text-sm font-semibold mb-2">{{ $isEn ? 'Name' : 'Naam' }}</label>
					<input id="name" name="name" type="text" required value="{{ old('name') }}" class="field-input">
				</div>
				<div>
					<label for="email" class="block text-sm font-semibold mb-2">{{ __('E-mail') }}</label>
					<input id="email" name="email" type="email" required value="{{ old('email') }}" class="field-input">
				</div>
			</div>

			<div>
				<label for="topic" class="block text-sm font-semibold mb-2">{{ $isEn ? 'Topic (optional)' : 'Onderwerp (optioneel)' }}</label>
				<select id="topic" name="topic" class="field-input">
					<option value="">{{ $isEn ? '— Choose a topic —' : '— Kies een onderwerp —' }}</option>
					@foreach ($topics as $key => $label)
						<option value="{{ $key }}" @selected(old('topic', $topic) === $key)>{{ $label }}</option>
					@endforeach
				</select>
			</div>

			<div>
				<label for="subject" class="block text-sm font-semibold mb-2">{{ $isEn ? 'Subject (optional)' : 'Onderwerp-titel (optioneel)' }}</label>
				<input id="subject" name="subject" type="text" value="{{ old('subject') }}"
					placeholder="{{ $isEn ? 'e.g. Need a quote for SEO check' : 'bv. Offerte voor SEO check' }}" class="field-input">
			</div>

			<div>
				<label for="message" class="block text-sm font-semibold mb-2">{{ $isEn ? 'Message' : 'Bericht' }}</label>
				<textarea id="message" name="message" rows="6" required
					placeholder="{{ $isEn ? 'What would you like to know or arrange?' : 'Waar wil je meer over weten?' }}"
					class="field-input" style="min-height:160px;resize:vertical">{{ old('message') }}</textarea>
			</div>

			<details class="text-sm">
				<summary class="cursor-pointer text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)] font-medium">
					{{ $isEn ? 'Additional details (optional)' : 'Extra details (optioneel)' }}
				</summary>
				<div class="mt-4 space-y-3">
					<input name="website" type="text" placeholder="{{ $isEn ? 'Website URL' : 'Website URL' }}" value="{{ old('website') }}" class="field-input">
					<input name="company" type="text" placeholder="{{ $isEn ? 'Company' : 'Bedrijf' }}" value="{{ old('company') }}" class="field-input">
					<input name="phone" type="tel" placeholder="{{ $isEn ? 'Phone' : 'Telefoon' }}" value="{{ old('phone') }}" class="field-input">
				</div>
			</details>

			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3 space-y-1">
					@foreach ($errors->all() as $err)
						<div>{{ $err }}</div>
					@endforeach
				</div>
			@endif

			<button type="submit" class="btn-accent w-full justify-center">
				{{ $isEn ? 'Send message' : 'Bericht versturen' }}
				<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
		</form>

		<aside class="space-y-4">
			<div class="card">
				<h2 class="font-bold mb-4">{{ $isEn ? 'Direct' : 'Direct' }}</h2>
				<ul class="space-y-3 text-sm">
					<li>
						<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-soft)] mb-1">{{ __('E-mail') }}</div>
						<a href="mailto:info@betergeregeld.com" class="font-semibold hover:text-[color:var(--color-accent-hover)]">info@betergeregeld.com</a>
					</li>
					<li>
						<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-soft)] mb-1">{{ $isEn ? 'Phone' : 'Telefoon' }}</div>
						<a href="tel:+31352011729" class="font-semibold hover:text-[color:var(--color-accent-hover)]">+31 35 201 1729</a>
					</li>
				</ul>
			</div>
			<div class="card">
				<h2 class="font-bold mb-3">{{ $isEn ? 'Address' : 'Adres' }}</h2>
				<address class="text-sm text-[color:var(--color-ink-muted)] not-italic leading-relaxed">
					Beter Geregeld ICT<br>
					T.B. Huurmanlaan 5<br>
					1403 SL Bussum<br>
					{{ $isEn ? 'Netherlands' : 'Nederland' }}
				</address>
			</div>
		</aside>
	</div>
</section>

@endsection
