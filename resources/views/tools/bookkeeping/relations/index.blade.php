@extends('layouts.app')

@section('title', __('Relaties') . ', ' . config('app.name'))

@php $locale = app()->getLocale(); @endphp

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1200px] mx-auto px-6 py-12">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-4 flex items-center gap-2">
			<a href="/{{ $locale }}/tools" class="hover:text-white">Tools</a>
			<span class="opacity-40">/</span>
			<a href="{{ route('tools.bookkeeping.index', ['locale' => $locale]) }}" class="hover:text-white">Boekhouden</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">{{ __('Relaties') }}</span>
		</nav>
		<div class="flex items-start justify-between gap-6 flex-wrap">
			<h1 class="display-1">{{ __('Relaties') }}</h1>
			<a href="{{ route('tools.bookkeeping.relations.create', ['locale' => $locale]) }}" class="btn-accent text-sm">
				+ {{ __('Nieuwe relatie') }}
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
			<div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Type') }}</label>
					<select name="type" class="field-input py-1.5">
						<option value="" @selected(empty($filters['type']))>, {{ __('alle') }}, </option>
						<option value="client" @selected(($filters['type'] ?? '') === 'client')>{{ __('Klant') }}</option>
						<option value="supplier" @selected(($filters['type'] ?? '') === 'supplier')>{{ __('Leverancier') }}</option>
						<option value="both" @selected(($filters['type'] ?? '') === 'both')>{{ __('Beide') }}</option>
					</select>
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Status') }}</label>
					<select name="active" class="field-input py-1.5">
						<option value="all" @selected(($filters['active'] ?? '') === 'all')>, {{ __('alle') }}, </option>
						<option value="active" @selected(($filters['active'] ?? 'active') === 'active')>{{ __('Actief') }}</option>
						<option value="inactive" @selected(($filters['active'] ?? '') === 'inactive')>{{ __('Inactief') }}</option>
					</select>
				</div>
				<div class="sm:col-span-2">
					<label class="block text-xs font-semibold mb-1">{{ __('Zoeken') }}</label>
					<input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('naam, e-mail, KvK, BTW, stad…') }}" class="field-input py-1.5">
				</div>
			</div>
			<div class="mt-3 flex gap-2">
				<button type="submit" class="btn-accent text-sm">{{ __('Filter') }}</button>
				<a href="{{ route('tools.bookkeeping.relations.index', ['locale' => $locale]) }}" class="btn-dark text-sm">{{ __('Reset') }}</a>
			</div>
		</form>

		<div class="card">
			@if ($relations->isEmpty())
				<p class="text-sm text-[color:var(--color-ink-muted)] py-6 text-center">
					{{ __('Nog geen relaties. Voeg je eerste klant of leverancier toe hierboven.') }}
				</p>
			@else
				<div class="overflow-x-auto">
					<table class="w-full text-sm">
						<thead>
							<tr class="border-b-2 border-[color:var(--color-line)]">
								<th class="text-left py-2 pr-3 font-semibold">{{ __('Naam') }}</th>
								<th class="text-left py-2 px-3 font-semibold">{{ __('Type') }}</th>
								<th class="text-left py-2 px-3 font-semibold">{{ __('E-mail / telefoon') }}</th>
								<th class="text-left py-2 px-3 font-semibold">{{ __('Plaats') }}</th>
								<th class="text-left py-2 px-3 font-semibold">{{ __('KvK / BTW') }}</th>
								<th class="text-center py-2 px-3 font-semibold">{{ __('Actief') }}</th>
								<th class="py-2 pl-3"></th>
							</tr>
						</thead>
						<tbody>
							@foreach ($relations as $r)
								<tr class="border-b border-[color:var(--color-line)]/60">
									<td class="py-2 pr-3 font-medium">{{ $r->name }}</td>
									<td class="py-2 px-3 text-[color:var(--color-ink-muted)]">{{ __('rel.type.' . $r->type) }}</td>
									<td class="py-2 px-3 text-[color:var(--color-ink-muted)]">
										@if ($r->email)<div class="truncate max-w-[16rem]">{{ $r->email }}</div>@endif
										@if ($r->phone)<div class="text-xs">{{ $r->phone }}</div>@endif
									</td>
									<td class="py-2 px-3 text-[color:var(--color-ink-muted)]">
										{{ collect([$r->postal_code, $r->city])->filter()->join(' ') }}
									</td>
									<td class="py-2 px-3 text-[color:var(--color-ink-muted)] font-mono text-xs">
										@if ($r->kvk_number)<div>KvK {{ $r->kvk_number }}</div>@endif
										@if ($r->vat_number)<div>BTW {{ $r->vat_number }}</div>@endif
									</td>
									<td class="py-2 px-3 text-center">
										@if ($r->is_active)
											<span class="text-emerald-700">✓</span>
										@else
											<span class="text-[color:var(--color-ink-soft)]">, </span>
										@endif
									</td>
									<td class="py-2 pl-3 text-right whitespace-nowrap">
										<a href="{{ route('tools.bookkeeping.relations.edit', ['locale' => $locale, 'id' => $r->id]) }}" class="text-xs text-[color:var(--color-accent)] hover:underline">{{ __('Bewerken') }}</a>
										<form method="POST" action="{{ route('tools.bookkeeping.relations.destroy', ['locale' => $locale, 'id' => $r->id]) }}" class="inline ml-2" onsubmit="return confirm('{{ __('Relatie verwijderen?') }}')">
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
				<div class="mt-4">{{ $relations->links() }}</div>
			@endif
		</div>
	</div>
</section>

@endsection
