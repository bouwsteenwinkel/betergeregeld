<x-filament-panels::page>
    @if (! $connected)
        <x-filament::section>
            <x-slot name="heading">Google Ads is nog niet gekoppeld</x-slot>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Er is nog geen actieve API-koppeling in deze omgeving. Koppel via de terminal met
                <code class="rounded bg-gray-100 px-1 py-0.5 text-xs dark:bg-white/10">php artisan ads:connect</code>
                en controleer met <code class="rounded bg-gray-100 px-1 py-0.5 text-xs dark:bg-white/10">php artisan ads:status</code>.
            </p>
        </x-filament::section>
    @else
        @php($t = $this->totals())

        {{-- Kerncijfers over alle campagnes --}}
        <x-filament::section>
            <x-slot name="heading">Prestaties — alle campagnes (sinds start)</x-slot>
            <x-slot name="description">Live uit het gekoppelde Google Ads-account.</x-slot>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ([
                    ['Vertoningen', number_format($t['impressions'], 0, ',', '.')],
                    ['Klikken', number_format($t['clicks'], 0, ',', '.')],
                    ['Kosten', '€ ' . number_format($t['cost'], 2, ',', '.')],
                    ['Conversies', rtrim(rtrim(number_format($t['conversions'], 1, ',', '.'), '0'), ',')],
                ] as [$label, $waarde])
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700 dark:bg-white/5">
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</div>
                        <div class="mt-1 text-2xl font-bold tabular-nums text-gray-950 dark:text-white">{{ $waarde }}</div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        {{-- Nieuwe campagne --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">Nieuwe campagne aanmaken</x-slot>
            <x-slot name="description">Maakt een Search-campagne vanuit het vaste template — altijd gepauzeerd, niets gaat direct live.</x-slot>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <label class="block">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Eind-URL</span>
                    <input type="url" wire:model="newUrl" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-white/5 dark:text-white">
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Dagbudget (€)</span>
                    <input type="number" step="1" min="1" wire:model="newBudget" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-white/5 dark:text-white">
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Max. CPC (€)</span>
                    <input type="number" step="0.10" min="0.1" wire:model="newMaxCpc" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-white/5 dark:text-white">
                </label>
            </div>

            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Heel Nederland · Nederlands · alleen Google Zoeken · 2 advertentiegroepen (9 zoekwoorden), 14 uitsluitingen, 1 RSA per groep.
            </p>

            <div class="mt-4">
                <x-filament::button wire:click="createCampaign" wire:loading.attr="disabled" wire:target="createCampaign" icon="heroicon-o-plus">
                    <span wire:loading.remove wire:target="createCampaign">Campagne aanmaken (gepauzeerd)</span>
                    <span wire:loading wire:target="createCampaign">Bezig…</span>
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Campagne-overzicht + beheer --}}
        <x-filament::section>
            <x-slot name="heading">Campagnes</x-slot>
            <x-slot name="headerEnd">
                <x-filament::button size="sm" color="gray" wire:click="laden" wire:loading.attr="disabled" wire:target="laden" icon="heroicon-o-arrow-path">Vernieuwen</x-filament::button>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            <th class="py-2 pr-3">Campagne</th>
                            <th class="py-2 pr-3">Status</th>
                            <th class="py-2 pr-3">Dagbudget</th>
                            <th class="py-2 pr-3 text-right">Vertoningen</th>
                            <th class="py-2 pr-3 text-right">Klikken</th>
                            <th class="py-2 pr-3 text-right">Kosten</th>
                            <th class="py-2 pr-3 text-right">Conv.</th>
                            <th class="py-2 pr-3 text-right">Actie</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($campaigns as $c)
                            @php
                                $removed = $c['status'] === 'REMOVED';
                                $labels  = ['ENABLED' => ['Actief', 'success'], 'PAUSED' => ['Gepauzeerd', 'warning'], 'REMOVED' => ['Verwijderd', 'danger']];
                                [$stLabel, $stColor] = $labels[$c['status']] ?? [$c['status'], 'gray'];
                            @endphp
                            <tr class="@if ($removed) opacity-50 @endif align-middle">
                                <td class="py-3 pr-3 font-medium text-gray-900 dark:text-white">{{ $c['name'] }}</td>
                                <td class="py-3 pr-3"><x-filament::badge :color="$stColor">{{ $stLabel }}</x-filament::badge></td>
                                <td class="py-3 pr-3">
                                    @unless ($removed)
                                        <div class="flex items-center gap-1">
                                            <span class="text-gray-400">€</span>
                                            <input type="number" step="1" min="1" wire:model="budgets.{{ $c['id'] }}" class="w-20 rounded-lg border border-gray-300 bg-white px-2 py-1 text-sm tabular-nums dark:border-gray-600 dark:bg-white/5 dark:text-white">
                                            <x-filament::button size="xs" color="gray" wire:click="saveBudget('{{ $c['id'] }}')" wire:target="saveBudget('{{ $c['id'] }}')" wire:loading.attr="disabled">Opslaan</x-filament::button>
                                        </div>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endunless
                                </td>
                                <td class="py-3 pr-3 text-right tabular-nums">{{ number_format($c['impressions'], 0, ',', '.') }}</td>
                                <td class="py-3 pr-3 text-right tabular-nums">{{ number_format($c['clicks'], 0, ',', '.') }}</td>
                                <td class="py-3 pr-3 text-right tabular-nums">€ {{ number_format($c['cost'], 2, ',', '.') }}</td>
                                <td class="py-3 pr-3 text-right tabular-nums">{{ rtrim(rtrim(number_format($c['conversions'], 1, ',', '.'), '0'), ',') }}</td>
                                <td class="py-3 pr-3 text-right">
                                    @if ($c['status'] === 'ENABLED')
                                        <x-filament::button size="xs" color="warning" wire:click="pause('{{ $c['id'] }}')">Pauzeren</x-filament::button>
                                    @elseif ($c['status'] === 'PAUSED')
                                        <x-filament::button size="xs" color="success" wire:click="enable('{{ $c['id'] }}')" wire:confirm="Weet je het zeker? De campagne gaat live en geeft budget uit.">Activeren</x-filament::button>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="py-6 text-center text-gray-500 dark:text-gray-400">Nog geen campagnes in dit account.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
