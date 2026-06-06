@extends('layouts.app')

@section('title', __('SPF / DKIM / DMARC checker') . ', ' . config('app.name'))
@section('description', __('Controleer of de e-mailbeveiliging van een domein klopt: SPF, DKIM en DMARC in één keer.'))

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[900px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<a href="/{{ app()->getLocale() }}/tools" class="hover:text-white">Tools</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Mail-beveiliging</span>
		</nav>
		<span class="pill pill-dark mb-5">Tool · {{ __('Gratis') }}</span>
		<h1 class="display-1 mb-5">SPF · DKIM · <span class="accent-word">DMARC</span></h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			{{ __('Klopt jouw e-mail-authenticatie? Wij halen direct de drie belangrijkste DNS-records op en geven per record een verdict + verbeterpunten.') }}
		</p>
	</div>
</section>

<section class="py-16">
	<div class="max-w-[900px] mx-auto px-6">
		<form method="POST" action="{{ route('tools.mail-auth.check') }}" class="card space-y-5">
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
				{{ __('Controleren') }}
				<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
		</form>

		@if ($result)
			@php
				$verdictColor = ['ok' => 'green', 'warn' => 'amber', 'err' => 'red', 'missing' => 'red'];
				$verdictLabel = ['ok' => '✓ OK', 'warn' => '⚠ Aandachtspunt', 'err' => '✕ Probleem', 'missing' => ', Ontbreekt'];
				$renderBlock = function ($label, $data) use ($verdictColor, $verdictLabel) {
					$color = $verdictColor[$data['verdict']] ?? 'gray';
					return [$color, $verdictLabel[$data['verdict']] ?? '?', $label, $data];
				};
				$blocks = [
					$renderBlock('SPF',   $result['spf']),
					$renderBlock('DKIM',  $result['dkim']),
					$renderBlock('DMARC', $result['dmarc']),
				];
			@endphp

			<div class="mt-8 space-y-4">
				<h2 class="display-3">{{ __('Rapport voor') }} <code class="font-mono text-[color:var(--color-accent)]">{{ $result['domain'] }}</code></h2>

				@foreach ($blocks as [$color, $verdict, $label, $data])
					<div class="card border-l-4
						@if ($color === 'green') border-l-green-500
						@elseif ($color === 'amber') border-l-amber-500
						@else border-l-red-500
						@endif">
						<div class="flex items-baseline justify-between mb-3">
							<h3 class="font-bold text-lg">{{ $label }}</h3>
							<span class="text-xs font-semibold
								@if ($color === 'green') text-green-700
								@elseif ($color === 'amber') text-amber-700
								@else text-red-700
								@endif">{{ $verdict }}</span>
						</div>
						@if (!empty($data['value']))
							<pre class="text-xs font-mono bg-[color:var(--color-surface)] p-3 rounded border border-[color:var(--color-line)] overflow-x-auto mb-3 whitespace-pre-wrap break-all">{{ $data['value'] }}</pre>
						@endif
						@if (!empty($data['records']))
							@foreach ($data['records'] as $rec)
								<div class="text-xs text-[color:var(--color-ink-muted)] mb-1">Selector: <code>{{ $rec['selector'] }}</code></div>
								<pre class="text-xs font-mono bg-[color:var(--color-surface)] p-3 rounded border border-[color:var(--color-line)] overflow-x-auto mb-3 whitespace-pre-wrap break-all">{{ $rec['value'] }}</pre>
							@endforeach
						@endif
						<ul class="text-sm text-[color:var(--color-ink)] space-y-1">
							@foreach (($data['notes'] ?? []) as $note)
								<li>· {{ $note }}</li>
							@endforeach
						</ul>
					</div>
				@endforeach
			</div>

			<div class="mt-10 card bg-[color:var(--color-surface)]">
				<h3 class="font-bold mb-2">{{ __('Een of meer rode/gele blokken?') }}</h3>
				<p class="text-sm text-[color:var(--color-ink-muted)] mb-4">
					{{ __('Wij regelen SPF, DKIM en DMARC voor je in, inclusief de stappenplan-aanpak van p=none naar p=reject zonder dat legitieme mail in spam belandt.') }}
				</p>
				<a href="/{{ app()->getLocale() }}/diensten/mail-beveiliging-fix" class="btn-accent">
					{{ __('E-mail-beveiliging laten regelen') }}
					<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</a>
			</div>
		@endif
	</div>
</section>

@endsection
