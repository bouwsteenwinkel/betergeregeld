@extends('layouts.app')

@section('title', __('Factuur-instellingen') . ' — ' . config('app.name'))

@php $locale = app()->getLocale(); @endphp

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[900px] mx-auto px-6 py-12">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-4 flex items-center gap-2">
			<a href="{{ route('tools.bookkeeping.index', ['locale' => $locale]) }}" class="hover:text-white">Boekhouden</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">{{ __('Instellingen') }}</span>
		</nav>
		<h1 class="display-1">{{ __('Factuur-instellingen') }}</h1>
		<p class="text-[color:var(--color-on-dark-muted)] mt-2 max-w-xl">
			{{ __('Deze gegevens verschijnen als afzender op je facturen en in de footer.') }}
		</p>
	</div>
</section>

@include('tools.bookkeeping._subnav')

<section class="py-6">
	<div class="max-w-[900px] mx-auto px-6">
		@if (session('bookkeeping_message'))
			<div class="rounded-[var(--radius-control)] border border-emerald-200 bg-emerald-50 text-emerald-900 p-3 text-sm mb-4">
				{{ session('bookkeeping_message') }}
			</div>
		@endif

		<form method="POST" action="{{ route('tools.bookkeeping.settings.update', ['locale' => $locale]) }}" class="card space-y-5">
			@csrf
			@method('PUT')

			<div>
				<h3 class="text-xs font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Bedrijfsgegevens') }}</h3>
				<div class="space-y-4">
					<div>
						<label for="company_name" class="block text-sm font-semibold mb-2">{{ __('Bedrijfsnaam') }}</label>
						<input id="company_name" name="company_name" type="text" maxlength="190"
							value="{{ old('company_name', $settings->company_name) }}" class="field-input">
					</div>
					<div class="grid grid-cols-1 sm:grid-cols-[2fr_1fr] gap-4">
						<div>
							<label for="address" class="block text-sm font-semibold mb-2">{{ __('Straat + huisnummer') }}</label>
							<input id="address" name="address" type="text" maxlength="190"
								value="{{ old('address', $settings->address) }}" class="field-input">
						</div>
						<div>
							<label for="postal_code" class="block text-sm font-semibold mb-2">{{ __('Postcode') }}</label>
							<input id="postal_code" name="postal_code" type="text" maxlength="16"
								value="{{ old('postal_code', $settings->postal_code) }}" class="field-input">
						</div>
					</div>
					<div class="grid grid-cols-1 sm:grid-cols-[2fr_1fr] gap-4">
						<div>
							<label for="city" class="block text-sm font-semibold mb-2">{{ __('Plaats') }}</label>
							<input id="city" name="city" type="text" maxlength="120"
								value="{{ old('city', $settings->city) }}" class="field-input">
						</div>
						<div>
							<label for="country" class="block text-sm font-semibold mb-2">{{ __('Land (ISO-2)') }}</label>
							<input id="country" name="country" type="text" maxlength="2"
								value="{{ old('country', $settings->country ?? 'NL') }}" class="field-input font-mono uppercase">
						</div>
					</div>
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div>
							<label for="email" class="block text-sm font-semibold mb-2">{{ __('E-mail') }}</label>
							<input id="email" name="email" type="email" maxlength="190"
								value="{{ old('email', $settings->email) }}" class="field-input">
						</div>
						<div>
							<label for="phone" class="block text-sm font-semibold mb-2">{{ __('Telefoon') }}</label>
							<input id="phone" name="phone" type="text" maxlength="50"
								value="{{ old('phone', $settings->phone) }}" class="field-input">
						</div>
					</div>
				</div>
			</div>

			<div>
				<h3 class="text-xs font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Zakelijk') }}</h3>
				<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
					<div>
						<label for="kvk_number" class="block text-sm font-semibold mb-2">{{ __('KvK-nummer') }}</label>
						<input id="kvk_number" name="kvk_number" type="text" maxlength="20"
							value="{{ old('kvk_number', $settings->kvk_number) }}" class="field-input font-mono">
					</div>
					<div>
						<label for="vat_number" class="block text-sm font-semibold mb-2">{{ __('BTW-nummer') }}</label>
						<input id="vat_number" name="vat_number" type="text" maxlength="20"
							value="{{ old('vat_number', $settings->vat_number) }}" class="field-input font-mono">
					</div>
					<div>
						<label for="iban" class="block text-sm font-semibold mb-2">IBAN</label>
						<input id="iban" name="iban" type="text" maxlength="34"
							value="{{ old('iban', $settings->iban) }}" class="field-input font-mono">
					</div>
				</div>
			</div>

			<div>
				<h3 class="text-xs font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Standaarden') }}</h3>
				<div class="space-y-4">
					<div>
						<label for="default_payment_terms_days" class="block text-sm font-semibold mb-2">{{ __('Standaard betalingstermijn (dagen)') }}</label>
						<input id="default_payment_terms_days" name="default_payment_terms_days" type="number" min="0" max="365"
							value="{{ old('default_payment_terms_days', $settings->default_payment_terms_days ?? 30) }}"
							class="field-input w-32 font-mono">
					</div>
					<div>
						<label class="flex items-center gap-2 text-sm cursor-pointer">
							<input type="hidden" name="auto_reminders_enabled" value="0">
							<input type="checkbox" name="auto_reminders_enabled" value="1"
								@checked(old('auto_reminders_enabled', $settings->auto_reminders_enabled ?? true))>
							<span>{{ __('Automatische betaalherinneringen versturen') }}</span>
						</label>
						<p class="text-xs text-[color:var(--color-ink-soft)] mt-1.5 ml-6">
							{{ __('Dagelijkse job stuurt herinneringen op T−3, T+0, T+7 en T+21 t.o.v. vervaldatum. Je kunt altijd handmatig versturen vanaf de factuurpagina.') }}
						</p>
					</div>
					<div>
						<label for="invoice_footer" class="block text-sm font-semibold mb-2">{{ __('Extra footer-tekst op facturen (optioneel)') }}</label>
						<textarea id="invoice_footer" name="invoice_footer" rows="2" maxlength="500"
							class="field-input">{{ old('invoice_footer', $settings->invoice_footer) }}</textarea>
						<p class="text-xs text-[color:var(--color-ink-soft)] mt-1.5">{{ __('Bijv. betalingsvoorwaarden of bankgegevens-zin.') }}</p>
					</div>
				</div>
			</div>

			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">
					<ul class="list-disc pl-5 space-y-0.5">
						@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
					</ul>
				</div>
			@endif

			<div>
				<button type="submit" class="btn-accent">{{ __('Opslaan') }}</button>
			</div>
		</form>
	</div>
</section>

@endsection
