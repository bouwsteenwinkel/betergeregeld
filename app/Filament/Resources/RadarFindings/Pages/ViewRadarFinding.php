<?php

namespace App\Filament\Resources\RadarFindings\Pages;

use App\Filament\Resources\RadarFindings\RadarFindingResource;
use App\Models\AccessGuard\Radar\Finding;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewRadarFinding extends ViewRecord
{
	protected static string $resource = RadarFindingResource::class;

	protected function getHeaderActions(): array
	{
		return [
			Action::make('mark_patched')
				->label('Mark patched')
				->icon('heroicon-m-check-circle')
				->color('success')
				->visible(fn () => ! in_array($this->getRecord()->status, ['patched', 'resolved', 'false_positive'], true))
				->action(function (): void {
					/** @var Finding $rec */
					$rec = $this->getRecord();
					$rec->update(['status' => 'patched', 'resolved_at' => now()]);
					Notification::make()->title('Marked as patched')->success()->send();
				}),
			Action::make('mark_false_positive')
				->label('False positive')
				->icon('heroicon-m-x-circle')
				->color('gray')
				->requiresConfirmation()
				->visible(fn () => $this->getRecord()->status !== 'false_positive')
				->action(function (): void {
					/** @var Finding $rec */
					$rec = $this->getRecord();
					$rec->update(['status' => 'false_positive', 'resolved_at' => now()]);
					Notification::make()->title('Marked as false positive')->success()->send();
				}),
			Action::make('accept_risk')
				->label('Accept risk (90 days)')
				->icon('heroicon-m-hand-raised')
				->color('warning')
				->requiresConfirmation()
				->visible(fn () => $this->getRecord()->status !== 'accepted_risk')
				->action(function (): void {
					/** @var Finding $rec */
					$rec = $this->getRecord();
					$rec->update(['status' => 'accepted_risk', 'accepted_until' => now()->addDays(90)]);
					Notification::make()->title('Risk accepted for 90 days')->success()->send();
				}),
		];
	}
}
