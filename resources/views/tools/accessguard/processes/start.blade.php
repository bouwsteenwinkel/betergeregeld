@extends('layouts.app')

@section('title', __('Nieuw proces') . ', AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = __('Nieuw proces');
@endphp

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[700px] mx-auto px-6">
		<form method="POST" action="{{ route('tools.accessguard.processes.store', ['locale' => $locale]) }}" class="card space-y-4">
			@csrf

			@if ($errors->any())
				<div class="rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3 text-sm">
					<ul class="list-disc list-inside">
						@foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach
					</ul>
				</div>
			@endif

			<div>
				<label class="block text-xs font-semibold mb-1">{{ __('Type') }}</label>
				<div class="flex gap-3 text-sm">
					<label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="kind" value="onboarding" @checked($kind === 'onboarding') onchange="document.getElementById('kind-hint').textContent = '{{ __('Voor een nieuwe medewerker: checklist voor IT-inrichting, intake, toegang.') }}'">{{ __('Onboarding') }}</label>
					<label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="kind" value="offboarding" @checked($kind === 'offboarding') onchange="document.getElementById('kind-hint').textContent = '{{ __('Voor een vertrekkende medewerker: checklist + automatische revoke-acties voor alle has_access cellen.') }}'">{{ __('Offboarding') }}</label>
				</div>
				<p id="kind-hint" class="text-xs text-[color:var(--color-ink-soft)] mt-1">
					{{ $kind === 'onboarding' ? __('Voor een nieuwe medewerker: checklist voor IT-inrichting, intake, toegang.') : __('Voor een vertrekkende medewerker: checklist + automatische revoke-acties voor alle has_access cellen.') }}
				</p>
			</div>

			<div>
				<label class="block text-xs font-semibold mb-1">{{ __('Persoon') }} *</label>
				<select name="person_id" class="field-input py-1.5" required>
					<option value="">{{ __('Kies persoon…') }}</option>
					@foreach ($people as $p)
						<option value="{{ $p->id }}" @selected($preselectPerson === $p->id)>
							{{ $p->full_name }}
							@if ($p->status !== 'active') ({{ __(ucfirst(str_replace('_', ' ', $p->status))) }}) @endif
						</option>
					@endforeach
				</select>
			</div>

			<div>
				<label class="block text-xs font-semibold mb-1">{{ __('Template') }}</label>
				<select name="template_id" class="field-input py-1.5">
					<option value="">{{ __('Gebruik standaard-template') }}</option>
					@foreach ($templates as $t)
						<option value="{{ $t->id }}" data-kind="{{ $t->kind }}">
							{{ $t->name }} ({{ $t->kind }}){{ $t->is_default ? ', ' . __('standaard') : '' }}
						</option>
					@endforeach
				</select>
				<p class="text-xs text-[color:var(--color-ink-soft)] mt-1">{{ __('Bij leeg wordt de tenant-default template gebruikt.') }}</p>
			</div>

			<div>
				<label class="block text-xs font-semibold mb-1">{{ __('Deadline') }}</label>
				<input type="date" name="due_at" value="{{ old('due_at') }}" class="field-input py-1.5">
			</div>

			<div>
				<label class="block text-xs font-semibold mb-1">{{ __('Notities') }}</label>
				<textarea name="notes" rows="2" class="field-input py-1.5">{{ old('notes') }}</textarea>
			</div>

			<div class="flex items-center gap-3 border-t border-[color:var(--color-line)] pt-4">
				<button type="submit" class="btn-accent text-sm">{{ __('Starten') }}</button>
				<a href="{{ route('tools.accessguard.processes.index', ['locale' => $locale]) }}" class="text-sm text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">{{ __('Annuleren') }}</a>
			</div>
		</form>
	</div>
</section>

@endsection
