@extends('layouts.app')

@section('title', __('Twee-staps verificatie') . ', ' . config('app.name'))

@section('content')
	<div class="max-w-2xl">
		<h1 class="text-3xl font-bold mb-2">{{ __('Twee-staps verificatie') }}</h1>
		<p class="text-[color:var(--color-ink-muted)] mb-8">
			{{ __('Beveilig je account met een extra code uit een authenticator-app.') }}
		</p>

		@if ($enabled)
			<div class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-6 shadow-[var(--shadow-soft)] mb-6">
				<div class="flex items-center gap-3 mb-4">
					<span class="inline-flex items-center rounded-full bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-1">
						{{ __('Actief') }}
					</span>
					<p class="text-sm">{{ __('Twee-staps verificatie staat aan voor dit account.') }}</p>
				</div>

				@if ($backupCodes)
					<div class="rounded-[var(--radius-control)] border border-amber-200 bg-amber-50 p-4 mb-4">
						<p class="text-sm font-semibold text-amber-900 mb-2">
							{{ __('Bewaar deze backup-codes op een veilige plek.') }}
						</p>
						<p class="text-xs text-amber-800 mb-3">
							{{ __('Elke code is één keer bruikbaar. Je ziet ze alleen nu.') }}
						</p>
						<ul class="grid grid-cols-2 gap-1 font-mono text-sm">
							@foreach ($backupCodes as $c)
								<li>{{ $c }}</li>
							@endforeach
						</ul>
					</div>
				@endif

				<form method="POST" action="{{ route('settings.2fa.disable') }}" class="space-y-3">
					@csrf
					<label for="password" class="block text-sm font-semibold">
						{{ __('Bevestig met wachtwoord om uit te schakelen') }}
					</label>
					<input id="password" name="password" type="password" required autocomplete="current-password"
						class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3 focus:outline-none focus:ring-2 focus:ring-black/10">
					@if ($errors->any())
						<div class="text-sm text-red-800">{{ $errors->first() }}</div>
					@endif
					<button type="submit"
						class="rounded-[var(--radius-control)] bg-white text-red-700 border border-red-300 font-semibold px-4 py-2.5 hover:bg-red-50 transition">
						{{ __('2FA uitschakelen') }}
					</button>
				</form>
			</div>
		@else
			<div class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-6 shadow-[var(--shadow-soft)]">
				<ol class="list-decimal list-inside space-y-4 text-sm">
					<li>{{ __('Installeer een authenticator-app (Google Authenticator, 1Password, Authy, etc.).') }}</li>
					<li>
						{{ __('Scan deze QR-code:') }}
						<div class="mt-3 bg-white p-4 rounded-[var(--radius-control)] border border-[color:var(--color-line)] inline-block">
							<img src="{{ $qr }}" alt="QR" class="w-48 h-48">
						</div>
						<p class="text-xs text-[color:var(--color-ink-muted)] mt-2">
							{{ __('Of voer deze sleutel handmatig in:') }}
							<code class="font-mono">{{ $secret }}</code>
						</p>
					</li>
					<li>
						{{ __('Typ de 6-cijferige code uit de app:') }}
						<form method="POST" action="{{ route('settings.2fa.enable') }}" class="mt-3 space-y-3">
							@csrf
							<input name="code" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="8" required
								class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3 text-base tracking-widest text-center font-mono focus:outline-none focus:ring-2 focus:ring-black/10">
							@if ($errors->any())
								<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">
									{{ $errors->first() }}
								</div>
							@endif
							<button type="submit"
								class="rounded-[var(--radius-control)] bg-[color:var(--color-ink)] text-white font-semibold px-5 py-3 hover:opacity-90 transition">
								{{ __('2FA inschakelen') }}
							</button>
						</form>
					</li>
				</ol>
			</div>
		@endif
	</div>
@endsection
