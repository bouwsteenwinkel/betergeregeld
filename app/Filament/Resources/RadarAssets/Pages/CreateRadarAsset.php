<?php

namespace App\Filament\Resources\RadarAssets\Pages;

use App\Filament\Resources\RadarAssets\RadarAssetResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRadarAsset extends CreateRecord
{
	protected static string $resource = RadarAssetResource::class;

	protected function mutateFormDataBeforeCreate(array $data): array
	{
		// First-time discovery is the moment the user creates the asset
		// — record it so the "since X" timeline on the view page is meaningful.
		$data['discovered_at'] ??= now();
		return $data;
	}
}
