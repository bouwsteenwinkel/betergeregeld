<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\Rankdata\RankdataReportSender;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Mailt maandelijks het PDF-klantrapport naar elke actieve klant met sites
 * (demo uitgesloten). Draait via de scheduler op de 1e.
 */
class RankdataSendReports extends Command
{
	protected $signature = 'rankdata:send-reports {--tenant= : Alleen deze klant (id)}';

	protected $description = 'Mail het maandelijkse PDF-klantrapport naar de klanten.';

	public function handle(RankdataReportSender $sender): int
	{
		$query = Tenant::query()->where('is_active', true)->where('is_demo', false)->with('agency');
		if ($id = $this->option('tenant')) {
			$query->where('id', $id);
		}

		$sent = 0;
		foreach ($query->get() as $tenant) {
			$hasSites = DB::table('seo_properties')->where('tenant_id', $tenant->id)->where('is_active', 1)->exists();
			if (! $hasSites) {
				continue;
			}

			if ($sender->send($tenant)) {
				$sent++;
				$this->info("[{$tenant->name}] rapport verzonden");
			} else {
				$this->line("[{$tenant->name}] geen ontvanger — overgeslagen");
			}
		}

		$this->info("Klaar — {$sent} rapport(en) verzonden.");

		return self::SUCCESS;
	}
}
