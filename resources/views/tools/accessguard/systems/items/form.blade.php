@extends('layouts.app')

@php
	$locale = app()->getLocale();
	$editing = $item->exists;
	$crumb = $system->name . ' / ' . ($editing ? __('Item bewerken') : __('Nieuw item'));
	$action = $editing
		? route('tools.accessguard.systems.items.update', ['locale' => $locale, 'systemId' => $system->id, 'id' => $item->id])
		: route('tools.accessguard.systems.items.store', ['locale' => $locale, 'systemId' => $system->id]);
@endphp

@section('title', $crumb)

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
				<input type="text" name="name" value="{{ old('name', $item->name) }}" class="field-input py-1.5" required placeholder="Admin role / Basic licence / Finance viewer…">
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Type') }}</label>
					<select name="type" class="field-input py-1.5">
						@foreach (['role' => __('Rol'), 'licence' => __('Licentie'), 'account' => __('Account'), 'key' => __('Sleutel'), 'badge' => __('Pas'), 'group' => __('Groep'), 'other' => __('Overig')] as $k => $l)
							<option value="{{ $k }}" @selected(old('type', $item->type) === $k)>{{ $l }}</option>
						@endforeach
					</select>
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Sort order') }}</label>
					<input type="number" name="sort_order" value="{{ old('sort_order', $item->sort_order ?: 100) }}" class="field-input py-1.5">
				</div>
			</div>

			<div>
				<label class="block text-xs font-semibold mb-1">{{ __('Beschrijving') }}</label>
				<textarea name="description" rows="3" class="field-input py-1.5">{{ old('description', $item->description) }}</textarea>
			</div>

			<div>
				<label class="flex items-center gap-2 text-sm">
					<input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))>
					{{ __('Actief (zichtbaar in drill-down)') }}
				</label>
			</div>

			<div class="flex items-center gap-3 border-t border-[color:var(--color-line)] pt-4">
				<button type="submit" class="btn-accent text-sm">{{ $editing ? __('Opslaan') : __('Aanmaken') }}</button>
				<a href="{{ route('tools.accessguard.systems.items.index', ['locale' => $locale, 'systemId' => $system->id]) }}" class="text-sm text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">{{ __('Annuleren') }}</a>
			</div>
		</form>
	</div>
</section>

@endsection
