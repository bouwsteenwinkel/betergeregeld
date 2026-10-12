@extends('layouts.app')

{{-- NEDERLANDSE HOMEPAGE, PROCESSCAN-POSITIONERING (12-10-2026).

     Waarom een eigen view naast welcome.blade.php: de homepage verkocht tot nu toe "website of
     maatwerk software laten maken" met dertien dienstenkaartjes. De nieuwe boodschap is het
     probleem van de ondernemer (terugkerend handwerk), niet onze techniek. Die tekst is alleen
     in het Nederlands geschreven; /en houdt welcome.blade.php tot er een Engelse versie is.

     De zoekterm "website laten maken" leverde de homepage in 90 dagen nul vertoningen op
     (Search Console, 14-09-2026), dus daar valt geen ranking te verliezen. Die zoekintentie
     hoort bij jouw-bedrijfswebsite.nl en de branchesites; sectie 11 blijft daar naar linken.

     Klikmeting: elke knop met data-track gaat als cta_click naar onze eigen event-log
     (/_ev, first-party, geen cookie, zie ChannelEventController). Zie het script onderaan. --}}

@php
	$hreflangLocales = ['nl', 'en'];

	$herkenning = [
		'Medewerkers typen dezelfde gegevens meerdere keren over.',
		'Belangrijke processen draaien nog in Excel.',
		'Klantmails worden één voor één gelezen, opgezocht en doorgestuurd.',
		'Rapportages worden iedere week met de hand gemaakt.',
		'Verschillende systemen bevatten dezelfde informatie, maar zijn niet gekoppeld.',
		'Slechts één medewerker weet precies hoe een belangrijk proces werkt.',
		'Groei betekent automatisch meer administratief personeel.',
		'Veel werk bestaat uit kopiëren, controleren en doorzetten.',
	];

	$stappen = [
		['n' => '1', 't' => 'Analyseren',    'v' => 'Waar verdwijnt tijd?',                                   'b' => 'We kijken mee met hoe het werk nu gaat: wie doet wat, in welk systeem, hoe vaak.'],
		['n' => '2', 't' => 'Vereenvoudigen', 'v' => 'Welke stappen zijn overbodig of kunnen slimmer?',        'b' => 'Soms is een stap gewoon niet meer nodig. Dat is de goedkoopste verbetering.'],
		['n' => '3', 't' => 'Automatiseren', 'v' => 'Wat kan software of AI overnemen?',                       'b' => 'Pas nu kiezen we de techniek: een koppeling, een stukje maatwerk of AI.'],
		['n' => '4', 't' => 'Meten',         'v' => 'Wat levert de verandering echt op?',                      'b' => 'We kijken na de livegang hoeveel werk er is weggevallen, en wat daarna kan.'],
	];

	$scanPunten = [
		'de huidige werkwijze',
		'de medewerkers die erbij betrokken zijn',
		'de systemen die u gebruikt',
		'de stappen die nu met de hand gaan',
		'waar fouten ontstaan',
		'wat er te automatiseren valt',
		'hoeveel tijd dat kan schelen',
	];

	$voorbeelden = [
		['t' => 'Van inkomende e-mail naar voorbereide actie',
		 'voor' => 'Een medewerker leest een e-mail, zoekt de klant op, zoekt de bestelling op en bepaalt zelf de vervolgstap.',
		 'slim' => 'De e-mail wordt herkend, de klant- en bestelgegevens worden erbij gezocht en een antwoord of actie staat klaar.',
		 'res'  => 'De medewerker controleert en verstuurt, in plaats van alles zelf uit te zoeken.'],
		['t' => 'Van Excel naar een overzicht dat altijd klopt',
		 'voor' => 'Meerdere medewerkers houden dezelfde informatie bij in hun eigen spreadsheet.',
		 'slim' => 'De gegevens komen rechtstreeks uit de systemen waar ze toch al in staan.',
		 'res'  => 'Minder dubbel werk, minder fouten en één overzicht waar iedereen op kan vertrouwen.'],
		['t' => 'Van losse systemen naar één werkstroom',
		 'voor' => 'Gegevens worden met de hand van het ene systeem in het andere overgenomen.',
		 'slim' => 'De systemen wisselen de gegevens zelf uit, op het moment dat het nodig is.',
		 'res'  => 'Sneller verwerkt, minder administratie en geen overtypfouten meer.'],
	];

	$oplossingen = [
		['t' => 'Procesautomatisering',  'b' => 'Terugkerende handelingen laten we automatisch verlopen.',                              'href' => '/nl/processen-automatiseren'],
		['t' => 'Systeemkoppelingen',    'b' => 'CRM, webshop, administratie, ERP en andere systemen laten samenwerken.',               'href' => '/nl/api-koppelingen'],
		['t' => 'Maatwerksoftware',      'b' => 'Voor als standaardsoftware niet goed aansluit op hoe u werkt.',                       'href' => '/nl/maatwerk-webapplicatie'],
		['t' => 'AI',                    'b' => 'Voor lezen, sorteren, voorbereiden en controleren van informatie.',                    'href' => '/nl/slimmer-werken-met-ai'],
		['t' => 'Portals en dashboards', 'b' => 'Informatie, acties en inzicht op één plek, voor uw team of uw klanten.',               'href' => '/nl/klantportaal-laten-maken'],
	];

	// CookieGeregeld wijst naar de dienstpagina en niet meer naar cookie.betergeregeld.com: dat
	// subdomein serveert nog de oude V1-site (zie todo cookie-subdomein, gemeten 24-08-2026).
	$producten = [
		['t' => 'AI Telefoniste', 'b' => 'Neemt de telefoon op, beantwoordt vragen uit uw eigen kennisbank en stuurt na elk gesprek een verslag.', 'href' => '/nl/ai-telefoniste', 'track' => 'ai_telefoniste'],
		['t' => 'AccessGuard',    'b' => 'Overzicht van accounts, rechten, leveranciers en oude toegangen.',                                          'href' => 'https://toegang.betergeregeld.com', 'track' => 'accessguard'],
		['t' => 'CookieGeregeld', 'b' => 'Cookiebanner, categorieën en toestemming technisch goed ingesteld.',                                         'href' => '/nl/diensten/cookie-banner-instellen', 'track' => 'cookiegeregeld'],
		['t' => 'Gratis tools',   'b' => 'Praktische hulpmiddelen, van IBAN- en btw-check tot PDF samenvoegen.',                                       'href' => '/nl/tools', 'track' => 'tools'],
	];

	$waarom = [
		['t' => 'Eerst begrijpen, dan bouwen', 'b' => 'We beginnen bij het werk zelf. De techniek volgt uit wat er moet gebeuren, niet andersom.'],
		['t' => 'Ervaring uit de praktijk',    'b' => 'We automatiseren niet alleen voor klanten. In onze eigen bedrijven doen we het elke dag.'],
		['t' => 'Klein beginnen',              'b' => 'Geen groot IT-project vooraf. Eerst laten zien dat één proces beter kan.'],
		['t' => 'Blijven verbeteren',          'b' => 'Na de livegang kijken we wat het oplevert en wat de volgende logische stap is.'],
	];

	$faq = [
		['q' => 'Wanneer is een proces interessant om te automatiseren?',
		 'a' => 'Als het vaak gebeurt, steeds op dezelfde manier gaat en nu tijd kost of fouten oplevert. Denk aan gegevens overtypen, mails sorteren of een wekelijkse rapportage. Hoe vaker het terugkomt, hoe sneller het zich terugverdient.'],
		['q' => 'Is automatisering alleen interessant voor grote bedrijven?',
		 'a' => 'Nee. Juist in een klein team valt het op als iemand elke dag een uur kwijt is aan handwerk. Een kleine koppeling of een slimme stap kan dan al veel schelen.'],
		['q' => 'Moeten onze bestaande systemen vervangen worden?',
		 'a' => 'Meestal niet. We koppelen het liefst aan wat u al gebruikt. Vervangen adviseren we alleen als een systeem echt in de weg zit.'],
		['q' => 'Is maatwerksoftware altijd nodig?',
		 'a' => 'Nee. Vaak is een koppeling tussen bestaande pakketten of een instelling die nog niet gebruikt wordt genoeg. Maatwerk bouwen we als standaardsoftware niet past.'],
		['q' => 'Waar kan AI binnen ons bedrijf echt helpen?',
		 'a' => 'Bij werk waarin veel gelezen en uitgezocht wordt: e-mails en documenten sorteren, gegevens eruit halen, een concept-antwoord voorbereiden of controleren of een dossier compleet is. Een medewerker blijft controleren.'],
		['q' => 'Hoe bepalen jullie of automatisering rendabel is?',
		 'a' => 'We rekenen met wat het proces nu kost: hoe vaak het gebeurt, hoe lang het duurt en wie het doet. Daar zetten we de bouw- en beheerkosten tegenover. Is het verschil te klein, dan zeggen we dat.'],
		['q' => 'Kunnen we klein beginnen?',
		 'a' => 'Ja, dat is zelfs ons advies. We beginnen met één proces. Werkt dat, dan kijken we samen naar het volgende.'],
		['q' => 'Wat kost automatisering?',
		 'a' => 'Dat hangt af van het proces en de systemen. Een eenvoudige koppeling is iets anders dan een maatwerkapplicatie. Na de ProcesScan krijgt u een concrete inschatting, voordat u ergens aan vastzit.'],
	];

	$ctaScan    = '/nl/contact?topic=processcan';
	$ctaAfspraak = '/nl/afspraak?onderwerp=processcan';
