@extends('layouts.app')

@section('title', __('Kolommen mappen') . ', AccessGuard')

@php
	$locale = app()->getLocale();
	$kindLabels = ['people' => __('Personen'), 'systems' => __('Systemen')];
	$crumb = __('Importeren') . ' · ' . __('Kolommen mappen');
@endphp

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[1100px] mx-auto px-6">
		<form method="POST" action="{{ route('tools.accessguard.data.import-commit', ['locale' => $locale]) }}" class="card space-y-4">
			@csrf
			<input type="hidden" name="key" value="{{ $key }}">

			<div class="flex items-start justify-between flex-wrap gap-2">
				<div>
					<h2 class="text-lg font-bold">{{ __('Koppel jouw kolommen') }}</h2>
					<p class="text-sm text-[color:var(--color-ink-muted)]">
						{{ __(':n rijen gevonden. Kies voor elke kolom waar deze heen moet.', ['n' => count($stash['rows'])]) }}
					</p>
				</div>
				<div class="flex items-center gap-2">
					<button type="button" id="ai-map-btn" class="text-xs px-3 py-1.5 rounded bg-slate-800 text-white hover:bg-slate-700">
						🤖 {{ __('AI: voorstel mapping') }}
					</button>
					<span class="pill pill-teal text-xs">{{ $kindLabels[$kind] ?? $kind }}</span>
				</div>
			</div>

			<div class="overflow-x-auto border border-[color:var(--color-line)] rounded">
				<table class="w-full text-xs">
					<thead>
						<tr class="bg-slate-50 border-b border-[color:var(--color-line)]">
							@foreach ($headers as $idx => $h)
								<th class="p-3 text-left align-top" style="min-width:160px">
									<div class="font-bold text-[color:var(--color-ink)] mb-2">{{ $h }}</div>
									<select name="mapping[{{ $idx }}]" class="field-input py-1 text-xs w-full">
										<option value="">, {{ __('negeren') }}, </option>
										@foreach ($fields as $fieldKey => $fieldLabel)
											<option value="{{ $fieldKey }}" @selected(isset($suggested[$fieldKey]) && $suggested[$fieldKey] === $idx)>{{ $fieldLabel }}</option>
										@endforeach
									</select>
								</th>
							@endforeach
						</tr>
					</thead>
					<tbody>
						@foreach ($preview as $row)
							<tr class="border-b border-[color:var(--color-line)]/40">
								@foreach ($headers as $idx => $_)
									<td class="p-3 text-[color:var(--color-ink-muted)] font-mono text-xs align-top">{{ $row[$idx] ?? '' }}</td>
								@endforeach
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			<p class="text-xs text-[color:var(--color-ink-muted)]">
				{{ __('Preview toont de eerste 5 rijen. Bij commit wordt elke rij verwerkt en bestaande records worden bijgewerkt op basis van') }}
				<code>{{ $kind === 'people' ? 'email' : 'name' }}</code>.
			</p>

			<div class="flex items-center gap-3 border-t border-[color:var(--color-line)] pt-4">
				<button type="submit" class="btn-accent text-sm">{{ __('Importeren starten') }}</button>
				<a href="{{ route('tools.accessguard.data.show', ['locale' => $locale]) }}" class="text-sm text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">{{ __('Annuleren') }}</a>
				<span id="ai-map-status" class="text-xs text-[color:var(--color-ink-muted)]"></span>
			</div>
		</form>
	</div>
</section>

@push('scripts')
<script>
(function () {
	const btn = document.getElementById('ai-map-btn');
	const status = document.getElementById('ai-map-status');
	if (!btn) return;
	const url = @json(route('tools.accessguard.data.ai-smart-map', ['locale' => $locale]));
	const csrf = document.querySelector('meta[name="csrf-token"]').content;
	const key = @json($key);

	btn.addEventListener('click', async () => {
		btn.disabled = true;
		status.textContent = '🤖 ' + @json(__('AI analyseert de kolommen…'));
		try {
			const resp = await fetch(url, {
				method: 'POST',
				headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},
				body: JSON.stringify({key}),
			});
			const body = await resp.json();
			if (!resp.ok || !body.ok) {
				status.textContent = body.error || 'error';
				return;
			}
			// Apply the mapping to each <select name="mapping[N]">
			let applied = 0;
			Object.entries(body.mapping).forEach(([idx, field]) => {
				const sel = document.querySelector(`select[name="mapping[${idx}]"]`);
				if (!sel) return;
				sel.value = field ?? '';
				applied++;
			});
			status.textContent = @json(__(':n kolommen ingevuld door AI')).replace(':n', applied);
		} catch (e) {
			status.textContent = 'error: ' + e.message;
		} finally {
			btn.disabled = false;
		}
	});
})();
</script>
@endpush

@endsection
