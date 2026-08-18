<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@php
	$__bg_locale  = app()->getLocale();
	$__bg_path    = request()->path();
	$__bg_pparts  = explode('/', ltrim($__bg_path, '/'));
	$__bg_tail    = count($__bg_pparts) > 1 ? '/' . implode('/', array_slice($__bg_pparts, 1)) : '';
	$__bg_canon_path = '/' . $__bg_locale . $__bg_tail;
	$__bg_canonical  = url($__bg_canon_path);
	// Een view mag de canonical overschrijven. Nodig voor paginering: het pad
	// hierboven komt uit request()->path() en bevat dus géén querystring,
	// waardoor /nl/blog?page=3 naar /nl/blog canonicaliseerde. Zo'n pagina
	// zegt dan tegen Google "ik ben niet echt" terwijl er 29 artikelen op
	// staan die nergens anders vandaan gelinkt worden — in augustus 2026
	// stonden er 329 blog-artikelen als "gevonden, nooit gecrawld".
	// De channel-blog deed dit al goed (channels/blog/index.blade.php:6).
	$__bg_canon_override = trim((string) View::yieldContent('canonical'));
	if ($__bg_canon_override !== '') $__bg_canonical = $__bg_canon_override;
	// yieldContent kan een al door Blade ge-escapete string opleveren; eerst
	// terug naar plain text decoden, daarna laat Blade's {{ }} ze opnieuw
	// nét één keer escapen.
	$__bg_og_title_raw = html_entity_decode((string) View::yieldContent('title'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
	$__bg_og_desc_raw  = html_entity_decode((string) View::yieldContent('description'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
	$__bg_og_title = trim($__bg_og_title_raw) !== '' ? trim($__bg_og_title_raw) : config('app.name');
	$__bg_og_desc  = trim($__bg_og_desc_raw) !== ''
		? trim($__bg_og_desc_raw)
		: __('Beter Geregeld ICT helpt bedrijven met maatwerk websites, klantportalen, API-koppelingen, procesautomatisering, beveiliging en technische optimalisatie.');
@endphp
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="robots" content="@yield('robots', 'index,follow,max-image-preview:large,max-snippet:-1')">

	{{-- Local SEO + mobile / browser hints --}}
	<meta name="geo.region" content="NL">
	<meta name="geo.placename" content="Bussum">
	<meta name="geo.position" content="52.275;5.166">
	<meta name="ICBM" content="52.275, 5.166">
	<meta name="theme-color" content="#0f172a">
	<meta name="format-detection" content="telephone=no">
	<meta name="apple-mobile-web-app-title" content="Beter Geregeld">
	<meta name="application-name" content="Beter Geregeld">

	<title>@yield('title', config('app.name'))</title>
	<meta name="description" content="@yield('description', __('Beter Geregeld ICT helpt bedrijven met maatwerk websites, klantportalen, API-koppelingen, procesautomatisering, beveiliging en technische optimalisatie.'))">

	{{-- Canonical + hreflang per locale (per-page override via @push('head')).

		 ALLEEN TALEN DIE ER ECHT ZIJN. Dit lijstje stond op SetLocale::SUPPORTED en wisselde blind
		 het taalvoorvoegsel in de URL om. Dat klopt voor de vaste pagina's — die bestaan in beide
		 talen — maar niet voor de blog: 357 gepubliceerde artikelen bestaan alleen in het
		 Nederlands, en die riepen zo tegen Google "er is ook een Engelse versie" terwijl /en/blog/…
		 een 410 geeft. Search Console meldde daardoor 303 pagina's als "Niet gevonden" (410 valt in
		 diezelfde bak) en de validatie van 03-07-2026 mislukte, want aan die URL's veranderde niets.

		 Een view die het beter weet geeft $hreflangLocales mee; de rest houdt alle ondersteunde
		 talen, want daar is het wél waar. --}}
	@php
		$__bg_alts     = \App\Support\Hreflang::alternates($hreflangLocales ?? null);
		$__bg_xdefault = \App\Support\Hreflang::xDefault($__bg_alts);
	@endphp
	<link rel="canonical" href="{{ $__bg_canonical }}">
	@foreach ($__bg_alts as $__bg_loc)
		<link rel="alternate" hreflang="{{ $__bg_loc }}" href="{{ url('/' . $__bg_loc . $__bg_tail) }}">
	@endforeach
	@if ($__bg_xdefault)
		<link rel="alternate" hreflang="x-default" href="{{ url('/' . $__bg_xdefault . $__bg_tail) }}">
	@endif

	{{-- Open Graph defaults (per-page override via @section('og_type', '...') etc) --}}
	<meta property="og:type" content="@yield('og_type', 'website')">
	<meta property="og:locale" content="{{ str_replace('-', '_', $__bg_locale) }}_NL">
	<meta property="og:site_name" content="Beter Geregeld ICT">
	<meta property="og:title" content="{{ $__bg_og_title }}">
	<meta property="og:description" content="{{ $__bg_og_desc }}">
	<meta property="og:url" content="{{ $__bg_canonical }}">
	<meta property="og:image" content="@yield('og_image', url('/og-image.png'))">
	<meta property="og:image:width" content="1200">
	<meta property="og:image:height" content="630">
	<meta property="og:image:alt" content="Beter Geregeld ICT, maatwerk websites, koppelingen en automatisering">
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:image" content="@yield('og_image', url('/og-image.png'))">

	{{-- SVG eerst: die schaalt scherp mee op elk scherm. De .ico blijft staan voor
	     oudere browsers en voor /favicon.ico, waar een browser sowieso naar vraagt.
	     Zelfde volgorde als channels/partials/favicon.blade.php. --}}
	<link rel="icon" href="/favicon.svg" type="image/svg+xml">
	<link rel="icon" href="/favicon.ico" sizes="32x32">
	<link rel="apple-touch-icon" href="/apple-touch-icon.png">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
	@vite(['resources/css/app.css', 'resources/js/app.js'])

	{{-- Organization + LocalBusiness baseline JSON-LD --}}
	<script type="application/ld+json">
	{!! json_encode([
		"\x40context"    => 'https://schema.org',
		"\x40type"       => 'Organization',
		"\x40id"         => url('/#organization'),
		'name'        => 'Beter Geregeld ICT',
		'alternateName' => 'Beter Geregeld',
		'url'         => url('/'),
		'logo'        => url('/favicon.ico'),
		'email'       => 'info@betergeregeld.com',
		'telephone'   => '+31882545101',
		'foundingDate' => '1989',
		'address'     => [
			"\x40type"           => 'PostalAddress',
			'streetAddress'   => 'T.B. Huurmanlaan 5',
			'postalCode'      => '1403 SL',
			'addressLocality' => 'Bussum',
			'addressCountry'  => 'NL',
		],
		'sameAs'      => [],
	], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
	</script>
	<script type="application/ld+json">
	{!! json_encode([
		"\x40context"   => 'https://schema.org',
		"\x40type"      => 'WebSite',
		"\x40id"        => url('/#website'),
		'name'          => 'Beter Geregeld ICT',
		'alternateName' => 'Beter Geregeld',
		'url'           => url('/'),
		'inLanguage'    => ['nl-NL', 'en-NL'],
		'publisher'     => ["\x40id" => url('/#organization')],
	], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
	</script>
	<script type="application/ld+json">
	{!! json_encode([
		"\x40context"    => 'https://schema.org',
		"\x40type"       => 'LocalBusiness',
		"\x40id"         => url('/#localbusiness'),
		'name'        => 'Beter Geregeld ICT',
		'image'       => url('/favicon.ico'),
		'url'         => url('/'),
		'email'       => 'info@betergeregeld.com',
		'telephone'   => '+31882545101',
		'priceRange'  => '€€',
		'address'     => [
			"\x40type"           => 'PostalAddress',
			'streetAddress'   => 'T.B. Huurmanlaan 5',
			'postalCode'      => '1403 SL',
			'addressLocality' => 'Bussum',
			'addressCountry'  => 'NL',
		],
	], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
	</script>

	@stack('head')
</head>
<body class="font-sans antialiased min-h-screen flex flex-col">
	@php
		$currentLocale = app()->getLocale();
		$pathParts = explode('/', ltrim(request()->path(), '/'));
		$pathTail = count($pathParts) > 1 ? '/' . implode('/', array_slice($pathParts, 1)) : '';
	@endphp

	<header class="sticky top-0 z-40 bg-white/85 backdrop-blur border-b border-[color:var(--color-line)]">
		<div class="max-w-[1400px] mx-auto px-6 py-4 flex items-center justify-between gap-6">
			<a href="{{ route('home') }}" class="flex items-center gap-3 group">
				<span class="flex items-center justify-center w-9 h-9 rounded-lg bg-[color:var(--color-ink)] text-white font-black text-sm tracking-tight">BG</span>
				<span class="flex flex-col leading-tight">
					<span class="font-bold text-[color:var(--color-ink)] whitespace-nowrap">Beter Geregeld ICT</span>
					<span class="text-[11px] text-[color:var(--color-ink-soft)] hidden sm:inline">{{ __('Tools & automatisering') }}</span>
				</span>
			</a>

			<nav class="hidden md:flex items-center gap-6 text-sm" aria-label="{{ __('Hoofdnavigatie') }}">
				<div class="flex items-center gap-6 font-medium">
					<a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-[color:var(--color-ink)]' : 'text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]' }}">{{ __('Home') }}</a>
					<a href="/{{ $currentLocale }}/diensten" class="{{ request()->is('*diensten*') ? 'text-[color:var(--color-ink)]' : 'text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]' }}">{{ __('Diensten') }}</a>
					<a href="/{{ $currentLocale }}/tools" class="{{ request()->is('*tools*') ? 'text-[color:var(--color-ink)]' : 'text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]' }}">{{ __('Tools') }}</a>
					<a href="/{{ $currentLocale }}/prijzen" class="{{ request()->is('*prijzen*') ? 'text-[color:var(--color-ink)]' : 'text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]' }}">{{ __('Prijzen') }}</a>
					<a href="/{{ $currentLocale }}/over" class="{{ request()->is('*over*') ? 'text-[color:var(--color-ink)]' : 'text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]' }}">{{ __('Over ons') }}</a>
					<a href="/{{ $currentLocale }}/contact" class="{{ request()->is('*contact*') ? 'text-[color:var(--color-ink)]' : 'text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]' }}">{{ __('Contact') }}</a>
				</div>

				<div class="flex items-center gap-1.5 text-xs font-semibold">
					@foreach (\App\Http\Middleware\SetLocale::SUPPORTED as $loc)
						<a href="/{{ $loc }}{{ $pathTail }}"
							class="px-2 py-1 rounded uppercase tracking-wider {{ $loc === $currentLocale ? 'bg-[color:var(--color-ink)] text-white' : 'text-[color:var(--color-ink-soft)] hover:text-[color:var(--color-ink)]' }}">
							{{ $loc }}
						</a>
					@endforeach
				</div>

				@auth
					<a href="{{ route('dashboard') }}" class="text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)] font-medium">{{ __('Dashboard') }}</a>
					<span class="hidden lg:inline text-[color:var(--color-ink-muted)] text-xs">{{ Auth::user()->email }}</span>
					<form method="POST" action="{{ route('logout') }}">
						@csrf
						<button type="submit" class="text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">{{ __('Uitloggen') }}</button>
					</form>
				@else
					<a href="{{ route('login') }}" class="hidden sm:inline text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">{{ __('Inloggen') }}</a>
					<a href="/{{ $currentLocale }}/contact" class="btn-accent text-sm">
						{{ __('Plan gesprek') }}
						<svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</a>
				@endauth
			</nav>

			{{-- Mobiel: hamburger-knop --}}
			<button type="button" id="bg-nav-toggle" class="md:hidden inline-flex items-center justify-center w-10 h-10 -mr-2 rounded-lg text-[color:var(--color-ink)] hover:bg-[color:var(--color-surface-2)]" aria-label="{{ __('Menu') }}" aria-expanded="false" aria-controls="bg-mobile-nav">
				<svg id="bg-nav-icon-open" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
				<svg id="bg-nav-icon-close" class="w-6 h-6 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
			</button>
		</div>

		{{-- Mobiel uitklap-menu --}}
		<div id="bg-mobile-nav" class="md:hidden hidden border-t border-[color:var(--color-line)] bg-white">
			<nav class="px-6 py-4 flex flex-col gap-1 text-[15px]" aria-label="{{ __('Mobiele navigatie') }}">
				<a href="{{ route('home') }}" class="py-2 {{ request()->routeIs('home') ? 'text-[color:var(--color-ink)] font-semibold' : 'text-[color:var(--color-ink-muted)]' }}">{{ __('Home') }}</a>
				<a href="/{{ $currentLocale }}/diensten" class="py-2 {{ request()->is('*diensten*') ? 'text-[color:var(--color-ink)] font-semibold' : 'text-[color:var(--color-ink-muted)]' }}">{{ __('Diensten') }}</a>
				<a href="/{{ $currentLocale }}/tools" class="py-2 {{ request()->is('*tools*') ? 'text-[color:var(--color-ink)] font-semibold' : 'text-[color:var(--color-ink-muted)]' }}">{{ __('Tools') }}</a>
				<a href="/{{ $currentLocale }}/prijzen" class="py-2 {{ request()->is('*prijzen*') ? 'text-[color:var(--color-ink)] font-semibold' : 'text-[color:var(--color-ink-muted)]' }}">{{ __('Prijzen') }}</a>
				<a href="/{{ $currentLocale }}/over" class="py-2 {{ request()->is('*over*') ? 'text-[color:var(--color-ink)] font-semibold' : 'text-[color:var(--color-ink-muted)]' }}">{{ __('Over ons') }}</a>
				<a href="/{{ $currentLocale }}/contact" class="py-2 {{ request()->is('*contact*') ? 'text-[color:var(--color-ink)] font-semibold' : 'text-[color:var(--color-ink-muted)]' }}">{{ __('Contact') }}</a>

				<div class="flex items-center gap-1.5 text-xs font-semibold pt-3 mt-2 border-t border-[color:var(--color-line)]">
					@foreach (\App\Http\Middleware\SetLocale::SUPPORTED as $loc)
						<a href="/{{ $loc }}{{ $pathTail }}" class="px-2 py-1 rounded uppercase tracking-wider {{ $loc === $currentLocale ? 'bg-[color:var(--color-ink)] text-white' : 'text-[color:var(--color-ink-soft)]' }}">{{ $loc }}</a>
					@endforeach
				</div>

				@auth
					<a href="{{ route('dashboard') }}" class="py-2 text-[color:var(--color-ink-muted)] font-medium mt-1">{{ __('Dashboard') }}</a>
					<form method="POST" action="{{ route('logout') }}" class="pt-1">
						@csrf
						<button type="submit" class="py-2 text-left text-[color:var(--color-ink-muted)]">{{ __('Uitloggen') }}</button>
					</form>
				@else
					<a href="{{ route('login') }}" class="py-2 text-[color:var(--color-ink-muted)] mt-1">{{ __('Inloggen') }}</a>
					<a href="/{{ $currentLocale }}/contact" class="btn-accent text-sm justify-center mt-2">
						{{ __('Plan gesprek') }}
						<svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</a>
				@endauth
			</nav>
		</div>
	</header>

	<script>
		(function () {
			var btn = document.getElementById('bg-nav-toggle');
			var panel = document.getElementById('bg-mobile-nav');
			if (!btn || !panel) return;
			var iOpen = document.getElementById('bg-nav-icon-open');
			var iClose = document.getElementById('bg-nav-icon-close');
			function set(open) {
				panel.classList.toggle('hidden', !open);
				btn.setAttribute('aria-expanded', open ? 'true' : 'false');
				if (iOpen) iOpen.classList.toggle('hidden', open);
				if (iClose) iClose.classList.toggle('hidden', !open);
			}
			btn.addEventListener('click', function () { set(panel.classList.contains('hidden')); });
			panel.addEventListener('click', function (e) { if (e.target.closest('a')) set(false); });
		})();
	</script>

	<main class="flex-1 w-full overflow-x-clip" id="main-content">
		@yield('content')
	</main>

	<footer class="mt-0 section-dark">
		<div class="max-w-[1400px] mx-auto px-6 pt-16 pb-8">
			<div class="grid lg:grid-cols-[2fr_1fr_1fr_1fr_1fr] gap-10 mb-12">
				<div>
					<div class="flex items-center gap-3 mb-4">
						<span class="flex items-center justify-center w-10 h-10 rounded-lg bg-[color:var(--color-accent)] text-[color:var(--color-ink)] font-black">BG</span>
						<span class="font-bold text-lg">Beter Geregeld ICT</span>
					</div>
					<p class="text-sm text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-sm">
						{{ $currentLocale === 'en'
							? 'Custom web applications, integrations and technical optimisation. Since 1989.'
							: 'Maatwerk webapplicaties, koppelingen en technische optimalisatie. Sinds 1989.' }}
					</p>
				</div>
				<div>
					<h3 class="font-semibold mb-4 text-sm tracking-wide uppercase text-[color:var(--color-on-dark-soft)]">{{ __('Diensten') }}</h3>
					<ul class="space-y-2 text-sm text-[color:var(--color-on-dark-muted)]">
						<li><a href="/{{ $currentLocale }}/diensten/seo-check" class="hover:text-white">SEO check</a></li>
						<li><a href="/{{ $currentLocale }}/diensten/2fa-implementeren" class="hover:text-white">2FA</a></li>
						<li><a href="/{{ $currentLocale }}/diensten/website-snelheid-verbeteren" class="hover:text-white">{{ $currentLocale === 'en' ? 'Speed' : 'Snelheid' }}</a></li>
						<li><a href="/{{ $currentLocale }}/diensten" class="hover:text-white">{{ $currentLocale === 'en' ? 'All services' : 'Alle diensten' }}</a></li>
					</ul>
				</div>
				<div>
					<h3 class="font-semibold mb-4 text-sm tracking-wide uppercase text-[color:var(--color-on-dark-soft)]">{{ __('Tools') }}</h3>
					<ul class="space-y-2 text-sm text-[color:var(--color-on-dark-muted)]">
						<li><a href="/{{ $currentLocale }}/tools/iban-check" class="hover:text-white">IBAN check</a></li>
						<li><a href="/{{ $currentLocale }}/tools/vat-check" class="hover:text-white">{{ __('VAT check') }}</a></li>
						<li><a href="/{{ $currentLocale }}/tools/postcode-check" class="hover:text-white">{{ __('Postcode check') }}</a></li>
						<li><a href="/{{ $currentLocale }}/tools/ip-lookup" class="hover:text-white">{{ __('IP lookup') }}</a></li>
						<li><a href="/{{ $currentLocale }}/tools/json-formatter" class="hover:text-white">JSON formatter</a></li>
						<li><a href="/{{ $currentLocale }}/tools/diff" class="hover:text-white">{{ __('Diff checker') }}</a></li>
						<li><a href="/{{ $currentLocale }}/tools/favicon-generator" class="hover:text-white">{{ __('Favicon generator') }}</a></li>
						<li><a href="/{{ $currentLocale }}/tools/speedtest" class="hover:text-white">{{ __('Speedtest') }}</a></li>
						<li><a href="/{{ $currentLocale }}/tools/pdf-merge" class="hover:text-white">{{ __('PDF merge') }}</a></li>
						<li><a href="/{{ $currentLocale }}/tools/pdf-redact" class="hover:text-white">{{ __('PDF redact') }}</a></li>
						<li><a href="/{{ $currentLocale }}/tools/boekhouden" class="hover:text-white">{{ __('Boekhouden') }}</a></li>
					</ul>
				</div>
				<div>
					<h3 class="font-semibold mb-4 text-sm tracking-wide uppercase text-[color:var(--color-on-dark-soft)]">{{ __('Bedrijf') }}</h3>
					<ul class="space-y-2 text-sm text-[color:var(--color-on-dark-muted)]">
						<li><a href="/{{ $currentLocale }}/over" class="hover:text-white">{{ __('Over ons') }}</a></li>
						<li><a href="/{{ $currentLocale }}/contact" class="hover:text-white">{{ __('Contact') }}</a></li>
					</ul>
				</div>
				<div>
					<h3 class="font-semibold mb-4 text-sm tracking-wide uppercase text-[color:var(--color-on-dark-soft)]">{{ __('Contact') }}</h3>
					<ul class="space-y-2 text-sm text-[color:var(--color-on-dark-muted)]">
						<li><a href="mailto:info@betergeregeld.com" class="hover:text-white">info@betergeregeld.com</a></li>
						<li><a href="tel:+31882545101" class="hover:text-white">088-2545101</a></li>
						<li class="pt-1 text-xs text-[color:var(--color-on-dark-soft)]">T.B. Huurmanlaan 5<br>1403 SL Bussum</li>
					</ul>
				</div>
			</div>

			<div class="pt-6 border-t border-[color:var(--color-on-dark-line)] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs text-[color:var(--color-on-dark-soft)]">
				<div>&copy; {{ date('Y') }} Beter Geregeld ICT. {{ __('Alle rechten voorbehouden.') }}</div>
				<div class="flex gap-5">
					<a href="{{ route('legal.privacy') }}" class="hover:text-white">{{ __('Privacy') }}</a>
					<a href="{{ route('legal.terms') }}" class="hover:text-white">{{ __('Voorwaarden') }}</a>
					<a href="{{ route('legal.cookies') }}" class="hover:text-white">{{ __('Cookiebeleid') }}</a>
				</div>
			</div>
		</div>
	</footer>

	@stack('scripts')
</body>
</html>
