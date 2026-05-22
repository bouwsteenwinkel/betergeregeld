@extends('layouts.app')

@section('title', 'Radar Assets — ' . config('app.name'))

@php $crumb = __('Assets'); $locale = app()->getLocale(); @endphp

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

		<div class="flex items-center justify-between">
			<h2 class="text-xl font-bold">{{ __('Assets') }} <span class="text-sm text-[color:var(--color-ink-muted)] font-normal">({{ count($assets) }})</span></h2>
			<a href="{{ route('tools.radar.assets.create', ['locale' => $locale]) }}"
				class="px-3 py-1.5 rounded bg-[color:var(--color-accent)] text-white text-sm font-semibold hover:opacity-90">
				+ {{ __('Asset toevoegen') }}
			</a>
		</div>

		@if ($assets->isEmpty())
			<div class="card text-center py-10 text-sm text-[color:var(--color-ink-muted)]">
				{{ __('Nog geen assets. Voeg er een toe om te beginnen met scannen.') }}
			</div>
		@else
			<div class="card p-0 overflow-hidden">
				<table class="w-full text-sm">
					<thead class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] bg-[color:var(--color-surface-soft)]">
						<tr>
							<th class="text-left px-4 py-3">{{ __('Naam') }}</th>
							<th class="text-left px-4 py-3">{{ __('URL') }}</th>
							<th class="text-left px-4 py-3">{{ __('Criticality') }}</th>
							<th class="text-right px-4 py-3">{{ __('Open issues') }}</th>
							<th class="text-left px-4 py-3">{{ __('Laatst gescand') }}</th>
							<th class="text-right px-4 py-3">{{ __('Acties') }}</th>
						</tr>
					</thead>
					<tbody class="divide-y divide-[color:var(--color-line)]">
						@foreach ($assets as $a)
							<tr class="hover:bg-[color:var(--color-surface-soft)]">
								<td class="px-4 py-3">
									<a href="{{ route('tools.radar.assets.show', ['locale' => $locale, 'id' => $a->id]) }}" class="font-semibold hover:text-[color:var(--color-accent)]">{{ $a->name }}</a>
								</td>
								<td class="px-4 py-3 text-[color:var(--color-ink-muted)] truncate max-w-[280px]"><a href="{{ $a->url }}" target="_blank" rel="noopener" class="hover:underline">{{ $a->url }}</a></td>
								<td class="px-4 py-3">
									<span @class([
										'text-xs px-2 py-0.5 rounded',
										'bg-red-100 text-red-700' => $a->criticality === 'critical',
										'bg-orange-100 text-orange-700' => $a->criticality === 'high',
										'bg-amber-100 text-amber-700' => $a->criticality === 'medium',
										'bg-slate-100 text-slate-700' => $a->criticality === 'low',
									])>{{ $a->criticality }}</span>
								</td>
								<td class="px-4 py-3 text-right tabular-nums font-semibold {{ $a->open_findings_count > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ $a->open_findings_count }}</td>
								<td class="px-4 py-3 text-xs text-[color:var(--color-ink-muted)]">{{ $a->last_scanned_at?->diffForHumans() ?? __('nog niet') }}</td>
								<td class="px-4 py-3 text-right">
									<div class="inline-flex gap-1">
										<form method="POST" action="{{ route('tools.radar.assets.scan', ['locale' => $locale, 'id' => $a->id]) }}">
											@csrf
											<button type="submit" class="text-xs px-2 py-1 rounded border border-[color:var(--color-line)] hover:bg-white">{{ __('Scan nu') }}</button>
										</form>
										<a href="{{ route('tools.radar.assets.show', ['locale' => $locale, 'id' => $a->id]) }}" class="text-xs px-2 py-1 rounded border border-[color:var(--color-line)] hover:bg-white">{{ __('Bekijk') }}</a>
										<form method="POST" action="{{ route('tools.radar.assets.destroy', ['locale' => $locale, 'id' => $a->id]) }}" onsubmit="return confirm('{{ __('Asset verwijderen?') }}');">
											@csrf
											@method('DELETE')
											<button type="submit" class="text-xs px-2 py-1 rounded border border-red-200 text-red-700 hover:bg-red-50">{{ __('Verwijder') }}</button>
										</form>
									</div>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		@endif
	</div>
</section>
@endsection
