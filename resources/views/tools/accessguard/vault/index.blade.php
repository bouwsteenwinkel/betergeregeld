@extends('layouts.app')

@section('title', __('Vault') . ' — AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = __('Vault');
	$typeLabels = [
		'password' => __('Wachtwoord'),
		'token' => __('Token'),
		'api_key' => __('API key'),
		'ssh_key' => __('SSH key'),
		'cert' => __('Certificaat'),
		'other' => __('Overig'),
	];
	$grouped = $credentials->groupBy(fn ($c) => $c->system?->name ?? __('Zonder systeem'));
@endphp

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[1400px] mx-auto px-6 space-y-4">
		@if (session('status'))
			<div class="card text-sm bg-emerald-50 border-emerald-200 text-emerald-900">{{ session('status') }}</div>
		@endif

		<div class="flex items-center justify-between">
			<strong class="text-sm uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ __(':n credentials', ['n' => $credentials->count()]) }}</strong>
			<a href="{{ route('tools.accessguard.vault.create', ['locale' => $locale]) }}" class="btn-accent text-sm">{{ __('+ Nieuwe credential') }}</a>
		</div>

		@if ($credentials->isEmpty())
			<div class="card text-sm text-center text-[color:var(--color-ink-muted)] py-8">
				<p class="mb-2">{{ __('Nog geen credentials. Voeg er één toe om te beginnen.') }}</p>
				<p class="text-xs">{{ __('Secrets worden versleuteld (AES-256) opgeslagen met Laravel\'s Crypt facade. Elke decrypt-actie wordt gelogd.') }}</p>
			</div>
		@else
			@foreach ($grouped as $systemName => $items)
				<div class="card">
					<h3 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ $systemName }}</h3>
					<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
						@foreach ($items as $c)
							<a href="{{ route('tools.accessguard.vault.show', ['locale' => $locale, 'id' => $c->id]) }}"
								class="block p-3 rounded border border-[color:var(--color-line)] hover:border-[color:var(--color-accent)] hover:shadow transition">
								<div class="flex items-start gap-2 mb-1">
									<svg class="w-4 h-4 text-[color:var(--color-accent)] mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="15" r="4"/><path d="m10.85 12.15 8.15-8.15 3 3-2 2-2-2-2 2 2 2-2 2"/></svg>
									<div class="flex-1 min-w-0">
										<div class="font-semibold truncate">{{ $c->name }}</div>
										<div class="text-xs text-[color:var(--color-ink-muted)] truncate">
											{{ $typeLabels[$c->type] ?? $c->type }}
											@if ($c->username) · {{ $c->username }} @endif
											@if ($c->accessItem) · {{ $c->accessItem->name }} @endif
										</div>
									</div>
								</div>
								<div class="flex items-center gap-2 text-xs mt-2">
									@if ($c->isExpired())
										<span class="inline-flex items-center px-2 py-0.5 rounded bg-red-100 text-red-800 font-semibold">{{ __('Verlopen') }}</span>
									@elseif ($c->isRotationDue())
										<span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-semibold">{{ __('Rotatie nodig') }}</span>
									@endif
									@if ($c->expires_at)
										<span class="text-[color:var(--color-ink-muted)]">{{ __('exp') }} {{ $c->expires_at->format('d-m-Y') }}</span>
									@endif
								</div>
							</a>
						@endforeach
					</div>
				</div>
			@endforeach
		@endif
	</div>
</section>

@endsection
