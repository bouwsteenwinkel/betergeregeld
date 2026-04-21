<?php

namespace App\Filament\Resources\ToolEvents\Pages;

use App\Filament\Resources\ToolEvents\ToolEventResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewToolEvent extends ViewRecord
{
    protected static string $resource = ToolEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
