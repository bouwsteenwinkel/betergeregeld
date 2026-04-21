@extends('layouts.app')

@php
	$locale = app()->getLocale();
	$isEn = $locale === 'en';
	$h1 = $isEn ? $service['h1_en'] : $service['h1_nl'];
	$lead = $isEn ? $service['lead_en'] : $service['lead_nl'];
	$pill = $isEn ? $service['pill_en'] : $service['pill_nl'];
	$bullets = $isEn ? $service['bullets_en'] : $service['bullets_nl'];
	$ctaTitle = $isEn ? $service['cta_title_en'] : $service['cta_title_nl'];
	$ctaText = $isEn ? $service['cta_text_en'] : $service['cta_text_nl'];
@endphp

@section('title', $h1 . ' — Beter Geregeld ICT')
@section('description', $lead)

@section('content')
<div class="bg-[#f5f7fb]">
	<div class="max-w-[1100px] mx-auto px-4 py-10 sm:py-14">

		<nav class="text-sm text-[color:var(--color-ink-muted)] mb-6">
			<a href="{{ route('home') }}" class="hover:text-[color:var(--color-ink)]">{{ __('Home') }}</a>
			<span class="mx-2">/</span>
			<a href="/{{ $locale }}/diensten" class="hover:text-[color:var(--color-ink)]">{{ $isEn ? 'Services' : 'Diensten' }}</a>
			<span class="mx-2">/</span>
			<span>{{ $h1 }}</span>
		</nav>

		<div class="inline-block mb-4">
			<span class="inline-flex items-center px-3 py-1 rounded-full border border-[color:var(--color-line)] bg-white text-xs font-bold text-[color:var(--color-ink-muted)]">{{ $pill }}</span>
		</div>

		<h1 class="text-4xl sm:text-5xl font-bold tracking-tight mb-5">{{ $h1 }}</h1>
		<p class="text-lg text-[color:var(--color-ink-muted)] leading-relaxed mb-8 max-w-3xl">{{ $lead }}</p>

		<div class="flex flex-wrap gap-3 mb-10">
			<a href="/{{ $locale }}/contact?topic={{ urlencode($slug) }}" class="rounded-[var(--radius-control)] bg-[color:var(--color-ink)] text-white font-semibold px-5 py-3 hover:opacity-90 transition">
				{{ $isEn ? 'Plan a call' : 'Plan een gesprek' }}
			</a>
			<a href="/{{ $locale }}/diensten" class="rounded-[var(--radius-control)] bg-white text-[color:var(--color-ink)] border border-[color:var(--color-line)] font-semibold px-5 py-3 hover:bg-gray-50 transition">
				{{ $isEn ? 'View all services' : 'Bekijk alle diensten' }}
			</a>
		</div>

		<section class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-8 shadow-[var(--shadow-soft)] mb-8">
			<h2 class="text-2xl font-bold mb-4">{{ $isEn ? 'What you get' : 'Wat je krijgt' }}</h2>
			<ul class="space-y-3">
				@foreach ($bullets as $b)
					<li class="flex items-start gap-3">
						<span class="shrink-0 w-6 h-6 rounded-full bg-[color:var(--color-ink)] text-white text-xs font-bold inline-flex items-center justify-center mt-0.5">✓</span>
						<span class="text-[color:var(--color-ink-muted)] leading-relaxed">{{ $b }}</span>
					</li>
				@endforeach
			</ul>
		</section>

		<section class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-8 shadow-[var(--shadow-soft)] mb-8">
			<h2 class="text-2xl font-bold mb-4">{{ $isEn ? 'How we work' : 'Onze aanpak' }}</h2>
			<ol class="grid md:grid-cols-3 gap-4">
				@foreach ([
					['nl' => '1. Korte intake', 'en' => '1. Short intake', 'd_nl' => 'Eerst inventariseren we kort wat er al staat en wat het doel is.', 'd_en' => 'First we briefly inventory what\'s there and what the goal is.'],
					['nl' => '2. Praktische aanpak', 'en' => '2. Practical approach', 'd_nl' => 'We komen met een concrete aanpak met scope, prioriteiten en vervolgstappen.', 'd_en' => 'We come with a concrete approach with scope, priorities and next steps.'],
					['nl' => '3. Uitvoering & uitleg', 'en' => '3. Execution & explanation', 'd_nl' => 'We voeren uit, leggen uit wat we doen en zorgen dat je grip houdt.', 'd_en' => 'We execute, explain what we do and make sure you stay in control.'],
				] as $st)
					<li class="bg-gray-50 rounded-[var(--radius-control)] p-5 border border-[color:var(--color-line)]">
						<h3 class="font-bold mb-2">{{ $isEn ? $st['en'] : $st['nl'] }}</h3>
						<p class="text-sm text-[color:var(--color-ink-muted)]">{{ $isEn ? $st['d_en'] : $st['d_nl'] }}</p>
					</li>
				@endforeach
			</ol>
		</section>

		<section class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-8 shadow-[var(--shadow-soft)]">
			<h2 class="text-2xl font-bold mb-3">{{ $ctaTitle }}</h2>
			<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-5">{{ $ctaText }}</p>
			<div class="flex flex-wrap gap-3">
				<a href="/{{ $locale }}/contact?topic={{ urlencode($slug) }}" class="rounded-[var(--radius-control)] bg-[color:var(--color-ink)] text-white font-semibold px-5 py-3 hover:opacity-90 transition">
					{{ $isEn ? 'Contact us' : 'Neem contact op' }}
				</a>
				<a href="/{{ $locale }}/diensten" class="rounded-[var(--radius-control)] bg-white text-[color:var(--color-ink)] border border-[color:var(--color-line)] font-semibold px-5 py-3 hover:bg-gray-50 transition">
					{{ $isEn ? 'View all services' : 'Bekijk alle diensten' }}
				</a>
			</div>
		</section>

	</div>
</div>
@endsection
