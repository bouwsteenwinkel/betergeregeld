@extends('layouts.app')

@section('title', __('Items') . ', ' . $system->name . ', AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = $system->name . ' / ' . __('Items');
	$typeLabels = [
		'role' => __('Rol'),
		'licence' => __('Licentie'),
		'account' => __('Account'),
		'key' => __('Sleutel'),
		'badge' => __('Pas'),
		'group' => __('Groep'),
		'other' => __('Overig'),
	];
@endphp

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[1400px] mx-auto px-6 space-y-4">
		@if (session('status'))
			<div class="card text-sm bg-emerald-50 border-emerald-200 text-emerald-900">{{ session('status') }}</div>
		@endif

		<div class="card">
			<div class="flex items-start justify-between gap-4 flex-wrap">
				<div>
					<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold">{{ __('Systeem') }}</div>
					<h2 class="text-lg font-bold">{{ $system->name }}</h2>
					<p class="text-xs text-[color:var(--color-ink-muted)] mt-1">
						{{ __('Fijnmazige permissies/rollen/licenties binnen dit systeem. Als je items definieert wordt de cel-state in de matrix hieruit afgeleid.') }}
					</p>
				</div>
				<a href="{{ route('tools.accessguard.systems.index', ['locale' => $locale]) }}" class="text-sm text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">← {{ __('Terug naar systemen') }}</a>
			</div>
		</div>

		<div class="flex items-center justify-between">
			<strong class="text-sm uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ __(':n items', ['n' => $items->count()]) }}</strong>
			<a href="{{ route('tools.accessguard.systems.items.create', ['locale' => $locale, 'systemId' => $system->id]) }}" class="btn-accent text-sm">{{ __('+ Nieuw item') }}</a>
		</div>

		<div class="card p-0 overflow-hidden">
			@if ($items->isEmpty())
				<div class="p-6 text-center text-sm text-[color:var(--color-ink-muted)]">
					<p class="mb-2">{{ __('Nog geen items voor dit systeem.') }}</p>
					<p class="text-xs">{{ __('Voorbeelden: "Admin role", "Read-only", "Finance dashboard viewer", of per licentietype bij SaaS.') }}</p>
				</div>
			@else
				<table class="w-full text-sm">
					<thead class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] border-b border-[color:var(--color-line)]">
						<tr>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Naam') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Type') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Beschrijving') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Status') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Sort') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Acties') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($items as $item)
							<tr class="border-b border-[color:var(--color-line)]/60 hover:bg-[color:var(--color-surface-soft,#fafafa)]">
								<td class="py-2 px-3 font-semibold">{{ $item->name }}</td>
								<td class="py-2 px-3 text-[color:var(--color-ink-muted)]">{{ $typeLabels[$item->type] ?? $item->type }}</td>
								<td class="py-2 px-3 text-xs text-[color:var(--color-ink-muted)]">{{ $item->description ?: ', ' }}</td>
								<td class="py-2 px-3">
									<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $item->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-500' }}">
										{{ $item->is_active ? __('Actief') : __('Inactief') }}
									</span>
								</td>
								<td class="py-2 px-3 text-right text-xs text-[color:var(--color-ink-muted)] tabular-nums">{{ $item->sort_order }}</td>
								<td class="py-2 px-3 text-right whitespace-nowrap">
									<a href="{{ route('tools.accessguard.systems.items.edit', ['locale' => $locale, 'systemId' => $system->id, 'id' => $item->id]) }}" class="text-[color:var(--color-accent)] font-semibold hover:underline text-xs">{{ __('Bewerken') }}</a>
									<form method="POST" action="{{ route('tools.accessguard.systems.items.destroy', ['locale' => $locale, 'systemId' => $system->id, 'id' => $item->id]) }}" class="inline" onsubmit="return confirm('{{ __('Item verwijderen? Alle persoon-koppelingen worden ook verwijderd.') }}');">
										@csrf
										@method('DELETE')
										<button type="submit" class="text-red-600 font-semibold hover:underline text-xs ml-2">{{ __('Verwijderen') }}</button>
									</form>
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
