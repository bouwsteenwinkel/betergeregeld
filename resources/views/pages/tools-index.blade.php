@extends('layouts.app')

@section('title', __('Tools') . ' — ' . config('app.name'))
@section('description', __('Overzicht van alle Beter Geregeld-tools: IBAN, BTW, postcode, IP, PDF, JSON, diff, favicon, speedtest.'))

@php
	$locale = app()->getLocale();
	$isEn = $locale === 'en';

	$icons = [
		'credit-card' => '<path d="M3 8h18M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z M6 14h4"/>',
		'check-badge' => '<path d="M12 2 4 5v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V5l-8-3Z M9 12l2 2 4-4"/>',
		'map-pin' => '<path d="M12 22s-7-7.5-7-13a7 7 0 1 1 14 0c0 5.5-7 13-7 13Z M12 11a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>',
		'braces' => '<path d="M7 4H6a2 2 0 0 0-2 2v3a2 2 0 0 1-2 2h0a2 2 0 0 1 2 2v3a2 2 0 0 0 2 2h1 M17 4h1a2 2 0 0 1 2 2v3a2 2 0 0 0 2 2h0a2 2 0 0 0-2 2v3a2 2 0 0 1-2 2h-1"/>',
		'compare' => '<path d="M8 3v18M16 3v18 M3 6l5 5-5 5 M21 6l-5 5 5 5"/>',
		'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 4 3 14 0 18M12 3c-3 4-3 14 0 18"/>',
		'gauge' => '<path d="M5 18a8 8 0 1 1 14 0 M12 14l4-4"/>',
		'files' => '<path d="M8 4h8l4 4v12a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z M4 8v12a2 2 0 0 0 2 2h10"/>',
		'image' => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 15-5-5-8 8"/>',
	];
@endphp

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-60"></div>
	<div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full bg-[color:var(--color-accent)] opacity-10 blur-3xl"></div>

	<div class="relative max-w-[1200px] mx-auto px-6 py-20 text-center">
		<span class="pill pill-dark mb-6">
			<span class="w-1.5 h-1.5 rounded-full bg-[color:var(--color-accent)]"></span>
			{{ __('Freemium tools') }}
		</span>
		<h1 class="display-1 mb-5">
			{{ __('Praktische') }} <span class="accent-word">{{ __('tools') }}</span><br>
			{{ __('voor elke werkdag') }}
		</h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl mx-auto">
			{{ __('Direct bruikbaar zonder account. Log in voor hogere limieten en geschiedenis, upgrade voor bulk en team-features.') }}
		</p>
	</div>
</section>

@foreach ($sections as $i => $section)
	<section class="py-14 {{ $i % 2 === 1 ? 'bg-[color:var(--color-surface-soft,#fafafa)]' : '' }}">
		<div class="max-w-[1200px] mx-auto px-6">
			<div class="mb-8">
				<h2 class="text-2xl font-bold mb-1">{{ $section[$isEn ? 'title_en' : 'title_nl'] }}</h2>
				<p class="text-sm text-[color:var(--color-ink-muted)]">{{ $section[$isEn ? 'subtitle_en' : 'subtitle_nl'] }}</p>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
				@foreach ($section['tools'] as $tool)
					<a href="{{ route($tool['route'], ['locale' => $locale]) }}"
						class="card card-accent group flex flex-col">
						<div class="flex items-start justify-between mb-3">
							<div class="shrink-0 w-11 h-11 rounded-[var(--radius-control)] bg-[color:var(--color-accent)]/10 text-[color:var(--color-accent)] inline-flex items-center justify-center">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
									{!! $icons[$tool['icon']] ?? $icons['files'] !!}
								</svg>
							</div>
							<span class="pill pill-ink text-[10px]">{{ $tool[$isEn ? 'badge_en' : 'badge_nl'] }}</span>
						</div>

						<h3 class="text-lg font-semibold mb-1.5 group-hover:text-[color:var(--color-accent)] transition">
							{{ $tool[$isEn ? 'title_en' : 'title_nl'] }}
						</h3>
						<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed flex-1">
							{{ $tool[$isEn ? 'desc_en' : 'desc_nl'] }}
						</p>

						<span class="inline-flex items-center gap-1.5 text-sm font-medium text-[color:var(--color-accent)] mt-4">
							{{ __('Openen') }}
							<svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</span>
					</a>
				@endforeach
			</div>
		</div>
	</section>
@endforeach

<section class="section-dark py-16">
	<div class="max-w-[800px] mx-auto px-6 text-center">
		<h2 class="text-3xl font-bold mb-4">
			{{ __('Meer nodig dan de gratis versie?') }}
		</h2>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] mb-6">
			{{ __('Pro en Business unlocken bulk, API-toegang, onbeperkte checks en team-features.') }}
		</p>
		<a href="{{ route('pricing', ['locale' => $locale]) }}" class="btn-accent">
			{{ __('Bekijk plannen') }}
			<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</a>
	</div>
</section>

@endsection
