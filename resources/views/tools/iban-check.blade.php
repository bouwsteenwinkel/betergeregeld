@extends('layouts.app')

@section('title', __('IBAN naamcheck & risico-analyse') . ' — ' . config('app.name'))

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[900px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Tools</span>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">IBAN check</span>
		</nav>
		<span class="pill pill-dark mb-5">Tool · {{ __('Gratis') }}</span>
		<h1 class="display-1 mb-5">IBAN <span class="accent-word">{{ __('naamcheck') }}</span></h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			{{ __('Controleer of een IBAN geldig is en detecteer mogelijke risico\'s op basis van naam en structuur.') }}
		</p>
	</div>
</section>

<section class="py-16">
	<div class="max-w-[900px] mx-auto px-6">
		@include('tools._usage')
		<form method="POST" action="{{ route('tools.iban-check.check') }}" class="card space-y-5">
			@csrf
			<div>
				<label for="name" class="block text-sm font-semibold mb-2">{{ __('Naam rekeninghouder') }}</label>
				<input id="name" name="name" type="text" value="{{ $input['name'] }}" autocomplete="off" class="field-input">
			</div>
			<div>
				<label for="iban" class="block text-sm font-semibold mb-2">IBAN</label>
				<input id="iban" name="iban" type="text" required value="{{ $input['iban'] }}" autocomplete="off"
					placeholder="NL91ABNA0417164300" class="field-input font-mono">
			</div>
			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">
					{{ $errors->first() }}
				</div>
			@endif
			<button type="submit" class="btn-accent">
				{{ __('Controleer IBAN') }}
				<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
		</form>

		@if ($result)
			<div class="card mt-6">
				<div class="flex items-center gap-3 mb-5">
					@if ($result['valid'])
						<span class="inline-flex items-center gap-2 pill pill-teal">
							<svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 6l3 3 5-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
							{{ __('Technisch geldig') }}
						</span>
					@else
						<span class="inline-flex items-center gap-2 pill" style="background:rgba(220,38,38,0.12);color:#991b1b;border:1px solid rgba(220,38,38,0.24)">
							<svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3l6 6M9 3l-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
							{{ __('Ongeldig') }}
						</span>
					@endif
					<code class="font-mono text-sm text-[color:var(--color-ink-muted)]">{{ $result['iban_clean'] }}</code>
				</div>

				@if (count($result['risks']) > 0)
					<h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Risico-indicatoren') }}</h2>
					<ul class="space-y-2.5">
						@foreach ($result['risks'] as $risk)
							<li class="flex items-start gap-3">
								<span class="shrink-0 w-6 h-6 rounded-full bg-amber-100 text-amber-700 inline-flex items-center justify-center text-xs font-bold mt-0.5">!</span>
								<span class="text-sm text-[color:var(--color-ink)] leading-relaxed pt-0.5">{{ __('iban.' . $risk) }}</span>
							</li>
						@endforeach
					</ul>
				@elseif ($result['valid'])
					<p class="text-sm text-[color:var(--color-ink-muted)]">{{ __('Geen risico-indicatoren gevonden.') }}</p>
				@endif

				<p class="text-xs text-[color:var(--color-ink-soft)] mt-5 pt-5 border-t border-[color:var(--color-line)] leading-relaxed">
					{{ __('iban.disclaimer') }}
				</p>
			</div>
		@endif
	</div>
</section>

@endsection
