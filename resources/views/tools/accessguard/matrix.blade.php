@extends('layouts.app')

@section('title', __('Access Matrix') . ', AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = __('Access Matrix');
	$stateColors = [
		'unknown' => ['bg' => 'bg-slate-100', 'border' => 'border-slate-200', 'text' => 'text-slate-500', 'label' => ', '],
		'has_access' => ['bg' => 'bg-emerald-100', 'border' => 'border-emerald-300', 'text' => 'text-emerald-800', 'label' => '✓'],
		'no_access' => ['bg' => 'bg-slate-300', 'border' => 'border-slate-400', 'text' => 'text-slate-700', 'label' => '×'],
		'needs_review' => ['bg' => 'bg-amber-100', 'border' => 'border-amber-300', 'text' => 'text-amber-800', 'label' => '?'],
	];
@endphp

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[1400px] mx-auto px-6 space-y-4">
		<div class="card">
			<div class="flex flex-wrap gap-4 items-center text-sm">
				<strong class="text-[color:var(--color-ink-muted)] uppercase tracking-wider text-xs">{{ __('Legenda') }}</strong>
				@foreach ($stateColors as $k => $c)
					<div class="flex items-center gap-2">
						<span class="w-6 h-6 rounded inline-flex items-center justify-center border {{ $c['bg'] }} {{ $c['border'] }} {{ $c['text'] }} font-bold">{{ $c['label'] }}</span>
						<span class="text-xs">{{ __(ucfirst(str_replace('_', ' ', $k))) }}</span>
					</div>
				@endforeach
				<span class="text-xs text-[color:var(--color-ink-soft)] ml-auto">{{ __('Klik op een cel om door de statussen te rouleren') }}</span>
			</div>
		</div>

		@if ($people->isEmpty() || $systems->isEmpty())
			<div class="card text-sm">
				{{ __('Voeg eerst') }}
				@if ($people->isEmpty())
					<a href="{{ route('tools.accessguard.people.index', ['locale' => $locale]) }}" class="text-[color:var(--color-accent)] font-semibold hover:underline">{{ __('personen') }}</a>
				@endif
				@if ($people->isEmpty() && $systems->isEmpty())
					{{ __('en') }}
				@endif
				@if ($systems->isEmpty())
					<a href="{{ route('tools.accessguard.systems.index', ['locale' => $locale]) }}" class="text-[color:var(--color-accent)] font-semibold hover:underline">{{ __('systemen') }}</a>
				@endif
				{{ __('toe voordat je de matrix kunt invullen.') }}
			</div>
		@else
			<div class="card p-0 overflow-x-auto">
				<table class="w-full text-sm border-collapse" id="accessguard-matrix">
					<thead>
						<tr class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)]">
							<th class="sticky left-0 z-10 bg-white text-left p-3 border-b border-r border-[color:var(--color-line)] min-w-[220px]">{{ __('Persoon') }}</th>
							@foreach ($systems as $s)
								@php $itemCount = $itemsBySystem[$s->id] ?? 0; @endphp
								<th class="p-2 border-b border-[color:var(--color-line)] align-bottom" style="min-width:60px">
									<div class="transform -rotate-45 origin-bottom-left whitespace-nowrap text-left pl-4" style="height:120px">
										{{ $s->name }}
										@if ($itemCount > 0)
											<span class="text-[10px] text-[color:var(--color-ink-muted)]">({{ $itemCount }})</span>
										@endif
									</div>
								</th>
							@endforeach
						</tr>
					</thead>
					<tbody>
						@foreach ($people as $p)
							<tr>
								<td class="sticky left-0 z-10 bg-white p-3 border-b border-r border-[color:var(--color-line)]">
									<div class="font-semibold">{{ $p->full_name }}</div>
									<div class="text-xs text-[color:var(--color-ink-muted)]">{{ $p->job_title ?: ', ' }}</div>
								</td>
								@foreach ($systems as $s)
									@php
										$cell = $cells[$p->id][$s->id] ?? ['state' => 'unknown', 'verified_at' => null];
										$c = $stateColors[$cell['state']];
										$hasItems = ($itemsBySystem[$s->id] ?? 0) > 0;
									@endphp
									<td class="p-1 border-b border-[color:var(--color-line)] text-center">
										@if ($hasItems)
											<a href="{{ route('tools.accessguard.matrix.cell', ['locale' => $locale, 'personId' => $p->id, 'systemId' => $s->id]) }}"
												class="cell inline-flex items-center justify-center w-9 h-9 rounded border font-bold {{ $c['bg'] }} {{ $c['border'] }} {{ $c['text'] }} hover:scale-110 transition relative"
												@if ($cell['verified_at']) title="{{ __('Laatst geverifieerd') }}: {{ $cell['verified_at'] }} · {{ __('klik om items te bekijken') }}" @else title="{{ __('klik om items te bekijken') }}" @endif>
												{{ $c['label'] }}
												<span class="absolute -top-1 -right-1 bg-[color:var(--color-accent)] text-white text-[8px] rounded-full w-3 h-3 flex items-center justify-center">·</span>
											</a>
										@else
											<button type="button"
												class="cell w-9 h-9 rounded border font-bold {{ $c['bg'] }} {{ $c['border'] }} {{ $c['text'] }} hover:scale-110 transition"
												data-person="{{ $p->id }}"
												data-system="{{ $s->id }}"
												data-state="{{ $cell['state'] }}"
												@if ($cell['verified_at']) title="{{ __('Laatst geverifieerd') }}: {{ $cell['verified_at'] }}" @endif>
												{{ $c['label'] }}
											</button>
										@endif
									</td>
								@endforeach
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		@endif
	</div>
