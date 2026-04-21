@extends('layouts.app')

@section('title', __('Postcode sanity check') . ' — ' . config('app.name'))

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[900px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Tools</span>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Postcode check</span>
		</nav>
		<span class="pill pill-dark mb-5">Tool · {{ __('Gratis') }}</span>
		<h1 class="display-1 mb-5">Postcode <span class="accent-word">{{ __('sanity check') }}</span></h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			{{ __('Controleer postcode, plaats, straat en huisnummer op formaat en opvallende invoer. Handig voor exports, fulfilment en administratie.') }}
		</p>
	</div>
</section>

<section class="py-16">
	<div class="max-w-[900px] mx-auto px-6">
		@include('tools._usage')
		<form method="POST" action="{{ route('tools.postcode-check.check') }}" class="card space-y-5">
			@csrf
			<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
				<div>
					<label for="postcode" class="block text-sm font-semibold mb-2">{{ __('Postcode') }}</label>
					<input id="postcode" name="postcode" type="text" maxlength="16" value="{{ $input['postcode'] }}" autocomplete="postal-code"
						placeholder="1234 AB / 1000 / 10115" class="field-input font-mono uppercase">
					<p class="text-xs text-[color:var(--color-ink-soft)] mt-1.5">{{ __('Spaties worden genegeerd. Land wordt heuristisch gedetecteerd (NL/BE/DE).') }}</p>
				</div>
				<div>
					<label for="city" class="block text-sm font-semibold mb-2">{{ __('Plaats') }}</label>
					<input id="city" name="city" type="text" maxlength="120" value="{{ $input['city'] }}" autocomplete="address-level2"
						placeholder="Bussum" class="field-input">
				</div>
				<div>
					<label for="street" class="block text-sm font-semibold mb-2">{{ __('Straat') }}</label>
					<input id="street" name="street" type="text" maxlength="160" value="{{ $input['street'] }}" autocomplete="address-line1"
						placeholder="Stationsweg" class="field-input">
				</div>
				<div>
					<label for="house_number" class="block text-sm font-semibold mb-2">{{ __('Huisnummer') }}</label>
					<input id="house_number" name="house_number" type="text" maxlength="16" value="{{ $input['house_number'] }}" autocomplete="address-line2"
						placeholder="12A" class="field-input">
				</div>
			</div>
			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">
					{{ $errors->first() }}
				</div>
			@endif
			<button type="submit" class="btn-accent">
				{{ __('Controleren') }}
				<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
		</form>

		@if ($result)
			<div class="card mt-6">
				<div class="flex items-center gap-3 mb-5">
					@if ($result['valid'])
						<span class="inline-flex items-center gap-2 pill pill-teal">
							<svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 6l3 3 5-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
							{{ __('Geen fouten') }}
						</span>
					@else
						<span class="inline-flex items-center gap-2 pill" style="background:rgba(220,38,38,0.12);color:#991b1b;border:1px solid rgba(220,38,38,0.24)">
							<svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3l6 6M9 3l-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
							{{ __('Fouten gevonden') }}
						</span>
					@endif
					@if ($result['country'] !== 'UNKNOWN')
						<code class="font-mono text-sm text-[color:var(--color-ink-muted)]">{{ $result['country'] }}</code>
					@endif
					@if ($result['normalized']['postcode'])
						<code class="font-mono text-sm text-[color:var(--color-ink-muted)]">{{ $result['normalized']['postcode'] }}</code>
					@endif
				</div>

				@foreach (['errors' => ['postcode.label_errors', 'bg-red-50 border-red-200 text-red-800', 'text-red-700'], 'warnings' => ['postcode.label_warnings', 'bg-amber-50 border-amber-200 text-amber-900', 'text-amber-700'], 'info' => ['postcode.label_info', 'bg-slate-50 border-slate-200 text-slate-800', 'text-slate-600']] as $bucket => [$labelKey, $boxClass, $iconClass])
					@if (count($result[$bucket]) > 0)
						<div class="rounded-[var(--radius-control)] border {{ $boxClass }} p-3 mb-3">
							<h3 class="text-xs font-bold uppercase tracking-wider mb-2">{{ __($labelKey) }}</h3>
							<ul class="space-y-1.5 text-sm">
								@foreach ($result[$bucket] as $item)
									<li class="flex items-start gap-2">
										<span class="shrink-0 {{ $iconClass }} mt-0.5">•</span>
										<span>{{ __($item) }}</span>
									</li>
								@endforeach
							</ul>
						</div>
					@endif
				@endforeach

				<p class="text-xs text-[color:var(--color-ink-soft)] mt-5 pt-5 border-t border-[color:var(--color-line)] leading-relaxed">
					{{ __('postcode.disclaimer') }}
				</p>
			</div>
		@endif
	</div>
</section>

@endsection
