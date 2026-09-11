@extends('layouts.app')

@php
	// DE AI TELEFONISTE. Draait in bouwsteenwinkel_v3/telefonie (Asterisk + OpenAI
	// Realtime, live sinds 28-08-2026). Op deze pagina staat ALLEEN wat de telefoniste
	// aantoonbaar doet (telefonie/README.md, audiosocket.py, beleid/*). Bewust NIET:
	// - afspraken in een agenda zetten of om reviews vragen: dat kan ze niet, ook al
	//   beloven de AI-secties op de channel-sites dat (config/*_landings.php);
	// - terugbellen op een gekozen uur: gebouwd op 09-09 (1fa58ff5), maar niet
	//   bevestigd uitgerold. Pas noemen als dat vaststaat.
	// Het demonummer 088 254 5150 is een verzonnen apotheek met verzonnen patiënten.
	$hreflangLocales = ['nl'];
	$demoTel  = '+31882545150';
	$demoToon = '088 254 5150';
	$afspraak = '/nl/afspraak?onderwerp=ai-telefoniste';

	$p = [
		'slug'    => 'ai-telefoniste',
		'kruimel' => 'AI Telefoniste',
		'lead'    => 'Een AI-telefoniste die opneemt, luistert, vragen beantwoordt uit uw eigen kennisbank, doorverbindt als het nodig is en na elk gesprek een verslag stuurt. Ook als u zelf niet kunt opnemen.',
		'faq' => [
			['v' => 'Weet de beller dat hij met een AI praat?', 'a' => 'Ja. Ze stelt zich aan het begin van elk gesprek voor als de digitale assistent van uw organisatie. Daarna praat ze natuurlijk, luistert eerst en vat samen wat ze begrepen heeft.'],
			['v' => 'Kan ze afspraken in onze agenda zetten?', 'a' => 'Nee, dat kan ze nu niet. Ze neemt een terugbelverzoek of bericht aan, of verbindt door naar een medewerker. U ziet alles terug in uw portaal en krijgt een verslag per mail.'],
			['v' => 'Worden de gesprekken opgenomen?', 'a' => 'Nee. Gesprekken worden niet opgenomen. U krijgt een samenvatting van wat er besproken is.'],
			['v' => 'Wat als ze een vraag niet kan beantwoorden?', 'a' => 'Dan zegt ze dat eerlijk en biedt ze aan om door te verbinden (tijdens openingstijden), een terugbelverzoek te noteren, of het antwoord per e-mail te laten sturen. Voor onderwerpen die u altijd bij een mens wilt hebben, kunt u vastleggen dat ze meteen doorverbindt.'],
			['v' => 'In welke talen spreekt ze?', 'a' => 'Standaard Nederlands. Merkt ze dat de beller liever een andere taal spreekt, dan biedt ze Engels, Duits of Arabisch aan. Ze kan ook langzamer en duidelijker gaan praten.'],
			['v' => 'Kunnen we zelf aanpassen wat ze weet?', 'a' => 'Ja. In uw portaal houdt u de kennisbank bij en zet u dagberichten klaar, bijvoorbeeld een afwijkende openingstijd. Een dagbericht gebruikt ze binnen twee minuten.'],
		],
	];

	$kan = [
		['icon' => '👂', 't' => 'Neemt op en luistert eerst',     'b' => 'Geen keuzemenu met "toets 1". De beller vertelt waarvoor hij belt, en ze vat samen wat ze begrepen heeft.'],
		['icon' => '📚', 't' => 'Antwoordt uit uw kennisbank',    'b' => 'Openingstijden, werkwijze, veelgestelde vragen: ze antwoordt uit de informatie die u zelf vastlegt. Staat het er niet in, dan zegt ze dat ze het niet weet.'],
		['icon' => '📞', 't' => 'Verbindt door als het moet',     'b' => 'Wil de beller een mens spreken, of gaat het over een onderwerp dat altijd naar een medewerker moet, dan verbindt ze door tijdens openingstijden.'],
		['icon' => '📝', 't' => 'Neemt verzoeken aan',            'b' => 'De beller kiest: teruggebeld worden, het antwoord per e-mail krijgen of een bericht achterlaten. Het e-mailadres leest ze ter controle terug.'],
		['icon' => '🌍', 't' => 'Spreekt meerdere talen',         'b' => 'Nederlands, en op verzoek Engels, Duits of Arabisch. Op verzoek praat ze ook langzamer en duidelijker.'],
		['icon' => '🗓️', 't' => 'Kent uw openingstijden',          'b' => 'Ze weet hoe laat het is, wanneer u open bent en welke feestdagen eraan komen.'],
		['icon' => '🔎', 't' => 'Zoekt gegevens op',              'b' => 'Gekoppeld aan uw systeem kan ze bijvoorbeeld de status van een bestelling opzoeken, na een controle van de postcode.'],
		['icon' => '📨', 't' => 'Stuurt een verslag',             'b' => 'Na elk gesprek een samenvatting per mail, en alle gesprekken terug te zien in uw eigen portaal.'],
	];
