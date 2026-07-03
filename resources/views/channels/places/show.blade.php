@php
	/** @var \App\Support\ChannelSite $site */
	$c        = (array) ($content ?? []);
	$business = (array) ($business ?? []);
	$biz      = (array) ($businesses ?? []);
	$nearby   = (array) ($nearby ?? []);
	$facets   = (array) config('groeidiamant.facets', []);
	$faq      = (array) ($c['faq'] ?? []);

	$host = function ($url) {
		$h = parse_url((string) $url, PHP_URL_HOST);
		return $h ? preg_replace('/^www\./', '', $h) : '';
	};

	// JSON-LD: Service (areaServed = plaats) + LocalBusiness-provider + breadcrumb + FAQ.
	$canonical = $site->url('plaatsen/' . $placeSlug);
	$ld = [
		['@context' => 'https://schema.org', '@type' => 'Service',
			'name' => $c['h1'] ?? ('Website voor je bedrijf in ' . $placeName),
			'description' => $c['meta_description'] ?? '',
			'areaServed' => ['@type' => 'City', 'name' => $placeName,
				'containedInPlace' => ['@type' => 'AdministrativeArea', 'name' => $place['provincie'] ?? 'Nederland']],
			'provider' => ['@type' => 'Organization', 'name' => $site->name(), 'url' => $site->url('')],
			'url' => $canonical],
		['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
			['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $site->url('')],
			['@type' => 'ListItem', 'position' => 2, 'name' => 'Plaatsen', 'item' => $site->url('plaatsen')],
			['@type' => 'ListItem', 'position' => 3, 'name' => $placeName, 'item' => $canonical],
		]],
	];
	if ($faq) {
		$ld[] = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn ($f) => [
			'@type' => 'Question', 'name' => $f['q'],
			'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
		], $faq)];
	}
@endphp
@extends('channels.layout')

@section('title', $c['meta_title'] ?? ('Website in ' . $placeName))
@section('description', $c['meta_description'] ?? $site->homeDescription())

