@extends('layouts.app')

@section('title', 'Beter Geregeld ICT – maatwerk websites & automatisering')
@section('description', 'Beter Geregeld ICT helpt bedrijven met maatwerk webapplicaties, klantportalen, API-koppelingen, procesautomatisering, beveiliging en technische SEO.')

@php
	$locale = app()->getLocale();
	// $isEn dient als binary-fallback voor de hardcoded NL/EN-content.
	// Voor DE/FR/ES geven we EN i.p.v. NL, beter te begrijpen voor
	// internationale bezoekers tot we per-locale-varianten toevoegen
	// (services_catalog.php + services_faq.php uitbreiden met _de/_fr/_es).
	$isEn = $locale !== 'nl';

	$services = [
		['slug' => 'cookie-banner-instellen',     'badge' => 'CookieGeregeld',   'title_nl' => 'Cookie banner laten instellen',      'title_en' => 'Cookie banner setup',              'desc_nl' => 'Categorieën, scripts en een nette technische basis voor consent.', 'desc_en' => 'Categories, scripts and a clean technical basis for consent.'],
		['slug' => 'mail-beveiliging-fix',        'badge' => 'Mail Security',    'title_nl' => 'E-mailbeveiliging instellen',         'title_en' => 'Email security setup',              'desc_nl' => 'SPF, DKIM en DMARC correct, voorkom spam, spoofing en slechte aflevering.', 'desc_en' => 'SPF, DKIM and DMARC done right, prevent spam, spoofing, bad delivery.'],
		['slug' => 'toegang-check',               'badge' => 'Access',           'title_nl' => 'Toegang check',                        'title_en' => 'Access review',                      'desc_nl' => 'Accounts, rechten, leveranciers en oude toegangen in kaart.', 'desc_en' => 'Accounts, permissions, vendors and stale access mapped.'],
		['slug' => 'website-meertalig-maken',     'badge' => 'Website',          'title_nl' => 'Website meertalig maken',              'title_en' => 'Make website multilingual',          'desc_nl' => 'Meertalig opzetten met sterke structuur, nette URLs en SEO.', 'desc_en' => 'Multilingual setup with strong structure, clean URLs and SEO.'],
		['slug' => '2fa-implementeren',           'badge' => 'Security',         'title_nl' => '2FA implementeren',                    'title_en' => 'Implement 2FA',                      'desc_nl' => 'Extra loginbeveiliging voor webshop, portal of admin-omgeving.', 'desc_en' => 'Extra login security for shop, portal or admin area.'],
		['slug' => 'seo-check',                   'badge' => 'SEO',              'title_nl' => 'SEO check & optimalisatie',            'title_en' => 'SEO check & optimisation',           'desc_nl' => 'Technische SEO, indexatie, structuur en concrete quick wins.', 'desc_en' => 'Technical SEO, indexation, structure and concrete quick wins.'],
		['slug' => 'website-snelheid-verbeteren', 'badge' => 'Performance',      'title_nl' => 'Website snelheid verbeteren',          'title_en' => 'Improve website speed',              'desc_nl' => 'Afbeeldingen, scripts, caching en server, sneller voor bezoekers.', 'desc_en' => 'Images, scripts, caching and server, faster for visitors.'],
		['slug' => 'website-onderhoud-uitbesteden','badge' => 'Onderhoud',       'title_nl' => 'Website onderhoud uitbesteden',        'title_en' => 'Outsource website maintenance',      'desc_nl' => 'Updates, controles en technisch beheer zonder er zelf achteraan te hoeven.', 'desc_en' => 'Updates, checks and technical management handled for you.'],
		['slug' => 'website-beveiligen',          'badge' => 'Beveiliging',      'title_nl' => 'Website beveiligen',                   'title_en' => 'Secure your website',                'desc_nl' => 'Zwakke instellingen, oude plugins en onduidelijke toegang aanpakken.', 'desc_en' => 'Address weak settings, outdated plugins and unclear access.'],
		['slug' => 'website-backup-en-herstel',   'badge' => 'Back-up',          'title_nl' => 'Website back-up en herstel',           'title_en' => 'Website backup & recovery',          'desc_nl' => 'Back-up en recovery goed geregeld, minder impact van fouten.', 'desc_en' => 'Backup and recovery sorted, less impact from mistakes.'],
		['slug' => 'wordpress-opschonen',         'badge' => 'WordPress',        'title_nl' => 'WordPress opschonen',                  'title_en' => 'Clean up WordPress',                 'desc_nl' => 'Oude plugins, dubbele functies en vervuiling opruimen.', 'desc_en' => 'Clear out old plugins, duplicates and clutter.'],
		['slug' => 'website-migratie-zonder-gedoe','badge' => 'Migratie',        'title_nl' => 'Website migratie zonder gedoe',        'title_en' => 'Website migration without hassle',   'desc_nl' => 'Veilig verhuizen naar nieuwe hosting zonder dataverlies of chaos.', 'desc_en' => 'Safely move to new hosting without data loss or chaos.'],
		['slug' => 'website-structuur-check',     'badge' => 'Structuur',        'title_nl' => 'Website structuur check',              'title_en' => 'Website structure check',            'desc_nl' => 'Sitemap, URL-structuur, interne links en SEO-basis.', 'desc_en' => 'Sitemap, URL structure, internal links and SEO basis.'],
	];

	$faq = [
		['q_nl' => 'Wat houdt een technische SEO check in?', 'q_en' => 'What does a technical SEO check involve?',
		 'a_nl' => 'Indexatie, metadata, headings, interne links, crawlbaarheid, canonicals, paginastructuur, snelheid en fouten die de vindbaarheid beperken.',
		 'a_en' => 'Indexation, metadata, headings, internal links, crawlability, canonicals, page structure, speed and errors that limit findability.'],
		['q_nl' => 'Wanneer is een maatwerk portal interessant?', 'q_en' => 'When does a custom portal make sense?',
		 'a_nl' => 'Zodra processen, rollen of koppelingen specifieker worden en standaard tools te veel beperkingen opleveren.',
		 'a_en' => 'Once processes, roles or integrations get specific and standard tools hit their limits.'],
		['q_nl' => 'Wanneer heb je een API-koppeling nodig?', 'q_en' => 'When do you need an API integration?',
		 'a_nl' => 'Als gegevens nu handmatig tussen systemen worden overgenomen of platforms niet goed samenwerken.',
		 'a_en' => 'When data is being copied manually between systems or platforms don\'t work together well.'],
		['q_nl' => 'Waarom zijn SPF, DKIM en DMARC belangrijk?', 'q_en' => 'Why do SPF, DKIM and DMARC matter?',
		 'a_nl' => 'Ze beschermen tegen spoofing en verhogen de kans dat legitieme e-mail netjes wordt afgeleverd.',
		 'a_en' => 'They protect against spoofing and increase the chance legit email gets delivered properly.'],
		['q_nl' => 'Hoe verbeter je website-snelheid?', 'q_en' => 'How do you improve website speed?',
		 'a_nl' => 'Meestal via afbeeldingen, caching, scripts, CSS, fonts, plug-ins en serverinstellingen.',
		 'a_en' => 'Usually via images, caching, scripts, CSS, fonts, plugins and server settings.'],
		['q_nl' => 'Wat levert procesautomatisering concreet op?', 'q_en' => 'What does automation actually deliver?',
		 'a_nl' => 'Minder handmatig werk, minder fouten, kortere doorlooptijden en meer grip op processen.',
		 'a_en' => 'Less manual work, fewer errors, shorter lead times and more grip on processes.'],
	];

	$expertise = ['Maatwerk webapplicaties', 'Klantportalen', 'Custom business portals', 'API integraties', 'ERP-koppelingen', 'Procesautomatisering', 'Performance optimalisatie', 'Technische SEO', 'WordPress optimalisatie', 'Toegangsbeheer', '2FA en beveiliging', 'Meertalige websites'];
