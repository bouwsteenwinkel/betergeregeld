@extends('layouts.app')

@section('title', __('Categorieën') . ', ' . config('app.name'))

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
			<span class="text-[color:var(--color-on-dark-muted)]">{{ __('Categorieën') }}</span>
		</nav>
		<div class="flex items-start justify-between gap-6 flex-wrap">
			<h1 class="display-1">{{ __('Categorieën') }}</h1>
			<a href="{{ route('tools.bookkeeping.categories.create', ['locale' => $locale]) }}" class="btn-accent text-sm">
				+ {{ __('Eigen categorie toevoegen') }}
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
				{{ __('Jouw eigen categorieën') }}
			</h3>
			@if ($own->isEmpty())
				<p class="text-sm text-[color:var(--color-ink-muted)]">
					{{ __('Nog geen eigen categorieën. Voeg er een toe om afwijkende boekhouding te ondersteunen.') }}
				</p>
			@else
				<table class="w-full text-sm">
					<thead>
						<tr class="border-b border-[color:var(--color-line)]">
							<th class="text-left py-2 pr-3 font-semibold">{{ __('Naam') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Type') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Volgorde') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Actief') }}</th>
							<th class="py-2 pl-3"></th>
						</tr>
					</thead>
					<tbody>
						@foreach ($own as $cat)
							<tr class="border-b border-[color:var(--color-line)]/60">
								<td class="py-2 pr-3">{{ $cat->name }}</td>
								<td class="py-2 px-3 text-[color:var(--color-ink-muted)]">{{ __('cat.type.' . $cat->type) }}</td>
								<td class="py-2 px-3 text-right tabular-nums text-[color:var(--color-ink-muted)]">{{ $cat->sort_order }}</td>
								<td class="py-2 px-3">
									@if ($cat->is_active)
										<span class="text-emerald-700">✓</span>
									@else
										<span class="text-[color:var(--color-ink-soft)]">, </span>
									@endif
								</td>
								<td class="py-2 pl-3 text-right whitespace-nowrap">
									<a href="{{ route('tools.bookkeeping.categories.edit', ['locale' => $locale, 'id' => $cat->id]) }}" class="text-xs text-[color:var(--color-accent)] hover:underline">{{ __('Bewerken') }}</a>
									<form method="POST" action="{{ route('tools.bookkeeping.categories.destroy', ['locale' => $locale, 'id' => $cat->id]) }}" class="inline ml-2" onsubmit="return confirm('{{ __('Categorie verwijderen?') }}')">
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
					{{ __('Standaard-categorieën') }}
				</h3>
				<span class="text-xs text-[color:var(--color-ink-soft)]">{{ __('alleen-lezen') }}</span>
			</div>
			<p class="text-xs text-[color:var(--color-ink-muted)] mb-4">
				{{ __('Deze categorieën zijn standaard voor alle gebruikers en niet aanpasbaar. Voeg een eigen categorie toe als je iets specifieks nodig hebt.') }}
			</p>
			<div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8">
				@foreach (['expense', 'income'] as $type)
					<div>
						<h4 class="text-xs font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-2 mt-2">
							{{ __('cat.type.' . $type) }}
						</h4>
						<ul class="space-y-0.5 text-sm">
							@foreach ($defaults->where('type', $type) as $cat)
								<li class="text-[color:var(--color-ink-muted)] flex justify-between py-1 border-b border-[color:var(--color-line)]/30">
									<span>{{ $cat->name }}</span>
									<span class="text-xs text-[color:var(--color-ink-soft)] tabular-nums">{{ $cat->sort_order }}</span>
								</li>
							@endforeach
						</ul>
					</div>
				@endforeach
			</div>
		</div>
	</div>
</section>

@endsection
