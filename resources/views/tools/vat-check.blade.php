@extends('layouts.app')

@section('title', __('VAT Check (VIES)') . ', ' . config('app.name'))

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[900px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Tools</span>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">VAT check</span>
		</nav>
		<span class="pill pill-dark mb-5">Tool · {{ __('Gratis') }}</span>
		<h1 class="display-1 mb-5">VAT <span class="accent-word">{{ __('check') }}</span></h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			{{ __('Valideer EU BTW-nummers via VIES. Geen opslag, alleen check + resultaat.') }}
		</p>
	</div>
</section>

<section class="py-16">
	<div class="max-w-[900px] mx-auto px-6">
		@include('tools._usage')
		<form method="POST" action="{{ route('tools.vat-check.check') }}" class="card space-y-5">
			@csrf
			<div>
				<label for="country" class="block text-sm font-semibold mb-2">{{ __('Landcode') }}</label>
				<input id="country" name="country" type="text" maxlength="2" value="{{ $input['country'] }}"
					autocomplete="off" placeholder="NL" class="field-input font-mono uppercase" style="max-width:8rem">
				<p class="text-xs text-[color:var(--color-ink-soft)] mt-1.5">{{ __('2 letters (NL, BE, DE, FR, ES, …).') }}</p>
			</div>
			<div>
				<label for="vat" class="block text-sm font-semibold mb-2">{{ __('BTW-nummer') }}</label>
				<input id="vat" name="vat" type="text" required value="{{ $input['vat'] }}" autocomplete="off"
					placeholder="123456789B01 of NL123456789B01" class="field-input font-mono">
				<p class="text-xs text-[color:var(--color-ink-soft)] mt-1.5">{{ __('Je mag ook “NL123…” invullen; de landcode wordt dan automatisch gepakt.') }}</p>
			</div>
			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">
					{{ $errors->first() }}
				</div>
			@endif
			<button type="submit" class="btn-accent">
				{{ __('Controleer BTW-nummer') }}
				<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
		</form>

		@if ($result)
			<div class="card mt-6">
				@if ($result['ok'] && ($result['vies']['valid'] ?? false))
					<div class="flex items-center gap-3 mb-5">
						<span class="inline-flex items-center gap-2 pill pill-teal">
							<svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 6l3 3 5-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
							{{ __('Geldig BTW-nummer (VIES)') }}
						</span>
						<code class="font-mono text-sm text-[color:var(--color-ink-muted)]">{{ $result['input']['formatted'] }}</code>
					</div>

					<dl class="grid grid-cols-1 sm:grid-cols-[10rem_1fr] gap-x-6 gap-y-3 text-sm">
						@if ($result['vies']['name'] !== '')
							<dt class="font-semibold text-[color:var(--color-ink-muted)]">{{ __('Naam') }}</dt>
							<dd class="text-[color:var(--color-ink)]">{{ $result['vies']['name'] }}</dd>
						@endif
						@if ($result['vies']['address'] !== '')
							<dt class="font-semibold text-[color:var(--color-ink-muted)]">{{ __('Adres') }}</dt>
							<dd class="text-[color:var(--color-ink)] whitespace-pre-line">{{ $result['vies']['address'] }}</dd>
						@endif
						@if (! empty($result['vies']['requestDate']))
							<dt class="font-semibold text-[color:var(--color-ink-muted)]">{{ __('Gecontroleerd op') }}</dt>
							<dd class="text-[color:var(--color-ink)]">{{ $result['vies']['requestDate'] }}</dd>
						@endif
					</dl>
				@elseif ($result['ok'])
					<div class="flex items-center gap-3 mb-3">
						<span class="inline-flex items-center gap-2 pill" style="background:rgba(220,38,38,0.12);color:#991b1b;border:1px solid rgba(220,38,38,0.24)">
							<svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3l6 6M9 3l-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
							{{ __('Ongeldig BTW-nummer (VIES)') }}
						</span>
						<code class="font-mono text-sm text-[color:var(--color-ink-muted)]">{{ $result['input']['formatted'] }}</code>
					</div>
					<p class="text-sm text-[color:var(--color-ink-muted)]">{{ __('VIES geeft aan dat dit nummer op dit moment niet geldig is.') }}</p>
				@else
					<div class="flex items-center gap-3 mb-3">
						<span class="inline-flex items-center gap-2 pill" style="background:rgba(220,38,38,0.12);color:#991b1b;border:1px solid rgba(220,38,38,0.24)">
							{{ __('Kon niet controleren') }}
						</span>
					</div>
					<p class="text-sm text-[color:var(--color-ink)]">{{ __($result['error_key']) }}</p>
					@if (! empty($result['error_detail']))
						<p class="text-xs text-[color:var(--color-ink-soft)] mt-2 font-mono">{{ $result['error_detail'] }}</p>
					@endif
				@endif

				<p class="text-xs text-[color:var(--color-ink-soft)] mt-5 pt-5 border-t border-[color:var(--color-line)] leading-relaxed">
					{{ __('vat.disclaimer') }}
				</p>
			</div>
		@endif
	</div>
</section>

@endsection
