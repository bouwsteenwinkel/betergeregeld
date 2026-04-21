<?php

namespace App\Filament\Resources\ToolEvents\Pages;

use App\Filament\Resources\ToolEvents\ToolEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateToolEvent extends CreateRecord
{
    protected static string $resource = ToolEventResource::class;
}
