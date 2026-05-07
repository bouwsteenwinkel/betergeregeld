<?php

namespace App\Filament\Resources\CmpBrandings\Pages;

use App\Filament\Resources\CmpBrandings\CmpBrandingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCmpBrandings extends ManageRecords
{
    protected static string $resource = CmpBrandingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
