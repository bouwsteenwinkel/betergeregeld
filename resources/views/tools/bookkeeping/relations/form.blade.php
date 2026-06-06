@extends('layouts.app')

@section('title', ($relation ? __('Relatie bewerken') : __('Nieuwe relatie')) . ', ' . config('app.name'))

@php
	$locale = app()->getLocale();
	$isEdit = (bool) $relation;
	$r = $relation;
	$old = fn ($key, $fallback = '') => old($key, $r ? $r->{$key} : $fallback);
	$action = $isEdit
		? route('tools.bookkeeping.relations.update', ['locale' => $locale, 'id' => $r->id])
		: route('tools.bookkeeping.relations.store', ['locale' => $locale]);
@endphp

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[900px] mx-auto px-6 py-12">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-4 flex items-center gap-2">
			<a href="{{ route('tools.bookkeeping.relations.index', ['locale' => $locale]) }}" class="hover:text-white">{{ __('Relaties') }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">{{ $isEdit ? __('Bewerken') : __('Nieuw') }}</span>
		</nav>
		<h1 class="display-1">{{ $isEdit ? __('Relatie bewerken') : __('Nieuwe relatie') }}</h1>
	</div>
</section>

<section class="py-10">
	<div class="max-w-[900px] mx-auto px-6">
		<form method="POST" action="{{ $action }}" class="card space-y-5">
			@csrf
			@if ($isEdit) @method('PUT') @endif

			<div class="grid grid-cols-1 sm:grid-cols-[2fr_1fr] gap-4">
				<div>
					<label for="name" class="block text-sm font-semibold mb-2">{{ __('Naam') }}</label>
					<input id="name" name="name" type="text" maxlength="190" required
						value="{{ $old('name') }}" class="field-input">
				</div>
				<div>
					<label for="type" class="block text-sm font-semibold mb-2">{{ __('Type') }}</label>
					<select id="type" name="type" class="field-input" required>
						@foreach (['client', 'supplier', 'both'] as $val)
							<option value="{{ $val }}" @selected($old('type', 'both') === $val)>
								{{ __('rel.type.' . $val) }}
							</option>
						@endforeach
					</select>
				</div>
			</div>

			<div>
				<h3 class="text-xs font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Contact') }}</h3>
				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div>
						<label for="email" class="block text-sm font-semibold mb-2">{{ __('E-mail') }}</label>
						<input id="email" name="email" type="email" maxlength="190" value="{{ $old('email') }}" class="field-input">
					</div>
					<div>
						<label for="phone" class="block text-sm font-semibold mb-2">{{ __('Telefoon') }}</label>
						<input id="phone" name="phone" type="text" maxlength="50" value="{{ $old('phone') }}" class="field-input">
					</div>
				</div>
			</div>

			<div>
				<h3 class="text-xs font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Adres') }}</h3>
				<div class="grid grid-cols-1 sm:grid-cols-[2fr_1fr] gap-4">
					<div>
						<label for="address" class="block text-sm font-semibold mb-2">{{ __('Straat + huisnummer') }}</label>
						<input id="address" name="address" type="text" maxlength="190" value="{{ $old('address') }}" class="field-input">
					</div>
					<div>
						<label for="postal_code" class="block text-sm font-semibold mb-2">{{ __('Postcode') }}</label>
						<input id="postal_code" name="postal_code" type="text" maxlength="16" value="{{ $old('postal_code') }}" class="field-input">
					</div>
				</div>
				<div class="grid grid-cols-1 sm:grid-cols-[2fr_1fr] gap-4 mt-4">
					<div>
						<label for="city" class="block text-sm font-semibold mb-2">{{ __('Plaats') }}</label>
						<input id="city" name="city" type="text" maxlength="120" value="{{ $old('city') }}" class="field-input">
					</div>
					<div>
						<label for="country" class="block text-sm font-semibold mb-2">{{ __('Land (ISO-2)') }}</label>
						<input id="country" name="country" type="text" maxlength="2" value="{{ $old('country', 'NL') }}" class="field-input font-mono uppercase">
					</div>
				</div>
			</div>

			<div>
				<h3 class="text-xs font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Zakelijk') }}</h3>
				<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
					<div>
						<label for="kvk_number" class="block text-sm font-semibold mb-2">{{ __('KvK-nummer') }}</label>
						<input id="kvk_number" name="kvk_number" type="text" maxlength="20" value="{{ $old('kvk_number') }}" class="field-input font-mono">
					</div>
					<div>
						<label for="vat_number" class="block text-sm font-semibold mb-2">{{ __('BTW-nummer') }}</label>
						<input id="vat_number" name="vat_number" type="text" maxlength="20" value="{{ $old('vat_number') }}" class="field-input font-mono">
					</div>
					<div>
						<label for="iban" class="block text-sm font-semibold mb-2">IBAN</label>
						<input id="iban" name="iban" type="text" maxlength="34" value="{{ $old('iban') }}" class="field-input font-mono">
					</div>
				</div>
			</div>

			<div>
				<label for="notes" class="block text-sm font-semibold mb-2">{{ __('Notities') }}</label>
				<textarea id="notes" name="notes" rows="3" maxlength="2000" class="field-input">{{ $old('notes') }}</textarea>
			</div>

			<div>
				<label class="flex items-center gap-2 text-sm cursor-pointer">
					<input type="hidden" name="is_active" value="0">
					<input type="checkbox" name="is_active" value="1"
						@checked(old('is_active', $isEdit ? $r->is_active : true))>
					<span>{{ __('Actief (beschikbaar in transactieformulier)') }}</span>
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
				<a href="{{ route('tools.bookkeeping.relations.index', ['locale' => $locale]) }}" class="btn-dark">
					{{ __('Annuleer') }}
				</a>
			</div>
		</form>
	</div>
</section>

@endsection
