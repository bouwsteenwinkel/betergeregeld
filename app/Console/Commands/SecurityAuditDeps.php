<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Process\Process;

/**
 * Draait `composer audit` / `npm audit` op de geconfigureerde projecten
 * (config('security.audit_paths')) en bewaart de advisories. Mailt het
 * platform alleen als de set wijzigt. Alleen voor code-toegang-projecten;
 * vereist composer/npm in PATH op de server.
 */
class SecurityAuditDeps extends Command
{
	protected $signature = 'security:audit-deps';

	protected $description = 'Composer/npm dependency-audit op de eigen code-projecten.';

	public function handle(): int
	{
		$rows = [];
		foreach ((array) config('security.audit_paths', []) as $path) {
			$path = rtrim((string) $path, "/\\");
			if ($path === '' || ! is_dir($path)) {
				continue;
			}
			$label = basename($path) ?: $path;

			if (is_file($path . '/composer.lock')) {
				$rows = array_merge($rows, $this->composerAudit($path, $label));
			}
			if (is_file($path . '/package-lock.json')) {
				$rows = array_merge($rows, $this->npmAudit($path, $label));
			}
		}

		DB::table('dependency_advisories')->truncate();
		if ($rows) {
			DB::table('dependency_advisories')->insert($rows);
		}

		$this->info('Klaar — ' . count($rows) . ' advisory(s).');
		$this->alertIfChanged($rows);

		return self::SUCCESS;
	}

	/** @return array<int,array<string,mixed>> */
	private function composerAudit(string $path, string $label): array
	{
		$out = $this->runJson(['composer', 'audit', '--locked', '--format=json', '--no-interaction'], $path, $label);
		if (! is_array($out)) {
			return [];
		}

		$rows = [];
		foreach (($out['advisories'] ?? []) as $package => $advisories) {
			foreach ((array) $advisories as $a) {
				$rows[] = [
					'ecosystem'   => 'composer',
					'project'     => mb_substr($label, 0, 120),
					'package'     => mb_substr((string) $package, 0, 190),
					'severity'    => isset($a['severity']) ? mb_substr((string) $a['severity'], 0, 16) : null,
					'title'       => mb_substr((string) ($a['title'] ?? 'Advisory'), 0, 255),
					'advisory_id' => isset($a['advisoryId']) ? mb_substr((string) $a['advisoryId'], 0, 64) : null,
					'cve'         => isset($a['cve']) ? mb_substr((string) $a['cve'], 0, 32) : null,
					'fixed_in'    => null,
					'link'        => isset($a['link']) ? mb_substr((string) $a['link'], 0, 500) : null,
					'imported_at' => now(),
				];
			}
		}

		return $rows;
	}

	/** @return array<int,array<string,mixed>> */
	private function npmAudit(string $path, string $label): array
	{
		$npm = PHP_OS_FAMILY === 'Windows' ? 'npm.cmd' : 'npm';
		$out = $this->runJson([$npm, 'audit', '--json'], $path, $label);
		if (! is_array($out)) {
			return [];
		}

		$rows = [];
		foreach (($out['vulnerabilities'] ?? []) as $package => $v) {
			$title = 'Vulnerability';
			$link = null;
			foreach ((array) ($v['via'] ?? []) as $entry) {
				if (is_array($entry)) {
					$title = $entry['title'] ?? $title;
					$link = $entry['url'] ?? $link;
					break;
				}
			}
			$rows[] = [
				'ecosystem'   => 'npm',
				'project'     => mb_substr($label, 0, 120),
				'package'     => mb_substr((string) $package, 0, 190),
				'severity'    => isset($v['severity']) ? mb_substr((string) $v['severity'], 0, 16) : null,
				'title'       => mb_substr((string) $title, 0, 255),
				'advisory_id' => null,
				'cve'         => null,
				'fixed_in'    => isset($v['fixAvailable']['version']) ? mb_substr((string) $v['fixAvailable']['version'], 0, 40) : null,
				'link'        => $link ? mb_substr((string) $link, 0, 500) : null,
				'imported_at' => now(),
			];
		}

		return $rows;
	}

	private function runJson(array $command, string $cwd, string $label): ?array
	{
		$process = new Process($command, $cwd);
		$process->setTimeout(180);
		try {
			$process->run();
		} catch (\Throwable $e) {
			$this->warn("[{$label}] {$command[0]} niet uitgevoerd: " . $e->getMessage());

			return null;
		}

		// audit-tools geven exit≠0 bij gevonden advisories; JSON staat in stdout.
		$json = json_decode($process->getOutput(), true);

		return is_array($json) ? $json : null;
	}

	/** @param array<int,array<string,mixed>> $rows */
	private function alertIfChanged(array $rows): void
	{
		$to = config('monitor.alert_email');
		if (! $to) {
			return;
		}

		$sig = md5((string) json_encode(array_map(
			fn ($r) => $r['ecosystem'] . '|' . $r['project'] . '|' . $r['package'] . '|' . $r['title'],
			$rows,
		)));
		if (Cache::get('security:deps:sig') === $sig) {
			return;
		}
		Cache::put('security:deps:sig', $sig, now()->addDays(60));

		if (empty($rows)) {
			return; // niets te melden (signatuur wel bijgewerkt)
		}

		$lines = array_map(
			fn ($r) => "- [{$r['ecosystem']}] {$r['project']}/{$r['package']}: {$r['title']} (" . ($r['severity'] ?? '?') . ')',
			array_slice($rows, 0, 30),
		);
		Mail::raw(
			'De dependency-audit vond ' . count($rows) . " advisory(s):\n\n" . implode("\n", $lines),
			fn ($m) => $m->to($to)->subject('[Security] Dependency-advisories gevonden'),
		);
	}
}
