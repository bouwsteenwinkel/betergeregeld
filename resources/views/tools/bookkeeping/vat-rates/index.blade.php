@extends('layouts.app')

@section('title', __('BTW-tarieven') . ', ' . config('app.name'))

@php
	$locale = app()->getLocale();
	$fmtRate = fn ($r) => rtrim(rtrim(number_format((float) $r, 2, ',', ''), '0'), ',') . '%';
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
			<span class="text-[color:var(--color-on-dark-muted)]">{{ __('BTW-tarieven') }}</span>
		</nav>
		<div class="flex items-start justify-between gap-6 flex-wrap">
			<h1 class="display-1">BTW-<span class="accent-word">{{ __('tarieven') }}</span></h1>
			<a href="{{ route('tools.bookkeeping.vat-rates.create', ['locale' => $locale]) }}" class="btn-accent text-sm">
				+ {{ __('Eigen tarief toevoegen') }}
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

		<div class="card">
			<h3 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">
				{{ __('Jouw eigen tarieven') }}
			</h3>
			@if ($own->isEmpty())
				<p class="text-sm text-[color:var(--color-ink-muted)]">
					{{ __('Nog geen eigen tarieven. Voeg een afwijkend of historisch tarief toe als je dat nodig hebt.') }}
				</p>
			@else
				<table class="w-full text-sm">
					<thead>
						<tr class="border-b border-[color:var(--color-line)]">
							<th class="text-left py-2 pr-3 font-semibold">{{ __('Naam') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Tarief') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Van') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Tot') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Standaard') }}</th>
							<th class="py-2 pl-3"></th>
						</tr>
					</thead>
					<tbody>
						@foreach ($own as $r)
							<tr class="border-b border-[color:var(--color-line)]/60">
								<td class="py-2 pr-3">{{ $r->name }}</td>
								<td class="py-2 px-3 text-right tabular-nums font-mono">{{ $fmtRate($r->rate) }}</td>
								<td class="py-2 px-3 tabular-nums text-[color:var(--color-ink-muted)]">{{ $r->effective_from?->format('d-m-Y') }}</td>
								<td class="py-2 px-3 tabular-nums text-[color:var(--color-ink-muted)]">{{ $r->effective_to?->format('d-m-Y') ?? ', ' }}</td>
								<td class="py-2 px-3">
									@if ($r->is_default)
										<span class="pill pill-teal text-[10px]">{{ __('Standaard') }}</span>
									@endif
								</td>
								<td class="py-2 pl-3 text-right whitespace-nowrap">
									<a href="{{ route('tools.bookkeeping.vat-rates.edit', ['locale' => $locale, 'id' => $r->id]) }}" class="text-xs text-[color:var(--color-accent)] hover:underline">{{ __('Bewerken') }}</a>
									<form method="POST" action="{{ route('tools.bookkeeping.vat-rates.destroy', ['locale' => $locale, 'id' => $r->id]) }}" class="inline ml-2" onsubmit="return confirm('{{ __('Tarief verwijderen?') }}')">
										@csrf
										@method('DELETE')
										<button type="submit" class="text-xs text-red-600 hover:underline">{{ __('Verwijderen') }}</button>
									</form>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			@endif
		</div>

		<div class="card">
			<div class="flex items-center justify-between mb-3">
				<h3 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)]">
					{{ __('Standaard NL-tarieven') }}
				</h3>
				<span class="text-xs text-[color:var(--color-ink-soft)]">{{ __('alleen-lezen') }}</span>
			</div>
			<table class="w-full text-sm">
				<tbody>
					@foreach ($defaults as $r)
						<tr class="border-b border-[color:var(--color-line)]/60">
							<td class="py-2 pr-3 text-[color:var(--color-ink-muted)]">{{ $r->name }}</td>
							<td class="py-2 px-3 text-right tabular-nums font-mono">{{ $fmtRate($r->rate) }}</td>
							<td class="py-2 pl-3">
								@if ($r->is_default)
									<span class="pill pill-teal text-[10px]">{{ __('Standaard') }}</span>
								@endif
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>

		<p class="text-xs text-[color:var(--color-ink-soft)] leading-relaxed">
			{{ __('Tip: gebruik eigen tarieven alleen als je afwijkt van de NL-standaarden (bijv. historisch 19% tarief voor oude boekingen of specifieke verleggingsregeling-varianten).') }}
		</p>
	</div>
</section>

@endsection
