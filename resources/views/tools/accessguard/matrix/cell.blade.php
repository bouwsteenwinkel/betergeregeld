@extends('layouts.app')

@section('title', $person->full_name . ' × ' . $system->name . ' — AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = $person->full_name . ' / ' . $system->name;
	$stateColors = [
		'unknown' => ['bg' => 'bg-slate-100', 'border' => 'border-slate-200', 'text' => 'text-slate-500', 'label' => '—', 'name' => __('Onbekend')],
		'has_access' => ['bg' => 'bg-emerald-100', 'border' => 'border-emerald-300', 'text' => 'text-emerald-800', 'label' => '✓', 'name' => __('Toegang')],
		'no_access' => ['bg' => 'bg-slate-300', 'border' => 'border-slate-400', 'text' => 'text-slate-700', 'label' => '×', 'name' => __('Geen toegang')],
		'needs_review' => ['bg' => 'bg-amber-100', 'border' => 'border-amber-300', 'text' => 'text-amber-800', 'label' => '?', 'name' => __('Heroverwegen')],
	];
	$typeLabels = [
		'role' => __('Rol'), 'licence' => __('Licentie'), 'account' => __('Account'),
		'key' => __('Sleutel'), 'badge' => __('Pas'), 'group' => __('Groep'), 'other' => __('Overig'),
	];
@endphp

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[900px] mx-auto px-6 space-y-4">
		<div class="card flex items-start justify-between flex-wrap gap-3">
			<div>
				<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold">{{ __('Persoon × Systeem') }}</div>
				<h2 class="text-lg font-bold">{{ $person->full_name }} <span class="text-[color:var(--color-ink-muted)] font-normal">→</span> {{ $system->name }}</h2>
				<div class="text-xs text-[color:var(--color-ink-muted)] mt-1">
					{{ __('Cel-status wordt afgeleid van items:') }}
					<span class="inline-flex items-center gap-2 px-2 py-0.5 rounded border {{ $stateColors[$cellState]['bg'] }} {{ $stateColors[$cellState]['border'] }} {{ $stateColors[$cellState]['text'] }} font-semibold ml-1" id="cell-state-badge">
						{{ $stateColors[$cellState]['label'] }} {{ $stateColors[$cellState]['name'] }}
					</span>
				</div>
			</div>
			<a href="{{ route('tools.accessguard.matrix', ['locale' => $locale]) }}" class="text-sm text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">← {{ __('Terug naar matrix') }}</a>
		</div>

		@if ($items->isEmpty())
			<div class="card text-sm">
				<p>{{ __('Dit systeem heeft geen actieve items.') }}</p>
				<p class="text-xs text-[color:var(--color-ink-muted)] mt-2">
					{{ __('Bij systemen zonder items beslis je direct op cel-niveau in de matrix.') }}
					<a href="{{ route('tools.accessguard.systems.items.index', ['locale' => $locale, 'systemId' => $system->id]) }}" class="text-[color:var(--color-accent)] font-semibold hover:underline">{{ __('Items beheren →') }}</a>
				</p>
			</div>
		@else
			<div class="card">
				<h3 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Items') }}</h3>
				<p class="text-xs text-[color:var(--color-ink-muted)] mb-3">{{ __('Klik op een item om door de statussen te rouleren. De cel-status boven wordt live bijgewerkt.') }}</p>

				<div class="space-y-2" id="items-list">
					@foreach ($items as $item)
						@php
							$state = $itemStates[$item->id]['state'] ?? 'unknown';
							$verified = $itemStates[$item->id]['verified_at'] ?? null;
							$c = $stateColors[$state];
						@endphp
						<div class="flex items-center gap-3 p-2 rounded border border-[color:var(--color-line)] hover:bg-[color:var(--color-surface-soft,#fafafa)]">
							<button type="button"
								class="item-btn w-10 h-10 rounded border font-bold text-base {{ $c['bg'] }} {{ $c['border'] }} {{ $c['text'] }} hover:scale-110 transition"
								data-item="{{ $item->id }}"
								data-state="{{ $state }}"
								@if ($verified) title="{{ __('Laatst geverifieerd') }}: {{ $verified }}" @endif>
								{{ $c['label'] }}
							</button>
							<div class="flex-1 min-w-0">
								<div class="flex items-center gap-2">
									<strong>{{ $item->name }}</strong>
									<span class="text-xs text-[color:var(--color-ink-muted)] uppercase tracking-wider">{{ $typeLabels[$item->type] ?? $item->type }}</span>
								</div>
								@if ($item->description)<p class="text-xs text-[color:var(--color-ink-muted)]">{{ $item->description }}</p>@endif
							</div>
							<span class="item-state-label text-xs text-[color:var(--color-ink-muted)] tabular-nums">{{ $c['name'] }}</span>
						</div>
					@endforeach
				</div>

				<div class="flex flex-wrap gap-4 text-xs mt-4 pt-3 border-t border-[color:var(--color-line)]">
					<strong class="text-[color:var(--color-ink-muted)] uppercase tracking-wider">{{ __('Legenda') }}:</strong>
					@foreach ($stateColors as $k => $c)
						<div class="flex items-center gap-2">
							<span class="w-5 h-5 rounded inline-flex items-center justify-center border text-xs font-bold {{ $c['bg'] }} {{ $c['border'] }} {{ $c['text'] }}">{{ $c['label'] }}</span>
							<span>{{ $c['name'] }}</span>
						</div>
					@endforeach
				</div>
			</div>
		@endif
	</div>
</section>

@push('scripts')
<script>
(function () {
	const CYCLE = ['unknown', 'has_access', 'no_access', 'needs_review'];
	const CLASSES = {
		unknown: {bg:'bg-slate-100', border:'border-slate-200', text:'text-slate-500', label:'—', name: @json(__('Onbekend'))},
		has_access: {bg:'bg-emerald-100', border:'border-emerald-300', text:'text-emerald-800', label:'✓', name: @json(__('Toegang'))},
		no_access: {bg:'bg-slate-300', border:'border-slate-400', text:'text-slate-700', label:'×', name: @json(__('Geen toegang'))},
		needs_review: {bg:'bg-amber-100', border:'border-amber-300', text:'text-amber-800', label:'?', name: @json(__('Heroverwegen'))},
	};
	const BADGE_CLASSES = {
		unknown: ['bg-slate-100','border-slate-200','text-slate-500'],
		has_access: ['bg-emerald-100','border-emerald-300','text-emerald-800'],
		no_access: ['bg-slate-300','border-slate-400','text-slate-700'],
		needs_review: ['bg-amber-100','border-amber-300','text-amber-800'],
	};

	const url = @json(route('tools.accessguard.matrix.update-item', ['locale' => $locale]));
	const csrf = document.querySelector('meta[name="csrf-token"]').content;
	const personId = {{ $person->id }};
	const badge = document.getElementById('cell-state-badge');

	document.querySelectorAll('.item-btn').forEach(btn => {
		btn.addEventListener('click', async () => {
			const current = btn.dataset.state;
			const next = CYCLE[(CYCLE.indexOf(current) + 1) % CYCLE.length];
			btn.disabled = true;
			try {
				const resp = await fetch(url, {
					method: 'POST',
					headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},
					body: JSON.stringify({
						person_id: personId,
						access_item_id: parseInt(btn.dataset.item, 10),
						state: next,
					}),
				});
				if (!resp.ok) throw new Error('http ' + resp.status);
				const body = await resp.json();
				const c = CLASSES[body.state];
				btn.className = 'item-btn w-10 h-10 rounded border font-bold text-base hover:scale-110 transition';
				btn.classList.add(c.bg, c.border, c.text);
				btn.textContent = c.label;
				btn.dataset.state = body.state;
				btn.title = @json(__('Laatst geverifieerd')) + ': ' + body.verified_at;
				const label = btn.closest('.p-2').querySelector('.item-state-label');
				if (label) label.textContent = c.name;
				// Update aggregate badge
				if (badge && body.cell_state) {
					['bg-slate-100','border-slate-200','text-slate-500','bg-emerald-100','border-emerald-300','text-emerald-800','bg-slate-300','border-slate-400','text-slate-700','bg-amber-100','border-amber-300','text-amber-800'].forEach(cls => badge.classList.remove(cls));
					BADGE_CLASSES[body.cell_state].forEach(cls => badge.classList.add(cls));
					const bc = CLASSES[body.cell_state];
					badge.textContent = bc.label + ' ' + bc.name;
				}
			} catch (e) {
				console.warn('item update failed', e);
			} finally {
				btn.disabled = false;
			}
		});
	});
})();
</script>
@endpush

@endsection
