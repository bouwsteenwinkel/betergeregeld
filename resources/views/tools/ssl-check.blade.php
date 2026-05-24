@extends('layouts.app')

@section('title', __('SSL/TLS certificaat checker') . ' — ' . config('app.name'))
@section('description', __('Bekijk uitgever, vervaldatum, SANs en sleutel-info van het SSL-certificaat van een domein.'))

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[900px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<a href="/{{ app()->getLocale() }}/tools" class="hover:text-white">Tools</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">SSL-check</span>
		</nav>
		<span class="pill pill-dark mb-5">Tool · {{ __('Gratis') }}</span>
		<h1 class="display-1 mb-5">SSL/TLS <span class="accent-word">{{ __('check') }}</span></h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			{{ __('Live TLS-handshake — wij lezen het certificaat en tonen uitgever, vervaldatum, SANs, sleutel-info en waarschuwen bij issues.') }}
		</p>
	</div>
</section>

<section class="py-16">
	<div class="max-w-[900px] mx-auto px-6">
		<form method="POST" action="{{ route('tools.ssl-check.check') }}" class="card space-y-5">
			@csrf
			<div>
				<label for="domain" class="block text-sm font-semibold mb-2">{{ __('Domeinnaam') }}</label>
				<input id="domain" name="domain" type="text" required value="{{ $domain }}" autocomplete="off"
					placeholder="example.com" class="field-input font-mono">
			</div>
			@if ($error)
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">
					{{ $error }}
				</div>
			@endif
			<button type="submit" class="btn-accent">
				{{ __('Certificaat controleren') }}
				<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
		</form>

		@if ($result)
			@if (!($result['success'] ?? false))
				<div class="mt-8 card border-l-4 border-l-red-500">
					<h3 class="font-bold mb-2">{{ __('Geen handshake mogelijk') }}</h3>
					<p class="text-sm">{{ $result['error'] }}</p>
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
						<h2 class="display-3">{{ $result['host'] }}</h2>
						<span class="text-xs font-semibold uppercase
							@if ($color === 'green') text-green-700
							@elseif ($color === 'amber') text-amber-700
							@else text-red-700
							@endif">
							@if ($result['days_left'] !== null && $result['days_left'] >= 0)
								{{ $result['days_left'] }} {{ __('dagen geldig') }}
							@elseif ($result['days_left'] !== null)
								{{ __('VERLOPEN') }}
							@endif
						</span>
					</div>

					<dl class="grid sm:grid-cols-[180px_1fr] gap-y-2 gap-x-4 text-sm">
						<dt class="text-[color:var(--color-ink-muted)]">{{ __('Common Name') }}</dt><dd class="font-mono">{{ $result['subject_cn'] ?? '?' }}</dd>
						<dt class="text-[color:var(--color-ink-muted)]">{{ __('Uitgever') }}</dt><dd>{{ $result['issuer_cn'] ?? '?' }} <span class="text-xs text-[color:var(--color-ink-muted)]">({{ $result['issuer_o'] ?? '?' }})</span></dd>
						<dt class="text-[color:var(--color-ink-muted)]">{{ __('Geldig vanaf') }}</dt><dd>{{ $result['valid_from'] }}</dd>
						<dt class="text-[color:var(--color-ink-muted)]">{{ __('Geldig tot') }}</dt><dd>{{ $result['valid_to'] }}</dd>
						<dt class="text-[color:var(--color-ink-muted)]">{{ __('Signature') }}</dt><dd class="font-mono text-xs">{{ $result['signature'] }}</dd>
						<dt class="text-[color:var(--color-ink-muted)]">{{ __('Public key') }}</dt><dd>{{ strtoupper($result['key_type']) }} {{ $result['key_bits'] }} bits</dd>
						<dt class="text-[color:var(--color-ink-muted)]">{{ __('Serial') }}</dt><dd class="font-mono text-xs break-all">{{ $result['serial'] }}</dd>
						<dt class="text-[color:var(--color-ink-muted)]">{{ __('Chain') }}</dt><dd>{{ $result['chain_count'] }} {{ __('certificaten') }}</dd>
					</dl>

					@if (!empty($result['sans']))
						<div class="mt-4 pt-4 border-t border-[color:var(--color-line)]">
							<div class="text-xs text-[color:var(--color-ink-muted)] mb-2">Subject Alternative Names ({{ count($result['sans']) }})</div>
							<div class="flex flex-wrap gap-1.5">
								@foreach ($result['sans'] as $san)
									<code class="text-xs bg-[color:var(--color-surface)] border border-[color:var(--color-line)] rounded px-2 py-0.5">{{ $san }}</code>
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
				</div>
			@endif

			<div class="mt-10 card bg-[color:var(--color-surface)]">
				<h3 class="font-bold mb-2">{{ __('Certificaat-issues of een verlopen cert?') }}</h3>
				<p class="text-sm text-[color:var(--color-ink-muted)] mb-4">
					{{ __('Wij regelen auto-renewal, herstellen kapotte chains en zetten je website-beveiliging op orde.') }}
				</p>
				<a href="/{{ app()->getLocale() }}/diensten/website-beveiligen" class="btn-accent">
					{{ __('Website-beveiliging laten regelen') }}
					<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</a>
			</div>
		@endif
	</div>
</section>

@endsection
