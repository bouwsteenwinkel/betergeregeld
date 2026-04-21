@extends('layouts.app')

@section('title', __('PDF merge') . ' — ' . config('app.name'))

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[900px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Tools</span>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">PDF merge</span>
		</nav>
		<span class="pill pill-dark mb-5">Tool · {{ __('Gratis') }}</span>
		<h1 class="display-1 mb-5">PDF <span class="accent-word">{{ __('merge') }}</span></h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			{{ __('Voeg meerdere PDF\'s samen tot één bestand. Sleep om de volgorde aan te passen.') }}
		</p>
	</div>
</section>

<section class="py-16">
	<div class="max-w-[900px] mx-auto px-6">
		@include('tools._usage')

		<div class="card">
			<div class="flex items-center justify-between gap-4 mb-4 flex-wrap">
				<h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)]">
					{{ __('Bestanden') }}
					<span id="pmCount" class="text-[color:var(--color-ink)] ml-1">{{ count($sessionFiles) }}</span>
					<span class="text-[color:var(--color-ink-soft)]">
						/ {{ $caps['unlimited_files'] ? '∞' : $caps['max_files'] }}
					</span>
				</h2>
				<div class="text-xs text-[color:var(--color-ink-soft)]">
					{{ __('Totaal: :mb MB', ['mb' => collect($sessionFiles)->sum('size_mb')]) }}
					/ {{ $caps['unlimited_size'] ? '∞' : $caps['max_total_mb'] }} MB
				</div>
			</div>

			<label for="pmFile" id="pmDrop"
				class="block border-2 border-dashed border-[color:var(--color-line-strong)] rounded-[var(--radius-control)] p-8 text-center cursor-pointer hover:bg-[color:var(--color-surface-soft,#fafafa)] transition">
				<div class="text-sm font-medium mb-1">{{ __('Kies een PDF of sleep hem hierheen') }}</div>
				<div class="text-xs text-[color:var(--color-ink-soft)]">
					{{ __('Elk bestand afzonderlijk uploaden. Max :n MB per file.', ['n' => 100]) }}
				</div>
				<input id="pmFile" type="file" accept="application/pdf" class="hidden">
			</label>

			<div id="pmStatus" class="text-xs text-[color:var(--color-ink-muted)] mt-2 min-h-[1.25rem]"></div>
			<div id="pmError" class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3 mt-3" hidden></div>

			<ul id="pmList" class="mt-5 space-y-2">
				@foreach ($sessionFiles as $i => $f)
					<li data-id="{{ $f['id'] }}" class="flex items-center gap-3 p-3 rounded-[var(--radius-control)] border border-[color:var(--color-line)] bg-white">
						<span class="drag-handle cursor-grab text-[color:var(--color-ink-soft)] select-none" title="{{ __('Versleep') }}">⋮⋮</span>
						<span class="text-xs font-mono text-[color:var(--color-ink-muted)] w-8 text-right pm-index">{{ $i + 1 }}</span>
						<span class="flex-1 text-sm truncate">{{ $f['original_name'] }}</span>
						<span class="text-xs text-[color:var(--color-ink-muted)] font-mono">{{ $f['size_mb'] }} MB</span>
						<button type="button" data-remove="{{ $f['id'] }}"
							class="text-sm text-[color:var(--color-ink-soft)] hover:text-red-600 transition" title="{{ __('Verwijderen') }}">×</button>
					</li>
				@endforeach
			</ul>

			<form id="pmMergeForm" method="POST" action="{{ route('tools.pdf-merge.merge', ['locale' => app()->getLocale()]) }}" class="mt-6 flex gap-2 flex-wrap">
				@csrf
				<input type="hidden" name="order" id="pmOrder" value="{{ collect($sessionFiles)->pluck('id')->implode(',') }}">
				<button type="submit" id="pmMergeBtn" class="btn-accent" @if (count($sessionFiles) < 2) disabled @endif>
					{{ __('Mergen') }}
				</button>
				<form method="POST" action="{{ route('tools.pdf-merge.reset', ['locale' => app()->getLocale()]) }}" class="inline">
					@csrf
					<button type="submit" class="btn-dark">{{ __('Alles wissen') }}</button>
				</form>
			</form>

			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3 mt-3">
					{{ $errors->first() }}
				</div>
			@endif

			@if ($lastMerge)
				<div class="rounded-[var(--radius-control)] border border-emerald-200 bg-emerald-50 p-4 mt-6">
					<div class="flex items-center justify-between gap-3 flex-wrap">
						<div>
							<div class="font-semibold text-emerald-900">{{ __('Merge klaar') }}</div>
							<div class="text-xs text-emerald-900/80 font-mono">
								{{ $lastMerge['name'] }} · {{ $lastMerge['file_count'] }} {{ __('files') }} · {{ $lastMerge['size_mb'] }} MB
							</div>
						</div>
						<a href="{{ route('tools.pdf-merge.download', ['locale' => app()->getLocale(), 'key' => $lastMerge['key']]) }}" class="btn-accent">
							{{ __('Download') }}
						</a>
					</div>
				</div>
			@endif

			@if ($caps['watermark'])
				<p class="text-xs text-[color:var(--color-ink-soft)] mt-4">
					{{ __('Free plan: watermerk op output.') }}
					<a href="{{ route('pricing') }}" class="underline hover:text-[color:var(--color-ink)]">{{ __('Upgrade voor watermerk-vrij + meer files.') }}</a>
				</p>
			@endif
		</div>
	</div>
