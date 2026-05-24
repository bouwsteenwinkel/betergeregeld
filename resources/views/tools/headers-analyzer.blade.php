@extends('layouts.app')

@section('title', __('HTTP security headers analyzer') . ' — ' . config('app.name'))
@section('description', __('Krijg een A-F grade voor de security-headers van een website: HSTS, CSP, X-Frame-Options en meer.'))

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[900px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<a href="/{{ app()->getLocale() }}/tools" class="hover:text-white">Tools</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Security headers</span>
		</nav>
		<span class="pill pill-dark mb-5">Tool · {{ __('Gratis') }}</span>
		<h1 class="display-1 mb-5">Security <span class="accent-word">headers</span></h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			{{ __('Wij doen een HEAD-request naar je site, lezen alle security-headers en geven een A-F grade plus uitleg per ontbrekende header.') }}
		</p>
	</div>
</section>

<section class="py-16">
	<div class="max-w-[900px] mx-auto px-6">
		<form method="POST" action="{{ route('tools.headers-analyzer.check') }}" class="card space-y-5">
			@csrf
			<div>
				<label for="url" class="block text-sm font-semibold mb-2">URL</label>
				<input id="url" name="url" type="text" required value="{{ $url }}" autocomplete="off"
					placeholder="https://example.com" class="field-input font-mono">
			</div>
			<button type="submit" class="btn-accent">
				{{ __('Headers controleren') }}
				<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
		</form>

		@if ($result)
			@if (empty($result['success']))
				<div class="mt-8 card border-l-4 border-l-red-500">
					<h3 class="font-bold mb-2">{{ __('Niet bereikbaar') }}</h3>
					<p class="text-sm">{{ $result['error'] ?? '?' }}</p>
				</div>
			@else
				@php
					$gradeColor = ['A'=>'green','B'=>'green','C'=>'amber','D'=>'red','F'=>'red'][$result['grade']] ?? 'gray';
				@endphp
				<div class="mt-8 card flex items-center gap-6 border-l-4
					@if ($gradeColor === 'green') border-l-green-500
					@elseif ($gradeColor === 'amber') border-l-amber-500
					@else border-l-red-500
					@endif">
					<div class="text-6xl font-black
						@if ($gradeColor === 'green') text-green-600
						@elseif ($gradeColor === 'amber') text-amber-600
						@else text-red-600
						@endif">{{ $result['grade'] }}</div>
					<div>
						<div class="text-xs text-[color:var(--color-ink-muted)] uppercase tracking-wider mb-1">{{ __('Security-headers score') }}</div>
						<div class="text-2xl font-bold">{{ $result['score'] }} / {{ $result['max_score'] }} <span class="text-base text-[color:var(--color-ink-muted)] font-medium">({{ $result['pct'] }}%)</span></div>
						<div class="text-sm text-[color:var(--color-ink-muted)] mt-1 font-mono break-all">{{ $result['final_url'] }} · HTTP {{ $result['status'] }}</div>
					</div>
				</div>

				<div class="mt-6 space-y-3">
					@foreach ($result['checks'] as $c)
						<div class="card border-l-4 {{ $c['present'] ? 'border-l-green-500' : 'border-l-red-500' }}">
							<div class="flex items-baseline justify-between mb-2">
								<h3 class="font-bold">{{ $c['label'] }}</h3>
								<span class="text-xs font-semibold {{ $c['present'] ? 'text-green-700' : 'text-red-700' }}">
									{{ $c['present'] ? '✓ aanwezig' : '✕ ontbreekt' }} · {{ $c['weight'] }} pt
								</span>
							</div>
							@if ($c['present'])
								<pre class="text-xs font-mono bg-[color:var(--color-surface)] p-2 rounded border border-[color:var(--color-line)] overflow-x-auto mb-2 whitespace-pre-wrap break-all">{{ $c['value'] }}</pre>
							@endif
							<p class="text-sm text-[color:var(--color-ink-muted)]">{{ $c['why'] }}</p>
						</div>
					@endforeach
				</div>

				@if ($result['server'] || $result['x_powered_by'])
					<div class="mt-6 card bg-[color:var(--color-surface)] text-xs">
						<div class="font-semibold mb-2">{{ __('Info-only (geen score)') }}</div>
						@if ($result['server'])      <div>Server: <code>{{ $result['server'] }}</code></div> @endif
						@if ($result['x_powered_by']) <div>X-Powered-By: <code>{{ $result['x_powered_by'] }}</code> — overweeg verbergen.</div> @endif
					</div>
				@endif
			@endif

			<div class="mt-10 card bg-[color:var(--color-surface)]">
				<h3 class="font-bold mb-2">{{ __('Lage score?') }}</h3>
				<p class="text-sm text-[color:var(--color-ink-muted)] mb-4">
					{{ __('Wij configureren de ontbrekende headers en zorgen voor een werkbare CSP zonder dat je site breekt.') }}
				</p>
				<a href="/{{ app()->getLocale() }}/diensten/website-beveiligen" class="btn-accent">
					{{ __('Website beveiligen') }}
					<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</a>
			</div>
		@endif
	</div>
</section>

@endsection