@endphp

@section('title', 'Bedrijfsprocessen automatiseren | Beter Geregeld')
@section('description', 'Minder handmatig werk en minder fouten. Wij automatiseren bedrijfsprocessen met koppelingen, maatwerk en AI. Laat gratis één proces analyseren.')

@push('head')
	@php
		$__bg_faq_schema = [
			"\x40context" => 'https://schema.org',
			"\x40type"    => 'FAQPage',
			'mainEntity'  => array_map(fn ($v) => [
				"\x40type"         => 'Question',
				'name'           => $v['q'],
				'acceptedAnswer' => ["\x40type" => 'Answer', 'text' => $v['a']],
			], $faq),
		];
	@endphp
	<script type="application/ld+json">
	{!! json_encode($__bg_faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
	</script>
	<style>
		.hp-flow { display: grid; gap: 1rem; }
		@media (min-width: 768px) { .hp-flow { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
		.hp-flow-step { position: relative; }
		@media (min-width: 768px) {
			.hp-flow-step:not(:last-child)::after {
				content: ''; position: absolute; top: 2.1rem; right: -0.85rem; width: 0.7rem; height: 0.7rem;
				border-top: 2px solid var(--color-accent); border-right: 2px solid var(--color-accent);
				transform: rotate(45deg); z-index: 1;
			}
		}
		.hp-num { width: 2.5rem; height: 2.5rem; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center;
			font-weight: 800; background: var(--color-accent-soft); color: var(--color-accent-hover); }
		.hp-calc input { width: 100%; }
		.hp-case dt { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--color-ink-soft); }
		.hp-case dd { margin: 0.25rem 0 1rem; line-height: 1.6; color: var(--color-ink-muted); font-size: 0.9rem; }
		.hp-case dd:last-child { margin-bottom: 0; color: var(--color-ink); font-weight: 600; }
		.hp-diagram text { font-family: inherit; }
	</style>
@endpush

@section('content')

{{-- ============ 1. HERO ============ --}}
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-60" aria-hidden="true"></div>
	<div class="relative max-w-[1200px] mx-auto px-6 pt-14 pb-20 sm:py-28">
		<div class="grid lg:grid-cols-12 gap-12 items-center">
			<div class="lg:col-span-7">
				<span class="pill pill-dark mb-6 max-w-full">
					<span class="w-1.5 h-1.5 rounded-full bg-[color:var(--color-accent)]"></span>
					Minder handwerk, minder fouten
				</span>
				<h1 class="display-1 mb-6">
					Uw bedrijf groeit. <span class="accent-word">Uw administratie hoeft dat niet te doen.</span>
				</h1>
				<p class="text-lg sm:text-xl text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl mb-8">
					Wij zoeken waar medewerkers onnodig tijd kwijt zijn aan handmatig werk, losse systemen en terugkerende administratie. Dat automatiseren we met slimme software, koppelingen en AI.
				</p>
				<div class="flex flex-col sm:flex-row flex-wrap gap-3 mb-6">
					<a href="{{ $ctaScan }}" class="btn-accent justify-center" data-track="homepage_process_scan_hero">
						Laat gratis één proces analyseren
						<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</a>
					<a href="#praktijkvoorbeelden" class="btn-ghost-light justify-center" data-track="homepage_cases_click">Bekijk praktijkvoorbeelden</a>
				</div>
				<p class="text-sm text-[color:var(--color-on-dark-soft)]">
					Geen ingewikkeld ICT-verhaal. We kijken eerst naar één concreet bedrijfsproces.
				</p>
			</div>

			{{-- Procesflow als illustratie, geen stockfoto. Puur decoratief: de tekst ernaast zegt het al. --}}
			<div class="lg:col-span-5 hidden sm:block" aria-hidden="true">
				<svg class="hp-diagram w-full h-auto" viewBox="0 0 420 380" fill="none">
					<g font-size="13" font-weight="600">
						<rect x="10" y="12" width="240" height="56" rx="14" fill="rgba(255,255,255,0.06)" stroke="rgba(255,255,255,0.18)"/>
						<text x="30" y="36" fill="rgba(255,255,255,0.55)" font-size="10" letter-spacing="1.2">VOORHEEN</text>
						<text x="30" y="55" fill="#fff">E-mail lezen en uitzoeken</text>

						<rect x="40" y="94" width="240" height="56" rx="14" fill="rgba(255,255,255,0.06)" stroke="rgba(255,255,255,0.18)"/>
						<text x="60" y="118" fill="rgba(255,255,255,0.55)" font-size="10" letter-spacing="1.2">VOORHEEN</text>
						<text x="60" y="137" fill="#fff">Gegevens overtypen</text>

						<path d="M160 160 C 160 190, 250 190, 250 214" stroke="#14B8A6" stroke-width="2" stroke-dasharray="5 5"/>
						<path d="M244 206 l6 10 l6 -10" stroke="#14B8A6" stroke-width="2" fill="none"/>

						<rect x="120" y="222" width="290" height="60" rx="14" fill="rgba(20,184,166,0.16)" stroke="#14B8A6"/>
						<text x="140" y="247" fill="#5EEAD4" font-size="10" letter-spacing="1.2">AUTOMATISCH</text>
						<text x="140" y="267" fill="#fff">Gegevens erbij, actie klaargezet</text>

						<path d="M265 290 L265 316" stroke="#14B8A6" stroke-width="2"/>
						<path d="M259 308 l6 10 l6 -10" stroke="#14B8A6" stroke-width="2" fill="none"/>

						<rect x="150" y="324" width="260" height="48" rx="14" fill="#fff"/>
						<circle cx="174" cy="348" r="9" fill="#14B8A6"/>
						<path d="M169 348 l4 4 l7 -8" stroke="#fff" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
						<text x="192" y="353" fill="#0A0F1F">Medewerker controleert</text>
					</g>
				</svg>
			</div>
		</div>
	</div>
</section>

{{-- ============ 2. HERKENNING ============ --}}
<section class="py-16 sm:py-20 border-b border-[color:var(--color-line)]">
	<div class="max-w-[1200px] mx-auto px-6">
		<h2 class="display-2 mb-10 max-w-2xl">Herkent u dit in uw bedrijf?</h2>
		<ul class="grid sm:grid-cols-2 gap-4">
			@foreach ($herkenning as $regel)
				<li class="card flex items-start gap-3">
					<svg class="w-5 h-5 mt-0.5 shrink-0 text-[color:var(--color-accent)]" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 8.5l3.5 3.5L13 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					<span class="leading-relaxed">{{ $regel }}</span>
				</li>
			@endforeach
		</ul>
		<div class="mt-10 flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6">
			<p class="text-lg font-bold">Dan zit er waarschijnlijk automatiseringspotentieel in uw organisatie.</p>
			<a href="{{ $ctaScan }}" class="btn-dark justify-center shrink-0" data-track="homepage_process_scan_herkenning">
				Laat ons meekijken
				<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</a>
		</div>
	</div>
</section>

{{-- ============ 3. AANPAK ============ --}}
<section class="bg-[color:var(--color-surface)] py-16 sm:py-20">
	<div class="max-w-[1200px] mx-auto px-6">
		<div class="max-w-3xl mb-12">
			<span class="pill pill-ink mb-3">Onze aanpak</span>
			<h2 class="display-2 mb-5">Eerst het proces. Daarna pas de techniek.</h2>
			<p class="text-lg text-[color:var(--color-ink-muted)] leading-relaxed">
				Wij beginnen niet met de vraag welke software u nodig heeft. Eerst willen we begrijpen hoe het werk nu gebeurt, en waarom.
			</p>
		</div>
		<ol class="hp-flow">
			@foreach ($stappen as $st)
				<li class="card hp-flow-step">
					<span class="hp-num mb-4">{{ $st['n'] }}</span>
					<h3 class="font-bold text-lg mb-1">{{ $st['t'] }}</h3>
					<p class="font-semibold text-sm mb-2">{{ $st['v'] }}</p>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $st['b'] }}</p>
				</li>
			@endforeach
		</ol>
		<p class="mt-6 text-sm text-[color:var(--color-ink-muted)]">
			Na stap 4 begint het opnieuw: wat we meten, bepaalt de volgende verbetering.
		</p>
	</div>
</section>

{{-- ============ 4. PROCESSCAN ============ --}}
<section id="processcan" class="section-dark relative overflow-hidden scroll-mt-24">
	<div class="absolute inset-0 grid-pattern opacity-40" aria-hidden="true"></div>
	<div class="relative max-w-[1200px] mx-auto px-6 py-16 sm:py-24">
		<div class="grid lg:grid-cols-12 gap-10 items-start">
			<div class="lg:col-span-6">
				<span class="pill pill-dark mb-5">
					<span class="w-1.5 h-1.5 rounded-full bg-[color:var(--color-accent)]"></span>
					ProcesScan
				</span>
				<h2 class="display-2 mb-6 text-white">Laat één proces <span class="accent-word">gratis analyseren.</span></h2>
				<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed mb-4">
					Heeft u een proces waarvan u regelmatig denkt: "Waarom doen we dit eigenlijk nog met de hand?" Laat het ons zien.
				</p>
				<p class="text-[color:var(--color-on-dark-muted)] leading-relaxed mb-8">
					Na de ProcesScan weet u of automatisering zinvol is en wat een logische volgende stap kan zijn.
				</p>
				<div class="flex flex-col sm:flex-row flex-wrap gap-3 mb-4">
					<a href="{{ $ctaAfspraak }}" class="btn-accent justify-center" data-track="homepage_process_scan_mid">
						Plan mijn ProcesScan
						<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</a>
					<a href="{{ $ctaScan }}" class="btn-ghost-light justify-center" data-track="homepage_process_scan_mid_bericht">Liever eerst een bericht sturen</a>
				</div>
				<p class="text-sm text-[color:var(--color-on-dark-soft)]">Vrijblijvend. U zit niet vast aan een softwareproject.</p>
			</div>
			<div class="lg:col-span-6">
				<div class="rounded-2xl border border-white/10 bg-white/5 p-6 sm:p-8">
					<h3 class="text-xs font-semibold uppercase tracking-wider text-[color:var(--color-on-dark-soft)] mb-5">Waar we tijdens de scan naar kijken</h3>
					<ul class="space-y-3">
						@foreach ($scanPunten as $punt)
							<li class="flex items-start gap-3 text-white">
								<svg class="w-5 h-5 mt-0.5 shrink-0 text-[color:var(--color-accent)]" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 8.5l3.5 3.5L13 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
								<span class="leading-relaxed">{{ ucfirst($punt) }}</span>
							</li>
						@endforeach
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>

{{-- ============ 5. PRAKTIJKVOORBEELDEN ============
     Typische situaties, geen klantcases: er zijn nog geen openbare cases met toestemming.
     Daarom geen namen en geen cijfers. Een knop "Meer praktijkvoorbeelden" komt pas als er
     een casepagina is. --}}
<section id="praktijkvoorbeelden" class="py-16 sm:py-20 scroll-mt-24">
	<div class="max-w-[1200px] mx-auto px-6">
		<div class="max-w-3xl mb-12">
			<span class="pill pill-teal mb-3">Praktijkvoorbeelden</span>
			<h2 class="display-2 mb-4">Zo ziet slimmer werken er in de praktijk uit.</h2>
			<p class="text-[color:var(--color-ink-muted)] leading-relaxed">Drie situaties die we vaak tegenkomen, en wat er dan verandert.</p>
		</div>
		<div class="grid md:grid-cols-3 gap-5">
			@foreach ($voorbeelden as $vb)
				<article class="card hp-case">
					<h3 class="font-bold text-lg mb-5 leading-snug">{{ $vb['t'] }}</h3>
					<dl>
						<dt>Voorheen</dt>
						<dd>{{ $vb['voor'] }}</dd>
						<dt>Slimmer</dt>
						<dd>{{ $vb['slim'] }}</dd>
						<dt>Resultaat</dt>
						<dd>{{ $vb['res'] }}</dd>
					</dl>
				</article>
			@endforeach
		</div>
	</div>
</section>

{{-- ============ 6. TIJDVERLIES / REKENVOORBEELD ============
     Rekensom (gecontroleerd 12-10-2026): 3 medewerkers x 20 min = 60 min per dag = 1 uur.
     1 uur x 220 werkdagen = 220 uur per jaar; x EUR 40 = EUR 8.800. Daarom "bijna 9.000" en
     niet de 10.000 uit de opdracht. De rekenhulp rekent met dezelfde 220 werkdagen. Zonder
     JavaScript blijft het vaste voorbeeld gewoon staan. --}}
<section class="bg-[color:var(--color-surface)] py-16 sm:py-20 border-y border-[color:var(--color-line)]">
	<div class="max-w-[1200px] mx-auto px-6">
		<div class="grid lg:grid-cols-12 gap-10 items-start">
			<div class="lg:col-span-6">
				<h2 class="display-2 mb-5">Kleine handelingen worden op jaarbasis groot.</h2>
				<p class="text-lg text-[color:var(--color-ink-muted)] leading-relaxed mb-6">
					Werk dat iedere dag maar een paar minuten kost, kan op jaarbasis honderden uren opslokken.
				</p>
				<div class="card">
					<p class="text-xs font-semibold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">Rekenvoorbeeld</p>
					<p class="leading-relaxed">
						Drie medewerkers zijn elk 20 minuten per dag kwijt aan hetzelfde handwerk. Samen is dat een uur per dag. Over 220 werkdagen is dat 220 uur per jaar. Bij € 40 interne kosten per uur gaat het om <strong>bijna € 9.000 per jaar</strong>.
					</p>
				</div>
			</div>
			<div class="lg:col-span-6">
				<form class="card hp-calc" id="hp-calc" aria-labelledby="hp-calc-titel" onsubmit="return false">
					<h3 id="hp-calc-titel" class="font-bold text-lg mb-1">Reken het voor uw eigen proces uit</h3>
					<p class="text-sm text-[color:var(--color-ink-muted)] mb-5">We rekenen met 220 werkdagen per jaar.</p>
					<div class="grid sm:grid-cols-3 gap-4 mb-6">
						<label class="block text-sm font-semibold">Medewerkers
							<input type="number" name="mensen" min="1" max="500" step="1" value="3" inputmode="numeric" class="field-input mt-1">
						</label>
						<label class="block text-sm font-semibold">Minuten per dag
							<input type="number" name="minuten" min="1" max="480" step="1" value="20" inputmode="numeric" class="field-input mt-1">
						</label>
						<label class="block text-sm font-semibold">Kosten per uur (€)
							<input type="number" name="tarief" min="1" max="500" step="1" value="40" inputmode="numeric" class="field-input mt-1">
						</label>
					</div>
					<div class="rounded-xl bg-[color:var(--color-accent-soft)] p-5" aria-live="polite">
						<div class="text-sm text-[color:var(--color-ink-muted)]">Per jaar</div>
						<div class="text-3xl font-black tracking-tight"><span data-uitkomst="euro">€ 8.800</span></div>
						<div class="text-sm text-[color:var(--color-ink-muted)] mt-1"><span data-uitkomst="uren">220</span> uur handwerk</div>
					</div>
					<a href="{{ $ctaScan }}" class="btn-dark justify-center mt-6 w-full sm:w-auto" data-track="homepage_roi_click">
						Laat dit proces analyseren
						<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</a>
				</form>
			</div>
		</div>
	</div>
</section>

{{-- ============ 7. HOE WE HET OPLOSSEN ============ --}}
<section class="py-16 sm:py-20">
	<div class="max-w-[1200px] mx-auto px-6">
		<div class="max-w-3xl mb-12">
			<span class="pill pill-ink mb-3">Hoe we het oplossen</span>
			<h2 class="display-2">De oplossing hangt af van het probleem.</h2>
		</div>
		<div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
			@foreach ($oplossingen as $op)
				<a href="{{ $op['href'] }}" class="card card-accent group block">
					<h3 class="font-bold mb-2 flex items-center justify-between gap-2">
						{{ $op['t'] }}
						<svg class="w-4 h-4 shrink-0 text-[color:var(--color-ink-soft)] group-hover:text-[color:var(--color-accent)] transition" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</h3>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $op['b'] }}</p>
				</a>
			@endforeach
		</div>
		<p class="mt-8 text-lg font-semibold max-w-3xl">We kiezen geen techniek voordat we begrijpen welk probleem opgelost moet worden.</p>
		<a href="/nl/diensten" class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-[color:var(--color-accent-hover)] hover:gap-2.5 transition-all">
			Alle diensten
			<svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</a>
	</div>
