<?php

namespace App\Console\Commands;

use App\Models\Agency;
use App\Services\Rankdata\RankdataInvoiceService;
use Illuminate\Console\Command;

/**
 * Maakt de maandelijkse Rankdata-bureaufacturen (concept) aan — één per actief
 * bureau met klanten. Draait via de scheduler op de 1e van de maand; de
 * facturen blijven 'draft' zodat je ze nakijkt vóór verzending.
 */
class RankdataInvoiceBureaus extends Command
{
	protected $signature = 'rankdata:invoice-bureaus {--agency= : Alleen voor dit bureau (id)}';

	protected $description = 'Genereer de maandelijkse Rankdata-bureaufacturen (concept).';

	public function handle(RankdataInvoiceService $service): int
	{
		$query = Agency::query()->where('is_active', true);
		if ($id = $this->option('agency')) {
			$query->where('id', $id);
		}

		$made = 0;
		foreach ($query->get() as $agency) {
			$clients = $agency->tenants()->where('is_active', true)->count();
			if ($clients === 0) {
				$this->line("[{$agency->name}] geen actieve klanten — overgeslagen.");
				continue;
			}

			$invoice = $service->generateForAgency($agency);
			$made++;
			$this->info("[{$agency->name}] {$invoice->invoice_number} — € " . number_format($invoice->total, 2, ',', '.'));
		}

		$this->info("Klaar — {$made} concept-factuur(en) aangemaakt.");

		return self::SUCCESS;
	}
}
