@extends('layouts.app')

@section('title', __('CSV-import voorbeeld') . ' — ' . config('app.name'))

@php
	$locale = app()->getLocale();
	$fmt = fn ($v) => '€' . number_format(abs((float) $v), 2, ',', '.');
@endphp

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1200px] mx-auto px-6 py-12">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-4 flex items-center gap-2">
			<a href="{{ route('tools.bookkeeping.import.show', ['locale' => $locale]) }}" class="hover:text-white">{{ __('CSV-import') }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">{{ __('Voorbeeld') }}</span>
		</nav>
		<h1 class="display-1">{{ __('Voorbeeld') }}: {{ $state['filename'] }}</h1>
		<div class="mt-3 text-sm text-[color:var(--color-on-dark-muted)] flex gap-5 flex-wrap">
			<span>{{ $stats['total'] }} {{ __('rijen') }}</span>
			<span>{{ $stats['matched'] }} {{ __('match(es) gevonden') }}</span>
			<span class="text-emerald-300">+{{ $stats['income'] }}</span>
			<span class="text-red-300">−{{ $stats['expense'] }}</span>
			<span class="opacity-60">{{ __('Herkend:') }} {{ implode(', ', $state['columns_used']) }}</span>
		</div>
	</div>
</section>

<section class="py-6">
	<div class="max-w-[1200px] mx-auto px-6 space-y-4">
		<form method="POST" action="{{ route('tools.bookkeeping.import.commit', ['locale' => $locale, 'key' => $key]) }}">
			@csrf

			<div class="flex items-center justify-between mb-3 gap-3 flex-wrap">
				<div class="text-sm text-[color:var(--color-ink-muted)]">
					{{ __('Standaard is elke rij ingesteld op aanmaken, behalve rijen waar we een match vonden (die staan op linken). Pas per rij aan indien nodig.') }}
				</div>
				<div class="flex gap-2 text-xs">
					<button type="button" onclick="setAll('create')" class="btn-dark">{{ __('Alles aanmaken') }}</button>
					<button type="button" onclick="setAll('link')" class="btn-dark">{{ __('Alles linken (waar match)') }}</button>
					<button type="button" onclick="setAll('skip')" class="btn-dark">{{ __('Alles overslaan') }}</button>
				</div>
			</div>

			<div class="card overflow-x-auto">
				<table class="w-full text-sm">
					<thead>
						<tr class="border-b-2 border-[color:var(--color-line)]">
							<th class="text-left py-2 pr-3 font-semibold">{{ __('Datum') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Bedrag') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Omschrijving / tegenpartij') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Voorgestelde match') }}</th>
							<th class="text-left py-2 pl-3 font-semibold">{{ __('Actie') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($state['rows'] as $idx => $row)
							@php
								$match = $row['match_tx_id'] ? ($matches[$row['match_tx_id']] ?? null) : null;
								$defaultAction = $match ? 'link' : ($row['date'] && $row['amount'] !== null ? 'create' : 'skip');
							@endphp
							<tr class="border-b border-[color:var(--color-line)]/60 {{ $match ? 'bg-emerald-50/50' : '' }}">
								<td class="py-2 pr-3 tabular-nums text-[color:var(--color-ink-muted)] whitespace-nowrap">{{ $row['date'] ?? '—' }}</td>
								<td class="py-2 px-3 text-right tabular-nums font-medium {{ $row['amount'] !== null && $row['amount'] < 0 ? 'text-red-700' : ($row['amount'] !== null ? 'text-emerald-700' : '') }}">
									{{ $row['amount'] !== null ? ($row['amount'] < 0 ? '−' : '+') . $fmt($row['amount']) : '—' }}
								</td>
								<td class="py-2 px-3">
									<div class="font-medium">{{ $row['counterparty'] ?: $row['description'] ?: '—' }}</div>
									@if ($row['counterparty'] && $row['description'])
										<div class="text-xs text-[color:var(--color-ink-soft)] truncate max-w-[24rem]">{{ $row['description'] }}</div>
									@endif
								</td>
								<td class="py-2 px-3">
									@if ($match)
										<div class="text-xs">
											<div class="text-emerald-700 font-medium">
												{{ __('Match') }} ({{ $row['match_score'] }}/100)
											</div>
											<div class="text-[color:var(--color-ink-muted)] truncate max-w-[22rem]">
												{{ $match->description }}
												@if ($match->counterparty) · {{ $match->counterparty }} @endif
											</div>
											<div class="text-[color:var(--color-ink-soft)] tabular-nums">{{ $match->transaction_date->format('d-m-Y') }} · {{ $fmt($match->amount) }}</div>
										</div>
									@else
										<span class="text-xs text-[color:var(--color-ink-soft)]">{{ __('geen match') }}</span>
									@endif
								</td>
								<td class="py-2 pl-3 whitespace-nowrap">
									<select name="action[{{ $idx }}]" class="field-input py-1 text-xs" data-default="{{ $defaultAction }}">
										<option value="create" @selected($defaultAction === 'create')>{{ __('Aanmaken') }}</option>
										<option value="link" @selected($defaultAction === 'link') {{ ! $match ? 'disabled' : '' }}>{{ __('Linken') }}</option>
										<option value="skip" @selected($defaultAction === 'skip')>{{ __('Overslaan') }}</option>
									</select>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			<div class="flex gap-2 items-center flex-wrap">
				<button type="submit" class="btn-accent">{{ __('Doorvoeren') }}</button>
				<a href="{{ route('tools.bookkeeping.import.show', ['locale' => $locale]) }}" class="btn-dark">{{ __('Annuleer') }}</a>
			</div>
		</form>
	</div>
</section>

@push('scripts')
<script>
function setAll(val) {
	document.querySelectorAll('select[name^="action["]').forEach(function (sel) {
		if (val === 'link') {
			const has = Array.from(sel.options).some(o => o.value === 'link' && !o.disabled);
			sel.value = has ? 'link' : sel.dataset.default;
		} else {
			sel.value = val;
		}
	});
}
</script>
@endpush

@endsection
