@extends('layouts.app')

@section('title', __('Reminders') . ' — AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = __('Reminders');
	$kindLabels = [
		'cycle_due' => __('Cyclus-deadline'),
		'process_due' => __('Proces-deadline'),
		'action_overdue' => __('Actie open'),
		'person_starting' => __('Start binnenkort'),
		'person_leaving' => __('Vertrekt binnenkort'),
	];
	$statusColors = [
		'open' => 'bg-amber-100 text-amber-800',
		'done' => 'bg-emerald-100 text-emerald-800',
		'dismissed' => 'bg-slate-200 text-slate-500',
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

		<div class="flex items-center justify-between flex-wrap gap-3">
			<div class="flex items-center gap-2 text-sm">
				<strong class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ __('Filter') }}:</strong>
				@foreach (['open' => __('Open'), 'done' => __('Klaar'), 'dismissed' => __('Weggeklikt'), 'all' => __('Alles')] as $k => $l)
					<a href="{{ route('tools.accessguard.reminders.index', ['locale' => $locale, 'status' => $k === 'all' ? '' : $k]) }}"
						class="px-3 py-1 rounded {{ $status === ($k === 'all' ? '' : $k) ? 'bg-[color:var(--color-accent)] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
						{{ $l }}
					</a>
				@endforeach
			</div>
			<form method="POST" action="{{ route('tools.accessguard.reminders.build', ['locale' => $locale]) }}">
				@csrf
				<button type="submit" class="btn-dark text-sm">{{ __('Opnieuw opbouwen') }}</button>
			</form>
		</div>

		<div class="card p-0 overflow-hidden">
			@if ($reminders->isEmpty())
				<p class="text-sm text-[color:var(--color-ink-muted)] p-6 text-center">{{ __('Geen reminders. Draai "Opnieuw opbouwen" om te scannen.') }}</p>
			@else
				<table class="w-full text-sm">
					<thead class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] border-b border-[color:var(--color-line)]">
						<tr>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Type') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Onderwerp') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Omschrijving') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Deadline') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Status') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Acties') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($reminders as $r)
							<tr class="border-b border-[color:var(--color-line)]/60 hover:bg-[color:var(--color-surface-soft,#fafafa)]">
								<td class="py-2 px-3 text-[color:var(--color-ink-muted)] whitespace-nowrap">{{ $kindLabels[$r->kind] ?? $r->kind }}</td>
								<td class="py-2 px-3 font-semibold">{{ $r->title }}</td>
								<td class="py-2 px-3 text-xs text-[color:var(--color-ink-muted)]">{{ $r->description }}</td>
								<td class="py-2 px-3 text-xs tabular-nums whitespace-nowrap">{{ $r->due_at?->format('d-m-Y') ?? '—' }}</td>
								<td class="py-2 px-3">
									<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $statusColors[$r->status] ?? '' }}">
										{{ __(ucfirst($r->status)) }}
									</span>
								</td>
								<td class="py-2 px-3 text-right whitespace-nowrap">
									@if ($r->status === 'open')
										<form method="POST" action="{{ route('tools.accessguard.reminders.done', ['locale' => $locale, 'id' => $r->id]) }}" class="inline">
											@csrf
											<button type="submit" class="text-xs px-3 py-1 rounded bg-emerald-600 text-white hover:bg-emerald-700">{{ __('Klaar') }}</button>
										</form>
										<form method="POST" action="{{ route('tools.accessguard.reminders.dismiss', ['locale' => $locale, 'id' => $r->id]) }}" class="inline">
											@csrf
											<button type="submit" class="text-xs px-2 py-1 rounded text-slate-500 hover:bg-slate-100">{{ __('Weg') }}</button>
										</form>
									@endif
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			@endif
		</div>

		<div>{{ $reminders->links() }}</div>
	</div>
</section>

@endsection