@endphp

@section('title', \App\Support\PaginaTitel::met('AI Telefoniste: nooit meer een gemiste oproep'))
@section('description', 'Een AI-telefoniste die opneemt, vragen beantwoordt uit uw eigen kennisbank, doorverbindt en na elk gesprek een verslag stuurt. Bel zelf de demo: 088 254 5150.')

@push('head')
	@php
		$__tel_blokken = [
			[
				"\x40context"   => 'https://schema.org',
				"\x40type"      => 'Service',
				'name'        => 'AI Telefoniste',
				'description' => $p['lead'],
				'serviceType' => 'AI-telefonist / virtuele receptionist',
				'provider'    => ["\x40type" => 'Organization', "\x40id" => url('/#organization'), 'name' => 'Beter Geregeld ICT', 'url' => url('/')],
				'areaServed'  => ["\x40type" => 'Country', 'name' => 'Netherlands'],
				'inLanguage'  => 'nl',
				'url'         => url('/nl/ai-telefoniste'),
			],
			[
				"\x40context"        => 'https://schema.org',
				"\x40type"           => 'BreadcrumbList',
				'itemListElement' => [
					["\x40type" => 'ListItem', 'position' => 1, 'name' => 'Home',           'item' => url('/nl')],
					["\x40type" => 'ListItem', 'position' => 2, 'name' => 'Diensten',       'item' => url('/nl/diensten')],
					["\x40type" => 'ListItem', 'position' => 3, 'name' => 'AI Telefoniste', 'item' => url('/nl/ai-telefoniste')],
				],
			],
			[
				"\x40context"  => 'https://schema.org',
				"\x40type"     => 'FAQPage',
				'mainEntity' => array_map(fn ($q) => ["\x40type" => 'Question', 'name' => $q['v'], 'acceptedAnswer' => ["\x40type" => 'Answer', 'text' => $q['a']]], $p['faq']),
			],
		];
	@endphp
	@foreach ($__tel_blokken as $__tel_blok)
		<script type="application/ld+json">
		{!! json_encode($__tel_blok, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
		</script>
	@endforeach
@endpush

@section('content')

{{-- ============ HERO ============ --}}
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-60"></div>
	<div class="absolute -top-32 -right-32 w-[600px] h-[600px] rounded-full bg-[color:var(--color-accent)] opacity-10 blur-3xl"></div>

	<div class="relative max-w-[1400px] mx-auto px-6 pt-16 pb-24 sm:py-28">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-8 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">Home</a>
			<span class="opacity-40">/</span>
			<a href="/nl/diensten" class="hover:text-white">Diensten</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">AI Telefoniste</span>
		</nav>

		<div class="grid lg:grid-cols-12 gap-12 items-center">
			<div class="lg:col-span-7">
				<span class="pill pill-dark mb-6 max-w-full">
					<span class="w-1.5 h-1.5 rounded-full bg-[color:var(--color-accent)]"></span>
					AI Telefoniste
				</span>
				<h1 class="display-1 mb-6">
					Nooit meer een
					<span class="accent-word">gemiste oproep.</span>
				</h1>
				<p class="text-lg sm:text-xl text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl mb-8">{{ $p['lead'] }}</p>
				<div class="flex flex-wrap gap-3">
					<a href="tel:{{ $demoTel }}" class="btn-accent">
						Bel de demo: {{ $demoToon }}
						<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</a>
					<a href="{{ $afspraak }}" class="btn-ghost-light">Plan een kennismaking</a>
				</div>
			</div>
			<div class="lg:col-span-5">
				<div class="rounded-2xl border border-white/10 bg-white/5 p-8">
					<div class="text-xs font-semibold uppercase tracking-wider text-[color:var(--color-on-dark-soft)] mb-3">Hoor het zelf</div>
					<a href="tel:{{ $demoTel }}" class="block text-4xl sm:text-5xl font-black text-white hover:text-[color:var(--color-accent)] mb-4 tracking-tight">{{ $demoToon }}</a>
					<p class="text-[color:var(--color-on-dark-muted)] leading-relaxed mb-5">
						Bel onze demo-apotheek en praat met de telefoniste. Vraag naar de openingstijden, vraag een herhaalrecept aan, of vraag of u iemand kunt spreken.
					</p>
					<p class="text-xs text-[color:var(--color-on-dark-soft)] leading-relaxed">
						De apotheek en de patiëntgegevens in deze demo zijn verzonnen. U belt een gewoon 088-nummer tegen uw normale belkosten.
					</p>
				</div>
			</div>
		</div>
	</div>
</section>

{{-- ============ HET PROBLEEM ============ --}}
<section class="py-20 border-b border-[color:var(--color-line)]">
	<div class="max-w-[1100px] mx-auto px-6">
		<span class="pill pill-ink mb-4">Herkent u dit?</span>
		<h2 class="display-2 mb-6 max-w-3xl">
			De telefoon gaat
			<span class="accent-word">precies op het verkeerde moment.</span>
		</h2>
		<div class="grid md:grid-cols-2 gap-8 text-[color:var(--color-ink-muted)] leading-relaxed">
			<p>Midden in een klant, een behandeling of een klus. Of na sluitingstijd. Een gemiste oproep is vaak een klant die het ergens anders probeert, of een vraag die morgen alsnog op uw bureau ligt.</p>
			<p>En een groot deel van de telefoontjes gaat over hetzelfde: hoe laat bent u open, is mijn bestelling al verstuurd, kan iemand mij terugbellen. Vragen waarvoor niemand zijn werk hoeft te onderbreken.</p>
		</div>
	</div>
</section>

{{-- ============ WAT ZE DOET ============ --}}
<section class="bg-[color:var(--color-surface)] py-20">
	<div class="max-w-[1400px] mx-auto px-6">
		<div class="max-w-3xl mb-12">
			<span class="pill pill-teal mb-3">Wat ze doet</span>
			<h2 class="display-2 mb-5">Een telefoniste die altijd opneemt.</h2>
			<p class="text-lg text-[color:var(--color-ink-muted)] leading-relaxed">Ze vervangt uw medewerkers niet. Ze vangt de telefoontjes op die hen nu steeds onderbreken, en zorgt dat de rest bij de juiste persoon terechtkomt.</p>
		</div>
		<div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5">
			@foreach ($kan as $k)
				<div class="card">
					<div class="w-11 h-11 rounded-lg bg-[color:var(--color-accent-soft)] flex items-center justify-center text-xl mb-4">{{ $k['icon'] }}</div>
					<h3 class="font-bold mb-2">{{ $k['t'] }}</h3>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $k['b'] }}</p>
				</div>
			@endforeach
		</div>
	</div>