</section>

{{-- ============ 8. BESTAANDE OPLOSSINGEN ============
     Lager op de pagina dan voorheen: de AI Telefoniste stond direct onder de hero
     (besluit 11-09-2026) en concurreerde daar met de ProcesScan. Het menu houdt haar
     prominent; hier staat ze als eerste kaart. --}}
<section class="bg-[color:var(--color-surface)] py-16 sm:py-20">
	<div class="max-w-[1200px] mx-auto px-6">
		<div class="max-w-3xl mb-10">
			<span class="pill pill-teal mb-3">Producten</span>
			<h2 class="display-2 mb-4">Oplossingen die we al hebben gebouwd.</h2>
			<p class="text-[color:var(--color-ink-muted)] leading-relaxed">Onderwerpen die we bij veel bedrijven zagen terugkomen, en die we daarom als eigen product hebben uitgewerkt.</p>
		</div>
		<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5">
			@foreach ($producten as $p)
				<a href="{{ $p['href'] }}" class="card card-accent group block" data-track="homepage_product_{{ $p['track'] }}">
					<h3 class="font-bold mb-1.5">{{ $p['t'] }}</h3>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $p['b'] }}</p>
					<span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-[color:var(--color-accent-hover)] group-hover:gap-2.5 transition-all">
						Bekijken
						<svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</span>
				</a>
			@endforeach
		</div>
	</div>
