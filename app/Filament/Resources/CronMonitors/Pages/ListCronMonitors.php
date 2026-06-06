<?php

namespace App\Filament\Resources\CronMonitors\Pages;

use App\Filament\Resources\CronMonitors\CronMonitorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCronMonitors extends ListRecords
{
	protected static string $resource = CronMonitorResource::class;

	protected function getHeaderActions(): array
	{
		return [
			CreateAction::make(),
		];
	}
}
