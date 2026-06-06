@extends('layouts.app')

@section('title', ($rate ? __('BTW-tarief bewerken') : __('Nieuw BTW-tarief')) . ', ' . config('app.name'))

@php
	$locale = app()->getLocale();
	$isEdit = (bool) $rate;
	$action = $isEdit
		? route('tools.bookkeeping.vat-rates.update', ['locale' => $locale, 'id' => $rate->id])
		: route('tools.bookkeeping.vat-rates.store', ['locale' => $locale]);
@endphp

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[800px] mx-auto px-6 py-12">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-4 flex items-center gap-2">
			<a href="{{ route('tools.bookkeeping.vat-rates.index', ['locale' => $locale]) }}" class="hover:text-white">{{ __('BTW-tarieven') }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">{{ $isEdit ? __('Bewerken') : __('Nieuw') }}</span>
		</nav>
		<h1 class="display-1">{{ $isEdit ? __('BTW-tarief bewerken') : __('Nieuw BTW-tarief') }}</h1>
	</div>
</section>

<section class="py-10">
	<div class="max-w-[800px] mx-auto px-6">
		<form method="POST" action="{{ $action }}" class="card space-y-5">
			@csrf
			@if ($isEdit) @method('PUT') @endif

			<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
				<div>
					<label for="name" class="block text-sm font-semibold mb-2">{{ __('Naam') }}</label>
					<input id="name" name="name" type="text" maxlength="32" required
						value="{{ old('name', $rate->name ?? '') }}" placeholder="19% of verlegd"
						class="field-input">
				</div>
				<div>
					<label for="rate" class="block text-sm font-semibold mb-2">{{ __('Tarief (%)') }}</label>
					<input id="rate" name="rate" type="number" step="0.01" min="0" max="100" required
						value="{{ old('rate', $rate->rate ?? '') }}" class="field-input font-mono">
				</div>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
				<div>
					<label for="effective_from" class="block text-sm font-semibold mb-2">{{ __('Geldig vanaf') }}</label>
					<input id="effective_from" name="effective_from" type="date" required
						value="{{ old('effective_from', $rate?->effective_from?->toDateString()) }}" class="field-input">
				</div>
				<div>
					<label for="effective_to" class="block text-sm font-semibold mb-2">{{ __('Geldig tot (optioneel)') }}</label>
					<input id="effective_to" name="effective_to" type="date"
						value="{{ old('effective_to', $rate?->effective_to?->toDateString()) }}" class="field-input">
				</div>
			</div>

			<div>
				<label class="flex items-center gap-2 text-sm cursor-pointer">
					<input type="hidden" name="is_default" value="0">
					<input type="checkbox" name="is_default" value="1"
						@checked(old('is_default', $isEdit ? $rate->is_default : false))>
					<span>{{ __('Als standaard markeren in transactie-dropdown') }}</span>
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
				<button type="submit" class="btn-accent">
					{{ $isEdit ? __('Opslaan') : __('Toevoegen') }}
				</button>
				<a href="{{ route('tools.bookkeeping.vat-rates.index', ['locale' => $locale]) }}" class="btn-dark">
					{{ __('Annuleer') }}
				</a>
			</div>
		</form>
	</div>
</section>

@endsection
