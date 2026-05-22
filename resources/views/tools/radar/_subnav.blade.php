@php
	$locale = app()->getLocale();
	$tabs = [
		['route' => 'tools.radar.index',         'label' => __('Overzicht')],
		['route' => 'tools.radar.assets.index',  'label' => __('Assets')],
	];
@endphp
<div class="max-w-[1400px] mx-auto px-6 mb-4">
	<nav class="flex gap-1 border-b border-[color:var(--color-line)] text-sm overflow-x-auto">
		@foreach ($tabs as $tab)
			@php $active = request()->routeIs($tab['route']) || request()->routeIs(str_replace('.index', '.*', $tab['route'])); @endphp
			<a href="{{ route($tab['route'], ['locale' => $locale]) }}"
				class="px-4 py-2.5 -mb-px border-b-2 whitespace-nowrap {{ $active
					? 'border-[color:var(--color-accent)] text-[color:var(--color-ink)] font-semibold'
					: 'border-transparent text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]' }}">
				{{ $tab['label'] }}
			</a>
		@endforeach
	</nav>
</div>
