<?php

namespace App\Filament\Resources\ChannelSites\Pages;

use App\Filament\Resources\ChannelSites\ChannelSiteResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditChannelSite extends EditRecord
{
    protected static string $resource = ChannelSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Bekijk site')
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn () => $this->record->toChannelSite()->baseUrl(), shouldOpenInNewTab: true),

            Action::make('generate')
                ->label('Genereer basis')
                ->icon('heroicon-m-sparkles')
                ->requiresConfirmation()
                ->modalDescription('Maakt de blokken uit de branche-blueprint aan. Bestaande blokken blijven staan; alleen ontbrekende worden toegevoegd.')
                ->action(function (): void {
                    $n = $this->record->generateBlocksFromBlueprint(force: true);
                    Notification::make()
                        ->title($n > 0 ? "{$n} blok(ken) toegevoegd" : 'Niets toegevoegd')
                        ->success()->send();
                }),

            DeleteAction::make(),
        ];
    }
}
