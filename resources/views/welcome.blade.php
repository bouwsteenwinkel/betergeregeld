@extends('layouts.app')

@section('title', 'Beter Geregeld ICT — maatwerk websites, portals, koppelingen & automatisering')
@section('description', 'Beter Geregeld ICT helpt bedrijven met maatwerk webapplicaties, klantportalen, API-koppelingen, procesautomatisering, beveiliging, performance en technische SEO.')

@php
	$locale = app()->getLocale();
	$isEn = $locale === 'en';

	$services = [
		['badge' => 'CookieGeregeld',   'title_nl' => 'Cookie banner laten instellen',      'title_en' => 'Cookie banner setup',              'desc_nl' => 'Hulp bij categorieën, scripts, tagging en een nette technische basis voor cookie consent en cookiemeldingen.', 'desc_en' => 'Help with categories, scripts, tagging and a clean technical foundation for cookie consent.'],
		['badge' => 'Mail Security',    'title_nl' => 'E-mailbeveiliging instellen',         'title_en' => 'Email security setup',              'desc_nl' => 'Laat SPF, DKIM en DMARC correct instellen en voorkom spamproblemen, spoofing en onbetrouwbare e-mailaflevering.', 'desc_en' => 'SPF, DKIM and DMARC set up correctly to prevent spam issues, spoofing and unreliable email delivery.'],
		['badge' => 'Access',           'title_nl' => 'Toegang check',                        'title_en' => 'Access review',                      'desc_nl' => 'Krijg inzicht in accounts, rechten, leveranciers, oude gebruikers en onnodige toegangen binnen je organisatie.', 'desc_en' => 'Insight into accounts, permissions, vendors, stale users and unnecessary access within your organisation.'],
		['badge' => 'Website',          'title_nl' => 'Website meertalig maken',              'title_en' => 'Make website multilingual',          'desc_nl' => 'Laat je website logisch opzetten in meerdere talen met een sterke technische structuur, duidelijke URL-opbouw en SEO-basis.', 'desc_en' => 'Set up your website logically in multiple languages with a strong technical structure, clear URL design and SEO basis.'],
		['badge' => 'Security',         'title_nl' => '2FA implementeren',                    'title_en' => 'Implement 2FA',                      'desc_nl' => 'Extra loginbeveiliging voor webshop, portal, CMS of beheeromgeving zonder onnodige frictie voor gebruikers.', 'desc_en' => 'Extra login security for webshop, portal, CMS or admin — without unnecessary friction for users.'],
		['badge' => 'SEO',              'title_nl' => 'SEO check & optimalisatie',            'title_en' => 'SEO check & optimisation',           'desc_nl' => 'Inzicht in technische SEO, indexatie, headings, metadata, interne links, crawlbaarheid en concrete quick wins.', 'desc_en' => 'Insight into technical SEO: indexation, headings, metadata, internal links, crawlability and concrete quick wins.'],
		['badge' => 'Performance',      'title_nl' => 'Website snelheid verbeteren',          'title_en' => 'Improve website speed',              'desc_nl' => 'Verbeter laadtijd met optimalisaties voor afbeeldingen, scripts, caching, CSS, JavaScript en serverinstellingen.', 'desc_en' => 'Improve loading time with optimisations for images, scripts, caching, CSS, JavaScript and server settings.'],
		['badge' => 'Onderhoud',        'title_nl' => 'Website onderhoud uitbesteden',        'title_en' => 'Outsource website maintenance',      'desc_nl' => 'Laat updates, controles, technisch beheer en basisbeveiliging netjes uitvoeren zonder er zelf achteraan te hoeven.', 'desc_en' => 'Updates, checks, technical management and baseline security handled, without chasing it yourself.'],
		['badge' => 'Beveiliging',      'title_nl' => 'Website beveiligen',                   'title_en' => 'Secure your website',                'desc_nl' => 'Verklein risico\'s door zwakke instellingen, verouderde plugins, onduidelijke toegang en achterstallig technisch onderhoud aan te pakken.', 'desc_en' => 'Reduce risk by addressing weak settings, outdated plugins, unclear access and overdue technical maintenance.'],
		['badge' => 'Back-up',          'title_nl' => 'Website back-up en herstel',           'title_en' => 'Website backup & recovery',          'desc_nl' => 'Zorg dat back-up, herstelpunten en recovery-processen goed geregeld zijn zodat fouten, updates of storingen minder impact hebben.', 'desc_en' => 'Make sure backups, restore points and recovery processes are well arranged so mistakes, updates or outages have less impact.'],
		['badge' => 'WordPress',        'title_nl' => 'WordPress opschonen',                  'title_en' => 'Clean up WordPress',                 'desc_nl' => 'Ruim oude plugins, dubbele functies, overbodige code en technische vervuiling op voor meer snelheid en minder risico.', 'desc_en' => 'Clear out old plugins, duplicate functions, redundant code and technical clutter for more speed and less risk.'],
		['badge' => 'Migratie',         'title_nl' => 'Website migratie zonder gedoe',        'title_en' => 'Website migration without hassle',   'desc_nl' => 'Verhuis je website veilig naar nieuwe hosting, server of beheerpartij zonder dataverlies, downtime of technische chaos.', 'desc_en' => 'Move your website safely to new hosting, server or management party without data loss, downtime or chaos.'],
		['badge' => 'Structuur',        'title_nl' => 'Website structuur check',              'title_en' => 'Website structure check',            'desc_nl' => 'Krijg inzicht in sitemap, URL-structuur, pagina-opbouw, interne links en SEO-basis zodat je website logischer vindbaar wordt.', 'desc_en' => 'Insight into sitemap, URL structure, page composition, internal links and SEO basis to make your site more findable.'],
	];

	$cases = [
		['title_nl' => 'Maatwerk klantportal met koppelingen',            'title_en' => 'Custom customer portal with integrations',
		 'body_nl'  => 'Een organisatie werkt met een website, CRM en backoffice-systeem die niet goed op elkaar aansluiten. We bouwen een centraal portal waarin gegevens samenkomen, processen duidelijker worden en handmatige tussenstappen afnemen.',
		 'body_en'  => 'An organisation runs a website, CRM and back-office system that don\'t integrate well. We build a central portal where data comes together, processes become clearer and manual steps decrease.',
		 'bullets_nl' => ['Centraal overzicht voor gebruikers en beheerders', 'Koppeling tussen website en interne systemen', 'Minder dubbel werk en minder fouten in overdracht'],
		 'bullets_en' => ['Central overview for users and admins', 'Integration between website and internal systems', 'Less duplicate work and fewer hand-off errors']],
		['title_nl' => 'Workflow automatisering tussen afdelingen',        'title_en' => 'Workflow automation between departments',
		 'body_nl'  => 'Aanvragen lopen via mail, losse Excel-bestanden en verschillende systemen. We structureren het proces, automatiseren waar dat logisch is en zorgen voor duidelijke status, eigenaarschap en terugkoppeling.',
		 'body_en'  => 'Requests flow via email, loose Excel files and different systems. We structure the process, automate where it makes sense, and deliver clear status, ownership and feedback.',
		 'bullets_nl' => ['Minder handmatig werk', 'Meer grip op doorlooptijd en verantwoordelijkheden', 'Beter schaalbare interne workflow'],
		 'bullets_en' => ['Less manual work', 'Better grip on lead time and responsibilities', 'Scalable internal workflow']],
		['title_nl' => 'Technische optimalisatie van bestaande website',   'title_en' => 'Technical optimisation of existing website',
		 'body_nl'  => 'Een bestaande website is traag, onoverzichtelijk en technisch verouderd. We verbeteren structuur, performance, beveiliging en indexeerbaarheid zodat de site sneller, veiliger en beter beheersbaar wordt.',
		 'body_en'  => 'An existing site is slow, cluttered and outdated. We improve structure, performance, security and indexability so the site becomes faster, safer and easier to maintain.',
		 'bullets_nl' => ['Verbeterde laadtijd en technische basis', 'Betere crawlbaarheid en SEO-structuur', 'Minder risico door opschoning en onderhoud'],
		 'bullets_en' => ['Improved load time and technical basis', 'Better crawlability and SEO structure', 'Less risk through cleanup and maintenance']],
	];

	$faq = [
		['q_nl' => 'Wat houdt een technische SEO check in?',
		 'q_en' => 'What does a technical SEO check involve?',
		 'a_nl' => 'Een technische SEO check kijkt onder andere naar indexatie, metadata, headings, interne links, crawlbaarheid, canonicals, paginastructuur, snelheid en fouten die de vindbaarheid van een website beperken.',
		 'a_en' => 'A technical SEO check looks at indexation, metadata, headings, internal links, crawlability, canonicals, page structure, speed and errors that limit a site\'s findability.'],
		['q_nl' => 'Wanneer is een maatwerk portal interessanter dan een standaard oplossing?',
		 'q_en' => 'When is a custom portal better than a standard solution?',
		 'a_nl' => 'Zodra processen, rollen, koppelingen of informatiebehoeften specifieker worden en een standaard oplossing te veel beperkingen oplevert, kan een maatwerk portal zorgen voor meer controle, efficiëntie en betere aansluiting op de praktijk.',
		 'a_en' => 'As soon as processes, roles, integrations or information needs become more specific and a standard solution creates too many limits, a custom portal can deliver more control, efficiency and a better fit with practice.'],
		['q_nl' => 'Wanneer heb je een API-koppeling nodig?',
		 'q_en' => 'When do you need an API integration?',
		 'a_nl' => 'Een API-koppeling is interessant wanneer gegevens nu handmatig tussen systemen worden overgenomen of wanneer website, CRM, ATS, ERP, boekhouding of andere platformen niet goed samenwerken.',
		 'a_en' => 'An API integration makes sense when data is currently copied between systems manually, or when website, CRM, ATS, ERP, accounting or other platforms don\'t work together well.'],
		['q_nl' => 'Waarom zijn SPF, DKIM en DMARC belangrijk?',
		 'q_en' => 'Why do SPF, DKIM and DMARC matter?',
		 'a_nl' => 'SPF, DKIM en DMARC helpen e-maildomeinen beschermen tegen spoofing en verhogen de kans dat legitieme e-mails correct worden afgeleverd in plaats van in spam terecht te komen.',
		 'a_en' => 'SPF, DKIM and DMARC protect email domains against spoofing and increase the chance that legitimate email is delivered correctly rather than landing in spam.'],
		['q_nl' => 'Hoe verbeter je de snelheid van een website?',
		 'q_en' => 'How do you improve website speed?',
		 'a_nl' => 'Dat begint meestal bij afbeeldingen, caching, scripts, CSS, fonts, plug-ins, serverinstellingen en de algehele technische opbouw van de website. De juiste aanpak hangt af van het platform en de knelpunten.',
		 'a_en' => 'It usually starts with images, caching, scripts, CSS, fonts, plugins, server settings and the overall technical build of the site. The right approach depends on the platform and the bottlenecks.'],
		['q_nl' => 'Wat levert procesautomatisering concreet op?',
		 'q_en' => 'What does process automation actually deliver?',
		 'a_nl' => 'Minder handmatig werk, minder fouten, kortere doorlooptijden, duidelijkere verantwoordelijkheden en meer grip op hoe gegevens en processen door de organisatie heen lopen.',
		 'a_en' => 'Less manual work, fewer errors, shorter lead times, clearer responsibilities and more grip on how data and processes flow through the organisation.'],
	];

	$expertise = ['Maatwerk webapplicaties', 'Klantportalen', 'Custom business portals', 'API integraties', 'ERP en boekhoudkoppelingen', 'Procesautomatisering', 'Performance optimalisatie', 'Technische SEO', 'WordPress optimalisatie', 'Toegangsbeheer', '2FA en beveiliging', 'Meertalige websites'];
