<?php

namespace App\Filament\Resources\WebsiteLeads\Pages;

use App\Filament\Resources\WebsiteLeads\WebsiteLeadResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWebsiteLead extends EditRecord
{
    protected static string $resource = WebsiteLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
