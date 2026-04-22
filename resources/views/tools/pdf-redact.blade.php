@extends('layouts.app')

@section('title', __('PDF redact') . ' — ' . config('app.name'))

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1100px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<a href="/{{ app()->getLocale() }}/tools" class="hover:text-white">Tools</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">PDF redact</span>
		</nav>
		<span class="pill pill-teal mb-5">Pro</span>
		<h1 class="display-1 mb-5">PDF <span class="accent-word">{{ __('redact') }}</span></h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			{{ __('Teken zwarte balken over gevoelige content. De PDF wordt per pagina geraster­iseerd zodat onderliggende tekst niet meer te extraheren is.') }}
		</p>
	</div>
</section>

<section class="py-16">
	<div class="max-w-[1100px] mx-auto px-6">
		@include('tools._usage')

		@if (! $job)
			{{-- STATE 1: no upload yet --}}
			<form id="prUploadForm" method="POST" action="{{ route('tools.pdf-redact.upload', ['locale' => app()->getLocale()]) }}" enctype="multipart/form-data" class="card space-y-4">
				@csrf
				<div>
					<label for="prFile" class="block text-sm font-semibold mb-2">{{ __('Kies een PDF') }}</label>
					<input id="prFile" name="file" type="file" accept="application/pdf" required class="field-input">
					<p class="text-xs text-[color:var(--color-ink-soft)] mt-1.5">
						{{ __('Max :n MB, max :p pagina\'s. Rendering gebeurt op :dpi DPI.', ['n' => 50, 'p' => 100, 'dpi' => 150]) }}
					</p>
				</div>
				<div id="prUploadStatus" class="text-sm text-[color:var(--color-ink-muted)] min-h-[1.25rem]"></div>
				<div id="prUploadError" class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3" hidden></div>
				<button type="submit" class="btn-accent">{{ __('Upload en render') }}</button>
			</form>
		@else
			{{-- STATE 2: job active, show pages --}}
			<div class="card mb-6">
				<div class="flex items-center justify-between gap-4 flex-wrap">
					<div>
						<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Actieve job') }}</div>
						<div class="font-semibold">{{ $job['original_name'] }}</div>
						<div class="text-xs text-[color:var(--color-ink-muted)]">{{ count($job['pages']) }} {{ __('pagina\'s gerenderd') }}</div>
					</div>
					<div class="flex gap-2 items-center">
						<span class="pill pill-ink text-xs" id="prRectCount">0 {{ __('boxes') }}</span>
						<form method="POST" action="{{ route('tools.pdf-redact.reset', ['locale' => app()->getLocale()]) }}" class="inline">
							@csrf
							<button type="submit" class="btn-dark text-sm">{{ __('Nieuwe PDF') }}</button>
						</form>
					</div>
				</div>
			</div>

			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3 mb-4">
					{{ $errors->first() }}
				</div>
			@endif

			@if ($lastOutput)
				<div class="rounded-[var(--radius-control)] border border-emerald-200 bg-emerald-50 p-4 mb-6">
					<div class="flex items-center justify-between gap-3 flex-wrap">
						<div>
							<div class="font-semibold text-emerald-900">{{ __('PDF geredacteerd') }}</div>
							<div class="text-xs text-emerald-900/80 font-mono">
								{{ $lastOutput['name'] }} · {{ $lastOutput['rect_count'] }} {{ __('boxes') }} · {{ $lastOutput['size_mb'] }} MB
							</div>
						</div>
						<a href="{{ route('tools.pdf-redact.download', ['locale' => app()->getLocale(), 'key' => $lastOutput['key']]) }}" class="btn-accent">
							{{ __('Download') }}
						</a>
					</div>
				</div>
			@endif

			<div class="card">
				<div class="flex items-center justify-between gap-4 mb-4 flex-wrap">
					<div class="flex items-center gap-2">
						<button type="button" id="prPrev" class="btn-dark text-sm">← {{ __('Vorige') }}</button>
						<span class="text-sm font-medium">{{ __('Pagina') }} <span id="prPageNum">1</span> / {{ count($job['pages']) }}</span>
						<button type="button" id="prNext" class="btn-dark text-sm">{{ __('Volgende') }} →</button>
					</div>
					<div class="flex items-center gap-2">
						<button type="button" id="prUndo" class="btn-dark text-sm" disabled>{{ __('Undo box') }}</button>
						<button type="button" id="prClearPage" class="btn-dark text-sm">{{ __('Wis pagina') }}</button>
					</div>
				</div>

				<div class="text-xs text-[color:var(--color-ink-muted)] mb-3">
					{{ __('Sleep op de pagina om zwarte redact-balken te tekenen. Ze worden pas permanent na "Toepassen & download".') }}
				</div>

				<div id="prCanvasWrap" class="border border-[color:var(--color-line)] rounded-[var(--radius-control)] overflow-auto bg-[color:var(--color-surface-soft,#fafafa)]" style="max-height: 70vh">
					<div id="prStage" class="relative" style="width: fit-content; margin: 0 auto; padding: 16px">
						{{-- Image + overlay injected by JS --}}
					</div>
				</div>

				<form id="prApplyForm" method="POST" action="{{ route('tools.pdf-redact.apply', ['locale' => app()->getLocale()]) }}" class="mt-4 flex gap-2 items-center flex-wrap">
					@csrf
					<input type="hidden" name="rects" id="prRectsJson">
					<button type="submit" id="prApplyBtn" class="btn-accent" disabled>{{ __('Toepassen & download') }}</button>
					<span class="text-xs text-[color:var(--color-ink-soft)]">{{ __('Je krijgt een nieuwe PDF met geflattende raster-pagina\'s.') }}</span>
				</form>
			</div>
		@endif
	</div>