@push('head')
	@foreach ($ld as $block)
		<script type="application/ld+json">{!! json_encode($block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
	@endforeach
@endpush

@section('content')
	<section class="hero">
		<div class="wrap">
			<span class="eyebrow">{{ $placeName }}</span>
			<h1>{{ $c['h1'] ?? ('Website voor je bedrijf in ' . $placeName) }}</h1>
			@if (!empty($c['hero_lead']))<p class="lead" style="max-width:60ch">{{ $c['hero_lead'] }}</p>@endif
			<a href="#contact" class="btn">Gratis voorbeeld in {{ $placeName }}</a>
		</div>
	</section>

	@if (!empty($c['intro']))
		<section>
			<div class="wrap" style="max-width:760px">
				<div class="prose"><p>{{ $c['intro'] }}</p></div>
			</div>
		</section>
	@endif

	{{-- Echte lokale bedrijven in de regio (Google Places) — branche-gerichte info. --}}
	@if ($biz)
		<section style="background:var(--c-surface)">
			<div class="wrap">
				<span class="kicker"><span class="kicker-line"></span> In de regio</span>
				<h2>{{ $business['label'] ?: ('Bedrijven in ' . $placeName) }}</h2>
				@if (!empty($business['intro']))<p class="muted" style="max-width:70ch;margin-bottom:1.6rem">{{ $business['intro'] }}</p>@endif
				<div class="grid cols-4" style="gap:1rem">
					@foreach ($biz as $b)
						<div class="card" style="display:flex;flex-direction:column;gap:.35rem">
							<h3 style="font-size:1.02rem;line-height:1.25">{{ $b['name'] }}</h3>
							@if (!empty($b['type']))<span class="muted" style="font-size:.8rem">{{ $b['type'] }}</span>@endif
							@if (!empty($b['rating']))
								<div style="font-size:.85rem;font-weight:700;color:var(--c-accent)">★ {{ number_format((float) $b['rating'], 1, ',', '') }}<span class="muted" style="font-weight:400"> · {{ $b['reviews'] }} reviews</span></div>
							@endif
							@if (!empty($b['address']))<p class="muted" style="font-size:.82rem;margin-top:.1rem">{{ $b['address'] }}</p>@endif
							<div style="margin-top:auto;display:flex;gap:.8rem;padding-top:.5rem;font-size:.85rem;font-weight:600">
								@if (!empty($b['website']))<a href="{{ $b['website'] }}" target="_blank" rel="noopener nofollow" style="color:var(--c-cta)">{{ $host($b['website']) ?: 'Website' }} →</a>@endif
								@if (!empty($b['maps']))<a href="{{ $b['maps'] }}" target="_blank" rel="noopener nofollow" class="muted">Route</a>@endif
							</div>
						</div>
					@endforeach
				</div>
				<p class="muted" style="font-size:.8rem;margin-top:1rem">Bron: openbare Google-bedrijfsgegevens. Staat jouw bedrijf hier en wil je online opvallen? <a href="#contact" style="color:var(--c-cta);font-weight:600">Vraag een gratis voorbeeld aan</a>.</p>
			</div>
		</section>
	@endif

	@if (!empty($c['wie']))
		<section>
			<div class="wrap" style="max-width:760px">
				<div class="prose"><p>{{ $c['wie'] }}</p></div>
			</div>
		</section>
	@endif

	{{-- Groeidiamant: waar we een bedrijf in deze plaats mee helpen (links naar triggers). --}}
	@if ($facets)
		<section style="background:var(--c-surface)">
			<div class="wrap" style="text-align:center">
				<span class="kicker" style="justify-content:center"><span class="kicker-line"></span> Wat we voor je doen in {{ $placeName }}</span>
				<h2>Van website tot slimme groei</h2>
				<div style="display:flex;gap:.7rem;justify-content:center;flex-wrap:wrap;margin-top:1.4rem">
					@foreach ($facets as $key => $f)
						<a href="{{ $site->url($key) }}" class="btn btn-ghost" style="gap:.5rem">
							<span style="display:inline-flex;color:var(--c-primary)">@include('channels.partials.icon', ['name' => $f['icon'] ?? 'check'])</span>
							{{ $f['label'] ?? $key }}
						</a>
					@endforeach
				</div>
			</div>
		</section>
	@endif

	@if (!empty($c['trust']))
		<section>
			<div class="wrap" style="max-width:760px">
				<div class="prose"><p>{{ $c['trust'] }}</p></div>
				<a href="#contact" class="btn" style="margin-top:1.2rem">Vraag je gratis voorbeeld aan</a>
			</div>
		</section>
	@endif

	@if ($faq)
		<section style="background:var(--c-surface)">
			<div class="wrap">
				<span class="kicker"><span class="kicker-line"></span> Vragen</span>
				<h2>Veelgestelde vragen</h2>
				<div class="faq" style="margin-top:1.4rem">
					@foreach ($faq as $i => $qa)
						<details @if($i === 0) open @endif>
							<summary>{{ $qa['q'] }}</summary>
							<p>{{ $qa['a'] }}</p>
						</details>
					@endforeach
				</div>
			</div>
		</section>
	@endif

	{{-- Zoveel mogelijk interne links: alle plaatsen in dezelfde provincie. --}}
	@if ($nearby)
		<section>
			<div class="wrap">
				<span class="kicker"><span class="kicker-line"></span> {{ $place['provincie'] ?? 'In de buurt' }}</span>
				<h2>Ook actief in de omgeving</h2>
				<div style="display:flex;gap:.5rem .9rem;flex-wrap:wrap;margin-top:1rem;line-height:1.9">
					@foreach ($nearby as $slug => $name)
						<a href="{{ $site->url('plaatsen/' . $slug) }}" style="font-size:.9rem;font-weight:600;text-decoration:none;color:inherit">{{ $name }}</a>
					@endforeach
				</div>
				<p style="margin-top:1.4rem"><a href="{{ $site->url('plaatsen') }}" style="font-weight:700;color:var(--c-cta)">Alle plaatsen bekijken →</a></p>
			</div>
		</section>
	@endif

	<section class="cta-band" data-section="cta">
		<div class="wrap">
			<div class="cta-band-inner">
				<div>
					<h2>{{ $c['cta_title'] ?? ('Aan de slag in ' . $placeName . '?') }}</h2>
					@if (!empty($c['cta']))<p>{{ $c['cta'] }}</p>@endif
				</div>
				<a href="#contact" class="btn">Gratis voorbeeld aanvragen</a>
			</div>
		</div>
	</section>

	@include('channels.partials.lead-form')
@endsection
