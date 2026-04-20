@extends('layouts.app')

@section('title', config('app.name') . ' — software-partner')

@section('content')
	<section class="max-w-3xl">
		<h1 class="text-3xl sm:text-4xl font-bold tracking-tight mb-4">
			Slimme tools, <span class="whitespace-nowrap">beter geregeld.</span>
		</h1>
		<p class="text-lg text-[color:var(--color-ink-muted)] mb-8">
			{{ config('app.name') }} is je software-partner voor praktische online tools,
			gratis te gebruiken, met betaalde uitbreidingen voor wie meer nodig heeft.
		</p>

		@auth
			<div class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-5 shadow-[var(--shadow-soft)]">
				<p class="text-sm text-[color:var(--color-ink-muted)] mb-1">Ingelogd als</p>
				<p class="font-semibold">{{ Auth::user()->email }}</p>
				<p class="text-sm text-[color:var(--color-ink-muted)] mt-1">Rol: {{ Auth::user()->role }}</p>
			</div>
		@else
			<a href="{{ route('login') }}"
				class="inline-block rounded-[var(--radius-control)] bg-[color:var(--color-ink)] text-white font-semibold px-5 py-3 hover:opacity-90 transition">
				Inloggen
			</a>
		@endauth
	</section>
@endsection
