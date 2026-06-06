@extends('layouts.app')

@section('title', __('Winst & verlies') . ', ' . config('app.name'))

@php
	$locale = app()->getLocale();
	$fmt = fn ($v) => '€' . number_format((float) $v, 2, ',', '.');
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
			<span class="text-[color:var(--color-on-dark-muted)]">{{ __('Winst & verlies') }}</span>
		</nav>
		<h1 class="display-1">{{ __('Winst') }} <span class="accent-word">{{ __('& verlies') }}</span></h1>
	</div>
</section>

@include('tools.bookkeeping._subnav')

<section class="py-8">
	<div class="max-w-[1200px] mx-auto px-6 space-y-4">
		<form method="GET" class="card">
			<div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Jaar') }}</label>
					<select name="year" class="field-input py-1.5" onchange="this.form.submit()">
						@foreach ($availableYears as $y)
							<option value="{{ $y }}" @selected($year === (int) $y)>{{ $y }}</option>
						@endforeach
					</select>
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Van') }}</label>
					<input type="date" name="from" value="{{ $from }}" class="field-input py-1.5">
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Tot') }}</label>
					<input type="date" name="to" value="{{ $to }}" class="field-input py-1.5">
				</div>
				<div class="flex gap-2">
					<button type="submit" class="btn-accent text-sm">{{ __('Toepassen') }}</button>
					<a href="{{ route('tools.bookkeeping.reports.profit-loss.export', array_merge(['locale' => $locale], request()->only(['year','from','to']))) }}"
						class="btn-dark text-sm">
						{{ __('Excel-export') }}
					</a>
				</div>
			</div>
		</form>

		<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
			<div class="card">
				<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Omzet') }}</div>
				<div class="text-2xl font-bold tabular-nums text-emerald-700">{{ $fmt($incomeTotal) }}</div>
			</div>
			<div class="card">
				<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Kosten') }}</div>
				<div class="text-2xl font-bold tabular-nums text-red-700">{{ $fmt($expenseTotal) }}</div>
			</div>
			<div class="card">
				<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-1">{{ __('Resultaat') }}</div>
				<div class="text-2xl font-bold tabular-nums {{ $result >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
					{{ ($result >= 0 ? '+' : '') }}{{ $fmt($result) }}
				</div>
				<div class="text-xs text-[color:var(--color-ink-soft)]">
					@if ($incomeTotal > 0)
						{{ __('marge') }}: {{ number_format($result / $incomeTotal * 100, 1, ',', '.') }}%
					@else
						, 
					@endif
				</div>
			</div>
		</div>

		<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
			<div class="card">
				<h3 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Omzet per categorie') }}</h3>
				@if (empty($incomeRows))
					<p class="text-sm text-[color:var(--color-ink-muted)]">{{ __('Geen omzet in deze periode.') }}</p>
				@else
					<table class="w-full text-sm">
						<tbody>
							@foreach ($incomeRows as $r)
								<tr class="border-b border-[color:var(--color-line)]/60">
									<td class="py-2 pr-3">{{ $r['category'] }}</td>
									<td class="py-2 px-3 text-right text-[color:var(--color-ink-muted)] tabular-nums text-xs">{{ $r['count'] }}×</td>
									<td class="py-2 pl-3 text-right tabular-nums font-medium">{{ $fmt($r['total']) }}</td>
								</tr>
							@endforeach
							<tr class="font-semibold">
								<td class="py-3 pr-3">{{ __('Totaal') }}</td>
								<td></td>
								<td class="py-3 pl-3 text-right tabular-nums text-emerald-700">{{ $fmt($incomeTotal) }}</td>
							</tr>
						</tbody>
					</table>
				@endif
			</div>

			<div class="card">
				<h3 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Kosten per categorie') }}</h3>
				@if (empty($expenseRows))
					<p class="text-sm text-[color:var(--color-ink-muted)]">{{ __('Geen kosten in deze periode.') }}</p>
				@else
					<table class="w-full text-sm">
						<tbody>
							@foreach ($expenseRows as $r)
								<tr class="border-b border-[color:var(--color-line)]/60">
									<td class="py-2 pr-3">{{ $r['category'] }}</td>
									<td class="py-2 px-3 text-right text-[color:var(--color-ink-muted)] tabular-nums text-xs">{{ $r['count'] }}×</td>
									<td class="py-2 pl-3 text-right tabular-nums font-medium">{{ $fmt($r['total']) }}</td>
								</tr>
							@endforeach
							<tr class="font-semibold">
								<td class="py-3 pr-3">{{ __('Totaal') }}</td>
								<td></td>
								<td class="py-3 pl-3 text-right tabular-nums text-red-700">{{ $fmt($expenseTotal) }}</td>
							</tr>
						</tbody>
					</table>
				@endif
			</div>
		</div>

		<div class="text-xs text-[color:var(--color-ink-soft)] pt-4">
			{{ __('Bedragen zijn brutowaarden (incl. BTW indien van toepassing). Voor BTW-aangifte zie het tabblad BTW-aangifte.') }}
		</div>
	</div>
</section>

@endsection
