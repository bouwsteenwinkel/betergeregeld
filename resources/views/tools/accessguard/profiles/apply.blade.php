@extends('layouts.app')

@php
	$locale = app()->getLocale();
	$crumb = __('Profile toepassen') . ' · ' . $profile->name;
@endphp

@section('title', $crumb . ' — AccessGuard')

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[700px] mx-auto px-6">
		@if (session('status'))
			<div class="card text-sm bg-emerald-50 border-emerald-200 text-emerald-900 mb-4">{{ session('status') }}</div>
		@endif

		<form method="POST" action="{{ route('tools.accessguard.profiles.apply', ['locale' => $locale, 'id' => $profile->id]) }}" class="card space-y-4">
			@csrf

			<div>
				<h2 class="text-lg font-bold">{{ __('Profile toepassen: :name', ['name' => $profile->name]) }}</h2>
				@if ($profile->description)
					<p class="text-sm text-[color:var(--color-ink-muted)] mt-1">{{ $profile->description }}</p>
				@endif
			</div>

			<div>
				<label class="block text-xs font-semibold mb-1">{{ __('Persoon') }} *</label>
				<select name="person_id" class="field-input py-1.5" required>
					<option value="">{{ __('Kies persoon…') }}</option>
					@foreach ($people as $p)
						<option value="{{ $p->id }}">
							{{ trim($p->first_name . ' ' . ($p->middle_name ?? '') . ' ' . $p->last_name) }}
							@if ($p->job_title) — {{ $p->job_title }} @endif
						</option>
					@endforeach
				</select>
			</div>

			<div>
				<label class="block text-xs font-semibold mb-1">{{ __('Strategie') }}</label>
				<div class="space-y-2 text-sm">
					<label class="flex items-start gap-2">
						<input type="radio" name="strategy" value="dry_run" checked class="mt-1">
						<span>
							<strong>{{ __('Dry-run') }}</strong>
							<span class="text-[color:var(--color-ink-muted)] block text-xs">{{ __('Alleen tellen — niks schrijven. Test wat er zou veranderen.') }}</span>
						</span>
					</label>
					<label class="flex items-start gap-2">
						<input type="radio" name="strategy" value="add_only" class="mt-1">
						<span>
							<strong>{{ __('Alleen onbekende cellen vullen') }}</strong>
							<span class="text-[color:var(--color-ink-muted)] block text-xs">{{ __('Veilig: bestaande beslissingen blijven staan. Ideaal voor een nieuwe medewerker.') }}</span>
						</span>
					</label>
					<label class="flex items-start gap-2">
						<input type="radio" name="strategy" value="overwrite" class="mt-1">
						<span>
							<strong>{{ __('Overschrijven') }}</strong>
							<span class="text-[color:var(--color-ink-muted)] block text-xs">{{ __('Alle cellen in het profile worden gezet, ook als er al een andere beslissing is. Pas op bij bestaande medewerkers.') }}</span>
						</span>
					</label>
				</div>
			</div>

			<div class="flex items-center gap-3 border-t border-[color:var(--color-line)] pt-4">
				<button type="submit" class="btn-accent text-sm">{{ __('Toepassen') }}</button>
				<a href="{{ route('tools.accessguard.profiles.index', ['locale' => $locale]) }}" class="text-sm text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">{{ __('Annuleren') }}</a>
			</div>
		</form>
	</div>
</section>

@endsection
