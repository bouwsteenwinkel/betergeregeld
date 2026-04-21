@extends('layouts.app')

@php
	$locale = app()->getLocale();
	$isEn = $locale === 'en';
@endphp

@section('title', ($isEn ? 'All services' : 'Alle diensten') . ' — Beter Geregeld ICT')
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
