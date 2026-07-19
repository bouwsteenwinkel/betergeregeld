<x-filament-panels::page>
    <style>
        .gads-stats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; }
        @media (min-width: 640px) { .gads-stats { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .gads-stat { border: 1px solid #e5e7eb; border-radius: .75rem; padding: 1rem 1.1rem; background: #fff; }
        .dark .gads-stat { border-color: #374151; background: rgba(255,255,255,.03); }
        .gads-stat-label { font-size: .8rem; color: #6b7280; }
        .dark .gads-stat-label { color: #9ca3af; }
        .gads-stat-value { margin-top: .2rem; font-size: 1.6rem; font-weight: 700; font-variant-numeric: tabular-nums; color: #111827; letter-spacing: -.01em; }
        .dark .gads-stat-value { color: #fff; }

        .gads-scroll { overflow-x: auto; }
        .gads-table { width: 100%; min-width: 860px; border-collapse: collapse; font-size: .875rem; }
        .gads-table thead th { text-align: left; font-size: .68rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; padding: .55rem .75rem; border-bottom: 1px solid #e5e7eb; white-space: nowrap; }
        .dark .gads-table thead th { color: #9ca3af; border-color: #374151; }
        .gads-table tbody td { padding: .7rem .75rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        .dark .gads-table tbody td { border-color: #1f2937; }
        .gads-table tbody tr:last-child td { border-bottom: 0; }
        .gads-num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .gads-name { font-weight: 500; color: #111827; }
        .dark .gads-name { color: #f9fafb; }
        .gads-muted { color: #9ca3af; }
        .gads-removed td { opacity: .5; }
        .gads-budget { display: inline-flex; align-items: center; gap: .4rem; }
        .gads-budget input { width: 5rem; border: 1px solid #d1d5db; border-radius: .5rem; padding: .3rem .5rem; font-size: .85rem; text-align: right; font-variant-numeric: tabular-nums; background: #fff; color: #111827; }
        .dark .gads-budget input { border-color: #4b5563; background: rgba(255,255,255,.04); color: #fff; }
        .gads-form { display: grid; grid-template-columns: 1fr; gap: 1rem; }
        @media (min-width: 640px) { .gads-form { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .gads-field { display: block; }
        .gads-field > span { display: block; font-size: .82rem; font-weight: 500; color: #374151; margin-bottom: .3rem; }
        .dark .gads-field > span { color: #d1d5db; }
        .gads-field input { width: 100%; border: 1px solid #d1d5db; border-radius: .5rem; padding: .5rem .7rem; font-size: .875rem; background: #fff; color: #111827; }
        .dark .gads-field input { border-color: #4b5563; background: rgba(255,255,255,.04); color: #fff; }
        .gads-hint { margin-top: .75rem; font-size: .78rem; color: #6b7280; }
        .dark .gads-hint { color: #9ca3af; }
    </style>

    @if (! $connected)
        <x-filament::section>
            <x-slot name="heading">Google Ads is nog niet gekoppeld</x-slot>
            <p class="gads-hint">
                Er is nog geen actieve API-koppeling in deze omgeving. Koppel via de terminal met
                <code>php artisan ads:connect</code> en controleer met <code>php artisan ads:status</code>.
            </p>
        </x-filament::section>
    @else
        @php
            $t = $this->totals();
            $kaarten = [
                ['Vertoningen', number_format($t['impressions'], 0, ',', '.')],
                ['Klikken', number_format($t['clicks'], 0, ',', '.')],
                ['Kosten', '€ ' . number_format($t['cost'], 2, ',', '.')],
                ['Conversies', rtrim(rtrim(number_format($t['conversions'], 1, ',', '.'), '0'), ',')],
            ];
        @endphp

        <x-filament::section>
            <x-slot name="heading">Prestaties — alle campagnes (sinds start)</x-slot>
            <x-slot name="description">Live uit het gekoppelde Google Ads-account.</x-slot>

            <div class="gads-stats">
                @foreach ($kaarten as $kaart)
                    <div class="gads-stat">
                        <div class="gads-stat-label">{{ $kaart[0] }}</div>
                        <div class="gads-stat-value">{{ $kaart[1] }}</div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        <x-filament::section collapsible collapsed>
            <x-slot name="heading">Nieuwe campagne aanmaken</x-slot>
            <x-slot name="description">Maakt een Search-campagne vanuit het vaste template — altijd gepauzeerd, niets gaat direct live.</x-slot>

            <div class="gads-form">
                <label class="gads-field"><span>Eind-URL</span>
                    <input type="url" wire:model="newUrl"></label>
                <label class="gads-field"><span>Dagbudget (€)</span>
                    <input type="number" step="1" min="1" wire:model="newBudget"></label>
                <label class="gads-field"><span>Max. CPC (€)</span>
                    <input type="number" step="0.10" min="0.1" wire:model="newMaxCpc"></label>
            </div>

            <p class="gads-hint">Heel Nederland · Nederlands · alleen Google Zoeken · 2 advertentiegroepen (9 zoekwoorden), 14 uitsluitingen, 1 RSA per groep, plus extensies (4 sitelinks, 6 highlights, fragment, bel-asset).</p>

            <div style="margin-top: 1rem;">
                <x-filament::button wire:click="createCampaign" wire:loading.attr="disabled" wire:target="createCampaign" icon="heroicon-o-plus">
                    Campagne aanmaken (gepauzeerd)
                </x-filament::button>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Campagnes</x-slot>
            <x-slot name="headerEnd">
                <x-filament::button size="sm" color="gray" wire:click="laden" icon="heroicon-o-arrow-path">Vernieuwen</x-filament::button>
            </x-slot>

            <div class="gads-scroll">
                <table class="gads-table">
                    <thead>
                        <tr>
                            <th>Campagne</th>
                            <th>Status</th>
                            <th>Dagbudget</th>
                            <th class="gads-num">Vertoningen</th>
                            <th class="gads-num">Klikken</th>
                            <th class="gads-num">Kosten</th>
                            <th class="gads-num">Conv.</th>
                            <th class="gads-num">Actie</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (empty($campaigns))
                            <tr><td colspan="8" style="padding: 1.5rem; text-align: center;" class="gads-muted">Nog geen campagnes in dit account.</td></tr>
                        @endif

                        @foreach ($campaigns as $c)
                            <tr class="{{ $c['status'] === 'REMOVED' ? 'gads-removed' : '' }}">
                                <td class="gads-name">{{ $c['name'] }}</td>
                                <td>
                                    <x-filament::badge :color="['ENABLED' => 'success', 'PAUSED' => 'warning', 'REMOVED' => 'danger'][$c['status']] ?? 'gray'">
                                        {{ ['ENABLED' => 'Actief', 'PAUSED' => 'Gepauzeerd', 'REMOVED' => 'Verwijderd'][$c['status']] ?? $c['status'] }}
                                    </x-filament::badge>
                                </td>
                                <td>
                                    @if ($c['status'] !== 'REMOVED')
                                        <div class="gads-budget">
                                            <span class="gads-muted">€</span>
                                            <input type="number" step="1" min="1" wire:model="budgets.{{ $c['id'] }}">
                                            <x-filament::button size="xs" color="gray" wire:click="saveBudget('{{ $c['id'] }}')">Opslaan</x-filament::button>
                                        </div>
                                    @else
                                        <span class="gads-muted">—</span>
                                    @endif
                                </td>
                                <td class="gads-num">{{ number_format($c['impressions'], 0, ',', '.') }}</td>
                                <td class="gads-num">{{ number_format($c['clicks'], 0, ',', '.') }}</td>
                                <td class="gads-num">€ {{ number_format($c['cost'], 2, ',', '.') }}</td>
                                <td class="gads-num">{{ rtrim(rtrim(number_format($c['conversions'], 1, ',', '.'), '0'), ',') }}</td>
                                <td class="gads-num">
                                    @if ($c['status'] === 'ENABLED')
                                        <x-filament::button size="xs" color="warning" wire:click="pause('{{ $c['id'] }}')">Pauzeren</x-filament::button>
                                    @elseif ($c['status'] === 'PAUSED')
                                        <x-filament::button size="xs" color="success" wire:click="enable('{{ $c['id'] }}')" wire:confirm="Weet je het zeker? De campagne gaat live en geeft budget uit.">Activeren</x-filament::button>
                                    @else
                                        <span class="gads-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
