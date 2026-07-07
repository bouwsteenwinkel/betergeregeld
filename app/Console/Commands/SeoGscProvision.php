<?php

namespace App\Console\Commands;

use App\Models\Channel\Site;
use App\Services\Seo\GscProvisioner;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Regelt Google Search Console in voor channel-site-domein(en):
 * verifieer via DNS-TXT (OpenProvider) → property toevoegen → sitemap indienen.
 *
 *   php artisan seo:gsc-provision loodgieter        # één site (key of domein)
 *   php artisan seo:gsc-provision --all             # alle LIVE sites met domein
 *   php artisan seo:gsc-provision --all --dry-run   # laat alleen zien wat het zou doen
 */
class SeoGscProvision extends Command
{
	protected $signature = 'seo:gsc-provision
		{site? : site-key of domein}
		{--all : alle live sites met een domein}
		{--dry-run : toon alleen het plan, doe geen API-calls}';

	protected $description = 'Verifieer domein (DNS-TXT via OpenProvider), voeg GSC-property toe en dien sitemap in.';

	public function handle(GscProvisioner $prov): int
	{
		$sites = $this->targets();
		if ($sites->isEmpty()) {
			$this->error('Geen sites gevonden. Geef een site-key/domein of gebruik --all.');
			return self::FAILURE;
		}

		$dry = (bool) $this->option('dry-run');
		$this->info(($dry ? '[DRY-RUN] ' : '') . "GSC-provisioning voor {$sites->count()} site(s)\n");

		$okCount = 0;
		foreach ($sites as $site) {
			$this->line("── <options=bold>{$site->key}</> ({$site->domain})");
			try {
				$r = $prov->provision($site, $dry);
				foreach ($r['steps'] as $k => $v) {
					$this->line(sprintf('   %-9s %s', $k, $v));
				}
				$this->line('   => ' . ($r['ok'] ? '<info>OK</info>' : '<comment>onvolledig</comment>'));
				$okCount += $r['ok'] ? 1 : 0;
			} catch (\Throwable $e) {
				$this->line('   <error>FOUT: ' . $e->getMessage() . '</error>');
			}
			$this->newLine();
		}

		$this->info("Klaar: {$okCount}/{$sites->count()} volledig.");
		return self::SUCCESS;
	}

	private function targets(): Collection
	{
		if ($this->option('all')) {
			return Site::query()
				->whereNotNull('domain')->where('domain', '!=', '')
				->where('status', 'live')
				->orderBy('key')->get();
		}

		$arg = trim((string) $this->argument('site'));
		if ($arg === '') {
			return collect();
		}

		return Site::query()
			->where('key', $arg)
			->orWhere('domain', $arg)
			->orWhere('domain', 'like', '%' . $arg . '%')
			->get();
	}
}
