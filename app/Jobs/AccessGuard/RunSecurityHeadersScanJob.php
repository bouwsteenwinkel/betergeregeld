<?php

namespace App\Jobs\AccessGuard;

use App\Mail\AccessGuardRadarScanComplete;
use App\Models\AccessGuard\Radar\Asset;
use App\Models\AccessGuard\Radar\Scan;
use App\Models\User;
use App\Services\Features\FeatureResolver;
use App\Services\Radar\SecurityHeadersScanner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

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

	public function handle(SecurityHeadersScanner $scanner, FeatureResolver $features): void
	{
		$asset = Asset::query()->find($this->assetId);
		if (! $asset || $asset->status !== 'active' || empty($asset->url)) {
			return;
		}

		$result = $scanner->scan($asset);

		$diff = $result['diff'] ?? ['new' => [], 'resolved' => [], 'unchanged' => []];
		if (empty($diff['new']) && empty($diff['resolved'])) {
			return;
		}

		Log::info('radar.security_headers.diff', [
			'asset_id'  => $asset->id,
			'tenant_id' => $asset->tenant_id,
			'url'       => $asset->url,
			'scan_id'   => $result['scan_id'],
			'new'       => $diff['new'],
			'resolved'  => $diff['resolved'],
		]);

		$this->maybeDispatchMail($asset, (int) $result['scan_id'], $features);
	}

	private function maybeDispatchMail(Asset $asset, int $scanId, FeatureResolver $features): void
	{
		$recipient = $this->resolveRecipientUser($asset);
		if (! $recipient) {
			return;
		}

		// Free-plan = geen scheduled alerts (geen toegang tot admin om scans
		// te zien). Pro/Business zetten tool.radar.enabled op '1'.
		if (! $features->forUser($recipient)->bool('tool.radar.enabled')) {
			return;
		}

		$scan = Scan::query()->find($scanId);
		if (! $scan) {
			return;
		}

		try {
			Mail::to($recipient->email)->send(new AccessGuardRadarScanComplete($asset, $scan));
		} catch (Throwable $e) {
			Log::warning('radar.security_headers.mail_failed', [
				'asset_id' => $asset->id,
				'scan_id'  => $scanId,
				'to'       => $recipient->email,
				'error'    => $e->getMessage(),
			]);
		}
	}

	private function resolveRecipientUser(Asset $asset): ?User
	{
		if ($asset->owner_user_id) {
			$owner = User::query()->find($asset->owner_user_id);
			if ($owner?->email) {
				return $owner;
			}
		}
		return User::query()
			->where('tenant_id', $asset->tenant_id)
			->where('role', 'admin')
			->where('is_active', true)
			->first();
	}
}
