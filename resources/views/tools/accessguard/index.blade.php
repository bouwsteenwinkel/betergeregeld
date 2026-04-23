@extends('layouts.app')

@section('title', 'AccessGuard — ' . config('app.name'))

@section('content')

@include('tools.accessguard._header')
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[1400px] mx-auto px-6 space-y-6">
		@if (session('status'))
			<div class="card text-sm bg-emerald-50 border-emerald-200 text-emerald-900">{{ session('status') }}</div>
		@endif

		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
			<div class="card">
				<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Personen') }}</div>
				<div class="text-3xl font-bold tabular-nums">{{ $activePeopleCount }}</div>
				<div class="text-xs text-[color:var(--color-ink-soft)] mt-1">{{ __(':n totaal (incl. inactief)', ['n' => $peopleCount]) }}</div>
			</div>
			<div class="card">
				<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Systemen') }}</div>
				<div class="text-3xl font-bold tabular-nums">{{ $activeSystemsCount }}</div>
				<div class="text-xs text-[color:var(--color-ink-soft)] mt-1">{{ __(':n totaal', ['n' => $systemsCount]) }}</div>
			</div>
			<div class="card">
				<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Ingevuld') }}</div>
				<div class="text-3xl font-bold tabular-nums">
					{{ $recordedCells }}<span class="text-base text-[color:var(--color-ink-soft)]">/{{ $totalCells }}</span>
				</div>
				<div class="text-xs text-[color:var(--color-ink-soft)] mt-1">{{ __('cellen') }}</div>
			</div>
			<div class="card {{ $needsReview > 0 ? 'border-amber-300' : '' }}">
				<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Heroverwegen') }}</div>
				<div class="text-3xl font-bold tabular-nums {{ $needsReview > 0 ? 'text-amber-700' : '' }}">{{ $needsReview }}</div>
				<div class="text-xs text-[color:var(--color-ink-soft)] mt-1">{{ __('cellen op needs_review') }}</div>
			</div>
		</div>

		<div class="card">
			<h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Verdeling') }}</h2>
			@php
				$segments = [
					['label' => __('Toegang'), 'count' => $hasAccess, 'color' => 'bg-emerald-500'],
					['label' => __('Geen toegang'), 'count' => $noAccess, 'color' => 'bg-slate-400'],
					['label' => __('Heroverwegen'), 'count' => $needsReview, 'color' => 'bg-amber-400'],
					['label' => __('Onbekend'), 'count' => $unknown, 'color' => 'bg-slate-200'],
				];
				$grandTotal = max(1, $recordedCells);
			@endphp
			<div class="flex h-3 rounded overflow-hidden mb-3">
				@foreach ($segments as $s)
					@if ($s['count'] > 0)
						<div class="{{ $s['color'] }}" style="width:{{ ($s['count'] / $grandTotal) * 100 }}%" title="{{ $s['label'] }}: {{ $s['count'] }}"></div>
					@endif
				@endforeach
			</div>
			<div class="flex flex-wrap gap-4 text-sm">
				@foreach ($segments as $s)
					<div class="flex items-center gap-2">
						<span class="w-3 h-3 rounded {{ $s['color'] }}"></span>
						<span>{{ $s['label'] }}</span>
						<span class="font-semibold tabular-nums text-[color:var(--color-ink-muted)]">{{ $s['count'] }}</span>
					</div>
				@endforeach
			</div>
		</div>

		<div class="card">
			<h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Aan de slag') }}</h2>
			<ol class="space-y-2 text-sm list-decimal list-inside">
				<li>
					<a href="{{ route('tools.accessguard.people.index', ['locale' => app()->getLocale()]) }}" class="text-[color:var(--color-accent)] font-semibold hover:underline">{{ __('Voeg personen toe') }}</a>
					— {{ __('medewerkers, contractors en externe partijen waarvoor je toegang wilt bijhouden.') }}
				</li>
				<li>
					<a href="{{ route('tools.accessguard.systems.index', ['locale' => app()->getLocale()]) }}" class="text-[color:var(--color-accent)] font-semibold hover:underline">{{ __('Voeg systemen toe') }}</a>
					— {{ __('apps en services waar zij toegang tot kunnen hebben.') }}
				</li>
				<li>
					<a href="{{ route('tools.accessguard.matrix', ['locale' => app()->getLocale()]) }}" class="text-[color:var(--color-accent)] font-semibold hover:underline">{{ __('Open de Access Matrix') }}</a>
					— {{ __('klik op cellen om de toegang-status te zetten.') }}
				</li>
			</ol>
		</div>
	</div>
</section>

@endsection
