@extends('layouts.app')

@section('title', __('Over ons') . ' | Beter Geregeld ICT')
@section('description', __('Lees wie we zijn, hoe we werken en waarom bedrijven kiezen voor Beter Geregeld ICT.'))

@php
	$locale = app()->getLocale();
	$isEn = $locale !== 'nl';
@endphp

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1100px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">{{ $isEn ? 'About us' : 'Over ons' }}</span>
		</nav>
		<span class="pill pill-dark mb-5">{{ $isEn ? 'Since 1989' : 'Sinds 1989' }}</span>
		<h1 class="display-1 mb-6 max-w-4xl">
			{{ $isEn ? 'Automation and IT that' : 'Automatisering en ICT die' }}
			<span class="accent-word">{{ $isEn ? 'simply works.' : 'gewoon klopt.' }}</span>
		</h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-3xl">
			{{ $isEn
				? 'Since 1989 we\'ve helped organisations with tooling and integrations that are secure, clear and reliable.'
				: 'Sinds 1989 helpen we organisaties met tooling en koppelingen die veilig, duidelijk en betrouwbaar zijn.' }}
		</p>
	</div>
</section>

<section class="py-20">
	<div class="max-w-[1100px] mx-auto px-6 grid md:grid-cols-2 gap-8">
		<div class="card">
			<span class="pill pill-teal mb-3">{{ $isEn ? 'Story' : 'Verhaal' }}</span>
			<h2 class="display-3 mb-4">{{ $isEn ? 'Our background' : 'Onze achtergrond' }}</h2>
			<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-3">
				{{ $isEn
					? 'We\'ve been active in automation since 1989 under several names: VOS electronics, VOS automatisering, ICM results, ICM Groep and ICM Performance.'
					: 'We zijn actief in automatisering sinds 1989 onder verschillende namen: VOS electronics, VOS automatisering, ICM results, ICM Groep en ICM Performance.' }}
			</p>
			<p class="text-[color:var(--color-ink-muted)] leading-relaxed">
				{{ $isEn
					? 'Those decades translate into how we work today: pragmatic, short lines, focus on what\'s truly needed.'
					: 'Die decennia vertalen zich in hoe we vandaag werken: pragmatisch, korte lijnen en focus op wat er écht nodig is.' }}
			</p>
		</div>
		<div class="card">
			<span class="pill pill-teal mb-3">{{ $isEn ? 'Mission' : 'Missie' }}</span>
			<h2 class="display-3 mb-4">{{ $isEn ? 'IT you actually understand' : 'ICT die je wél begrijpt' }}</h2>
			<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-4">
				{{ $isEn
					? 'Make IT understandable and reliable so you can focus on your business while we ensure stability, security and continuity.'
					: 'ICT begrijpelijk en betrouwbaar maken, zodat jij kunt ondernemen terwijl wij zorgen voor stabiliteit, veiligheid en continuïteit.' }}
			</p>
			<ul class="space-y-2 text-sm text-[color:var(--color-ink-muted)]">
				<li class="flex items-start gap-2"><span class="text-[color:var(--color-accent)] mt-1">●</span>{{ $isEn ? 'Clear agreements, predictable results.' : 'Duidelijke afspraken, voorspelbare resultaten.' }}</li>
				<li class="flex items-start gap-2"><span class="text-[color:var(--color-accent)] mt-1">●</span>{{ $isEn ? 'Security built into the foundation.' : 'Beveiliging standaard in de basis.' }}</li>
				<li class="flex items-start gap-2"><span class="text-[color:var(--color-accent)] mt-1">●</span>{{ $isEn ? 'Smart automation where it truly saves time.' : 'Slim automatiseren waar het tijd scheelt.' }}</li>
			</ul>
		</div>
	</div>
</section>

<section class="bg-[color:var(--color-surface)] py-20">
	<div class="max-w-[1100px] mx-auto px-6">
		<span class="pill pill-ink mb-3">{{ $isEn ? 'Timeline' : 'Tijdlijn' }}</span>
		<h2 class="display-2 mb-10">{{ $isEn ? 'From 1989 to today.' : 'Van 1989 tot nu.' }}</h2>
		<div class="space-y-5">
			@foreach ([
				['y' => '1989',   't_nl' => 'Start met automatisering en techniek.', 't_en' => 'Started in automation and technology.'],
				['y' => '1990s',  't_nl' => 'Doorontwikkeling onder VOS electronics en VOS automatisering.', 't_en' => 'Evolved under VOS electronics and VOS automatisering.'],
				['y' => '2000s',  't_nl' => 'ICM results, ICM Groep en ICM Performance.', 't_en' => 'ICM results, ICM Groep and ICM Performance.'],
				['y' => '2026',   't_nl' => 'Beter Geregeld ICT: moderne tooling, directe support, focus op veiligheid en stabiliteit.', 't_en' => 'Beter Geregeld ICT: modern tooling, direct support, focus on security and stability.'],
			] as $ev)
				<div class="card flex items-start gap-6">
					<div class="shrink-0 w-20 text-xl font-black text-[color:var(--color-accent)]">{{ $ev['y'] }}</div>
					<p class="text-[color:var(--color-ink-muted)] leading-relaxed pt-1">{{ $isEn ? $ev['t_en'] : $ev['t_nl'] }}</p>
				</div>
			@endforeach
		</div>
	</div>
</section>

<section class="py-20">
	<div class="max-w-[1100px] mx-auto px-6 text-center">
		<h2 class="display-2 mb-5">{{ $isEn ? 'Want to talk?' : 'Kennismaken?' }}</h2>
		<p class="text-lg text-[color:var(--color-ink-muted)] leading-relaxed mb-8 max-w-2xl mx-auto">
			{{ $isEn
				? 'Tell us what you need. We\'ll map the safest and smartest route together.'
				: 'Vertel wat je nodig hebt. We kijken samen wat de slimste en veiligste route is.' }}
		</p>
		<div class="flex flex-wrap gap-3 justify-center">
			<a href="/{{ $locale }}/contact" class="btn-accent">
				{{ $isEn ? 'Contact us' : 'Neem contact op' }}
				<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</a>
			<a href="/{{ $locale }}/diensten" class="btn-outline">{{ $isEn ? 'View services' : 'Bekijk diensten' }}</a>
		</div>
	</div>
</section>

@endsection
