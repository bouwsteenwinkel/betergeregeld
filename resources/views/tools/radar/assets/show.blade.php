@extends('layouts.app')

@section('title', $asset->name . ', Radar, ' . config('app.name'))

@php $crumb = $asset->name; $locale = app()->getLocale(); @endphp

@php
	$statusGroups = [
		'new'           => __('Nieuw'),
		'confirmed'     => __('Bevestigd'),
		'reopened'      => __('Heropend'),
		'in_progress'   => __('In behandeling'),
		'planned'       => __('Gepland'),
		'accepted_risk' => __('Risico geaccepteerd'),
		'patched'       => __('Gepatcht'),
		'mitigated'     => __('Gemitigeerd'),
		'ignored'       => __('Genegeerd'),
		'false_positive'=> __('False positive'),
		'resolved'      => __('Opgelost'),
	];
@endphp

@section('content')
@include('tools.radar._header')
@include('tools.radar._subnav')

<section class="py-6">
	<div class="max-w-[1400px] mx-auto px-6 space-y-4">
		@if (session('status'))
			<div class="card text-sm bg-emerald-50 border-emerald-200 text-emerald-900">{{ session('status') }}</div>
		@endif
		@if ($errors->any())
			<div class="card text-sm bg-red-50 border-red-200 text-red-900">
				@foreach ($errors->all() as $err)<div>{{ $err }}</div>@endforeach
			</div>
		@endif

		<div class="card">
			<div class="flex flex-wrap items-start justify-between gap-4">
				<div>
					<h2 class="text-xl font-bold">{{ $asset->name }}</h2>
					<a href="{{ $asset->url }}" target="_blank" rel="noopener" class="text-sm text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-accent)] hover:underline">{{ $asset->url }} ↗</a>
					<div class="text-xs text-[color:var(--color-ink-muted)] mt-2">
						{{ __('Criticality') }}: <span class="font-semibold">{{ $asset->criticality }}</span>
						<span class="mx-2">·</span>
						{{ __('Laatst gescand') }}: <span class="font-semibold">{{ $asset->last_scanned_at?->diffForHumans() ?? __('nog niet') }}</span>
					</div>
				</div>
				<div class="flex gap-2">
					<form method="POST" action="{{ route('tools.radar.assets.scan', ['locale' => $locale, 'id' => $asset->id]) }}">
						@csrf
						<button type="submit" class="px-3 py-1.5 rounded bg-[color:var(--color-accent)] text-white text-sm font-semibold hover:opacity-90">{{ __('Scan nu') }}</button>
					</form>
					<a href="{{ route('tools.radar.assets.index', ['locale' => $locale]) }}" class="px-3 py-1.5 rounded border border-[color:var(--color-line)] text-sm hover:bg-[color:var(--color-surface-soft)]">← {{ __('Terug') }}</a>
				</div>
			</div>
		</div>

		<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
			{{-- Findings (2/3) --}}
			<div class="lg:col-span-2 space-y-4">
				<h3 class="font-bold text-lg">{{ __('Issues') }}</h3>

				@php $hasAny = false; @endphp
				@foreach (['new','confirmed','reopened','in_progress','planned'] as $st)
					@if (isset($findings[$st]) && $findings[$st]->isNotEmpty())
						@php $hasAny = true; @endphp
						<div class="card">
							<h4 class="font-semibold mb-3">{{ $statusGroups[$st] ?? $st }} <span class="text-sm text-[color:var(--color-ink-muted)] font-normal">({{ $findings[$st]->count() }})</span></h4>
							<div class="space-y-3">
								@foreach ($findings[$st] as $f)
									@include('tools.radar.assets._finding', ['f' => $f])
								@endforeach
							</div>
						</div>
					@endif
				@endforeach

				@if (! $hasAny)
					<div class="card text-center py-10 text-sm text-[color:var(--color-ink-muted)]">
						{{ __('Geen openstaande issues op dit asset.') }}
					</div>
				@endif

				{{-- Opgelost / genegeerd, ingeklapt --}}
				@foreach (['accepted_risk', 'resolved', 'ignored', 'false_positive', 'patched', 'mitigated'] as $st)
					@if (isset($findings[$st]) && $findings[$st]->isNotEmpty())
						<details class="card">
							<summary class="cursor-pointer text-sm font-semibold text-[color:var(--color-ink-muted)]">{{ $statusGroups[$st] ?? $st }} ({{ $findings[$st]->count() }})</summary>
							<div class="space-y-3 mt-3">
								@foreach ($findings[$st] as $f)
									@include('tools.radar.assets._finding', ['f' => $f])
								@endforeach
							</div>
						</details>
					@endif
				@endforeach
			</div>

			{{-- Scan-historie (1/3) --}}
			<div class="space-y-4">
				<h3 class="font-bold text-lg">{{ __('Scan-historie') }}</h3>
				<div class="card">
					@if ($scans->isEmpty())
						<div class="text-sm text-[color:var(--color-ink-muted)]">{{ __('Nog geen scans.') }}</div>
					@else
						<div class="space-y-2">
							@foreach ($scans as $s)
								<div class="flex items-center gap-2 text-sm py-1 border-b border-[color:var(--color-line)] last:border-0">
									<span @class([
										'inline-block w-2 h-2 rounded-full shrink-0',
										'bg-emerald-500' => $s->status === 'success',
										'bg-red-500' => $s->status === 'error',
										'bg-slate-300' => ! in_array($s->status, ['success', 'error']),
									])></span>
									<span class="text-xs text-[color:var(--color-ink-muted)] tabular-nums">{{ $s->started_at?->format('d/m H:i') }}</span>
									<span class="flex-1 text-xs text-[color:var(--color-ink-muted)] truncate">{{ $s->raw_output['host'] ?? ($s->raw_output['cmps'] ?? null ? 'cmp' : ($s->raw_output['url'] ?? '') ? 'cookies/headers' : $s->scan_type) }}</span>
									<span class="text-xs tabular-nums font-semibold {{ $s->findings_count > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ $s->findings_count ?? 0 }}</span>
								</div>
							@endforeach
						</div>
					@endif
				</div>
			</div>
		</div>
	</div>
</section>
@endsection
