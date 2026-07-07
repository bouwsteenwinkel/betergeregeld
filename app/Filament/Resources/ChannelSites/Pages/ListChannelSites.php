<?php

namespace App\Filament\Resources\ChannelSites\Pages;

use App\Filament\Resources\ChannelSites\ChannelSiteResource;
use App\Models\Channel\Site;
use App\Services\OpenProvider\OpenProviderClient;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListChannelSites extends ListRecords
{
    protected static string $resource = ChannelSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Haalt per site met een domein de OpenProvider-zonestatus op en
            // schrijft die naar meta (op.registered / op.dns_ok), zodat de
            // Domein- en DNS-kolcommen zonder API-calls kunnen renderen.
            Action::make('syncDomains')
                ->label('Domeinstatus verversen')
                ->icon('heroicon-m-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('Controleert bij OpenProvider welke domeinen geregistreerd zijn en of de DNS (apex + www) naar de VPS wijst. Doet één API-call per domein.')
                ->action(function (): void {
                    $op = app(OpenProviderClient::class);
                    if (! $op->isConfigured()) {
                        Notification::make()
                            ->title('OpenProvider niet geconfigureerd')
                            ->body('Zet OPENPROVIDER_USERNAME/PASSWORD/OWNER_HANDLE + CHANNEL_TARGET_IP in .env.')
                            ->danger()->send();
                        return;
                    }

                    $checked = 0;
                    $registered = 0;
                    $dnsOk = 0;

                    Site::query()
                        ->whereNotNull('domain')->where('domain', '!=', '')
                        ->get()
                        ->each(function (Site $s) use ($op, &$checked, &$registered, &$dnsOk): void {
                            try {
                                $st = $op->zoneStatus((string) $s->domain);
                            } catch (\Throwable $e) {
                                return;
                            }
                            $checked++;
                            $meta = (array) $s->meta;
                            $meta['op'] = [
                                'registered' => $st['registered'],
                                'dns_ok'     => $st['dns_ok'],
                                'checked_at' => now()->toIso8601String(),
                            ];
                            $s->meta = $meta;
                            $s->save();
                            if ($st['registered']) {
                                $registered++;
                            }
                            if ($st['dns_ok']) {
                                $dnsOk++;
                            }
                        });

                    Notification::make()
                        ->title('Domeinstatus bijgewerkt')
                        ->body("{$checked} domein(en) gecontroleerd — {$registered} geregistreerd, {$dnsOk} met correcte DNS.")
                        ->success()->send();
                }),

            CreateAction::make()->label('Nieuwe site'),
        ];
    }
}
