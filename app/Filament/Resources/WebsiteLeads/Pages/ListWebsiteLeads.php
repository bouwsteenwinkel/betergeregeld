<?php

namespace App\Filament\Resources\WebsiteLeads\Pages;

use App\Filament\Resources\WebsiteLeads\WebsiteLeadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWebsiteLeads extends ListRecords
{
    protected static string $resource = WebsiteLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
