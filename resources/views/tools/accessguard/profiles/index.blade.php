@extends('layouts.app')

@section('title', __('Profielen') . ', AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = __('Profielen');
@endphp

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[1200px] mx-auto px-6 space-y-4">
		@if (session('status'))
			<div class="card text-sm bg-emerald-50 border-emerald-200 text-emerald-900">{{ session('status') }}</div>
		@endif

		<div class="flex items-center justify-between">
			<strong class="text-sm uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ __(':n profielen', ['n' => $profiles->count()]) }}</strong>
			<a href="{{ route('tools.accessguard.profiles.create', ['locale' => $locale]) }}" class="btn-accent text-sm">{{ __('+ Nieuw profile') }}</a>
		</div>

		<div class="card p-0 overflow-hidden">
			@if ($profiles->isEmpty())
				<div class="p-6 text-center text-sm text-[color:var(--color-ink-muted)]">
					<p class="mb-2">{{ __('Nog geen profielen. Maak er één om toegang voor een hele rol in één klik toe te passen.') }}</p>
					<p class="text-xs">{{ __('Voorbeelden: "Sales", "Developer", "Office Manager", elk met een vaste set systemen en items.') }}</p>
				</div>
			@else
				<table class="w-full text-sm">
					<thead class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] border-b border-[color:var(--color-line)]">
						<tr>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Naam') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Omschrijving') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Items') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Leden') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Status') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Acties') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($profiles as $p)
							<tr class="border-b border-[color:var(--color-line)]/60 hover:bg-[color:var(--color-surface-soft,#fafafa)]">
								<td class="py-2 px-3 font-semibold">
									{{ $p->name }}
									@if ($p->external_source)
										<span class="inline-flex items-center ml-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800 uppercase tracking-wider">
											{{ $p->external_source }}
										</span>
									@endif
								</td>
								<td class="py-2 px-3 text-xs text-[color:var(--color-ink-muted)]">{{ $p->description ?: ', ' }}</td>
								<td class="py-2 px-3 text-right tabular-nums text-[color:var(--color-ink-muted)]">{{ $p->items_count }}</td>
								<td class="py-2 px-3 text-right tabular-nums text-[color:var(--color-ink-muted)]">{{ $p->members_count }}</td>
								<td class="py-2 px-3">
									<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $p->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-500' }}">
										{{ $p->is_active ? __('Actief') : __('Inactief') }}
									</span>
								</td>
								<td class="py-2 px-3 text-right whitespace-nowrap">
									@if ($p->is_active && $p->items_count > 0 && $p->members_count > 0)
										<form method="POST" action="{{ route('tools.accessguard.profiles.apply-to-members', ['locale' => $locale, 'id' => $p->id]) }}"
											class="inline"
											onsubmit="return confirm('{{ __('Profile toepassen op :n leden? Alleen onbekende cellen worden ingevuld.', ['n' => $p->members_count]) }}');">
											@csrf
											<input type="hidden" name="strategy" value="add_only">
											<button type="submit" class="text-emerald-700 font-semibold hover:underline text-xs">{{ __('Op alle leden') }}</button>
										</form>
									@endif
									@if ($p->is_active && $p->items_count > 0)
										<a href="{{ route('tools.accessguard.profiles.apply-form', ['locale' => $locale, 'id' => $p->id]) }}" class="text-emerald-700 font-semibold hover:underline text-xs ml-2">{{ __('Op 1 persoon') }}</a>
									@endif
									<a href="{{ route('tools.accessguard.profiles.edit', ['locale' => $locale, 'id' => $p->id]) }}" class="text-[color:var(--color-accent)] font-semibold hover:underline text-xs ml-2">{{ __('Bewerken') }}</a>
									@unless ($p->external_source)
										<form method="POST" action="{{ route('tools.accessguard.profiles.destroy', ['locale' => $locale, 'id' => $p->id]) }}" class="inline" onsubmit="return confirm('{{ __('Profile verwijderen?') }}');">
											@csrf
											@method('DELETE')
											<button type="submit" class="text-red-600 font-semibold hover:underline text-xs ml-2">{{ __('Verwijderen') }}</button>
										</form>
									@endunless
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			@endif
		</div>
	</div>
</section>

@endsection
