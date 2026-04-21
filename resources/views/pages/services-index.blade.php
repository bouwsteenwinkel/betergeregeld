@extends('layouts.app')

@php
	$locale = app()->getLocale();
	$isEn = $locale === 'en';
@endphp

@section('title', ($isEn ? 'All services' : 'Alle diensten') . ' — Beter Geregeld ICT')
@section('description', $isEn ? 'Overview of all services: custom websites, portals, integrations, security, performance and optimisation.' : 'Overzicht van alle diensten: maatwerk websites, portals, koppelingen, beveiliging, performance en optimalisatie.')

@section('content')
<div class="bg-[#f5f7fb]">
	<div class="max-w-[1400px] mx-auto px-4 py-10 sm:py-14">

		<nav class="text-sm text-[color:var(--color-ink-muted)] mb-6">
			<a href="{{ route('home') }}" class="hover:text-[color:var(--color-ink)]">{{ __('Home') }}</a>
			<span class="mx-2">/</span>
			<span>{{ $isEn ? 'Services' : 'Diensten' }}</span>
		</nav>

		<h1 class="text-4xl sm:text-5xl font-bold tracking-tight mb-4">{{ $isEn ? 'All services' : 'Alle diensten' }}</h1>
		<p class="text-lg text-[color:var(--color-ink-muted)] leading-relaxed mb-10 max-w-3xl">
			{{ $isEn
				? 'Practical services for websites, portals, integrations, security, performance and optimisation.'
				: 'Praktische diensten voor websites, portals, koppelingen, beveiliging, performance en optimalisatie.' }}
		</p>

		<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
			@foreach ($services as $slug => $s)
				<a href="/{{ $locale }}/diensten/{{ $slug }}"
					class="block bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-5 shadow-[var(--shadow-soft)] hover:-translate-y-0.5 hover:shadow-lg transition">
					<div class="flex items-center justify-between mb-3">
						<span class="inline-flex items-center px-2.5 py-1 rounded-full border border-[color:var(--color-line)] bg-gray-50 text-xs font-bold text-[color:var(--color-ink-muted)]">{{ $s['badge'] }}</span>
						<span class="text-[color:var(--color-ink-muted)] text-xl">→</span>
					</div>
					<h3 class="text-lg font-bold mb-2 leading-tight">{{ $isEn ? $s['h1_en'] : $s['h1_nl'] }}</h3>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed line-clamp-3">{{ $isEn ? $s['lead_en'] : $s['lead_nl'] }}</p>
				</a>
			@endforeach
		</div>

	</div>
</div>
@endsection
