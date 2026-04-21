@extends('layouts.app')

@section('title', __('Internet snelheidstest') . ' — ' . config('app.name'))

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[900px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Tools</span>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Speedtest</span>
		</nav>
		<span class="pill pill-dark mb-5">Tool · {{ __('Gratis') }}</span>
		<h1 class="display-1 mb-5">Internet <span class="accent-word">{{ __('speedtest') }}</span></h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			{{ __('Meet ping, downloadsnelheid en uploadsnelheid vanuit je browser. Tot :d MB download, :u MB upload.', ['d' => $maxDownloadMb, 'u' => $maxUploadMb]) }}
		</p>
	</div>
</section>

<section class="py-16">
	<div class="max-w-[900px] mx-auto px-6">
		@include('tools._usage')

		<div class="card">
			<div class="flex items-center justify-between gap-4 mb-6 flex-wrap">
				<div>
					<h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ __('Meting') }}</h2>
					<p id="stStatus" class="text-sm text-[color:var(--color-ink)] mt-1">{{ __('Klaar om te starten.') }}</p>
				</div>
				<div class="flex gap-2">
					<button id="stStart" type="button" class="btn-accent">{{ __('Start test') }}</button>
					<button id="stReset" type="button" class="btn-dark" hidden>{{ __('Opnieuw') }}</button>
				</div>
			</div>

			<div class="h-2 rounded-full bg-[color:var(--color-line)] overflow-hidden mb-6">
				<div id="stBar" class="h-full bg-[color:var(--color-accent)] transition-all duration-150" style="width: 0%"></div>
			</div>

			<div class="grid grid-cols-3 gap-4 mb-2">
				<div class="text-center">
					<div id="stPing" class="text-3xl font-bold tabular-nums">—</div>
					<div class="text-xs text-[color:var(--color-ink-muted)] mt-1">{{ __('Ping (ms)') }}</div>
				</div>
				<div class="text-center">
					<div id="stDown" class="text-3xl font-bold tabular-nums">—</div>
					<div class="text-xs text-[color:var(--color-ink-muted)] mt-1">{{ __('Download (Mbps)') }}</div>
				</div>
				<div class="text-center">
					<div id="stUp" class="text-3xl font-bold tabular-nums">—</div>
					<div class="text-xs text-[color:var(--color-ink-muted)] mt-1">{{ __('Upload (Mbps)') }}</div>
				</div>
			</div>

			<div id="stError" class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3 mt-4" hidden></div>

			<p class="text-xs text-[color:var(--color-ink-soft)] mt-6 pt-5 border-t border-[color:var(--color-line)] leading-relaxed">
				{{ __('Gemeten snelheden zijn een indicatie; de echte bandbreedte hangt mede af van afstand tot de server, netwerkroute en browser-limieten.') }}
			</p>
		</div>
	</div>
</section>

