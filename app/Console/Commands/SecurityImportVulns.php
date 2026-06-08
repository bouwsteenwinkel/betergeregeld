<?php

namespace App\Console\Commands;

use Composer\CaBundle\CaBundle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Importeert de Wordfence Intelligence vuln-feed (gratis) naar de lokale
 * `vulnerabilities`-cache. Full refresh (truncate + bulk insert), één rij per
 * (vuln × software × versiebereik). Dagelijks via de scheduler.
 */
class SecurityImportVulns extends Command
{
	protected $signature = 'security:import-vulns {--file= : Lokaal JSON-bestand i.p.v. download}';

	protected $description = 'Importeer de Wordfence vuln-feed naar de lokale cache.';

	private const FEED = 'https://www.wordfence.com/api/intelligence/v2/vulnerabilities/production';

	public function handle(): int
	{
		@ini_set('memory_limit', '1024M');

		$raw = $this->option('file')
			? @file_get_contents($this->option('file'))
			: $this->download();

		if (! $raw) {
			$this->error('Geen feed-data ontvangen.');

			return self::FAILURE;
		}

		$data = json_decode($raw, true);
		unset($raw);
		if (! is_array($data)) {
			$this->error('Ongeldige JSON in de feed.');

			return self::FAILURE;
		}

		$now = now();
		$rows = [];
		$count = 0;

		DB::table('vulnerabilities')->truncate();

		foreach ($data as $extId => $vuln) {
			$title = (string) ($vuln['title'] ?? 'Onbekende kwetsbaarheid');
			$cve = $vuln['cve'] ?? null;
			$severity = isset($vuln['cvss']['rating']) ? strtolower((string) $vuln['cvss']['rating']) : null;
			$cvss = isset($vuln['cvss']['score']) ? (float) $vuln['cvss']['score'] : null;
			$ref = $vuln['references'][0] ?? null;

			foreach (($vuln['software'] ?? []) as $sw) {
				$type = $sw['type'] ?? null;
				if (! in_array($type, ['core', 'plugin', 'theme'], true)) {
					continue;
				}
				$slug = trim((string) ($sw['slug'] ?? ''));
				if ($slug === '') {
					continue;
				}
				$patched = $sw['patched_versions'][0] ?? null;

				foreach (($sw['affected_versions'] ?? []) as $range) {
					$from = $range['from_version'] ?? '*';
					$to = $range['to_version'] ?? '*';
					$from = ($from === '*' || $from === '') ? null : $from;
					$to = ($to === '*' || $to === '') ? null : $to;

					$rows[] = [
						'source'         => 'wordfence',
						'ext_id'         => mb_substr((string) $extId, 0, 64),
						'software_type'  => $type,
						'slug'           => mb_substr($slug, 0, 190),
						'title'          => mb_substr($title, 0, 255),
						'severity'       => $severity ? mb_substr($severity, 0, 16) : null,
						'cvss'           => $cvss,
						'cve'            => $cve ? mb_substr((string) $cve, 0, 32) : null,
						'from_version'   => $from ? mb_substr((string) $from, 0, 40) : null,
						'from_inclusive' => (bool) ($range['from_inclusive'] ?? true),
						'to_version'     => $to ? mb_substr((string) $to, 0, 40) : null,
						'to_inclusive'   => (bool) ($range['to_inclusive'] ?? false),
						'patched_in'     => $patched ? mb_substr((string) $patched, 0, 40) : null,
						'reference'      => $ref ? mb_substr((string) $ref, 0, 500) : null,
						'imported_at'    => $now,
					];
					$count++;

					if (count($rows) >= 1000) {
						DB::table('vulnerabilities')->insert($rows);
						$rows = [];
					}
				}
			}
		}

		if ($rows) {
			DB::table('vulnerabilities')->insert($rows);
		}

		$this->info("Klaar — {$count} vuln-records geïmporteerd.");

		return self::SUCCESS;
	}

	private function download(): ?string
	{
		$resp = Http::timeout(180)
			->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
			->get(self::FEED);

		return $resp->ok() ? $resp->body() : null;
	}
}