@endphp

@push('head')
	@php
		$__bg_faq_schema = [
			"\x40context"   => 'https://schema.org',
			"\x40type"      => 'FAQPage',
			'mainEntity' => array_map(fn ($q) => [
				"\x40type"          => 'Question',
				'name'           => $isEn ? $q['q_en'] : $q['q_nl'],
				'acceptedAnswer' => [
					"\x40type" => 'Answer',
					'text'  => $isEn ? $q['a_en'] : $q['a_nl'],
				],
			], $faq ?? []),
		];
	@endphp
	@if (!empty($__bg_faq_schema['mainEntity']))
		<script type="application/ld+json">
		{!! json_encode($__bg_faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
		</script>
	@endif
@endpush

@section('content')

{{-- ============ HERO ============ --}}
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-60"></div>
	<div class="absolute -top-32 -right-32 w-[600px] h-[600px] rounded-full bg-[color:var(--color-accent)] opacity-10 blur-3xl"></div>
	<div class="absolute -bottom-40 -left-40 w-[500px] h-[500px] rounded-full bg-[color:var(--color-accent)] opacity-5 blur-3xl"></div>

	<div class="relative max-w-[1400px] mx-auto px-6 pt-16 pb-24 sm:py-32">
		<div class="max-w-4xl">
			<span class="pill pill-dark mb-6 max-w-full">
				<span class="w-1.5 h-1.5 rounded-full bg-[color:var(--color-accent)]"></span>
				{{ $isEn ? 'Since 1989: VOS, ICM, now Beter Geregeld' : 'Sinds 1989: VOS, ICM, nu Beter Geregeld' }}
			</span>
			<h1 class="display-1 mb-6">
				{{ $isEn ? 'We build what' : 'Wij bouwen wat' }}
				<span class="accent-word">{{ $isEn ? 'works.' : 'werkt.' }}</span><br>
				{{ $isEn ? 'And stay reachable.' : 'En blijven bereikbaar.' }}
			</h1>
			<p class="text-lg sm:text-xl text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl mb-10">
				{{ $isEn
					? 'Custom web applications, customer portals, API integrations and process automation. For organisations stuck with separate systems that don\'t talk to each other.'
					: 'Maatwerk webapplicaties, klantportalen, API-koppelingen en procesautomatisering. Voor organisaties die vastlopen op losse systemen die niet met elkaar praten.' }}
			</p>
			<div class="flex flex-wrap gap-3 mb-12">
				<a href="/{{ $locale }}/diensten" class="btn-accent">
					{{ $isEn ? 'Explore services' : 'Bekijk onze diensten' }}
					<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</a>
				<a href="/{{ $locale }}/contact" class="btn-ghost-light">
					{{ $isEn ? 'Book a free intro call' : 'Plan een gratis adviesgesprek' }}
				</a>
			</div>
			<div class="grid grid-cols-2 sm:grid-cols-4 gap-6 pt-8 border-t border-[color:var(--color-on-dark-line)]">
				<div>
					<div class="text-3xl font-bold text-white">35+</div>
					<div class="text-xs uppercase tracking-wider text-[color:var(--color-on-dark-soft)] mt-1">{{ $isEn ? 'Years in tech' : 'Jaar ervaring' }}</div>
				</div>
				<div>
					<div class="text-3xl font-bold text-white">13</div>
					<div class="text-xs uppercase tracking-wider text-[color:var(--color-on-dark-soft)] mt-1">{{ $isEn ? 'Services' : 'Diensten' }}</div>
				</div>
				<div>
					<div class="text-3xl font-bold text-white">1:1</div>
					<div class="text-xs uppercase tracking-wider text-[color:var(--color-on-dark-soft)] mt-1">{{ $isEn ? 'Direct contact' : 'Directe lijn' }}</div>
				</div>
				<div>
					<div class="text-3xl font-bold text-white">NL</div>
					<div class="text-xs uppercase tracking-wider text-[color:var(--color-on-dark-soft)] mt-1">{{ $isEn ? 'Based in Bussum' : 'Gevestigd Bussum' }}</div>
				</div>
			</div>
		</div>
	</div>
</section>

{{-- ============ WEBSITES VOOR ELKE BRANCHE (jouw-bedrijfswebsite.nl) ============ --}}
<section class="py-20 border-b border-[color:var(--color-line)]">
	<div class="max-w-[1400px] mx-auto px-6">
		<div class="grid lg:grid-cols-12 gap-10 lg:gap-14 items-center">
			<div class="lg:col-span-12">
				<span class="pill pill-teal mb-3">{{ $isEn ? 'Websites & webshops' : 'Websites & webshops' }}</span>
				<h2 class="display-2 max-w-xl">{{ $isEn ? 'A professional website for every industry.' : 'Een professionele website voor élke branche.' }}</h2>
				<p class="text-[color:var(--color-ink-muted)] max-w-lg mt-4 leading-relaxed">
					{{ $isEn
						? 'From roofers to hairdressers: we build sharp websites, webshops and client portals, tailored to your trade and your region. Fixed price, one point of contact, arranged by phone and online.'
						: 'Van dakdekker tot kapper: wij bouwen strakke websites, webshops en klantportalen, afgestemd op jouw vak en jouw regio. Vaste prijs, één vast aanspreekpunt, en alles telefonisch en online geregeld.' }}
				</p>
				<div class="flex flex-wrap gap-2 mt-6">
					@foreach ([['nl' => 'Dakdekker', 'en' => 'Roofer'], ['nl' => 'Installateur', 'en' => 'Installer'], ['nl' => 'Kapper', 'en' => 'Hairdresser'], ['nl' => 'Hovenier', 'en' => 'Gardener'], ['nl' => 'Garage', 'en' => 'Garage'], ['nl' => 'Bakkerij', 'en' => 'Bakery'], ['nl' => 'Advocaat', 'en' => 'Lawyer'], ['nl' => 'Fysiotherapeut', 'en' => 'Physio']] as $br)
						<span class="pill pill-ink">{{ $isEn ? $br['en'] : $br['nl'] }}</span>
					@endforeach
					<span class="pill">{{ $isEn ? '+ many more' : '+ veel meer' }}</span>
				</div>
				<div class="flex flex-wrap gap-3 mt-8">
					<a href="https://jouw-bedrijfswebsite.nl/voorbeeld-maken" class="btn-accent">
						{{ $isEn ? 'See a free example' : 'Maak een gratis voorbeeld' }}
						<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</a>
					<a href="https://jouw-bedrijfswebsite.nl" class="btn-outline">jouw-bedrijfswebsite.nl</a>
				</div>
			</div>
			<div class="lg:col-span-12 mt-2">
				<div class="card">
					<div class="text-xs font-semibold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-4">{{ $isEn ? 'Grows with you' : 'Groeit met je mee' }}</div>
						<div class="flex flex-wrap gap-x-8 gap-y-5">
					@foreach ([['icon' => '🌐', 'nl' => 'Website', 'en' => 'Website', 'nld' => 'Vindbaar en professioneel', 'end' => 'Findable and professional'], ['icon' => '🛍️', 'nl' => 'Webshop', 'en' => 'Webshop', 'nld' => 'Online verkopen', 'end' => 'Sell online'], ['icon' => '🔐', 'nl' => 'Klantenportaal', 'en' => 'Client portal', 'nld' => 'Klanten regelen het zelf', 'end' => 'Clients self-serve'], ['icon' => '⚙️', 'nl' => 'Automatisering', 'en' => 'Automation', 'nld' => 'Processen die zichzelf doen', 'end' => 'Processes that run themselves'], ['icon' => '✨', 'nl' => 'AI', 'en' => 'AI', 'nld' => 'Slimme assistentie', 'end' => 'Smart assistance']] as $i => $step)
						<div class="flex items-center gap-3" style="flex:1 1 160px">
							<div class="w-9 h-9 rounded-lg bg-[color:var(--color-accent-soft)] flex items-center justify-center text-base shrink-0">{{ $step['icon'] }}</div>
							<div>
								<div class="font-bold text-sm">{{ $isEn ? $step['en'] : $step['nl'] }}</div>
								<div class="text-xs text-[color:var(--color-ink-muted)]">{{ $isEn ? $step['end'] : $step['nld'] }}</div>
							</div>
						</div>
					@endforeach
						</div>
				</div>
			</div>
		</div>
	</div>
</section>

{{-- ============ FEATURED PRODUCTS ============ --}}
<section class="bg-[color:var(--color-surface)] py-20">
	<div class="max-w-[1400px] mx-auto px-6">
		<div class="flex items-end justify-between flex-wrap gap-4 mb-10">
			<div>
				<span class="pill pill-teal mb-3">{{ $isEn ? 'Our products' : 'Onze producten' }}</span>
				<h2 class="display-2 max-w-2xl">{{ $isEn ? 'Ready-made solutions, ready to use.' : 'Kant-en-klare oplossingen, direct inzetbaar.' }}</h2>
			</div>
			<p class="text-[color:var(--color-ink-muted)] max-w-md">
				{{ $isEn ? 'Our own products for topics we see come up time and again, from consent to access and free tooling.' : 'Eigen producten voor onderwerpen die we steeds zien terugkomen, van consent tot toegang en gratis tooling.' }}
			</p>
		</div>
		<div class="grid md:grid-cols-2 xl:grid-cols-4 gap-5">
			@foreach ([
				['href' => 'https://cookie.betergeregeld.com', 'title' => 'CookieGeregeld', 'nl' => 'Cookie banner, categorieën en consent technisch goed geregeld.', 'en' => 'Cookie banner, categories and consent, technically sorted.', 'icon' => '🍪'],
				['href' => 'https://toegang.betergeregeld.com', 'title' => 'AccessGuard', 'nl' => 'Grip op accounts, rechten, leveranciers en oude toegangen.', 'en' => 'Grip on accounts, permissions, vendors and stale access.', 'icon' => '🔐'],
				['href' => '/' . $locale . '/tools/iban-check', 'title' => $isEn ? 'Free tools' : 'Gratis tools', 'nl' => 'Praktische hulpmiddelen voor websites, processen en basischecks.', 'en' => 'Practical helpers for websites, processes and baseline checks.', 'icon' => '⚡'],
				['href' => '/' . $locale . '/contact', 'title' => $isEn ? 'Custom work' : 'Maatwerk', 'nl' => 'Van analyse tot uitvoering, optimalisatie en doorontwikkeling.', 'en' => 'From analysis to execution, optimisation and ongoing development.', 'icon' => '⚙️'],
			] as $b)
				<a href="{{ $b['href'] }}" class="card card-accent group">
					<div class="w-11 h-11 rounded-lg bg-[color:var(--color-accent-soft)] flex items-center justify-center text-xl mb-4">{{ $b['icon'] }}</div>
					<div class="font-bold mb-1.5">{{ $b['title'] }}</div>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $isEn ? $b['en'] : $b['nl'] }}</p>
					<div class="mt-4 flex items-center gap-1.5 text-sm font-semibold text-[color:var(--color-accent-hover)] group-hover:gap-2.5 transition-all">
						{{ $isEn ? 'Open' : 'Bekijken' }}
						<svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</div>
				</a>
			@endforeach
		</div>
	</div>
