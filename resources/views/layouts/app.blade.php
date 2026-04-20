<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>@yield('title', config('app.name'))</title>
	<link rel="icon" href="/favicon.ico">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
	@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased min-h-screen flex flex-col">
	<header class="border-b border-[color:var(--color-line)]">
		<div class="max-w-[1400px] mx-auto px-6 py-4 flex items-center justify-between">
			<a href="{{ route('home') }}" class="text-lg font-bold tracking-tight">
				{{ config('app.name') }}
			</a>
			<nav class="flex items-center gap-5 text-sm">
				@auth
					<span class="text-[color:var(--color-ink-muted)]">{{ Auth::user()->email }}</span>
					<form method="POST" action="{{ route('logout') }}">
						@csrf
						<button type="submit" class="underline hover:no-underline">Uitloggen</button>
					</form>
				@else
					<a href="{{ route('login') }}" class="underline hover:no-underline">Inloggen</a>
				@endauth
			</nav>
		</div>
	</header>

	<main class="flex-1 max-w-[1400px] mx-auto w-full px-6 py-10">
		@yield('content')
	</main>

	<footer class="border-t border-[color:var(--color-line)]">
		<div class="max-w-[1400px] mx-auto px-6 py-6 text-sm text-[color:var(--color-ink-muted)]">
			&copy; {{ date('Y') }} {{ config('app.name') }} — software-partner.
		</div>
	</footer>
</body>
</html>
