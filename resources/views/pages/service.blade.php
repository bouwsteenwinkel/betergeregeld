@extends('layouts.app')

@php
	$locale = app()->getLocale();
	$isEn = $locale !== 'nl';

	// Locale-aware lookup met 3-laagse fallback:
	//   1. services_catalog_extra.{slug}.{locale}.{field}  (DE/FR/ES via Claude)
	//   2. services_catalog.{slug}.{field}_en              (Engelse bron-variant)
	//   3. services_catalog.{slug}.{field}_nl              (NL bron als laatste)
	$tx = function (string $field) use ($service, $slug, $locale) {
		$extra = config("services_catalog_extra.{$slug}.{$locale}");
		if (is_array($extra) && array_key_exists($field, $extra)) {
			return $extra[$field];
		}
		if ($locale !== 'nl' && isset($service[$field . '_en'])) {
			return $service[$field . '_en'];
		}
		return $service[$field . '_nl'] ?? '';
	};

	$h1       = $tx('h1');
	$lead     = $tx('lead');
	$pill     = $tx('pill');
	$bullets  = $tx('bullets');
	$ctaTitle = $tx('cta_title');
	$ctaText  = $tx('cta_text');
@endphp

@section('title', $h1 . ', Beter Geregeld ICT')
@section('description', $lead)

