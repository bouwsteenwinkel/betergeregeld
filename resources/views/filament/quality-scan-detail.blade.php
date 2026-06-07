@php
	$problems = $scan->findings->whereIn('status', ['warn', 'fail']);
	$groups = [
		'hoog'   => ['label' => 'Hoge ernst',   'color' => '#dc2626', 'items' => $problems->where('severity', 'hoog')->values()],
		'middel' => ['label' => 'Middel',        'color' => '#d97706', 'items' => $problems->where('severity', 'middel')->values()],
		'laag'   => ['label' => 'Laag',          'color' => '#6b7280', 'items' => $problems->where('severity', 'laag')->values()],
	];
	$passCount = $scan->findings->where('status', 'pass')->count();
	$badge = fn ($s) => $s === 'fail' ? ['#fee2e2', '#991b1b', 'FAIL'] : ['#fef3c7', '#92400e', 'WARN'];
@endphp

<div style="font-size:14px;color:#1f2430;line-height:1.5">
	{{-- Meta --}}
	<div style="display:flex;flex-wrap:wrap;gap:8px 18px;padding:12px 14px;background:#f5f6f8;border-radius:10px;margin-bottom:14px">
		<span><strong>Score:</strong> {{ $scan->score ?? '—' }}/100</span>
		<span><strong>Status:</strong> {{ $scan->status }}</span>
		<span><strong>HTTP:</strong> {{ $scan->http_status ?? '—' }}</span>
		<span><strong>Ophalen:</strong> {{ $scan->fetch_duration_ms ?? '—' }} ms</span>
		@if ($scan->ai_model)
			<span><strong>AI:</strong> {{ $scan->ai_model }} ({{ $scan->ai_input_tokens }}/{{ $scan->ai_output_tokens }} tokens)</span>
		@endif
	</div>

	@if ($scan->error_message)
		<div style="padding:10px 12px;background:#fef3c7;border:1px solid #fde68a;border-radius:8px;margin-bottom:14px;font-size:13px">
			⚠️ {{ $scan->error_message }}
		</div>
	@endif

	{{-- Verschil met vorige scan --}}
	@if ($diff['has_prev'] && ($diff['new']->isNotEmpty() || $diff['resolved']->isNotEmpty()))
		<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
			<div style="border:1px solid #fecaca;border-radius:8px;padding:10px 12px;background:#fff5f5">
				<div style="font-weight:700;color:#991b1b;font-size:12px;text-transform:uppercase;letter-spacing:.4px">Nieuw ({{ $diff['new']->count() }})</div>
				@forelse ($diff['new'] as $f)
					<div style="font-size:13px;margin-top:4px">▲ {{ $f->check_id }} — {{ \Illuminate\Support\Str::limit($f->finding, 80) }}</div>
				@empty
					<div style="font-size:13px;color:#9aa1ad;margin-top:4px">Geen nieuwe problemen.</div>
				@endforelse
			</div>
			<div style="border:1px solid #bbf7d0;border-radius:8px;padding:10px 12px;background:#f0fdf4">
				<div style="font-weight:700;color:#166534;font-size:12px;text-transform:uppercase;letter-spacing:.4px">Opgelost ({{ $diff['resolved']->count() }})</div>
				@forelse ($diff['resolved'] as $f)
					<div style="font-size:13px;margin-top:4px">▼ {{ $f->check_id }} — {{ \Illuminate\Support\Str::limit($f->finding, 80) }}</div>
				@empty
					<div style="font-size:13px;color:#9aa1ad;margin-top:4px">Niets opgelost.</div>
				@endforelse
			</div>
		</div>
	@endif

	{{-- Bevindingen per ernst --}}
	@foreach ($groups as $key => $g)
		@if ($g['items']->isNotEmpty())
			<div style="font-weight:700;color:{{ $g['color'] }};margin:14px 0 6px;font-size:13px;text-transform:uppercase;letter-spacing:.4px">
				{{ $g['label'] }} ({{ $g['items']->count() }})
			</div>
			@foreach ($g['items'] as $f)
				@php [$bg, $fg, $lbl] = $badge($f->status); @endphp
				<div style="border:1px solid #e7e9ee;border-radius:8px;padding:10px 12px;margin-bottom:8px">
					<div style="display:flex;align-items:center;gap:8px">
						<span style="background:{{ $bg }};color:{{ $fg }};font-weight:700;font-size:11px;padding:2px 7px;border-radius:999px">{{ $lbl }}</span>
						<span style="font-weight:600">{{ $f->check_id }}</span>
						<span style="font-size:11px;color:#9aa1ad">{{ $f->source === 'ai' ? 'AI' : 'deterministisch' }}</span>
					</div>
					<div style="margin-top:6px">{{ $f->finding }}</div>
					@if ($f->suggestion)
						<div style="margin-top:4px;font-size:13px;color:#166534">💡 {{ $f->suggestion }}</div>
					@endif
					@if ($f->element)
						<div style="margin-top:4px;font-size:12px;color:#6b7280;background:#f5f6f8;border-radius:6px;padding:4px 8px;font-family:monospace;word-break:break-word">{{ \Illuminate\Support\Str::limit($f->element, 200) }}</div>
					@endif
				</div>
			@endforeach
		@endif
	@endforeach

	@if ($problems->isEmpty())
		<div style="padding:14px;text-align:center;color:#166534;background:#f0fdf4;border-radius:8px">Geen problemen gevonden. ✅</div>
	@endif

	<div style="margin-top:14px;font-size:12px;color:#9aa1ad">✓ {{ $passCount }} checks geslaagd</div>
</div>
