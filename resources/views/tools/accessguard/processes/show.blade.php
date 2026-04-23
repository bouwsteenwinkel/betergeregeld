@extends('layouts.app')

@section('title', $process->title . ' — AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = $process->title;
	$statusColors = [
		'active' => 'bg-emerald-100 text-emerald-800',
		'completed' => 'bg-blue-100 text-blue-800',
		'cancelled' => 'bg-slate-200 text-slate-500',
	];
	$itemColors = [
		'todo' => 'bg-slate-100 text-slate-600 border-slate-200',
		'in_progress' => 'bg-amber-100 text-amber-800 border-amber-300',
		'done' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
		'blocked' => 'bg-red-100 text-red-800 border-red-300',
		'na' => 'bg-slate-200 text-slate-500 border-slate-300',
	];
	$hasUnfinished = $counts['todo'] + $counts['in_progress'] > 0;
	$fmtSize = fn ($b) => $b < 1024 ? $b . ' B' : ($b < 1048576 ? round($b / 1024) . ' KB' : round($b / 1048576, 1) . ' MB');
@endphp

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[1400px] mx-auto px-6 space-y-4">
		@if (session('status'))
			<div class="card text-sm bg-emerald-50 border-emerald-200 text-emerald-900">{{ session('status') }}</div>
		@endif
		@if (session('error'))
			<div class="card text-sm bg-red-50 border-red-200 text-red-900">{{ session('error') }}</div>
		@endif

		<div class="card">
			<div class="flex items-start gap-4 flex-wrap">
				<div class="flex-1 min-w-[300px]">
					<div class="flex items-center gap-3 mb-1">
						<h2 class="text-xl font-bold">{{ $process->title }}</h2>
						<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $statusColors[$process->status] }}">
							{{ __(ucfirst($process->status)) }}
						</span>
						<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700">
							{{ __(ucfirst($process->kind)) }}
						</span>
					</div>
					<div class="text-xs text-[color:var(--color-ink-muted)]">
						{{ __('Persoon') }}: <strong>{{ $process->person?->full_name }}</strong>
						@if ($process->started_at) · {{ __('gestart') }} {{ $process->started_at->format('d-m-Y') }} @endif
						@if ($process->due_at) · {{ __('deadline') }} {{ $process->due_at->format('d-m-Y') }} @endif
						@if ($process->completed_at) · {{ __('afgerond') }} {{ $process->completed_at->format('d-m-Y H:i') }} @endif
					</div>
					@if ($process->notes)<p class="text-sm text-[color:var(--color-ink-soft)] mt-2">{{ $process->notes }}</p>@endif
				</div>

				@if ($process->isOpen())
					<form method="POST" action="{{ route('tools.accessguard.processes.cancel', ['locale' => $locale, 'id' => $process->id]) }}" onsubmit="return confirm('{{ __('Proces annuleren?') }}');">
						@csrf
						<button type="submit" class="text-sm py-2 px-3 rounded border border-slate-300 text-slate-600 hover:bg-slate-50">{{ __('Annuleren') }}</button>
					</form>
				@endif
			</div>

			<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mt-4 text-sm">
				<div><div class="text-xs text-[color:var(--color-ink-muted)] uppercase tracking-wider font-bold">{{ __('Te doen') }}</div><div class="text-xl font-bold tabular-nums">{{ $counts['todo'] }}</div></div>
				<div><div class="text-xs text-amber-700 uppercase tracking-wider font-bold">{{ __('Bezig') }}</div><div class="text-xl font-bold tabular-nums text-amber-700">{{ $counts['in_progress'] }}</div></div>
				<div><div class="text-xs text-emerald-700 uppercase tracking-wider font-bold">{{ __('Klaar') }}</div><div class="text-xl font-bold tabular-nums text-emerald-700">{{ $counts['done'] }}</div></div>
				<div><div class="text-xs text-red-700 uppercase tracking-wider font-bold">{{ __('Geblokkeerd') }}</div><div class="text-xl font-bold tabular-nums text-red-700">{{ $counts['blocked'] }}</div></div>
				<div><div class="text-xs text-[color:var(--color-ink-muted)] uppercase tracking-wider font-bold">{{ __('N.v.t.') }}</div><div class="text-xl font-bold tabular-nums">{{ $counts['na'] }}</div></div>
			</div>
		</div>

		@if ($process->isOpen())
			<div class="card">
				<form method="POST" action="{{ route('tools.accessguard.processes.complete', ['locale' => $locale, 'id' => $process->id]) }}" class="flex items-center justify-between flex-wrap gap-3" onsubmit="return confirm('{{ __('Proces afronden?') }} @if ($process->kind === 'offboarding'){{ __('Dit maakt revoke-acties voor alle has_access cellen van deze persoon.') }}@endif');">
					@csrf
					<strong class="text-sm">{{ __('Proces afronden') }}</strong>
					<span class="text-xs text-[color:var(--color-ink-muted)]">
						@if ($hasUnfinished)
							{{ __('Nog :n open items — mark ze als done, blocked of n.v.t. eerst.', ['n' => $counts['todo'] + $counts['in_progress']]) }}
						@else
							@if ($process->kind === 'offboarding')
								{{ __('Bij afronden worden alle has_access cellen van deze persoon omgezet in revoke-acties.') }}
							@else
								{{ __('Klaar om af te ronden.') }}
							@endif
						@endif
					</span>
					<button type="submit" class="btn-accent text-sm" @disabled($hasUnfinished)>{{ __('Afronden') }}</button>
				</form>
			</div>
		@endif

		<div class="space-y-3">
			@foreach ($process->items as $item)
				<div class="card">
					<div class="flex items-start gap-4 flex-wrap">
						<div class="flex-1 min-w-[300px]">
							<div class="flex items-center gap-2 mb-1">
								<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border {{ $itemColors[$item->status] ?? '' }}">
									{{ __(str_replace('_', ' ', ucfirst($item->status))) }}
								</span>
								<strong>{{ $item->title }}</strong>
								@if ($item->requires_evidence)
									<span class="text-xs text-amber-700">({{ __('bewijs vereist') }})</span>
								@endif
							</div>
							@if ($item->description)<p class="text-sm text-[color:var(--color-ink-soft)]">{{ $item->description }}</p>@endif
							@if ($item->status_reason)
								<p class="text-xs text-[color:var(--color-ink-muted)] mt-1">{{ __('Reden') }}: {{ $item->status_reason }}</p>
							@endif
						</div>

						@if ($process->isOpen())
							<form method="POST" action="{{ route('tools.accessguard.processes.update-item', ['locale' => $locale, 'id' => $process->id, 'itemId' => $item->id]) }}" class="flex items-center gap-2 text-sm">
								@csrf
								<select name="status" class="field-input py-1 text-xs" style="width:auto">
									@foreach (['todo' => __('Te doen'), 'in_progress' => __('Bezig'), 'done' => __('Klaar'), 'blocked' => __('Geblokkeerd'), 'na' => __('N.v.t.')] as $k => $l)
										<option value="{{ $k }}" @selected($item->status === $k)>{{ $l }}</option>
									@endforeach
								</select>
								<input type="text" name="reason" placeholder="{{ __('Reden (bij geblokkeerd / n.v.t.)') }}" class="field-input py-1 text-xs" style="min-width:180px">
								<button type="submit" class="text-xs px-3 py-1 rounded bg-slate-800 text-white hover:bg-slate-700">{{ __('Bijwerken') }}</button>
							</form>
						@endif
					</div>

					@if ($item->evidence->isNotEmpty() || $process->isOpen())
						<div class="mt-4 border-t border-[color:var(--color-line)] pt-3">
							<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold mb-2">{{ __('Bewijs') }}</div>
							@foreach ($item->evidence as $ev)
								<div class="flex items-center gap-3 text-sm py-1">
									<span class="inline-flex items-center gap-1 text-xs">
										<svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path d="M4 4a2 2 0 0 1 2-2h5l5 5v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4z"/></svg>
										<a href="{{ route('tools.accessguard.processes.download-evidence', ['locale' => $locale, 'id' => $process->id, 'evidenceId' => $ev->id]) }}" class="font-semibold text-[color:var(--color-accent)] hover:underline">{{ $ev->original_name }}</a>
									</span>
									<span class="text-xs text-[color:var(--color-ink-muted)] tabular-nums">{{ $fmtSize($ev->size_bytes) }}</span>
									<span class="text-xs text-[color:var(--color-ink-muted)]">{{ $ev->uploaded_at->format('d-m-Y H:i') }}</span>
									@if ($process->isOpen())
										<form method="POST" action="{{ route('tools.accessguard.processes.delete-evidence', ['locale' => $locale, 'id' => $process->id, 'evidenceId' => $ev->id]) }}" class="inline" onsubmit="return confirm('{{ __('Bewijs verwijderen?') }}');">
											@csrf
											@method('DELETE')
											<button type="submit" class="text-xs text-red-600 hover:underline">{{ __('verwijder') }}</button>
										</form>
									@endif
								</div>
							@endforeach
							@if ($process->isOpen())
								<form method="POST" action="{{ route('tools.accessguard.processes.upload-evidence', ['locale' => $locale, 'id' => $process->id, 'itemId' => $item->id]) }}" enctype="multipart/form-data" class="flex items-center gap-2 mt-2">
									@csrf
									<input type="file" name="file" accept=".pdf,.png,.jpg,.jpeg" required class="text-xs">
									<button type="submit" class="text-xs px-3 py-1 rounded bg-slate-200 hover:bg-slate-300">{{ __('Upload PDF / JPG / PNG') }}</button>
									<span class="text-xs text-[color:var(--color-ink-muted)]">{{ __('max 15 MB') }}</span>
								</form>
							@endif
						</div>
					@endif
				</div>
			@endforeach
		</div>
	</div>
</section>

@endsection
