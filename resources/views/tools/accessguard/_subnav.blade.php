@php
	$locale = app()->getLocale();
	$agDirectorySync = auth()->check()
		&& app(\App\Services\Features\FeatureResolver::class)
			->forUser(auth()->user())
			->bool('tool.accessguard.directory_sync');
	$tabs = [
		['route' => 'tools.accessguard.index', 'label' => __('Overzicht')],
		['route' => 'tools.accessguard.matrix', 'label' => __('Access Matrix')],
		['route' => 'tools.accessguard.reviews.index', 'label' => __('Reviews')],
		['route' => 'tools.accessguard.actions.index', 'label' => __('Acties')],
		['route' => 'tools.accessguard.processes.index', 'label' => __('Processen')],
		['route' => 'tools.accessguard.risks.index', 'label' => __('Risico\'s')],
		['route' => 'tools.accessguard.reminders.index', 'label' => __('Reminders')],
		['route' => 'tools.accessguard.vault.index', 'label' => __('Vault')],
		['route' => 'tools.accessguard.people.index', 'label' => __('Personen')],
		['route' => 'tools.accessguard.systems.index', 'label' => __('Systemen')],
		['route' => 'tools.accessguard.notifications.edit', 'label' => __('Notificaties')],
		['route' => 'tools.accessguard.data.show', 'label' => __('Data')],
		['route' => 'tools.accessguard.profiles.index', 'label' => __('Profielen')],
	];
	if ($agDirectorySync) {
		$tabs[] = ['route' => 'tools.accessguard.directory.index', 'label' => __('Directory')];
	}
@endphp
<div class="max-w-[1400px] mx-auto px-6 mb-4">
	<nav class="flex gap-1 border-b border-[color:var(--color-line)] text-sm overflow-x-auto">
		@foreach ($tabs as $tab)
			@php $active = request()->routeIs($tab['route']); @endphp
			<a href="{{ route($tab['route'], ['locale' => $locale]) }}"
				class="px-4 py-2.5 -mb-px border-b-2 whitespace-nowrap {{ $active
					? 'border-[color:var(--color-accent)] text-[color:var(--color-ink)] font-semibold'
					: 'border-transparent text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]' }}">
				{{ $tab['label'] }}
			</a>
		@endforeach
	</nav>
</div>

@include('tools.accessguard._feedback-widget')
