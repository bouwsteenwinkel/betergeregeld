@extends('layouts.app')

@section('title', ($template ? __('Template bewerken') : __('Nieuwe template')) . ' — ' . config('app.name'))

@php
	$locale = app()->getLocale();
	$isEdit = (bool) $template;
	$action = $isEdit
		? route('tools.bookkeeping.recurring.update', ['locale' => $locale, 'id' => $template->id])
		: route('tools.bookkeeping.recurring.store', ['locale' => $locale]);
@endphp

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1100px] mx-auto px-6 py-12">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-4 flex items-center gap-2">
			<a href="{{ route('tools.bookkeeping.recurring.index', ['locale' => $locale]) }}" class="hover:text-white">{{ __('Terugkerend') }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">{{ $isEdit ? $template->title : __('Nieuw') }}</span>
		</nav>
		<h1 class="display-1">{{ $isEdit ? __('Template bewerken') : __('Nieuwe template') }}</h1>
	</div>
</section>

<section class="py-10">
	<div class="max-w-[1100px] mx-auto px-6">
		<form method="POST" action="{{ $action }}" class="card space-y-6">
			@csrf
			@if ($isEdit) @method('PUT') @endif

			<div class="grid grid-cols-1 sm:grid-cols-[2fr_1fr] gap-4">
				<div>
					<label for="title" class="block text-sm font-semibold mb-2">{{ __('Titel (voor eigen overzicht)') }}</label>
					<input id="title" name="title" type="text" maxlength="190" required
						value="{{ old('title', $template->title ?? '') }}" placeholder="{{ __('Bijv. Maandelijkse consulting Klant X') }}" class="field-input">
				</div>
				<div>
					<label for="relation_id" class="block text-sm font-semibold mb-2">{{ __('Klant') }}</label>
					<select id="relation_id" name="relation_id" required class="field-input">
						<option value="">—</option>
						@foreach ($relations as $r)
							<option value="{{ $r->id }}" @selected((int) old('relation_id', $template->relation_id ?? 0) === $r->id)>{{ $r->name }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
				<div>
					<label for="frequency" class="block text-sm font-semibold mb-2">{{ __('Frequentie') }}</label>
					<select id="frequency" name="frequency" class="field-input" required>
						<option value="monthly" @selected(old('frequency', $template->frequency ?? 'monthly') === 'monthly')>{{ __('recurring.frequency.monthly') }}</option>
					</select>
				</div>
				<div>
					<label for="day_of_month" class="block text-sm font-semibold mb-2">{{ __('Dag v/d maand') }}</label>
					<input id="day_of_month" name="day_of_month" type="number" min="1" max="28" required
						value="{{ old('day_of_month', $template->day_of_month ?? 1) }}" class="field-input font-mono">
					<p class="text-xs text-[color:var(--color-ink-soft)] mt-1">{{ __('Max 28 om verschillen per maand te vermijden.') }}</p>
				</div>
				<div>
					<label for="start_date" class="block text-sm font-semibold mb-2">{{ __('Startdatum') }}</label>
					<input id="start_date" name="start_date" type="date" required
						value="{{ old('start_date', $template?->start_date?->toDateString() ?? now()->toDateString()) }}" class="field-input">
				</div>
				<div>
					<label for="end_date" class="block text-sm font-semibold mb-2">{{ __('Einddatum (optioneel)') }}</label>
					<input id="end_date" name="end_date" type="date"
						value="{{ old('end_date', $template?->end_date?->toDateString()) }}" class="field-input">
				</div>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
				<div>
					<label for="payment_terms_days" class="block text-sm font-semibold mb-2">{{ __('Betalingstermijn (dagen)') }}</label>
					<input id="payment_terms_days" name="payment_terms_days" type="number" min="0" max="365" required
						value="{{ old('payment_terms_days', $template->payment_terms_days ?? 30) }}" class="field-input w-32 font-mono">
				</div>
				@if ($isEdit)
					<div>
						<label for="next_run_at" class="block text-sm font-semibold mb-2">{{ __('Volgende run') }}</label>
						<input id="next_run_at" name="next_run_at" type="date"
							value="{{ old('next_run_at', $template->next_run_at?->toDateString()) }}" class="field-input">
					</div>
				@endif
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
							<input type="number" name="lines[{{ $i }}][unit_price]" step="0.01" min="0" required
								value="{{ old('lines.' . $i . '.unit_price', $line['unit_price']) }}" class="field-input font-mono">
							<select name="lines[{{ $i }}][vat_rate_id]" class="field-input">
								<option value="">— {{ __('Geen BTW') }}</option>
								@foreach ($vatRates as $vr)
									<option value="{{ $vr->id }}" @selected((int) old('lines.' . $i . '.vat_rate_id', $line['vat_rate_id'] ?? 0) === $vr->id)>{{ $vr->name }}</option>
								@endforeach
							</select>
							<button type="button" class="rm-line text-red-600 hover:text-red-800 text-xl leading-none px-2 py-1">×</button>
						</div>
					@endforeach
				</div>
			</div>

			<div>
				<label for="notes" class="block text-sm font-semibold mb-2">{{ __('Opmerkingen op factuur') }}</label>
				<textarea id="notes" name="notes" rows="2" maxlength="2000"
					class="field-input">{{ old('notes', $template->notes ?? '') }}</textarea>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
				<label class="flex items-center gap-2 text-sm cursor-pointer">
					<input type="hidden" name="auto_send_email" value="0">
					<input type="checkbox" name="auto_send_email" value="1"
						@checked(old('auto_send_email', $template->auto_send_email ?? false))>
					<span>{{ __('Automatisch e-mail versturen bij aanmaken') }}</span>
				</label>
				<label class="flex items-center gap-2 text-sm cursor-pointer">
					<input type="hidden" name="is_active" value="0">
					<input type="checkbox" name="is_active" value="1"
						@checked(old('is_active', $isEdit ? $template->is_active : true))>
					<span>{{ __('Actief — scheduler pakt hem mee') }}</span>
				</label>
			</div>

			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">
					<ul class="list-disc pl-5 space-y-0.5">
						@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
					</ul>
				</div>
			@endif

			<div class="flex gap-2">
				<button type="submit" class="btn-accent">{{ $isEdit ? __('Opslaan') : __('Toevoegen') }}</button>
				<a href="{{ route('tools.bookkeeping.recurring.index', ['locale' => $locale]) }}" class="btn-dark">{{ __('Annuleer') }}</a>
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

	function nextIndex() { return container.querySelectorAll('.line-row').length; }

	addBtn.addEventListener('click', function () {
		const i = nextIndex();
		let vatOpts = '<option value="">— @json(__('Geen BTW')) —</option>';
		vatOptions.forEach(v => vatOpts += `<option value="${v.id}">${v.name}</option>`);
		const div = document.createElement('div');
		div.className = 'line-row grid grid-cols-1 sm:grid-cols-[3fr_1fr_1.5fr_1fr_auto] gap-2 items-start';
		div.innerHTML = `
			<input type="text" name="lines[${i}][description]" placeholder="@json(__('Omschrijving'))" maxlength="500" required class="field-input">
			<input type="number" name="lines[${i}][quantity]" step="0.01" min="0.01" required value="1" class="field-input font-mono">
			<input type="number" name="lines[${i}][unit_price]" step="0.01" min="0" required class="field-input font-mono">
			<select name="lines[${i}][vat_rate_id]" class="field-input">${vatOpts}</select>
			<button type="button" class="rm-line text-red-600 hover:text-red-800 text-xl leading-none px-2 py-1">×</button>
		`;
		container.appendChild(div);
	});

	container.addEventListener('click', function (e) {
		if (e.target.classList.contains('rm-line')) {
			if (container.querySelectorAll('.line-row').length > 1) {
				e.target.closest('.line-row').remove();
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
