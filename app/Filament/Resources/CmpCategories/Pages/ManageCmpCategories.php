<?php

namespace App\Filament\Resources\CmpCategories\Pages;

use App\Filament\Resources\CmpCategories\CmpCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCmpCategories extends ManageRecords
{
    protected static string $resource = CmpCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
