@extends('layouts.app')

@php
	$locale = app()->getLocale();
	$editing = $credential->exists;
	$crumb = __('Vault') . ' / ' . ($editing ? __('Bewerken') : __('Nieuwe credential'));
	$action = $editing
		? route('tools.accessguard.vault.update', ['locale' => $locale, 'id' => $credential->id])
		: route('tools.accessguard.vault.store', ['locale' => $locale]);
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

			<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Naam') }} *</label>
					<input type="text" name="name" value="{{ old('name', $credential->name) }}" class="field-input py-1.5" required placeholder="M365 Admin account">
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Type') }}</label>
					<select name="type" class="field-input py-1.5">
						@foreach (['password' => __('Wachtwoord'), 'token' => __('Token'), 'api_key' => __('API key'), 'ssh_key' => __('SSH key'), 'cert' => __('Certificaat'), 'other' => __('Overig')] as $k => $l)
							<option value="{{ $k }}" @selected(old('type', $credential->type) === $k)>{{ $l }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Gebruikersnaam') }}</label>
					<input type="text" name="username" value="{{ old('username', $credential->username) }}" class="field-input py-1.5" autocomplete="off">
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Gekoppeld systeem') }}</label>
					<select name="system_id" class="field-input py-1.5">
						<option value="">—</option>
						@foreach ($systems as $s)
							<option value="{{ $s->id }}" @selected(old('system_id', $credential->system_id) === $s->id)>{{ $s->name }}</option>
						@endforeach
					</select>
				</div>
			</div>

			<div>
				<label class="block text-xs font-semibold mb-1">{{ __('Gekoppeld item (optioneel)') }}</label>
				<select name="access_item_id" class="field-input py-1.5">
					<option value="">—</option>
					@foreach ($items as $i)
						<option value="{{ $i->id }}" @selected(old('access_item_id', $credential->access_item_id) === $i->id)>{{ $i->name }}</option>
					@endforeach
				</select>
			</div>

			<div>
				<label class="block text-xs font-semibold mb-1">
					{{ __('Secret') }}
					@if ($editing)<span class="text-xs font-normal text-[color:var(--color-ink-muted)]">({{ __('laat leeg om onveranderd te laten') }})</span>@else *@endif
				</label>
				<textarea name="secret" rows="3" class="field-input py-1.5 font-mono text-sm" {{ $editing ? '' : 'required' }} autocomplete="new-password" placeholder="{{ __('Plak hier de secret waarde') }}"></textarea>
				<p class="text-xs text-[color:var(--color-ink-soft)] mt-1">{{ __('Wordt versleuteld opgeslagen. Jij bent eigenaar en kunt decrypten.') }}</p>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Verloopdatum (optioneel)') }}</label>
					<input type="date" name="expires_at" value="{{ old('expires_at', $credential->expires_at?->format('Y-m-d')) }}" class="field-input py-1.5">
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Rotatie-interval (dagen)') }}</label>
					<input type="number" name="rotation_interval_days" value="{{ old('rotation_interval_days', $credential->rotation_interval_days) }}" min="1" max="3650" class="field-input py-1.5" placeholder="90">
				</div>
			</div>

			<div class="flex items-center gap-3 border-t border-[color:var(--color-line)] pt-4">
				<button type="submit" class="btn-accent text-sm">{{ $editing ? __('Opslaan') : __('Aanmaken') }}</button>
				<a href="{{ route('tools.accessguard.vault.index', ['locale' => $locale]) }}" class="text-sm text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">{{ __('Annuleren') }}</a>
			</div>
		</form>
	</div>
</section>

@endsection
