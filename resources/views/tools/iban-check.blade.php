@extends('layouts.app')

@section('title', __('IBAN check') . ' — ' . config('app.name'))

@section('content')
	<div class="max-w-2xl mx-auto px-4 py-10">
		<h1 class="text-3xl font-bold mb-2">{{ __('IBAN naamcheck & risico-analyse') }}</h1>
		<p class="text-[color:var(--color-ink-muted)] mb-8 leading-relaxed">
			{{ __('Controleer of een IBAN geldig is en detecteer mogelijke risico\'s op basis van naam en structuur.') }}
		</p>

		<form method="POST" action="{{ route('tools.iban-check.check') }}"
			class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-6 shadow-[var(--shadow-soft)] space-y-4">
			@csrf

			<div>
				<label for="name" class="block text-sm font-semibold mb-1.5">{{ __('Naam rekeninghouder') }}</label>
				<input id="name" name="name" type="text" value="{{ $input['name'] }}" autocomplete="off"
					class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3 text-base focus:outline-none focus:ring-2 focus:ring-black/10">
			</div>

			<div>
				<label for="iban" class="block text-sm font-semibold mb-1.5">{{ __('IBAN') }}</label>
				<input id="iban" name="iban" type="text" required value="{{ $input['iban'] }}" autocomplete="off"
					placeholder="NL91ABNA0417164300"
					class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3 text-base font-mono focus:outline-none focus:ring-2 focus:ring-black/10">
			</div>

			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">
					{{ $errors->first() }}
				</div>
			@endif

			<button type="submit"
				class="rounded-[var(--radius-control)] bg-[color:var(--color-ink)] text-white font-semibold px-5 py-3 hover:opacity-90 transition">
				{{ __('Controleer IBAN') }}
			</button>
		</form>

		@if ($result)
			<div class="mt-6 bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-6 shadow-[var(--shadow-soft)]">
				<div class="flex items-center gap-3 mb-4">
					@if ($result['valid'])
						<span class="inline-flex items-center rounded-full bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-1">
							{{ __('Technisch geldig') }}
						</span>
					@else
						<span class="inline-flex items-center rounded-full bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-1">
							{{ __('Ongeldig') }}
						</span>
					@endif
					<code class="font-mono text-sm">{{ $result['iban_clean'] }}</code>
				</div>

				@if (count($result['risks']) > 0)
					<h2 class="text-sm font-semibold mb-2">{{ __('Risico-indicatoren') }}</h2>
					<ul class="space-y-1 text-sm">
						@foreach ($result['risks'] as $risk)
							<li class="flex items-start gap-2">
								<span class="text-amber-600 shrink-0">●</span>
								<span>{{ __('iban.' . $risk) }}</span>
							</li>
						@endforeach
					</ul>
				@else
					@if ($result['valid'])
						<p class="text-sm text-[color:var(--color-ink-muted)]">
							{{ __('Geen risico-indicatoren gevonden.') }}
						</p>
					@endif
				@endif

				<p class="text-xs text-[color:var(--color-ink-muted)] mt-4">
					{{ __('iban.disclaimer') }}
				</p>
			</div>
		@endif
	</div>
@endsection
