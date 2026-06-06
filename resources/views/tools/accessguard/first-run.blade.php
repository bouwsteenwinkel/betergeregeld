@extends('layouts.app')

@section('title', __('Welkom bij AccessGuard') . ', ' . config('app.name'))

@php
	$locale = app()->getLocale();
@endphp

@section('content')

@include('tools.accessguard._header')

<section class="py-12">
	<div class="max-w-[1000px] mx-auto px-6">
		<div class="text-center mb-10">
			<div class="text-5xl mb-4">👋</div>
			<h1 class="text-3xl font-bold mb-3">{{ __('Welkom bij AccessGuard') }}</h1>
			<p class="text-[color:var(--color-ink-muted)] max-w-2xl mx-auto leading-relaxed">
				{{ __('Om je snel op weg te helpen: kies of je wilt beginnen met een gevulde demo-omgeving (5 personen, 6 systemen, een lopende review-cyclus en wat gedetecteerde risico\'s), of meteen met je eigen data.') }}
			</p>
		</div>

		<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

			<div class="card border-[color:var(--color-accent)] border-2" style="background: linear-gradient(135deg, rgba(20,184,166,0.03) 0%, rgba(20,184,166,0.08) 100%)">
				<div class="flex items-start gap-3 mb-4">
					<div class="text-3xl">🚀</div>
					<div>
						<h2 class="text-lg font-bold">{{ __('Start met demo-data') }}</h2>
						<p class="text-xs text-[color:var(--color-ink-muted)] mt-1">{{ __('Aanbevolen · 5 seconden') }}</p>
					</div>
				</div>

				<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed mb-4">
					{{ __('Krijg direct alle features in actie. Je ziet:') }}
				</p>
				<ul class="text-sm space-y-1.5 mb-5">
					<li class="flex items-start gap-2"><span class="text-emerald-600 mt-0.5">✓</span>{{ __('Een ingevulde Access Matrix met 6 personen × 6 systemen') }}</li>
					<li class="flex items-start gap-2"><span class="text-emerald-600 mt-0.5">✓</span>{{ __('Access items (Basic/E3/Global Admin) op M365') }}</li>
					<li class="flex items-start gap-2"><span class="text-emerald-600 mt-0.5">✓</span>{{ __('Een lopende review-cyclus met open beslissingen') }}</li>
					<li class="flex items-start gap-2"><span class="text-emerald-600 mt-0.5">✓</span>{{ __('2 gedetecteerde risico\'s (orphan access + stale admin)') }}</li>
					<li class="flex items-start gap-2"><span class="text-emerald-600 mt-0.5">✓</span>{{ __('Een Sales role profile + een voorbeeld-credential in de Vault') }}</li>
				</ul>

				<form method="POST" action="{{ route('tools.accessguard.first-run.seed', ['locale' => $locale]) }}">
					@csrf
					<button type="submit" class="btn-accent w-full justify-center">
						{{ __('Laad demo-data') }}
						<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</button>
				</form>

				<p class="text-xs text-[color:var(--color-ink-soft)] mt-3 text-center">{{ __('Je kunt alles later in één klik wissen.') }}</p>
			</div>

			<div class="card">
				<div class="flex items-start gap-3 mb-4">
					<div class="text-3xl">🏗️</div>
					<div>
						<h2 class="text-lg font-bold">{{ __('Begin met een lege tenant') }}</h2>
						<p class="text-xs text-[color:var(--color-ink-muted)] mt-1">{{ __('Voor wie meteen wil starten') }}</p>
					</div>
				</div>

				<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed mb-4">
					{{ __('Drie snelle paden om je eigen data in te voeren:') }}
				</p>
				<ul class="text-sm space-y-2 mb-5">
					<li>
						<a href="{{ route('tools.accessguard.data.import-start', ['locale' => $locale, 'kind' => 'people']) }}" class="flex items-start gap-2 hover:bg-[color:var(--color-surface-soft,#fafafa)] p-2 -mx-2 rounded">
							<span class="text-slate-500">📥</span>
							<span>
								<strong>{{ __('Upload CSV') }}</strong>
								<span class="block text-xs text-[color:var(--color-ink-muted)]">{{ __('Heb je al een Excel? Map de kolommen in 2 klikken.') }}</span>
							</span>
						</a>
					</li>
					<li>
						<a href="{{ route('tools.accessguard.data.screenshot-start', ['locale' => $locale]) }}" class="flex items-start gap-2 hover:bg-[color:var(--color-surface-soft,#fafafa)] p-2 -mx-2 rounded">
							<span class="text-slate-500">🤖</span>
							<span>
								<strong>{{ __('Screenshot van M365/Google/Salesforce admin') }}</strong>
								<span class="block text-xs text-[color:var(--color-ink-muted)]">{{ __('AI extraheert automatisch gebruikers + rollen.') }}</span>
							</span>
						</a>
					</li>
					<li>
						<a href="{{ route('tools.accessguard.people.create', ['locale' => $locale]) }}" class="flex items-start gap-2 hover:bg-[color:var(--color-surface-soft,#fafafa)] p-2 -mx-2 rounded">
							<span class="text-slate-500">✏️</span>
							<span>
								<strong>{{ __('Tik het handmatig in') }}</strong>
								<span class="block text-xs text-[color:var(--color-ink-muted)]">{{ __('Begin met één persoon en één systeem; groei van daaruit.') }}</span>
							</span>
						</a>
					</li>
				</ul>
			</div>
		</div>

		<div class="mt-10 text-center">
			<a href="{{ route('accessguard.handleiding', ['locale' => $locale]) }}" target="_blank" class="text-sm text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)] underline">
				📄 {{ __('Download de handleiding (PDF)') }}
			</a>
		</div>
	</div>
</section>

@endsection
