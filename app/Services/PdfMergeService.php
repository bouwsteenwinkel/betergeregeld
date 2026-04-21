<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

class PdfMergeService
{
	public function __construct(private readonly ?string $qpdfPath = null) {}

	/**
	 * Merge $inputFiles (ordered list of absolute paths) into $outputFile.
	 * Runs qpdf as a subprocess. Throws on failure.
	 */
	public function merge(array $inputFiles, string $outputFile, ?callable $onWarning = null): void
	{
		if (empty($inputFiles)) {
			throw new RuntimeException('No input files provided.');
		}

		$qpdf = $this->qpdfPath ?: (string) config('pdfmerge.qpdf_path');
		if (! is_file($qpdf) || ! is_executable($qpdf)) {
			throw new RuntimeException('qpdf binary not found or not executable: ' . $qpdf);
		}

		$outDir = dirname($outputFile);
		if (! is_dir($outDir)) {
			mkdir($outDir, 0775, true);
		}

		// qpdf --warning-exit-0 --empty --pages in1.pdf in2.pdf ... -- out.pdf
		$args = [$qpdf, '--warning-exit-0', '--empty', '--pages'];
		foreach ($inputFiles as $f) {
			if (! is_file($f)) {
				throw new RuntimeException("Input not found: {$f}");
			}
			$args[] = $f;
		}
		$args[] = '--';
		$args[] = $outputFile;

		$process = new Process($args);
		$process->setTimeout(120);
		$process->run();

		$exit = $process->getExitCode();
		if (! in_array($exit, [0, 3], true)) {
			throw new RuntimeException(sprintf(
				'qpdf failed (exit %d): %s',
				$exit,
				trim($process->getErrorOutput() ?: $process->getOutput()),
			));
		}

		$warnings = trim($process->getOutput() . "\n" . $process->getErrorOutput());
		if ($warnings !== '' && $onWarning) {
			$onWarning($warnings);
		}

		if (! is_file($outputFile) || filesize($outputFile) < 100) {
			throw new RuntimeException('Merged PDF was not written correctly.');
		}
	}

	public function watermark(string $pdfPath): void
	{
		// Free plan stamps a small "Beter Geregeld — Free" line.
		// Implementation: qpdf can't add text directly. We append a second
		// single-page PDF via --underlay, but generating the watermark PDF
		// needs Ghostscript or imagemagick. For MVP we keep the plain merge
		// and document this as a TODO — Pro users get watermark-free by
		// virtue of skipping this step entirely.
		//
		// NOTE: not yet implemented — placeholder so the gating logic reads
		// naturally. See controller for the feature gate.
	}
}