@push('head')
	<script type="application/ld+json">
	{!! json_encode([
		"\x40context"    => 'https://schema.org',
		"\x40type"       => 'Service',
		'name'        => $h1,
		'description' => $lead,
		'serviceType' => $pill,
		'provider'    => [
			"\x40type" => 'Organization',
			"\x40id"   => url('/#organization'),
			'name'  => 'Beter Geregeld ICT',
			'url'   => url('/'),
		],
		'areaServed'  => [
			"\x40type" => 'Country',
			'name'  => 'Netherlands',
		],
		'inLanguage'  => $locale,
		'url'         => url(request()->path()),
	], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
	</script>
	<script type="application/ld+json">
	{!! json_encode([
		"\x40context"        => 'https://schema.org',
		"\x40type"           => 'BreadcrumbList',
		'itemListElement' => [
			["\x40type" => 'ListItem', 'position' => 1, 'name' => $isEn ? 'Home' : 'Home',         'item' => url("/{$locale}")],
			["\x40type" => 'ListItem', 'position' => 2, 'name' => $isEn ? 'Services' : 'Diensten', 'item' => url("/{$locale}/diensten")],
			["\x40type" => 'ListItem', 'position' => 3, 'name' => $h1,                              'item' => url(request()->path())],
		],
	], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
	</script>
@endpush

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full bg-[color:var(--color-accent)] opacity-10 blur-3xl"></div>

	<div class="relative max-w-[1100px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-8 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<a href="/{{ $locale }}/diensten" class="hover:text-white">{{ $isEn ? 'Services' : 'Diensten' }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">{{ $h1 }}</span>
		</nav>

		<span class="pill pill-dark mb-5">{{ $service['badge'] }} · {{ $pill }}</span>
		<h1 class="display-1 mb-6 max-w-4xl">{{ $h1 }}</h1>
		<p class="text-lg sm:text-xl text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-3xl mb-8">{{ $lead }}</p>

		<div class="flex flex-wrap gap-3">
			<a href="/{{ $locale }}/contact?topic={{ urlencode($slug) }}" class="btn-accent">
				{{ $isEn ? 'Plan a call' : 'Plan een gesprek' }}
				<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</a>
			<a href="/{{ $locale }}/diensten" class="btn-ghost-light">{{ $isEn ? 'All services' : 'Alle diensten' }}</a>
		</div>
	</div>
</section>

<section class="py-20">
	<div class="max-w-[1100px] mx-auto px-6 grid lg:grid-cols-[2fr_1fr] gap-12">
		<div>
			<span class="pill pill-teal mb-3">{{ $isEn ? 'What you get' : 'Wat je krijgt' }}</span>
			<h2 class="display-2 mb-8">{{ $isEn ? 'Concrete outcomes, clearly scoped.' : 'Concrete resultaten, duidelijk afgebakend.' }}</h2>
			<ul class="space-y-4">
				@foreach ($bullets as $b)
					<li class="flex items-start gap-4">
						<span class="shrink-0 w-8 h-8 rounded-full bg-[color:var(--color-accent-soft)] text-[color:var(--color-accent-hover)] inline-flex items-center justify-center mt-0.5">
							<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 6l3 3 5-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</span>
						<span class="text-[color:var(--color-ink)] leading-relaxed pt-0.5">{{ $b }}</span>
					</li>
				@endforeach
			</ul>
		</div>

		<aside class="lg:pt-12">
			<div class="card">
				<h3 class="font-bold mb-3">{{ $ctaTitle }}</h3>
				<p class="text-sm text-[color:var(--color-ink-muted)] mb-5 leading-relaxed">{{ $ctaText }}</p>
				<a href="/{{ $locale }}/contact?topic={{ urlencode($slug) }}" class="btn-accent w-full justify-center">
					{{ $isEn ? 'Contact us' : 'Neem contact op' }}
				</a>
				<div class="mt-5 pt-5 border-t border-[color:var(--color-line)] text-xs text-[color:var(--color-ink-soft)] space-y-1">
					<div><a href="mailto:info@betergeregeld.com" class="hover:text-[color:var(--color-ink)]">info@betergeregeld.com</a></div>
					<div><a href="tel:+31882545101" class="hover:text-[color:var(--color-ink)]">088-2545101</a></div>
				</div>
			</div>
		</aside>
	</div>
</section>

<section class="bg-[color:var(--color-surface)] py-20">
	<div class="max-w-[1100px] mx-auto px-6">
		<span class="pill pill-ink mb-3">{{ $isEn ? 'How we work' : 'Onze aanpak' }}</span>
		<h2 class="display-2 mb-12">{{ $isEn ? 'Three steps.' : 'In drie stappen.' }}</h2>
		<div class="grid md:grid-cols-3 gap-5">
			@foreach ([
				['n' => '01', 't_nl' => 'Korte intake',          't_en' => 'Short intake',        'd_nl' => 'We inventariseren kort wat er al staat en wat het doel is.', 'd_en' => 'We briefly inventory what\'s there and what the goal is.'],
				['n' => '02', 't_nl' => 'Praktische aanpak',     't_en' => 'Practical approach',   'd_nl' => 'Concrete aanpak met scope, prioriteiten en vervolgstappen.', 'd_en' => 'Concrete approach with scope, priorities and next steps.'],
				['n' => '03', 't_nl' => 'Uitvoering & uitleg',   't_en' => 'Execution & explanation','d_nl' => 'We voeren uit, leggen uit en zorgen dat je grip houdt.',      'd_en' => 'We execute, explain and keep you in control.'],
			] as $st)
				<div class="card">
					<div class="text-4xl font-black text-[color:var(--color-accent)] mb-3 leading-none">{{ $st['n'] }}</div>
					<h3 class="font-bold mb-2">{{ $isEn ? $st['t_en'] : $st['t_nl'] }}</h3>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $isEn ? $st['d_en'] : $st['d_nl'] }}</p>
				</div>
			@endforeach
		</div>
	</div>
</section>

{{-- Verwante artikelen, internal-linking + visitor-retention. Alleen
	 zichtbaar als ServiceController posts vond via category/tag-match
	 (config/service_blog_links.php). --}}
@if (!empty($relatedPosts) && $relatedPosts->isNotEmpty())
	<section class="py-16 bg-[color:var(--color-surface)]">
		<div class="max-w-[1100px] mx-auto px-6">
			<h2 class="display-3 mb-2">{{ $isEn ? 'Related articles' : 'Verwante artikelen' }}</h2>
			<p class="text-[color:var(--color-ink-muted)] mb-8 max-w-2xl">
				{{ $isEn ? 'Practical reads that fit this service.' : 'Praktische artikelen die bij deze dienst aansluiten.' }}
			</p>
			<div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5">
				@foreach ($relatedPosts as $rp)
					<a href="/{{ $locale }}/blog/{{ $rp->slug }}"
						class="block bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-5 hover:shadow-[var(--shadow-soft)] transition">
						<div class="text-[11px] uppercase tracking-wider font-semibold text-[color:var(--color-accent)] mb-2">
							{{ $rp->category->name ?? '' }}
						</div>
						<h3 class="font-bold mb-2 leading-snug">{{ $rp->title }}</h3>
						<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">
							{{ \Illuminate\Support\Str::limit($rp->excerpt, 140) }}
						</p>
						<div class="mt-3 text-xs text-[color:var(--color-ink-muted)]">
							{{ $rp->reading_time_min }} min{{ $rp->is_pillar ? ' · ★ Pillar' : '' }}
						</div>
					</a>
				@endforeach
			</div>
		</div>
	</section>
@endif

@endsection
