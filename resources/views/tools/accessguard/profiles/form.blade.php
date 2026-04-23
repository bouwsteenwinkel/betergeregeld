@extends('layouts.app')

@php
	$locale = app()->getLocale();
	$editing = $profile->exists;
	$crumb = $editing ? __('Profile bewerken') : __('Nieuw profile');
	$action = $editing
		? route('tools.accessguard.profiles.update', ['locale' => $locale, 'id' => $profile->id])
		: route('tools.accessguard.profiles.store', ['locale' => $locale]);
	$itemsBySystem = $items->groupBy('system_id');
@endphp

@section('title', $crumb . ' — AccessGuard')

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[900px] mx-auto px-6">
		<form method="POST" action="{{ $action }}" class="card space-y-4">
			@csrf
			@if ($editing) @method('PUT') @endif

			@if ($errors->any())
				<div class="rounded border border-red-200 bg-red-50 text-red-800 p-3 text-sm">
					<ul class="list-disc list-inside">
						@foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach
					</ul>
				</div>
			@endif

			<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Naam') }} *</label>
					<input type="text" name="name" value="{{ old('name', $profile->name) }}" class="field-input py-1.5" required placeholder="Sales / Developer / Office Manager …">
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Status') }}</label>
					<label class="flex items-center gap-2 mt-2 text-sm">
						<input type="checkbox" name="is_active" value="1" @checked(old('is_active', $profile->is_active ?? true))>
						{{ __('Actief (toepasbaar)') }}
					</label>
				</div>
			</div>

			<div>
				<label class="block text-xs font-semibold mb-1">{{ __('Omschrijving') }}</label>
				<textarea name="description" rows="2" class="field-input py-1.5">{{ old('description', $profile->description) }}</textarea>
			</div>

			<div class="border-t border-[color:var(--color-line)] pt-4">
				<h3 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Items in dit profile') }}</h3>
				<p class="text-xs text-[color:var(--color-ink-muted)] mb-3">
					{{ __('Kies per systeem wat de standaard-toegang wordt bij toepassing op een persoon. Laat item leeg voor cel-niveau.') }}
				</p>

				<div id="profile-items" class="space-y-2">
					@foreach ($profileItems as $pi)
						<div class="flex items-center gap-2 profile-item-row">
							<select name="items[{{ $loop->index }}][system_id]" class="field-input py-1 text-sm flex-1">
								<option value="">{{ __('Kies systeem…') }}</option>
								@foreach ($systems as $s)
									<option value="{{ $s->id }}" @selected($pi->system_id === $s->id)>{{ $s->name }}</option>
								@endforeach
							</select>
							<select name="items[{{ $loop->index }}][access_item_id]" class="field-input py-1 text-sm flex-1">
								<option value="">— {{ __('heel systeem') }} —</option>
								@foreach ($items->where('system_id', $pi->system_id) as $i)
									<option value="{{ $i->id }}" @selected($pi->access_item_id === $i->id)>{{ $i->name }}</option>
								@endforeach
							</select>
							<select name="items[{{ $loop->index }}][default_state]" class="field-input py-1 text-sm">
								<option value="has_access" @selected($pi->default_state === 'has_access')>{{ __('has_access') }}</option>
								<option value="no_access" @selected($pi->default_state === 'no_access')>{{ __('no_access') }}</option>
								<option value="needs_review" @selected($pi->default_state === 'needs_review')>{{ __('needs_review') }}</option>
							</select>
							<button type="button" onclick="this.closest('.profile-item-row').remove()" class="text-red-600 hover:bg-red-50 text-sm px-2 rounded">✕</button>
						</div>
					@endforeach
					{{-- Always keep one empty row for adding --}}
					<div class="flex items-center gap-2 profile-item-row">
						<select name="items[999][system_id]" class="field-input py-1 text-sm flex-1">
							<option value="">{{ __('Kies systeem…') }}</option>
							@foreach ($systems as $s)
								<option value="{{ $s->id }}">{{ $s->name }}</option>
							@endforeach
						</select>
						<select name="items[999][access_item_id]" class="field-input py-1 text-sm flex-1">
							<option value="">— {{ __('heel systeem') }} —</option>
							@foreach ($items as $i)
								<option value="{{ $i->id }}">{{ $i->name }} ({{ $i->system_id }})</option>
							@endforeach
						</select>
						<select name="items[999][default_state]" class="field-input py-1 text-sm">
							<option value="has_access" selected>has_access</option>
							<option value="no_access">no_access</option>
							<option value="needs_review">needs_review</option>
						</select>
						<button type="button" onclick="this.closest('.profile-item-row').remove()" class="text-red-600 hover:bg-red-50 text-sm px-2 rounded">✕</button>
					</div>
				</div>

				<button type="button" onclick="duplicateProfileRow()" class="mt-3 text-xs px-3 py-1.5 rounded border border-[color:var(--color-line)] hover:bg-[color:var(--color-surface-soft,#fafafa)]">+ {{ __('Extra rij') }}</button>
			</div>

			<div class="flex items-center gap-3 border-t border-[color:var(--color-line)] pt-4">
				<button type="submit" class="btn-accent text-sm">{{ $editing ? __('Opslaan') : __('Aanmaken') }}</button>
				<a href="{{ route('tools.accessguard.profiles.index', ['locale' => $locale]) }}" class="text-sm text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">{{ __('Annuleren') }}</a>
			</div>
		</form>

		@if ($editing && $members->count() > 0)
			<div class="card mt-4">
				<div class="flex items-center justify-between mb-3">
					<h3 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)]">
						{{ __('Leden') }} ({{ $members->count() }})
					</h3>
					@if ($profile->external_source)
						<span class="text-xs text-[color:var(--color-ink-muted)]">
							{{ __('Gesynchroniseerd vanuit :src', ['src' => strtoupper($profile->external_source)]) }}
							@if ($profile->last_synced_at)
								· {{ $profile->last_synced_at->diffForHumans() }}
							@endif
						</span>
					@endif
				</div>
				<ul class="divide-y divide-[color:var(--color-line)]/60 text-sm">
					@foreach ($members as $m)
						@php $p = $m->person; @endphp
						@if ($p)
							<li class="flex items-center justify-between py-2">
								<div>
									<span class="font-semibold">{{ trim($p->first_name . ' ' . $p->last_name) }}</span>
									@if ($p->job_title)
										<span class="text-xs text-[color:var(--color-ink-muted)]"> — {{ $p->job_title }}</span>
									@endif
									@if ($p->status === 'inactive')
										<span class="ml-2 px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 uppercase">{{ __('Inactief') }}</span>
									@endif
								</div>
								<span class="text-[10px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wider
									{{ $m->source === 'manual' ? 'bg-slate-100 text-slate-700' : 'bg-sky-100 text-sky-800' }}">
									{{ $m->source }}
								</span>
							</li>
						@endif
					@endforeach
				</ul>
			</div>
		@endif
	</div>
</section>

@push('scripts')
<script>
let profileRowCounter = 1000;
function duplicateProfileRow() {
	const rows = document.querySelectorAll('.profile-item-row');
	const last = rows[rows.length - 1];
	const clone = last.cloneNode(true);
	const newIdx = profileRowCounter++;
	clone.querySelectorAll('[name]').forEach(el => {
		el.name = el.name.replace(/\[\d+\]/, '[' + newIdx + ']');
		if (el.tagName === 'SELECT') el.selectedIndex = 0;
	});
	last.after(clone);
}
</script>
@endpush

@endsection
