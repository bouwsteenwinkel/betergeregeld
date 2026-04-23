@php $fmt = fn ($v) => '€ ' . number_format((float) $v, 2, ',', '.'); @endphp
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

	<p>Beste {{ $relation->name ?? 'relatie' }},</p>
	<p>Hierbij de factuur voor de afgelopen periode. De factuur is als PDF bijgevoegd.</p>

	<table class="details">
		<tr><th>Factuurnummer</th><td>{{ $invoice->invoice_number }}</td></tr>
		<tr><th>Factuurdatum</th><td>{{ $invoice->issue_date->format('d-m-Y') }}</td></tr>
		@if ($invoice->due_date)
			<tr><th>Vervaldatum</th><td>{{ $invoice->due_date->format('d-m-Y') }}</td></tr>
		@endif
		@if ($invoice->reference)
			<tr><th>Uw referentie</th><td>{{ $invoice->reference }}</td></tr>
		@endif
	</table>

	<p class="total">Totaal: {{ $fmt($invoice->total) }}</p>

	@if ($settings->iban)
		<p>Gelieve het bedrag over te maken naar <strong>{{ $settings->iban }}</strong> t.n.v. {{ $settings->company_name }} met vermelding van factuurnummer <strong>{{ $invoice->invoice_number }}</strong>.</p>
	@endif

	@if ($invoice->notes)
		<p>{{ $invoice->notes }}</p>
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
