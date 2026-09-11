@extends('layouts.app')

@php
	$locale = app()->getLocale();
	$isEn = $locale !== 'nl';
@endphp

@section('title', ($isEn ? 'All services' : 'Alle diensten') . ', Beter Geregeld ICT')
@section('description', $isEn ? 'Overview of all services: custom websites, portals, integrations, security, performance and optimisation.' : 'Overzicht van alle diensten: maatwerk websites, portals, koppelingen, beveiliging, performance en optimalisatie.')

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1400px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">{{ $isEn ? 'Services' : 'Diensten' }}</span>
		</nav>
		<span class="pill pill-dark mb-5">{{ $isEn ? 'Service catalog' : 'Dienstcatalogus' }}</span>
		<h1 class="display-1 mb-5">{{ $isEn ? 'Thirteen practical services.' : 'Dertien praktische diensten.' }}</h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			{{ $isEn
				? 'Practical help for websites, portals, integrations, security, performance and optimisation.'
				: 'Praktische hulp voor websites, portals, koppelingen, beveiliging, performance en optimalisatie.' }}
		</p>
	</div>
</section>

<section class="py-20">
	<div class="max-w-[1400px] mx-auto px-6">
		{{-- Uitgelicht boven de catalogus: de AI-dienst heeft een eigen pagina omdat
		     hij niet in het sjabloon van een catalogusdienst past. --}}
		{{-- Achtergrond inline: .card zet zelf een witte achtergrond en wint van een
		     losse bg-utility, waardoor het uitgelichte blok niet opviel. --}}
		<a href="/{{ $locale }}/slimmer-werken-met-ai" class="card card-accent group block mb-8" style="background: var(--color-accent-soft)">
			<div class="flex flex-wrap items-center justify-between gap-6">
				<div class="max-w-2xl">
					<span class="pill pill-ink text-[10px] mb-3">{{ $isEn ? 'Featured' : 'Uitgelicht' }} · {{ $isEn ? 'AI & automation' : 'AI & automatisering' }}</span>
					<h2 class="display-3 mb-2">{{ $isEn ? 'Working smarter with AI' : 'Slimmer werken met AI' }}</h2>
					<p class="text-[color:var(--color-ink-muted)] leading-relaxed">
						{{ $isEn
							? 'Less manual work, more overview, more capacity. We map your process and show where automation and AI genuinely save time.'
							: 'Minder handwerk, meer overzicht, meer capaciteit. Wij brengen uw proces in kaart en laten zien waar automatisering en AI daadwerkelijk tijd besparen.' }}
					</p>
				</div>
				<span class="btn-accent shrink-0">
					{{ $isEn ? 'Read more' : 'Lees meer' }}
					<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</span>
			</div>
		</a>

		{{-- Kernaanbod (11-09-2026): eigen pagina's, alleen Nederlands, dus altijd /nl. --}}
		<h2 class="font-bold text-lg mb-4">{{ $isEn ? 'What we build' : 'Wat we bouwen' }}</h2>
		<div class="grid md:grid-cols-2 lg:grid-cols-5 gap-5 mb-14">
			@foreach ([
				['/nl/ai-telefoniste',           $isEn ? 'AI receptionist' : 'AI Telefoniste',                 $isEn ? 'Answers the phone, answers questions, transfers and reports.' : 'Neemt op, beantwoordt vragen, verbindt door en stuurt een verslag.'],
				['/nl/maatwerk-webapplicatie',   $isEn ? 'Custom web application' : 'Maatwerk webapplicatie',  $isEn ? 'Software built around how you work.' : 'Software gebouwd rond hoe u werkt.'],
				['/nl/klantportaal-laten-maken', $isEn ? 'Client portal' : 'Klantportaal',                     $isEn ? 'Clients see status, documents and invoices themselves.' : 'Klanten zien zelf status, documenten en facturen.'],
				['/nl/api-koppelingen',          $isEn ? 'API integrations' : 'API-koppelingen',                $isEn ? 'Enter data once, correct everywhere.' : 'Gegevens één keer invoeren, overal kloppen.'],
				['/nl/processen-automatiseren',  $isEn ? 'Process automation' : 'Processen automatiseren',      $isEn ? 'Reminders, follow-up and checks that run by themselves.' : 'Herinneringen, opvolging en controles die vanzelf lopen.'],
			] as [$href, $titel, $sub])
				<a href="{{ $href }}" class="card card-accent group block">
					<h3 class="font-bold text-lg mb-2 leading-tight">{{ $titel }}</h3>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $sub }}</p>
				</a>
			@endforeach
		</div>

		<h2 class="font-bold text-lg mb-4">{{ $isEn ? 'Practical services' : 'Praktische diensten' }}</h2>
		<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
			@foreach ($services as $slug => $s)
				<a href="/{{ $locale }}/diensten/{{ $slug }}" class="card card-accent group block">
					<div class="flex items-center justify-between mb-4">
						<span class="pill pill-ink text-[10px]">{{ $s['badge'] }}</span>
						<svg class="w-4 h-4 text-[color:var(--color-ink-soft)] group-hover:text-[color:var(--color-accent)] group-hover:translate-x-0.5 transition" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</div>
					<h3 class="font-bold text-lg mb-2 leading-tight">{{ $isEn ? $s['h1_en'] : $s['h1_nl'] }}</h3>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed line-clamp-3">{{ $isEn ? $s['lead_en'] : $s['lead_nl'] }}</p>
				</a>
			@endforeach
		</div>
	</div>
</section>

@endsection