@endphp

@section('content')
<div class="bg-[#f5f7fb]">
	<div class="max-w-[1400px] mx-auto px-4 py-8 sm:py-12">

		{{-- Top banners --}}
		<section aria-labelledby="banners-title">
			<h2 id="banners-title" class="sr-only">{{ __('Uitgelichte oplossingen') }}</h2>
			<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
				@foreach ([
					['href' => 'https://cookie.betergeregeld.com', 'title' => 'CookieGeregeld', 'nl' => 'Cookie banner, categorieën en consent technisch goed geregeld.', 'en' => 'Cookie banner, categories and consent — technically sorted.'],
					['href' => 'https://toegang.betergeregeld.com', 'title' => 'AccessGuard', 'nl' => 'Meer grip op accounts, rechten, leveranciers en oude toegangen.', 'en' => 'More grip on accounts, permissions, vendors and stale access.'],
					['href' => '/' . $locale . '/tools/iban-check', 'title' => $isEn ? 'Free tools' : 'Gratis tools', 'nl' => 'Praktische hulpmiddelen voor websites, processen en technische basischecks.', 'en' => 'Practical helpers for websites, processes and technical baseline checks.'],
					['href' => '/' . $locale . '/contact', 'title' => $isEn ? 'Custom & advice' : 'Maatwerk & advies', 'nl' => 'Van technische analyse tot uitvoering, optimalisatie en doorontwikkeling.', 'en' => 'From technical analysis to execution, optimisation and ongoing development.'],
				] as $b)
					<a href="{{ $b['href'] }}" class="block bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-5 shadow-[var(--shadow-soft)] hover:-translate-y-0.5 hover:shadow-lg transition">
						<strong class="block text-base mb-1">{{ $b['title'] }}</strong>
						<span class="text-sm text-[color:var(--color-ink-muted)]">{{ $isEn ? $b['en'] : $b['nl'] }}</span>
					</a>
				@endforeach
			</div>
		</section>

		{{-- Hero --}}
		<header class="mt-6 bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-8 sm:p-12 shadow-[var(--shadow-soft)]">
			<div class="grid md:grid-cols-2 gap-10 items-center">
				<div>
					<p class="text-sm font-bold tracking-wide text-[color:var(--color-ink-muted)] mb-2">
						{{ $isEn ? 'Practical technology for websites, portals and digital processes' : 'Praktische techniek voor websites, portals en digitale processen' }}
					</p>
					<h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight leading-tight mb-5">
						{{ $isEn ? 'Custom websites, portals and technical optimisation for businesses' : 'Maatwerk websites, portals en technische optimalisatie voor bedrijven' }}
					</h1>
					<p class="text-lg text-[color:var(--color-ink-muted)] mb-6 leading-relaxed">
						{{ $isEn
							? 'We help companies with custom web applications, customer portals, API integrations, process automation, security, performance and technical SEO. No complicated fuss, just clear solutions that make processes more efficient and manageable.'
							: 'Wij helpen bedrijven met maatwerk webapplicaties, klantportalen, API-koppelingen, procesautomatisering, beveiliging, performance en technische SEO. Geen ingewikkeld gedoe, maar duidelijke oplossingen die processen efficiënter en beter beheersbaar maken.' }}
					</p>
					<div class="flex flex-wrap gap-3 mb-8">
						<a href="/{{ $locale }}/diensten" class="rounded-[var(--radius-control)] bg-[color:var(--color-ink)] text-white font-semibold px-5 py-3 hover:opacity-90 transition">
							{{ $isEn ? 'See our services' : 'Bekijk onze diensten' }}
						</a>
						<a href="/{{ $locale }}/contact" class="rounded-[var(--radius-control)] bg-white text-[color:var(--color-ink)] border border-[color:var(--color-line)] font-semibold px-5 py-3 hover:bg-gray-50 transition">
							{{ $isEn ? 'Book a free intro call' : 'Plan een gratis adviesgesprek' }}
						</a>
					</div>
					<ul class="grid sm:grid-cols-2 gap-2 text-sm">
						@foreach ($isEn
							? ['⚙️ Custom websites and customer portals', '🔌 API integrations and system connectors', '🔁 Process automation and smart workflows', '📈 Performance, security and technical SEO']
							: ['⚙️ Maatwerk websites en klantportalen', '🔌 API-koppelingen en systeemintegraties', '🔁 Procesautomatisering en slimme workflows', '📈 Performance, beveiliging en technische SEO'] as $f)
							<li class="bg-gray-50 border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3 py-2 font-semibold">{{ $f }}</li>
						@endforeach
					</ul>
				</div>
				<aside class="relative min-h-[320px] hidden md:block" aria-hidden="true">
					<div class="absolute inset-x-0 top-6 bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-5 shadow-[var(--shadow-soft)]">
						<div class="flex flex-wrap gap-2 mb-3">
							<span class="bg-blue-100 text-blue-900 text-xs font-bold px-2.5 py-1 rounded-full">Portals</span>
							<span class="bg-blue-100 text-blue-900 text-xs font-bold px-2.5 py-1 rounded-full">{{ $isEn ? 'Integrations' : 'Integraties' }}</span>
							<span class="bg-blue-100 text-blue-900 text-xs font-bold px-2.5 py-1 rounded-full">{{ $isEn ? 'Automation' : 'Automatisering' }}</span>
						</div>
						<div class="grid grid-cols-3 gap-3 mb-3 text-center">
							<div class="bg-gray-50 rounded-[var(--radius-control)] p-2.5"><strong class="block">API</strong><span class="text-xs text-[color:var(--color-ink-muted)]">{{ $isEn ? 'Connectors' : 'Koppelingen' }}</span></div>
							<div class="bg-gray-50 rounded-[var(--radius-control)] p-2.5"><strong class="block">Portal</strong><span class="text-xs text-[color:var(--color-ink-muted)]">{{ $isEn ? 'Control' : 'Controle' }}</span></div>
							<div class="bg-gray-50 rounded-[var(--radius-control)] p-2.5"><strong class="block">Workflow</strong><span class="text-xs text-[color:var(--color-ink-muted)]">{{ $isEn ? 'Efficiency' : 'Efficiëntie' }}</span></div>
						</div>
						<div class="space-y-2">
							<div class="h-2.5 w-[85%] rounded-full bg-blue-200"></div>
							<div class="h-2.5 w-[65%] rounded-full bg-blue-200"></div>
							<div class="h-2.5 w-[75%] rounded-full bg-blue-200"></div>
							<div class="h-2.5 w-[55%] rounded-full bg-blue-200"></div>
						</div>
					</div>
				</aside>
			</div>
		</header>

		{{-- Intro --}}
		<section class="mt-6 bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-8 shadow-[var(--shadow-soft)]">
			<h2 class="text-2xl sm:text-3xl font-bold mb-4">{{ $isEn ? 'Websites, portals, integrations and automation that actually solve something' : 'Websites, portals, koppelingen en automatisering die echt iets oplossen' }}</h2>
			<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-4">
				{{ $isEn
					? 'A good digital solution is more than a pretty front. Companies need a technical foundation that\'s logically built, works safely, loads fast, stays indexable and fits existing processes. That\'s why we help with custom websites, customer portals, multilingual sites, API integrations, access management, technical SEO, performance optimisation and smart automation.'
					: 'Een goede digitale oplossing is meer dan alleen een mooie voorkant. Bedrijven hebben behoefte aan een technische basis die logisch is opgebouwd, veilig werkt, snel laadt, goed indexeerbaar blijft en aansluit op bestaande processen. Daarom helpen wij met maatwerk websites, klantportalen, meertalige websites, API-koppelingen, toegangsbeheer, technische SEO, performance optimalisatie en slimme automatisering.' }}
			</p>
			<p class="text-[color:var(--color-ink-muted)] leading-relaxed">
				{{ $isEn
					? 'Our strength is in building custom web applications and integrations between systems. Think: connecting websites to CRM, ATS, ERP, order or accounting systems, streamlining internal workflows, building central dashboards and making processes clearer. We don\'t automate for the sake of it — we automate to structurally solve concrete operational bottlenecks.'
					: 'Onze kracht ligt in het ontwikkelen van maatwerk webapplicaties en het realiseren van koppelingen tussen systemen. Denk aan websites koppelen met CRM-, ATS-, ERP-, order- of boekhoudsystemen, interne workflows stroomlijnen, centrale dashboards bouwen en processen overzichtelijker maken. Wij automatiseren niet om het automatiseren, maar om concrete operationele knelpunten structureel op te lossen.' }}
			</p>
		</section>

		{{-- Audience --}}
		<section class="mt-6">
			<div class="mb-4">
				<h2 class="text-2xl sm:text-3xl font-bold mb-2">{{ $isEn ? 'Who we work with' : 'Voor welke bedrijven wij werken' }}</h2>
				<p class="text-[color:var(--color-ink-muted)]">{{ $isEn ? 'For organisations that want a stable technical foundation, less manual work and better collaboration between systems.' : 'Voor organisaties die behoefte hebben aan een stabiele technische basis, minder handmatig werk en betere samenwerking tussen systemen.' }}</p>
			</div>
			<div class="grid md:grid-cols-3 gap-4">
				@foreach ([
					['t_nl' => 'MKB-bedrijven en groeiende organisaties', 't_en' => 'SMB and growing organisations',
					 'b_nl' => 'Bedrijven die hun website, portal of interne processen professioneler willen inrichten en klaar willen maken voor groei, betere controle en minder afhankelijkheid van losse handmatige stappen.',
					 'b_en' => 'Businesses that want to professionalise their website, portal or internal processes, ready for growth, better control and less dependency on manual steps.'],
					['t_nl' => 'Teams met meerdere systemen', 't_en' => 'Teams with multiple systems',
					 'b_nl' => 'Organisaties die werken met combinaties van website, CRM, ATS, ERP, ordermanagement, boekhouding of externe databronnen en merken dat gegevens, processen of verantwoordelijkheden niet goed op elkaar aansluiten.',
					 'b_en' => 'Organisations working with website + CRM + ATS + ERP + order management + accounting combinations where data, processes or ownership don\'t line up well.'],
					['t_nl' => 'Bedrijven die een technisch verlengstuk zoeken', 't_en' => 'Businesses looking for a technical extension of their team',
					 'b_nl' => 'Wij functioneren regelmatig als verlengstuk van bestaande teams of agencies en helpen bij ontwikkeling, integraties, procesverbetering en technische doorontwikkeling zonder onnodige complexiteit.',
					 'b_en' => 'We regularly act as an extension of in-house teams or agencies and help with development, integrations, process improvement and technical evolution — without unnecessary complexity.'],
				] as $a)
					<article class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-6 shadow-[var(--shadow-soft)]">
						<h3 class="text-lg font-bold mb-2">{{ $isEn ? $a['t_en'] : $a['t_nl'] }}</h3>
						<p class="text-[color:var(--color-ink-muted)] leading-relaxed text-sm">{{ $isEn ? $a['b_en'] : $a['b_nl'] }}</p>
					</article>
				@endforeach
			</div>
		</section>

		{{-- Services --}}
		<section class="mt-10">
			<div class="mb-4">
				<h2 class="text-2xl sm:text-3xl font-bold mb-2">{{ $isEn ? 'Popular services' : 'Populaire diensten' }}</h2>
				<p class="text-[color:var(--color-ink-muted)]">{{ $isEn ? 'Practical help with websites, security, maintenance, performance, structure, integrations and technical optimisation.' : 'Praktische hulp bij websites, beveiliging, onderhoud, performance, structuur, koppelingen en technische optimalisatie.' }}</p>
			</div>
			<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
				@foreach ($services as $s)
					<article class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-5 shadow-[var(--shadow-soft)] hover:-translate-y-0.5 hover:shadow-lg transition">
						<div class="flex items-center justify-between mb-3">
							<span class="inline-flex items-center px-2.5 py-1 rounded-full border border-[color:var(--color-line)] bg-gray-50 text-xs font-bold text-[color:var(--color-ink-muted)]">{{ $s['badge'] }}</span>
							<span class="text-[color:var(--color-ink-muted)] text-xl">→</span>
						</div>
						<h3 class="text-lg font-bold mb-2 leading-tight">{{ $isEn ? $s['title_en'] : $s['title_nl'] }}</h3>
						<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed mb-4">{{ $isEn ? $s['desc_en'] : $s['desc_nl'] }}</p>
						<span class="inline-flex items-center px-3 py-2 rounded-[var(--radius-control)] border border-[color:var(--color-line)] bg-gray-50 text-xs font-bold">{{ $isEn ? 'View service' : 'Bekijk dienst' }}</span>
					</article>
				@endforeach
			</div>
		</section>

		{{-- Approach --}}
		<section class="mt-10">
			<div class="mb-4">
				<h2 class="text-2xl sm:text-3xl font-bold mb-2">{{ $isEn ? 'Our approach: clear, technically strong and practical' : 'Onze aanpak: duidelijk, technisch sterk en praktisch uitvoerbaar' }}</h2>
				<p class="text-[color:var(--color-ink-muted)]">{{ $isEn ? 'No loose ends or unrealistic trajectories — an approach with grip on content, planning and result.' : 'Geen losse eindjes of onrealistische trajecten, maar een aanpak met grip op inhoud, planning en resultaat.' }}</p>
			</div>
			<div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
				@foreach ([
					['t_nl' => '1. Analyse en prioriteiten', 't_en' => '1. Analysis and priorities', 'b_nl' => 'We brengen systemen, processen, knelpunten en afhankelijkheden in kaart. Zo wordt duidelijk waar vertraging, fouten of onnodig handmatig werk ontstaan.', 'b_en' => 'We map systems, processes, bottlenecks and dependencies, so it\'s clear where delays, errors or unnecessary manual work come from.'],
					['t_nl' => '2. Technische oplossing en planning', 't_en' => '2. Technical solution and planning', 'b_nl' => 'We vertalen de vraag naar een realistische oplossing met duidelijke scope, concrete stappen en een aanpak die beheersbaar blijft.', 'b_en' => 'We translate the question into a realistic solution with clear scope, concrete steps and a manageable approach.'],
					['t_nl' => '3. Bouwen, koppelen en optimaliseren', 't_en' => '3. Build, integrate and optimise', 'b_nl' => 'Van maatwerk webapplicaties en portals tot API-koppelingen, performanceverbetering en technische SEO: we voeren uit wat nodig is om processen echt beter te laten werken.', 'b_en' => 'From custom web apps and portals to API integrations, performance improvements and technical SEO: we execute what\'s needed to make processes actually work better.'],
					['t_nl' => '4. Doorontwikkeling en samenwerking', 't_en' => '4. Ongoing development and collaboration', 'b_nl' => 'We denken mee op de langere termijn en functioneren waar nodig als verlengstuk van je team, zodat systemen niet alleen live gaan, maar ook goed blijven aansluiten op de praktijk.', 'b_en' => 'We think along for the long term and act as an extension of your team, so systems don\'t just go live but keep fitting real-world needs.'],
				] as $st)
					<article class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-5 shadow-[var(--shadow-soft)]">
						<h3 class="text-base font-bold mb-2">{{ $isEn ? $st['t_en'] : $st['t_nl'] }}</h3>
						<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $isEn ? $st['b_en'] : $st['b_nl'] }}</p>
					</article>
				@endforeach
			</div>
		</section>

		{{-- Cases --}}
		<section class="mt-10">
			<div class="mb-4">
				<h2 class="text-2xl sm:text-3xl font-bold mb-2">{{ $isEn ? 'Example cases and solution types' : 'Voorbeeldcases en type oplossingen' }}</h2>
				<p class="text-[color:var(--color-ink-muted)]">{{ $isEn ? 'Examples where custom work, integrations and process improvement make the difference.' : 'Voorbeelden van vraagstukken waarbij maatwerk, koppelingen en procesverbetering het verschil maken.' }}</p>
			</div>
			<div class="grid md:grid-cols-3 gap-4">
				@foreach ($cases as $c)
					<article class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-6 shadow-[var(--shadow-soft)]">
						<h3 class="text-lg font-bold mb-3">{{ $isEn ? $c['title_en'] : $c['title_nl'] }}</h3>
						<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed mb-4">{{ $isEn ? $c['body_en'] : $c['body_nl'] }}</p>
						<ul class="text-sm text-[color:var(--color-ink-muted)] space-y-1.5 list-disc list-inside">
							@foreach (($isEn ? $c['bullets_en'] : $c['bullets_nl']) as $bl)
								<li>{{ $bl }}</li>
							@endforeach
						</ul>
					</article>
				@endforeach
			</div>
		</section>

		{{-- Why --}}
		<section class="mt-10">
			<div class="mb-4">
				<h2 class="text-2xl sm:text-3xl font-bold mb-2">{{ $isEn ? 'Why companies choose a practical technical approach' : 'Waarom bedrijven kiezen voor een praktische technische aanpak' }}</h2>
				<p class="text-[color:var(--color-ink-muted)]">{{ $isEn ? 'No more loose tools, temporary patches or unclear management — a clear foundation you can build on.' : 'Niet meer losse tools, tijdelijke lapmiddelen of onduidelijk beheer, maar een duidelijke basis waar je op kunt bouwen.' }}</p>
			</div>
			<div class="grid md:grid-cols-3 gap-4">
				@foreach ([
					['t_nl' => 'Technisch sterk fundament', 't_en' => 'Technically strong foundation', 'b_nl' => 'Wij kijken naar de technische basis van je website of portaal: structuur, veiligheid, prestaties, koppelingen, beheerbaarheid en schaalbaarheid.', 'b_en' => 'We look at the technical base of your site or portal: structure, security, performance, integrations, manageability and scalability.'],
					['t_nl' => 'Duidelijke communicatie', 't_en' => 'Clear communication', 'b_nl' => 'We combineren technische diepgang met heldere communicatie en realistische planningen, zodat projecten voorspelbaar blijven en verwachtingen kloppen.', 'b_en' => 'We combine technical depth with clear communication and realistic planning, so projects stay predictable and expectations match.'],
					['t_nl' => 'Gericht op operationele winst', 't_en' => 'Focused on operational gain', 'b_nl' => 'Onze oplossingen zijn gericht op minder handmatig werk, betere samenwerking tussen systemen, meer overzicht, meer controle en concrete procesverbetering.', 'b_en' => 'Our solutions focus on less manual work, better collaboration between systems, more overview, more control and concrete process improvement.'],
				] as $w)
					<article class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-5 shadow-[var(--shadow-soft)]">
						<h3 class="text-base font-bold mb-2">{{ $isEn ? $w['t_en'] : $w['t_nl'] }}</h3>
						<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $isEn ? $w['b_en'] : $w['b_nl'] }}</p>
					</article>
				@endforeach
			</div>
		</section>

		{{-- Expertise --}}
		<section class="mt-10">
			<div class="mb-4">
				<h2 class="text-2xl sm:text-3xl font-bold mb-2">{{ $isEn ? 'Areas of expertise' : 'Expertisegebieden' }}</h2>
				<p class="text-[color:var(--color-ink-muted)]">{{ $isEn ? 'Topics we help companies with in practice, from analysis to implementation and optimisation.' : 'Onderwerpen waar wij bedrijven praktisch bij helpen, van analyse tot implementatie en optimalisatie.' }}</p>
			</div>
			<div class="flex flex-wrap gap-2">
				@foreach ($expertise as $tag)
					<span class="inline-flex items-center px-3.5 py-2 rounded-full bg-white border border-[color:var(--color-line)] shadow-[var(--shadow-soft)] text-sm font-semibold text-[color:var(--color-ink-muted)]">{{ $tag }}</span>
				@endforeach
			</div>
		</section>

		{{-- FAQ --}}
		<section class="mt-10">
			<div class="mb-4">
				<h2 class="text-2xl sm:text-3xl font-bold mb-2">{{ $isEn ? 'Frequently asked questions' : 'Veelgestelde vragen' }}</h2>
				<p class="text-[color:var(--color-ink-muted)]">{{ $isEn ? 'Answers about websites, portals, integrations, security and technical optimisation.' : 'Antwoorden op vragen over websites, portals, koppelingen, beveiliging en technische optimalisatie.' }}</p>
			</div>
			<div class="grid md:grid-cols-2 gap-4">
				@foreach ($faq as $item)
					<article class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-5 shadow-[var(--shadow-soft)]">
						<h3 class="text-base font-bold mb-2">{{ $isEn ? $item['q_en'] : $item['q_nl'] }}</h3>
						<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $isEn ? $item['a_en'] : $item['a_nl'] }}</p>
					</article>
				@endforeach
			</div>
		</section>

		{{-- CTA --}}
		<section class="mt-10">
			<div class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-8 shadow-[var(--shadow-soft)]">
				<h2 class="text-2xl sm:text-3xl font-bold mb-3">{{ $isEn ? 'Need help with your website, portal or technical foundation?' : 'Hulp nodig met je website, portal of technische basis?' }}</h2>
				<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-5">
					{{ $isEn ? 'Want to know where the biggest improvements lie for your website, portal, integrations or workflows? Get in touch for practical advice and a clear approach.' : 'Wil je weten waar de grootste verbeterkansen liggen voor jouw website, klantportal, koppelingen of interne workflows? Neem contact op voor praktisch advies en een heldere aanpak.' }}
				</p>
				<div class="flex flex-wrap gap-3">
					<a href="/{{ $locale }}/contact" class="rounded-[var(--radius-control)] bg-[color:var(--color-ink)] text-white font-semibold px-5 py-3 hover:opacity-90 transition">{{ $isEn ? 'Request advice' : 'Vraag advies aan' }}</a>
					<a href="/{{ $locale }}/diensten" class="rounded-[var(--radius-control)] bg-white text-[color:var(--color-ink)] border border-[color:var(--color-line)] font-semibold px-5 py-3 hover:bg-gray-50 transition">{{ $isEn ? 'View all services' : 'Bekijk alle diensten' }}</a>
				</div>
			</div>
		</section>

	</div>
</div>
@endsection
