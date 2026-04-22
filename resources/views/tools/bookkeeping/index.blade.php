@extends('layouts.app')

@section('title', __('Boekhouden') . ' — ' . config('app.name'))

@php
	$locale = app()->getLocale();
@endphp

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1200px] mx-auto px-6 py-16">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-4 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<a href="/{{ $locale }}/tools" class="hover:text-white">Tools</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Boekhouden</span>
		</nav>
		<div class="flex items-start justify-between gap-6 flex-wrap">
			<div>
				<span class="pill pill-teal mb-4">Pro</span>
				<h1 class="display-1 mb-2">{{ __('Boekhouden') }}</h1>
				<p class="text-[color:var(--color-on-dark-muted)] max-w-xl">
					{{ __('Eenvoudige administratie: transacties met BTW, categorieën en filters.') }}
				</p>
			</div>
			<div class="flex gap-2">
				<a href="{{ route('tools.bookkeeping.create', ['locale' => $locale, 'type' => 'expense']) }}" class="btn-accent text-sm">
					+ {{ __('Kostenpost') }}
				</a>
				<a href="{{ route('tools.bookkeeping.create', ['locale' => $locale, 'type' => 'income']) }}" class="btn-dark text-sm">
					+ {{ __('Inkomst') }}
				</a>
			</div>
		</div>
	</div>
</section>

@include('tools.bookkeeping._subnav')

