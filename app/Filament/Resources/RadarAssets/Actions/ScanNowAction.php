<?php

namespace App\Filament\Resources\RadarAssets\Actions;

use App\Jobs\AccessGuard\RunRadarScanJob;
use App\Models\AccessGuard\Radar\Asset;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

/**
 * Single-action factory used by both the asset table (per row) and the
 * asset view page (header). dispatchSync because V2 runs QUEUE_CONNECTION=sync;
 * scans typically finish in under 10 s. Switching to a real queue later
 * is a one-character change to ::dispatch().
 */
class ScanNowAction
{
	public static function make(?string $name = 'scan_now'): Action
	{
		return Action::make($name)
			->label('Scan')
			->icon('heroicon-m-bolt')
			->color('primary')
			->requiresConfirmation()
			->modalHeading('Scan asset now?')
			->modalDescription(fn (Asset $r) =>
				"Probes {$r->url} with HTTP fingerprinters (passive, no exploit attempts). "
				. 'Detected software is upserted into the asset; existing detections refresh their last_seen_at.'
			)
			->modalSubmitActionLabel('Run scan')
			->visible(fn (Asset $r) => $r->status === 'active' && filled($r->url))
			->action(function (Asset $record): void {
				try {
					RunRadarScanJob::dispatchSync($record->id);
					$record->refresh();
					$count = $record->softwareInstances()->count();
					Notification::make()
						->title('Scan finished')
						->body("Detected {$count} software instance(s). See the Software tab below.")
						->success()
						->send();
				} catch (Throwable $e) {
					Notification::make()
						->title('Scan failed')
						->body($e->getMessage())
						->danger()
						->send();
				}
			});
	}
}
