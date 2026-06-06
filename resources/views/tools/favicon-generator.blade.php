@extends('layouts.app')

@section('title', __('Favicon generator') . ', ' . config('app.name'))

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[900px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Tools</span>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Favicon</span>
		</nav>
		<span class="pill pill-dark mb-5">Tool · {{ __('Gratis') }}</span>
		<h1 class="display-1 mb-5">Favicon <span class="accent-word">{{ __('generator') }}</span></h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			{{ __('Upload een vierkant logo (PNG/JPG/WEBP, max 5 MB), krijg alle standaard favicon-formaten in één ZIP.') }}
		</p>
	</div>
</section>

<section class="py-16">
	<div class="max-w-[900px] mx-auto px-6">
		@include('tools._usage')

		@if (! $result)
			<form method="POST" action="{{ route('tools.favicon-generator.generate') }}" enctype="multipart/form-data" class="card space-y-5">
				@csrf
				<div>
					<label for="source" class="block text-sm font-semibold mb-2">{{ __('Bronafbeelding') }}</label>
					<input id="source" name="source" type="file" accept="image/png,image/jpeg,image/webp,image/gif" required
						class="field-input">
					<p class="text-xs text-[color:var(--color-ink-soft)] mt-1.5">{{ __('Tip: gebruik een vierkant logo op transparante achtergrond voor het beste resultaat.') }}</p>
				</div>
				@if ($errors->any())
					<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">
						{{ $errors->first() }}
					</div>
				@endif
				<button type="submit" class="btn-accent">{{ __('Genereren') }}</button>

				@if (! $features->bool('tool.favicon.all_sizes'))
					<p class="text-xs text-[color:var(--color-ink-soft)]">
						{{ __('Free plan: 16, 32, 180 en 192 px.') }}
						<a href="{{ route('pricing') }}" class="underline hover:text-[color:var(--color-ink)]">{{ __('Upgrade voor alle formaten (incl. 48, 512).') }}</a>
					</p>
				@endif
			</form>
		@else
			<div class="card">
				<div class="flex items-center gap-3 mb-5">
					<span class="pill pill-teal">{{ __('Klaar') }}</span>
					<span class="text-sm text-[color:var(--color-ink-muted)]">
						{{ count($result['files']) }} {{ __('bestanden') }}
					</span>
				</div>

				<div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4 mb-6">
					@foreach ($result['sizes'] as $size)
						@if (isset($result['files'][$size]))
							<a href="{{ route('tools.favicon-generator.download', ['locale' => app()->getLocale(), 'key' => $result['key'], 'what' => $size]) }}"
								class="flex flex-col items-center gap-1.5 p-3 rounded-[var(--radius-control)] border border-[color:var(--color-line)] hover:border-[color:var(--color-line-strong)] hover:shadow-sm transition">
								<div class="w-16 h-16 flex items-center justify-center bg-[color:var(--color-surface-soft,#fafafa)] rounded">
									{{-- Can't preview without serving the file; show label --}}
									<span class="text-xs font-mono text-[color:var(--color-ink-muted)]">{{ $size }}</span>
								</div>
								<span class="text-xs font-semibold">{{ $size }}×{{ $size }}</span>
								<span class="text-[10px] text-[color:var(--color-ink-soft)]">PNG</span>
							</a>
						@endif
					@endforeach

					@if (isset($result['files']['ico']))
						<a href="{{ route('tools.favicon-generator.download', ['locale' => app()->getLocale(), 'key' => $result['key'], 'what' => 'ico']) }}"
							class="flex flex-col items-center gap-1.5 p-3 rounded-[var(--radius-control)] border border-[color:var(--color-line)] hover:border-[color:var(--color-line-strong)] hover:shadow-sm transition">
							<div class="w-16 h-16 flex items-center justify-center bg-[color:var(--color-surface-soft,#fafafa)] rounded">
								<span class="text-xs font-mono text-[color:var(--color-ink-muted)]">ICO</span>
							</div>
							<span class="text-xs font-semibold">favicon.ico</span>
							<span class="text-[10px] text-[color:var(--color-ink-soft)]">ICO</span>
						</a>
					@endif
				</div>

				<div class="flex gap-2">
					<a href="{{ route('tools.favicon-generator.download', ['locale' => app()->getLocale(), 'key' => $result['key'], 'what' => 'zip']) }}" class="btn-accent">
						{{ __('Download alle als ZIP') }}
					</a>
					<a href="{{ route('tools.favicon-generator', ['locale' => app()->getLocale()]) }}" class="btn-dark">
						{{ __('Opnieuw') }}
					</a>
				</div>
			</div>
		@endif
	</div>
</section>

@endsection
