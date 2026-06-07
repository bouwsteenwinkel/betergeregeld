<?php

namespace App\Console\Commands;

use App\Services\QualityScan\Contracts\ClaudeAnalyzerInterface;
use App\Services\QualityScan\Contracts\ContentExtractorInterface;
use App\Services\QualityScan\Contracts\PageFetcherInterface;
use App\Services\QualityScan\Data\Finding;
use App\Services\QualityScan\DeterministicChecker;
use App\Services\QualityScan\Exceptions\PageFetchException;
use App\Services\QualityScan\QualityScanService;
use Illuminate\Console\Command;

/**
 * Handmatige kwaliteitsscan tegen een willekeurige URL — zonder opslag, output
 * als nette tabel. Voor testen/ontwikkelen. Met --no-ai sla je de AI-call over.
 */
class QualityScanRun extends Command
{
	protected $signature = 'quality-scan:run {url} {--no-ai : Sla de AI-analyse over}';

	protected $description = 'Scan een URL op kwaliteit en toon de bevindingen (zonder op te slaan).';

	public function handle(
		PageFetcherInterface $fetcher,
		ContentExtractorInterface $extractor,
		DeterministicChecker $checker,
		ClaudeAnalyzerInterface $analyzer,
		QualityScanService $service,
	): int {
		$url = (string) $this->argument('url');

		try {
			$result = $fetcher->fetch($url);
		} catch (PageFetchException $e) {
			$this->error('Ophalen mislukt: ' . $e->getMessage());

			return self::FAILURE;
		}
		$this->line("HTTP {$result->httpStatus} · {$result->durationMs} ms");

		$content = $extractor->extract($result);
		/** @var Finding[] $findings */
		$findings = $checker->check($content);

		if (! $this->option('no-ai')) {
			$ai = $analyzer->analyze($content);
			if ($ai['ok']) {
				$findings = array_merge($findings, $ai['findings']);
				$this->line("AI: {$ai['model']} · {$ai['input_tokens']}/{$ai['output_tokens']} tokens");
			} else {
				$this->warn("AI overgeslagen: {$ai['error']}");
			}
		}

		$rows = [];
		foreach ($findings as $f) {
			if ($f->status === 'pass') {
				continue;
			}
			$rows[] = [strtoupper($f->status), $f->severity, $f->source, $f->checkId, mb_strimwidth($f->finding, 0, 60, '…')];
		}
		$this->newLine();
		$this->table(['Status', 'Ernst', 'Bron', 'Check', 'Bevinding'], $rows);

		$score = $service->computeScore($findings);
		$this->info("Score: {$score}/100  ·  {$content->finalUrl}  ·  " . count($rows) . ' aandachtspunt(en)');

		return self::SUCCESS;
	}
}
