<?php

namespace App\Filament\Resources\CmpDomains\Pages;

use App\Filament\Resources\CmpDomains\CmpDomainResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCmpDomains extends ManageRecords
{
    protected static string $resource = CmpDomainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
