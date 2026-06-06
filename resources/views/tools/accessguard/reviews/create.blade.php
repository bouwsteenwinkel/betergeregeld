@extends('layouts.app')

@section('title', __('Nieuwe cyclus') . ', AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = __('Nieuwe cyclus');
@endphp

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[700px] mx-auto px-6">
		<form method="POST" action="{{ route('tools.accessguard.reviews.store', ['locale' => $locale]) }}" class="card space-y-4">
			@csrf

			@if (session('error'))
				<div class="rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3 text-sm">{{ session('error') }}</div>
			@endif
			@if ($errors->any())
				<div class="rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3 text-sm">
					<ul class="list-disc list-inside">
						@foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach
					</ul>
				</div>
			@endif

			<div>
				<label class="block text-xs font-semibold mb-1">{{ __('Titel') }} *</label>
				<input type="text" name="title" value="{{ old('title', $defaultTitle) }}" class="field-input py-1.5" required>
			</div>

			<div>
				<label class="block text-xs font-semibold mb-1">{{ __('Scope') }}</label>
				<div class="space-y-2 text-sm">
					<label class="flex items-start gap-2">
						<input type="radio" name="scope" value="active_people" @checked(old('scope', 'active_people') === 'active_people') class="mt-1">
						<span>
							<strong>{{ __('Actieve personen') }}</strong>
							<span class="text-[color:var(--color-ink-muted)] block text-xs">{{ __('Standaard. Snapshot van huidige/aankomende/uitgaande medewerkers × actieve systemen.') }}</span>
						</span>
					</label>
					<label class="flex items-start gap-2">
						<input type="radio" name="scope" value="all" @checked(old('scope') === 'all') class="mt-1">
						<span>
							<strong>{{ __('Iedereen (incl. inactief)') }}</strong>
							<span class="text-[color:var(--color-ink-muted)] block text-xs">{{ __('Voor jaarlijkse audit waarbij je ook oude accounts wilt opruimen.') }}</span>
						</span>
					</label>
				</div>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Deadline') }}</label>
					<input type="date" name="due_at" value="{{ old('due_at') }}" class="field-input py-1.5">
				</div>
				<div></div>
			</div>

			<div>
				<label class="block text-xs font-semibold mb-1">{{ __('Notities') }}</label>
				<textarea name="notes" rows="3" class="field-input py-1.5" placeholder="{{ __('Bijv. context voor reviewers: welke criteria gelden, wat is er veranderd, etc.') }}">{{ old('notes') }}</textarea>
			</div>

			<div class="flex items-center gap-3 border-t border-[color:var(--color-line)] pt-4">
				<button type="submit" class="btn-accent text-sm">{{ __('Cyclus starten') }}</button>
				<a href="{{ route('tools.accessguard.reviews.index', ['locale' => $locale]) }}" class="text-sm text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">{{ __('Annuleren') }}</a>
			</div>
			<p class="text-xs text-[color:var(--color-ink-soft)]">
				{{ __('Bij starten wordt de matrix gesnapshot als review-items. Aanpassingen aan de matrix daarna hebben geen invloed op deze cyclus.') }}
			</p>
		</form>
	</div>
</section>

@endsection
