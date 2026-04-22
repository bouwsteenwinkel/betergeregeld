<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Flatten-based PDF redaction:
 *
 *  1. Upload PDF → Ghostscript rasterises every page to PNG at config DPI
 *  2. User draws rectangles on the client; controller stores their coords
 *  3. On apply, each preview PNG is copied and the rects are drawn as solid
 *     black boxes via GD → saved back as PNG
 *  4. The redacted PNG set is reassembled into a fresh PDF via FPDF
 *
 * Because each page becomes a raster image, the underlying text and vector
 * content is gone — no pdftotext or copy-paste can retrieve what was hidden.
 */
class PdfRedactService
{
	public function createJob(string $storageRoot): string
	{
		$job = bin2hex(random_bytes(12));
		$dir = $this->jobDir($storageRoot, $job);
		foreach (['', '/src', '/pages_src', '/pages_redacted', '/out'] as $sub) {
			$full = $dir . $sub;
			if (! is_dir($full)) {
				mkdir($full, 0755, true);
			}
		}
		return $job;
	}

	public function jobDir(string $storageRoot, string $job): string
	{
		return $storageRoot . '/' . preg_replace('/[^a-f0-9]/i', '', $job);
	}

	/**
	 * Render each page of $pdfPath to a PNG in $jobDir/pages_src.
	 * Returns [pageCount, [['num'=>int, 'w'=>int, 'h'=>int], ...]].
	 */
	public function renderPreviews(string $jobDir, string $pdfPath, int $dpi): array
	{
		$gs = $this->resolveGsPath();
		$srcDir = $jobDir . '/pages_src';
		foreach (glob($srcDir . '/page-*.png') ?: [] as $f) {
			@unlink($f);
		}

		$process = new Process([
			$gs,
			'-dSAFER', '-dNOPAUSE', '-dBATCH', '-dQUIET',
			'-sDEVICE=pngalpha',
			'-r' . $dpi,
			'-o', $srcDir . '/page-%03d.png',
			$pdfPath,
		]);
		$process->setTimeout(120);
		$process->run();

		if (! $process->isSuccessful()) {
			throw new RuntimeException('Ghostscript failed: ' . trim($process->getErrorOutput() ?: $process->getOutput()));
		}

		$pngs = glob($srcDir . '/page-*.png') ?: [];
		sort($pngs, SORT_STRING);
		if (empty($pngs)) {
			throw new RuntimeException('No previews produced.');
		}

		$pages = [];
		foreach ($pngs as $png) {
			if (! preg_match('/page-(\d{3})\.png$/', $png, $m)) {
				continue;
			}
			$info = getimagesize($png);
			$pages[] = [
				'num' => (int) $m[1],
				'w' => $info ? (int) $info[0] : 0,
				'h' => $info ? (int) $info[1] : 0,
			];
		}

		return ['count' => count($pages), 'pages' => $pages];
	}

