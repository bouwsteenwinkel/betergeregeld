<?php

namespace App\Filament\Resources\ChannelSites\Pages;

use App\Filament\Resources\ChannelSites\ChannelSiteResource;
use App\Services\OpenProvider\OpenProviderClient;
use App\Services\Seo\GscProvisioner;
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

            // Domein in 1 keer registreren bij OpenProvider + DNS (A-records) naar de VPS.
            // Alleen zichtbaar als er een domein is dat nog niet via ons geregistreerd is.
            Action::make('registerDomain')
                ->label('Registreer domein')
                ->icon('heroicon-m-globe-alt')
                ->color('success')
                ->visible(fn () => filled($this->record->domain) && blank(data_get($this->record->meta, 'domain_registered_at')))
                ->requiresConfirmation()
                ->modalHeading('Domein registreren bij OpenProvider')
                ->modalDescription(fn () => "Registreert {$this->record->domain} bij OpenProvider en zet de DNS (A-records @ en www) naar de VPS. Dit is definitief en brengt registratiekosten met zich mee.")
                ->modalSubmitActionLabel('Ja, registreren')
                ->action(function (): void {
                    $op = app(OpenProviderClient::class);
                    if (! $op->isConfigured()) {
                        Notification::make()
                            ->title('OpenProvider niet geconfigureerd')
                            ->body('Zet OPENPROVIDER_USERNAME/PASSWORD/OWNER_HANDLE + CHANNEL_TARGET_IP in .env.')
                            ->danger()->send();
                        return;
                    }
                    try {
                        $result = $op->registerWithDns((string) $this->record->domain);
                        $meta = (array) $this->record->meta;
                        $meta['domain_registered_at'] = $result['registered_at'];
                        $meta['openprovider'] = ['domain_id' => $result['domain_id'], 'ip' => $result['ip']];
                        $this->record->meta = $meta;
                        $this->record->save();
                        Notification::make()
                            ->title('Domein geregistreerd')
                            ->body("{$result['domain']} geregistreerd en DNS naar {$result['ip']} gezet. Zet de site op 'live' zodra de DNS is doorgekomen.")
                            ->success()->send();
                        $this->refreshFormData(['meta']);
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Registratie mislukt')
                            ->body($e->getMessage())
                            ->danger()->persistent()->send();
                    }
                }),

            // Google Search Console inregelen: verifieer (DNS-TXT via OpenProvider),
            // property toevoegen + sitemap indienen. Alleen bij een gekoppeld domein.
            Action::make('provisionGsc')
                ->label('Search inregelen')
                ->icon('heroicon-m-magnifying-glass')
                ->color('info')
                ->visible(fn () => filled($this->record->domain))
                ->requiresConfirmation()
                ->modalHeading('Google Search Console inregelen')
                ->modalDescription(fn () => "Verifieert {$this->record->domain} via een DNS-TXT-record (OpenProvider), voegt de property toe in Search Console en dient de sitemap in. Alleen zinvol voor een live, bereikbaar domein waarvan de DNS in OpenProvider staat.")
                ->modalSubmitActionLabel('Ja, inregelen')
                ->action(function (): void {
                    try {
                        $res = app(GscProvisioner::class)->provision($this->record);
                        $body = collect($res['steps'])->map(fn ($v, $k) => "• {$k}: {$v}")->implode("\n");
                        $n = Notification::make()
                            ->title($res['ok'] ? 'Search Console ingeregeld' : 'Nog niet compleet')
                            ->body($body);
                        ($res['ok'] ? $n->success() : $n->warning())->persistent()->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Inregelen mislukt')->body($e->getMessage())
                            ->danger()->persistent()->send();
                    }
                }),

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
