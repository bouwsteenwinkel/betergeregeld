<?php

namespace App\Filament\Resources\AvailabilityExceptions\Pages;

use App\Filament\Resources\AvailabilityExceptions\AvailabilityExceptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAvailabilityException extends EditRecord
{
    protected static string $resource = AvailabilityExceptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
