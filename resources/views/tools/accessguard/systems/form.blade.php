@extends('layouts.app')

@php
	$locale = app()->getLocale();
	$editing = $system->exists;
	$crumb = $editing ? __('Systeem bewerken') : __('Nieuw systeem');
	$action = $editing
		? route('tools.accessguard.systems.update', ['locale' => $locale, 'id' => $system->id])
		: route('tools.accessguard.systems.store', ['locale' => $locale]);
@endphp

@section('title', $crumb . ' — AccessGuard')

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[700px] mx-auto px-6">
		<form method="POST" action="{{ $action }}" class="card space-y-4">
			@csrf
			@if ($editing) @method('PUT') @endif

			@if ($errors->any())
				<div class="rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3 text-sm">
					<ul class="list-disc list-inside">
						@foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach
					</ul>
				</div>
			@endif

			<div>
				<label class="block text-xs font-semibold mb-1">{{ __('Naam') }} *</label>
				<input type="text" name="name" value="{{ old('name', $system->name) }}" class="field-input py-1.5" required placeholder="Microsoft 365 / Salesforce / AWS Console …">
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Categorie') }}</label>
					<select name="category" class="field-input py-1.5">
						<option value="saas" @selected(old('category', $system->category) === 'saas')>SaaS</option>
						<option value="on_prem" @selected(old('category', $system->category) === 'on_prem')>{{ __('On-prem') }}</option>
						<option value="infra" @selected(old('category', $system->category) === 'infra')>{{ __('Infrastructuur') }}</option>
						<option value="finance" @selected(old('category', $system->category) === 'finance')>{{ __('Financieel') }}</option>
						<option value="security" @selected(old('category', $system->category) === 'security')>{{ __('Security') }}</option>
						<option value="comm" @selected(old('category', $system->category) === 'comm')>{{ __('Communicatie') }}</option>
						<option value="other" @selected(old('category', $system->category) === 'other')>{{ __('Overig') }}</option>
					</select>
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Status') }}</label>
					<label class="flex items-center gap-2 mt-2 text-sm">
						<input type="checkbox" name="is_active" value="1" @checked(old('is_active', $system->is_active))>
						{{ __('Actief (zichtbaar in matrix)') }}
					</label>
				</div>
			</div>

			<div>
				<label class="block text-xs font-semibold mb-1">{{ __('Notities') }}</label>
				<textarea name="notes" rows="3" class="field-input py-1.5">{{ old('notes', $system->notes) }}</textarea>
			</div>

			<div class="flex items-center gap-3">
				<button type="submit" class="btn-accent text-sm">{{ $editing ? __('Opslaan') : __('Aanmaken') }}</button>
				<a href="{{ route('tools.accessguard.systems.index', ['locale' => $locale]) }}" class="text-sm text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">{{ __('Annuleren') }}</a>
			</div>
		</form>
	</div>
</section>

@endsection
