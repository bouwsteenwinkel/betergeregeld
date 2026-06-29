<?php

namespace App\Filament\Resources\ChannelBranches\Pages;

use App\Filament\Resources\ChannelBranches\ChannelBrancheResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChannelBranches extends ListRecords
{
    protected static string $resource = ChannelBrancheResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
