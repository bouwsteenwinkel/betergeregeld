<?php

namespace App\Filament\Resources\MonitoredPages\Pages;

use App\Filament\Resources\MonitoredPages\MonitoredPageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMonitoredPage extends CreateRecord
{
	protected static string $resource = MonitoredPageResource::class;
}
