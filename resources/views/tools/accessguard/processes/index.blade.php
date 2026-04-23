@extends('layouts.app')

@section('title', __('Processen') . ' — AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = __('Processen');
	$kindLabels = ['onboarding' => __('Onboarding'), 'offboarding' => __('Offboarding')];
	$statusColors = [
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

		<div class="flex items-center gap-3 flex-wrap">
			<div class="flex items-center gap-2 text-sm">
				<strong class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ __('Type') }}:</strong>
				@foreach (['' => __('Alles'), 'onboarding' => __('Onboarding'), 'offboarding' => __('Offboarding')] as $k => $l)
					<a href="{{ route('tools.accessguard.processes.index', ['locale' => $locale, 'kind' => $k, 'status' => $status]) }}"
						class="px-3 py-1 rounded {{ $kind === $k ? 'bg-[color:var(--color-accent)] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
						{{ $l }}
					</a>
				@endforeach
			</div>
			<a href="{{ route('tools.accessguard.processes.create', ['locale' => $locale]) }}" class="btn-accent text-sm ml-auto">{{ __('+ Nieuw proces') }}</a>
		</div>

		<div class="card p-0 overflow-hidden">
			@if ($processes->isEmpty())
				<p class="text-sm text-[color:var(--color-ink-muted)] p-6 text-center">{{ __('Nog geen processen. Start er één voor een onboarding of offboarding.') }}</p>
			@else
				<table class="w-full text-sm">
					<thead class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] border-b border-[color:var(--color-line)]">
						<tr>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Persoon') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Type') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Status') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Gestart') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Deadline') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Voortgang') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($processes as $p)
							@php $pct = $p->items_count > 0 ? round(($p->done_count / $p->items_count) * 100) : 0; @endphp
							<tr class="border-b border-[color:var(--color-line)]/60 hover:bg-[color:var(--color-surface-soft,#fafafa)]">
								<td class="py-2 px-3 font-semibold">
									<a href="{{ route('tools.accessguard.processes.show', ['locale' => $locale, 'id' => $p->id]) }}" class="hover:underline">
										{{ $p->person?->full_name ?? '—' }}
									</a>
								</td>
								<td class="py-2 px-3 text-[color:var(--color-ink-muted)]">{{ $kindLabels[$p->kind] ?? $p->kind }}</td>
								<td class="py-2 px-3">
									<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $statusColors[$p->status] ?? '' }}">
										{{ __(ucfirst($p->status)) }}
									</span>
								</td>
								<td class="py-2 px-3 text-[color:var(--color-ink-muted)] tabular-nums">{{ $p->started_at?->format('d-m-Y') ?? '—' }}</td>
								<td class="py-2 px-3 text-[color:var(--color-ink-muted)] tabular-nums">{{ $p->due_at?->format('d-m-Y') ?? '—' }}</td>
								<td class="py-2 px-3 text-right tabular-nums">
									<span class="text-xs text-[color:var(--color-ink-muted)]">{{ $p->done_count }} / {{ $p->items_count }}</span>
									<div class="inline-block w-20 h-1.5 bg-slate-200 rounded overflow-hidden align-middle ml-2">
										<div class="h-full bg-[color:var(--color-accent)]" style="width:{{ $pct }}%"></div>
									</div>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			@endif
		</div>

		<div>{{ $processes->links() }}</div>
	</div>
</section>

@endsection
