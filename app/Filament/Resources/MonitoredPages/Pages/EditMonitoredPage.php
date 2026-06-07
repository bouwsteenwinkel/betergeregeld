<?php

namespace App\Filament\Resources\MonitoredPages\Pages;

use App\Filament\Resources\MonitoredPages\MonitoredPageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMonitoredPage extends EditRecord
{
	protected static string $resource = MonitoredPageResource::class;

	protected function getHeaderActions(): array
	{
		return [DeleteAction::make()];
	}
}