</section>

{{-- ============ 9. WAAROM BETER GEREGELD ============ --}}
<section class="py-16 sm:py-20">
	<div class="max-w-[1200px] mx-auto px-6">
		<h2 class="display-2 mb-10">Waarom Beter Geregeld?</h2>
		<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
			@foreach ($waarom as $w)
				<div class="card">
					<h3 class="font-bold mb-2">{{ $w['t'] }}</h3>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $w['b'] }}</p>
				</div>
			@endforeach
		</div>
	</div>
</section>

{{-- ============ 10. EIGEN PRAKTIJK ============
     Bewust zonder namen of cijfers van de eigen bedrijven: alleen wat al openbaar is
     (actief in automatisering sinds 1989, zie /nl/over). --}}
<section class="bg-[color:var(--color-accent-soft)] py-16 sm:py-20">
	<div class="max-w-[900px] mx-auto px-6">
		<span class="pill pill-ink mb-4">Eigen praktijk</span>
		<h2 class="display-2 mb-6">Automatiseren doen we zelf ook iedere dag.</h2>
		<div class="space-y-4 text-lg text-[color:var(--color-ink-muted)] leading-relaxed">
			<p>Beter Geregeld is ontstaan vanuit de praktijk. In onze eigen ondernemingen kwamen we steeds processen tegen die te veel handwerk vroegen.</p>
			<p>In plaats van ons daarbij neer te leggen, zijn we ze gaan automatiseren. Diezelfde manier van denken gebruiken we nu voor andere organisaties.</p>
		</div>
		<a href="/nl/over" class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-[color:var(--color-accent-hover)] hover:gap-2.5 transition-all">
			Meer over ons, sinds 1989
			<svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</a>
	</div>
