<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\ToolEvent;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SpeedTestController extends Controller
{
	// Hard caps protect the server — multiples of 1 MB.
	private const MAX_DOWNLOAD_MB = 10;
	private const MAX_UPLOAD_MB = 5;
	private const CHUNK_BYTES = 65536; // 64 KB

	public function __construct(private readonly FeatureResolver $features) {}

	public function show(Request $request, string $locale): View
	{
		return view('tools.speedtest', [
			'features' => $this->features->forRequest($request->user()),
			'maxDownloadMb' => self::MAX_DOWNLOAD_MB,
			'maxUploadMb' => self::MAX_UPLOAD_MB,
		]);
	}

	public function ping(Request $request): JsonResponse
	{
		return response()->json([
			'ok' => true,
			'server_time' => microtime(true),
		])->withHeaders([
			'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
			'Pragma' => 'no-cache',
		]);
	}

	public function download(Request $request): StreamedResponse
	{
		$size = (int) $request->query('size', 1048576 * 5);
		$size = max(256 * 1024, min($size, self::MAX_DOWNLOAD_MB * 1024 * 1024));

		return response()->stream(function () use ($size) {
			$sent = 0;
			while ($sent < $size) {
				$chunk = min(self::CHUNK_BYTES, $size - $sent);
				echo random_bytes($chunk);
				$sent += $chunk;
				if (function_exists('ob_flush')) {
					@ob_flush();
				}
				flush();
			}
		}, 200, [
			'Content-Type' => 'application/octet-stream',
			'Content-Length' => (string) $size,
			'Content-Disposition' => 'inline; filename="speed-test.bin"',
			'X-Content-Type-Options' => 'nosniff',
			'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
		]);
	}

	public function upload(Request $request): JsonResponse
	{
		$contentLength = (int) $request->header('Content-Length', 0);
		if ($contentLength > self::MAX_UPLOAD_MB * 1024 * 1024) {
			return response()->json(['ok' => false, 'error' => 'upload_too_large'], 413);
		}

		$raw = $request->getContent();
		$actual = strlen((string) $raw);

		return response()->json([
			'ok' => true,
			'received_bytes' => $actual,
			'server_time' => microtime(true),
		])->withHeaders(['Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0']);
	}

	public function record(Request $request, string $locale): JsonResponse
	{
		$data = $request->validate([
			'ping_ms' => ['nullable', 'numeric', 'min:0', 'max:60000'],
			'download_mbps' => ['nullable', 'numeric', 'min:0', 'max:10000'],
			'upload_mbps' => ['nullable', 'numeric', 'min:0', 'max:10000'],
		]);

		ToolEvent::create([
			'tool' => 'speedtest',
			'event' => 'complete',
			'session_id' => substr($request->session()->getId(), 0, 64),
			'meta_json' => $data,
		]);

		return response()->json(['ok' => true]);
	}
}
