<?php

namespace App\Filament\Resources\RadarAssets\Pages;

use App\Filament\Resources\RadarAssets\Actions\ScanHeadersAction;
use App\Filament\Resources\RadarAssets\Actions\ScanNowAction;
use App\Filament\Resources\RadarAssets\RadarAssetResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRadarAsset extends ViewRecord
{
	protected static string $resource = RadarAssetResource::class;

	protected function getHeaderActions(): array
	{
		return [
			ScanNowAction::make('scan_now_header'),
			ScanHeadersAction::make('scan_headers_header'),
			EditAction::make(),
		];
	}
}
