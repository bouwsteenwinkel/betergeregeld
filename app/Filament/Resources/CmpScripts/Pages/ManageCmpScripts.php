<?php

namespace App\Filament\Resources\CmpScripts\Pages;

use App\Filament\Resources\CmpScripts\CmpScriptResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCmpScripts extends ManageRecords
{
    protected static string $resource = CmpScriptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
