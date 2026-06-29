<?php

namespace App\Filament\Resources\ChannelSites\Pages;

use App\Filament\Resources\ChannelSites\ChannelSiteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChannelSites extends ListRecords
{
    protected static string $resource = ChannelSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nieuwe site')];
    }
}
