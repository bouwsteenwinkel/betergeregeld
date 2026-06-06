<?php

namespace App\Filament\Resources\MonitorServers\Pages;

use App\Filament\Resources\MonitorServers\Actions\InstallAgentAction;
use App\Filament\Resources\MonitorServers\Actions\RegenerateTokenAction;
use App\Filament\Resources\MonitorServers\MonitorServerResource;
use Filament\Resources\Pages\EditRecord;

class EditMonitorServer extends EditRecord
{
	protected static string $resource = MonitorServerResource::class;

	protected function getHeaderActions(): array
	{
		return [
			InstallAgentAction::make(),
			RegenerateTokenAction::make(),
		];
	}
}
