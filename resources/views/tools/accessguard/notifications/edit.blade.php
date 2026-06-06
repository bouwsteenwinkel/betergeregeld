@extends('layouts.app')

@section('title', __('Notificaties') . ', AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = __('Notificaties');
@endphp

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[700px] mx-auto px-6">
		@if (session('status'))
			<div class="card text-sm bg-emerald-50 border-emerald-200 text-emerald-900 mb-4">{{ session('status') }}</div>
		@endif

		<form method="POST" action="{{ route('tools.accessguard.notifications.update', ['locale' => $locale]) }}" class="card space-y-4">
			@csrf
			@method('PUT')

			<h2 class="text-lg font-bold">{{ __('E-mail notificaties') }}</h2>
			<p class="text-sm text-[color:var(--color-ink-muted)]">
				{{ __('AccessGuard kan je proactief op de hoogte houden van risico\'s en deadlines per e-mail. Je kunt hier per soort kiezen.') }}
			</p>

			<label class="flex items-start gap-3 cursor-pointer p-3 rounded border border-[color:var(--color-line)] hover:bg-[color:var(--color-surface-soft,#fafafa)]">
				<input type="checkbox" name="digest_enabled" value="1" @checked($pref->digest_enabled) class="mt-1">
				<div class="flex-1">
					<div class="font-semibold text-sm">{{ __('Dagelijks overzicht (08:00)') }}</div>
					<div class="text-xs text-[color:var(--color-ink-muted)] mt-1">{{ __('Open risico\'s, upcoming deadlines en open acties, alleen als er iets is te melden.') }}</div>
					@if ($pref->last_digest_sent_at)
						<div class="text-xs text-[color:var(--color-ink-soft)] mt-1">{{ __('Laatst verstuurd: :ts', ['ts' => $pref->last_digest_sent_at->format('d-m-Y H:i')]) }}</div>
					@endif
				</div>
			</label>

			<label class="flex items-start gap-3 cursor-pointer p-3 rounded border border-[color:var(--color-line)] hover:bg-[color:var(--color-surface-soft,#fafafa)]">
				<input type="checkbox" name="instant_critical_enabled" value="1" @checked($pref->instant_critical_enabled) class="mt-1">
				<div class="flex-1">
					<div class="font-semibold text-sm">{{ __('Instant bij kritiek (severity 5)') }}</div>
					<div class="text-xs text-[color:var(--color-ink-muted)] mt-1">{{ __('Bijvoorbeeld orphan access of verlopen credentials, zodra ze worden gedetecteerd.') }}</div>
				</div>
			</label>

			<div class="flex items-center gap-3 border-t border-[color:var(--color-line)] pt-4">
				<button type="submit" class="btn-accent text-sm">{{ __('Opslaan') }}</button>
				<a href="{{ route('tools.accessguard.index', ['locale' => $locale]) }}" class="text-sm text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">{{ __('Annuleren') }}</a>
			</div>
		</form>
	</div>
</section>

@endsection
