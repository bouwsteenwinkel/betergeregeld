@extends('layouts.app')

@section('title', __('Dashboard') . ' — ' . config('app.name'))

@php
	$locale = app()->getLocale();
	$isEn = $locale !== 'nl';

	$planBadgeClass = match ($plan->plan_key) {
		'tools_pro' => 'pill-teal',
		'tools_business' => 'pill-ink',
		default => 'pill-dark',
	};

	$planLabel = [
		'tools_free' => $isEn ? 'Free plan' : 'Free plan',
		'tools_pro' => 'Pro',
		'tools_business' => 'Business',
	][$plan->plan_key] ?? $plan->name;

	$seats = (int) ($features->raw('seats', '1'));
	$apiEnabled = $features->bool('api.enabled');
	$historyDays = $features->raw('history.retention_days', '7');
@endphp

@section('content')

{{-- ============ HERO ============ --}}
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1200px] mx-auto px-6 py-16">
		<div class="flex items-start justify-between gap-6 flex-wrap">
			<div>
				<span class="pill pill-dark mb-4">{{ __('Dashboard') }}</span>
				<h1 class="display-1 mb-3">
					{{ __('Welkom terug,') }}<br>
					<span class="accent-word">{{ $user->email }}</span>
				</h1>
			</div>
			<div class="flex items-center gap-3">
				<span class="pill {{ $planBadgeClass }}">{{ $planLabel }}</span>
				@if ($plan->plan_key === 'tools_free')
					<a href="{{ route('pricing') }}" class="btn-accent text-sm">
						{{ __('Upgrade') }}
						<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</a>
				@endif
			</div>
		</div>
	</div>
</section>

