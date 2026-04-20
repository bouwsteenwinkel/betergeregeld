@extends('layouts.app')

@section('title', 'Inloggen — ' . config('app.name'))

@section('content')
	<div class="max-w-md mx-auto">
		<h1 class="text-2xl font-bold mb-6">Inloggen</h1>

		<form method="POST" action="{{ route('login') }}"
			class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-6 shadow-[var(--shadow-soft)] space-y-4">
			@csrf

			<div>
				<label for="email" class="block text-sm font-semibold mb-1.5">E-mail</label>
				<input id="email" name="email" type="email" autocomplete="email" required autofocus
					value="{{ old('email') }}"
					class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3 text-base focus:outline-none focus:ring-2 focus:ring-black/10">
			</div>

			<div>
				<label for="password" class="block text-sm font-semibold mb-1.5">Wachtwoord</label>
				<input id="password" name="password" type="password" autocomplete="current-password" required
					class="w-full border border-[color:var(--color-line)] rounded-[var(--radius-control)] px-3.5 py-3 text-base focus:outline-none focus:ring-2 focus:ring-black/10">
			</div>

			<label class="flex items-center gap-2 text-sm">
				<input type="checkbox" name="remember" value="1" class="rounded border-[color:var(--color-line)]">
				<span>Onthoud mij</span>
			</label>

			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">
					{{ $errors->first() }}
				</div>
			@endif

			<button type="submit"
				class="w-full rounded-[var(--radius-control)] bg-[color:var(--color-ink)] text-white font-semibold px-4 py-3 hover:opacity-90 transition">
				Inloggen
			</button>
		</form>
	</div>
@endsection
