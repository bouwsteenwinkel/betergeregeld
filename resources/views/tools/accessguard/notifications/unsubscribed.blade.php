@extends('layouts.app')

@section('title', __('Uitgeschreven') . ' — AccessGuard')

@section('content')

<section class="py-20">
	<div class="max-w-[600px] mx-auto px-6 text-center">
		<div class="card">
			<div class="text-5xl mb-4">📭</div>
			<h1 class="text-2xl font-bold mb-3">{{ __('Je bent uitgeschreven') }}</h1>
			<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-6">
				{{ __('Je ontvangt geen AccessGuard e-mails meer. Je kunt dit altijd weer aanzetten vanuit de Notificaties-pagina in de app.') }}
			</p>
			<a href="{{ route('tools.accessguard.notifications.edit', ['locale' => app()->getLocale()]) }}" class="btn-accent text-sm">
				{{ __('Naar notificatie-instellingen') }}
			</a>
		</div>
	</div>
</section>

@endsection
