@extends('layouts.app')

@section('title', ($invoice ? __('Factuur bewerken') : __('Nieuwe factuur')) . ' — ' . config('app.name'))

@php
	$locale = app()->getLocale();
	$isEdit = (bool) $invoice;
	$action = $isEdit
		? route('tools.bookkeeping.invoices.update', ['locale' => $locale, 'id' => $invoice->id])
		: route('tools.bookkeeping.invoices.store', ['locale' => $locale]);
@endphp

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1100px] mx-auto px-6 py-12">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-4 flex items-center gap-2">
			<a href="{{ route('tools.bookkeeping.invoices.index', ['locale' => $locale]) }}" class="hover:text-white">{{ __('Facturen') }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">{{ $isEdit ? $invoice->invoice_number : __('Nieuw') }}</span>
		</nav>
		<h1 class="display-1">{{ $isEdit ? __('Factuur bewerken') : __('Nieuwe factuur') }}</h1>
	</div>
</section>

<section class="py-10">
	<div class="max-w-[1100px] mx-auto px-6">
		<form method="POST" action="{{ $action }}" class="card space-y-6">
			@csrf
			@if ($isEdit) @method('PUT') @endif

			<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
				<div>
					<label for="relation_id" class="block text-sm font-semibold mb-2">{{ __('Klant') }}</label>
					<select id="relation_id" name="relation_id" required class="field-input">
						<option value="">—</option>
						@foreach ($relations as $r)
							<option value="{{ $r->id }}" @selected((int) old('relation_id', $invoice->relation_id ?? 0) === $r->id)>{{ $r->name }}</option>
						@endforeach
					</select>
					@if ($relations->isEmpty())
						<p class="text-xs text-amber-800 mt-1.5">
							{{ __('Geen klanten gevonden.') }}
							<a href="{{ route('tools.bookkeeping.relations.create', ['locale' => $locale]) }}" class="underline">{{ __('Maak er eerst een aan.') }}</a>
						</p>
					@endif
				</div>
				<div>
					<label for="reference" class="block text-sm font-semibold mb-2">{{ __('Referentie') }}</label>
					<input id="reference" name="reference" type="text" maxlength="80"
						value="{{ old('reference', $invoice->reference ?? '') }}" placeholder="{{ __('PO-nummer of kenmerk (optioneel)') }}" class="field-input">
				</div>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
				<div>
					<label for="issue_date" class="block text-sm font-semibold mb-2">{{ __('Factuurdatum') }}</label>
					<input id="issue_date" name="issue_date" type="date" required
						value="{{ old('issue_date', $invoice?->issue_date?->toDateString() ?? now()->toDateString()) }}" class="field-input">
				</div>
				<div>
					<label for="due_date" class="block text-sm font-semibold mb-2">{{ __('Vervaldatum') }}</label>
					<input id="due_date" name="due_date" type="date"
						value="{{ old('due_date', $invoice?->due_date?->toDateString()) }}" class="field-input">
					<p class="text-xs text-[color:var(--color-ink-soft)] mt-1.5">
						{{ __('Leeg = geen vervaldatum. Standaard +:n dagen.', ['n' => $defaultTerms]) }}
					</p>
				</div>
				<div>
					<label for="payment_terms_days" class="block text-sm font-semibold mb-2">{{ __('Betalingstermijn (dagen)') }}</label>
					<input id="payment_terms_days" name="payment_terms_days" type="number" min="0" max="365"
						value="{{ old('payment_terms_days', $invoice->payment_terms_days ?? $defaultTerms) }}" class="field-input w-32 font-mono">
				</div>
			</div>

			<div>
				<div class="flex items-center justify-between mb-2">
					<label class="text-sm font-semibold">{{ __('Regels') }}</label>
					<button type="button" id="addLine" class="text-xs btn-dark">+ {{ __('Regel') }}</button>
				</div>
				<div id="linesContainer" class="space-y-2">
					@foreach ($lines as $i => $line)
						<div class="line-row grid grid-cols-1 sm:grid-cols-[3fr_1fr_1.5fr_1fr_auto] gap-2 items-start">
							<input type="text" name="lines[{{ $i }}][description]" placeholder="{{ __('Omschrijving') }}" maxlength="500" required
								value="{{ old('lines.' . $i . '.description', $line['description']) }}" class="field-input">
							<input type="number" name="lines[{{ $i }}][quantity]" step="0.01" min="0.01" required
								value="{{ old('lines.' . $i . '.quantity', $line['quantity']) }}" class="field-input font-mono">
							<input type="number" name="lines[{{ $i }}][unit_price]" step="0.01" min="0" required placeholder="{{ __('Stuksprijs') }}"
								value="{{ old('lines.' . $i . '.unit_price', $line['unit_price']) }}" class="field-input font-mono">
							<select name="lines[{{ $i }}][vat_rate_id]" class="field-input">
								<option value="">— {{ __('Geen BTW') }}</option>
								@foreach ($vatRates as $vr)
									<option value="{{ $vr->id }}" @selected((int) old('lines.' . $i . '.vat_rate_id', $line['vat_rate_id'] ?? 0) === $vr->id)>{{ $vr->name }}</option>
								@endforeach
							</select>
							<button type="button" class="rm-line text-red-600 hover:text-red-800 text-xl leading-none px-2 py-1" title="{{ __('Regel verwijderen') }}">×</button>
						</div>
					@endforeach
				</div>
			</div>

			<div>
				<label for="notes" class="block text-sm font-semibold mb-2">{{ __('Opmerkingen op factuur') }}</label>
				<textarea id="notes" name="notes" rows="3" maxlength="2000"
					class="field-input">{{ old('notes', $invoice->notes ?? '') }}</textarea>
			</div>

			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">
					<ul class="list-disc pl-5 space-y-0.5">
						@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
					</ul>
				</div>
			@endif

			<div class="flex gap-2">
				<button type="submit" class="btn-accent">{{ $isEdit ? __('Opslaan') : __('Concept opslaan') }}</button>
				<a href="{{ route('tools.bookkeeping.invoices.index', ['locale' => $locale]) }}" class="btn-dark">{{ __('Annuleer') }}</a>
			</div>
		</form>
	</div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
	const container = document.getElementById('linesContainer');
	const addBtn = document.getElementById('addLine');
	const vatOptions = @json($vatRates->map(fn ($v) => ['id' => $v->id, 'name' => $v->name]));

	function nextIndex() {
		return container.querySelectorAll('.line-row').length;
	}

	function newRow() {
		const i = nextIndex();
		const div = document.createElement('div');
		div.className = 'line-row grid grid-cols-1 sm:grid-cols-[3fr_1fr_1.5fr_1fr_auto] gap-2 items-start';
		let vatOpts = '<option value="">— @json(__('Geen BTW')) —</option>';
		vatOptions.forEach(v => vatOpts += `<option value="${v.id}">${v.name}</option>`);
		div.innerHTML = `
			<input type="text" name="lines[${i}][description]" placeholder="@json(__('Omschrijving'))" maxlength="500" required class="field-input">
			<input type="number" name="lines[${i}][quantity]" step="0.01" min="0.01" required value="1" class="field-input font-mono">
			<input type="number" name="lines[${i}][unit_price]" step="0.01" min="0" required placeholder="@json(__('Stuksprijs'))" class="field-input font-mono">
			<select name="lines[${i}][vat_rate_id]" class="field-input">${vatOpts}</select>
			<button type="button" class="rm-line text-red-600 hover:text-red-800 text-xl leading-none px-2 py-1">×</button>
		`;
		container.appendChild(div);
	}

	addBtn.addEventListener('click', newRow);
	container.addEventListener('click', function (e) {
		if (e.target.classList.contains('rm-line')) {
			if (container.querySelectorAll('.line-row').length > 1) {
				e.target.closest('.line-row').remove();
				// re-index names
				container.querySelectorAll('.line-row').forEach((row, i) => {
					row.querySelectorAll('input,select').forEach(el => {
						el.name = el.name.replace(/lines\[\d+\]/, `lines[${i}]`);
					});
				});
			}
		}
	});
});
</script>
@endpush

@endsection