<section class="py-6">
	<div class="max-w-[1200px] mx-auto px-6">
		@if (session('bookkeeping_message'))
			<div class="rounded-[var(--radius-control)] border border-emerald-200 bg-emerald-50 text-emerald-900 p-3 text-sm mb-4">
				{{ session('bookkeeping_message') }}
			</div>
		@endif

		{{-- Totals --}}
		<div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-5">
			<div class="card">
				<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Kosten') }}</div>
				<div class="text-2xl font-bold tabular-nums">€{{ number_format($totals['expense_total'], 2, ',', '.') }}</div>
				<div class="text-xs text-[color:var(--color-ink-soft)]">{{ $totals['expense_count'] }} {{ __('posten') }}</div>
			</div>
			<div class="card">
				<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Inkomsten') }}</div>
				<div class="text-2xl font-bold tabular-nums">€{{ number_format($totals['income_total'], 2, ',', '.') }}</div>
				<div class="text-xs text-[color:var(--color-ink-soft)]">{{ $totals['income_count'] }} {{ __('posten') }}</div>
			</div>
			<div class="card sm:col-span-2">
				<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Saldo (bruto)') }}</div>
				@php $saldo = $totals['income_total'] - $totals['expense_total']; @endphp
				<div class="text-2xl font-bold tabular-nums {{ $saldo >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
					€{{ number_format($saldo, 2, ',', '.') }}
				</div>
				<div class="text-xs text-[color:var(--color-ink-soft)]">
					{{ __('Filterbereik') }}:
					{{ $filters['from'] ?? __('alle dagen') }} — {{ $filters['to'] ?? __('nu') }}
				</div>
			</div>
		</div>

		{{-- Filter form --}}
		<form method="GET" class="card mb-4">
			<div class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Type') }}</label>
					<select name="type" class="field-input py-1.5">
						<option value="" @selected(empty($filters['type']))>— {{ __('alle') }} —</option>
						<option value="expense" @selected(($filters['type'] ?? '') === 'expense')>{{ __('Kosten') }}</option>
						<option value="income" @selected(($filters['type'] ?? '') === 'income')>{{ __('Inkomsten') }}</option>
					</select>
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Van') }}</label>
					<input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="field-input py-1.5">
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Tot') }}</label>
					<input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="field-input py-1.5">
				</div>
				<div class="sm:col-span-2">
					<label class="block text-xs font-semibold mb-1">{{ __('Zoeken') }}</label>
					<input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('omschrijving, wederpartij, factuurnr…') }}" class="field-input py-1.5">
				</div>
			</div>
			<div class="mt-3 flex gap-2">
				<button type="submit" class="btn-accent text-sm">{{ __('Filter') }}</button>
				<a href="{{ route('tools.bookkeeping.index', ['locale' => $locale]) }}" class="btn-dark text-sm">{{ __('Reset') }}</a>
			</div>
		</form>

		{{-- Transactions table --}}
		<div class="card">
			@if ($transactions->isEmpty())
				<p class="text-sm text-[color:var(--color-ink-muted)] py-6 text-center">
					{{ __('Nog geen transacties. Voeg je eerste kostenpost of inkomst toe hierboven.') }}
				</p>
			@else
				<div class="overflow-x-auto">
					<table class="w-full text-sm">
						<thead>
							<tr class="border-b-2 border-[color:var(--color-line)]">
								<th class="text-left py-2 pr-3 font-semibold">{{ __('Datum') }}</th>
								<th class="text-left py-2 px-3 font-semibold">{{ __('Omschrijving') }}</th>
								<th class="text-left py-2 px-3 font-semibold">{{ __('Categorie') }}</th>
								<th class="text-left py-2 px-3 font-semibold">{{ __('Wederpartij') }}</th>
								<th class="text-right py-2 px-3 font-semibold">{{ __('BTW') }}</th>
								<th class="text-right py-2 px-3 font-semibold">{{ __('Bedrag') }}</th>
								<th class="py-2"></th>
							</tr>
						</thead>
						<tbody>
							@foreach ($transactions as $tx)
								<tr class="border-b border-[color:var(--color-line)]/60">
									<td class="py-3 pr-3 tabular-nums text-[color:var(--color-ink-muted)]">{{ $tx->transaction_date->format('d-m-Y') }}</td>
									<td class="py-3 px-3">
										<div class="font-medium flex items-center gap-2">
											<span>{{ $tx->description }}</span>
											@if ($tx->receipt_path)
												<a href="{{ route('tools.bookkeeping.receipt.view', ['locale' => $locale, 'id' => $tx->id]) }}" target="_blank" rel="noopener"
													title="{{ __('Bonnetje bekijken') }}"
													class="shrink-0 inline-flex items-center justify-center w-5 h-5 rounded bg-[color:var(--color-accent)]/10 text-[color:var(--color-accent)] hover:bg-[color:var(--color-accent)]/20">
													<svg class="w-3 h-3" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M4 2h6l4 4v8a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1Z M10 2v4h4 M6 9h4M6 12h3" stroke-linecap="round" stroke-linejoin="round"/></svg>
												</a>
											@endif
											@if ($tx->invoice_id)
												<a href="{{ route('tools.bookkeeping.invoices.show', ['locale' => $locale, 'id' => $tx->invoice_id]) }}"
													title="{{ __('Gekoppelde factuur') }}"
													class="shrink-0 inline-flex items-center justify-center w-5 h-5 rounded bg-[color:var(--color-accent)]/10 text-[color:var(--color-accent)] hover:bg-[color:var(--color-accent)]/20">
													<svg class="w-3 h-3" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M3 2h7l3 3v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1Z M10 2v3h3 M5 8h6M5 11h4" stroke-linecap="round" stroke-linejoin="round"/></svg>
												</a>
											@endif
										</div>
										@if ($tx->invoice_number)
											<div class="text-xs text-[color:var(--color-ink-soft)] font-mono">{{ $tx->invoice_number }}</div>
										@endif
									</td>
									<td class="py-3 px-3 text-[color:var(--color-ink-muted)]">{{ $tx->category?->name ?? '—' }}</td>
									<td class="py-3 px-3 text-[color:var(--color-ink-muted)]">
										@if ($tx->relation)
											<a href="{{ route('tools.bookkeeping.relations.edit', ['locale' => $locale, 'id' => $tx->relation->id]) }}" class="hover:text-[color:var(--color-accent)]">{{ $tx->relation->name }}</a>
										@else
											{{ $tx->counterparty ?? '—' }}
										@endif
									</td>
									<td class="py-3 px-3 text-right tabular-nums text-[color:var(--color-ink-muted)]">
										@if ($tx->vatRate)
											{{ rtrim(rtrim((string) $tx->vatRate->rate, '0'), '.') }}%
										@else
											—
										@endif
									</td>
									<td class="py-3 px-3 text-right tabular-nums font-medium {{ $tx->type === 'income' ? 'text-emerald-700' : '' }}">
										{{ $tx->type === 'income' ? '+' : '−' }}€{{ number_format((float) $tx->amount, 2, ',', '.') }}
									</td>
									<td class="py-3 pl-3 pr-0 whitespace-nowrap">
										<a href="{{ route('tools.bookkeeping.edit', ['locale' => $locale, 'id' => $tx->id]) }}" class="text-xs text-[color:var(--color-accent)] hover:underline">{{ __('Bewerken') }}</a>
										<form method="POST" action="{{ route('tools.bookkeeping.destroy', ['locale' => $locale, 'id' => $tx->id]) }}" class="inline ml-2" onsubmit="return confirm('{{ __('Transactie verwijderen?') }}')">
											@csrf
											@method('DELETE')
											<button type="submit" class="text-xs text-red-600 hover:underline">{{ __('Verwijderen') }}</button>
										</form>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
				<div class="mt-4">
					{{ $transactions->links() }}
				</div>
			@endif
		</div>
	</div>
</section>

@endsection
