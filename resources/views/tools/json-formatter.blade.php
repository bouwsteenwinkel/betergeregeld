@extends('layouts.app')

@section('title', __('JSON formatter & validator') . ' — ' . config('app.name'))

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1100px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Tools</span>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">JSON formatter</span>
		</nav>
		<span class="pill pill-dark mb-5">Tool · {{ __('Gratis') }}</span>
		<h1 class="display-1 mb-5">JSON <span class="accent-word">{{ __('formatter') }}</span></h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			{{ __('Valideer, formatteer of minify JSON. Toont de exacte positie van eventuele fouten.') }}
		</p>
	</div>
</section>

<section class="py-16">
	<div class="max-w-[1100px] mx-auto px-6">
		<form method="POST" action="{{ route('tools.json-formatter.run') }}" class="card">
			@csrf
			<div class="flex flex-wrap items-center gap-4 mb-4 text-sm">
				<div class="flex items-center gap-2">
					<label class="font-semibold">{{ __('Modus') }}:</label>
					<select name="mode" class="field-input py-1.5 px-2">
						<option value="format" @selected($input['mode'] === 'format')>{{ __('Formatteren') }}</option>
						<option value="minify" @selected($input['mode'] === 'minify')>{{ __('Minify') }}</option>
						<option value="validate" @selected($input['mode'] === 'validate')>{{ __('Alleen valideren') }}</option>
					</select>
				</div>
				<div class="flex items-center gap-2">
					<label class="font-semibold">{{ __('Indent') }}:</label>
					<select name="indent" class="field-input py-1.5 px-2">
						@foreach ([2, 4, 8] as $n)
							<option value="{{ $n }}" @selected($input['indent'] === $n)>{{ $n }}</option>
						@endforeach
					</select>
				</div>
				<label class="flex items-center gap-2">
					<input type="checkbox" name="sort_keys" value="1" @checked($input['sort_keys'])>
					<span>{{ __('Keys alfabetisch sorteren') }}</span>
				</label>
			</div>

			<textarea name="json" rows="14" required placeholder='{"hello":"world"}'
				class="field-input font-mono text-sm w-full resize-y">{{ $input['json'] }}</textarea>

			@if ($errors->any())
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3 mt-3">
					{{ $errors->first() }}
				</div>
			@endif

			<button type="submit" class="btn-accent mt-4">
				{{ __('Uitvoeren') }}
			</button>
		</form>

		@if ($result)
			<div class="card mt-6">
				@if ($result['ok'])
					<div class="flex items-center gap-3 mb-4">
						<span class="pill pill-teal">{{ __('Geldige JSON') }}</span>
						@if (isset($result['stats']['bytes_in']))
							<span class="text-xs text-[color:var(--color-ink-muted)] font-mono">
								{{ $result['stats']['bytes_in'] }} B in
								@isset($result['stats']['bytes_out'])
									→ {{ $result['stats']['bytes_out'] }} B uit
								@endisset
								@if ($result['mode'] === 'format' && ! empty($result['stats']['sorted']))
									· {{ __('gesorteerd') }}
								@endif
							</span>
						@endif
					</div>

					@if (isset($result['output']))
						<pre class="text-sm font-mono bg-[color:var(--color-surface-soft,#fafafa)] border border-[color:var(--color-line)] rounded-[var(--radius-control)] p-4 overflow-x-auto max-h-[600px]"><code>{{ $result['output'] }}</code></pre>
					@else
						<p class="text-sm text-[color:var(--color-ink-muted)]">{{ __('JSON is syntactisch correct.') }}</p>
					@endif
				@else
					<div class="flex items-center gap-3 mb-4">
						<span class="pill" style="background:rgba(220,38,38,0.12);color:#991b1b;border:1px solid rgba(220,38,38,0.24)">
							{{ __('Ongeldige JSON') }}
						</span>
						@if (! empty($result['details']['line']))
							<span class="text-xs text-[color:var(--color-ink-muted)] font-mono">
								{{ __('regel') }} {{ $result['details']['line'] }},
								{{ __('kolom') }} {{ $result['details']['col'] }}
							</span>
						@endif
					</div>
					<p class="text-sm text-[color:var(--color-ink)] font-mono">{{ $result['error'] ?? '' }}</p>
					@if (! empty($result['suggestions']))
						<ul class="mt-3 space-y-1.5 text-sm text-[color:var(--color-ink-muted)]">
							@foreach ($result['suggestions'] as $s)
								<li class="flex items-start gap-2"><span class="mt-0.5">•</span><span>{{ __($s) }}</span></li>
							@endforeach
						</ul>
					@endif
				@endif
			</div>
		@endif
	</div>
</section>

@endsection
