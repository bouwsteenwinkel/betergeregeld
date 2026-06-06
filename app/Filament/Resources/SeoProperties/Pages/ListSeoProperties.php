<?php

namespace App\Filament\Resources\SeoProperties\Pages;

use App\Filament\Resources\SeoProperties\SeoPropertyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSeoProperties extends ListRecords
{
	protected static string $resource = SeoPropertyResource::class;

	protected function getHeaderActions(): array
	{
		return [
			CreateAction::make(),
		];
	}
}
