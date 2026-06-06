@extends('layouts.app')

@section('title', __('BTW-aangifte') . ', ' . config('app.name'))

@php
	$locale = app()->getLocale();
	$fmt = fn ($v) => '€' . number_format((float) $v, 2, ',', '.');
	$fmtRate = fn ($r) => rtrim(rtrim(number_format($r, 2, ',', ''), '0'), ',') . '%';
@endphp

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1200px] mx-auto px-6 py-12">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-4 flex items-center gap-2">
			<a href="/{{ $locale }}/tools" class="hover:text-white">Tools</a>
			<span class="opacity-40">/</span>
			<a href="{{ route('tools.bookkeeping.index', ['locale' => $locale]) }}" class="hover:text-white">Boekhouden</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">{{ __('BTW-aangifte') }}</span>
		</nav>
		<h1 class="display-1">BTW-<span class="accent-word">{{ __('aangifte') }}</span></h1>
		<p class="text-[color:var(--color-on-dark-muted)] mt-2">
			Q{{ $quarter }} {{ $year }} · {{ $from }}, {{ $to }}
		</p>
	</div>
</section>

@include('tools.bookkeeping._subnav')

<section class="py-8">
	<div class="max-w-[1000px] mx-auto px-6 space-y-4">
		<form method="GET" class="card">
			<div class="flex gap-3 items-end flex-wrap">
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Jaar') }}</label>
					<select name="year" class="field-input py-1.5" onchange="this.form.submit()">
						@for ($y = (int) date('Y'); $y >= (int) date('Y') - 5; $y--)
							<option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
						@endfor
					</select>
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Kwartaal') }}</label>
					<select name="quarter" class="field-input py-1.5" onchange="this.form.submit()">
						@for ($q = 1; $q <= 4; $q++)
							<option value="{{ $q }}" @selected($quarter === $q)>Q{{ $q }}</option>
						@endfor
					</select>
				</div>
				<div class="flex items-end">
					<a href="{{ route('tools.bookkeeping.reports.vat.export', ['locale' => $locale, 'year' => $year, 'quarter' => $quarter]) }}"
						class="btn-dark text-sm">
						{{ __('Excel-export') }}
					</a>
				</div>
			</div>
		</form>

		<div class="card">
			<h3 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">
				{{ __('1. Prestaties binnenland, omzet per BTW-tarief') }}
			</h3>
			@if (empty($incomeByRate))
				<p class="text-sm text-[color:var(--color-ink-muted)]">{{ __('Geen omzet in dit kwartaal.') }}</p>
			@else
				<table class="w-full text-sm">
					<thead>
						<tr class="border-b-2 border-[color:var(--color-line)]">
							<th class="text-left py-2 pr-3 font-semibold">{{ __('Tarief') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Omzet excl.') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('BTW') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Omzet incl.') }}</th>
							<th class="text-right py-2 pl-3 font-semibold text-[color:var(--color-ink-soft)]">{{ __('Aantal') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($incomeByRate as $row)
							<tr class="border-b border-[color:var(--color-line)]/60">
								<td class="py-2 pr-3">{{ $fmtRate($row['rate']) }}</td>
								<td class="py-2 px-3 text-right tabular-nums">{{ $fmt($row['net']) }}</td>
								<td class="py-2 px-3 text-right tabular-nums">{{ $fmt($row['vat']) }}</td>
								<td class="py-2 px-3 text-right tabular-nums font-medium">{{ $fmt($row['gross']) }}</td>
								<td class="py-2 pl-3 text-right tabular-nums text-xs text-[color:var(--color-ink-soft)]">{{ $row['count'] }}×</td>
							</tr>
						@endforeach
						<tr class="font-semibold">
							<td class="py-3 pr-3">{{ __('Totaal verschuldigd') }}</td>
							<td></td>
							<td class="py-3 px-3 text-right tabular-nums">{{ $fmt($totalVatDue) }}</td>
							<td></td><td></td>
						</tr>
					</tbody>
				</table>
			@endif
		</div>

		<div class="card">
			<h3 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">
				{{ __('5b. Voorbelasting, BTW op inkoop') }}
			</h3>
			<dl class="grid grid-cols-1 sm:grid-cols-[15rem_1fr] gap-x-6 gap-y-2 text-sm">
				<dt class="text-[color:var(--color-ink-muted)]">{{ __('Aftrekbare BTW') }}</dt>
				<dd class="tabular-nums font-medium">{{ $fmt($deductibleVat) }}</dd>

				@if ($nonDeductibleVat > 0)
					<dt class="text-[color:var(--color-ink-muted)]">{{ __('Niet-aftrekbare BTW (info)') }}</dt>
					<dd class="tabular-nums text-[color:var(--color-ink-muted)]">{{ $fmt($nonDeductibleVat) }}</dd>
				@endif

				<dt class="text-[color:var(--color-ink-muted)]">{{ __('Kosten excl. BTW') }}</dt>
				<dd class="tabular-nums text-[color:var(--color-ink-muted)]">{{ $fmt($expenseNet) }} · {{ $expenseCount }}×</dd>
			</dl>
		</div>

		<div class="card border-2 {{ $netPayable >= 0 ? 'border-amber-300 bg-amber-50' : 'border-emerald-300 bg-emerald-50' }}">
			<div class="flex items-center justify-between gap-4 flex-wrap">
				<div>
					<div class="text-xs uppercase tracking-wider font-bold mb-1 {{ $netPayable >= 0 ? 'text-amber-900' : 'text-emerald-900' }}">
						{{ $netPayable >= 0 ? __('Te betalen aan Belastingdienst') : __('Te ontvangen van Belastingdienst') }}
					</div>
					<div class="text-3xl font-bold tabular-nums {{ $netPayable >= 0 ? 'text-amber-900' : 'text-emerald-900' }}">
						{{ $fmt(abs($netPayable)) }}
					</div>
				</div>
				<div class="text-xs text-[color:var(--color-ink-muted)] max-w-xs">
					{{ __('Verschuldigde BTW op omzet minus aftrekbare voorbelasting.') }}
				</div>
			</div>
		</div>

		<div class="text-xs text-[color:var(--color-ink-soft)] pt-4 leading-relaxed">
			{{ __('Deze berekening is een indicatie op basis van je ingevoerde transacties. Voor de officiële aangifte gebruik je Mijn Belastingdienst Zakelijk. Let op: verleggingsregeling, EU-diensten en ICP-aangifte zijn in deze MVP niet meegenomen.') }}
		</div>
	</div>
</section>

@endsection