</section>

{{-- ============ INTRO STATEMENT ============ --}}
<section class="py-20 border-t border-[color:var(--color-line)]">
	<div class="max-w-[1100px] mx-auto px-6">
		<span class="pill pill-ink mb-4">{{ $isEn ? 'What we do' : 'Wat we doen' }}</span>
		<h2 class="display-2 mb-6 max-w-3xl">
			{{ $isEn ? 'Websites, portals, integrations and automation that actually' : 'Websites, portals, koppelingen en automatisering die echt' }}
			<span class="accent-word">{{ $isEn ? 'solve something.' : 'iets oplossen.' }}</span>
		</h2>
		<div class="grid md:grid-cols-2 gap-8 text-[color:var(--color-ink-muted)] leading-relaxed">
			<p>
				{{ $isEn
					? 'A good digital solution is more than a pretty front. Companies need a technical foundation that\'s logically built, works safely, loads fast, stays indexable and fits existing processes.'
					: 'Een goede digitale oplossing is meer dan een mooie voorkant. Bedrijven hebben een technische basis nodig die logisch is opgebouwd, veilig werkt, snel laadt en aansluit op bestaande processen.' }}
			</p>
			<p>
				{{ $isEn
					? 'Our strength is building custom web applications and integrations between systems. We connect websites to CRM, ATS, ERP, order or accounting systems, streamline workflows and make processes clearer.'
					: 'Onze kracht ligt in maatwerk webapplicaties en koppelingen tussen systemen. We verbinden websites met CRM, ATS, ERP, order- of boekhoudsystemen, stroomlijnen workflows en maken processen overzichtelijker.' }}
			</p>
		</div>
	</div>