</section>

{{-- ============ 11. WEBSITES EN WEBSHOPS ============
     Teruggebracht van een hele sectie naar één blok. De branchelinks blijven: dit is de enige
     plek waar de live branchesites een link vanaf het moederdomein krijgen
     (App\Support\ChannelNetwork, zie de toelichting in welcome.blade.php). --}}
<section class="py-16 sm:py-20 border-b border-[color:var(--color-line)]">
	<div class="max-w-[1200px] mx-auto px-6">
		<div class="grid lg:grid-cols-12 gap-8 items-start">
			<div class="lg:col-span-5">
				<h2 class="display-2 mb-4">Ook een website, webshop of klantportaal nodig?</h2>
				<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-6">
					Als uw online aanwezigheid onderdeel is van de oplossing, bouwen we ook websites, webshops en klantportalen.
				</p>
				<a href="https://jouw-bedrijfswebsite.nl" class="btn-outline" data-track="homepage_websites_click">
					Bekijk websites en webshops
					<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</a>
			</div>
			<div class="lg:col-span-7">
				<p class="text-xs font-semibold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">Websites per branche</p>
				<div class="flex flex-wrap gap-2">
					@foreach (\App\Support\ChannelNetwork::live('bedrijfswebsite') as $br)
						<a href="{{ $br['url'] }}" class="pill pill-ink" style="text-decoration:none">{{ $br['name'] }}</a>
					@endforeach
				</div>
			</div>
		</div>
	</div>