</section>

@if ($job)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
	const PAGES = @json($job['pages']);
	const PREVIEW_URL = @json(route('tools.pdf-redact.preview', ['locale' => app()->getLocale(), 'page' => 'PAGE_NUM']));

	const stage = document.getElementById('prStage');
	const pageNumEl = document.getElementById('prPageNum');
	const rectCountEl = document.getElementById('prRectCount');
	const btnPrev = document.getElementById('prPrev');
	const btnNext = document.getElementById('prNext');
	const btnUndo = document.getElementById('prUndo');
	const btnClear = document.getElementById('prClearPage');
	const applyBtn = document.getElementById('prApplyBtn');
	const rectsInput = document.getElementById('prRectsJson');

	let currentPageIdx = 0;
	let img = null;
	// rects: { [pageNum]: [{x,y,w,h}, ...] } in PNG-pixel space
	const rects = {};

	function updateCount() {
		const total = Object.values(rects).reduce((a, arr) => a + arr.length, 0);
		rectCountEl.textContent = total + ' {{ __("boxes") }}';
		applyBtn.disabled = total === 0;
		btnUndo.disabled = !(rects[currentPage()] && rects[currentPage()].length);
		rectsInput.value = JSON.stringify(
			Object.entries(rects).flatMap(([page, list]) => list.map(r => ({ page: +page, ...r })))
		);
	}

	function currentPage() { return PAGES[currentPageIdx].num; }

	function renderOverlay(scale) {
		// Clear existing rect overlays
		stage.querySelectorAll('.pr-rect').forEach(el => el.remove());
		const list = rects[currentPage()] || [];
		list.forEach((r, i) => {
			const el = document.createElement('div');
			el.className = 'pr-rect absolute bg-black/95 border border-black';
			el.style.left = (r.x * scale) + 'px';
			el.style.top = (r.y * scale) + 'px';
			el.style.width = (r.w * scale) + 'px';
			el.style.height = (r.h * scale) + 'px';
			el.style.pointerEvents = 'none';
			stage.appendChild(el);
		});
	}

	function loadPage(idx) {
		currentPageIdx = Math.max(0, Math.min(PAGES.length - 1, idx));
		pageNumEl.textContent = PAGES[currentPageIdx].num;

		// Clear stage
		stage.innerHTML = '';
		img = document.createElement('img');
		img.alt = 'page preview';
		img.style.display = 'block';
		img.style.maxWidth = '100%';
		img.style.userSelect = 'none';
		img.draggable = false;
		img.src = PREVIEW_URL.replace('PAGE_NUM', String(currentPage()));
		stage.appendChild(img);

		img.onload = function () {
			// scale = rendered pixel width / natural PNG pixel width
			const scale = img.clientWidth / img.naturalWidth;
			attachDrawing(scale);
			renderOverlay(scale);
			updateCount();
		};
		window.addEventListener('resize', debounceRedraw);
	}

	let resizeTimer = null;
	function debounceRedraw() {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(() => {
			if (!img) return;
			const scale = img.clientWidth / img.naturalWidth;
			renderOverlay(scale);
		}, 100);
	}

	function attachDrawing(scale) {
		let start = null;
		let previewEl = null;

		function onDown(e) {
			if (e.button !== 0) return;
			e.preventDefault();
			const r = img.getBoundingClientRect();
			start = { x: e.clientX - r.left, y: e.clientY - r.top };
			previewEl = document.createElement('div');
			previewEl.className = 'absolute bg-black/70 border-2 border-dashed border-white';
			previewEl.style.left = start.x + 'px';
			previewEl.style.top = start.y + 'px';
			previewEl.style.width = '0px';
			previewEl.style.height = '0px';
			previewEl.style.pointerEvents = 'none';
			// Position relative to stage (which has padding); add offsetLeft/Top of img
			previewEl.style.left = (start.x + img.offsetLeft) + 'px';
			previewEl.style.top = (start.y + img.offsetTop) + 'px';
			stage.appendChild(previewEl);
		}
		function onMove(e) {
			if (!start || !previewEl) return;
			const r = img.getBoundingClientRect();
			const cx = e.clientX - r.left;
			const cy = e.clientY - r.top;
			const x1 = Math.min(start.x, cx), y1 = Math.min(start.y, cy);
			const w = Math.abs(cx - start.x), h = Math.abs(cy - start.y);
			previewEl.style.left = (x1 + img.offsetLeft) + 'px';
			previewEl.style.top = (y1 + img.offsetTop) + 'px';
			previewEl.style.width = w + 'px';
			previewEl.style.height = h + 'px';
		}
		function onUp(e) {
			if (!start) return;
			const r = img.getBoundingClientRect();
			const cx = e.clientX - r.left;
			const cy = e.clientY - r.top;
			const x1 = Math.min(start.x, cx), y1 = Math.min(start.y, cy);
			const w = Math.abs(cx - start.x), h = Math.abs(cy - start.y);
			if (previewEl) { previewEl.remove(); previewEl = null; }
			start = null;
			if (w < 4 || h < 4) return; // ignore accidental clicks
			const natural = {
				x: Math.round(x1 / scale),
				y: Math.round(y1 / scale),
				w: Math.round(w / scale),
				h: Math.round(h / scale),
			};
			rects[currentPage()] = rects[currentPage()] || [];
			rects[currentPage()].push(natural);
			renderOverlay(scale);
			updateCount();
		}

		img.addEventListener('mousedown', onDown);
		document.addEventListener('mousemove', onMove);
		document.addEventListener('mouseup', onUp);
	}

	btnPrev.addEventListener('click', () => loadPage(currentPageIdx - 1));
	btnNext.addEventListener('click', () => loadPage(currentPageIdx + 1));
	btnUndo.addEventListener('click', () => {
		const list = rects[currentPage()];
		if (list && list.length) {
			list.pop();
			const scale = img.clientWidth / img.naturalWidth;
			renderOverlay(scale);
			updateCount();
		}
	});
	btnClear.addEventListener('click', () => {
		rects[currentPage()] = [];
		const scale = img.clientWidth / img.naturalWidth;
		renderOverlay(scale);
		updateCount();
	});

	loadPage(0);
});
</script>
@endpush
@else
@push('scripts')
<script>
// Upload progress (simple fetch + XHR alt)
document.addEventListener('DOMContentLoaded', function () {
	const form = document.getElementById('prUploadForm');
	if (!form) return;
	const status = document.getElementById('prUploadStatus');
	const err = document.getElementById('prUploadError');

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		err.hidden = true; err.textContent = '';
		status.textContent = @json(__('Uploaden en renderen… dit kan enkele seconden duren.'));
		const fd = new FormData(form);
		fetch(form.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
			.then(r => r.json().then(j => ({ ok: r.ok, j })))
			.then(({ ok, j }) => {
				if (!ok || !j.ok) {
					err.hidden = false;
					err.textContent = j.error === 'pdf_too_long'
						? @json(__('PDF te lang voor deze tool (:p pagina\'s, max :l).')).replace(':p', j.pages).replace(':l', j.limit)
						: (j.error || @json(__('Upload mislukt.')));
					status.textContent = '';
					return;
				}
				location.reload();
			})
			.catch((e) => {
				err.hidden = false;
				err.textContent = @json(__('Upload mislukt.')) + ' ' + e.message;
				status.textContent = '';
			});
	});
});
</script>
@endpush
@endif

@endsection
