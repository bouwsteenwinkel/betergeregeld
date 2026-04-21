<?php

namespace App\Filament\Resources\PlanFeatures\Pages;

use App\Filament\Resources\PlanFeatures\PlanFeatureResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPlanFeature extends ViewRecord
{
    protected static string $resource = PlanFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
