@php $locale = app()->getLocale(); @endphp
<div class="max-w-[1200px] mx-auto px-6 mb-4">
	<nav class="flex gap-1 border-b border-[color:var(--color-line)] text-sm overflow-x-auto">
		@foreach ([
			['route' => 'tools.bookkeeping.index',                         'label' => __('Transacties')],
			['route' => 'tools.bookkeeping.relations.index',              'label' => __('Relaties')],
			['route' => 'tools.bookkeeping.reports.profit-loss',          'label' => __('Winst & verlies')],
			['route' => 'tools.bookkeeping.reports.vat',                  'label' => __('BTW-aangifte')],
			['route' => 'tools.bookkeeping.categories.index',             'label' => __('Categorieën'), 'admin' => true],
			['route' => 'tools.bookkeeping.vat-rates.index',              'label' => __('BTW-tarieven'), 'admin' => true],
		] as $tab)
			@php $active = request()->routeIs($tab['route']); @endphp
			<a href="{{ route($tab['route'], ['locale' => $locale]) }}"
				class="px-4 py-2.5 -mb-px border-b-2 whitespace-nowrap {{ $active
					? 'border-[color:var(--color-accent)] text-[color:var(--color-ink)] font-semibold'
					: 'border-transparent text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]' }} {{ ($tab['admin'] ?? false) ? 'ml-auto first:ml-0' : '' }}">
				{{ $tab['label'] }}
			</a>
		@endforeach
	</nav>
</div>
