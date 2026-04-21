@extends('layouts.app')

@section('title', __('Betaling verwerken') . ' — ' . config('app.name'))

@section('content')

@if ($intent->isPending())
	{{-- No-JS auto-refresh every 2 seconds until the intent resolves --}}
	<meta http-equiv="refresh" content="2">
@endif

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[700px] mx-auto px-6 py-20 text-center">
		@if ($intent->isPaid())
			<span class="pill pill-teal mb-5">{{ __('Betaald') }}</span>
			<h1 class="display-1 mb-4">{{ __('Dankjewel!') }}</h1>
			<p class="text-lg text-[color:var(--color-on-dark-muted)] mb-8">
				{{ __(':plan is nu actief op je account.', ['plan' => $intent->plan->name]) }}
			</p>
			<a href="{{ route('dashboard', ['locale' => app()->getLocale()]) }}" class="btn-accent">
				{{ __('Naar je dashboard') }}
			</a>
		@elseif ($intent->isPending())
			<span class="pill pill-dark mb-5">
				<span class="w-1.5 h-1.5 rounded-full bg-[color:var(--color-accent)]"></span>
				{{ __('Verwerken') }}
			</span>
			<h1 class="display-1 mb-4">{{ __('Betaling wordt verwerkt…') }}</h1>
			<p class="text-lg text-[color:var(--color-on-dark-muted)] mb-8">
				{{ __('Dit kan enkele seconden duren. Deze pagina ververst automatisch.') }}
			</p>
			@if ($isFakeMode)
				<div class="rounded-[var(--radius-control)] border border-amber-300/30 bg-amber-500/10 p-4 text-sm text-amber-100 max-w-md mx-auto">
					<p class="font-semibold mb-1">{{ __('Dev-modus actief') }}</p>
					<p class="opacity-90">{{ __('MOLLIE_KEY is niet gezet — deze checkout is een simulatie en is direct na confirm gemarkeerd als betaald.') }}</p>
				</div>
			@endif
		@else
			<span class="pill" style="background:rgba(220,38,38,0.2);color:#fca5a5;border:1px solid rgba(220,38,38,0.3)">
				{{ __('Niet gelukt') }}
			</span>
			<h1 class="display-1 mb-4 mt-5">{{ __('Betaling niet afgerond') }}</h1>
			<p class="text-lg text-[color:var(--color-on-dark-muted)] mb-8">
				{{ __('Status: :status. Probeer opnieuw of kies een andere betaalmethode.', ['status' => $intent->status]) }}
			</p>
			<div class="flex gap-3 justify-center">
				<a href="{{ route('pricing', ['locale' => app()->getLocale()]) }}" class="btn-accent">
					{{ __('Terug naar prijzen') }}
				</a>
				<a href="{{ route('dashboard', ['locale' => app()->getLocale()]) }}" class="btn-ghost-light">
					{{ __('Naar dashboard') }}
				</a>
			</div>
		@endif
	</div>
</section>

@endsection
