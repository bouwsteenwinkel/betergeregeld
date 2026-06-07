<?php

namespace App\Services\QualityScan;

use App\Models\Quality\MonitoredPage;
use App\Models\Quality\QualityFinding;
use App\Models\Quality\QualityScan;
use App\Services\QualityScan\Contracts\ClaudeAnalyzerInterface;
use App\Services\QualityScan\Contracts\ContentExtractorInterface;
use App\Services\QualityScan\Contracts\PageFetcherInterface;
use App\Services\QualityScan\Data\Finding;
use App\Services\QualityScan\Exceptions\PageFetchException;

/**
 * Orkestreert één scan: scan-record → fetch → extract → (hash gelijk? vorige
 * findings kopiëren en AI overslaan) → deterministische checks → AI-analyse →
 * findings opslaan → totaalscore → status completed.
 */
class QualityScanService
{
	public function __construct(
		private readonly PageFetcherInterface $fetcher,
		private readonly ContentExtractorInterface $extractor,
		private readonly DeterministicChecker $checker,
		private readonly ClaudeAnalyzerInterface $analyzer,
	) {
	}

	public function scan(MonitoredPage $page): QualityScan
	{
		$scan = QualityScan::create([
			'monitored_page_id' => $page->id,
			'status' => 'running',
			'started_at' => now(),
		]);

		try {
			$result = $this->fetcher->fetch($page->url);
		} catch (PageFetchException $e) {
			$scan->update([
				'status' => 'failed',
				'http_status' => $e->httpStatus,
				'fetch_duration_ms' => $e->durationMs,
				'completed_at' => now(),
				'error_message' => $e->getMessage(),
			]);

			return $scan->fresh();
		}

		$scan->update(['http_status' => $result->httpStatus, 'fetch_duration_ms' => $result->durationMs]);

		$content = $this->extractor->extract($result);
		$hash = $content->hash();

		// Ongewijzigd sinds de vorige geslaagde scan? Vorige findings kopiëren,
		// AI overslaan (bespaart kosten).
		$previous = QualityScan::where('monitored_page_id', $page->id)
			->where('status', 'completed')->whereNotNull('raw_input_hash')
			->where('id', '!=', $scan->id)->latest('completed_at')->first();

		if ($previous && $previous->raw_input_hash === $hash) {
			$this->copyFindings($previous, $scan);
			$scan->update(['raw_input_hash' => $hash, 'score' => $previous->score, 'status' => 'completed', 'completed_at' => now()]);

			return $scan->fresh();
		}

		// Deterministische checks (altijd) + AI-analyse.
		$findings = $this->checker->check($content);
		$ai = $this->analyzer->analyze($content);
		$aiError = null;

		if ($ai['ok']) {
			$findings = array_merge($findings, $ai['findings'], $this->observationsToFindings($ai['free_observations']));
		} else {
			$aiError = $ai['error'];
			$findings[] = new Finding('ai', 'ai_analyse', 'warn', 'middel', 'AI-analyse niet beschikbaar: ' . $ai['error']);
		}

		$this->persistFindings($scan, $findings);

		$scan->update([
			'raw_input_hash'   => $hash,
			'score'            => $this->computeScore($findings),
			'status'           => 'completed',
			'completed_at'     => now(),
			'ai_model'         => $ai['model'] ?? null,
			'ai_input_tokens'  => $ai['input_tokens'] ?? null,
			'ai_output_tokens' => $ai['output_tokens'] ?? null,
			'error_message'    => $aiError,
		]);

		return $scan->fresh();
	}

	/** @param Finding[] $findings */
	public function computeScore(array $findings): int
	{
		$cfg = config('quality-scan.scoring');
		$score = (int) $cfg['start'];
		foreach ($findings as $f) {
			if ($f->status === 'fail') {
				$score -= (int) ($cfg['fail'][$f->severity] ?? $cfg['fail']['laag']);
			} elseif ($f->status === 'warn') {
				$score -= (int) $cfg['warn'];
			}
		}

		return max((int) $cfg['min'], $score);
	}

	/** @param Finding[] $findings */
	private function persistFindings(QualityScan $scan, array $findings): void
	{
		$seen = [];
		foreach ($findings as $f) {
			$key = $f->checkId . '|' . ($f->element ?? '');
			if (isset($seen[$key])) {
				continue; // unieke index (scan, check_id, element) respecteren
			}
			$seen[$key] = true;
			QualityFinding::create(['quality_scan_id' => $scan->id] + $f->toArray());
		}
	}

	private function copyFindings(QualityScan $from, QualityScan $to): void
	{
		foreach ($from->findings as $f) {
			QualityFinding::create([
				'quality_scan_id' => $to->id,
				'check_id' => $f->check_id, 'source' => $f->source, 'status' => $f->status,
				'severity' => $f->severity, 'finding' => $f->finding, 'suggestion' => $f->suggestion, 'element' => $f->element,
			]);
		}
		QualityFinding::create([
			'quality_scan_id' => $to->id, 'check_id' => 'pagina_ongewijzigd', 'source' => 'deterministic',
			'status' => 'pass', 'severity' => 'laag',
			'finding' => 'De pagina is ongewijzigd sinds de vorige scan; de AI-analyse is overgeslagen.',
		]);
	}

	/** Vrije AI-observaties als informatieve pass-findings (max 10, tellen niet mee in de score). */
	private function observationsToFindings(array $observations): array
	{
		$out = [];
		foreach (array_slice($observations, 0, 10) as $i => $obs) {
			$out[] = new Finding('ai', 'ai_observatie', 'pass', 'laag', (string) $obs, null, mb_substr((string) $obs, 0, 200));
		}

		return $out;
	}
}
