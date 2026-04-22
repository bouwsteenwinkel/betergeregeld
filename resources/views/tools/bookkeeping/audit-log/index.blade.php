@extends('layouts.app')

@section('title', __('Logboek') . ' — ' . config('app.name'))

@php
	$locale = app()->getLocale();
	$fmtValue = function ($v) {
		if (is_bool($v)) {
			return $v ? '✓' : '✗';
		}
		if ($v === null || $v === '') {
			return '—';
		}
		if (is_array($v)) {
			return json_encode($v, JSON_UNESCAPED_UNICODE);
		}
		return (string) $v;
	};
@endphp

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1200px] mx-auto px-6 py-12">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-4 flex items-center gap-2">
			<a href="/{{ $locale }}/tools" class="hover:text-white">Tools</a>
			<span class="opacity-40">/</span>
			<a href="{{ route('tools.bookkeeping.index', ['locale' => $locale]) }}" class="hover:text-white">Boekhouden</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">{{ __('Logboek') }}</span>
		</nav>
		<h1 class="display-1">{{ __('Logboek') }}</h1>
		<p class="text-[color:var(--color-on-dark-muted)] mt-2 max-w-xl">
			{{ __('Elke wijziging aan transacties, relaties, categorieën of BTW-tarieven wordt automatisch vastgelegd.') }}
		</p>
	</div>
</section>

@include('tools.bookkeeping._subnav')

<section class="py-6">
	<div class="max-w-[1200px] mx-auto px-6 space-y-4">

		<form method="GET" class="card">
			<div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Entiteit') }}</label>
					<select name="entity_type" class="field-input py-1.5">
						<option value="" @selected(empty($filters['entity_type']))>— {{ __('alle') }} —</option>
						@foreach (['transaction', 'relation', 'category', 'vat_rate'] as $et)
							<option value="{{ $et }}" @selected(($filters['entity_type'] ?? '') === $et)>{{ __('audit.entity.' . $et) }}</option>
						@endforeach
					</select>
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Actie') }}</label>
					<select name="action" class="field-input py-1.5">
						<option value="" @selected(empty($filters['action']))>— {{ __('alle') }} —</option>
						@foreach (['created', 'updated', 'deleted'] as $a)
							<option value="{{ $a }}" @selected(($filters['action'] ?? '') === $a)>{{ __('audit.action.' . $a) }}</option>
						@endforeach
					</select>
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Van') }}</label>
					<input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="field-input py-1.5">
				</div>
				<div>
					<label class="block text-xs font-semibold mb-1">{{ __('Tot') }}</label>
					<input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="field-input py-1.5">
				</div>
			</div>
			<div class="mt-3 flex gap-2">
				<button type="submit" class="btn-accent text-sm">{{ __('Filter') }}</button>
				<a href="{{ route('tools.bookkeeping.audit-log.index', ['locale' => $locale]) }}" class="btn-dark text-sm">{{ __('Reset') }}</a>
			</div>
		</form>

		<div class="card">
			@if ($entries->isEmpty())
				<p class="text-sm text-[color:var(--color-ink-muted)] py-6 text-center">
					{{ __('Geen audit-entries voor dit filter.') }}
				</p>
			@else
				<ul class="space-y-1">
					@foreach ($entries as $entry)
						@php
							$actionClass = match ($entry->action) {
								'created' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
								'updated' => 'bg-amber-50 text-amber-900 border-amber-200',
								'deleted' => 'bg-red-50 text-red-800 border-red-200',
								default => 'bg-slate-50 text-slate-800 border-slate-200',
							};
						@endphp
						<li class="border-b border-[color:var(--color-line)]/60 last:border-b-0 py-3">
							<details class="group">
								<summary class="cursor-pointer flex items-center gap-3 list-none">
									<span class="inline-block text-[10px] uppercase tracking-wider font-bold px-2 py-1 rounded border {{ $actionClass }} min-w-[5rem] text-center">
										{{ __('audit.action.' . $entry->action) }}
									</span>
									<span class="text-sm font-medium">{{ __('audit.entity.' . $entry->entity_type) }}</span>
									<code class="text-xs text-[color:var(--color-ink-soft)] font-mono truncate max-w-[12rem]">{{ $entry->entity_id }}</code>
									<span class="flex-1"></span>
									<span class="text-xs text-[color:var(--color-ink-muted)]">
										{{ $entry->user_email ?? __('systeem') }}
									</span>
									<span class="text-xs text-[color:var(--color-ink-soft)] tabular-nums whitespace-nowrap">
										{{ $entry->created_at->format('d-m-Y H:i:s') }}
									</span>
									<svg class="w-3 h-3 text-[color:var(--color-ink-soft)] group-open:rotate-90 transition" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 3l4 3-4 3" stroke-linecap="round"/></svg>
								</summary>
								<div class="mt-3 pl-[5.5rem] text-xs">
									@if (empty($entry->changes))
										<p class="text-[color:var(--color-ink-soft)]">{{ __('Geen veranderingen vastgelegd.') }}</p>
									@else
										<table class="w-full font-mono">
											<thead>
												<tr class="text-left text-[color:var(--color-ink-soft)] border-b border-[color:var(--color-line)]/50">
													<th class="py-1 pr-3 font-semibold">{{ __('Veld') }}</th>
													@if ($entry->action === 'updated')
														<th class="py-1 px-3 font-semibold">{{ __('Was') }}</th>
														<th class="py-1 px-3 font-semibold">{{ __('Is') }}</th>
													@else
														<th class="py-1 px-3 font-semibold">{{ __('Waarde') }}</th>
													@endif
												</tr>
											</thead>
											<tbody>
												@foreach ($entry->changes as $field => $value)
													<tr>
														<td class="py-1 pr-3 text-[color:var(--color-ink-muted)]">{{ $field }}</td>
														@if ($entry->action === 'updated' && is_array($value) && array_key_exists('from', $value))
															<td class="py-1 px-3 text-red-700 line-through truncate max-w-[16rem]">{{ $fmtValue($value['from']) }}</td>
															<td class="py-1 px-3 text-emerald-700 truncate max-w-[16rem]">{{ $fmtValue($value['to']) }}</td>
														@else
															<td class="py-1 px-3 truncate max-w-[24rem]">{{ $fmtValue($value) }}</td>
														@endif
													</tr>
												@endforeach
											</tbody>
										</table>
									@endif
								</div>
							</details>
						</li>
					@endforeach
				</ul>
				<div class="mt-4">{{ $entries->links() }}</div>
			@endif
		</div>

		<p class="text-xs text-[color:var(--color-ink-soft)]">
			{{ __('IP-adressen worden versleuteld opgeslagen; de user-agent wordt afgekapt op 300 tekens.') }}
		</p>
	</div>
</section>

@endsection
