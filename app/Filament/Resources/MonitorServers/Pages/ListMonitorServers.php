<?php

namespace App\Filament\Resources\MonitorServers\Pages;

use App\Filament\Resources\MonitorServers\MonitorServerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMonitorServers extends ListRecords
{
	protected static string $resource = MonitorServerResource::class;

	protected function getHeaderActions(): array
	{
		return [
			CreateAction::make(),
		];
	}
}
