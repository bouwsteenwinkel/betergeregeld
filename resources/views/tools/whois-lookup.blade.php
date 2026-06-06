@extends('layouts.app')

@section('title', __('WHOIS lookup + domain expiry') . ', ' . config('app.name'))
@section('description', __('Wie heeft dit domein geregistreerd, bij welke registrar, en wanneer verloopt het?'))

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[900px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<a href="/{{ app()->getLocale() }}/tools" class="hover:text-white">Tools</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">WHOIS</span>
		</nav>
		<span class="pill pill-dark mb-5">Tool · {{ __('Gratis') }}</span>
		<h1 class="display-1 mb-5">WHOIS <span class="accent-word">{{ __('lookup') }}</span></h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			{{ __('Registratie-info en vervaldatum van een domein. Werkt voor .nl, .com, .net, .org, .be, .de, .fr, .es, .eu, .io, .app, .dev en meer.') }}
		</p>
	</div>
</section>

<section class="py-16">
	<div class="max-w-[900px] mx-auto px-6">
		<form method="POST" action="{{ route('tools.whois-lookup.check') }}" class="card space-y-5">
			@csrf
			<div>
				<label for="domain" class="block text-sm font-semibold mb-2">{{ __('Domeinnaam') }}</label>
				<input id="domain" name="domain" type="text" required value="{{ $domain }}" autocomplete="off"
					placeholder="example.com" class="field-input font-mono">
			</div>
			@if ($error)
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">{{ $error }}</div>
			@endif
			<button type="submit" class="btn-accent">
				WHOIS {{ __('opvragen') }}
				<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
		</form>

		@if ($result)
			@if (empty($result['success']))
				<div class="mt-8 card border-l-4 border-l-red-500">
					<h3 class="font-bold mb-2">{{ __('Geen WHOIS-data') }}</h3>
					<p class="text-sm">{{ $result['error'] ?? '?' }}</p>
				</div>
			@else
				@php
					$color = ['ok'=>'green','warn'=>'amber','err'=>'red'][$result['verdict']] ?? 'gray';
				@endphp
				<div class="mt-8 card border-l-4
					@if ($color === 'green') border-l-green-500
					@elseif ($color === 'amber') border-l-amber-500
					@else border-l-red-500
					@endif">
					<div class="flex items-baseline justify-between mb-4">
						<h2 class="display-3">{{ $result['domain'] }}</h2>
						@isset ($result['expiry_days'])
							<span class="text-xs font-semibold uppercase
								@if ($color === 'green') text-green-700
								@elseif ($color === 'amber') text-amber-700
								@else text-red-700
								@endif">
								{{ $result['expiry_days'] >= 0 ? $result['expiry_days'] . ' dagen geldig' : 'verlopen' }}
							</span>
						@endisset
					</div>

					<dl class="grid sm:grid-cols-[180px_1fr] gap-y-2 gap-x-4 text-sm">
						<dt class="text-[color:var(--color-ink-muted)]">Registrar</dt><dd>{{ $result['registrar'] ?? ', ' }}</dd>
						<dt class="text-[color:var(--color-ink-muted)]">Registrant</dt><dd>{{ $result['registrant'] ?? ', ' }}</dd>
						<dt class="text-[color:var(--color-ink-muted)]">{{ __('Aangemaakt') }}</dt><dd>{{ $result['created_at'] ?? ', ' }}</dd>
						<dt class="text-[color:var(--color-ink-muted)]">{{ __('Laatst bijgewerkt') }}</dt><dd>{{ $result['updated_at'] ?? ', ' }}</dd>
						<dt class="text-[color:var(--color-ink-muted)]">{{ __('Verloopt') }}</dt><dd class="font-semibold">{{ $result['expiry_at'] ?? ', ' }}</dd>
						<dt class="text-[color:var(--color-ink-muted)]">WHOIS server</dt><dd class="font-mono text-xs">{{ $result['whois_server'] }}</dd>
					</dl>

					@if (!empty($result['nameservers']))
						<div class="mt-4 pt-4 border-t border-[color:var(--color-line)]">
							<div class="text-xs text-[color:var(--color-ink-muted)] mb-2">Nameservers ({{ count($result['nameservers']) }})</div>
							<div class="flex flex-wrap gap-1.5">
								@foreach ($result['nameservers'] as $ns)
									<code class="text-xs bg-[color:var(--color-surface)] border border-[color:var(--color-line)] rounded px-2 py-0.5">{{ $ns }}</code>
								@endforeach
							</div>
						</div>
					@endif

					@if (!empty($result['status']))
						<div class="mt-4 pt-4 border-t border-[color:var(--color-line)]">
							<div class="text-xs text-[color:var(--color-ink-muted)] mb-2">Status</div>
							<div class="flex flex-wrap gap-1.5">
								@foreach ($result['status'] as $s)
									<code class="text-xs bg-[color:var(--color-surface)] border border-[color:var(--color-line)] rounded px-2 py-0.5">{{ $s }}</code>
								@endforeach
							</div>
						</div>
					@endif

					@if (!empty($result['notes']))
						<ul class="mt-4 pt-4 border-t border-[color:var(--color-line)] text-sm space-y-1">
							@foreach ($result['notes'] as $note)
								<li>· {{ $note }}</li>
							@endforeach
						</ul>
					@endif

					<details class="mt-6">
						<summary class="text-xs text-[color:var(--color-ink-muted)] cursor-pointer">{{ __('Toon ruwe WHOIS-output') }}</summary>
						<pre class="mt-2 text-xs font-mono bg-[color:var(--color-surface)] p-3 rounded border border-[color:var(--color-line)] overflow-x-auto whitespace-pre-wrap">{{ $result['raw_excerpt'] ?? '' }}</pre>
					</details>
				</div>
			@endif

			<div class="mt-10 card bg-[color:var(--color-surface)]">
				<h3 class="font-bold mb-2">{{ __('Domein bijna verlopen of verkeerd geregistreerd?') }}</h3>
				<p class="text-sm text-[color:var(--color-ink-muted)] mb-4">
					{{ __('Wij helpen met verlengen, verhuizen of het rechtzetten van de registratie-gegevens, zonder dataverlies of downtime.') }}
				</p>
				<a href="/{{ app()->getLocale() }}/diensten/website-migratie-zonder-gedoe" class="btn-accent">
					{{ __('Migratie of verhuizing laten regelen') }}
					<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</a>
			</div>
		@endif
	</div>
</section>

@endsection
