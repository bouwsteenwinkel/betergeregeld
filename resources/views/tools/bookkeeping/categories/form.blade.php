@extends('layouts.app')

@section('title', ($category ? __('Categorie bewerken') : __('Nieuwe categorie')) . ' — ' . config('app.name'))

@php
	$locale = app()->getLocale();
	$isEdit = (bool) $category;
	$action = $isEdit
		? route('tools.bookkeeping.categories.update', ['locale' => $locale, 'id' => $category->id])
		: route('tools.bookkeeping.categories.store', ['locale' => $locale]);
@endphp

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[800px] mx-auto px-6 py-12">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-4 flex items-center gap-2">
			<a href="{{ route('tools.bookkeeping.categories.index', ['locale' => $locale]) }}" class="hover:text-white">{{ __('Categorieën') }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">{{ $isEdit ? __('Bewerken') : __('Nieuw') }}</span>
		</nav>
		<h1 class="display-1">{{ $isEdit ? __('Categorie bewerken') : __('Nieuwe categorie') }}</h1>
	</div>
</section>

<section class="py-10">
	<div class="max-w-[800px] mx-auto px-6">
		<form method="POST" action="{{ $action }}" class="card space-y-5">
			@csrf
			@if ($isEdit) @method('PUT') @endif

			<div>
				<label for="name" class="block text-sm font-semibold mb-2">{{ __('Naam') }}</label>
				<input id="name" name="name" type="text" maxlength="120" required
					value="{{ old('name', $category->name ?? '') }}" class="field-input">
			</div>

			<div>
				<label for="type" class="block text-sm font-semibold mb-2">{{ __('Type') }}</label>
				<select id="type" name="type" class="field-input" required>
					@foreach (['expense', 'income', 'both'] as $val)
						<option value="{{ $val }}" @selected(old('type', $category->type ?? 'expense') === $val)>
							{{ __('cat.type.' . $val) }}
						</option>
					@endforeach
				</select>
			</div>

			<div>
				<label for="sort_order" class="block text-sm font-semibold mb-2">{{ __('Volgorde') }}</label>
				<input id="sort_order" name="sort_order" type="number" min="0" max="65535"
					value="{{ old('sort_order', $category->sort_order ?? 100) }}" class="field-input w-32">
				<p class="text-xs text-[color:var(--color-ink-soft)] mt-1.5">
					{{ __('Lagere nummers verschijnen eerst. Standaard-categorieën staan op 0–310.') }}
				</p>
			</div>

			<div>
				<label class="flex items-center gap-2 text-sm cursor-pointer">
					<input type="hidden" name="is_active" value="0">
					<input type="checkbox" name="is_active" value="1"
						@checked(old('is_active', $isEdit ? $category->is_active : true))>
					<span>{{ __('Actief (beschikbaar in dropdown bij transacties)') }}</span>
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
				<a href="{{ route('tools.bookkeeping.categories.index', ['locale' => $locale]) }}" class="btn-dark">
					{{ __('Annuleer') }}
				</a>
			</div>
		</form>
	</div>
</section>

@endsection
