<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<title>{{ $cycle->title }}, Executive summary</title>
<style>
	@page { margin: 20mm 18mm; }
	body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #0f172a; line-height: 1.55; margin: 0; }

	.cover { padding: 20mm 0 14mm 0; border-bottom: 3pt solid #ff7a18; }
	.cover-kicker { font-size: 9pt; font-weight: bold; letter-spacing: 3px; color: #ff7a18; text-transform: uppercase; }
	.cover h1 { font-size: 24pt; font-weight: bold; letter-spacing: -0.02em; margin: 8mm 0 4mm; line-height: 1.1; }
	.cover-meta { font-size: 9pt; color: rgba(15,23,42,.62); }

	h2 { font-size: 14pt; font-weight: bold; letter-spacing: -0.01em; margin: 10mm 0 3mm; color: #0f172a; page-break-after: avoid; }
	h3 { font-size: 11pt; font-weight: bold; margin: 6mm 0 2mm; color: #0f172a; }

	p { margin: 0 0 3mm 0; }
	ul, ol { margin: 2mm 0 4mm; padding-left: 7mm; }
	li { margin: 0 0 2mm; }

	.lead { font-size: 11pt; color: rgba(15,23,42,.82); line-height: 1.6; }

	table.stats { width: 100%; border-collapse: collapse; margin: 4mm 0 6mm; }
	table.stats td { padding: 4mm 3mm; text-align: center; background: #f5f7fb; border-radius: 2mm; border: 1px solid rgba(15,23,42,.08); width: 20%; vertical-align: top; }
	.stat-n { font-size: 18pt; font-weight: bold; color: #0f172a; display: block; line-height: 1; }
	.stat-label { font-size: 8pt; font-weight: bold; color: rgba(15,23,42,.60); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-top: 1.5mm; }
	.stat-accent .stat-n { color: #ff7a18; }

	table.data { width: 100%; border-collapse: collapse; margin: 3mm 0 5mm; font-size: 9.5pt; }
	table.data th { background: #f5f7fb; text-align: left; padding: 2.5mm 3mm; font-weight: bold; border-bottom: 1pt solid rgba(15,23,42,.15); font-size: 9pt; text-transform: uppercase; letter-spacing: 0.5px; color: rgba(15,23,42,.70); }
	table.data td { padding: 2.5mm 3mm; border-bottom: 1px solid rgba(15,23,42,.08); }
	table.data td.n { text-align: right; font-family: monospace; }

	.info-box { background: #fef3c7; border-left: 3pt solid #b45309; padding: 4mm 5mm; margin: 4mm 0; border-radius: 2mm; }

	.finding { margin: 4mm 0 6mm; }
	.finding-heading { font-weight: bold; font-size: 10.5pt; margin-bottom: 2mm; }
	.finding-body { color: rgba(15,23,42,.78); }

	.action-item {
		padding: 3mm 0;
		border-bottom: 1px solid rgba(15,23,42,.08);
	}
	.action-item:last-child { border-bottom: none; }
	.action-n { display: inline-block; width: 8mm; color: #ff7a18; font-weight: bold; }

	.footnote { font-size: 8pt; color: rgba(15,23,42,.50); margin-top: 8mm; padding-top: 4mm; border-top: 1px solid rgba(15,23,42,.10); }
</style>
</head>
<body>

<div class="cover">
	<div class="cover-kicker">Executive summary · Access review</div>
	<h1>{{ $summary['title'] ?? $cycle->title }}</h1>
	<div class="cover-meta">
		{{ $cycle->title }} ·
		@if ($cycle->starts_at) {{ $cycle->starts_at->format('d-m-Y') }} - @endif
		@if ($cycle->completed_at) {{ $cycle->completed_at->format('d-m-Y') }} @endif
		· Scope: {{ $cycle->scope }}
	</div>
</div>

<h2>Samenvatting</h2>
<p class="lead">{{ $summary['lead'] ?? '' }}</p>

<h2>Aantallen in één oogopslag</h2>
<table class="stats">
	<tr>
		<td><span class="stat-n">{{ $counts['total'] }}</span><span class="stat-label">Totaal</span></td>
		<td><span class="stat-n">{{ $counts['keep'] }}</span><span class="stat-label">Behouden</span></td>
		<td class="stat-accent"><span class="stat-n">{{ $counts['revoke'] }}</span><span class="stat-label">Ingetrokken</span></td>
		<td class="stat-accent"><span class="stat-n">{{ $counts['change'] }}</span><span class="stat-label">Gewijzigd</span></td>
		<td><span class="stat-n">{{ $actionCounts['open'] }}</span><span class="stat-label">Open acties</span></td>
	</tr>
</table>

@if (! empty($bySystem))
	<h2>Meest geraakte systemen</h2>
	<table class="data">
		<tr><th>Systeem</th><th>Ingetrokken</th><th>Gewijzigd</th><th>Totaal</th></tr>
		@foreach ($bySystem as $row)
			<tr>
				<td>{{ $row['system'] }}</td>
				<td class="n">{{ $row['revokes'] }}</td>
				<td class="n">{{ $row['changes'] }}</td>
				<td class="n"><strong>{{ $row['revokes'] + $row['changes'] }}</strong></td>
			</tr>
		@endforeach
	</table>
@endif

@if (! empty($summary['findings']))
	<h2>Bevindingen</h2>
	@foreach ($summary['findings'] as $f)
		<div class="finding">
			<div class="finding-heading">{{ $f['heading'] }}</div>
			<div class="finding-body">{{ $f['body'] }}</div>
		</div>
	@endforeach
@endif

@if (! empty($summary['next_actions']))
	<h2>Vervolgacties</h2>
	@foreach ($summary['next_actions'] as $i => $action)
		<div class="action-item">
			<span class="action-n">{{ sprintf('%02d', $i + 1) }}.</span>{{ $action }}
		</div>
	@endforeach
@endif

@if ($counts['undecided'] > 0)
	<div class="info-box">
		<strong>Let op:</strong> {{ $counts['undecided'] }} items zonder expliciete beslissing bij afronding.
		Deze zijn standaard als "keep" verwerkt. Overweeg voor de volgende cyclus een strenger default.
	</div>
@endif

<div class="footnote">
	AccessGuard · Beter Geregeld ICT · Gegenereerd {{ $generatedAt }}<br>
	Deze samenvatting is automatisch gegenereerd op basis van de review-cyclus-data.
</div>

</body>
</html>