	/**
	 * Apply redaction rects and assemble a final PDF.
	 * $rects: [ ['page' => 1, 'x' => 100, 'y' => 200, 'w' => 50, 'h' => 30], ... ]
	 * Coords are in PNG-pixel space of the corresponding preview.
	 */
	public function applyAndBuild(string $jobDir, array $rects, string $outputPath): string
	{
		$srcDir = $jobDir . '/pages_src';
		$workDir = $jobDir . '/pages_redacted';
		foreach (glob($workDir . '/page-*.png') ?: [] as $f) {
			@unlink($f);
		}

		$srcPngs = glob($srcDir . '/page-*.png') ?: [];
		sort($srcPngs, SORT_STRING);
		if (empty($srcPngs)) {
			throw new RuntimeException('No source previews; upload first.');
		}

		// Copy previews → working set
		foreach ($srcPngs as $src) {
			$dst = $workDir . '/' . basename($src);
			if (! @copy($src, $dst)) {
				throw new RuntimeException('Failed copying preview to working set.');
			}
		}

		// Burn rects onto the working copies
		$byPage = [];
		foreach ($rects as $r) {
			$byPage[(int) ($r['page'] ?? 0)][] = $r;
		}
		foreach (glob($workDir . '/page-*.png') ?: [] as $png) {
			if (! preg_match('/page-(\d{3})\.png$/', $png, $m)) {
				continue;
			}
			$pageNum = (int) $m[1];
			if (empty($byPage[$pageNum])) {
				continue;
			}
			$this->drawRectsOnPng($png, $byPage[$pageNum]);
		}

		// Convert to JPG then into a single PDF via FPDF
		$pngs = glob($workDir . '/page-*.png') ?: [];
		sort($pngs, SORT_STRING);

		$pdf = new \FPDF('P', 'pt', 'A4');
		$pdf->SetMargins(0, 0, 0);
		$pdf->SetAutoPageBreak(false);

		foreach ($pngs as $png) {
			$info = getimagesize($png);
			if (! $info) {
				continue;
			}
			$pxW = (int) $info[0];
			$pxH = (int) $info[1];
			$ratio = $pxW / max(1, $pxH);

			// Keep original page aspect by building PDF page at PNG aspect * A4 width
			$pageW = 595; // A4 pt width
			$pageH = (int) round($pageW / $ratio);

			$pdf->AddPage('P', [$pageW, $pageH]);
			$pdf->Image($png, 0, 0, $pageW, $pageH, 'PNG');
		}

		$pdf->Output('F', $outputPath);

		if (! is_file($outputPath) || filesize($outputPath) < 500) {
			throw new RuntimeException('Output PDF is empty or not written.');
		}
		return $outputPath;
	}

	private function drawRectsOnPng(string $pngPath, array $rects): void
	{
		$im = @imagecreatefrompng($pngPath);
		if (! $im) {
			throw new RuntimeException("Cannot open PNG: {$pngPath}");
		}
		imagesavealpha($im, true);
		$black = imagecolorallocate($im, 0, 0, 0);
		$imgW = imagesx($im);
		$imgH = imagesy($im);

		foreach ($rects as $r) {
			$x = (int) round((float) ($r['x'] ?? 0));
			$y = (int) round((float) ($r['y'] ?? 0));
			$w = (int) round((float) ($r['w'] ?? 0));
			$h = (int) round((float) ($r['h'] ?? 0));
			if ($w <= 0 || $h <= 0) {
				continue;
			}
			if ($x < 0) { $w += $x; $x = 0; }
			if ($y < 0) { $h += $y; $y = 0; }
			if ($x + $w > $imgW) $w = $imgW - $x;
			if ($y + $h > $imgH) $h = $imgH - $y;
			if ($w <= 0 || $h <= 0) continue;

			imagefilledrectangle($im, $x, $y, $x + $w, $y + $h, $black);
		}

		imagepng($im, $pngPath, 3);
		imagedestroy($im);
	}

	public function resolveGsPath(): string
	{
		$configured = (string) config('pdfredact.gs_path', '');
		if ($configured !== '' && is_file($configured) && is_executable($configured)) {
			return $configured;
		}

		// Windows PATH lookup
		$candidates = ['gswin64c', 'gswin64c.exe', 'gs'];
		foreach ($candidates as $cand) {
			$probe = @shell_exec("where $cand 2>NUL");
			if (is_string($probe)) {
				$first = trim(strtok($probe, "\r\n"));
				if ($first !== '' && is_file($first)) {
					return $first;
				}
			}
		}

		// Well-known install locations
		$paths = [
			'C:\\Program Files\\gs\\gs10.07.0\\bin\\gswin64c.exe',
			'C:\\Program Files\\gs\\gs10.06.0\\bin\\gswin64c.exe',
			'C:\\Program Files\\gs\\gs10.0.0\\bin\\gswin64c.exe',
		];
		foreach ($paths as $p) {
			if (is_file($p)) {
				return $p;
			}
		}

		throw new RuntimeException('Ghostscript not found. Install it and set GS_PATH in .env if needed.');
	}
}
