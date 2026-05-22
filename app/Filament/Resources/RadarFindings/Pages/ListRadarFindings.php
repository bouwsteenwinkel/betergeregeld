<?php

namespace App\Filament\Resources\RadarFindings\Pages;

use App\Filament\Resources\RadarFindings\RadarFindingResource;
use Filament\Resources\Pages\ListRecords;

class ListRadarFindings extends ListRecords
{
	protected static string $resource = RadarFindingResource::class;

	protected function getHeaderActions(): array
	{
		return [];
	}
}
