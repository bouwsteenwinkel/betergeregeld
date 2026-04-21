<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\ToolEvent;
use App\Services\Features\FeatureBag;
use App\Services\Features\FeatureResolver;
use App\Services\PdfMergeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PdfMergeController extends Controller
{
	public function __construct(
		private readonly PdfMergeService $merger,
		private readonly FeatureResolver $features,
	) {}

	public function show(Request $request, string $locale): View
	{
		$bag = $this->features->forRequest($request->user());
		return view('tools.pdf-merge', [
			'features' => $bag,
			'caps' => $this->caps($bag),
			'sessionFiles' => $this->sessionFiles($request),
			'lastMerge' => $request->session()->get('pdfmerge_last'),
		]);
	}

	public function upload(Request $request, string $locale): JsonResponse
	{
		$request->validate([
			'file' => ['required', 'file', 'mimetypes:application/pdf', 'max:102400'], // 100 MB hard ceiling
		]);

		$bag = $this->features->forRequest($request->user());
		$caps = $this->caps($bag);

		$files = $this->sessionFiles($request);
		if (count($files) >= $caps['max_files']) {
			return response()->json([
				'ok' => false,
				'error' => 'max_files_reached',
				'limit' => $caps['max_files'],
			], 422);
		}

		$uploaded = $request->file('file');
		$sizeMb = ($uploaded->getSize() ?? 0) / 1024 / 1024;
		$currentTotalMb = array_sum(array_column($files, 'size_mb'));
		if ($currentTotalMb + $sizeMb > $caps['max_total_mb']) {
			return response()->json([
				'ok' => false,
				'error' => 'max_size_reached',
				'limit_mb' => $caps['max_total_mb'],
			], 422);
		}

		$sessionKey = $this->sessionKey($request);
		$workDir = storage_path('app/pdfmerge/' . $sessionKey);
		if (! is_dir($workDir)) {
			mkdir($workDir, 0755, true);
		}

		$fileId = (string) Str::uuid();
		$path = $workDir . '/' . $fileId . '.pdf';
		$uploaded->move(dirname($path), basename($path));

		$entry = [
			'id' => $fileId,
			'original_name' => $uploaded->getClientOriginalName(),
			'size_mb' => round($sizeMb, 2),
			'path' => $path,
		];
		$files[] = $entry;
		$request->session()->put('pdfmerge_files', $files);

		return response()->json([
			'ok' => true,
			'file' => [
				'id' => $entry['id'],
				'name' => $entry['original_name'],
				'size_mb' => $entry['size_mb'],
			],
			'total' => count($files),
		]);
	}

	public function remove(Request $request, string $locale, string $fileId): JsonResponse
	{
		$files = $this->sessionFiles($request);
		$kept = array_values(array_filter($files, function ($f) use ($fileId) {
			if ($f['id'] === $fileId) {
				@unlink($f['path']);
				return false;
			}
			return true;
		}));
		$request->session()->put('pdfmerge_files', $kept);
		return response()->json(['ok' => true, 'total' => count($kept)]);
	}

	public function merge(Request $request, string $locale): RedirectResponse
	{
		$data = $request->validate([
			'order' => ['required', 'string'],
		]);
		$order = array_filter(explode(',', $data['order']));
		$files = $this->sessionFiles($request);
		if (count($order) < 2) {
			return back()->withErrors(['merge' => __('Upload minimaal 2 PDFs om te kunnen mergen.')]);
		}

		$byId = collect($files)->keyBy('id');
		$paths = [];
		foreach ($order as $id) {
			if (! $byId->has($id)) {
				return back()->withErrors(['merge' => __('Onbekende file in volgorde.')]);
			}
			$paths[] = $byId[$id]['path'];
		}

		$sessionKey = $this->sessionKey($request);
		$workDir = storage_path('app/pdfmerge/' . $sessionKey);
		$outputPath = $workDir . '/merged-' . date('Ymd-His') . '.pdf';

		try {
			$this->merger->merge($paths, $outputPath, fn ($w) => Log::info('pdf-merge warning', ['w' => $w]));
		} catch (\Throwable $e) {
			Log::error('pdf-merge failed', ['e' => $e->getMessage()]);
			return back()->withErrors(['merge' => __('Merge mislukt: :msg', ['msg' => $e->getMessage()])]);
		}

		$downloadKey = (string) Str::uuid();
		$request->session()->put('pdfmerge_last', [
			'key' => $downloadKey,
			'path' => $outputPath,
			'name' => 'merged-' . date('Ymd-His') . '.pdf',
			'size_mb' => round(filesize($outputPath) / 1024 / 1024, 2),
			'file_count' => count($paths),
		]);

		ToolEvent::create([
			'tool' => 'pdf-merge',
			'event' => 'merge',
			'session_id' => substr($request->session()->getId(), 0, 64),
			'meta_json' => [
				'file_count' => count($paths),
				'output_mb' => round(filesize($outputPath) / 1024 / 1024, 2),
			],
		]);

		return redirect()->route('tools.pdf-merge', ['locale' => $locale]);
	}

	public function download(Request $request, string $locale, string $key): BinaryFileResponse
	{
		$last = $request->session()->get('pdfmerge_last');
		abort_unless($last && $last['key'] === $key && is_file($last['path']), 404);
		return response()->download($last['path'], $last['name']);
	}

	public function reset(Request $request, string $locale): RedirectResponse
	{
		$files = $this->sessionFiles($request);
		foreach ($files as $f) {
			@unlink($f['path']);
		}
		$last = $request->session()->get('pdfmerge_last');
		if ($last && is_file($last['path'])) {
			@unlink($last['path']);
		}
		$request->session()->forget(['pdfmerge_files', 'pdfmerge_last']);
		return redirect()->route('tools.pdf-merge', ['locale' => $locale]);
	}

	// ------- helpers -------

	private function sessionKey(Request $request): string
	{
		$key = $request->session()->get('pdfmerge_session_key');
		if (! $key) {
			$key = (string) Str::uuid();
			$request->session()->put('pdfmerge_session_key', $key);
		}
		return $key;
	}

	private function sessionFiles(Request $request): array
	{
		return $request->session()->get('pdfmerge_files', []);
	}

	private function caps(FeatureBag $bag): array
	{
		$maxFiles = $bag->limit('tool.pdf_merge.max_files');
		$maxSize = $bag->limit('tool.pdf_merge.max_size_mb');
		return [
			'max_files' => $maxFiles ?? (int) config('pdfmerge.hard_max_files'),
			'max_total_mb' => $maxSize ?? (int) config('pdfmerge.hard_max_total_mb'),
			'unlimited_files' => $bag->isUnlimited('tool.pdf_merge.max_files'),
			'unlimited_size' => $bag->isUnlimited('tool.pdf_merge.max_size_mb'),
			'watermark' => $bag->bool('tool.pdf_merge.watermark'),
		];
	}
}