@push('scripts')
<script>
(function () {
	'use strict';
	const $ = (id) => document.getElementById(id);
	const btnStart = $('stStart'), btnReset = $('stReset'), bar = $('stBar'), status = $('stStatus'),
		errBox = $('stError'), pingEl = $('stPing'), downEl = $('stDown'), upEl = $('stUp');

	const URL_PING = @json(route('tools.speedtest.ping'));
	const URL_DOWN = @json(route('tools.speedtest.download'));
	const URL_UP = @json(route('tools.speedtest.upload'));
	const URL_RECORD = @json(route('tools.speedtest.record', ['locale' => app()->getLocale()]));
	const CSRF = @json(csrf_token());
	const DOWN_BYTES = {{ $maxDownloadMb * 1024 * 1024 }};
	const UP_BYTES = {{ $maxUploadMb * 1024 * 1024 }};
	const PING_ROUNDS = 5;

	let running = false;

	const LABELS = {
		starting: @json(__('Voorbereiden…')),
		ping: @json(__('Ping meten…')),
		download: @json(__('Download meten…')),
		upload: @json(__('Upload meten…')),
		done: @json(__('Klaar.')),
		failed: @json(__('Meting mislukt.')),
	};

	function setBar(pct) { bar.style.width = Math.max(0, Math.min(100, pct)) + '%'; }
	function setStatus(t) { status.textContent = t; }
	function setError(t) { if (!t) { errBox.hidden = true; errBox.textContent = ''; } else { errBox.hidden = false; errBox.textContent = t; } }
	function reset() {
		setBar(0); setError(''); setStatus(@json(__('Klaar om te starten.')));
		pingEl.textContent = '—'; downEl.textContent = '—'; upEl.textContent = '—';
		btnReset.hidden = true;
	}

	async function measurePing() {
		const samples = [];
		for (let i = 0; i < PING_ROUNDS; i++) {
			const t0 = performance.now();
			const r = await fetch(URL_PING + '?_=' + Date.now(), { cache: 'no-store' });
			await r.json();
			const rtt = performance.now() - t0;
			samples.push(rtt);
			setBar(5 + (i / PING_ROUNDS) * 15);
		}
		samples.sort((a, b) => a - b);
		const trimmed = samples.slice(1, samples.length - 1);
		const avg = trimmed.reduce((a, b) => a + b, 0) / trimmed.length;
		return Math.max(0, avg);
	}

	async function measureDownload() {
		const url = URL_DOWN + '?size=' + DOWN_BYTES + '&_=' + Date.now();
		const t0 = performance.now();
		const resp = await fetch(url, { cache: 'no-store' });
		if (!resp.ok || !resp.body) throw new Error('download failed ' + resp.status);
		const reader = resp.body.getReader();
		let received = 0;
		while (true) {
			const { done, value } = await reader.read();
			if (done) break;
			received += value.length;
			const pct = 20 + (received / DOWN_BYTES) * 40;
			setBar(pct);
		}
		const elapsed = (performance.now() - t0) / 1000;
		const mbps = (received * 8) / elapsed / 1_000_000;
		return { mbps, bytes: received, seconds: elapsed };
	}

	async function measureUpload() {
		// Random blob — incompressible, reflects real network throughput
		const buf = new Uint8Array(UP_BYTES);
		crypto.getRandomValues(buf.subarray(0, Math.min(UP_BYTES, 65536)));
		// Fill rest by repeating the random block
		for (let i = 65536; i < UP_BYTES; i += 65536) {
			buf.copyWithin(i, 0, Math.min(65536, UP_BYTES - i));
		}

		setBar(60);
		const t0 = performance.now();
		const resp = await fetch(URL_UP, {
			method: 'POST',
			headers: { 'Content-Type': 'application/octet-stream', 'X-CSRF-TOKEN': CSRF },
			body: buf,
			cache: 'no-store',
		});
		if (!resp.ok) throw new Error('upload failed ' + resp.status);
		const json = await resp.json();
		const elapsed = (performance.now() - t0) / 1000;
		const bytes = json.received_bytes ?? UP_BYTES;
		const mbps = (bytes * 8) / elapsed / 1_000_000;
		setBar(95);
		return { mbps, bytes, seconds: elapsed };
	}

	async function record(pingMs, downMbps, upMbps) {
		try {
			await fetch(URL_RECORD, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
				body: JSON.stringify({ ping_ms: pingMs, download_mbps: downMbps, upload_mbps: upMbps }),
			});
		} catch (e) { /* best-effort */ }
	}

	async function run() {
		if (running) return;
		running = true;
		btnStart.disabled = true; btnReset.hidden = true;
		reset();
		setStatus(LABELS.starting); setBar(2);

		try {
			setStatus(LABELS.ping);
			const pingMs = await measurePing();
			pingEl.textContent = pingMs.toFixed(0);

			setStatus(LABELS.download);
			const d = await measureDownload();
			downEl.textContent = d.mbps.toFixed(1);

			setStatus(LABELS.upload);
			const u = await measureUpload();
			upEl.textContent = u.mbps.toFixed(1);

			setBar(100); setStatus(LABELS.done);
			await record(pingMs, d.mbps, u.mbps);
		} catch (err) {
			setError(LABELS.failed + ' ' + (err && err.message ? '(' + err.message + ')' : ''));
			setStatus(LABELS.failed);
		} finally {
			running = false;
			btnStart.disabled = false;
			btnReset.hidden = false;
		}
	}

	btnStart.addEventListener('click', run);
	btnReset.addEventListener('click', reset);
})();
</script>
@endpush

@endsection
