<?php

namespace App\Filament\Resources\CmpConsents\Pages;

use App\Filament\Resources\CmpConsents\CmpConsentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCmpConsents extends ManageRecords
{
    protected static string $resource = CmpConsentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
