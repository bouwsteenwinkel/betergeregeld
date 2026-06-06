<x-filament-panels::page>
	@php($data = $this->getSeoData())

	@if (empty($data))
		<x-filament::section>
			<x-slot name="heading">Geen SEO-property gekoppeld</x-slot>
			<p class="text-sm text-gray-500 dark:text-gray-400">
				Er is nog geen Search Console-property aan jouw account gekoppeld. Neem contact op met Beter Geregeld.
			</p>
		</x-filament::section>
	@else
		<x-filament::section>
			<x-slot name="heading">Zoekprestaties — laatste 28 dagen</x-slot>
			<x-slot name="description">Bron: Google Search Console.</x-slot>

			<div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
				<div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700 dark:bg-white/5">
					<div class="text-sm text-gray-500 dark:text-gray-400">Klikken</div>
					<div class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ number_format($data['clicks'], 0, ',', '.') }}</div>
				</div>
				<div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700 dark:bg-white/5">
					<div class="text-sm text-gray-500 dark:text-gray-400">Vertoningen</div>
					<div class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ number_format($data['impressions'], 0, ',', '.') }}</div>
				</div>
				<div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700 dark:bg-white/5">
					<div class="text-sm text-gray-500 dark:text-gray-400">Gem. CTR</div>
					<div class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ number_format($data['ctr'], 2, ',', '.') }}%</div>
				</div>
				<div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700 dark:bg-white/5">
					<div class="text-sm text-gray-500 dark:text-gray-400">Gem. positie</div>
					<div class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ number_format($data['position'], 1, ',', '.') }}</div>
				</div>
			</div>
		</x-filament::section>

		<x-filament::section>
			<x-slot name="heading">Top zoektermen</x-slot>

			@if ($data['top_queries']->isEmpty())
				<p class="text-sm text-gray-500 dark:text-gray-400">Nog geen data voor deze periode.</p>
			@else
				<table class="w-full text-sm">
					<thead>
						<tr class="border-b border-gray-200 text-left text-gray-500 dark:border-gray-700 dark:text-gray-400">
							<th class="py-2">Zoekterm</th>
							<th class="py-2 text-right">Klikken</th>
							<th class="py-2 text-right">Vertoningen</th>
							<th class="py-2 text-right">Gem. positie</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($data['top_queries'] as $row)
							<tr class="border-b border-gray-100 dark:border-gray-800">
								<td class="py-2 text-gray-950 dark:text-white">{{ $row->query }}</td>
								<td class="py-2 text-right">{{ number_format((int) $row->clicks, 0, ',', '.') }}</td>
								<td class="py-2 text-right">{{ number_format((int) $row->impressions, 0, ',', '.') }}</td>
								<td class="py-2 text-right">{{ number_format((float) $row->position, 1, ',', '.') }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			@endif
		</x-filament::section>
	@endif
</x-filament-panels::page>
