@extends('layouts.app')

@php
	$locale = app()->getLocale();
	$editing = $person->exists;
	$crumb = $editing ? __('Persoon bewerken') : __('Nieuwe persoon');
	$action = $editing
		? route('tools.accessguard.people.update', ['locale' => $locale, 'id' => $person->id])
		: route('tools.accessguard.people.store', ['locale' => $locale]);
@endphp

@section('title', $crumb . ', AccessGuard')

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[900px] mx-auto px-6">
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

			<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Type') }}</label>
					<select name="type" class="field-input py-1.5">
						<option value="employee" @selected(old('type', $person->type) === 'employee')>{{ __('Medewerker') }}</option>
						<option value="contractor" @selected(old('type', $person->type) === 'contractor')>{{ __('Inhuur') }}</option>
						<option value="external" @selected(old('type', $person->type) === 'external')>{{ __('Extern') }}</option>
					</select>
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Status') }}</label>
					<select name="status" class="field-input py-1.5">
						<option value="active" @selected(old('status', $person->status) === 'active')>{{ __('Actief') }}</option>
						<option value="scheduled_in" @selected(old('status', $person->status) === 'scheduled_in')>{{ __('Start gepland') }}</option>
						<option value="scheduled_out" @selected(old('status', $person->status) === 'scheduled_out')>{{ __('Uitdienst gepland') }}</option>
						<option value="inactive" @selected(old('status', $person->status) === 'inactive')>{{ __('Inactief') }}</option>
					</select>
				</div>
				<div></div>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Voornaam') }} *</label>
					<input type="text" name="first_name" value="{{ old('first_name', $person->first_name) }}" class="field-input py-1.5" required>
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Tussenvoegsel') }}</label>
					<input type="text" name="middle_name" value="{{ old('middle_name', $person->middle_name) }}" class="field-input py-1.5">
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Achternaam') }} *</label>
					<input type="text" name="last_name" value="{{ old('last_name', $person->last_name) }}" class="field-input py-1.5" required>
				</div>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('E-mail') }}</label>
					<input type="email" name="email" value="{{ old('email', $person->email) }}" class="field-input py-1.5">
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Telefoon') }}</label>
					<input type="text" name="phone" value="{{ old('phone', $person->phone) }}" class="field-input py-1.5">
				</div>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Functie') }}</label>
					<input type="text" name="job_title" value="{{ old('job_title', $person->job_title) }}" class="field-input py-1.5">
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Afdeling') }}</label>
					<input type="text" name="department" value="{{ old('department', $person->department) }}" class="field-input py-1.5">
				</div>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Startdatum') }}</label>
					<input type="date" name="start_date" value="{{ old('start_date', $person->start_date?->format('Y-m-d')) }}" class="field-input py-1.5">
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Einddatum') }}</label>
					<input type="date" name="end_date" value="{{ old('end_date', $person->end_date?->format('Y-m-d')) }}" class="field-input py-1.5">
				</div>
			</div>

			<div>
				<label class="block text-xs font-semibold mb-1">{{ __('Notities') }}</label>
				<textarea name="notes" rows="3" class="field-input py-1.5">{{ old('notes', $person->notes) }}</textarea>
			</div>

			<div class="flex items-center gap-3">
				<button type="submit" class="btn-accent text-sm">{{ $editing ? __('Opslaan') : __('Aanmaken') }}</button>
				<a href="{{ route('tools.accessguard.people.index', ['locale' => $locale]) }}" class="text-sm text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">{{ __('Annuleren') }}</a>
			</div>
		</form>
	</div>
</section>

@endsection
