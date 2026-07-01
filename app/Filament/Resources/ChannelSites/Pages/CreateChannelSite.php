<?php

namespace App\Filament\Resources\ChannelSites\Pages;

use App\Filament\Resources\ChannelSites\ChannelSiteResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateChannelSite extends CreateRecord
{
    protected static string $resource = ChannelSiteResource::class;

    /** Direct de basis-blokken uit de branche-blueprint genereren. */
    protected function afterCreate(): void
    {
        $n = $this->record->generateBlocksFromBlueprint();
        if ($n > 0) {
            Notification::make()
                ->title("Basis aangemaakt: {$n} blokken")
                ->body('Pas de inhoud en volgorde aan onder “Blokken”.')
                ->success()->send();
        }
    }
}