</section>

{{-- ============ SERVICES ============ --}}
<section class="bg-[color:var(--color-surface)] py-20">
	<div class="max-w-[1400px] mx-auto px-6">
		<div class="flex items-end justify-between flex-wrap gap-4 mb-12">
			<div>
				<span class="pill pill-teal mb-3">{{ $isEn ? 'Services' : 'Diensten' }}</span>
				<h2 class="display-2">{{ $isEn ? 'Thirteen practical services.' : 'Dertien praktische diensten.' }}</h2>
			</div>
			<a href="/{{ $locale }}/diensten" class="text-sm font-semibold text-[color:var(--color-accent-hover)] inline-flex items-center gap-1.5 hover:gap-2.5 transition-all">
				{{ $isEn ? 'See all services' : 'Alle diensten' }}
				<svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</a>
		</div>

		<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
			@foreach ($services as $s)
				<a href="/{{ $locale }}/diensten/{{ $s['slug'] }}" class="card card-accent group block">
					<div class="flex items-center justify-between mb-4">
						<span class="pill pill-ink text-[10px]">{{ $s['badge'] }}</span>
						<svg class="w-4 h-4 text-[color:var(--color-ink-soft)] group-hover:text-[color:var(--color-accent)] group-hover:translate-x-0.5 transition" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</div>
					<h3 class="font-bold text-lg mb-2 leading-tight">{{ $isEn ? $s['title_en'] : $s['title_nl'] }}</h3>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $isEn ? $s['desc_en'] : $s['desc_nl'] }}</p>
				</a>
			@endforeach
		</div>
	</div>
