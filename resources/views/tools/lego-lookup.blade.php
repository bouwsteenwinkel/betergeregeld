@extends('layouts.app')

@section('title', __('LEGO Element Finder') . ' — ' . config('app.name'))

@php
	$locale = app()->getLocale();
@endphp

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[900px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<a href="{{ route('tools.index', ['locale' => $locale]) }}" class="hover:text-white">Tools</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">{{ __('LEGO Element Finder') }}</span>
		</nav>
		<span class="pill pill-dark mb-5">Tool · {{ __('Gratis') }}</span>
		<h1 class="display-1 mb-5">LEGO <span class="accent-word">{{ __('Element Finder') }}</span></h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			{{ __('Voer een Element ID in (de 6- of 7-cijferige code op een LEGO Pick-a-Brick-tegoed of doosje) en bekijk het onderdeel, de kleur en een afbeelding.') }}
		</p>
	</div>
</section>

<section class="py-16">
	<div class="max-w-[900px] mx-auto px-6">
		<form method="POST" action="{{ route('tools.lego-lookup.check', ['locale' => $locale]) }}" class="card space-y-5">
			@csrf
			<div>
				<label for="element_id" class="block text-sm font-semibold mb-2">{{ __('Element ID') }}</label>
				<div class="flex gap-3">
					<input id="element_id" name="element_id" type="text" inputmode="numeric"
						maxlength="16" value="{{ $input['element_id'] }}"
						placeholder="{{ __('Bijv. 370026') }}"
						class="field-input font-mono"
						autocomplete="off"
						autofocus>
					<button type="submit" class="btn-accent whitespace-nowrap">
						{{ __('Zoeken') }}
						<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</button>
				</div>
				<p class="text-xs text-[color:var(--color-ink-soft)] mt-2">{{ __('Niet-numerieke tekens worden genegeerd. Resultaten worden 30 dagen gecached.') }}</p>
			</div>
			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">
					{{ $errors->first() }}
				</div>
			@endif
		</form>

		@if ($result)
			<div class="card mt-6">
				@if ($result['found'])
					<div class="flex items-start gap-3 mb-5">
						<span class="inline-flex items-center gap-2 pill pill-teal">
							<svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 6l3 3 5-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
							{{ __('Gevonden') }}
						</span>
						<code class="font-mono text-sm text-[color:var(--color-ink-muted)] mt-1">{{ $result['element_id'] }}</code>
						@if ($result['from_cache'])
							<span class="text-xs text-[color:var(--color-ink-soft)] mt-1">· {{ __('uit cache') }}</span>
						@endif
					</div>

					<div class="grid grid-cols-1 md:grid-cols-[180px_1fr] gap-6">
						<div class="rounded-[var(--radius-control)] border border-[color:var(--color-line)] overflow-hidden bg-white flex items-center justify-center min-h-[160px]">
							@if ($result['img_url'])
								<img src="{{ $result['img_url'] }}" alt="{{ $result['name'] }}" class="w-full h-auto" loading="lazy">
							@else
								<span class="text-sm text-[color:var(--color-ink-soft)] p-4">{{ __('Geen afbeelding') }}</span>
							@endif
						</div>

						<div class="space-y-3">
							@if ($result['name'])
								<div>
									<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Naam') }}</div>
									<div class="text-base font-semibold">{{ $result['name'] }}</div>
								</div>
							@endif
							<div class="grid grid-cols-2 gap-4">
								<div>
									<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Part (design)') }}</div>
									<div class="font-mono text-sm">{{ $result['part_num'] ?? '—' }}</div>
								</div>
								<div>
									<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Kleur') }}</div>
									<div class="text-sm">
										{{ $result['color_name'] ?? '—' }}
										@if ($result['color_id'] !== null)
											<span class="text-xs text-[color:var(--color-ink-muted)] font-mono ml-1">#{{ $result['color_id'] }}</span>
										@endif
									</div>
								</div>
							</div>

							<div class="flex flex-wrap gap-2 pt-2">
								@if ($result['rebrickable_url'])
									<a href="{{ $result['rebrickable_url'] }}" target="_blank" rel="noopener"
										class="inline-flex items-center gap-2 text-sm font-semibold px-3 py-2 rounded-[var(--radius-control)] border border-[color:var(--color-line)] hover:bg-[color:var(--color-surface-soft,#fafafa)]">
										Rebrickable
										<svg class="w-3 h-3" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l6-6M4 3h5v5" stroke-linecap="round" stroke-linejoin="round"/></svg>
									</a>
								@endif
								<a href="{{ $result['bricklink_url'] }}" target="_blank" rel="noopener"
									class="inline-flex items-center gap-2 text-sm font-semibold px-3 py-2 rounded-[var(--radius-control)] border border-[color:var(--color-line)] hover:bg-[color:var(--color-surface-soft,#fafafa)]">
									BrickLink
									<svg class="w-3 h-3" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l6-6M4 3h5v5" stroke-linecap="round" stroke-linejoin="round"/></svg>
								</a>
							</div>
						</div>
					</div>
				@else
					<div class="flex items-start gap-3 mb-3">
						<span class="inline-flex items-center gap-2 pill" style="background:rgba(220,38,38,0.12);color:#991b1b;border:1px solid rgba(220,38,38,0.24)">
							<svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3l6 6M9 3l-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
							{{ __('Niet gevonden') }}
						</span>
						@if ($result['element_id'])
							<code class="font-mono text-sm text-[color:var(--color-ink-muted)] mt-1">{{ $result['element_id'] }}</code>
						@endif
					</div>
					<p class="text-sm text-[color:var(--color-ink-soft)]">
						{{ __('Geen match in onze database of bij Rebrickable. Probeer het zonder extra tekens — alleen het numerieke Element ID.') }}
					</p>
					@if ($result['element_id'])
						<div class="mt-4">
							<a href="{{ $result['bricklink_url'] }}" target="_blank" rel="noopener"
								class="inline-flex items-center gap-2 text-sm font-semibold px-3 py-2 rounded-[var(--radius-control)] border border-[color:var(--color-line)] hover:bg-[color:var(--color-surface-soft,#fafafa)]">
								{{ __('Zoek op BrickLink') }}
								<svg class="w-3 h-3" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l6-6M4 3h5v5" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</a>
						</div>
					@endif
				@endif
			</div>
		@endif

		<div class="card mt-6">
			<h3 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Veelgestelde vragen') }}</h3>
			<details class="border-b border-[color:var(--color-line)]/60 py-3">
				<summary class="cursor-pointer font-semibold text-sm">{{ __('Wat is een Element ID?') }}</summary>
				<p class="text-sm text-[color:var(--color-ink-soft)] mt-2 leading-relaxed">
					{{ __('Een Element ID is de unieke identificatie die LEGO geeft aan een specifieke combinatie van onderdeel (design) en kleur. Je vindt het op Pick-a-Brick-tegoeden, in de LEGO customer service en op losse tegels in grotere sets.') }}
				</p>
			</details>
			<details class="py-3">
				<summary class="cursor-pointer font-semibold text-sm">{{ __('Waar komt de data vandaan?') }}</summary>
				<p class="text-sm text-[color:var(--color-ink-soft)] mt-2 leading-relaxed">
					{{ __('Resultaten komen primair uit onze eigen cache. Bij een miss of oudere data wordt éénmalig de Rebrickable API bevraagd en het antwoord lokaal opgeslagen voor snellere vervolg-lookups.') }}
				</p>
			</details>
		</div>
	</div>
</section>

@endsection