</section>

{{-- ============ PRIVACY ============ --}}
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1100px] mx-auto px-6 py-24">
		<span class="pill pill-dark mb-5">Privacy</span>
		<h2 class="display-2 mb-8 max-w-3xl">
			Discreet,
			<span class="accent-word">zoals een goede telefoniste.</span>
		</h2>
		<div class="grid md:grid-cols-3 gap-6 text-[color:var(--color-on-dark-muted)] leading-relaxed">
			<div>
				<h3 class="text-white font-bold mb-2">Geen opnames</h3>
				<p>Gesprekken worden niet opgenomen. U krijgt een samenvatting van wat er besproken is.</p>
			</div>
			<div>
				<h3 class="text-white font-bold mb-2">Bevestigen, niet voorlezen</h3>
				<p>Ze controleert gegevens die de beller noemt, maar leest zelf geen persoonsgegevens voor en geeft derden geen informatie.</p>
			</div>
			<div>
				<h3 class="text-white font-bold mb-2">Inhoud achter een login</h3>
				<p>In de mail die u na een gesprek krijgt, staat standaard alleen dát er gebeld is. Waar het over ging, leest u in uw beveiligde portaal.</p>
			</div>
		</div>
	</div>
</section>

{{-- ============ ZELF IN DE HAND ============ --}}
<section class="py-20">
	<div class="max-w-[1400px] mx-auto px-6">
		<div class="grid lg:grid-cols-12 gap-10 lg:gap-14 items-start">
			<div class="lg:col-span-5">
				<span class="pill pill-teal mb-3">Uw portaal</span>
				<h2 class="display-2 mb-5">U bepaalt wat ze weet.</h2>
				<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-4">Bij de telefoniste hoort een eigen portaal. Daar ziet u elk gesprek terug en past u zelf aan wat ze vertelt, zonder ons te hoeven bellen.</p>
				<a href="/nl/klantportaal-laten-maken" class="text-sm font-semibold text-[color:var(--color-accent-hover)] inline-flex items-center gap-1.5">Wij bouwen ook klantportalen →</a>
			</div>
			<div class="lg:col-span-7">
				<div class="card">
					<ul class="space-y-4">
						@foreach ([
							['Gesprekken', 'Elk gesprek met samenvatting, en wat de beller wilde.'],
							['Terugbelverzoeken', 'Wie teruggebeld wil worden, waarover en op welk nummer.'],
							['Kennisbank', 'De vragen en antwoorden waaruit de telefoniste put, door u bij te houden.'],
							['Dagbericht', 'Een tijdelijke mededeling, zoals een afwijkende openingstijd. De telefoniste gebruikt hem binnen twee minuten.'],
						] as [$t, $b])
							<li class="flex items-start gap-3 border-b border-[color:var(--color-line)] pb-4 last:border-0 last:pb-0">
								<span class="mt-0.5 shrink-0 w-6 h-6 rounded-full bg-[color:var(--color-accent-soft)] text-[color:var(--color-accent-hover)] flex items-center justify-center text-xs font-bold">{{ $loop->iteration }}</span>
								<span><span class="font-semibold">{{ $t }}.</span> <span class="text-[color:var(--color-ink-muted)]">{{ $b }}</span></span>
							</li>
						@endforeach
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>

