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
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
	@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased min-h-screen flex flex-col">
	@php
		$currentLocale = app()->getLocale();
		$pathParts = explode('/', ltrim(request()->path(), '/'));
		$pathTail = count($pathParts) > 1 ? '/' . implode('/', array_slice($pathParts, 1)) : '';
	@endphp

	<header class="border-b border-[color:var(--color-line)] bg-white">
		<div class="max-w-[1400px] mx-auto px-6 py-4 flex items-center justify-between gap-6">
			<a href="{{ route('home') }}" class="flex items-baseline gap-2">
				<span class="text-lg font-bold tracking-tight">{{ __('Beter Geregeld ICT') }}</span>
				<span class="hidden sm:inline text-xs text-[color:var(--color-ink-muted)]">{{ __('Tools & automatisering') }}</span>
			</a>

			<nav class="flex items-center gap-5 text-sm" aria-label="{{ __('Hoofdnavigatie') }}">
				<div class="hidden md:flex items-center gap-5">
					<a href="{{ route('home') }}" class="hover:text-[color:var(--color-ink)] {{ request()->routeIs('home') ? 'font-semibold' : 'text-[color:var(--color-ink-muted)]' }}">{{ __('Home') }}</a>
					<a href="/{{ $currentLocale }}/tools/iban-check" class="text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">{{ __('Tools') }}</a>
					<a href="/{{ $currentLocale }}/over" class="text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">{{ __('Over ons') }}</a>
					<a href="/{{ $currentLocale }}/contact" class="text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">{{ __('Contact') }}</a>
				</div>

				<div class="flex items-center gap-2 text-[color:var(--color-ink-muted)]">
					@foreach (\App\Http\Middleware\SetLocale::SUPPORTED as $loc)
						<a href="/{{ $loc }}{{ $pathTail }}"
							class="uppercase {{ $loc === $currentLocale ? 'text-[color:var(--color-ink)] font-semibold' : 'hover:text-[color:var(--color-ink)]' }}">
							{{ $loc }}
						</a>
						@if (! $loop->last)<span class="opacity-40">/</span>@endif
					@endforeach
				</div>

				@auth
					<a href="{{ route('settings.2fa') }}" class="text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">2FA</a>
					<span class="hidden sm:inline text-[color:var(--color-ink-muted)]">{{ Auth::user()->email }}</span>
					<form method="POST" action="{{ route('logout') }}">
						@csrf
						<button type="submit" class="underline hover:no-underline">{{ __('Uitloggen') }}</button>
					</form>
				@else
					<a href="{{ route('login') }}" class="underline hover:no-underline">{{ __('Inloggen') }}</a>
					<a href="{{ route('register') }}" class="hidden sm:inline-flex rounded-[var(--radius-control)] bg-[color:var(--color-ink)] text-white font-semibold px-3 py-1.5 hover:opacity-90 transition">{{ __('Registreren') }}</a>
				@endauth
			</nav>
		</div>
	</header>

	<main class="flex-1 w-full" id="main-content">
		@yield('content')
	</main>

	<footer class="mt-16 border-t border-[color:var(--color-line)] bg-white">
		<div class="max-w-[1400px] mx-auto px-6 py-10">
			<div class="grid grid-cols-2 md:grid-cols-5 gap-8 text-sm">
				<div>
					<h3 class="font-semibold mb-3">{{ __('Product') }}</h3>
					<ul class="space-y-2 text-[color:var(--color-ink-muted)]">
						<li><a href="/{{ $currentLocale }}/tools/iban-check" class="hover:text-[color:var(--color-ink)]">{{ __('Overzicht') }}</a></li>
						<li><a href="/{{ $currentLocale }}/tools/iban-check" class="hover:text-[color:var(--color-ink)]">{{ __('Features') }}</a></li>
						<li><a href="/{{ $currentLocale }}/tools/iban-check" class="hover:text-[color:var(--color-ink)]">{{ __('Integraties') }}</a></li>
						<li><a href="/{{ $currentLocale }}/over" class="hover:text-[color:var(--color-ink)]">{{ __('Security') }}</a></li>
					</ul>
				</div>
				<div>
					<h3 class="font-semibold mb-3">{{ __('Oplossingen') }}</h3>
					<ul class="space-y-2 text-[color:var(--color-ink-muted)]">
						<li>{{ __('Voor teams') }}</li>
						<li>{{ __('Voor zzp') }}</li>
						<li>{{ __('Voor bedrijven') }}</li>
						<li>{{ __('Support') }}</li>
					</ul>
				</div>
				<div>
					<h3 class="font-semibold mb-3">{{ __('Bedrijf') }}</h3>
					<ul class="space-y-2 text-[color:var(--color-ink-muted)]">
						<li><a href="/{{ $currentLocale }}/over" class="hover:text-[color:var(--color-ink)]">{{ __('Over Betergeregeld ICT') }}</a></li>
						<li>{{ __('Werken bij') }}</li>
						<li>{{ __('Pers') }}</li>
						<li>{{ __('Blog') }}</li>
					</ul>
				</div>
				<div>
					<h3 class="font-semibold mb-3">{{ __('Juridisch') }}</h3>
					<ul class="space-y-2 text-[color:var(--color-ink-muted)]">
						<li>{{ __('Privacy') }}</li>
						<li>{{ __('Voorwaarden') }}</li>
						<li>{{ __('Cookiebeleid') }}</li>
						<li>{{ __('Disclaimer') }}</li>
					</ul>
				</div>
				<div>
					<h3 class="font-semibold mb-3">{{ __('Contact') }}</h3>
					<ul class="space-y-2 text-[color:var(--color-ink-muted)]">
						<li>Beter Geregeld ICT</li>
						<li>T.B. Huurmanlaan 5</li>
						<li>1403 SL Bussum</li>
						<li><a href="mailto:info@betergeregeld.com" class="hover:text-[color:var(--color-ink)]">info@betergeregeld.com</a></li>
						<li><a href="tel:+31352011729" class="hover:text-[color:var(--color-ink)]">+31 35 201 1729</a></li>
					</ul>
				</div>
			</div>

			<div class="mt-8 pt-6 border-t border-[color:var(--color-line)] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs text-[color:var(--color-ink-muted)]">
				<div>&copy; {{ date('Y') }} Beter Geregeld ICT. {{ __('Alle rechten voorbehouden.') }}</div>
				<div class="flex gap-4">
					<span>{{ __('Status') }}</span>
					<span>{{ __('Beveiliging') }}</span>
					<span>{{ __('Sitemap') }}</span>
				</div>
			</div>
		</div>
	</footer>
</body>
</html>
