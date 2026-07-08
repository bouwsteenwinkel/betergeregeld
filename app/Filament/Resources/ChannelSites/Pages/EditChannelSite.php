<?php

namespace App\Filament\Resources\ChannelSites\Pages;

use App\Filament\Resources\ChannelSites\ChannelSiteResource;
use App\Services\ChannelSites\GoLiveOrchestrator;
use App\Services\OpenProvider\OpenProviderClient;
use App\Services\Plesk\PleskClient;
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
            // Hele keten in één klik: OpenProvider (registratie + DNS) → Plesk
            // (alias + Let's Encrypt) → status live → Google Search Console.
            Action::make('goLive')
                ->label(fn () => $this->record->status === 'live' ? '✓ Live (opnieuw)' : 'Volledig live')
                ->icon('heroicon-m-rocket-launch')
                ->color(fn () => $this->record->status === 'live' ? 'gray' : 'success')
                ->visible(fn () => filled($this->record->domain))
                ->requiresConfirmation()
                ->modalHeading('Site volledig live zetten')
                ->modalDescription(fn () => "Doorloopt in één keer: 1) domein {$this->record->domain} registreren + DNS bij OpenProvider, 2) Plesk-alias + Let's Encrypt, 3) status op live, 4) Google Search Console inregelen. Stap 1 registreert het domein en brengt kosten met zich mee. Draai dit op de VPS.")
                ->modalSubmitActionLabel('Ja, volledig live')
                ->action(function (): void {
                    $res = app(GoLiveOrchestrator::class)->run($this->record);
                    $body = collect($res['steps'])->map(fn ($v, $k) => "• {$k}: {$v}")->implode("\n");
                    Notification::make()
                        ->title($res['ok'] ? 'Site is volledig live' : 'Keten gestopt (zie stappen)')
                        ->body($body)
                        ->{$res['ok'] ? 'success' : 'warning'}()
                        ->persistent()->send();
                    $this->refreshFormData(['status', 'meta']);
                }),

            Action::make('preview')
                ->label('Bekijk site')
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn () => $this->record->toChannelSite()->baseUrl(), shouldOpenInNewTab: true),

            // Domein in 1 keer registreren bij OpenProvider + DNS (A-records) naar de VPS.
            // Alleen zichtbaar als er een domein is dat nog niet via ons geregistreerd is.
            Action::make('registerDomain')
                ->label(fn () => filled(data_get($this->record->meta, 'domain_registered_at')) ? 'Domein ✓ (opnieuw)' : 'Registreer domein')
                ->icon('heroicon-m-globe-alt')
                ->color(fn () => filled(data_get($this->record->meta, 'domain_registered_at')) ? 'gray' : 'success')
                ->visible(fn () => filled($this->record->domain))
                ->requiresConfirmation()
                ->modalHeading('Domein registreren bij OpenProvider')
                ->modalDescription(fn () => "Registreert {$this->record->domain} bij OpenProvider en zet de DNS (A-records @ en www) naar de VPS. Idempotent: is het domein al van ons, dan wordt niets dubbel geregistreerd (alleen de DNS gecontroleerd). Registratie brengt eenmalig kosten met zich mee.")
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
                        $already = (bool) ($result['already'] ?? false);
                        Notification::make()
                            ->title($already ? 'Domein was al geregistreerd' : 'Domein geregistreerd')
                            ->body($already
                                ? "{$result['domain']} stond al in ons OpenProvider-account; DNS gecontroleerd. Niets dubbel aangemaakt."
                                : "{$result['domain']} geregistreerd en DNS naar {$result['ip']} gezet. Zet de site op 'live' zodra de DNS is doorgekomen.")
                            ->success()->send();
                        $this->refreshFormData(['meta']);
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Registratie mislukt')
                            ->body($e->getMessage())
                            ->danger()->persistent()->send();
                    }
                }),

            // Plesk: domein als alias van betergeregeld.com + Let's Encrypt.
            Action::make('pleskAlias')
                ->label(fn () => filled(data_get($this->record->meta, 'plesk_provisioned_at')) ? 'Plesk-alias ✓ (opnieuw)' : 'Plesk-alias + SSL')
                ->icon('heroicon-m-server')
                ->color(fn () => filled(data_get($this->record->meta, 'plesk_provisioned_at')) ? 'gray' : 'warning')
                ->visible(fn () => filled($this->record->domain))
                ->requiresConfirmation()
                ->modalHeading('Plesk-alias aanmaken + certificaat')
                ->modalDescription(fn () => "Voegt {$this->record->domain} toe als domein-alias van betergeregeld.com in Plesk en (her)geeft het Let's Encrypt-certificaat uit. Idempotent: bestaat de alias al, dan wordt niets dubbel aangemaakt. Draai dit op de VPS zelf (Plesk is daar bereikbaar).")
                ->modalSubmitActionLabel('Ja, aanmaken')
                ->action(function (): void {
                    try {
                        $res = app(PleskClient::class)->provisionAlias((string) $this->record->domain);
                        if ($res['ok']) {
                            $meta = (array) $this->record->meta;
                            $meta['plesk_provisioned_at'] = now()->toIso8601String();
                            $this->record->meta = $meta;
                            $this->record->save();
                            $this->refreshFormData(['meta']);
                        }
                        $body = collect($res['steps'])->map(fn ($v, $k) => "• {$k}: {$v}")->implode("\n");
                        $n = Notification::make()
                            ->title($res['ok'] ? 'Plesk-alias + SSL ingeregeld' : 'Alias OK, SSL nog niet gelukt')
                            ->body($body);
                        ($res['ok'] ? $n->success() : $n->warning())->persistent()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Plesk-alias mislukt')->body($e->getMessage())->danger()->persistent()->send();
                    }
                }),

            // Google Search Console inregelen: verifieer (DNS-TXT via OpenProvider),
            // property toevoegen + sitemap indienen. Alleen bij een gekoppeld domein.
            Action::make('provisionGsc')
                ->label(fn () => filled(data_get($this->record->meta, 'gsc_provisioned_at')) ? 'Search ✓ (opnieuw)' : 'Search inregelen')
                ->icon('heroicon-m-magnifying-glass')
                ->color(fn () => filled(data_get($this->record->meta, 'gsc_provisioned_at')) ? 'gray' : 'info')
                ->visible(fn () => filled($this->record->domain))
                ->requiresConfirmation()
                ->modalHeading('Google Search Console inregelen')
                ->modalDescription(fn () => "Verifieert {$this->record->domain} via een DNS-TXT-record (OpenProvider), voegt de property toe in Search Console en dient de sitemap in. Idempotent: al-geverifieerd/al-toegevoegd wordt overgeslagen. Alleen zinvol voor een live, bereikbaar domein waarvan de DNS in OpenProvider staat.")
                ->modalSubmitActionLabel('Ja, inregelen')
                ->action(function (): void {
                    try {
                        $res = app(GscProvisioner::class)->provision($this->record);
                        if ($res['ok']) {
                            $meta = (array) $this->record->meta;
                            $meta['gsc_provisioned_at'] = now()->toIso8601String();
                            $this->record->meta = $meta;
                            $this->record->save();
                            $this->refreshFormData(['meta']);
                        }
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