</section>

{{-- ============ 12. FAQ ============ --}}
<section class="py-16 sm:py-20">
	<div class="max-w-[1100px] mx-auto px-6">
		<h2 class="display-2 mb-10">Veelgestelde vragen</h2>
		<div class="grid md:grid-cols-2 gap-4 items-start">
			@foreach ($faq as $item)
				<details class="card group">
					<summary class="flex items-center justify-between gap-4 list-none cursor-pointer">
						<h3 class="font-bold text-base">{{ $item['q'] }}</h3>
						<span class="shrink-0 w-8 h-8 rounded-full border border-[color:var(--color-line)] flex items-center justify-center text-[color:var(--color-ink-muted)] group-open:bg-[color:var(--color-accent)] group-open:border-[color:var(--color-accent)] group-open:text-white group-open:rotate-45 transition" aria-hidden="true">+</span>
					</summary>
					<p class="mt-4 text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $item['a'] }}</p>
				</details>
			@endforeach
		</div>
	</div>
</section>

{{-- ============ 13. AFSLUITENDE CTA ============ --}}
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40" aria-hidden="true"></div>
	<div class="relative max-w-[1000px] mx-auto px-6 py-20 sm:py-24 text-center">
		<h2 class="display-1 mb-6">Welk werk zou in uw bedrijf eigenlijk <span class="accent-word">niet meer met de hand</span> moeten gebeuren?</h2>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] max-w-2xl mx-auto mb-8">
			Laat ons één proces zien. Wij vertellen waar we kansen zien en of automatisering zinvol is.
		</p>
		<div class="flex flex-col sm:flex-row flex-wrap gap-3 justify-center">
			<a href="{{ $ctaScan }}" class="btn-accent justify-center" data-track="homepage_process_scan_bottom">
				Laat mijn proces analyseren
				<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</a>
			<a href="/nl/afspraak?onderwerp=anders" class="btn-ghost-light justify-center" data-track="homepage_afspraak_bottom">Plan een kennismaking</a>
		</div>
	</div>
