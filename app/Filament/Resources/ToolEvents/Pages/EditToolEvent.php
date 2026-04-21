<?php

namespace App\Filament\Resources\ToolEvents\Pages;

use App\Filament\Resources\ToolEvents\ToolEventResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditToolEvent extends EditRecord
{
    protected static string $resource = ToolEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
