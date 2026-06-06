@extends('layouts.app')

@section('title', 'Asset toevoegen, Radar, ' . config('app.name'))

@php $crumb = __('Asset toevoegen'); $locale = app()->getLocale(); @endphp

@section('content')
@include('tools.radar._header')
@include('tools.radar._subnav')

<section class="py-6">
	<div class="max-w-[700px] mx-auto px-6 space-y-4">
		@if ($errors->any())
			<div class="card text-sm bg-red-50 border-red-200 text-red-900">
				@foreach ($errors->all() as $err)<div>{{ $err }}</div>@endforeach
			</div>
		@endif

		@if (! $canAdd)
			<div class="card bg-amber-50 border-amber-200 text-amber-900">
				<h3 class="font-bold mb-1">{{ __('Asset-limiet bereikt') }}</h3>
				<p class="text-sm">{{ __('Je :plan-plan staat :n assets toe en je hebt er nu :n. Verwijder een asset of upgrade je plan om meer toe te voegen.', ['plan' => $planName, 'n' => $max]) }}</p>
				<a href="{{ route('pricing', ['locale' => $locale]) }}" class="inline-block mt-3 px-3 py-1.5 rounded bg-amber-700 text-white text-sm font-semibold">{{ __('Plan upgraden') }}</a>
			</div>
		@else
			<div class="card">
				<h2 class="font-bold text-lg mb-4">{{ __('Nieuw asset toevoegen') }}</h2>
				<form method="POST" action="{{ route('tools.radar.assets.store', ['locale' => $locale]) }}" class="space-y-4">
					@csrf
					<div>
						<label class="block text-sm font-semibold mb-1">{{ __('Naam') }}</label>
						<input type="text" name="name" required maxlength="120" value="{{ old('name') }}"
							class="w-full px-3 py-2 rounded border border-[color:var(--color-line)] bg-white text-sm"
							placeholder="{{ __('Bijv. Hoofdwebsite') }}">
					</div>
					<div>
						<label class="block text-sm font-semibold mb-1">{{ __('URL') }}</label>
						<input type="url" name="url" required maxlength="500" value="{{ old('url') }}"
							class="w-full px-3 py-2 rounded border border-[color:var(--color-line)] bg-white text-sm"
							placeholder="https://www.voorbeeld.nl">
						<p class="text-xs text-[color:var(--color-ink-muted)] mt-1">{{ __('Volledige URL incl. https://') }}</p>
					</div>
					<div>
						<label class="block text-sm font-semibold mb-1">{{ __('Criticality') }}</label>
						<select name="criticality" required class="w-full px-3 py-2 rounded border border-[color:var(--color-line)] bg-white text-sm">
							<option value="low" @selected(old('criticality') === 'low')>{{ __('Laag') }}</option>
							<option value="medium" @selected(old('criticality', 'medium') === 'medium')>{{ __('Midden') }}</option>
							<option value="high" @selected(old('criticality') === 'high')>{{ __('Hoog') }}</option>
							<option value="critical" @selected(old('criticality') === 'critical')>{{ __('Kritiek') }}</option>
						</select>
					</div>
					<div class="flex items-center justify-between pt-2">
						<a href="{{ route('tools.radar.assets.index', ['locale' => $locale]) }}" class="text-sm text-[color:var(--color-ink-muted)] hover:underline">← {{ __('Annuleer') }}</a>
						<button type="submit" class="px-4 py-2 rounded bg-[color:var(--color-accent)] text-white text-sm font-semibold hover:opacity-90">{{ __('Toevoegen') }}</button>
					</div>
				</form>
			</div>
			<div class="text-xs text-[color:var(--color-ink-muted)] text-center">
				{{ __(':current van :max assets gebruikt op :plan-plan', ['current' => $current, 'max' => $max ?? '∞', 'plan' => $planName]) }}
			</div>
		@endif
	</div>
</section>
@endsection
