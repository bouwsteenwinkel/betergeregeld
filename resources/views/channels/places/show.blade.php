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

	// Compat-shim: deze view is herschreven naar korte var-namen ($c/$biz/$faq/
	// $facets/$ld) maar ChannelSiteController@place levert nog de lange namen
	// (content/businesses/…). Zonder dit 500't elke plaatspagina op een undefined
	// variable. Koppelt de controller-data door; blijft correct als de controller
	// later zelf de korte namen meegeeft (?? behoudt een meegegeven waarde).
	$c      = $c      ?? ($content ?? []);
	$biz    = $biz    ?? ($businesses ?? []);
	$faq    = $faq    ?? ($c['faq'] ?? []);
	$facets = $facets ?? (array) config('groeidiamant.facets', []);
	$ld     = $ld     ?? [];
@endphp
@extends('channels.layout')

@section('title', $c['meta_title'] ?? ('Website in ' . $placeName))
@section('description', $c['meta_description'] ?? $site->homeDescription())
{{-- Dunne plaatsen (te weinig echte bedrijven) niet indexeren: voorkomt doorway-content. --}}
@unless ($indexable ?? true)
    @section('robots', 'noindex,follow')
@endunless

@push('head')
	{{-- $ld = optionele extra JSON-LD-blokken vanuit de controller. Default leeg
	     zodat de plaatspagina niet 500't als de var (nog) niet wordt meegegeven;
	     het Service-/Breadcrumb-schema hieronder staat er sowieso. --}}
	@foreach (($ld ?? []) as $block)
		<script type="application/ld+json">{!! json_encode($block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
	@endforeach
@endpush

@push('head')
	{{-- Per-stad Service-schema (areaServed = de stad) + BreadcrumbList. \x40 = @. --}}
	<script type="application/ld+json">
	{!! json_encode([
		"\x40context" => 'https://schema.org',
		"\x40type"    => 'Service',
		{{-- Zelfde kop als de H1, anders belooft het schema iets anders dan de pagina. --}}
		'name'        => $c['h1'] ?? $repl($pl['city_h1'] ?? ('Website laten maken in ' . $placeName)),
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
			{{-- H1/lead uit de variatie-engine ($c): die is branche- én plaatsgericht
			     ("Badkamerbedrijf in Utrecht? Word online gevonden"). De oude
			     city_h1-fallback gaf op alle 17 sites dezelfde kop. --}}
			<h1>{{ $c['h1'] ?? $repl($pl['city_h1'] ?? ('Website laten maken in ' . $placeName)) }}</h1>
			<p class="lead" style="max-width:58ch">{{ $site->placeIntro($placeSlug, $placeName) ?: ($c['hero_lead'] ?? '') }}</p>
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

	{{-- Echte lokale bedrijven in de regio (Google Places) — branche-gerichte info.
	     Dit blok lúisterde eerst wel naar $biz maar toonde de generieke
	     site-features, met een bronvermelding zonder bron. De opgehaalde
	     bedrijven zijn juist het enige wat deze pagina echt uniek maakt én de
	     directe aanleiding voor de pitch: "sta jij hier tussen?" --}}
	@if ($biz)
		<section style="background:var(--c-surface)">
			<div class="wrap">
				<h2>{{ $business['label'] ?? ($pl['trades'] ?? 'Bedrijven') . ' in ' . $placeName }}</h2>
				@if (!empty($business['intro']))
					<p class="muted" style="max-width:70ch;margin-top:.6rem">{{ $business['intro'] }}</p>
				@endif

				{{-- Marktbeeld, gerekend uit de échte listings. Uniek per plaats én per
				     branche omdat het uit de cijfers van díe combinatie volgt — geen
				     sjabloonzin die op 1.195 plaatsen hetzelfde is. --}}
				@php
					$mRatings = array_values(array_filter(array_map(fn ($b) => $b['rating'] ?? null, $biz)));
					$mReviews = array_sum(array_map(fn ($b) => (int) ($b['reviews'] ?? 0), $biz));
					$mGem     = $mRatings ? array_sum($mRatings) / count($mRatings) : null;
					$mTop     = null;
					foreach ($biz as $b) {
						if (($b['rating'] ?? 0) && (! $mTop || $b['rating'] > $mTop['rating'])) { $mTop = $b; }
					}
				@endphp
				@if ($mGem)
					<p style="max-width:70ch;margin-top:.9rem">
						In {{ $placeName }} en omgeving vonden we {{ count($biz) }}
						{{ $pl['trades'] ?? 'bedrijven' }}, samen goed voor
						{{ number_format($mReviews, 0, ',', '.') }} beoordelingen met een gemiddelde van
						{{ number_format($mGem, 1, ',', '') }}.
						@if ($mTop)
							De hoogst gewaardeerde is {{ $mTop['name'] }} ({{ number_format((float) $mTop['rating'], 1, ',', '') }}).
						@endif
						Dat is het gezelschap waarin een klant uit {{ $placeName }} jou vergelijkt.
					</p>
				@endif
				<div class="grid cols-4" style="margin-top:1.4rem">
					@foreach ($biz as $b)
						<div class="card">
							<h3 style="font-size:1rem">{{ $b['name'] ?? '' }}</h3>
							@if (!empty($b['address']))
								<p class="muted" style="font-size:.9rem">{{ $b['address'] }}</p>
							@endif
							@if (!empty($b['rating']))
								<p class="muted" style="font-size:.85rem">
									{{ number_format((float) $b['rating'], 1, ',', '') }}★
									@if (!empty($b['reviews'])) · {{ $b['reviews'] }} reviews @endif
								</p>
							@endif
						</div>
					@endforeach
				</div>
				<p class="muted" style="font-size:.8rem;margin-top:1rem">Bron: openbare Google-bedrijfsgegevens. Staat jouw bedrijf hier en wil je online opvallen? <a href="#contact" style="color:var(--c-cta);font-weight:600">Vraag een gratis voorbeeld aan</a>.</p>
			</div>
		</section>
	@endif

	{{-- Echte feiten over deze plaats (channel_place_facts): gemeente, provincie,
	     inwoners en de werkelijke buurplaatsen. Stond al klaar in de controller
	     maar werd door geen enkele view gebruikt. --}}
	@if (!empty($facts))
		<section>
			<div class="wrap" style="max-width:760px">
				<h2>{{ $placeName }} in het kort</h2>
				<div class="grid cols-4" style="margin-top:1.2rem">
					@if (!empty($facts['gemeente']))
						<div class="card"><h3 style="font-size:.85rem" class="muted">Gemeente</h3><p>{{ $facts['gemeente'] }}</p></div>
					@endif
					@if (!empty($facts['provincie']))
						<div class="card"><h3 style="font-size:.85rem" class="muted">Provincie</h3><p>{{ $facts['provincie'] }}</p></div>
					@endif
					@if (!empty($facts['inwoners']))
						<div class="card"><h3 style="font-size:.85rem" class="muted">Inwoners</h3><p>{{ number_format($facts['inwoners'], 0, ',', '.') }}</p></div>
					@endif
					@if (!empty($facts['afstand_km']))
						<div class="card"><h3 style="font-size:.85rem" class="muted">Afstand tot ons</h3><p>{{ $facts['afstand_km'] }} km</p></div>
					@endif
				</div>
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
