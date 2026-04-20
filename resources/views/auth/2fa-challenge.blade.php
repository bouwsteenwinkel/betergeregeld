@extends('layouts.app')

@section('title', __('Twee-staps verificatie') . ' — ' . config('app.name'))

@section('content')
	<div class="max-w-md mx-auto">
		<h1 class="text-2xl font-bold mb-2">{{ __('Twee-staps verificatie') }}</h1>
		<p class="text-sm text-[color:var(--color-ink-muted)] mb-6">
			{{ __('Voer de 6-cijferige code uit je authenticator-app in.') }}
		</p>

		<form method="POST" action="{{ route('2fa.challenge') }}"
			class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-6 shadow-[var(--shadow-soft)] space-y-4">
			@csrf

			<div>
				<label for="code" class="block text-sm font-semibold mb-1.5">{{ __('Code') }}</label>
				<input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
					pattern="[0-9]*" maxlength="8" autofocus
					class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3 text-base tracking-widest text-center font-mono focus:outline-none focus:ring-2 focus:ring-black/10">
			</div>

			<details class="text-sm">
				<summary class="cursor-pointer text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">
					{{ __('Geen toegang? Gebruik een backup-code') }}
				</summary>
				<div class="mt-3">
					<input name="backup_code" type="text" placeholder="XXXXXXXXXX"
						class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3 text-base font-mono uppercase focus:outline-none focus:ring-2 focus:ring-black/10">
				</div>
			</details>

			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">
					{{ $errors->first() }}
				</div>
			@endif

			<button type="submit"
				class="w-full rounded-[var(--radius-control)] bg-[color:var(--color-ink)] text-white font-semibold px-4 py-3 hover:opacity-90 transition">
				{{ __('Verifiëren') }}
			</button>
		</form>
	</div>
@endsection
