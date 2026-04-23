@extends('layouts.app')

@section('title', __('Acties') . ' — AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = __('Acties');
	$kindLabels = [
		'revoke_access' => __('Toegang intrekken'),
		'review_level' => __('Rechtenniveau herzien'),
	];
	$statusColors = [
		'open' => 'bg-amber-100 text-amber-800',
		'done' => 'bg-emerald-100 text-emerald-800',
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

		<div class="flex items-center gap-2 text-sm">
			<strong class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ __('Filter') }}:</strong>
			@foreach (['open' => __('Open'), 'done' => __('Afgerond'), 'cancelled' => __('Geannuleerd'), 'all' => __('Alles')] as $k => $l)
				<a href="{{ route('tools.accessguard.actions.index', ['locale' => $locale, 'status' => $k === 'all' ? '' : $k]) }}"
					class="px-3 py-1 rounded {{ $status === ($k === 'all' ? '' : $k) ? 'bg-[color:var(--color-accent)] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
					{{ $l }}
				</a>
			@endforeach
		</div>

		<div class="card p-0 overflow-hidden">
			@if ($actions->isEmpty())
				<p class="text-sm text-[color:var(--color-ink-muted)] p-6 text-center">{{ __('Geen acties in deze selectie.') }}</p>
			@else
				<table class="w-full text-sm">
					<thead class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] border-b border-[color:var(--color-line)]">
						<tr>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Titel') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Type') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Cyclus') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Status') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Aangemaakt') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Acties') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($actions as $a)
							<tr class="border-b border-[color:var(--color-line)]/60 hover:bg-[color:var(--color-surface-soft,#fafafa)]">
								<td class="py-2 px-3 font-semibold">
									{{ $a->title }}
									@if ($a->note)
										<div class="text-xs text-[color:var(--color-ink-muted)] mt-0.5">{{ $a->note }}</div>
									@endif
								</td>
								<td class="py-2 px-3 text-[color:var(--color-ink-muted)]">{{ $kindLabels[$a->kind] ?? $a->kind }}</td>
								<td class="py-2 px-3">
									@if ($a->cycle_id)
										<a href="{{ route('tools.accessguard.reviews.show', ['locale' => $locale, 'id' => $a->cycle_id]) }}" class="text-[color:var(--color-accent)] hover:underline">{{ $a->cycle?->title ?? '#' . $a->cycle_id }}</a>
									@elseif ($a->process_id)
										<a href="{{ route('tools.accessguard.processes.show', ['locale' => $locale, 'id' => $a->process_id]) }}" class="text-[color:var(--color-accent)] hover:underline">{{ __('Offboarding') }} #{{ $a->process_id }}</a>
									@else
										<span class="text-xs text-[color:var(--color-ink-muted)]">—</span>
									@endif
								</td>
								<td class="py-2 px-3">
									<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $statusColors[$a->status] }}">
										{{ __(ucfirst($a->status)) }}
									</span>
								</td>
								<td class="py-2 px-3 text-[color:var(--color-ink-muted)] tabular-nums">{{ $a->created_at->format('d-m-Y') }}</td>
								<td class="py-2 px-3 text-right whitespace-nowrap">
									@if ($a->status === 'open')
										<form method="POST" action="{{ route('tools.accessguard.actions.done', ['locale' => $locale, 'id' => $a->id]) }}" class="inline">
											@csrf
											<button type="submit" class="text-xs px-3 py-1 rounded bg-emerald-600 text-white hover:bg-emerald-700" title="{{ $a->kind === 'revoke_access' ? __('Uitvoeren: matrix cel → no_access') : __('Afronden') }}">
												{{ __('Afronden') }}
											</button>
										</form>
										<form method="POST" action="{{ route('tools.accessguard.actions.cancel', ['locale' => $locale, 'id' => $a->id]) }}" class="inline" onsubmit="return confirm('{{ __('Actie annuleren zonder uit te voeren?') }}');">
											@csrf
											<button type="submit" class="text-xs px-2 py-1 rounded text-slate-500 hover:bg-slate-100">{{ __('Annuleren') }}</button>
										</form>
									@else
										<span class="text-xs text-[color:var(--color-ink-muted)]">
											@if ($a->completed_at) {{ $a->completed_at->format('d-m-Y H:i') }} @endif
										</span>
									@endif
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			@endif
		</div>

		<div>{{ $actions->links() }}</div>
	</div>
</section>

@endsection
