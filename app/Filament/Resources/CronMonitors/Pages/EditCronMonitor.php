<?php

namespace App\Filament\Resources\CronMonitors\Pages;

use App\Filament\Resources\CronMonitors\CronMonitorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCronMonitor extends EditRecord
{
	protected static string $resource = CronMonitorResource::class;

	protected function getHeaderActions(): array
	{
		return [
			DeleteAction::make(),
		];
	}
}
