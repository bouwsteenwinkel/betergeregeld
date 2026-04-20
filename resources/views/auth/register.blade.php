@extends('layouts.app')

@section('title', __('Account aanmaken') . ' — ' . config('app.name'))

@section('content')
	<div class="max-w-md mx-auto">
		<h1 class="text-2xl font-bold mb-6">{{ __('Account aanmaken') }}</h1>

		<form method="POST" action="{{ route('register') }}"
			class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-6 shadow-[var(--shadow-soft)] space-y-4">
			@csrf

			<div>
				<label for="name" class="block text-sm font-semibold mb-1.5">{{ __('Naam / bedrijf') }}</label>
				<input id="name" name="name" type="text" required autofocus value="{{ old('name') }}"
					class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3 text-base focus:outline-none focus:ring-2 focus:ring-black/10">
			</div>

			<div>
				<label for="email" class="block text-sm font-semibold mb-1.5">{{ __('E-mail') }}</label>
				<input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
					class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3 text-base focus:outline-none focus:ring-2 focus:ring-black/10">
			</div>

			<div>
				<label for="password" class="block text-sm font-semibold mb-1.5">{{ __('Wachtwoord') }}</label>
				<input id="password" name="password" type="password" autocomplete="new-password" required
					class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3 text-base focus:outline-none focus:ring-2 focus:ring-black/10">
				<p class="text-xs text-[color:var(--color-ink-muted)] mt-1">{{ __('Minimaal 10 tekens.') }}</p>
			</div>

			<div>
				<label for="password_confirmation" class="block text-sm font-semibold mb-1.5">{{ __('Wachtwoord bevestigen') }}</label>
				<input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
					class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3 text-base focus:outline-none focus:ring-2 focus:ring-black/10">
			</div>

			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3 space-y-1">
					@foreach ($errors->all() as $err)
						<div>{{ $err }}</div>
					@endforeach
				</div>
			@endif

			<button type="submit"
				class="w-full rounded-[var(--radius-control)] bg-[color:var(--color-ink)] text-white font-semibold px-4 py-3 hover:opacity-90 transition">
				{{ __('Account aanmaken') }}
			</button>

			<p class="text-sm text-center text-[color:var(--color-ink-muted)]">
				{{ __('Al een account?') }}
				<a href="{{ route('login') }}" class="underline hover:no-underline">{{ __('Inloggen') }}</a>
			</p>
		</form>
	</div>
@endsection
