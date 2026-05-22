<?php

namespace App\Filament\Resources\RadarAssets\Pages;

use App\Filament\Resources\RadarAssets\RadarAssetResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRadarAsset extends EditRecord
{
	protected static string $resource = RadarAssetResource::class;

	protected function getHeaderActions(): array
	{
		return [
			ViewAction::make(),
			DeleteAction::make(),
		];
	}
}