{{-- ============ MAIN GRID ============ --}}
<section class="py-12">
	@if (session('billing_message'))
		<div class="max-w-[1200px] mx-auto px-6 mb-4">
			<div class="rounded-[var(--radius-control)] border border-emerald-200 bg-emerald-50 text-emerald-900 p-4 text-sm">
				<div class="flex items-start gap-3">
					<svg class="w-5 h-5 mt-0.5 shrink-0 text-emerald-600" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 10l3 3 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
					<div>{{ session('billing_message') }}</div>
				</div>
			</div>
		</div>
	@endif
	<div class="max-w-[1200px] mx-auto px-6 grid grid-cols-1 lg:grid-cols-3 gap-5">

		{{-- PLAN CARD --}}
		<div class="card lg:col-span-1">
			<div class="flex items-center gap-2 mb-4">
				<h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ __('Je plan') }}</h2>
			</div>

			<div class="mb-4">
				<div class="flex items-baseline gap-1.5">
					<span class="text-3xl font-bold">{{ $plan->name }}</span>
				</div>
				@if ($plan->price_monthly > 0)
					<p class="text-sm text-[color:var(--color-ink-muted)]">€{{ (int) $plan->price_monthly }}/{{ __('maand') }}</p>
				@else
					<p class="text-sm text-[color:var(--color-ink-muted)]">€0 — {{ __('altijd') }}</p>
				@endif
			</div>

			@if ($subscription && $subscription->status === 'trial')
				<div class="rounded-[var(--radius-control)] bg-amber-50 border border-amber-200 p-3 mb-4">
					<p class="text-xs font-semibold text-amber-900">
						{{ __('Proefperiode actief') }}
					</p>
					@if ($subscription->trial_ends_at)
						<p class="text-xs text-amber-900/80 mt-1">
							{{ __('Loopt af op') }} {{ $subscription->trial_ends_at->format('d-m-Y') }}
						</p>
					@endif
				</div>
			@elseif ($subscription && $subscription->status === 'active' && $subscription->current_period_ends_at)
				<p class="text-xs text-[color:var(--color-ink-soft)] mb-4">
					{{ __('Volgende verlenging') }}: {{ $subscription->current_period_ends_at->format('d-m-Y') }}
				</p>
			@endif

			<ul class="space-y-1.5 text-sm text-[color:var(--color-ink-muted)] mb-5">
				<li class="flex items-center gap-2">
					<span class="w-1 h-1 rounded-full bg-[color:var(--color-ink-muted)]"></span>
					{{ $seats }} {{ $seats === 1 ? __('gebruiker') : __('gebruikers') }}
				</li>
				<li class="flex items-center gap-2">
					<span class="w-1 h-1 rounded-full bg-[color:var(--color-ink-muted)]"></span>
					{{ __('Historie') }}:
					{{ strtolower($historyDays) === 'unlimited' ? ($isEn ? 'unlimited' : 'onbeperkt') : $historyDays . ' ' . __('dagen') }}
				</li>
				<li class="flex items-center gap-2">
					<span class="w-1 h-1 rounded-full bg-[color:var(--color-ink-muted)]"></span>
					{{ __('API-toegang') }}: {{ $apiEnabled ? __('ja') : __('nee') }}
				</li>
			</ul>

			@if ($plan->plan_key !== 'tools_business')
				<a href="{{ route('pricing') }}" class="btn-dark w-full justify-center text-sm">
					@if ($plan->plan_key === 'tools_free')
						{{ __('Bekijk upgrade-opties') }}
					@else
						{{ __('Bekijk plannen') }}
					@endif
				</a>
			@endif
		</div>

		{{-- USAGE TODAY --}}
		<div class="card lg:col-span-2">
			<div class="flex items-center justify-between mb-5">
				<h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ __('Gebruik vandaag') }}</h2>
				<span class="text-xs text-[color:var(--color-ink-soft)]">{{ now()->format('d-m-Y') }}</span>
			</div>

			<div class="space-y-4">
				@foreach ($tools as $t)
					<div>
						<div class="flex items-center justify-between text-sm mb-1.5">
							<a href="/{{ $locale }}/tools/{{ $t['slug'] === 'iban' ? 'iban-check' : 'vat-check' }}" class="font-medium hover:text-[color:var(--color-accent)]">
								{{ $t['label'] }}
							</a>
							<span class="text-[color:var(--color-ink-muted)]">
								<span class="font-semibold text-[color:var(--color-ink)]">{{ $t['today'] }}</span>
								/ {{ $t['unlimited'] ? ($isEn ? 'unlimited' : 'onbeperkt') : ($t['limit'] ?? '—') }}
							</span>
						</div>
						<div class="h-2 rounded-full bg-[color:var(--color-line)] overflow-hidden">
							@if ($t['unlimited'])
								<div class="h-full bg-[color:var(--color-accent)]/30" style="width: {{ min(100, $t['today'] > 0 ? 10 + min(40, $t['today']) : 3) }}%"></div>
							@else
								<div class="h-full {{ $t['percent'] >= 90 ? 'bg-amber-500' : 'bg-[color:var(--color-accent)]' }}" style="width: {{ $t['percent'] }}%"></div>
							@endif
						</div>
					</div>
				@endforeach
			</div>
		</div>

		{{-- 30-DAY TABLE --}}
		<div class="card lg:col-span-3">
			<h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-4">{{ __('Gebruik afgelopen 30 dagen') }}</h2>
			<div class="overflow-x-auto">
				<table class="w-full text-sm">
					<thead>
						<tr class="border-b border-[color:var(--color-line)]">
							<th class="text-left py-2 pr-4 font-semibold">{{ __('Tool') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Vandaag') }}</th>
							<th class="text-right py-2 px-3 font-semibold">7 {{ __('dagen') }}</th>
							<th class="text-right py-2 px-3 font-semibold">30 {{ __('dagen') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Daglimiet') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($tools as $t)
							<tr class="border-b border-[color:var(--color-line)]/60">
								<td class="py-3 pr-4">
									<a href="/{{ $locale }}/tools/{{ $t['slug'] === 'iban' ? 'iban-check' : 'vat-check' }}" class="hover:text-[color:var(--color-accent)]">{{ $t['label'] }}</a>
								</td>
								<td class="py-3 px-3 text-right tabular-nums">{{ $t['today'] }}</td>
								<td class="py-3 px-3 text-right tabular-nums">{{ $t['week'] }}</td>
								<td class="py-3 px-3 text-right tabular-nums">{{ $t['month'] }}</td>
								<td class="py-3 px-3 text-right tabular-nums text-[color:var(--color-ink-muted)]">
									{{ $t['unlimited'] ? ($isEn ? 'unlimited' : 'onbeperkt') : ($t['limit'] ?? '—') }}
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>

		@if ($bookkeeping)
			@php
				$fmt = fn ($v) => '€' . number_format((float) $v, 2, ',', '.');
			@endphp
			<div class="card lg:col-span-3">
				<div class="flex items-center justify-between gap-4 mb-4 flex-wrap">
					<h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)]">
						{{ __('Boekhouden — snapshot') }}
					</h2>
					<a href="{{ route('tools.bookkeeping.index', ['locale' => $locale]) }}" class="text-xs text-[color:var(--color-accent)] hover:underline">
						{{ __('Naar boekhouden') }} →
					</a>
				</div>

				<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
					<a href="{{ route('tools.bookkeeping.invoices.index', ['locale' => $locale, 'status' => 'sent']) }}" class="block">
						<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Openstaand') }}</div>
						<div class="text-2xl font-bold tabular-nums">{{ $fmt($bookkeeping['outstanding']['sum']) }}</div>
						<div class="text-xs text-[color:var(--color-ink-soft)]">{{ $bookkeeping['outstanding']['count'] }} {{ __('facturen') }}</div>
					</a>
					<a href="{{ route('tools.bookkeeping.invoices.index', ['locale' => $locale, 'status' => 'sent']) }}" class="block">
						<div class="text-xs uppercase tracking-wider font-bold mb-1 {{ $bookkeeping['overdue']['count'] > 0 ? 'text-red-700' : 'text-[color:var(--color-ink-muted)]' }}">{{ __('Vervallen') }}</div>
						<div class="text-2xl font-bold tabular-nums {{ $bookkeeping['overdue']['count'] > 0 ? 'text-red-700' : '' }}">{{ $fmt($bookkeeping['overdue']['sum']) }}</div>
						<div class="text-xs text-[color:var(--color-ink-soft)]">{{ $bookkeeping['overdue']['count'] }} {{ __('facturen') }}</div>
					</a>
					<div>
						<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Deze week vervalt') }}</div>
						<div class="text-2xl font-bold tabular-nums">{{ $fmt($bookkeeping['upcoming']['sum']) }}</div>
						<div class="text-xs text-[color:var(--color-ink-soft)]">{{ $bookkeeping['upcoming']['count'] }} {{ __('facturen') }}</div>
					</div>
					<div>
						<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">
							{{ __('Deze maand') }} · {{ $fmt($bookkeeping['mtd']['result']) }}
						</div>
						<div class="text-xs text-emerald-700 tabular-nums">+ {{ $fmt($bookkeeping['mtd']['income']) }} {{ __('omzet') }}</div>
						<div class="text-xs text-red-700 tabular-nums">− {{ $fmt($bookkeeping['mtd']['expense']) }} {{ __('kosten') }}</div>
					</div>
				</div>

				@if ($bookkeeping['recent']->isNotEmpty())
					<h3 class="text-xs font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-2">{{ __('Laatst betaald') }}</h3>
					<table class="w-full text-sm">
						<tbody>
							@foreach ($bookkeeping['recent'] as $inv)
								<tr class="border-b border-[color:var(--color-line)]/60 last:border-b-0">
									<td class="py-1.5 pr-3 font-mono text-xs">
										<a href="{{ route('tools.bookkeeping.invoices.show', ['locale' => $locale, 'id' => $inv->id]) }}" class="hover:text-[color:var(--color-accent)]">
											{{ $inv->invoice_number }}
										</a>
									</td>
									<td class="py-1.5 px-3 text-[color:var(--color-ink-muted)]">{{ $inv->relation?->name ?? '—' }}</td>
									<td class="py-1.5 px-3 text-[color:var(--color-ink-soft)] tabular-nums text-xs">
										{{ $inv->paid_at?->format('d-m-Y') ?? '—' }}
									</td>
									<td class="py-1.5 pl-3 text-right tabular-nums font-medium text-emerald-700">+{{ $fmt($inv->total) }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				@endif
			</div>
		@endif

		{{-- QUICK SETTINGS --}}
		<div class="card lg:col-span-3">
			<h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-4">{{ __('Instellingen') }}</h2>
			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
				<a href="{{ route('settings.2fa') }}" class="flex items-center gap-3 p-3 rounded-[var(--radius-control)] border border-[color:var(--color-line)] hover:border-[color:var(--color-line-strong)] hover:shadow-sm transition">
					<div class="shrink-0 w-9 h-9 rounded-full bg-[color:var(--color-surface-soft,#f5f5f5)] flex items-center justify-center">
						<svg class="w-4 h-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 1l6 3v4c0 4-3 6.5-6 7-3-.5-6-3-6-7V4l6-3z" stroke-linejoin="round"/><path d="M5.5 8l2 2 3.5-3.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</div>
					<div>
						<p class="text-sm font-medium">{{ __('Twee-staps verificatie') }}</p>
						<p class="text-xs text-[color:var(--color-ink-muted)]">{{ __('Extra beveiliging met authenticator-app') }}</p>
					</div>
				</a>

				<a href="{{ route('pricing') }}" class="flex items-center gap-3 p-3 rounded-[var(--radius-control)] border border-[color:var(--color-line)] hover:border-[color:var(--color-line-strong)] hover:shadow-sm transition">
					<div class="shrink-0 w-9 h-9 rounded-full bg-[color:var(--color-surface-soft,#f5f5f5)] flex items-center justify-center">
						<svg class="w-4 h-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 6h10M3 10h10M5 2v12M11 2v12" stroke-linecap="round"/></svg>
					</div>
					<div>
						<p class="text-sm font-medium">{{ __('Plannen & facturatie') }}</p>
						<p class="text-xs text-[color:var(--color-ink-muted)]">{{ __('Prijzen bekijken en upgraden') }}</p>
					</div>
				</a>

				<form method="POST" action="{{ route('logout') }}" class="block">
					@csrf
					<button type="submit" class="w-full flex items-center gap-3 p-3 rounded-[var(--radius-control)] border border-[color:var(--color-line)] hover:border-[color:var(--color-line-strong)] hover:shadow-sm transition text-left">
						<div class="shrink-0 w-9 h-9 rounded-full bg-[color:var(--color-surface-soft,#f5f5f5)] flex items-center justify-center">
							<svg class="w-4 h-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10 12l3-4-3-4M13 8H5M9 2H3v12h6" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</div>
						<div>
							<p class="text-sm font-medium">{{ __('Uitloggen') }}</p>
							<p class="text-xs text-[color:var(--color-ink-muted)]">{{ __('Sessie beëindigen') }}</p>
						</div>
					</button>
				</form>
			</div>
		</div>

	</div>
</section>

@endsection
