<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>@yield('title', config('app.name'))</title>
	<meta name="description" content="@yield('description', __('Beter Geregeld ICT helpt bedrijven met maatwerk websites, klantportalen, API-koppelingen, procesautomatisering, beveiliging en technische optimalisatie.'))">
	<link rel="icon" href="/favicon.ico">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
	@vite(['resources/css/app.css', 'resources/js/app.js'])
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
					<span class="font-bold text-[color:var(--color-ink)]">Beter Geregeld ICT</span>
					<span class="text-[11px] text-[color:var(--color-ink-soft)] hidden sm:inline">{{ __('Tools & automatisering') }}</span>
				</span>
			</a>

			<nav class="flex items-center gap-6 text-sm" aria-label="{{ __('Hoofdnavigatie') }}">
				<div class="hidden md:flex items-center gap-6 font-medium">
					<a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-[color:var(--color-ink)]' : 'text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]' }}">{{ __('Home') }}</a>
					<a href="/{{ $currentLocale }}/diensten" class="{{ request()->is('*diensten*') ? 'text-[color:var(--color-ink)]' : 'text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]' }}">{{ __('Diensten') }}</a>
					<a href="/{{ $currentLocale }}/tools/iban-check" class="{{ request()->is('*tools*') ? 'text-[color:var(--color-ink)]' : 'text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]' }}">{{ __('Tools') }}</a>
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
					<a href="{{ route('settings.2fa') }}" class="hidden lg:inline text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">2FA</a>
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
		</div>
	</header>

	<main class="flex-1 w-full" id="main-content">
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
						<li><a href="tel:+31352011729" class="hover:text-white">+31 35 201 1729</a></li>
						<li class="pt-1 text-xs text-[color:var(--color-on-dark-soft)]">T.B. Huurmanlaan 5<br>1403 SL Bussum</li>
					</ul>
				</div>
			</div>

			<div class="pt-6 border-t border-[color:var(--color-on-dark-line)] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs text-[color:var(--color-on-dark-soft)]">
				<div>&copy; {{ date('Y') }} Beter Geregeld ICT. {{ __('Alle rechten voorbehouden.') }}</div>
				<div class="flex gap-5">
					<span class="hover:text-white cursor-pointer">{{ __('Privacy') }}</span>
					<span class="hover:text-white cursor-pointer">{{ __('Voorwaarden') }}</span>
					<span class="hover:text-white cursor-pointer">{{ __('Cookiebeleid') }}</span>
				</div>
			</div>
		</div>
	</footer>
</body>
</html>
