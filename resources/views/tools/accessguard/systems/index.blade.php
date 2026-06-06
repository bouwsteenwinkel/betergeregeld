@extends('layouts.app')

@section('title', __('Systemen') . ', AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = __('Systemen');
	$categoryLabels = [
		'saas' => 'SaaS',
		'on_prem' => __('On-prem'),
		'infra' => __('Infrastructuur'),
		'finance' => __('Financieel'),
		'security' => __('Security'),
		'comm' => __('Communicatie'),
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

		<div class="flex items-center justify-end">
			<a href="{{ route('tools.accessguard.systems.create', ['locale' => $locale]) }}" class="btn-accent text-sm">{{ __('+ Nieuw systeem') }}</a>
		</div>

		<div class="card p-0 overflow-hidden">
			@if ($systems->isEmpty())
				<p class="text-sm text-[color:var(--color-ink-muted)] p-6 text-center">{{ __('Nog geen systemen. Voeg er één toe om te beginnen.') }}</p>
			@else
				<table class="w-full text-sm">
					<thead class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] border-b border-[color:var(--color-line)]">
						<tr>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Naam') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Categorie') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Status') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Acties') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($systems as $s)
							<tr class="border-b border-[color:var(--color-line)]/60 hover:bg-[color:var(--color-surface-soft,#fafafa)]">
								<td class="py-2 px-3 font-semibold">{{ $s->name }}</td>
								<td class="py-2 px-3 text-[color:var(--color-ink-muted)]">{{ $categoryLabels[$s->category] ?? $s->category }}</td>
								<td class="py-2 px-3">
									<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $s->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
										{{ $s->is_active ? __('Actief') : __('Inactief') }}
									</span>
								</td>
								<td class="py-2 px-3 text-right">
									<a href="{{ route('tools.accessguard.systems.items.index', ['locale' => $locale, 'systemId' => $s->id]) }}" class="text-slate-600 font-semibold hover:underline text-xs mr-2">{{ __('Items') }}</a>
									<a href="{{ route('tools.accessguard.systems.edit', ['locale' => $locale, 'id' => $s->id]) }}" class="text-[color:var(--color-accent)] font-semibold hover:underline text-xs">{{ __('Bewerken') }}</a>
									<form method="POST" action="{{ route('tools.accessguard.systems.destroy', ['locale' => $locale, 'id' => $s->id]) }}" class="inline" onsubmit="return confirm('{{ __('Weet je zeker dat je dit systeem wilt verwijderen? Alle cellen in de matrix worden ook verwijderd.') }}');">
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

		<div>{{ $systems->links() }}</div>
	</div>
</section>

@endsection
