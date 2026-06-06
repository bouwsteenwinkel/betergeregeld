@extends('layouts.app')

@section('title', __('Risico\'s') . ', AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = __('Risico\'s');
	$kindLabels = [
		'stale_admin' => __('Stale admin'),
		'orphan_access' => __('Orphan access'),
		'excessive_access' => __('Excessive access'),
		'overdue_review' => __('Overdue review'),
		'overdue_action' => __('Overdue action'),
		'pending_onboarding' => __('Onboarding vergeten'),
		'stale_credential' => __('Stale credential'),
		'dormant_account' => __('Dormant account'),
		'admin_group_creep' => __('Admin-groep uitbreiding'),
	];
	$statusColors = [
		'open' => 'bg-red-100 text-red-800',
		'acknowledged' => 'bg-amber-100 text-amber-800',
		'resolved' => 'bg-emerald-100 text-emerald-800',
	];
	$severityBar = fn ($s) => '<span class="inline-flex gap-0.5">' . str_repeat('<span class="w-1.5 h-3 bg-red-500 rounded"></span>', (int) $s) . str_repeat('<span class="w-1.5 h-3 bg-slate-200 rounded"></span>', 5 - (int) $s) . '</span>';
@endphp

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[1400px] mx-auto px-6 space-y-4">
		@if (session('status'))
			<div class="card text-sm bg-emerald-50 border-emerald-200 text-emerald-900">{{ session('status') }}</div>
		@endif

		<div class="flex items-center justify-between flex-wrap gap-3">
			<div class="flex items-center gap-2 text-sm">
				<strong class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ __('Filter') }}:</strong>
				@foreach (['open' => __('Open'), 'acknowledged' => __('Bevestigd'), 'resolved' => __('Opgelost'), 'all' => __('Alles')] as $k => $l)
					<a href="{{ route('tools.accessguard.risks.index', ['locale' => $locale, 'status' => $k === 'all' ? '' : $k]) }}"
						class="px-3 py-1 rounded {{ $status === ($k === 'all' ? '' : $k) ? 'bg-[color:var(--color-accent)] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
						{{ $l }}
					</a>
				@endforeach
			</div>
			<form method="POST" action="{{ route('tools.accessguard.risks.scan', ['locale' => $locale]) }}">
				@csrf
				<button type="submit" class="btn-dark text-sm">{{ __('Scan nu') }}</button>
			</form>
		</div>

		<div class="card p-0 overflow-hidden">
			@if ($flags->isEmpty())
				<p class="text-sm text-[color:var(--color-ink-muted)] p-6 text-center">{{ __('Geen risico\'s gedetecteerd in deze selectie. Draai "Scan nu" om te checken.') }}</p>
			@else
				<table class="w-full text-sm">
					<thead class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] border-b border-[color:var(--color-line)]">
						<tr>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Ernst') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Type') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Onderwerp') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Omschrijving') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Status') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Gedetecteerd') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Acties') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($flags as $f)
							<tr class="border-b border-[color:var(--color-line)]/60 hover:bg-[color:var(--color-surface-soft,#fafafa)]">
								<td class="py-2 px-3">{!! $severityBar($f->severity) !!}</td>
								<td class="py-2 px-3 text-[color:var(--color-ink-muted)] whitespace-nowrap">{{ $kindLabels[$f->kind] ?? $f->kind }}</td>
								<td class="py-2 px-3 font-semibold">{{ $f->title }}</td>
								<td class="py-2 px-3 text-xs text-[color:var(--color-ink-muted)]">{{ $f->description }}</td>
								<td class="py-2 px-3">
									<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $statusColors[$f->status] ?? '' }}">
										{{ __(ucfirst($f->status)) }}
									</span>
								</td>
								<td class="py-2 px-3 text-xs text-[color:var(--color-ink-muted)] tabular-nums whitespace-nowrap">{{ $f->detected_at->format('d-m H:i') }}</td>
								<td class="py-2 px-3 text-right whitespace-nowrap">
									<button type="button" class="ai-explain-btn text-xs px-3 py-1 rounded bg-slate-100 text-slate-700 hover:bg-slate-200" data-id="{{ $f->id }}" title="{{ __('Leg uit met AI') }}">
										🤖 {{ __('Uitleg') }}
									</button>
									@if ($f->status === 'open')
										<form method="POST" action="{{ route('tools.accessguard.risks.acknowledge', ['locale' => $locale, 'id' => $f->id]) }}" class="inline">
											@csrf
											<button type="submit" class="text-xs px-3 py-1 rounded bg-amber-100 text-amber-800 hover:bg-amber-200">{{ __('Bevestig') }}</button>
										</form>
									@endif
									@if ($f->status !== 'resolved')
										<form method="POST" action="{{ route('tools.accessguard.risks.resolve', ['locale' => $locale, 'id' => $f->id]) }}" class="inline">
											@csrf
											<button type="submit" class="text-xs px-3 py-1 rounded bg-emerald-600 text-white hover:bg-emerald-700">{{ __('Opgelost') }}</button>
										</form>
									@else
										<form method="POST" action="{{ route('tools.accessguard.risks.reopen', ['locale' => $locale, 'id' => $f->id]) }}" class="inline">
											@csrf
											<button type="submit" class="text-xs px-2 py-1 rounded text-slate-500 hover:bg-slate-100">{{ __('Heropen') }}</button>
										</form>
									@endif
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			@endif
		</div>

		<div>{{ $flags->links() }}</div>
	</div>