</section>

{{-- ============ APPROACH ============ --}}
<section class="py-20">
	<div class="max-w-[1400px] mx-auto px-6">
		<div class="text-center max-w-3xl mx-auto mb-14">
			<span class="pill pill-ink mb-3">{{ $isEn ? 'Approach' : 'Werkwijze' }}</span>
			<h2 class="display-2">{{ $isEn ? 'Clear, technically strong, practical.' : 'Duidelijk, technisch sterk, praktisch.' }}</h2>
		</div>
		<div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5">
			@foreach ([
				['n' => '01', 't_nl' => 'Analyse & prioriteiten',     't_en' => 'Analysis & priorities',    'b_nl' => 'Systemen, processen, knelpunten en afhankelijkheden in kaart.', 'b_en' => 'Systems, processes, bottlenecks and dependencies mapped.'],
				['n' => '02', 't_nl' => 'Oplossing & planning',       't_en' => 'Solution & planning',      'b_nl' => 'Realistische oplossing met scope en concrete vervolgstappen.', 'b_en' => 'Realistic solution with scope and concrete next steps.'],
				['n' => '03', 't_nl' => 'Bouwen & optimaliseren',     't_en' => 'Build & optimise',         'b_nl' => 'Van maatwerk tot koppelingen en performanceverbeteringen.', 'b_en' => 'From custom builds to integrations and performance gains.'],
				['n' => '04', 't_nl' => 'Doorontwikkeling',           't_en' => 'Ongoing development',      'b_nl' => 'Systemen gaan live én blijven goed aansluiten op de praktijk.', 'b_en' => 'Systems go live and keep fitting the real world.'],
			] as $st)
				<div class="card">
					<div class="text-5xl font-black text-[color:var(--color-accent)] mb-3 leading-none">{{ $st['n'] }}</div>
					<h3 class="font-bold mb-2">{{ $isEn ? $st['t_en'] : $st['t_nl'] }}</h3>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $isEn ? $st['b_en'] : $st['b_nl'] }}</p>
				</div>
			@endforeach
		</div>
	</div>
