@php
	$appUrl = rtrim(config('app.url'), '/');
	$assetLink = $appUrl . '/admin/radar-assets/' . $asset->id;

	$totals = [
		'critical' => $asset->findings()->where('severity', 'critical')->whereIn('status', ['new', 'reopened', 'confirmed', 'in_progress'])->count(),
		'high'     => $asset->findings()->where('severity', 'high')->whereIn('status', ['new', 'reopened', 'confirmed', 'in_progress'])->count(),
		'medium'   => $asset->findings()->where('severity', 'medium')->whereIn('status', ['new', 'reopened', 'confirmed', 'in_progress'])->count(),
		'low'      => $asset->findings()->where('severity', 'low')->whereIn('status', ['new', 'reopened', 'confirmed', 'in_progress'])->count(),
	];
@endphp
<x-mail::message>
# Radar scan voltooid

**{{ $asset->name }}**
@if ($asset->url) `{{ $asset->url }}` @endif

## Samenvatting

- **Status:** {{ ucfirst($scan->status) }}
- **Software gedetecteerd:** {{ $scan->detections_count }}
- **Bevindingen:** {{ $scan->findings_count }}
- **Gestart:** {{ $scan->started_at?->format('d-m-Y H:i:s') }}
- **Duur:** @if ($scan->started_at && $scan->finished_at) {{ round($scan->started_at->diffInMilliseconds($scan->finished_at) / 1000, 2) }}s @else — @endif

@if (array_sum($totals) > 0)
## Open bevindingen

| Severity | Aantal |
|---|---|
| Kritiek | {{ $totals['critical'] }} |
| Hoog | {{ $totals['high'] }} |
| Middel | {{ $totals['medium'] }} |
| Laag | {{ $totals['low'] }} |
@endif

@if ($topFindings->isNotEmpty())
## Top {{ $topFindings->count() }} ({{ count($topFindings) }} van {{ $totals['critical'] + $totals['high'] }} kritiek/hoog)

@foreach ($topFindings as $f)
- **{{ $f->vulnerability?->cve_id ?? 'Finding #' . $f->id }}** ({{ $f->severity }}, score {{ $f->risk_score }})@if ($f->vulnerability?->cisa_kev) — *actief misbruikt (CISA KEV)*@endif
  {{ \Illuminate\Support\Str::limit($f->vulnerability?->description ?? '', 180) }}
@endforeach
@endif

@if ($scan->status === 'error')
## Foutmelding

`{{ $scan->error_message }}`
@endif

<x-mail::button :url="$assetLink">Open asset in AccessGuard Radar</x-mail::button>

<x-slot:subcopy>
Automatische melding na een Vulnerability Radar-scan. Trigger: `{{ $scan->scan_type }}`. Asset-id `{{ $asset->id }}`, scan-id `{{ $scan->id }}`.
</x-slot:subcopy>
</x-mail::message>