</section>

<div id="ai-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
	<div class="bg-white rounded-[var(--radius-control)] shadow-xl max-w-2xl w-full max-h-[80vh] overflow-y-auto">
		<div class="p-5 border-b border-[color:var(--color-line)] flex items-start justify-between gap-3">
			<div class="flex items-center gap-2">
				<span class="text-lg">🤖</span>
				<h3 id="ai-title" class="text-lg font-bold">{{ __('AI-uitleg') }}</h3>
			</div>
			<button type="button" id="ai-close" class="text-slate-400 hover:text-slate-700 text-xl leading-none">×</button>
		</div>
		<div id="ai-body" class="p-5 text-sm">
			<div id="ai-loading" class="text-center py-8 text-[color:var(--color-ink-muted)]">{{ __('Bezig met AI-uitleg…') }}</div>
			<div id="ai-error" class="hidden p-3 rounded bg-red-50 border border-red-200 text-red-800"></div>
			<div id="ai-content" class="hidden space-y-4"></div>
		</div>
		<div class="p-3 border-t border-[color:var(--color-line)] text-xs text-[color:var(--color-ink-soft)] flex items-center justify-between">
			<span id="ai-cached-hint"></span>
			<span>{{ __('Aangedreven door OpenAI. Context is tenant-scoped, nooit secrets.') }}</span>
		</div>
	</div>
</div>

@push('scripts')
<script>
(function () {
	const modal = document.getElementById('ai-modal');
	const closeBtn = document.getElementById('ai-close');
	const loading = document.getElementById('ai-loading');
	const errorEl = document.getElementById('ai-error');
	const content = document.getElementById('ai-content');
	const cachedHint = document.getElementById('ai-cached-hint');
	const url = @json(route('tools.accessguard.ai.explain', ['locale' => $locale]));
	const csrf = document.querySelector('meta[name="csrf-token"]').content;

	function show() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
	function hide() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

	closeBtn.addEventListener('click', hide);
	modal.addEventListener('click', e => { if (e.target === modal) hide(); });

	document.querySelectorAll('.ai-explain-btn').forEach(btn => {
		btn.addEventListener('click', async () => {
			loading.classList.remove('hidden');
			errorEl.classList.add('hidden');
			content.classList.add('hidden');
			content.innerHTML = '';
			cachedHint.textContent = '';
			show();
			try {
				const resp = await fetch(url, {
					method: 'POST',
					headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf},
					body: JSON.stringify({subject_type: 'risk_flag', subject_id: parseInt(btn.dataset.id, 10)}),
				});
				const body = await resp.json();
				loading.classList.add('hidden');
				if (!resp.ok || !body.ok) {
					errorEl.textContent = body.error || ('HTTP ' + resp.status);
					errorEl.classList.remove('hidden');
					return;
				}
				const ex = body.explanation;
				const esc = (s) => String(s).replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'})[c]);
				let html = '';
				if (ex.title) html += `<h4 class="font-bold text-base">${esc(ex.title)}</h4>`;
				if (ex.summary) html += `<p>${esc(ex.summary)}</p>`;
				if (ex.why_it_matters) html += `<div><strong class="text-xs uppercase tracking-wider text-slate-500">${@json(__('Waarom dit ertoe doet'))}</strong><p class="mt-1">${esc(ex.why_it_matters)}</p></div>`;
				if (Array.isArray(ex.recommended_steps) && ex.recommended_steps.length) {
					html += `<div><strong class="text-xs uppercase tracking-wider text-slate-500">${@json(__('Aanbevolen stappen'))}</strong><ol class="list-decimal list-inside mt-1 space-y-1">` +
						ex.recommended_steps.map(s => `<li>${esc(s)}</li>`).join('') + `</ol></div>`;
				}
				if (Array.isArray(ex.warnings) && ex.warnings.length) {
					html += `<div class="rounded bg-amber-50 border border-amber-200 p-2"><strong class="text-xs uppercase tracking-wider text-amber-800">${@json(__('Waarschuwingen'))}</strong><ul class="list-disc list-inside mt-1 space-y-1">` +
						ex.warnings.map(s => `<li>${esc(s)}</li>`).join('') + `</ul></div>`;
				}
				content.innerHTML = html;
				content.classList.remove('hidden');
				cachedHint.textContent = body.cached ? @json(__('cached antwoord')) : (`${body.tokens.in + body.tokens.out} ` + @json(__('tokens')));
			} catch (e) {
				loading.classList.add('hidden');
				errorEl.textContent = 'Request failed';
				errorEl.classList.remove('hidden');
			}
		});
	});
})();
</script>
@endpush

@endsection
