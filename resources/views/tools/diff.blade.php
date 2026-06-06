@extends('layouts.app')

@section('title', __('Diff checker') . ', ' . config('app.name'))

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1200px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Tools</span>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Diff checker</span>
		</nav>
		<span class="pill pill-dark mb-5">Tool · {{ __('Gratis') }}</span>
		<h1 class="display-1 mb-5">{{ __('Vergelijk') }} <span class="accent-word">{{ __('twee teksten') }}</span></h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			{{ __('Zie direct welke regels toegevoegd, verwijderd of ongewijzigd zijn. Werkt voor code, e-mails, logs of documenten.') }}
		</p>
	</div>
</section>

<section class="py-16">
	<div class="max-w-[1200px] mx-auto px-6">
		<form method="POST" action="{{ route('tools.diff.compare') }}" class="card">
			@csrf
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
				<div>
					<label for="left" class="block text-sm font-semibold mb-2">{{ __('Origineel') }}</label>
					<textarea id="left" name="left" rows="14" required
						class="field-input font-mono text-sm w-full resize-y">{{ $input['left'] }}</textarea>
				</div>
				<div>
					<label for="right" class="block text-sm font-semibold mb-2">{{ __('Nieuw') }}</label>
					<textarea id="right" name="right" rows="14" required
						class="field-input font-mono text-sm w-full resize-y">{{ $input['right'] }}</textarea>
				</div>
			</div>

			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3 mt-3">
					{{ $errors->first() }}
				</div>
			@endif

			<button type="submit" class="btn-accent mt-4">
				{{ __('Vergelijken') }}
			</button>
		</form>

		@if ($result)
			<div class="card mt-6">
				<div class="flex items-center gap-3 mb-4 flex-wrap">
					<span class="pill pill-teal">+{{ $result['stats']['added'] }}</span>
					<span class="pill" style="background:rgba(220,38,38,0.12);color:#991b1b;border:1px solid rgba(220,38,38,0.24)">−{{ $result['stats']['removed'] }}</span>
					<span class="pill pill-ink">={{ $result['stats']['equal'] }}</span>
					<span class="text-xs text-[color:var(--color-ink-muted)]">{{ __('regels') }}</span>
				</div>

				<div class="overflow-x-auto">
					<pre class="text-sm font-mono leading-relaxed">@foreach ($result['lines'] as $line)@if ($line['type'] === 'added')<span class="block bg-green-50 text-green-900 border-l-2 border-green-400 pl-3 pr-2"><span class="text-green-600 select-none">+ </span>{{ rtrim($line['text'], "\n") }}</span>@elseif ($line['type'] === 'removed')<span class="block bg-red-50 text-red-900 border-l-2 border-red-400 pl-3 pr-2"><span class="text-red-600 select-none">− </span>{{ rtrim($line['text'], "\n") }}</span>@else<span class="block text-[color:var(--color-ink-muted)] pl-3 pr-2"><span class="select-none">  </span>{{ rtrim($line['text'], "\n") }}</span>@endif
@endforeach</pre>
				</div>
			</div>
		@endif
	</div>
</section>

@endsection