</section>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
	const list = document.getElementById('pmList');
	const orderInput = document.getElementById('pmOrder');
	const fileInput = document.getElementById('pmFile');
	const drop = document.getElementById('pmDrop');
	const statusEl = document.getElementById('pmStatus');
	const errEl = document.getElementById('pmError');
	const countEl = document.getElementById('pmCount');
	const mergeBtn = document.getElementById('pmMergeBtn');
	const CSRF = @json(csrf_token());
	const URL_UPLOAD = @json(route('tools.pdf-merge.upload', ['locale' => app()->getLocale()]));
	const URL_REMOVE = @json(route('tools.pdf-merge.remove', ['locale' => app()->getLocale(), 'fileId' => 'FILE_ID_HERE']));

	function setStatus(t) { statusEl.textContent = t || ''; }
	function setError(t) { if (!t) { errEl.hidden = true; errEl.textContent = ''; } else { errEl.hidden = false; errEl.textContent = t; } }

	function reindex() {
		Array.from(list.children).forEach((li, i) => {
			const idx = li.querySelector('.pm-index');
			if (idx) idx.textContent = (i + 1);
		});
		const ids = Array.from(list.children).map(li => li.dataset.id).filter(Boolean);
		orderInput.value = ids.join(',');
		countEl.textContent = ids.length;
		mergeBtn.disabled = ids.length < 2;
	}
	reindex();

	// Sortable init (script is deferred; wait a tick)
	setTimeout(() => {
		if (window.Sortable) {
			new Sortable(list, { handle: '.drag-handle', animation: 150, onEnd: reindex });
		}
	}, 100);

	// Upload handlers
	async function upload(file) {
		if (!file || file.type !== 'application/pdf') {
			setError(@json(__('Alleen PDF-bestanden zijn toegestaan.')));
			return;
		}
		setError('');
		setStatus(@json(__('Uploaden…')) + ' ' + file.name);
		const fd = new FormData();
		fd.append('_token', CSRF);
		fd.append('file', file);
		try {
			const r = await fetch(URL_UPLOAD, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
			const j = await r.json();
			if (!j.ok) {
				setError((j.error === 'max_files_reached') ? @json(__('Je plan-limiet van bestanden is bereikt.'))
					: (j.error === 'max_size_reached') ? @json(__('Je plan-limiet voor totaalgrootte is bereikt.'))
					: (j.error || @json(__('Upload mislukt.'))));
				setStatus('');
				return;
			}
			// Append to list
			const li = document.createElement('li');
			li.dataset.id = j.file.id;
			li.className = 'flex items-center gap-3 p-3 rounded-[var(--radius-control)] border border-[color:var(--color-line)] bg-white';
			li.innerHTML = `
				<span class="drag-handle cursor-grab text-[color:var(--color-ink-soft)] select-none">⋮⋮</span>
				<span class="text-xs font-mono text-[color:var(--color-ink-muted)] w-8 text-right pm-index"></span>
				<span class="flex-1 text-sm truncate"></span>
				<span class="text-xs text-[color:var(--color-ink-muted)] font-mono"></span>
				<button type="button" class="text-sm text-[color:var(--color-ink-soft)] hover:text-red-600 transition">×</button>
			`;
			li.children[2].textContent = j.file.name;
			li.children[3].textContent = j.file.size_mb + ' MB';
			li.children[4].dataset.remove = j.file.id;
			list.appendChild(li);
			reindex();
			setStatus(@json(__('Toegevoegd:')) + ' ' + j.file.name);
		} catch (e) {
			setError(@json(__('Upload mislukt.')) + ' ' + e.message);
			setStatus('');
		}
	}

	fileInput.addEventListener('change', (e) => {
		const files = Array.from(e.target.files || []);
		files.reduce((p, f) => p.then(() => upload(f)), Promise.resolve()).then(() => { fileInput.value = ''; });
	});

	// Drag/drop
	drop.addEventListener('dragover', (e) => { e.preventDefault(); drop.classList.add('bg-[color:var(--color-surface-soft,#fafafa)]'); });
	drop.addEventListener('dragleave', () => { drop.classList.remove('bg-[color:var(--color-surface-soft,#fafafa)]'); });
	drop.addEventListener('drop', (e) => {
		e.preventDefault();
		drop.classList.remove('bg-[color:var(--color-surface-soft,#fafafa)]');
		const files = Array.from(e.dataTransfer.files || []);
		files.reduce((p, f) => p.then(() => upload(f)), Promise.resolve());
	});

	// Remove handler
	list.addEventListener('click', async (e) => {
		const btn = e.target.closest('[data-remove]');
		if (!btn) return;
		const id = btn.dataset.remove;
		const li = btn.closest('li');
		const r = await fetch(URL_REMOVE.replace('FILE_ID_HERE', id), {
			method: 'DELETE',
			headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
		});
		if (r.ok && li) {
			li.remove();
			reindex();
		}
	});
});
</script>
@endpush

@endsection
