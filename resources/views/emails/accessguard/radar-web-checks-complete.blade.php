@php
	$appUrl = rtrim(config('app.url'), '/');
	$assetLink = $appUrl . '/admin/radar-assets/' . $asset->id;

	$labels = [
		'tls'     => 'TLS / certificaat',
		'cookies' => 'Cookies',
		'cmp'     => 'Consent / CMP',
	];
@endphp
<x-mail::message>
# Radar web-checks voltooid

**{{ $asset->name }}**
@if ($asset->url) `{{ $asset->url }}` @endif

Eén of meer van de TLS-, cookies- of CMP-scans liet wijzigingen zien sinds
de vorige run. Onderstaand per check een korte status.

## Per check

| Check | Status | Bevindingen | Nieuw | Opgelost |
|---|---|---|---|---|
@foreach ($perCheck as $type => $info)
| {{ $labels[$type] ?? ucfirst($type) }} | {{ ucfirst($info['scan']->status) }} | {{ $info['scan']->findings_count ?? 0 }} | {{ count($info['diff']['new'] ?? []) }} | {{ count($info['diff']['resolved'] ?? []) }} |
@endforeach

@if ($openFindings->isNotEmpty())
## Top open bevindingen

@foreach ($openFindings as $f)
- **{{ ucfirst($f->check_type) }}, {{ $f->title }}** ({{ $f->severity }}, score {{ $f->risk_score }})
  {{ \Illuminate\Support\Str::limit($f->detail, 180) }}
@endforeach
@endif

<x-mail::button :url="$assetLink">Open asset in AccessGuard Radar</x-mail::button>

<x-slot:subcopy>
Automatische melding na een Vulnerability Radar web-checks-scan (TLS +
cookies + CMP gebundeld). Asset-id `{{ $asset->id }}`.
</x-slot:subcopy>
</x-mail::message>
