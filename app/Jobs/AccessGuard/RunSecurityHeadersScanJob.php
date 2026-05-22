<?php

namespace App\Jobs\AccessGuard;

use App\Models\AccessGuard\Radar\Asset;
use App\Services\Radar\SecurityHeadersScanner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class RunSecurityHeadersScanJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public int $tries = 1;
	public int $timeout = 60;

	public function __construct(
		public readonly int $assetId,
	) {}

	public function handle(SecurityHeadersScanner $scanner): void
	{
		$asset = Asset::query()->find($this->assetId);
		if (! $asset || $asset->status !== 'active' || empty($asset->url)) {
			return;
		}

		$result = $scanner->scan($asset);

		$diff = $result['diff'] ?? ['new' => [], 'resolved' => [], 'unchanged' => []];
		if (! empty($diff['new']) || ! empty($diff['resolved'])) {
			Log::channel(config('logging.default'))->info('radar.security_headers.diff', [
				'asset_id'  => $asset->id,
				'tenant_id' => $asset->tenant_id,
				'url'       => $asset->url,
				'scan_id'   => $result['scan_id'],
				'new'       => $diff['new'],
				'resolved'  => $diff['resolved'],
			]);
		}
	}
}
