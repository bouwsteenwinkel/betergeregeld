@extends('layouts.app')

@section('title', __('Verzendtarieven') . ' — ' . config('app.name'))

@php
	$locale = app()->getLocale();
	$fmt = fn ($v) => '€' . number_format((float) $v, 2, ',', '.');
	$fmtWeight = function (int $from, int $to) {
		$fmtG = fn (int $g) => $g >= 1000
			? rtrim(rtrim(number_format($g / 1000, 2, ',', '.'), '0'), ',') . ' kg'
			: $g . ' g';
		return $fmtG($from) . ' – ' . $fmtG($to);
	};
@endphp

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1100px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<a href="{{ route('tools.index', ['locale' => $locale]) }}" class="hover:text-white">Tools</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">{{ __('Verzendtarieven') }}</span>
		</nav>
		<span class="pill pill-dark mb-5">Tool · {{ __('Gratis') }}</span>
		<h1 class="display-1 mb-5">{{ __('Verzend') }}<span class="accent-word">{{ __('tarieven') }}</span></h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			{{ __('Vergelijk tarieven van PostNL en DHL per zone en gewicht. Bereken snel de goedkoopste match, incl. eventuele handling.') }}
		</p>
	</div>
</section>

<section class="py-16">
	<div class="max-w-[1100px] mx-auto px-6 space-y-6">
		<form method="GET" action="{{ route('tools.shipping-rates', ['locale' => $locale]) }}" class="card">
			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
				<div>
					<label for="carrier" class="block text-sm font-semibold mb-2">{{ __('Carrier') }}</label>
					<select id="carrier" name="carrier" class="field-input">
						<option value="">{{ __('Alles') }}</option>
						@foreach ($filters['carriers'] as $c)
							<option value="{{ $c }}" @selected($input['carrier'] === $c)>{{ strtoupper($c) }}</option>
						@endforeach
					</select>
				</div>
				<div>
					<label for="service" class="block text-sm font-semibold mb-2">{{ __('Service') }}</label>
					<select id="service" name="service" class="field-input">
						<option value="">{{ __('Alles') }}</option>
						@foreach ($filters['services'] as $s)
							<option value="{{ $s }}" @selected($input['service'] === $s)>{{ $s }}</option>
						@endforeach
					</select>
				</div>
				<div>
					<label for="zone" class="block text-sm font-semibold mb-2">{{ __('Zone') }}</label>
					<select id="zone" name="zone" class="field-input">
						<option value="">{{ __('Alles') }}</option>
						@foreach ($filters['zones'] as $z)
							<option value="{{ $z }}" @selected($input['zone'] === $z)>{{ $z }}</option>
						@endforeach
					</select>
				</div>
				<div>
					<label for="weight_g" class="block text-sm font-semibold mb-2">{{ __('Gewicht (gram)') }}</label>
					<input id="weight_g" name="weight_g" type="number" min="1" step="1" placeholder="500"
						value="{{ $input['weight_g'] }}" class="field-input">
					<p class="text-xs text-[color:var(--color-ink-soft)] mt-1.5">{{ __('Tip: 0,5 kg = 500 gram') }}</p>
				</div>
			</div>
			<div class="flex items-center gap-3 mt-5">
				<button type="submit" class="btn-accent">
					{{ __('Toon tarieven') }}
					<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
				@if ($input['carrier'] !== '' || $input['zone'] !== '' || $input['service'] !== '' || $input['weight_g'])
					<a href="{{ route('tools.shipping-rates', ['locale' => $locale]) }}" class="text-sm font-semibold text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">{{ __('Reset') }}</a>
				@endif
			</div>
		</form>

		@if ($best)
			<div class="card" style="border-color:rgba(16,185,129,0.35);background:rgba(16,185,129,0.05)">
				<div class="flex items-start gap-3 mb-4">
					<span class="inline-flex items-center gap-2 pill pill-teal">
						<svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 6l3 3 5-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
						{{ __('Goedkoopste match') }}
					</span>
					<span class="text-xs text-[color:var(--color-ink-muted)] mt-1">
						{{ __('voor :w gram', ['w' => number_format((int) $input['weight_g'], 0, ',', '.')]) }}
					</span>
				</div>
				<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
					<div>
						<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Carrier / service') }}</div>
						<div class="text-sm font-semibold">{{ strtoupper($best->carrier) }}</div>
						<div class="text-sm text-[color:var(--color-ink-soft)]">{{ $best->service }}</div>
						<div class="text-xs text-[color:var(--color-ink-muted)] mt-1">{{ __('Zone') }}: {{ $best->zone }} · {{ $fmtWeight($best->weight_from_g, $best->weight_to_g) }}</div>
					</div>
					<div>
						<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Tarief') }}</div>
						<div class="text-sm tabular-nums">{{ $fmt($best->price_ex) }} + {{ $fmt($best->handling_ex) }} {{ __('handling') }}</div>
					</div>
					<div>
						<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Totaal excl. btw') }}</div>
						<div class="text-2xl font-bold tabular-nums text-emerald-700">{{ $fmt($best->total_ex) }}</div>
					</div>
				</div>
			</div>
		@elseif ($input['weight_g'])
			<div class="card" style="border-color:rgba(220,38,38,0.35);background:rgba(220,38,38,0.04)">
				<p class="text-sm text-red-800">{{ __('Geen match gevonden voor deze selectie en dit gewicht.') }}</p>
			</div>
		@endif

		<div class="card">
			<div class="flex items-center justify-between mb-4">
				<h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ __('Overzicht') }}</h2>
				<span class="text-xs text-[color:var(--color-ink-soft)]">{{ __(':n tarieven', ['n' => $rates->count()]) }} · {{ __('excl. btw') }}</span>
			</div>
			@if ($rates->isEmpty())
				<p class="text-sm text-[color:var(--color-ink-muted)] py-6 text-center">{{ __('Geen tarieven gevonden voor deze filters.') }}</p>
			@else
				<div class="overflow-x-auto">
					<table class="w-full text-sm">
						<thead class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] border-b border-[color:var(--color-line)]">
							<tr>
								<th class="text-left py-2 pr-3 font-semibold">{{ __('Carrier') }}</th>
								<th class="text-left py-2 pr-3 font-semibold">{{ __('Service') }}</th>
								<th class="text-left py-2 pr-3 font-semibold">{{ __('Zone') }}</th>
								<th class="text-left py-2 pr-3 font-semibold">{{ __('Gewicht') }}</th>
								<th class="text-right py-2 pr-3 font-semibold">{{ __('Prijs') }}</th>
								<th class="text-right py-2 pr-3 font-semibold">{{ __('Handling') }}</th>
								<th class="text-right py-2 pl-3 font-semibold">{{ __('Totaal') }}</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($rates as $r)
								<tr class="border-b border-[color:var(--color-line)]/60 {{ $best && $best->id === $r->id ? 'bg-emerald-50' : '' }}">
									<td class="py-2 pr-3 font-semibold">{{ strtoupper($r->carrier) }}</td>
									<td class="py-2 pr-3">{{ $r->service }}</td>
									<td class="py-2 pr-3">{{ $r->zone }}</td>
									<td class="py-2 pr-3 tabular-nums text-[color:var(--color-ink-muted)]">{{ $fmtWeight($r->weight_from_g, $r->weight_to_g) }}</td>
									<td class="py-2 pr-3 text-right tabular-nums">{{ $fmt($r->price_ex) }}</td>
									<td class="py-2 pr-3 text-right tabular-nums text-[color:var(--color-ink-muted)]">{{ $fmt($r->handling_ex) }}</td>
									<td class="py-2 pl-3 text-right tabular-nums font-semibold">{{ $fmt($r->total_ex) }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			@endif
		</div>

		<div class="card">
			<h3 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Veelgestelde vragen') }}</h3>
			<details class="border-b border-[color:var(--color-line)]/60 py-3">
				<summary class="cursor-pointer font-semibold text-sm">{{ __('Waar komen deze tarieven vandaan?') }}</summary>
				<p class="text-sm text-[color:var(--color-ink-soft)] mt-2 leading-relaxed">
					{{ __('De tool toont tarieven uit onze eigen database, ingelezen uit openbaar beschikbare tarievenfolders (PostNL en DHL eCommerce). Bron en periode worden per regel bewaard.') }}
				</p>
			</details>
			<details class="py-3">
				<summary class="cursor-pointer font-semibold text-sm">{{ __('Waarom excl. btw?') }}</summary>
				<p class="text-sm text-[color:var(--color-ink-soft)] mt-2 leading-relaxed">
					{{ __('Voor interne calculaties is exclusief btw het meest praktisch. Zakelijke verzenders rekenen intern met ex. btw en passen btw later toe.') }}
				</p>
			</details>
		</div>
	</div>
</section>

@endsection