</section>

@endsection

@push('scripts')
<script>
(function () {
	// Klikmeting: één cta_click per klik op een element met data-track, naar onze eigen
	// event-log. Geen cookie, geen persoonsgegevens; alleen pad en de naam van de knop.
	// Bestaat er een dataLayer (GTM), dan gaat hetzelfde event daar ook heen.
	function meet(naam) {
		try {
			var payload = JSON.stringify({ e: 'cta_click', p: location.pathname, d: { cta: naam } });
			if (navigator.sendBeacon) {
				navigator.sendBeacon('/_ev', new Blob([payload], { type: 'application/json' }));
			} else {
				fetch('/_ev', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: payload, keepalive: true }).catch(function () {});
			}
			if (window.dataLayer && typeof window.dataLayer.push === 'function') {
				window.dataLayer.push({ event: 'cta_click', cta: naam });
			}
		} catch (e) {}
	}
	document.addEventListener('click', function (e) {
		var el = e.target.closest ? e.target.closest('[data-track]') : null;
		if (el) meet(el.getAttribute('data-track'));
	});

	// Rekenhulp: medewerkers x minuten per dag x 220 werkdagen x uurkosten.
	var form = document.getElementById('hp-calc');
	if (!form) return;
	var euro = form.querySelector('[data-uitkomst="euro"]');
	var uren = form.querySelector('[data-uitkomst="uren"]');
	var fmt = new Intl.NumberFormat('nl-NL', { maximumFractionDigits: 0 });
	var gemeten = false;
	function getal(naam) {
		var v = parseFloat(form.elements[naam].value);
		return isFinite(v) && v > 0 ? v : 0;
	}
	function reken() {
		var urenPerJaar = getal('mensen') * getal('minuten') / 60 * 220;
		euro.textContent = '€ ' + fmt.format(Math.round(urenPerJaar * getal('tarief') / 10) * 10);
		uren.textContent = fmt.format(Math.round(urenPerJaar));
	}
	form.addEventListener('input', function () {
		reken();
		if (!gemeten) { gemeten = true; meet('homepage_roi_gebruikt'); }
	});
	reken();
})();
</script>
@endpush
