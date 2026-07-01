<?php

namespace App\Filament\Resources\ChannelBranches\Pages;

use App\Filament\Resources\ChannelBranches\ChannelBrancheResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChannelBranche extends EditRecord
{
    protected static string $resource = ChannelBrancheResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
