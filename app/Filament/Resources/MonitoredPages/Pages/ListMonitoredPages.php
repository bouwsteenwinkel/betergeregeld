<?php

namespace App\Filament\Resources\MonitoredPages\Pages;

use App\Filament\Resources\MonitoredPages\MonitoredPageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMonitoredPages extends ListRecords
{
	protected static string $resource = MonitoredPageResource::class;

	protected function getHeaderActions(): array
	{
		return [CreateAction::make()];
	}
}
