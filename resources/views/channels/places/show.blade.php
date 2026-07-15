@php
	/** @var \App\Support\ChannelSite $site */
	$pl   = $site->get('places', []);
	$repl = fn ($t) => str_replace(':city', $placeName, (string) $t);
	$h    = $site->get('home', []);
	$service = $pl['service'] ?? 'website';
	$placePrefill = $placeName;

	// Ondersteunende alinea: 3 niche+stad-varianten, deterministisch per stad
	// gekozen. Zo is de tekst onder de unieke AI-intro niet 1-op-1 gelijk aan
	// andere steden (minder duplicate-content-signaal). :city / :service placeholders.
	$angles = [
		'Wie in :city een vakman zoekt, kiest wie het snelst vertrouwen wekt. Wij zorgen dat jouw :service daar bovenaan staat: snel, vindbaar in Google en gemaakt voor je klanten in :city en omgeving.',
		'In :city draait het om gevonden worden op het juiste moment. Met een :service die klopt, laat je zien wie je bent en waarom mensen in :city juist jou moeten bellen.',
		'Klanten in :city vergelijken snel en beslissen nog sneller. Een verzorgde :service die op elke telefoon werkt, geeft je in :city net dat streepje voor.',
	];
	$angle = str_replace([':city', ':service'], [$placeName, $service], $angles[abs(crc32($site->key . '|' . $placeSlug)) % count($angles)]);
@endphp
@extends('channels.layout')

@section('title', $c['meta_title'] ?? ('Website in ' . $placeName))
@section('description', $c['meta_description'] ?? $site->homeDescription())
{{-- Dunne plaatsen (te weinig echte bedrijven) niet indexeren: voorkomt doorway-content. --}}
@unless ($indexable ?? true)
    @section('robots', 'noindex,follow')
@endunless

@push('head')
	@foreach ($ld as $block)
		<script type="application/ld+json">{!! json_encode($block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
	@endforeach
@endpush

@push('head')
	{{-- Per-stad Service-schema (areaServed = de stad) + BreadcrumbList. \x40 = @. --}}
	<script type="application/ld+json">
	{!! json_encode([
		"\x40context" => 'https://schema.org',
		"\x40type"    => 'Service',
		'name'        => $repl($pl['city_h1'] ?? ('Website laten maken in ' . $placeName)),
		'serviceType' => $service,
		'areaServed'  => ["\x40type" => 'City', 'name' => $placeName],
		'provider'    => ["\x40id" => $site->url('') . '#org'],
		'url'         => $site->url('plaatsen/' . $placeSlug),
		'inLanguage'  => $site->locale(),
	], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
	</script>
	<script type="application/ld+json">
	{!! json_encode([
		"\x40context"     => 'https://schema.org',
		"\x40type"        => 'BreadcrumbList',
		'itemListElement' => [
			["\x40type" => 'ListItem', 'position' => 1, 'name' => 'Home',     'item' => $site->url('')],
			["\x40type" => 'ListItem', 'position' => 2, 'name' => 'Plaatsen', 'item' => $site->url('plaatsen')],
			["\x40type" => 'ListItem', 'position' => 3, 'name' => $placeName, 'item' => $site->url('plaatsen/' . $placeSlug)],
		],
	], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
	</script>
@endpush

@section('content')
	<section class="hero">
		<div class="wrap">
			{{-- Geen eyebrow-pill (afspraak). --}}
			<h1>{{ $repl($pl['city_h1'] ?? ('Website laten maken in ' . $placeName)) }}</h1>
			<p class="lead" style="max-width:58ch">{{ $site->placeIntro($placeSlug, $placeName) }}</p>
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
				<h2>Wat je krijgt in {{ $placeName }}</h2>
				<div class="grid cols-4" style="margin-top:1.4rem">
					@foreach ($h['features'] as $f)
						<div class="card">
							{{-- Altijd inline-SVG (nooit emoji). --}}
							<div style="color:var(--c-primary);margin-bottom:.5rem">@include('channels.partials.icon', ['name' => $f['icon'] ?? 'check'])</div>
							<h3>{{ $f['title'] }}</h3>
							<p class="muted" style="font-size:.95rem">{{ $f['text'] }}</p>
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
	@include('channels.partials.groeipad', [
		'site'   => $site,
		'facets' => $facets,
		'kicker' => 'Wat we voor je doen in ' . $placeName,
		'title'  => 'Van website tot slimme groei',
		'lead'   => 'Begin met een sterke website en breid later uit met een webshop, klantenportaal, automatisering of AI. Je groeit stap voor stap, in je eigen tempo.',
	])

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

	<section style="background:var(--c-surface)">
		<div class="wrap" style="max-width:720px">
			<h2>Een {{ $service }} die werkt in {{ $placeName }}</h2>
			<div class="prose" style="margin-top:1rem">
				<p>{{ $angle }}</p>
				<p>Geen lange contracten of technisch gedoe. We zetten vooraf een gratis voorbeeld klaar dat is afgestemd op jouw zaak in {{ $placeName }}, zodat je precies ziet wat je krijgt voordat je iets beslist.</p>
			</div>
		</div>
	</section>

	<div id="contact" class="scroll-anchor" aria-hidden="true"></div>
	@include('channels.partials.lead-wizard', ['site' => $site, 'facet' => 'website', 'placePrefill' => $placeName])
@endsection
