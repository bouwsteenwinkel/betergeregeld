@php
	$fmt = fn ($v) => '€ ' . number_format((float) $v, 2, ',', '.');
	$greeting = match ($tone) {
		'friendly' => 'Beste ' . ($relation->name ?? 'relatie') . ',',
		'neutral' => 'Geachte ' . ($relation->name ?? 'relatie') . ',',
		'firm' => 'Geachte ' . ($relation->name ?? 'relatie') . ',',
		'final' => 'Geachte ' . ($relation->name ?? 'relatie') . ',',
		default => 'Beste ' . ($relation->name ?? 'relatie') . ',',
	};
	$opener = match ($kind) {
		'pre_due' => 'Dit is een vriendelijke herinnering dat onderstaande factuur binnen enkele dagen vervalt. Indien deze al voldaan is, dank u wel — dan kunt u deze mail negeren.',
		'due' => 'Onderstaande factuur vervalt vandaag. Indien deze al voldaan is, dank u wel.',
		'overdue_7' => 'Onderstaande factuur is inmiddels vervallen. Wilt u de betaling zo spoedig mogelijk voldoen of ons laten weten wanneer we die kunnen verwachten?',
		'overdue_21' => 'Onderstaande factuur is inmiddels drie weken vervallen. Dit is een laatste herinnering — blijft betaling uit, dan dragen we de vordering verder over.',
		default => 'Zie onderstaande factuur.',
	};
@endphp
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<title>Factuur {{ $invoice->invoice_number }}</title>
<style>
	body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color: #111; max-width: 600px; margin: 0 auto; padding: 24px; line-height: 1.6; }
	h1 { font-size: 20px; margin: 0 0 16px; }
	table.details { width: 100%; border-collapse: collapse; margin: 16px 0; }
	table.details th, table.details td { padding: 8px 0; border-bottom: 1px solid #eee; text-align: left; font-size: 14px; vertical-align: top; }
	table.details th { color: #666; font-weight: 600; width: 40%; }
	.total { font-size: 18px; font-weight: 700; margin: 12px 0; }
	.footer { font-size: 12px; color: #666; margin-top: 24px; border-top: 1px solid #eee; padding-top: 16px; }
</style>
</head>
<body>
	<h1>Factuur {{ $invoice->invoice_number }}</h1>

	<p>{{ $greeting }}</p>
	<p>{{ $opener }}</p>

	<table class="details">
		<tr><th>Factuurnummer</th><td>{{ $invoice->invoice_number }}</td></tr>
		<tr><th>Factuurdatum</th><td>{{ $invoice->issue_date->format('d-m-Y') }}</td></tr>
		@if ($invoice->due_date)
			<tr><th>Vervaldatum</th><td>{{ $invoice->due_date->format('d-m-Y') }}</td></tr>
		@endif
		@if ($invoice->reference)
			<tr><th>Uw referentie</th><td>{{ $invoice->reference }}</td></tr>
		@endif
		<tr><th>Subtotaal</th><td>{{ $fmt($invoice->subtotal) }}</td></tr>
		<tr><th>BTW</th><td>{{ $fmt($invoice->vat_amount) }}</td></tr>
	</table>

	<p class="total">Openstaand bedrag: {{ $fmt($invoice->total) }}</p>

	@if ($settings->iban)
		<p>Gelieve het bedrag over te maken naar <strong>{{ $settings->iban }}</strong> t.n.v. {{ $settings->company_name }} met vermelding van factuurnummer <strong>{{ $invoice->invoice_number }}</strong>.</p>
	@endif

	@if ($settings->invoice_footer)
		<p>{{ $settings->invoice_footer }}</p>
	@endif

	<p>Met vriendelijke groet,<br>{{ $settings->company_name ?? config('app.name') }}</p>

	<div class="footer">
		@if ($settings->company_name)
			{{ $settings->company_name }}
			@if ($settings->vat_number) · BTW {{ $settings->vat_number }} @endif
			@if ($settings->kvk_number) · KvK {{ $settings->kvk_number }} @endif
			<br>
		@endif
		@if ($settings->email) {{ $settings->email }} @endif
		@if ($settings->phone) · {{ $settings->phone }} @endif
	</div>
</body>
</html>
