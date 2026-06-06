@extends('layouts.app')

@section('title', __('Factuur') . ' ' . $invoice->invoice_number . ', ' . config('app.name'))

@php
	$locale = app()->getLocale();
	$fmt = fn ($v) => '€' . number_format((float) $v, 2, ',', '.');
	$statusClass = fn ($s) => match ($s) {
		'draft' => 'bg-slate-100 text-slate-700 border-slate-200',
		'sent' => 'bg-amber-50 text-amber-900 border-amber-200',
		'paid' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
		'cancelled' => 'bg-red-50 text-red-700 border-red-200',
	};
@endphp

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1100px] mx-auto px-6 py-10">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-4 flex items-center gap-2">
			<a href="{{ route('tools.bookkeeping.invoices.index', ['locale' => $locale]) }}" class="hover:text-white">{{ __('Facturen') }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">{{ $invoice->invoice_number }}</span>
		</nav>
		<div class="flex items-start justify-between gap-6 flex-wrap">
			<div>
				<span class="pill border {{ $statusClass($invoice->status) }} text-[10px] uppercase tracking-wider mb-3">{{ __('invoice.status.' . $invoice->status) }}</span>
				<h1 class="display-1 font-mono">{{ $invoice->invoice_number }}</h1>
				<p class="text-[color:var(--color-on-dark-muted)] mt-2">
					{{ $invoice->relation?->name }} · {{ $invoice->issue_date->format('d-m-Y') }}
				</p>
			</div>
			<div class="flex gap-2 flex-wrap">
				<a href="{{ route('tools.bookkeeping.invoices.pdf', ['locale' => $locale, 'id' => $invoice->id]) }}" class="btn-accent text-sm">
					{{ __('Download PDF') }}
				</a>
				@if ($invoice->isEditable())
					<a href="{{ route('tools.bookkeeping.invoices.edit', ['locale' => $locale, 'id' => $invoice->id]) }}" class="btn-dark text-sm">
						{{ __('Bewerken') }}
					</a>
				@endif
			</div>
		</div>
	</div>
</section>

<section class="py-6">
	<div class="max-w-[1100px] mx-auto px-6 space-y-4">
		@if (session('bookkeeping_message'))
			<div class="rounded-[var(--radius-control)] border border-emerald-200 bg-emerald-50 text-emerald-900 p-3 text-sm">
				{{ session('bookkeeping_message') }}
			</div>
		@endif
		@if ($errors->any())
			<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">{{ $errors->first() }}</div>
		@endif

		<div class="flex gap-2 flex-wrap">
			@if ($invoice->status === 'draft')
				<form method="POST" action="{{ route('tools.bookkeeping.invoices.mark-sent', ['locale' => $locale, 'id' => $invoice->id]) }}" class="inline">
					@csrf
					<button type="submit" class="btn-dark text-sm">{{ __('Markeer als verzonden') }}</button>
				</form>
			@endif
			@if (in_array($invoice->status, ['draft', 'sent'], true))
				<form method="POST" action="{{ route('tools.bookkeeping.invoices.mark-paid', ['locale' => $locale, 'id' => $invoice->id]) }}" class="inline">
					@csrf
					<button type="submit" class="btn-accent text-sm">{{ __('Markeer als betaald') }}</button>
				</form>
			@endif
			@if ($invoice->status !== 'cancelled')
				@php
					$autoTxCount = $invoice->transactions->where('import_source', 'invoice')->count();
					$cancelConfirm = $invoice->status === 'paid' && $autoTxCount > 0
						? __('Deze factuur is op betaald gezet. Annuleren verwijdert ook :n automatisch aangemaakte transactie(s). Doorgaan?', ['n' => $autoTxCount])
						: __('Factuur annuleren?');
				@endphp
				<form method="POST" action="{{ route('tools.bookkeeping.invoices.mark-cancelled', ['locale' => $locale, 'id' => $invoice->id]) }}" class="inline"
					onsubmit="return confirm('{{ $cancelConfirm }}')">
					@csrf
					<button type="submit" class="text-sm text-red-600 hover:text-red-800 px-3 py-2">{{ __('Annuleer factuur') }}</button>
				</form>
			@endif
			@if ($invoice->isEditable())
				<form method="POST" action="{{ route('tools.bookkeeping.invoices.destroy', ['locale' => $locale, 'id' => $invoice->id]) }}" class="inline ml-auto"
					onsubmit="return confirm('{{ __('Factuur permanent verwijderen?') }}')">
					@csrf @method('DELETE')
					<button type="submit" class="text-sm text-red-600 hover:text-red-800 px-3 py-2">{{ __('Verwijderen') }}</button>
				</form>
			@endif
		</div>

		<div class="card">
			<div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
				<div>
					<h3 class="text-xs font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-2">{{ __('Van') }}</h3>
					@if ($settings)
						<p class="text-sm font-medium">{{ $settings->company_name ?? ', ' }}</p>
						@if ($settings->address || $settings->city)
							<p class="text-sm text-[color:var(--color-ink-muted)]">
								{{ $settings->address }}<br>
								{{ trim(($settings->postal_code ?? '') . ' ' . ($settings->city ?? '')) }}
							</p>
						@endif
					@else
						<p class="text-sm text-amber-800">
							{{ __('Afzender-gegevens ontbreken.') }}
							<a href="{{ route('tools.bookkeeping.settings.edit', ['locale' => $locale]) }}" class="underline">{{ __('Vul in bij Instellingen') }}</a>.
						</p>
					@endif
				</div>
				<div>
					<h3 class="text-xs font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-2">{{ __('Aan') }}</h3>
					<p class="text-sm font-medium">{{ $invoice->relation?->name }}</p>
					@if ($invoice->relation?->address || $invoice->relation?->city)
						<p class="text-sm text-[color:var(--color-ink-muted)]">
							{{ $invoice->relation?->address }}<br>
							{{ trim(($invoice->relation?->postal_code ?? '') . ' ' . ($invoice->relation?->city ?? '')) }}
						</p>
					@endif
				</div>
			</div>

			<table class="w-full text-sm mb-4">
				<thead>
					<tr class="border-b-2 border-[color:var(--color-line)]">
						<th class="text-left py-2 pr-3 font-semibold">{{ __('Omschrijving') }}</th>
						<th class="text-right py-2 px-3 font-semibold">{{ __('Aantal') }}</th>
						<th class="text-right py-2 px-3 font-semibold">{{ __('Prijs') }}</th>
						<th class="text-right py-2 px-3 font-semibold">{{ __('BTW') }}</th>
						<th class="text-right py-2 pl-3 font-semibold">{{ __('Totaal') }}</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($invoice->lines as $line)
						<tr class="border-b border-[color:var(--color-line)]/60">
							<td class="py-2 pr-3">{{ $line->description }}</td>
							<td class="py-2 px-3 text-right tabular-nums">{{ rtrim(rtrim(number_format((float) $line->quantity, 2, ',', ''), '0'), ',') }}</td>
							<td class="py-2 px-3 text-right tabular-nums">{{ $fmt($line->unit_price) }}</td>
							<td class="py-2 px-3 text-right tabular-nums text-[color:var(--color-ink-muted)]">
								{{ $line->vatRate ? rtrim(rtrim(number_format((float) $line->vatRate->rate, 2, ',', ''), '0'), ',') . '%' : ', ' }}
							</td>
							<td class="py-2 pl-3 text-right tabular-nums font-medium">{{ $fmt($line->lineNet()) }}</td>
						</tr>
					@endforeach
				</tbody>
				<tfoot>
					<tr>
						<td colspan="4" class="py-2 pr-3 text-right text-[color:var(--color-ink-muted)]">{{ __('Subtotaal (excl. BTW)') }}</td>
						<td class="py-2 pl-3 text-right tabular-nums">{{ $fmt($invoice->subtotal) }}</td>
					</tr>
					<tr>
						<td colspan="4" class="py-2 pr-3 text-right text-[color:var(--color-ink-muted)]">{{ __('BTW') }}</td>
						<td class="py-2 pl-3 text-right tabular-nums">{{ $fmt($invoice->vat_amount) }}</td>
					</tr>
					<tr class="border-t-2 border-[color:var(--color-line)]">
						<td colspan="4" class="py-3 pr-3 text-right font-bold">{{ __('Totaal') }}</td>
						<td class="py-3 pl-3 text-right tabular-nums font-bold text-lg">{{ $fmt($invoice->total) }}</td>
					</tr>
				</tfoot>
			</table>

			@if ($invoice->notes)
				<div class="text-sm text-[color:var(--color-ink-muted)] mt-4 pt-4 border-t border-[color:var(--color-line)]">
					<span class="font-semibold text-[color:var(--color-ink)]">{{ __('Opmerkingen') }}:</span>
					{{ $invoice->notes }}
				</div>
			@endif
		</div>

		@if (in_array($invoice->status, ['sent'], true))
			@php
				$reminderKinds = ['pre_due', 'due', 'overdue_7', 'overdue_21'];
				$sentKinds = $reminders->pluck('kind')->all();
			@endphp
			<div class="card">
				<div class="flex items-center justify-between gap-4 mb-3 flex-wrap">
					<h3 class="text-xs font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)]">
						{{ __('Herinneringen') }}
					</h3>
					<span class="text-xs text-[color:var(--color-ink-soft)]">
						{{ $invoice->relation?->email ?: __('relatie heeft geen e-mail') }}
					</span>
				</div>

				@if ($reminders->isEmpty())
					<p class="text-sm text-[color:var(--color-ink-muted)]">
						{{ __('Nog geen herinneringen verstuurd.') }}
					</p>
				@else
					<ul class="text-sm space-y-1 mb-3">
						@foreach ($reminders as $r)
							<li class="flex items-center gap-2 text-[color:var(--color-ink-muted)]">
								<span class="pill pill-ink text-[10px]">{{ __('reminder.' . $r->kind) }}</span>
								<span class="tabular-nums text-xs">{{ $r->sent_at->format('d-m-Y H:i') }}</span>
								<span class="text-xs">→ {{ $r->sent_to_email }}</span>
								@if ($r->was_manual)<span class="text-xs text-[color:var(--color-ink-soft)]">({{ __('handmatig') }})</span>@endif
							</li>
						@endforeach
					</ul>
				@endif

				@if ($invoice->relation?->email)
					<div class="flex flex-wrap gap-2">
						@foreach ($reminderKinds as $kind)
							@if (! in_array($kind, $sentKinds, true))
								<form method="POST" action="{{ route('tools.bookkeeping.invoices.send-reminder', ['locale' => $locale, 'id' => $invoice->id]) }}" class="inline">
									@csrf
									<input type="hidden" name="kind" value="{{ $kind }}">
									<button type="submit" class="btn-dark text-xs">
										{{ __('Stuur') }} {{ __('reminder.' . $kind) }}
									</button>
								</form>
							@endif
						@endforeach
					</div>
				@endif
			</div>
		@endif

		@if ($invoice->transactions->isNotEmpty())
			<div class="card">
				<h3 class="text-xs font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">
					{{ __('Gekoppelde transacties') }}
				</h3>
				<table class="w-full text-sm">
					<tbody>
						@foreach ($invoice->transactions as $tx)
							<tr class="border-b border-[color:var(--color-line)]/60 last:border-b-0">
								<td class="py-2 pr-3 tabular-nums text-[color:var(--color-ink-muted)] text-xs whitespace-nowrap">
									{{ $tx->transaction_date->format('d-m-Y') }}
								</td>
								<td class="py-2 px-3 text-[color:var(--color-ink-muted)] text-xs">
									@if ($tx->vatRate)
										{{ rtrim(rtrim(number_format((float) $tx->vatRate->rate, 2, ',', ''), '0'), ',') }}% BTW
									@else
										{{ __('Geen BTW') }}
									@endif
								</td>
								<td class="py-2 px-3 text-right tabular-nums font-medium text-emerald-700">
									+{{ $fmt($tx->amount) }}
								</td>
								<td class="py-2 pl-3 text-right">
									<a href="{{ route('tools.bookkeeping.edit', ['locale' => $locale, 'id' => $tx->id]) }}" class="text-xs text-[color:var(--color-accent)] hover:underline">
										{{ __('Bekijk') }}
									</a>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
				<p class="text-xs text-[color:var(--color-ink-soft)] mt-3">
					{{ __('Deze transacties zijn automatisch aangemaakt toen de factuur op betaald gezet werd.') }}
				</p>
			</div>
		@endif

		<div class="text-xs text-[color:var(--color-ink-soft)] flex flex-wrap gap-4">
			<span>{{ __('Aangemaakt') }}: {{ $invoice->created_at->format('d-m-Y H:i') }}</span>
			@if ($invoice->sent_at)
				<span>{{ __('Verzonden') }}: {{ $invoice->sent_at->format('d-m-Y H:i') }}</span>
			@endif
			@if ($invoice->paid_at)
				<span>{{ __('Betaald') }}: {{ $invoice->paid_at->format('d-m-Y H:i') }}</span>
			@endif
			@if ($invoice->due_date)
				<span>{{ __('Vervaldatum') }}: {{ $invoice->due_date->format('d-m-Y') }}</span>
			@endif
		</div>
	</div>
</section>

@endsection