</section>

@push('scripts')
<script>
(function () {
	const CYCLE = ['unknown', 'has_access', 'no_access', 'needs_review'];
	const CLASSES = {
		unknown: {bg:'bg-slate-100', border:'border-slate-200', text:'text-slate-500', label:', '},
		has_access: {bg:'bg-emerald-100', border:'border-emerald-300', text:'text-emerald-800', label:'✓'},
		no_access: {bg:'bg-slate-300', border:'border-slate-400', text:'text-slate-700', label:'×'},
		needs_review: {bg:'bg-amber-100', border:'border-amber-300', text:'text-amber-800', label:'?'},
	};
	const url = @json(route('tools.accessguard.matrix.update', ['locale' => $locale]));
	const csrf = document.querySelector('meta[name="csrf-token"]').content;

	// Only cells without items use the AJAX cycle; item-bearing cells are <a href=...>.
	document.querySelectorAll('#accessguard-matrix button.cell').forEach(btn => {
		btn.addEventListener('click', async () => {
			const current = btn.dataset.state;
			const next = CYCLE[(CYCLE.indexOf(current) + 1) % CYCLE.length];
			const old = Object.assign({}, btn.classList);
			btn.disabled = true;
			try {
				const resp = await fetch(url, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'application/json',
						'X-CSRF-TOKEN': csrf,
					},
					body: JSON.stringify({
						person_id: parseInt(btn.dataset.person, 10),
						system_id: parseInt(btn.dataset.system, 10),
						state: next,
					}),
				});
				if (!resp.ok) throw new Error('http ' + resp.status);
				const body = await resp.json();
				// Rebuild classes
				btn.className = 'cell w-9 h-9 rounded border font-bold hover:scale-110 transition';
				const c = CLASSES[body.state];
				btn.classList.add(c.bg, c.border, c.text);
				btn.textContent = c.label;
				btn.dataset.state = body.state;
				btn.title = @json(__('Laatst geverifieerd')) + ': ' + body.verified_at;
			} catch (e) {
				console.warn('accessguard cell update failed', e);
			} finally {
				btn.disabled = false;
			}
		});
	});
})();
</script>
@endpush

@endsection