</section>

{{-- ============ EXPERTISE ============ --}}
<section class="bg-[color:var(--color-surface)] py-20">
	<div class="max-w-[1400px] mx-auto px-6">
		<div class="grid lg:grid-cols-2 gap-12 items-start">
			<div>
				<span class="pill pill-ink mb-3">{{ $isEn ? 'Expertise' : 'Expertise' }}</span>
				<h2 class="display-2 mb-5">{{ $isEn ? 'What we know, inside out.' : 'Waar we thuis in zijn.' }}</h2>
				<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-6">
					{{ $isEn
						? 'Topics we help companies with in practice, from analysis to implementation and optimisation.'
						: 'Onderwerpen waar wij bedrijven praktisch bij helpen, van analyse tot implementatie en optimalisatie.' }}
				</p>
				<a href="/{{ $locale }}/contact" class="btn-dark">
					{{ $isEn ? 'Discuss your case' : 'Bespreek jouw case' }}
					<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</a>
			</div>
			<div class="flex flex-wrap gap-2">
				@foreach ($expertise as $tag)
					<span class="pill pill-ink text-xs">{{ $tag }}</span>
				@endforeach
			</div>
		</div>
	</div>
</section>

{{-- ============ FAQ ============ --}}
<section class="py-20">
	<div class="max-w-[1100px] mx-auto px-6">
		<div class="text-center max-w-2xl mx-auto mb-12">
			<span class="pill pill-ink mb-3">FAQ</span>
			<h2 class="display-2">{{ $isEn ? 'Frequently asked.' : 'Veelgesteld.' }}</h2>
		</div>
		<div class="grid md:grid-cols-2 gap-4">
			@foreach ($faq as $item)
				<details class="card group cursor-pointer">
					<summary class="flex items-center justify-between list-none cursor-pointer">
						<span class="font-bold pr-4">{{ $isEn ? $item['q_en'] : $item['q_nl'] }}</span>
						<span class="shrink-0 w-8 h-8 rounded-full border border-[color:var(--color-line)] flex items-center justify-center text-[color:var(--color-ink-muted)] group-open:bg-[color:var(--color-accent)] group-open:border-[color:var(--color-accent)] group-open:text-white group-open:rotate-45 transition">+</span>
					</summary>
					<p class="mt-4 text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $isEn ? $item['a_en'] : $item['a_nl'] }}</p>
				</details>
			@endforeach
		</div>
	</div>
</section>

{{-- ============ CTA ============ --}}
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1100px] mx-auto px-6 py-24 text-center">
		<h2 class="display-1 mb-6">
			{{ $isEn ? 'Ready to' : 'Klaar om' }} <span class="accent-word">{{ $isEn ? 'build something that works?' : 'iets te bouwen dat werkt?' }}</span>
		</h2>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] max-w-2xl mx-auto mb-8">
			{{ $isEn
				? 'Tell us about your website, portal, integrations or workflows. We\'ll come back with practical advice and a clear approach.'
				: 'Vertel ons over je website, portal, koppelingen of workflows. Wij komen terug met praktisch advies en een heldere aanpak.' }}
		</p>
		<div class="flex flex-wrap gap-3 justify-center">
			<a href="/{{ $locale }}/contact" class="btn-accent">
				{{ $isEn ? 'Request advice' : 'Vraag advies aan' }}
				<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</a>
			<a href="/{{ $locale }}/diensten" class="btn-ghost-light">
				{{ $isEn ? 'View all services' : 'Bekijk alle diensten' }}
			</a>
		</div>
	</div>
</section>

@endsection