{{-- ============ WERKWIJZE + PRIJS ============ --}}
<section class="bg-[color:var(--color-surface)] py-20">
	<div class="max-w-[1400px] mx-auto px-6">
		<div class="max-w-3xl mb-12">
			<span class="pill pill-ink mb-3">Zo starten we</span>
			<h2 class="display-2 mb-5">Van kennismaking tot eerste gesprek.</h2>
		</div>
		<div class="grid md:grid-cols-3 gap-5 mb-14">
			@foreach ([
				['Kennismaking', 'We bespreken welke telefoontjes u binnenkrijgt, wat de telefoniste mag beantwoorden en wanneer ze moet doorverbinden.'],
				['Kennisbank en regels', 'Samen leggen we de antwoorden, openingstijden en onderwerpen vast die altijd naar een mens gaan. We testen met uw eigen vragen.'],
				['Live', 'De telefoniste neemt op. U volgt de gesprekken in uw portaal en past de kennisbank aan waar dat nodig is.'],
			] as [$t, $b])
				<div class="card">
					<div class="text-5xl font-black text-[color:var(--color-accent)] mb-3 leading-none">{{ sprintf('%02d', $loop->iteration) }}</div>
					<h3 class="font-bold mb-2">{{ $t }}</h3>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $b }}</p>
				</div>
			@endforeach
		</div>

		<div class="card grid lg:grid-cols-12 gap-8 items-start">
			<div class="lg:col-span-6">
				<span class="pill pill-ink mb-3">Wat kost het?</span>
				<h3 class="text-2xl font-bold mb-3">Een prijs die past bij uw telefoonverkeer.</h3>
				<p class="text-[color:var(--color-ink-muted)] leading-relaxed">We noemen geen bedrag zonder uw situatie te kennen. Na de kennismaking krijgt u vooraf een duidelijke prijs, zodat u weet waar u aan toe bent.</p>
			</div>
			<div class="lg:col-span-6">
				<div class="text-xs font-semibold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">De prijs hangt vooral af van</div>
				<ul class="space-y-2">
					@foreach ([
						'Hoeveel gesprekken en belminuten er zijn',
						'Wat ze moet kunnen: vragen beantwoorden, of ook gegevens opzoeken in uw systeem',
						'Hoeveel talen en onderwerpen de kennisbank moet dekken',
					] as $f)
						<li class="flex items-start gap-3">
							<span class="mt-2 w-1.5 h-1.5 rounded-full bg-[color:var(--color-accent)] shrink-0"></span>
							<span class="leading-relaxed">{{ $f }}</span>
						</li>
					@endforeach
				</ul>
			</div>
		</div>
	</div>
