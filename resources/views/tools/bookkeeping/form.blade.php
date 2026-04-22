@extends('layouts.app')

@section('title', ($transaction ? __('Transactie bewerken') : __('Nieuwe transactie')) . ' — ' . config('app.name'))

@php
	$locale = app()->getLocale();
	$isEdit = (bool) $transaction;
	$t = $transaction;
	$old = fn ($key, $fallback = '') => old($key, $t ? $t->{$key} : $fallback);
	$action = $isEdit
		? route('tools.bookkeeping.update', ['locale' => $locale, 'id' => $t->id])
		: route('tools.bookkeeping.store', ['locale' => $locale]);
	$currentType = old('type', $t->type ?? $type);
@endphp

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[900px] mx-auto px-6 py-14">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-4 flex items-center gap-2">
			<a href="/{{ $locale }}/tools" class="hover:text-white">Tools</a>
			<span class="opacity-40">/</span>
			<a href="{{ route('tools.bookkeeping.index', ['locale' => $locale]) }}" class="hover:text-white">Boekhouden</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">
				{{ $isEdit ? __('Bewerken') : __('Nieuw') }}
			</span>
		</nav>
		<h1 class="display-1 mb-2">
			{{ $isEdit ? __('Transactie bewerken') : __('Nieuwe transactie') }}
		</h1>
	</div>
</section>

<section class="py-10">
	<div class="max-w-[900px] mx-auto px-6">
		<form method="POST" action="{{ $action }}" class="card space-y-5">
			@csrf
			@if ($isEdit) @method('PUT') @endif

			<div>
				<label class="block text-sm font-semibold mb-2">{{ __('Type') }}</label>
				<div class="flex gap-2">
					@foreach (['expense' => __('Kosten'), 'income' => __('Inkomsten')] as $val => $label)
						<label class="flex-1">
							<input type="radio" name="type" value="{{ $val }}" @checked($currentType === $val) class="peer sr-only">
							<span class="block text-center py-2 px-4 border border-[color:var(--color-line)] rounded-[var(--radius-control)] cursor-pointer peer-checked:border-[color:var(--color-accent)] peer-checked:bg-[color:var(--color-accent)]/5 peer-checked:font-semibold">
								{{ $label }}
							</span>
						</label>
					@endforeach
				</div>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
				<div>
					<label for="transaction_date" class="block text-sm font-semibold mb-2">{{ __('Datum') }}</label>
					<input id="transaction_date" name="transaction_date" type="date" required
						value="{{ $old('transaction_date', now()->toDateString()) }}" class="field-input">
				</div>
				<div>
					<label for="amount" class="block text-sm font-semibold mb-2">{{ __('Bedrag (€)') }}</label>
					<input id="amount" name="amount" type="number" step="0.01" min="0" required
						value="{{ $old('amount') }}" class="field-input font-mono">
				</div>
			</div>

			<div>
				<label for="description" class="block text-sm font-semibold mb-2">{{ __('Omschrijving') }}</label>
				<input id="description" name="description" type="text" maxlength="500" required
					value="{{ $old('description') }}" class="field-input">
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
				<div>
					<label for="category_id" class="block text-sm font-semibold mb-2">{{ __('Categorie') }}</label>
					<select id="category_id" name="category_id" class="field-input">
						<option value="">—</option>
						@foreach ($categories as $cat)
							<option value="{{ $cat->id }}" @selected((int) $old('category_id') === $cat->id) data-type="{{ $cat->type }}">
								{{ $cat->name }}
								@if ($cat->type === 'income') ({{ __('Inkomst') }})
								@elseif ($cat->type === 'expense') ({{ __('Kosten') }})
								@endif
							</option>
						@endforeach
					</select>
				</div>
				<div>
					<label for="counterparty" class="block text-sm font-semibold mb-2">{{ __('Wederpartij') }}</label>
					<input id="counterparty" name="counterparty" type="text" maxlength="190"
						value="{{ $old('counterparty') }}" placeholder="{{ __('klant of leverancier (optioneel)') }}" class="field-input">
				</div>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
				<div>
					<label for="vat_rate_id" class="block text-sm font-semibold mb-2">{{ __('BTW-tarief') }}</label>
					<select id="vat_rate_id" name="vat_rate_id" class="field-input">
						<option value="">{{ __('Geen BTW') }}</option>
						@foreach ($vatRates as $vr)
							<option value="{{ $vr->id }}" @selected((int) $old('vat_rate_id') === $vr->id)>
								{{ $vr->name }}
							</option>
						@endforeach
					</select>
				</div>
				<div class="flex items-center pt-6">
					<label class="flex items-center gap-2 text-sm cursor-pointer">
						<input type="hidden" name="vat_included" value="0">
						<input type="checkbox" name="vat_included" value="1"
							@checked($isEdit ? $t->vat_included : old('vat_included', true))>
						<span>{{ __('Bedrag is incl. BTW') }}</span>
					</label>
				</div>
				<div class="flex items-center pt-6">
					<label class="flex items-center gap-2 text-sm cursor-pointer">
						<input type="hidden" name="vat_deductible" value="0">
						<input type="checkbox" name="vat_deductible" value="1"
							@checked($isEdit ? $t->vat_deductible : old('vat_deductible', true))>
						<span>{{ __('BTW aftrekbaar') }}</span>
					</label>
				</div>
			</div>

			<div>
				<label for="invoice_number" class="block text-sm font-semibold mb-2">{{ __('Factuurnummer') }}</label>
				<input id="invoice_number" name="invoice_number" type="text" maxlength="64"
					value="{{ $old('invoice_number') }}" placeholder="{{ __('optioneel') }}" class="field-input font-mono">
			</div>

			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">
					<ul class="list-disc pl-5 space-y-0.5">
						@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
					</ul>
				</div>
			@endif

			<div class="flex gap-2">
				<button type="submit" class="btn-accent">
					{{ $isEdit ? __('Opslaan') : __('Toevoegen') }}
				</button>
				<a href="{{ route('tools.bookkeeping.index', ['locale' => $locale]) }}" class="btn-dark">
					{{ __('Annuleer') }}
				</a>
			</div>
		</form>
	</div>
</section>

@endsection
