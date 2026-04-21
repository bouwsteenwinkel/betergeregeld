@extends('layouts.app')

@section('title', __('Inloggen') . ' — ' . config('app.name'))

@section('content')
<section class="py-16 bg-[color:var(--color-surface)] min-h-[70vh] flex items-center">
	<div class="max-w-md mx-auto w-full px-6">
		<div class="text-center mb-8">
			<h1 class="display-2 mb-2">{{ __('Inloggen') }}</h1>
			<p class="text-[color:var(--color-ink-muted)]">{{ __('Welkom terug.') }}</p>
		</div>

		<form method="POST" action="{{ route('login') }}" class="card space-y-5">
			@csrf

			<div>
				<label for="email" class="block text-sm font-semibold mb-2">{{ __('E-mail') }}</label>
				<input id="email" name="email" type="email" autocomplete="email" required autofocus
					value="{{ old('email') }}" class="field-input">
			</div>

			<div>
				<label for="password" class="block text-sm font-semibold mb-2">{{ __('Wachtwoord') }}</label>
				<input id="password" name="password" type="password" autocomplete="current-password" required class="field-input">
			</div>

			<label class="flex items-center gap-2 text-sm text-[color:var(--color-ink-muted)]">
				<input type="checkbox" name="remember" value="1" class="rounded border-[color:var(--color-line)] accent-[color:var(--color-accent)]">
				<span>{{ __('Onthoud mij') }}</span>
			</label>

			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">{{ $errors->first() }}</div>
			@endif

			<button type="submit" class="btn-accent w-full justify-center">
				{{ __('Inloggen') }}
				<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>

			<p class="text-sm text-center text-[color:var(--color-ink-muted)]">
				{{ __('Nog geen account?') }}
				<a href="{{ route('register') }}" class="font-semibold text-[color:var(--color-accent-hover)] hover:underline">{{ __('Account aanmaken') }}</a>
			</p>
		</form>
	</div>
</section>
@endsection
