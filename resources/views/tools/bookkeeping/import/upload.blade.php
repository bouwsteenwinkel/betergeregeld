@extends('layouts.app')

@section('title', __('CSV-import') . ' — ' . config('app.name'))

@php $locale = app()->getLocale(); @endphp

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[900px] mx-auto px-6 py-12">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-4 flex items-center gap-2">
			<a href="/{{ $locale }}/tools" class="hover:text-white">Tools</a>
			<span class="opacity-40">/</span>
			<a href="{{ route('tools.bookkeeping.index', ['locale' => $locale]) }}" class="hover:text-white">Boekhouden</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">{{ __('CSV-import') }}</span>
		</nav>
		<h1 class="display-1">{{ __('CSV-import') }}</h1>
		<p class="text-[color:var(--color-on-dark-muted)] mt-2 max-w-xl">
			{{ __('Upload een CSV van je bank; de parser matcht rijen tegen bestaande transacties op datum + bedrag en stelt per rij voor om te maken, te linken of over te slaan.') }}
		</p>
	</div>
</section>

@include('tools.bookkeeping._subnav')

<section class="py-6">
	<div class="max-w-[900px] mx-auto px-6 space-y-4">
		<form method="POST" action="{{ route('tools.bookkeeping.import.upload', ['locale' => $locale]) }}" enctype="multipart/form-data" class="card space-y-5">
			@csrf
			<div>
				<label for="file" class="block text-sm font-semibold mb-2">{{ __('Kies CSV-bestand') }}</label>
				<input id="file" name="file" type="file" accept=".csv,text/csv,text/plain" required class="field-input">
				<p class="text-xs text-[color:var(--color-ink-soft)] mt-1.5">
					{{ __('Max 5 MB. Eerste regel moet kolom-headers bevatten.') }}
				</p>
			</div>

			<div>
				<label for="delimiter" class="block text-sm font-semibold mb-2">{{ __('Scheidingsteken') }}</label>
				<select id="delimiter" name="delimiter" class="field-input">
					<option value="comma">, {{ __('komma') }}</option>
					<option value="semicolon">; {{ __('puntkomma (NL/Excel)') }}</option>
					<option value="tab">tab</option>
				</select>
			</div>

			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">
					{{ $errors->first() }}
				</div>
			@endif

			<button type="submit" class="btn-accent">{{ __('Uploaden en analyseren') }}</button>
		</form>

		<div class="card">
			<h3 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Verwachte kolommen') }}</h3>
			<p class="text-sm text-[color:var(--color-ink-muted)] mb-3">{{ __('Eerste regel is de header. Kolom-namen worden case-insensitief herkend met NL en EN aliassen:') }}</p>
			<table class="w-full text-sm font-mono">
				<tbody>
					<tr class="border-b border-[color:var(--color-line)]/60">
						<td class="py-1.5 pr-3 font-semibold text-[color:var(--color-ink)]">date</td>
						<td class="py-1.5 text-[color:var(--color-ink-muted)]">datum, transactiedatum, boekdatum</td>
						<td class="py-1.5 pl-3 text-xs text-[color:var(--color-ink-soft)]">{{ __('verplicht') }}</td>
					</tr>
					<tr class="border-b border-[color:var(--color-line)]/60">
						<td class="py-1.5 pr-3 font-semibold text-[color:var(--color-ink)]">amount</td>
						<td class="py-1.5 text-[color:var(--color-ink-muted)]">bedrag, value</td>
						<td class="py-1.5 pl-3 text-xs text-[color:var(--color-ink-soft)]">{{ __('verplicht, minus = uitgave') }}</td>
					</tr>
					<tr class="border-b border-[color:var(--color-line)]/60">
						<td class="py-1.5 pr-3 font-semibold text-[color:var(--color-ink)]">description</td>
						<td class="py-1.5 text-[color:var(--color-ink-muted)]">omschrijving, mededelingen</td>
						<td class="py-1.5 pl-3 text-xs text-[color:var(--color-ink-soft)]">{{ __('optioneel') }}</td>
					</tr>
					<tr class="border-b border-[color:var(--color-line)]/60">
						<td class="py-1.5 pr-3 font-semibold text-[color:var(--color-ink)]">counterparty</td>
						<td class="py-1.5 text-[color:var(--color-ink-muted)]">tegenrekening naam, naam, begunstigde</td>
						<td class="py-1.5 pl-3 text-xs text-[color:var(--color-ink-soft)]">{{ __('optioneel') }}</td>
					</tr>
					<tr class="border-b border-[color:var(--color-line)]/60">
						<td class="py-1.5 pr-3 font-semibold text-[color:var(--color-ink)]">iban</td>
						<td class="py-1.5 text-[color:var(--color-ink-muted)]">tegenrekening iban</td>
						<td class="py-1.5 pl-3 text-xs text-[color:var(--color-ink-soft)]">{{ __('optioneel') }}</td>
					</tr>
					<tr>
						<td class="py-1.5 pr-3 font-semibold text-[color:var(--color-ink)]">reference</td>
						<td class="py-1.5 text-[color:var(--color-ink-muted)]">referentie, kenmerk, transaction id</td>
						<td class="py-1.5 pl-3 text-xs text-[color:var(--color-ink-soft)]">{{ __('optioneel') }}</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</section>

@endsection
