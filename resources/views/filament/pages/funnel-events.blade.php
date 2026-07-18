<x-filament-panels::page>
    @php($funnel = $this->getFunnel())
    @php($sites = $this->getPerSite())

    <x-filament::section>
        <x-slot name="heading">Trechter</x-slot>
        <x-slot name="description">
            Onze eigen meting (channel_events) — onafhankelijk van Meta/Google, inclusief bezoekers zonder cookie-toestemming.
            Unieke bezoeken per stap.
        </x-slot>

        <x-slot name="headerEnd">
            <select wire:model.live="days"
                class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                <option value="1">Vandaag</option>
                <option value="7">Laatste 7 dagen</option>
                <option value="30">Laatste 30 dagen</option>
                <option value="90">Laatste 90 dagen</option>
            </select>
        </x-slot>

        <div class="space-y-3">
            @foreach ($funnel as $step)
                <div class="flex items-center gap-4 rounded-xl border border-gray-200 p-4 dark:border-gray-700 dark:bg-white/5">
                    <div class="flex-1">
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $step['label'] }}</div>
                        <div class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">
                            {{ number_format($step['visits'], 0, ',', '.') }}
                            <span class="text-sm font-normal text-gray-400">bezoeken</span>
                        </div>
                    </div>
                    <div class="text-right">
                        @if ($step['ratio'] !== null)
                            <div class="text-lg font-semibold text-teal-600 dark:text-teal-400">{{ number_format($step['ratio'], 1, ',', '.') }}%</div>
                            <div class="text-xs text-gray-400">t.o.v. vorige stap</div>
                        @else
                            <div class="text-xs text-gray-400">startpunt</div>
                        @endif
                        <div class="mt-1 text-xs text-gray-400">{{ number_format($step['total'], 0, ',', '.') }} events</div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Per site</x-slot>
        <x-slot name="description">Voorbeeld getoond → planner → geboekt, met boekingsratio (geboekt ÷ voorbeeld getoond).</x-slot>

        @if ($sites->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">Nog geen events in deze periode.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        <th class="py-2">Site</th>
                        <th class="py-2 text-right">Voorbeeld</th>
                        <th class="py-2 text-right">Planner</th>
                        <th class="py-2 text-right">Geboekt</th>
                        <th class="py-2 text-right">Boekingsratio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sites as $row)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="py-2 text-gray-950 dark:text-white">{{ $row['site'] }}</td>
                            <td class="py-2 text-right">{{ number_format($row['preview_ready'], 0, ',', '.') }}</td>
                            <td class="py-2 text-right">{{ number_format($row['planner_opened'], 0, ',', '.') }}</td>
                            <td class="py-2 text-right">{{ number_format($row['booked'], 0, ',', '.') }}</td>
                            <td class="py-2 text-right">{{ $row['rate'] !== null ? number_format($row['rate'], 1, ',', '.') . '%' : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>
</x-filament-panels::page>
