<?php

namespace App\Console\Commands;

use App\Models\Seo\SeoProperty;
use App\Services\Security\SecurityScanService;
use Illuminate\Console\Command;

/**
 * Security-scan (fase 1, agent-loos) over alle actieve sites: malware/blacklist
 * (Safe Browsing + DNSBL) en mixed-content/broken-links.
 */
class SecurityScanCommand extends Command
{
	protected $signature = 'security:scan {--property= : Alleen deze property-id}';

	protected $description = 'Scan sites op malware/blacklist + mixed content/broken links.';

	public function handle(SecurityScanService $service): int
	{
		$query = SeoProperty::query()->where('is_active', true);
		if ($id = $this->option('property')) {
			$query->where('id', (int) $id);
		}
		$sites = $query->get();

		if ($sites->isEmpty()) {
			$this->warn('Geen actieve sites — niets te doen.');

			return self::SUCCESS;
		}

		foreach ($sites as $site) {
			$scan = $service->scan($site);
			$flag = $scan->isFlagged() ? '  /!\ GEFLAGD' : '';
			$this->info("[{$site->label}] safe-browsing={$scan->safe_browsing} blacklist="
				. ($scan->blacklisted ? 'JA' : 'nee') . " mixed={$scan->mixed_content_count}"
				. " broken={$scan->broken_link_count}/{$scan->links_checked}{$flag}");
		}

		$this->info('Klaar — ' . $sites->count() . ' site(s) gescand.');

		return self::SUCCESS;
	}
}
