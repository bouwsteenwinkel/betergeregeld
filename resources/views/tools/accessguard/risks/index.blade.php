@extends('layouts.app')

@section('title', __('Risico\'s') . ' — AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = __('Risico\'s');
	$kindLabels = [
		'stale_admin' => __('Stale admin'),
		'orphan_access' => __('Orphan access'),
		'excessive_access' => __('Excessive access'),
		'overdue_review' => __('Overdue review'),
		'overdue_action' => __('Overdue action'),
		'pending_onboarding' => __('Onboarding vergeten'),
	];
	$statusColors = [
		'open' => 'bg-red-100 text-red-800',
		'acknowledged' => 'bg-amber-100 text-amber-800',
		'resolved' => 'bg-emerald-100 text-emerald-800',
	];
	$severityBar = fn ($s) => '<span class="inline-flex gap-0.5">' . str_repeat('<span class="w-1.5 h-3 bg-red-500 rounded"></span>', (int) $s) . str_repeat('<span class="w-1.5 h-3 bg-slate-200 rounded"></span>', 5 - (int) $s) . '</span>';
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
				@foreach (['open' => __('Open'), 'acknowledged' => __('Bevestigd'), 'resolved' => __('Opgelost'), 'all' => __('Alles')] as $k => $l)
					<a href="{{ route('tools.accessguard.risks.index', ['locale' => $locale, 'status' => $k === 'all' ? '' : $k]) }}"
						class="px-3 py-1 rounded {{ $status === ($k === 'all' ? '' : $k) ? 'bg-[color:var(--color-accent)] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
						{{ $l }}
					</a>
				@endforeach
			</div>
			<form method="POST" action="{{ route('tools.accessguard.risks.scan', ['locale' => $locale]) }}">
				@csrf
				<button type="submit" class="btn-dark text-sm">{{ __('Scan nu') }}</button>
			</form>
		</div>

		<div class="card p-0 overflow-hidden">
			@if ($flags->isEmpty())
				<p class="text-sm text-[color:var(--color-ink-muted)] p-6 text-center">{{ __('Geen risico\'s gedetecteerd in deze selectie. Draai "Scan nu" om te checken.') }}</p>
			@else
				<table class="w-full text-sm">
					<thead class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] border-b border-[color:var(--color-line)]">
						<tr>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Ernst') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Type') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Onderwerp') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Omschrijving') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Status') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Gedetecteerd') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Acties') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($flags as $f)
							<tr class="border-b border-[color:var(--color-line)]/60 hover:bg-[color:var(--color-surface-soft,#fafafa)]">
								<td class="py-2 px-3">{!! $severityBar($f->severity) !!}</td>
								<td class="py-2 px-3 text-[color:var(--color-ink-muted)] whitespace-nowrap">{{ $kindLabels[$f->kind] ?? $f->kind }}</td>
								<td class="py-2 px-3 font-semibold">{{ $f->title }}</td>
								<td class="py-2 px-3 text-xs text-[color:var(--color-ink-muted)]">{{ $f->description }}</td>
								<td class="py-2 px-3">
									<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $statusColors[$f->status] ?? '' }}">
										{{ __(ucfirst($f->status)) }}
									</span>
								</td>
								<td class="py-2 px-3 text-xs text-[color:var(--color-ink-muted)] tabular-nums whitespace-nowrap">{{ $f->detected_at->format('d-m H:i') }}</td>
								<td class="py-2 px-3 text-right whitespace-nowrap">
									@if ($f->status === 'open')
										<form method="POST" action="{{ route('tools.accessguard.risks.acknowledge', ['locale' => $locale, 'id' => $f->id]) }}" class="inline">
											@csrf
											<button type="submit" class="text-xs px-3 py-1 rounded bg-amber-100 text-amber-800 hover:bg-amber-200">{{ __('Bevestig') }}</button>
										</form>
									@endif
									@if ($f->status !== 'resolved')
										<form method="POST" action="{{ route('tools.accessguard.risks.resolve', ['locale' => $locale, 'id' => $f->id]) }}" class="inline">
											@csrf
											<button type="submit" class="text-xs px-3 py-1 rounded bg-emerald-600 text-white hover:bg-emerald-700">{{ __('Opgelost') }}</button>
										</form>
									@else
										<form method="POST" action="{{ route('tools.accessguard.risks.reopen', ['locale' => $locale, 'id' => $f->id]) }}" class="inline">
											@csrf
											<button type="submit" class="text-xs px-2 py-1 rounded text-slate-500 hover:bg-slate-100">{{ __('Heropen') }}</button>
										</form>
									@endif
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			@endif
		</div>

		<div>{{ $flags->links() }}</div>
	</div>
</section>

@endsection
