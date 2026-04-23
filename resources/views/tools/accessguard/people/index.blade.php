@extends('layouts.app')

@section('title', __('Personen') . ' — AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = __('Personen');
	$statusLabels = [
		'active' => __('Actief'),
		'scheduled_in' => __('Start gepland'),
		'scheduled_out' => __('Uitdienst gepland'),
		'inactive' => __('Inactief'),
	];
	$statusColors = [
		'active' => 'bg-emerald-100 text-emerald-800',
		'scheduled_in' => 'bg-blue-100 text-blue-800',
		'scheduled_out' => 'bg-amber-100 text-amber-800',
		'inactive' => 'bg-slate-200 text-slate-600',
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
			<form method="GET" class="flex gap-2 items-center">
				<input type="text" name="q" value="{{ $q }}" placeholder="{{ __('Zoek op naam, e-mail of functie…') }}" class="field-input py-1.5" style="min-width:280px">
				<select name="status" class="field-input py-1.5" onchange="this.form.submit()">
					<option value="">{{ __('Alle statussen') }}</option>
					@foreach ($statusLabels as $k => $l)
						<option value="{{ $k }}" @selected($status === $k)>{{ $l }}</option>
					@endforeach
				</select>
				<button type="submit" class="btn-dark text-sm">{{ __('Filteren') }}</button>
			</form>
			<a href="{{ route('tools.accessguard.people.create', ['locale' => $locale]) }}" class="btn-accent text-sm">{{ __('+ Nieuwe persoon') }}</a>
		</div>

		<div class="card p-0 overflow-hidden">
			@if ($people->isEmpty())
				<p class="text-sm text-[color:var(--color-ink-muted)] p-6 text-center">{{ __('Nog geen personen. Voeg er één toe om te beginnen.') }}</p>
			@else
				<table class="w-full text-sm">
					<thead class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] border-b border-[color:var(--color-line)]">
						<tr>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Naam') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Functie') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Afdeling') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('E-mail') }}</th>
							<th class="text-left py-2 px-3 font-semibold">{{ __('Status') }}</th>
							<th class="text-right py-2 px-3 font-semibold">{{ __('Acties') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($people as $p)
							<tr class="border-b border-[color:var(--color-line)]/60 hover:bg-[color:var(--color-surface-soft,#fafafa)]">
								<td class="py-2 px-3 font-semibold">{{ $p->full_name }}</td>
								<td class="py-2 px-3 text-[color:var(--color-ink-muted)]">{{ $p->job_title ?: '—' }}</td>
								<td class="py-2 px-3 text-[color:var(--color-ink-muted)]">{{ $p->department ?: '—' }}</td>
								<td class="py-2 px-3 text-[color:var(--color-ink-muted)]">{{ $p->email ?: '—' }}</td>
								<td class="py-2 px-3">
									<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $statusColors[$p->status] ?? 'bg-slate-100 text-slate-700' }}">
										{{ $statusLabels[$p->status] ?? $p->status }}
									</span>
								</td>
								<td class="py-2 px-3 text-right">
									<a href="{{ route('tools.accessguard.people.edit', ['locale' => $locale, 'id' => $p->id]) }}" class="text-[color:var(--color-accent)] font-semibold hover:underline text-xs">{{ __('Bewerken') }}</a>
									<form method="POST" action="{{ route('tools.accessguard.people.destroy', ['locale' => $locale, 'id' => $p->id]) }}" class="inline" onsubmit="return confirm('{{ __('Weet je zeker dat je deze persoon wilt archiveren?') }}');">
										@csrf
										@method('DELETE')
										<button type="submit" class="text-red-600 font-semibold hover:underline text-xs ml-2">{{ __('Archiveren') }}</button>
									</form>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			@endif
		</div>

		<div>{{ $people->links() }}</div>
	</div>
</section>

@endsection
