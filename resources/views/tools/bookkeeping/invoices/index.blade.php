@extends('layouts.app')

@section('title', __('Facturen') . ', ' . config('app.name'))

@php
	$locale = app()->getLocale();
	$fmt = fn ($v) => '€' . number_format((float) $v, 2, ',', '.');
	$statusClass = fn ($s) => match ($s) {
		'draft' => 'bg-slate-100 text-slate-700 border-slate-200',
		'sent' => 'bg-amber-50 text-amber-900 border-amber-200',
		'paid' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
		'cancelled' => 'bg-red-50 text-red-700 border-red-200',
	};
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
			<span class="text-[color:var(--color-on-dark-muted)]">{{ __('Facturen') }}</span>
		</nav>
		<div class="flex items-start justify-between gap-6 flex-wrap">
			<h1 class="display-1">{{ __('Facturen') }}</h1>
			<a href="{{ route('tools.bookkeeping.invoices.create', ['locale' => $locale]) }}" class="btn-accent text-sm">
				+ {{ __('Nieuwe factuur') }}
			</a>
		</div>
	</div>
</section>

@include('tools.bookkeeping._subnav')

<section class="py-6">
	<div class="max-w-[1200px] mx-auto px-6 space-y-4">
		@if (session('bookkeeping_message'))
			<div class="rounded-[var(--radius-control)] border border-emerald-200 bg-emerald-50 text-emerald-900 p-3 text-sm">
				{{ session('bookkeeping_message') }}
			</div>
		@endif

		<form method="GET" class="card">
			<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Status') }}</label>
					<select name="status" class="field-input py-1.5">
						<option value="" @selected(empty($filters['status']))>, {{ __('alle') }}, </option>
						@foreach (['draft', 'sent', 'paid', 'cancelled'] as $s)
							<option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ __('invoice.status.' . $s) }}</option>
						@endforeach
					</select>
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Klant') }}</label>
					<select name="relation_id" class="field-input py-1.5">
						<option value="">, {{ __('alle') }}, </option>
						@foreach ($relations as $r)
							<option value="{{ $r->id }}" @selected(($filters['relation_id'] ?? 0) == $r->id)>{{ $r->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="flex gap-2">
					<button type="submit" class="btn-accent text-sm">{{ __('Filter') }}</button>
					<a href="{{ route('tools.bookkeeping.invoices.index', ['locale' => $locale]) }}" class="btn-dark text-sm">{{ __('Reset') }}</a>
				</div>
			</div>
		</form>

		<div class="card">
			@if ($invoices->isEmpty())
				<p class="text-sm text-[color:var(--color-ink-muted)] py-6 text-center">
					{{ __('Nog geen facturen. Maak je eerste factuur aan hierboven.') }}
				</p>
			@else
				<div class="overflow-x-auto">
					<table class="w-full text-sm">
						<thead>
							<tr class="border-b-2 border-[color:var(--color-line)]">
								<th class="text-left py-2 pr-3 font-semibold">{{ __('Nr.') }}</th>
								<th class="text-left py-2 px-3 font-semibold">{{ __('Datum') }}</th>
								<th class="text-left py-2 px-3 font-semibold">{{ __('Klant') }}</th>
								<th class="text-center py-2 px-3 font-semibold">{{ __('Status') }}</th>
								<th class="text-right py-2 px-3 font-semibold">{{ __('Totaal') }}</th>
								<th class="text-right py-2 px-3 font-semibold">{{ __('Vervalt') }}</th>
								<th class="py-2 pl-3"></th>
							</tr>
						</thead>
						<tbody>
							@foreach ($invoices as $inv)
								<tr class="border-b border-[color:var(--color-line)]/60">
									<td class="py-2 pr-3 font-mono font-medium">
										<a href="{{ route('tools.bookkeeping.invoices.show', ['locale' => $locale, 'id' => $inv->id]) }}" class="hover:text-[color:var(--color-accent)]">
											{{ $inv->invoice_number }}
										</a>
									</td>
									<td class="py-2 px-3 tabular-nums text-[color:var(--color-ink-muted)]">{{ $inv->issue_date->format('d-m-Y') }}</td>
									<td class="py-2 px-3">{{ $inv->relation?->name ?? ', ' }}</td>
									<td class="py-2 px-3 text-center">
										<span class="pill border {{ $statusClass($inv->status) }} text-[10px] uppercase tracking-wider">{{ __('invoice.status.' . $inv->status) }}</span>
									</td>
									<td class="py-2 px-3 text-right tabular-nums font-medium">{{ $fmt($inv->total) }}</td>
									<td class="py-2 px-3 text-right tabular-nums text-[color:var(--color-ink-muted)] text-xs">
										{{ $inv->due_date?->format('d-m-Y') ?? ', ' }}
									</td>
									<td class="py-2 pl-3 text-right whitespace-nowrap">
										<a href="{{ route('tools.bookkeeping.invoices.pdf', ['locale' => $locale, 'id' => $inv->id]) }}" class="text-xs text-[color:var(--color-accent)] hover:underline">PDF</a>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
				<div class="mt-4">{{ $invoices->links() }}</div>
			@endif
		</div>
	</div>
</section>

@endsection
