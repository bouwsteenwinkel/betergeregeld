@extends('layouts.app')

@php
	$locale = app()->getLocale();
	$isEn = $locale !== 'nl';

	// De bronteksten staan hier als data, niet verweven door de opmaak: dat houdt
	// de NL- en EN-variant naast elkaar leesbaar en maakt een tekstwijziging een
	// kwestie van één regel. Zelfde patroon als welcome.blade.php.
	$vragen = [
		['nl' => 'Waar zitten de terugkerende handelingen?',                             'en' => 'Where are the repetitive actions?'],
		['nl' => 'Waar wordt informatie dubbel ingevoerd?',                              'en' => 'Where is information entered twice?'],
		['nl' => 'Welke controles kosten veel tijd?',                                    'en' => 'Which checks take up a lot of time?'],
		['nl' => 'Waar ontstaan wachttijden?',                                           'en' => 'Where do waiting times arise?'],
		['nl' => 'Welke werkzaamheden zijn afhankelijk van één medewerker?',             'en' => 'Which work depends on a single employee?'],
		['nl' => 'Welke taken kunnen door automatisering en AI worden overgenomen?',     'en' => 'Which tasks can automation and AI largely take over?'],
	];

	$toepassingen = [
		['icon' => '📧', 'nl' => 'Automatisch verwerken en classificeren van binnenkomende e-mails',             'en' => 'Automatically processing and classifying incoming email'],
		['icon' => '📄', 'nl' => 'Uitlezen en verwerken van documenten, formulieren en facturen',                'en' => 'Reading and processing documents, forms and invoices'],
		['icon' => '🔗', 'nl' => 'Automatisch verzamelen en combineren van informatie uit verschillende bronnen', 'en' => 'Automatically gathering and combining information from different sources'],
		['icon' => '✍️', 'nl' => 'Opstellen van conceptbrieven, rapportages en andere documenten',               'en' => 'Drafting letters, reports and other documents'],
		['icon' => '🔍', 'nl' => 'Controleren van dossiers op ontbrekende informatie',                           'en' => 'Checking case files for missing information'],
		['icon' => '⚙️', 'nl' => 'Automatiseren van administratieve handelingen',                                'en' => 'Automating administrative steps'],
		['icon' => '💬', 'nl' => 'Ondersteunen van medewerkers bij complexe informatievragen',                   'en' => 'Supporting employees with complex information questions'],
		['icon' => '🎧', 'nl' => 'Slimmer verwerken van klantvragen',                                            'en' => 'Handling customer questions more intelligently'],
		['icon' => '🚨', 'nl' => 'Automatisch signaleren van afwijkingen of benodigde acties',                   'en' => 'Automatically flagging deviations or required actions'],
		['icon' => '🧩', 'nl' => 'Koppelen van verschillende stappen en systemen binnen één proces',             'en' => 'Connecting separate steps and systems within one process'],
	];

	$opbrengsten = [
		['nl' => 'Kortere doorlooptijden',    'en' => 'Shorter lead times',         'd_nl' => 'Werk dat blijft liggen tot iemand tijd heeft, gaat direct door.',              'd_en' => 'Work that used to wait for someone with time now moves straight on.'],
		['nl' => 'Minder administratief werk', 'en' => 'Less administrative work',   'd_nl' => 'Overtypen, opzoeken en samenstellen verdwijnt uit de werkdag.',                'd_en' => 'Retyping, looking up and compiling disappears from the working day.'],
		['nl' => 'Minder fouten',             'en' => 'Fewer errors',               'd_nl' => 'Een systeem slaat geen stap over en wordt aan het eind van de dag niet moe.',   'd_en' => 'A system never skips a step and does not get tired at the end of the day.'],
		['nl' => 'Meer grip op processen',    'en' => 'More grip on processes',     'd_nl' => 'Zichtbaar waar het werk staat, in plaats van in een mailbox of in iemands hoofd.', 'd_en' => 'Visible where the work stands, instead of in an inbox or in someone&rsquo;s head.'],
	];

	$h1   = $isEn ? 'Working smarter with AI' : 'Slimmer werken met AI';
	$lead = $isEn
		? 'Less manual work. More overview. More capacity. We redesign processes and use AI and automation where they genuinely save time.'
		: 'Minder handwerk. Meer overzicht. Meer capaciteit. Wij richten processen opnieuw in en zetten AI en automatisering in waar het écht tijd bespaart.';
