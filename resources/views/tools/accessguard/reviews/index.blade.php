@extends('layouts.app')

@section('title', __('Reviews') . ' — AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = __('Reviews');
	$statusColors = [
		'planned' => 'bg-slate-100 text-slate-700',
		'active' => 'bg-emerald-100 text-emerald-800',
		'completed' => 'bg-blue-100 text-blue-800',
		'cancelled' => 'bg-slate-200 text-slate-500',
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

		<div class="flex items-center justify-between">
			<h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ __('Review-cycli') }}</h2>
			<a href="{{ route('tools.accessguard.reviews.create', ['locale' => $locale]) }}" class="btn-accent text-sm">{{ __('+ Nieuwe cyclus') }}</a>
		</div>

		<div class="card p-0 overflow-hidden">
			@if ($cycles->isEmpty())
				<p class="text-sm text-[color:var(--color-ink-muted)] p-6 text-center">{{ __('Nog geen review-cycli. Start een cyclus om te beginnen.') }}</p>
			@else
				<table class="w-full text-sm">
					<thead class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] border-b border-[color:var(--color-line)]">
						<tr>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Titel') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Status') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Gestart') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Deadline') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Voortgang') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Acties') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($cycles as $c)
							@php
								$pct = $c->items_count > 0 ? round(($c->decided_count / $c->items_count) * 100) : 0;
							@endphp
							<tr class="border-b border-[color:var(--color-line)]/60 hover:bg-[color:var(--color-surface-soft,#fafafa)]">
								<td class="py-2 px-3 font-semibold">
									<a href="{{ route('tools.accessguard.reviews.show', ['locale' => $locale, 'id' => $c->id]) }}" class="hover:underline">{{ $c->title }}</a>
								</td>
								<td class="py-2 px-3">
									<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $statusColors[$c->status] ?? 'bg-slate-100' }}">
										{{ __(ucfirst($c->status)) }}
									</span>
								</td>
								<td class="py-2 px-3 text-[color:var(--color-ink-muted)] tabular-nums">{{ $c->starts_at?->format('d-m-Y') ?? '—' }}</td>
								<td class="py-2 px-3 text-[color:var(--color-ink-muted)] tabular-nums">{{ $c->due_at?->format('d-m-Y') ?? '—' }}</td>
								<td class="py-2 px-3 text-right tabular-nums">
									<span class="text-xs text-[color:var(--color-ink-muted)]">{{ $c->decided_count }} / {{ $c->items_count }}</span>
									<div class="inline-block w-24 h-1.5 bg-slate-200 rounded overflow-hidden align-middle ml-2">
										<div class="h-full bg-[color:var(--color-accent)]" style="width:{{ $pct }}%"></div>
									</div>
								</td>
								<td class="py-2 px-3 text-right text-xs">
									@if ($c->open_actions_count > 0)
										<span class="text-amber-700 font-semibold">{{ $c->open_actions_count }} {{ __('open') }}</span>
									@else
										<span class="text-[color:var(--color-ink-muted)]">{{ __('geen open acties') }}</span>
									@endif
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			@endif
		</div>

		<div>{{ $cycles->links() }}</div>
	</div>
</section>

@endsection
