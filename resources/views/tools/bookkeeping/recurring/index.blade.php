@extends('layouts.app')

@section('title', __('Terugkerende facturen') . ', ' . config('app.name'))

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
			<span class="text-[color:var(--color-on-dark-muted)]">{{ __('Terugkerend') }}</span>
		</nav>
		<div class="flex items-start justify-between gap-6 flex-wrap">
			<h1 class="display-1">{{ __('Terugkerende facturen') }}</h1>
			<a href="{{ route('tools.bookkeeping.recurring.create', ['locale' => $locale]) }}" class="btn-accent text-sm">
				+ {{ __('Nieuwe template') }}
			</a>
		</div>
		<p class="text-[color:var(--color-on-dark-muted)] mt-3 max-w-2xl">
			{{ __('Maak een template aan voor een factuur die elke maand hetzelfde is. De dagelijkse job maakt vanaf de gekozen dag automatisch een nieuwe factuur aan, eventueel direct als e-mail naar de klant.') }}
		</p>
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
		@if ($errors->any())
			<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">{{ $errors->first() }}</div>
		@endif

		<div class="card">
			@if ($templates->isEmpty())
				<p class="text-sm text-[color:var(--color-ink-muted)] py-6 text-center">
					{{ __('Nog geen terugkerende facturen. Voeg er een toe voor klanten die maandelijks hetzelfde factureren.') }}
				</p>
			@else
				<table class="w-full text-sm">
					<thead>
						<tr class="border-b-2 border-[color:var(--color-line)]">
							<th class="text-left py-2 pr-3 font-semibold">{{ __('Titel') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Klant') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Frequentie') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Volgende run') }}</th>
							<th class="text-center py-2 px-3 font-semibold">{{ __('Auto-e-mail') }}</th>
							<th class="text-center py-2 px-3 font-semibold">{{ __('Actief') }}</th>
							<th class="py-2 pl-3"></th>
						</tr>
					</thead>
					<tbody>
						@foreach ($templates as $tpl)
							<tr class="border-b border-[color:var(--color-line)]/60">
								<td class="py-2 pr-3 font-medium">{{ $tpl->title }}</td>
								<td class="py-2 px-3 text-[color:var(--color-ink-muted)]">{{ $tpl->relation?->name ?? ', ' }}</td>
								<td class="py-2 px-3 text-[color:var(--color-ink-muted)]">
									{{ __('recurring.frequency.' . $tpl->frequency) }} · {{ __('dag') }} {{ $tpl->day_of_month }}
								</td>
								<td class="py-2 px-3 tabular-nums text-[color:var(--color-ink-muted)]">
									{{ $tpl->next_run_at?->format('d-m-Y') ?? ', ' }}
								</td>
								<td class="py-2 px-3 text-center">
									@if ($tpl->auto_send_email)<span class="text-emerald-700">✓</span>@else<span class="text-[color:var(--color-ink-soft)]">, </span>@endif
								</td>
								<td class="py-2 px-3 text-center">
									@if ($tpl->is_active)<span class="text-emerald-700">✓</span>@else<span class="text-[color:var(--color-ink-soft)]">, </span>@endif
								</td>
								<td class="py-2 pl-3 text-right whitespace-nowrap">
									<form method="POST" action="{{ route('tools.bookkeeping.recurring.run-now', ['locale' => $locale, 'id' => $tpl->id]) }}" class="inline"
										onsubmit="return confirm('{{ __('Nu factuur aanmaken uit deze template?') }}')">
										@csrf
										<button type="submit" class="text-xs text-[color:var(--color-accent)] hover:underline">{{ __('Nu draaien') }}</button>
									</form>
									<a href="{{ route('tools.bookkeeping.recurring.edit', ['locale' => $locale, 'id' => $tpl->id]) }}" class="text-xs text-[color:var(--color-accent)] hover:underline ml-2">{{ __('Bewerken') }}</a>
									<form method="POST" action="{{ route('tools.bookkeeping.recurring.destroy', ['locale' => $locale, 'id' => $tpl->id]) }}" class="inline ml-2"
										onsubmit="return confirm('{{ __('Template verwijderen?') }}')">
										@csrf @method('DELETE')
										<button type="submit" class="text-xs text-red-600 hover:underline">{{ __('Verwijderen') }}</button>
									</form>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			@endif
		</div>

		<p class="text-xs text-[color:var(--color-ink-soft)]">
			{{ __('De scheduler draait dagelijks om 08:00. Voor productie moet php artisan schedule:run via cron lopen.') }}
		</p>
	</div>
</section>

@endsection
