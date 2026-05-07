<?php

namespace App\Filament\Resources\CmpTexts\Pages;

use App\Filament\Resources\CmpTexts\CmpTextResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCmpTexts extends ManageRecords
{
    protected static string $resource = CmpTextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