@endphp

@section('title', $h1 . ', ' . ($isEn ? 'process automation' : 'procesautomatisering') . ', Beter Geregeld ICT')
@section('description', $lead)

@push('head')
	@php
		$__bg_ai_service = [
			"\x40context"    => 'https://schema.org',
			"\x40type"       => 'Service',
			'name'        => $h1,
			'description' => $lead,
			'serviceType' => $isEn ? 'Process analysis, automation and AI' : 'Procesanalyse, automatisering en AI',
			'provider'    => [
				"\x40type" => 'Organization',
				"\x40id"   => url('/#organization'),
				'name'  => 'Beter Geregeld ICT',
				'url'   => url('/'),
			],
			'areaServed'  => ["\x40type" => 'Country', 'name' => 'Netherlands'],
			'inLanguage'  => $locale,
			'url'         => url(request()->path()),
		];
		$__bg_ai_crumbs = [
			"\x40context"        => 'https://schema.org',
			"\x40type"           => 'BreadcrumbList',
			'itemListElement' => [
				["\x40type" => 'ListItem', 'position' => 1, 'name' => 'Home',                          'item' => url("/{$locale}")],
				["\x40type" => 'ListItem', 'position' => 2, 'name' => $isEn ? 'Services' : 'Diensten', 'item' => url("/{$locale}/diensten")],
				["\x40type" => 'ListItem', 'position' => 3, 'name' => $h1,                             'item' => url(request()->path())],
			],
		];
	@endphp
	<script type="application/ld+json">
	{!! json_encode($__bg_ai_service, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
	</script>
	<script type="application/ld+json">
	{!! json_encode($__bg_ai_crumbs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
	</script>
@endpush

@section('content')

{{-- ============ HERO ============ --}}
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-60"></div>
	<div class="absolute -top-32 -right-32 w-[600px] h-[600px] rounded-full bg-[color:var(--color-accent)] opacity-10 blur-3xl"></div>

	<div class="relative max-w-[1400px] mx-auto px-6 pt-16 pb-24 sm:py-28">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-8 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<a href="/{{ $locale }}/diensten" class="hover:text-white">{{ $isEn ? 'Services' : 'Diensten' }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">{{ $h1 }}</span>
		</nav>

		<div class="max-w-4xl">
			<span class="pill pill-dark mb-6 max-w-full">
				<span class="w-1.5 h-1.5 rounded-full bg-[color:var(--color-accent)]"></span>
				{{ $isEn ? 'AI & automation' : 'AI & automatisering' }}
			</span>
			<h1 class="display-1 mb-6">
				{{ $isEn ? 'Working smarter' : 'Slimmer werken' }}
				<span class="accent-word">{{ $isEn ? 'with AI.' : 'met AI.' }}</span>
			</h1>
			<p class="text-lg sm:text-xl text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-3xl mb-6">
				{{ $isEn ? 'Less manual work. More overview. More capacity.' : 'Minder handwerk. Meer overzicht. Meer capaciteit.' }}
			</p>
			<p class="text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-3xl mb-4">
				{{ $isEn
					? 'In almost every organisation, processes have grown that once made sense but now cost unnecessary time.'
					: 'Binnen vrijwel iedere organisatie zijn processen ontstaan die ooit logisch waren, maar inmiddels onnodig veel tijd kosten.' }}
			</p>
			<p class="text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-3xl mb-8">
				{{ $isEn
					? 'Data is copied over by hand. Emails are read, assessed and forwarded. Documents are checked. Information is looked up across different systems. Reports are compiled manually. And every week again, employees spend time on work that is largely predictable and repetitive.'
					: 'Gegevens worden handmatig overgenomen. E-mails worden gelezen, beoordeeld en doorgestuurd. Documenten worden gecontroleerd. Informatie wordt opgezocht in verschillende systemen. Rapportages worden handmatig samengesteld. En medewerkers besteden iedere week opnieuw tijd aan werkzaamheden die grotendeels voorspelbaar en repeterend zijn.' }}
			</p>
			<p class="text-xl sm:text-2xl font-bold text-white mb-10">{{ $isEn ? 'That can be done smarter.' : 'Dat kan slimmer.' }}</p>

			<div class="flex flex-wrap gap-3">
				<a href="/{{ $locale }}/contact?topic=ai-procesanalyse" class="btn-accent">
					{{ $isEn ? 'Have your process analysed' : 'Laat uw proces analyseren' }}
					<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</a>
				<a href="#toepassingen" class="btn-ghost-light">{{ $isEn ? 'See what is possible' : 'Bekijk wat er mogelijk is' }}</a>
			</div>
		</div>
	</div>
</section>

{{-- ============ INTRO ============ --}}
<section class="py-20 border-b border-[color:var(--color-line)]">
	<div class="max-w-[1100px] mx-auto px-6">
		<span class="pill pill-ink mb-4">{{ $isEn ? 'What we do' : 'Wat we doen' }}</span>
		<h2 class="display-2 mb-6 max-w-3xl">
			{{ $isEn ? 'AI is not the goal.' : 'AI is niet het doel.' }}
			<span class="accent-word">{{ $isEn ? 'A better process is.' : 'Een beter proces wel.' }}</span>
		</h2>
		<div class="grid md:grid-cols-2 gap-8 text-[color:var(--color-ink-muted)] leading-relaxed">
			<p>
				{{ $isEn
					? 'Beter Geregeld helps organisations redesign their processes, making practical use of AI and automation.'
					: 'BeterGeregeld helpt organisaties processen opnieuw in te richten en maakt daarbij praktisch gebruik van AI en automatisering.' }}
			</p>
			<p>
				{{ $isEn
					? 'Not because AI is a goal in itself, but because it offers enormous scope to make processes faster, simpler and more efficient.'
					: 'Niet omdat AI een doel op zich is, maar omdat het enorme mogelijkheden biedt om processen sneller, eenvoudiger en efficiënter te maken.' }}
			</p>
		</div>
	</div>
</section>

{{-- ============ VAN UREN NAAR MINUTEN ============ --}}
<section class="bg-[color:var(--color-surface)] py-20">
	<div class="max-w-[1400px] mx-auto px-6">
		<div class="grid lg:grid-cols-12 gap-10 lg:gap-14">
			<div class="lg:col-span-5">
				<span class="pill pill-teal mb-3">{{ $isEn ? 'Analysis' : 'Analyse' }}</span>
				<h2 class="display-2 mb-5">{{ $isEn ? 'From hours of work to minutes.' : 'Van uren werk naar minuten.' }}</h2>
				<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-4">
					{{ $isEn
						? 'We look at how the work inside your organisation is actually carried out.'
						: 'Wij kijken naar hoe het werk binnen uw organisatie daadwerkelijk wordt uitgevoerd.' }}
				</p>
				<p class="text-[color:var(--color-ink-muted)] leading-relaxed">
					{{ $isEn
						? 'Then we look at how the process can be improved. That can range from relatively simple automation to a smart AI solution that reads, assesses, structures and processes information.'
						: 'Vervolgens kijken we hoe het proces beter kan. Dat kan variëren van een relatief eenvoudige automatisering tot een slimme AI-oplossing die informatie kan lezen, beoordelen, structureren en verwerken.' }}
				</p>
			</div>
			<div class="lg:col-span-7">
				<div class="card">
					<div class="text-xs font-semibold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-5">{{ $isEn ? 'The questions we ask' : 'De vragen die we stellen' }}</div>
					<ul class="space-y-4">
						@foreach ($vragen as $v)
							<li class="flex items-start gap-3">
								<span class="mt-0.5 shrink-0 w-6 h-6 rounded-full bg-[color:var(--color-accent-soft)] text-[color:var(--color-accent-hover)] flex items-center justify-center text-xs font-bold">{{ $loop->iteration }}</span>
								<span class="font-medium leading-relaxed">{{ $isEn ? $v['en'] : $v['nl'] }}</span>
							</li>
						@endforeach
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>

{{-- ============ TOEPASSINGEN ============ --}}
<section id="toepassingen" class="py-20 scroll-mt-24">
	<div class="max-w-[1400px] mx-auto px-6">
		<div class="flex items-end justify-between flex-wrap gap-4 mb-12">
			<div>
				<span class="pill pill-teal mb-3">{{ $isEn ? 'Applications' : 'Toepassingen' }}</span>
				<h2 class="display-2 max-w-2xl">{{ $isEn ? 'Think for example of:' : 'Denk bijvoorbeeld aan:' }}</h2>
			</div>
			<a href="/{{ $locale }}/contact?topic=ai-procesanalyse" class="text-sm font-semibold text-[color:var(--color-accent-hover)] inline-flex items-center gap-1.5 hover:gap-2.5 transition-all">
				{{ $isEn ? 'Discuss your process' : 'Bespreek uw proces' }}
				<svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</a>
		</div>
		<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
			@foreach ($toepassingen as $t)
				<div class="card">
					<div class="w-11 h-11 rounded-lg bg-[color:var(--color-accent-soft)] flex items-center justify-center text-xl mb-4">{{ $t['icon'] }}</div>
					<p class="font-semibold leading-relaxed">{{ $isEn ? $t['en'] : $t['nl'] }}</p>
				</div>
			@endforeach
		</div>
	</div>
</section>

{{-- ============ EERST HET PROCES ============ --}}
<section class="bg-[color:var(--color-surface)] py-20">
	<div class="max-w-[1100px] mx-auto px-6">
		<span class="pill pill-ink mb-4">{{ $isEn ? 'Approach' : 'Werkwijze' }}</span>
		<h2 class="display-2 mb-6 max-w-3xl">
			{{ $isEn ? 'First the process.' : 'Eerst het proces.' }}
			<span class="accent-word">{{ $isEn ? 'Then the technology.' : 'Dan de technologie.' }}</span>
		</h2>
		<p class="text-lg text-[color:var(--color-ink-muted)] leading-relaxed max-w-3xl mb-10">
			{{ $isEn
				? 'Automating an inefficient process does not automatically make it a good process. That is why we do not start with AI. We start with your organisation.'
				: 'Een inefficiënt proces automatiseren maakt het niet automatisch een goed proces. Daarom beginnen wij niet bij AI. We beginnen bij uw organisatie.' }}
		</p>
		<div class="grid md:grid-cols-3 gap-5">
			@foreach ([
				['n' => '01', 't_nl' => 'Proces in kaart',         't_en' => 'Map the process',    'b_nl' => 'We brengen het bestaande proces in kaart, stap voor stap, zoals het in de praktijk loopt.', 'b_en' => 'We map the existing process step by step, as it actually runs.'],
				['n' => '02', 't_nl' => 'Waar gaat tijd verloren', 't_en' => 'Where time is lost', 'b_nl' => 'We onderzoeken waar tijd en capaciteit verloren gaan en waar optimalisatie echt waarde oplevert.', 'b_en' => 'We investigate where time and capacity are lost, and where optimisation truly pays off.'],
				['n' => '03', 't_nl' => 'De juiste oplossing',     't_en' => 'The right solution', 'b_nl' => 'Pas daarna kijken we welke combinatie van procesverbetering, automatisering en AI het meest geschikt is.', 'b_en' => 'Only then do we decide which mix of process improvement, automation and AI fits best.'],
			] as $st)
				<div class="card">
					<div class="text-5xl font-black text-[color:var(--color-accent)] mb-3 leading-none">{{ $st['n'] }}</div>
					<h3 class="font-bold mb-2">{{ $isEn ? $st['t_en'] : $st['t_nl'] }}</h3>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $isEn ? $st['b_en'] : $st['b_nl'] }}</p>
				</div>
			@endforeach
		</div>
		<p class="text-[color:var(--color-ink-muted)] leading-relaxed max-w-3xl mt-8">
			{{ $isEn
				? 'Sometimes AI is the answer. Sometimes a simple automation. And regularly it is the combination that delivers the biggest result.'
				: 'Soms is AI de oplossing. Soms een eenvoudige automatisering. En regelmatig is het juist de combinatie die het grootste resultaat oplevert.' }}
		</p>
	</div>
</section>

{{-- ============ WAT LEVERT HET OP ============ --}}
<section class="py-20">
	<div class="max-w-[1400px] mx-auto px-6">
		<div class="text-center max-w-3xl mx-auto mb-14">
			<span class="pill pill-ink mb-3">{{ $isEn ? 'Result' : 'Resultaat' }}</span>
			<h2 class="display-2 mb-5">{{ $isEn ? 'What does it deliver?' : 'Wat levert het op?' }}</h2>
			<p class="text-lg text-[color:var(--color-ink-muted)] leading-relaxed">
				{{ $isEn
					? 'The goal is ultimately simple: getting more work done with less manual effort.'
					: 'Het doel is uiteindelijk eenvoudig: meer werk kunnen verzetten met minder handmatige inspanning.' }}
			</p>
		</div>
		<div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5">
			@foreach ($opbrengsten as $o)
				<div class="card card-accent">
					<h3 class="font-bold text-lg mb-2 leading-tight">{{ $isEn ? $o['en'] : $o['nl'] }}</h3>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{!! $isEn ? $o['d_en'] : $o['d_nl'] !!}</p>
				</div>
			@endforeach
		</div>
		<p class="text-[color:var(--color-ink-muted)] leading-relaxed max-w-3xl mx-auto text-center mt-10">
			{{ $isEn
				? 'Employees no longer have to spend their time on work a system performs perfectly well, and keep more time for the work that genuinely needs human knowledge, experience and attention.'
				: 'Medewerkers hoeven hun tijd niet langer te besteden aan werkzaamheden die een systeem uitstekend kan uitvoeren en houden daardoor meer tijd over voor het werk waarvoor menselijke kennis, ervaring en aandacht daadwerkelijk nodig zijn.' }}
		</p>
	</div>
</section>

{{-- ============ AI DIE ECHT IETS DOET ============ --}}
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1100px] mx-auto px-6 py-24">
		<span class="pill pill-dark mb-5">{{ $isEn ? 'Our view' : 'Onze visie' }}</span>
		<h2 class="display-2 mb-8 max-w-3xl">
			{{ $isEn ? 'AI that actually' : 'AI die daadwerkelijk' }}
			<span class="accent-word">{{ $isEn ? 'does something.' : 'iets doet.' }}</span>
		</h2>
		<div class="grid md:grid-cols-2 gap-8 text-[color:var(--color-on-dark-muted)] leading-relaxed">
			<div class="space-y-4">
				<p>
					{{ $isEn
						? 'We do not believe in implementing AI because it sounds modern.'
						: 'Wij geloven niet in AI implementeren omdat het modern klinkt.' }}
				</p>
				<p>
					{{ $isEn
						? 'Adding a chatbot to a website is easy. Redesigning a business process so that tens or hundreds of hours of work structurally disappear is something else entirely.'
						: 'Een chatbot toevoegen aan een website is eenvoudig. Een bedrijfsproces zo opnieuw ontwerpen dat er structureel tientallen of honderden uren werk verdwijnen, is iets anders.' }}
				</p>
			</div>
			<div class="space-y-4">
				<p class="text-white font-bold text-lg">
					{{ $isEn ? 'That is where the real value of AI lies for us.' : 'Daar ligt voor ons de echte waarde van AI.' }}
				</p>
				<p>
					{{ $isEn
						? 'We look for applications that demonstrably make a difference in time, cost, quality or capacity. No innovation project without a clear goal, but practical solutions for processes that cost unnecessary time today.'
						: 'We zoeken naar toepassingen die aantoonbaar verschil maken in tijd, kosten, kwaliteit of capaciteit. Geen innovatieproject zonder duidelijk doel, maar praktische oplossingen voor processen die vandaag onnodig veel tijd kosten.' }}
				</p>
			</div>
		</div>
	</div>
</section>

{{-- ============ VERSTOPTE TIJD ============ --}}
<section class="py-20">
	<div class="max-w-[1100px] mx-auto px-6">
		<div class="grid lg:grid-cols-12 gap-10 items-start">
			<div class="lg:col-span-7">
				<span class="pill pill-teal mb-3">{{ $isEn ? 'The maths' : 'De rekensom' }}</span>
				<h2 class="display-2 mb-5">{{ $isEn ? 'How much time is hidden in your organisation?' : 'Hoeveel tijd zit er verstopt in uw organisatie?' }}</h2>
				<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-4">
					{{ $isEn
						? 'Often it only becomes visible how much efficiency is possible once a process is examined step by step.'
						: 'Vaak is pas zichtbaar hoeveel efficiëntiewinst mogelijk is wanneer een proces stap voor stap wordt bekeken.' }}
				</p>
				<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-4">
					{{ $isEn
						? 'A five-minute action may not seem relevant. But when several employees carry it out dozens of times a week, it quickly adds up to hundreds of hours a year.'
						: 'Een handeling van vijf minuten lijkt misschien niet relevant. Maar wanneer die handeling door meerdere medewerkers tientallen keren per week wordt uitgevoerd, gaat het al snel om honderden uren per jaar.' }}
				</p>
				<p class="text-[color:var(--color-ink-muted)] leading-relaxed">
					{{ $isEn
						? 'And that is exactly where automation and AI can make a big difference.'
						: 'En juist daar kunnen automatisering en AI een groot verschil maken.' }}
				</p>
			</div>
			<div class="lg:col-span-5">
				<div class="card">
					<div class="text-xs font-semibold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-4">{{ $isEn ? 'A five-minute action' : 'Een handeling van vijf minuten' }}</div>
					<div class="space-y-4">
						<div class="flex items-baseline justify-between gap-4 border-b border-[color:var(--color-line)] pb-3">
							<span class="text-sm text-[color:var(--color-ink-muted)]">{{ $isEn ? '5 minutes, once' : '5 minuten, eenmalig' }}</span>
							<span class="text-lg font-bold">5 min</span>
						</div>
						<div class="flex items-baseline justify-between gap-4 border-b border-[color:var(--color-line)] pb-3">
							<span class="text-sm text-[color:var(--color-ink-muted)]">{{ $isEn ? '20 times a week' : '20 keer per week' }}</span>
							<span class="text-lg font-bold">{{ $isEn ? '1.7 hrs' : '1,7 uur' }}</span>
						</div>
						<div class="flex items-baseline justify-between gap-4 border-b border-[color:var(--color-line)] pb-3">
							<span class="text-sm text-[color:var(--color-ink-muted)]">{{ $isEn ? '4 employees' : '4 medewerkers' }}</span>
							<span class="text-lg font-bold">{{ $isEn ? '6.7 hrs' : '6,7 uur' }}</span>
						</div>
						<div class="flex items-baseline justify-between gap-4">
							<span class="text-sm font-semibold">{{ $isEn ? 'Per year' : 'Per jaar' }}</span>
							<span class="text-3xl font-black text-[color:var(--color-accent)]">{{ $isEn ? '347 hrs' : '347 uur' }}</span>
						</div>
					</div>
					<p class="text-xs text-[color:var(--color-ink-soft)] leading-relaxed mt-5">
						{{ $isEn
							? 'An example calculation, not a promise. What it is inside your organisation is exactly what the analysis makes visible.'
							: 'Een rekenvoorbeeld, geen belofte. Wat het binnen uw organisatie is, maakt de analyse zichtbaar.' }}
					</p>
				</div>
			</div>
		</div>
	</div>
</section>

{{-- ============ CTA ============ --}}
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="absolute -bottom-40 -left-40 w-[500px] h-[500px] rounded-full bg-[color:var(--color-accent)] opacity-10 blur-3xl"></div>
	<div class="relative max-w-[1100px] mx-auto px-6 py-24 text-center">
		<h2 class="display-1 mb-6">
			{{ $isEn ? 'Have your process' : 'Laat uw proces' }}
			<span class="accent-word">{{ $isEn ? 'analysed by us.' : 'door ons analyseren.' }}</span>
		</h2>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] max-w-2xl mx-auto mb-4">
			{{ $isEn
				? 'Tell us which process causes a lot of time, frustration or manual work. We investigate where it can be done smarter and what efficiency gain is possible.'
				: 'Vertel ons welk proces veel tijd, frustratie of handmatig werk veroorzaakt. Wij onderzoeken waar het slimmer kan en welke efficiëntiewinst mogelijk is.' }}
		</p>
		<p class="text-[color:var(--color-on-dark-soft)] max-w-2xl mx-auto mb-8">
			{{ $isEn
				? 'Beter Geregeld maps your processes and shows where AI and automation can genuinely save time, capacity and cost.'
				: 'BeterGeregeld brengt uw processen in kaart en laat zien waar AI en automatisering daadwerkelijk tijd, capaciteit en kosten kunnen besparen.' }}
		</p>
		<div class="flex flex-wrap gap-3 justify-center">
			<a href="/{{ $locale }}/contact?topic=ai-procesanalyse" class="btn-accent">
				{{ $isEn ? 'Request a process analysis' : 'Vraag een procesanalyse aan' }}
				<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</a>
			<a href="/{{ $locale }}/diensten" class="btn-ghost-light">{{ $isEn ? 'View all services' : 'Bekijk alle diensten' }}</a>
		</div>
	</div>
</section>

@endsection
