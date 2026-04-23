@extends('layouts.app')

@section('title', $cycle->title . ' — AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = $cycle->title;
	$statusColors = [
		'planned' => 'bg-slate-100 text-slate-700',
		'active' => 'bg-emerald-100 text-emerald-800',
		'completed' => 'bg-blue-100 text-blue-800',
		'cancelled' => 'bg-slate-200 text-slate-500',
	];
	$decisionColors = [
		'keep' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
		'revoke' => 'bg-red-100 text-red-800 border-red-300',
		'change' => 'bg-amber-100 text-amber-800 border-amber-300',
	];
	$stateColors = [
		'unknown' => 'bg-slate-100 text-slate-500',
		'has_access' => 'bg-emerald-100 text-emerald-800',
		'no_access' => 'bg-slate-300 text-slate-700',
		'needs_review' => 'bg-amber-100 text-amber-800',
	];
@endphp

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[1400px] mx-auto px-6 space-y-4">
		@if (session('status'))
			<div class="card text-sm bg-emerald-50 border-emerald-200 text-emerald-900">{{ session('status') }}</div>
		@endif

		<div class="card">
			<div class="flex items-start gap-4 flex-wrap">
				<div class="flex-1 min-w-[300px]">
					<div class="flex items-center gap-3 mb-1">
						<h2 class="text-xl font-bold">{{ $cycle->title }}</h2>
						<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $statusColors[$cycle->status] }}">
							{{ __(ucfirst($cycle->status)) }}
						</span>
					</div>
					<div class="text-xs text-[color:var(--color-ink-muted)]">
						{{ __('Scope') }}: <strong>{{ $cycle->scope === 'all' ? __('iedereen') : __('actieve personen') }}</strong>
						@if ($cycle->starts_at) · {{ __('gestart') }} {{ $cycle->starts_at->format('d-m-Y') }} @endif
						@if ($cycle->due_at) · {{ __('deadline') }} {{ $cycle->due_at->format('d-m-Y') }} @endif
						@if ($cycle->completed_at) · {{ __('afgerond') }} {{ $cycle->completed_at->format('d-m-Y H:i') }} @endif
					</div>
					@if ($cycle->notes)
						<p class="text-sm text-[color:var(--color-ink-soft)] mt-2">{{ $cycle->notes }}</p>
					@endif
				</div>

				@if ($cycle->isOpen())
					<div class="flex gap-2">
						<form method="POST" action="{{ route('tools.accessguard.reviews.cancel', ['locale' => $locale, 'id' => $cycle->id]) }}" onsubmit="return confirm('{{ __('Cyclus annuleren? Beslissingen blijven staan, maar er worden geen acties aangemaakt.') }}');">
							@csrf
							<button type="submit" class="text-sm py-2 px-3 rounded border border-slate-300 text-slate-600 hover:bg-slate-50">{{ __('Annuleren') }}</button>
						</form>
					</div>
				@endif
			</div>

			<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mt-4 text-sm">
				<div><div class="text-xs text-[color:var(--color-ink-muted)] uppercase tracking-wider font-bold">{{ __('Totaal') }}</div><div class="text-xl font-bold tabular-nums">{{ $counts['total'] }}</div></div>
				<div><div class="text-xs text-emerald-700 uppercase tracking-wider font-bold">{{ __('Behouden') }}</div><div class="text-xl font-bold tabular-nums text-emerald-700">{{ $counts['keep'] }}</div></div>
				<div><div class="text-xs text-red-700 uppercase tracking-wider font-bold">{{ __('Intrekken') }}</div><div class="text-xl font-bold tabular-nums text-red-700">{{ $counts['revoke'] }}</div></div>
				<div><div class="text-xs text-amber-700 uppercase tracking-wider font-bold">{{ __('Wijzigen') }}</div><div class="text-xl font-bold tabular-nums text-amber-700">{{ $counts['change'] }}</div></div>
				<div><div class="text-xs text-[color:var(--color-ink-muted)] uppercase tracking-wider font-bold">{{ __('Open') }}</div><div class="text-xl font-bold tabular-nums">{{ $counts['undecided'] }}</div></div>
			</div>
		</div>

		@if ($cycle->isOpen())
			<div class="card">
				<form method="POST" action="{{ route('tools.accessguard.reviews.complete', ['locale' => $locale, 'id' => $cycle->id]) }}" class="flex items-center gap-3 flex-wrap" onsubmit="return confirm('{{ __('Cyclus afronden? Dit materializeert acties voor intrekkingen en wijzigingen.') }}');">
					@csrf
					<strong class="text-sm">{{ __('Cyclus afronden') }}</strong>
					<span class="text-xs text-[color:var(--color-ink-muted)]">{{ __('Niet-besliste items defaulten naar:') }}</span>
					<select name="undecided_default" class="field-input py-1 text-sm" style="width:auto">
						<option value="keep">{{ __('Behouden') }}</option>
						<option value="revoke">{{ __('Intrekken') }}</option>
						<option value="change">{{ __('Wijzigen') }}</option>
					</select>
					<button type="submit" class="btn-accent text-sm">{{ __('Afronden') }}</button>
					@if ($counts['undecided'] > 0)
						<span class="text-xs text-amber-700">({{ $counts['undecided'] }} {{ __('nog open') }})</span>
					@endif
				</form>
			</div>
		@endif

		<div class="card p-0 overflow-hidden">
			@if ($items->isEmpty())
				<p class="text-sm text-[color:var(--color-ink-muted)] p-6 text-center">{{ __('Geen items in deze cyclus.') }}</p>
			@else
				<form method="POST" action="{{ route('tools.accessguard.reviews.bulk-decide', ['locale' => $locale, 'id' => $cycle->id]) }}">
					@csrf
					@if ($cycle->isOpen())
						<div class="flex items-center gap-2 text-sm p-3 border-b border-[color:var(--color-line)] bg-slate-50 flex-wrap">
							<strong class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ __('Bulk-beslissing voor geselecteerde items:') }}</strong>
							<button type="submit" name="decision" value="keep" class="text-xs px-3 py-1 rounded border border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100">{{ __('Behouden') }}</button>
							<button type="submit" name="decision" value="revoke" class="text-xs px-3 py-1 rounded border border-red-300 bg-red-50 text-red-800 hover:bg-red-100">{{ __('Intrekken') }}</button>
							<button type="submit" name="decision" value="change" class="text-xs px-3 py-1 rounded border border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100">{{ __('Wijzigen') }}</button>
						</div>
					@endif
					<table class="w-full text-sm">
						<thead class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] border-b border-[color:var(--color-line)]">
							<tr>
								@if ($cycle->isOpen())
									<th class="p-2 w-8"><input type="checkbox" onclick="document.querySelectorAll('.cycle-item-cb').forEach(cb=>cb.checked=this.checked)"></th>
								@endif
								<th class="text-left py-2 px-3 font-semibold">{{ __('Persoon') }}</th>
								<th class="text-left py-2 px-3 font-semibold">{{ __('Systeem') }}</th>
								<th class="text-left py-2 px-3 font-semibold">{{ __('Snapshot') }}</th>
								<th class="text-left py-2 px-3 font-semibold">{{ __('Beslissing') }}</th>
								@if ($cycle->isOpen())
									<th class="text-right py-2 px-3 font-semibold">{{ __('Acties') }}</th>
								@endif
							</tr>
						</thead>
						<tbody>
							@foreach ($items as $item)
								<tr class="border-b border-[color:var(--color-line)]/60 hover:bg-[color:var(--color-surface-soft,#fafafa)]">
									@if ($cycle->isOpen())
										<td class="p-2 text-center"><input type="checkbox" name="item_ids[]" value="{{ $item->id }}" class="cycle-item-cb"></td>
									@endif
									<td class="py-2 px-3 font-semibold">{{ $item->person_label }}</td>
									<td class="py-2 px-3">{{ $item->system_label }}</td>
									<td class="py-2 px-3">
										<span class="inline-flex items-center px-2 py-0.5 rounded text-xs {{ $stateColors[$item->snapshot_state] ?? '' }}">
											{{ str_replace('_', ' ', $item->snapshot_state) }}
										</span>
										@if ($item->snapshot_level)<span class="text-xs text-[color:var(--color-ink-muted)] ml-1">{{ $item->snapshot_level }}</span>@endif
									</td>
									<td class="py-2 px-3">
										@if ($item->decision)
											<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border {{ $decisionColors[$item->decision] ?? '' }}">
												{{ __(['keep' => 'Behouden', 'revoke' => 'Intrekken', 'change' => 'Wijzigen'][$item->decision] ?? $item->decision) }}
											</span>
											@if ($item->decision_note)
												<div class="text-xs text-[color:var(--color-ink-muted)] mt-0.5">{{ $item->decision_note }}</div>
											@endif
										@else
											<span class="text-xs text-[color:var(--color-ink-muted)]">—</span>
										@endif
									</td>
									@if ($cycle->isOpen())
										<td class="py-2 px-3 text-right whitespace-nowrap">
											<form method="POST" action="{{ route('tools.accessguard.reviews.decide', ['locale' => $locale, 'id' => $cycle->id, 'itemId' => $item->id]) }}" class="inline">
												@csrf
												<button type="submit" name="decision" value="keep" class="text-xs px-2 py-0.5 rounded hover:bg-emerald-50 text-emerald-700" title="{{ __('Behouden') }}">✓</button>
												<button type="submit" name="decision" value="revoke" class="text-xs px-2 py-0.5 rounded hover:bg-red-50 text-red-700" title="{{ __('Intrekken') }}">×</button>
												<button type="submit" name="decision" value="change" class="text-xs px-2 py-0.5 rounded hover:bg-amber-50 text-amber-700" title="{{ __('Wijzigen') }}">?</button>
											</form>
										</td>
									@endif
								</tr>
							@endforeach
						</tbody>
					</table>
				</form>
			@endif
		</div>

		@if ($logs->isNotEmpty())
			<details class="card">
				<summary class="cursor-pointer font-semibold text-sm">{{ __('Logboek') }} ({{ $logs->count() }})</summary>
				<div class="mt-3 text-xs space-y-1">
					@foreach ($logs as $log)
						<div class="flex gap-3 py-1 border-b border-[color:var(--color-line)]/40">
							<span class="text-[color:var(--color-ink-soft)] tabular-nums whitespace-nowrap">{{ $log->created_at->format('d-m H:i') }}</span>
							<span class="font-mono">{{ $log->event }}</span>
							<span class="text-[color:var(--color-ink-muted)]">{{ $log->payload ? json_encode($log->payload) : '' }}</span>
						</div>
					@endforeach
				</div>
			</details>
		@endif
	</div>
</section>

@endsection
