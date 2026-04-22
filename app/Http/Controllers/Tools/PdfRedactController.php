<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\ToolEvent;
use App\Services\Features\FeatureResolver;
use App\Services\PdfRedactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PdfRedactController extends Controller
{
	public function __construct(
		private readonly PdfRedactService $service,
		private readonly FeatureResolver $features,
	) {}

	public function show(Request $request, string $locale): View
	{
		$bag = $this->features->forRequest($request->user());
		abort_unless($bag->bool('tool.pdf_redact.enabled'), 402, 'PDF redact is not included in your plan.');

		return view('tools.pdf-redact', [
			'features' => $bag,
			'job' => $request->session()->get('pdfredact_job'),
			'lastOutput' => $request->session()->get('pdfredact_last'),
		]);
	}

	public function upload(Request $request, string $locale): JsonResponse
	{
		$request->validate([
			'file' => ['required', 'file', 'mimetypes:application/pdf', 'max:' . (config('pdfredact.hard_max_mb') * 1024)],
		]);

		$bag = $this->features->forRequest($request->user());
		abort_unless($bag->bool('tool.pdf_redact.enabled'), 402);

		$storageRoot = storage_path('app/pdfredact');
		if (! is_dir($storageRoot)) {
			mkdir($storageRoot, 0755, true);
		}

		$jobId = $this->service->createJob($storageRoot);
		$jobDir = $this->service->jobDir($storageRoot, $jobId);
		$pdfPath = $jobDir . '/src/input.pdf';
		$request->file('file')->move(dirname($pdfPath), basename($pdfPath));

		try {
			$result = $this->service->renderPreviews($jobDir, $pdfPath, (int) config('pdfredact.render_dpi'));
		} catch (\Throwable $e) {
			Log::error('pdf-redact render failed', ['e' => $e->getMessage()]);
			return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
		}

		if ($result['count'] > (int) config('pdfredact.hard_max_pages')) {
			return response()->json([
				'ok' => false,
				'error' => 'pdf_too_long',
				'pages' => $result['count'],
				'limit' => (int) config('pdfredact.hard_max_pages'),
			], 422);
		}

		$request->session()->put('pdfredact_job', [
			'id' => $jobId,
			'dir' => $jobDir,
			'pages' => $result['pages'],
			'original_name' => $request->file('file')->getClientOriginalName(),
		]);
		$request->session()->forget('pdfredact_last');

		return response()->json([
			'ok' => true,
			'job' => $jobId,
			'pages' => $result['pages'],
			'original_name' => $request->file('file')->getClientOriginalName(),
		]);
	}

	/** Serve a page preview PNG for the current job. */
	public function preview(Request $request, string $locale, int $page): BinaryFileResponse
	{
		$job = $request->session()->get('pdfredact_job');
		abort_unless($job, 404);

		$path = $job['dir'] . '/pages_src/page-' . str_pad((string) $page, 3, '0', STR_PAD_LEFT) . '.png';
		abort_unless(is_file($path), 404);

		return response()->file($path, [
			'Content-Type' => 'image/png',
			'Cache-Control' => 'private, max-age=600',
		]);
	}

	public function apply(Request $request, string $locale): RedirectResponse
	{
		$data = $request->validate([
			'rects' => ['required', 'string'], // JSON-encoded array
		]);

		$job = $request->session()->get('pdfredact_job');
		if (! $job) {
			return back()->withErrors(['redact' => __('Upload eerst een PDF.')]);
		}

		$rects = json_decode($data['rects'], true);
		if (! is_array($rects)) {
			return back()->withErrors(['redact' => __('Ongeldige rechthoek-data.')]);
		}

		try {
			$outputPath = $job['dir'] . '/out/redacted-' . date('Ymd-His') . '.pdf';
			$this->service->applyAndBuild($job['dir'], $rects, $outputPath);
		} catch (\Throwable $e) {
			Log::error('pdf-redact apply failed', ['e' => $e->getMessage()]);
			return back()->withErrors(['redact' => __('Verwerking mislukt: :msg', ['msg' => $e->getMessage()])]);
		}

		$key = (string) Str::uuid();
		$request->session()->put('pdfredact_last', [
			'key' => $key,
			'path' => $outputPath,
			'name' => 'redacted-' . pathinfo($job['original_name'], PATHINFO_FILENAME) . '.pdf',
			'size_mb' => round(filesize($outputPath) / 1024 / 1024, 2),
			'rect_count' => count($rects),
			'page_count' => count($job['pages']),
		]);

		ToolEvent::create([
			'tool' => 'pdf-redact',
			'event' => 'apply',
			'session_id' => substr($request->session()->getId(), 0, 64),
			'meta_json' => [
				'rect_count' => count($rects),
				'page_count' => count($job['pages']),
				'output_mb' => round(filesize($outputPath) / 1024 / 1024, 2),
			],
		]);

		return redirect()->route('tools.pdf-redact', ['locale' => $locale]);
	}

	public function download(Request $request, string $locale, string $key): BinaryFileResponse
	{
		$last = $request->session()->get('pdfredact_last');
		abort_unless($last && $last['key'] === $key && is_file($last['path']), 404);
		return response()->download($last['path'], $last['name']);
	}

	public function reset(Request $request, string $locale): RedirectResponse
	{
		$job = $request->session()->get('pdfredact_job');
		if ($job && is_dir($job['dir'])) {
			$this->rrmdir($job['dir']);
		}
		$request->session()->forget(['pdfredact_job', 'pdfredact_last']);
		return redirect()->route('tools.pdf-redact', ['locale' => $locale]);
	}

	private function rrmdir(string $dir): void
	{
		if (! is_dir($dir)) return;
		foreach (scandir($dir) as $e) {
			if ($e === '.' || $e === '..') continue;
			$p = $dir . '/' . $e;
			is_dir($p) ? $this->rrmdir($p) : @unlink($p);
		}
		@rmdir($dir);
	}
}
