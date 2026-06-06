@php
	$usage = request()->attributes->get('tool_usage');
	$limitHit = session('tool_limit_reached');
	$currentLocale = app()->getLocale();
@endphp

@if ($limitHit)
	<div class="rounded-[var(--radius-control)] border border-amber-200 bg-amber-50 p-4 mb-6">
		<div class="flex items-start gap-3">
			<span class="shrink-0 w-7 h-7 rounded-full bg-amber-100 text-amber-700 inline-flex items-center justify-center text-sm font-bold">!</span>
			<div class="flex-1">
				<p class="text-sm font-semibold text-amber-900">
					{{ __('Je daglimiet zit erop') }}
					@if ($limitHit['scope'] === 'anon')
						, {{ $limitHit['limit'] }} {{ __('checks per dag zonder account') }}
					@else
						, {{ $limitHit['limit'] }} {{ __('checks per dag op je huidige plan') }}
					@endif
				</p>
				<p class="text-sm text-amber-900/90 mt-1 leading-relaxed">
					@auth
						{{ __('Upgrade naar Pro voor hogere limieten en extra features.') }}
					@else
						{{ __('Maak gratis een account voor hogere limieten, of upgrade voor onbeperkt gebruik.') }}
					@endauth
				</p>
				<div class="flex gap-2 mt-3">
					@auth
						<a href="/{{ $currentLocale }}/prijzen" class="btn-accent text-sm py-2 px-3">{{ __('Bekijk plannen') }}</a>
					@else
						<a href="/{{ $currentLocale }}/register" class="btn-accent text-sm py-2 px-3">{{ __('Account aanmaken') }}</a>
						<a href="/{{ $currentLocale }}/prijzen" class="text-sm text-amber-900 hover:underline py-2">{{ __('Of bekijk de plannen') }}</a>
					@endauth
				</div>
			</div>
		</div>
	</div>
@elseif ($usage && $usage['limit'] !== null)
	<div class="text-xs text-[color:var(--color-on-dark-soft)] mb-4">
		@if ($usage['remaining'] <= 2 && $usage['remaining'] > 0)
			<span class="pill pill-dark">
				{{ __('Nog :n check(s) vandaag', ['n' => $usage['remaining']]) }}
				@guest · <a href="/{{ $currentLocale }}/register" class="underline hover:text-white">{{ __('meer met account') }}</a>@endguest
			</span>
		@elseif ($usage['remaining'] === 0)
			{{-- already redirected by middleware on POST; GET render keeps it neutral --}}
		@endif
	</div>
@endif
