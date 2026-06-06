<x-filament-panels::page>
	@php($data = $this->getStatusData())

	@if (empty($data))
		<x-filament::section>
			<x-slot name="heading">Geen server gekoppeld</x-slot>
			<p class="text-sm text-gray-500 dark:text-gray-400">
				Er is nog geen server aan jouw account gekoppeld. Neem contact op met Beter Geregeld.
			</p>
		</x-filament::section>
	@else
		<x-filament::section>
			<x-slot name="heading">{{ $data['name'] }}</x-slot>
			<x-slot name="description">Actuele status en beschikbaarheid van jouw server.</x-slot>

			<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
				<div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700 dark:bg-white/5">
					<div class="text-sm text-gray-500 dark:text-gray-400">Status</div>
					<div class="mt-2">
						<x-filament::badge :color="$data['status_color']" size="lg">
							{{ $data['status_label'] }}
						</x-filament::badge>
					</div>
					<div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
						Laatste contact:
						{{ $data['last_seen']?->diffForHumans() ?? 'nooit' }}
					</div>
				</div>

				<div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700 dark:bg-white/5">
					<div class="text-sm text-gray-500 dark:text-gray-400">Beschikbaarheid (24 uur)</div>
					<div class="mt-2 text-3xl font-bold text-gray-950 dark:text-white">
						{{ number_format($data['uptime_24h'], 2) }}%
					</div>
				</div>

				<div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700 dark:bg-white/5">
					<div class="text-sm text-gray-500 dark:text-gray-400">Beschikbaarheid (30 dagen)</div>
					<div class="mt-2 text-3xl font-bold text-gray-950 dark:text-white">
						{{ number_format($data['uptime_30d'], 2) }}%
					</div>
				</div>
			</div>

			<p class="mt-4 text-xs text-gray-400 dark:text-gray-500">
				Beschikbaarheid wordt gemeten via de heartbeat van de monitoring-agent
				(ontvangen metingen ÷ verwachte metingen per periode).
			</p>
		</x-filament::section>
	@endif
</x-filament-panels::page>
