@extends('layouts.app')

@section('title', config('app.name') . ' — ' . __('software-partner'))

@section('content')
	<section class="max-w-3xl">
		<h1 class="text-3xl sm:text-4xl font-bold tracking-tight mb-4">
			{{ __('Slimme tools, beter geregeld.') }}
		</h1>
		<p class="text-lg text-[color:var(--color-ink-muted)] mb-8">
			{{ __('app_tagline', ['app' => config('app.name')]) }}
		</p>

		@auth
			<div class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-5 shadow-[var(--shadow-soft)]">
				<p class="text-sm text-[color:var(--color-ink-muted)] mb-1">{{ __('Ingelogd als') }}</p>
				<p class="font-semibold">{{ Auth::user()->email }}</p>
				<p class="text-sm text-[color:var(--color-ink-muted)] mt-1">{{ __('Rol') }}: {{ Auth::user()->role }}</p>
			</div>
		@else
			<a href="{{ route('login') }}"
				class="inline-block rounded-[var(--radius-control)] bg-[color:var(--color-ink)] text-white font-semibold px-5 py-3 hover:opacity-90 transition">
				{{ __('Inloggen') }}
			</a>
		@endauth
	</section>
@endsection
