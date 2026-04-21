<?php

namespace App\Filament\Resources\ToolEvents\Pages;

use App\Filament\Resources\ToolEvents\ToolEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListToolEvents extends ListRecords
{
    protected static string $resource = ToolEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
