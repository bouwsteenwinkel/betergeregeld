@extends('layouts.app')

@section('title', 'Security Radar, ' . config('app.name'))

@section('content')
@include('tools.radar._header')
@include('tools.radar._subnav')

@php $locale = app()->getLocale(); @endphp

<section class="py-6">
	<div class="max-w-[1400px] mx-auto px-6 space-y-6">
		@if (session('status'))
			<div class="card text-sm bg-emerald-50 border-emerald-200 text-emerald-900">{{ session('status') }}</div>
		@endif
		@if ($errors->any())
			<div class="card text-sm bg-red-50 border-red-200 text-red-900">
				@foreach ($errors->all() as $err)<div>{{ $err }}</div>@endforeach
			</div>
		@endif

		{{-- Plan-overzicht --}}
		<div class="card flex flex-wrap items-center justify-between gap-3">
			<div class="text-sm">
				<span class="text-[color:var(--color-ink-muted)]">{{ __('Je plan') }}:</span>
				<span class="font-semibold ml-1">{{ $planLimits['plan_name'] }}</span>
				<span class="text-[color:var(--color-ink-muted)] mx-2">·</span>
				<span class="text-[color:var(--color-ink-muted)]">{{ __('Max assets') }}:</span>
				<span class="font-semibold ml-1">{{ $planLimits['max_assets'] }}</span>
				<span class="text-[color:var(--color-ink-muted)] mx-2">·</span>
				<span class="text-[color:var(--color-ink-muted)]">{{ __('Scans/dag') }}:</span>
				<span class="font-semibold ml-1">{{ $planLimits['scans_per_day'] ?: '∞' }}</span>
				<span class="text-[color:var(--color-ink-muted)] mx-2">·</span>
				<span class="text-[color:var(--color-ink-muted)]">{{ __('Checks') }}:</span>
				<span class="font-semibold ml-1">{{ implode(', ', $planLimits['checks_allowed']) ?: ', ' }}</span>
			</div>
			<a href="{{ route('pricing', ['locale' => $locale]) }}" class="text-xs px-3 py-1.5 rounded border border-[color:var(--color-line)] hover:bg-[color:var(--color-surface-soft)]">
				{{ __('Plan upgraden') }}
			</a>
		</div>

		{{-- Stats --}}
		<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
			<div class="card">
				<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Assets') }}</div>
				<div class="text-3xl font-bold tabular-nums">{{ $assetsActive }}</div>
				<div class="text-xs text-[color:var(--color-ink-soft)] mt-1">{{ __(':n totaal', ['n' => $assetsTotal]) }}</div>
			</div>
			<div class="card">
				<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Open issues') }}</div>
				<div class="text-3xl font-bold tabular-nums">{{ $findings['total'] }}</div>
			</div>
			<div class="card border-red-300 bg-red-50">
				<div class="text-xs uppercase tracking-wider text-red-700 font-bold mb-1">{{ __('Kritiek') }}</div>
				<div class="text-3xl font-bold tabular-nums text-red-700">{{ $findings['critical'] }}</div>
			</div>
			<div class="card border-orange-300 bg-orange-50">
				<div class="text-xs uppercase tracking-wider text-orange-700 font-bold mb-1">{{ __('Hoog') }}</div>
				<div class="text-3xl font-bold tabular-nums text-orange-700">{{ $findings['high'] }}</div>
			</div>
			<div class="card border-amber-300 bg-amber-50">
				<div class="text-xs uppercase tracking-wider text-amber-700 font-bold mb-1">{{ __('Midden') }}</div>
				<div class="text-3xl font-bold tabular-nums text-amber-700">{{ $findings['medium'] }}</div>
			</div>
			<div class="card border-slate-300 bg-slate-50">
				<div class="text-xs uppercase tracking-wider text-slate-600 font-bold mb-1">{{ __('Laag') }}</div>
				<div class="text-3xl font-bold tabular-nums text-slate-700">{{ $findings['low'] }}</div>
			</div>
		</div>

		{{-- Lege staat --}}
		@if ($assetsTotal === 0)
			<div class="card text-center py-12">
				<h3 class="text-lg font-bold mb-2">{{ __('Nog geen assets toegevoegd') }}</h3>
				<p class="text-sm text-[color:var(--color-ink-muted)] mb-4">{{ __('Voeg je eerste website toe om automatisch te laten scannen op security-issues.') }}</p>
				<a href="{{ route('tools.radar.assets.create', ['locale' => $locale]) }}" class="inline-block px-4 py-2 rounded bg-[color:var(--color-accent)] text-white text-sm font-semibold hover:opacity-90">
					+ {{ __('Eerste asset toevoegen') }}
				</a>
			</div>
		@else
			<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
				{{-- Topfindings --}}
				<div class="card">
					<div class="flex items-center justify-between mb-3">
						<h3 class="font-bold">{{ __('Hoogste risico-issues') }}</h3>
						<a href="{{ route('tools.radar.assets.index', ['locale' => $locale]) }}" class="text-xs text-[color:var(--color-accent)] hover:underline">{{ __('Alle assets') }} →</a>
					</div>
					@if ($recentFindings->isEmpty())
						<div class="text-sm text-[color:var(--color-ink-muted)]">{{ __('Geen open issues. 🎉') }}</div>
					@else
						<div class="space-y-2">
							@foreach ($recentFindings as $f)
								<a href="{{ route('tools.radar.assets.show', ['locale' => $locale, 'id' => $f->asset_id]) }}"
									class="flex items-start gap-3 p-2 rounded hover:bg-[color:var(--color-surface-soft)]">
									<span @class([
										'inline-block w-2 h-2 mt-1.5 rounded-full shrink-0',
										'bg-red-500' => $f->severity === 'critical',
										'bg-orange-500' => $f->severity === 'high',
										'bg-amber-500' => $f->severity === 'medium',
										'bg-slate-400' => $f->severity === 'low',
									])></span>
									<div class="flex-1 min-w-0">
										<div class="text-sm font-semibold truncate">{{ $f->title }}</div>
										<div class="text-xs text-[color:var(--color-ink-muted)] truncate">{{ $f->asset?->name ?? $f->asset?->url }}</div>
									</div>
									<span class="text-xs px-2 py-0.5 rounded border border-[color:var(--color-line)] text-[color:var(--color-ink-muted)] shrink-0 uppercase">{{ $f->check_type }}</span>
								</a>
							@endforeach
						</div>
					@endif
				</div>

				{{-- Recente scans --}}
				<div class="card">
					<h3 class="font-bold mb-3">{{ __('Recente scans') }}</h3>
					@if ($recentScans->isEmpty())
						<div class="text-sm text-[color:var(--color-ink-muted)]">{{ __('Nog geen scans uitgevoerd.') }}</div>
					@else
						<div class="space-y-1.5">
							@foreach ($recentScans as $s)
								<div class="flex items-center gap-3 text-sm py-1">
									<span @class([
										'inline-block w-2 h-2 rounded-full shrink-0',
										'bg-emerald-500' => $s->status === 'success',
										'bg-red-500' => $s->status === 'error',
										'bg-slate-300' => ! in_array($s->status, ['success', 'error']),
									])></span>
									<span class="text-xs text-[color:var(--color-ink-muted)] w-20 shrink-0">{{ $s->started_at?->format('d/m H:i') }}</span>
									<a href="{{ route('tools.radar.assets.show', ['locale' => $locale, 'id' => $s->asset_id]) }}" class="flex-1 min-w-0 truncate hover:text-[color:var(--color-accent)]">
										{{ $s->asset?->name ?? $s->asset?->url }}
									</a>
									<span class="text-xs text-[color:var(--color-ink-muted)] shrink-0 tabular-nums">{{ $s->findings_count ?? 0 }} {{ __('issues') }}</span>
								</div>
							@endforeach
						</div>
					@endif
				</div>
			</div>
		@endif
	</div>
</section>
@endsection
