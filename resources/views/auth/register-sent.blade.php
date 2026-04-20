@extends('layouts.app')

@section('title', __('Controleer je e-mail') . ' — ' . config('app.name'))

@section('content')
	<div class="max-w-md mx-auto">
		<div class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-6 shadow-[var(--shadow-soft)]">
			<h1 class="text-2xl font-bold mb-4">{{ __('Controleer je e-mail') }}</h1>
			<p class="text-[color:var(--color-ink-muted)]">
				{{ __('We hebben een bevestigingslink gestuurd naar') }}
				<span class="text-[color:var(--color-ink)] font-semibold">{{ session('email') }}</span>.
				{{ __('Klik erop om je account te activeren. De link is 24 uur geldig.') }}
			</p>
		</div>
	</div>
@endsection
