<?php

namespace App\Filament\Resources\TenantSubscriptions\Pages;

use App\Filament\Resources\TenantSubscriptions\TenantSubscriptionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTenantSubscription extends ViewRecord
{
    protected static string $resource = TenantSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
