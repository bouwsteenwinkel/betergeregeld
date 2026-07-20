<?php

namespace App\Console\Commands;

use App\Models\Seo\SeoProperty;
use App\Services\Seo\GscDailyImporter;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Daily import van Google Search Console search-analytics naar
 * seo_query_daily. Loopt per actieve property door alle dagen tussen
 * last_imported_date+1 en gisteren (max BACKFILL_DAYS terug).
 *
 * Aanroepen:
 *   php artisan seo:import-gsc                # alle properties
 *   php artisan seo:import-gsc --days=14     # backfill-window
 *   php artisan seo:import-gsc --property=1  # alleen één
 *   php artisan seo:import-gsc --date=2026-05-20  # specifieke dag
 */
class SeoImportGsc extends Command
{
	protected $signature = 'seo:import-gsc
		{--days=30 : Max aantal dagen backfill als last_imported_date leeg is}
		{--property= : Importeer alleen voor één property-id}
		{--date= : Specifieke datum (YYYY-MM-DD), anders alle openstaande tot gisteren}';

	protected $description = 'Import Google Search Console search-analytics naar seo_query_daily.';

	public function handle(GscDailyImporter $importer): int
	{
		$yesterday = Carbon::yesterday()->toDateString();
		$earliest  = Carbon::now()->subDays((int) $this->option('days'))->toDateString();
		$onlyDate  = $this->option('date');

		$query = SeoProperty::query()->where('is_active', true)->where('is_demo', false);
		if ($pid = $this->option('property')) {
			$query->where('id', (int) $pid);
		}
		$properties = $query->get();

		if ($properties->isEmpty()) {
			$this->warn('Geen actieve properties — niets te doen.');
			return self::SUCCESS;
		}

		$totalRows = 0;
		$totalDays = 0;
		$errors    = [];

		foreach ($properties as $prop) {
			if ($onlyDate) {
				$dates = [$onlyDate];
			} else {
				$start = $prop->last_imported_date
					? $prop->last_imported_date->copy()->addDay()->toDateString()
					: $earliest;
				if ($start < $earliest) {
					$start = $earliest;
				}
				if ($start > $yesterday) {
					$this->line("[{$prop->label}] up-to-date.");
					continue;
				}
				$dates = [];
				$cursor = $start;
				while ($cursor <= $yesterday) {
					$dates[] = $cursor;
					$cursor = Carbon::parse($cursor)->addDay()->toDateString();
				}
			}

			$this->info("[{$prop->label}] {$prop->site_url} — " . count($dates) . ' dag(en)');

			foreach ($dates as $date) {
				$r = $importer->importDay($prop->id, $date);
				if ($r['ok']) {
					$totalRows += $r['rows'];
					$totalDays++;
					$this->line("  ✓ {$date}: {$r['rows']} rijen");
				} else {
					$errors[] = "{$prop->site_url} {$date}: " . ($r['message'] ?? 'onbekend');
					$this->error("  ✕ {$date}: " . ($r['message'] ?? 'onbekend'));
					break; // stop deze property bij fout, ga naar volgende
				}
			}
		}

		$this->newLine();
		$this->info("Klaar: {$totalDays} dagen, {$totalRows} rijen.");
		if (!empty($errors)) {
			$this->warn(count($errors) . ' fout(en):');
			foreach (array_slice($errors, 0, 5) as $e) {
				$this->line('  - ' . $e);
			}
		}

		// Alleen falen als er fouten zijn én er niets is geïmporteerd (echte
		// systeemfout). Een geslaagde import met één probleem-property valt niet om;
		// per-property problemen staan gelogd (last_import_error) en worden apart
		// per property gealerteerd.
		return (!empty($errors) && $totalDays === 0) ? self::FAILURE : self::SUCCESS;
	}
}
