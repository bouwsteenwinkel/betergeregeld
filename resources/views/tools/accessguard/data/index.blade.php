@extends('layouts.app')

@section('title', __('Data') . ' — AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = __('Data');
@endphp

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[1100px] mx-auto px-6 space-y-4">
		@if (session('status'))
			<div class="card text-sm bg-emerald-50 border-emerald-200 text-emerald-900">
				{{ session('status') }}
				@if (session('import_errors') && count(session('import_errors')) > 0)
					<details class="mt-2">
						<summary class="cursor-pointer font-semibold text-xs">{{ __('Toon fouten') }} ({{ count(session('import_errors')) }})</summary>
						<ul class="mt-2 text-xs space-y-1">
							@foreach (session('import_errors') as $err)<li>{{ $err }}</li>@endforeach
						</ul>
					</details>
				@endif
			</div>
		@endif
		@if (session('error'))
			<div class="card text-sm bg-red-50 border-red-200 text-red-900">{{ session('error') }}</div>
		@endif

		<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
			<div class="card">
				<h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">📥 {{ __('Importeren') }}</h2>
				<p class="text-sm text-[color:var(--color-ink-muted)] mb-4">
					{{ __('Upload een CSV met je personen of systemen. Eigen kolomnamen zijn prima — je mapt ze in de volgende stap.') }}
				</p>
				<div class="space-y-2">
					<a href="{{ route('tools.accessguard.data.import-start', ['locale' => $locale, 'kind' => 'people']) }}" class="block p-3 rounded border border-[color:var(--color-line)] hover:bg-[color:var(--color-surface-soft,#fafafa)]">
						<div class="font-semibold text-sm">{{ __('Personen importeren') }}</div>
						<div class="text-xs text-[color:var(--color-ink-muted)] mt-0.5">{{ __('Medewerkers, contractors en externe partijen') }}</div>
					</a>
					<a href="{{ route('tools.accessguard.data.import-start', ['locale' => $locale, 'kind' => 'systems']) }}" class="block p-3 rounded border border-[color:var(--color-line)] hover:bg-[color:var(--color-surface-soft,#fafafa)]">
						<div class="font-semibold text-sm">{{ __('Systemen importeren') }}</div>
						<div class="text-xs text-[color:var(--color-ink-muted)] mt-0.5">{{ __('Apps, services en andere toegang-doelen') }}</div>
					</a>
					<a href="{{ route('tools.accessguard.data.screenshot-start', ['locale' => $locale]) }}" class="block p-3 rounded border border-[color:var(--color-accent)] bg-[color:var(--color-accent)]/5 hover:bg-[color:var(--color-accent)]/10">
						<div class="font-semibold text-sm">🤖 {{ __('Screenshot → personen') }}</div>
						<div class="text-xs text-[color:var(--color-ink-muted)] mt-0.5">{{ __('Plak een screenshot van je M365/Google/Salesforce admin — AI extraheert automatisch') }}</div>
					</a>
				</div>
			</div>

			<div class="card">
				<h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">📤 {{ __('Exporteren') }}</h2>
				<p class="text-sm text-[color:var(--color-ink-muted)] mb-4">
					{{ __('CSV-exports voor auditors, Excel-bewerking of een tweede opinie. UTF-8 met BOM — Excel opent direct correct.') }}
				</p>
				<div class="space-y-2">
					<a href="{{ route('tools.accessguard.data.export-matrix-wide', ['locale' => $locale]) }}" class="block p-3 rounded border border-[color:var(--color-line)] hover:bg-[color:var(--color-surface-soft,#fafafa)]">
						<div class="font-semibold text-sm">{{ __('Matrix (wide format)') }}</div>
						<div class="text-xs text-[color:var(--color-ink-muted)] mt-0.5">{{ __('Eén rij per persoon, kolommen per systeem') }}</div>
					</a>
					<a href="{{ route('tools.accessguard.data.export-matrix-long', ['locale' => $locale]) }}" class="block p-3 rounded border border-[color:var(--color-line)] hover:bg-[color:var(--color-surface-soft,#fafafa)]">
						<div class="font-semibold text-sm">{{ __('Matrix (long format)') }}</div>
						<div class="text-xs text-[color:var(--color-ink-muted)] mt-0.5">{{ __('Eén rij per cel (person_id, system_id, state)') }}</div>
					</a>
				</div>
			</div>
		</div>

		<div class="card">
			<h3 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Verwacht CSV-formaat') }}</h3>
			<div class="text-xs space-y-2">
				<p class="text-[color:var(--color-ink-muted)]">{{ __('De import detecteert automatisch comma, semicolon of tab als scheidingsteken. UTF-8 aanbevolen (BOM wordt herkend). Voorbeeld voor personen:') }}</p>
				<pre class="p-3 rounded bg-slate-900 text-slate-100 overflow-x-auto text-xs">Voornaam,Achternaam,E-mail,Functie,Afdeling
Jan,de Vries,jan@bedrijf.nl,CEO,Directie
Lisa,Jansen,lisa@bedrijf.nl,Finance Manager,Finance</pre>
				<p class="text-[color:var(--color-ink-muted)] mt-2">{{ __('Voor systemen:') }}</p>
				<pre class="p-3 rounded bg-slate-900 text-slate-100 overflow-x-auto text-xs">Naam,Categorie,Notities
Microsoft 365,saas,Hoofdaccount voor email + Office
Salesforce,saas,Sales + support
1Password,security,Team vault</pre>
			</div>
		</div>
	</div>
</section>

@endsection
