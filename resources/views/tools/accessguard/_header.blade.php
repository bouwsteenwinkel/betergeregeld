@php $locale = app()->getLocale(); @endphp
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1400px] mx-auto px-6 py-10">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-3 flex items-center gap-2">
			<a href="{{ route('tools.index', ['locale' => $locale]) }}" class="hover:text-white">Tools</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">AccessGuard</span>
			@isset($crumb)
				<span class="opacity-40">/</span>
				<span class="text-[color:var(--color-on-dark-muted)]">{{ $crumb }}</span>
			@endisset
		</nav>
		<h1 class="display-2">Access<span class="accent-word">Guard</span></h1>
		<p class="text-sm text-[color:var(--color-on-dark-muted)] mt-2 max-w-2xl">
			{{ __('Wie heeft toegang tot welk systeem? Houd het bij, beoordeel periodiek en sluit toegang bij uitdiensttreding.') }}
		</p>
	</div>
</section>