</section>

{{-- ============ VEELGESTELDE VRAGEN ============ --}}
<section class="py-20">
	<div class="max-w-[900px] mx-auto px-6">
		<span class="pill pill-ink mb-3">Veelgesteld</span>
		<h2 class="display-2 mb-10">Vragen die we vaak krijgen.</h2>
		<div class="space-y-4">
			@foreach ($p['faq'] as $q)
				<details class="card group">
					<summary class="font-bold cursor-pointer list-none flex items-start justify-between gap-4">
						<span>{{ $q['v'] }}</span>
						<span class="text-[color:var(--color-accent-hover)] group-open:rotate-45 transition-transform text-xl leading-none">+</span>
					</summary>
					<p class="text-[color:var(--color-ink-muted)] leading-relaxed mt-4">{{ $q['a'] }}</p>
				</details>
			@endforeach
		</div>
	</div>
</section>

{{-- ============ CTA ============ --}}
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="absolute -bottom-40 -left-40 w-[500px] h-[500px] rounded-full bg-[color:var(--color-accent)] opacity-10 blur-3xl"></div>
	<div class="relative max-w-[1100px] mx-auto px-6 py-24 text-center">
		<h2 class="display-1 mb-6">
			Bel haar zelf.
			<span class="accent-word">Dan weet u genoeg.</span>
		</h2>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] max-w-2xl mx-auto mb-8">Twee minuten met de demo zegt meer dan deze pagina. Wilt u daarna weten wat ze voor uw organisatie kan doen, dan plannen we een kennismaking.</p>
		<div class="flex flex-wrap gap-3 justify-center">
			<a href="tel:{{ $demoTel }}" class="btn-accent">Bel de demo: {{ $demoToon }}</a>
			<a href="{{ $afspraak }}" class="btn-ghost-light">Plan een kennismaking</a>
		</div>
	</div>
</section>

@endsection
