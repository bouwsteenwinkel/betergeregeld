@extends('layouts.app')

@section('title', __('Account aanmaken') . ', ' . config('app.name'))
@section('robots', 'noindex,nofollow')

@section('content')
<section class="py-16 bg-[color:var(--color-surface)] min-h-[70vh] flex items-center">
	<div class="max-w-md mx-auto w-full px-6">
		<div class="text-center mb-8">
			<h1 class="display-2 mb-2">{{ __('Account aanmaken') }}</h1>
			<p class="text-[color:var(--color-ink-muted)]">{{ __('Welkom bij Beter Geregeld.') }}</p>
		</div>

		<form method="POST" action="{{ route('register') }}" class="card space-y-5">
			@csrf

			<div>
				<label for="name" class="block text-sm font-semibold mb-2">{{ __('Naam / bedrijf') }}</label>
				<input id="name" name="name" type="text" required autofocus value="{{ old('name') }}" class="field-input">
			</div>

			<div>
				<label for="email" class="block text-sm font-semibold mb-2">{{ __('E-mail') }}</label>
				<input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" class="field-input">
			</div>

			<div>
				<label for="password" class="block text-sm font-semibold mb-2">{{ __('Wachtwoord') }}</label>
				<input id="password" name="password" type="password" autocomplete="new-password" required class="field-input">
				<p class="text-xs text-[color:var(--color-ink-soft)] mt-1.5">{{ __('Minimaal 10 tekens.') }}</p>
			</div>

			<div>
				<label for="password_confirmation" class="block text-sm font-semibold mb-2">{{ __('Wachtwoord bevestigen') }}</label>
				<input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="field-input">
			</div>

			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3 space-y-1">
					@foreach ($errors->all() as $err)<div>{{ $err }}</div>@endforeach
				</div>
			@endif

			<button type="submit" class="btn-accent w-full justify-center">
				{{ __('Account aanmaken') }}
				<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>

			<p class="text-sm text-center text-[color:var(--color-ink-muted)]">
				{{ __('Al een account?') }}
				<a href="{{ route('login') }}" class="font-semibold text-[color:var(--color-accent-hover)] hover:underline">{{ __('Inloggen') }}</a>
			</p>
		</form>
	</div>
</section>
@endsection
