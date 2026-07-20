<?php

namespace App\Filament\Pages;

use App\Services\Ads\GoogleAdsManager;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * Intern beheer van de Google Ads-campagnes (channel-sites). Haalt de campagnes
 * live uit de API en biedt aanmaken (vanuit het Search-template, gepauzeerd),
 * pauzeren/activeren en dagbudget wijzigen. Alleen voor super-admins.
 */
class GoogleAds extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|\UnitEnum|null $navigationGroup = 'Channel-sites';

    protected static ?string $navigationLabel = 'Google Ads';

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.google-ads';

    public bool $connected = false;

    /** @var array<int,array<string,mixed>> */
    public array $campaigns = [];

    /** Per campagne-ID het (bewerkbare) dagbudget als string. @var array<string,string> */
    public array $budgets = [];

    /** Per campagne-ID het (bewerkbare) CPC-plafond als string. @var array<string,string> */
    public array $maxcpcs = [];

    /** Per campagne-ID de advertentie-/reviewstatus + kwaliteit. @var array<string,array<string,string>> */
    public array $adStatus = [];

    /** Account-brede totalen over de afgelopen 30 dagen (grote blokken bovenin). @var array<string,int|float> */
    public array $totals30 = [];

    /** Toon ook gearchiveerde (verwijderde) campagnes in het overzicht. */
    public bool $showArchived = false;

    /** Automatisch verversen (Livewire-poll) terwijl je op de pagina blijft. */
    public bool $autoRefresh = false;

    /** Tijdstip van de laatste (her)laadactie, voor zichtbare feedback. */
    public ?string $lastLoaded = null;

    /** Nieuwe-campagne-formulier: gekozen profiel + (overschrijfbaar) budget/CPC. */
    public string $newProfile = 'bedrijfswebsite';

    public string $newBudget = '25';

    public string $newMaxCpc = '1.5';

    /** Fragment-herstel: gekozen campagne-ID + profiel om het fragment uit te lezen. */
    public string $snippetCampaign = '';

    public string $snippetProfile = 'bouwverrassing';

    /** @return array<string,string> profiel-key => label */
    public function profileOptions(): array
    {
        $out = [];
        foreach (app(GoogleAdsManager::class)->profiles() as $key => $p) {
            $out[$key] = $p['label'] ?? $key;
        }

        return $out;
    }

    /** @return array<string,mixed>|null het gekozen profiel (voor de formulier-toelichting) */
    public function currentProfile(): ?array
    {
        return app(GoogleAdsManager::class)->profile($this->newProfile);
    }

    public function updatedNewProfile(): void
    {
        $this->applyProfileDefaults();
    }

    private function applyProfileDefaults(): void
    {
        $p = app(GoogleAdsManager::class)->profile($this->newProfile);
        if ($p) {
            $this->newBudget = (string) ($p['budget'] ?? 25);
            $this->newMaxCpc = (string) ($p['max_cpc'] ?? 1.5);
        }
    }

    public function getTitle(): string
    {
        return 'Google Ads';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    public function mount(): void
    {
        $this->applyProfileDefaults();

        // Nooit een 500 op deze pagina: een koppeling-/API-fout degradeert netjes
        // naar het "niet gekoppeld"-scherm i.p.v. de admin om te leggen.
        try {
            $this->connected = app(GoogleAdsManager::class)->connected();

            if ($this->connected) {
                $this->laden();
            }
        } catch (\Throwable $e) {
            report($e);
            $this->connected = false;
        }
    }

    public function laden(): void
    {
        $this->campaigns = app(GoogleAdsManager::class)->listCampaigns();

        foreach ($this->campaigns as $c) {
            $this->budgets[$c['id']] = number_format((float) $c['budget'], 2, '.', '');
            $this->maxcpcs[$c['id']] = $c['maxCpc'] > 0 ? number_format((float) $c['maxCpc'], 2, '.', '') : '';
        }

        $this->adStatus   = app(GoogleAdsManager::class)->adStatusByCampaign();
        $this->totals30   = app(GoogleAdsManager::class)->totalsLast30Days();
        $this->lastLoaded = now()->format('H:i:s');
    }

    /**
     * Lichte ververs-actie voor de auto-poll: alleen de campagne-cijfers (vertoningen,
     * klikken, kosten, status) opnieuw ophalen. Raakt bewust NIET de budget-/CPC-
     * invoervelden ($budgets/$maxcpcs) aan, zodat een poll je niet overschrijft
     * terwijl je een bedrag aan het typen bent.
     */
    public function refreshMetrics(): void
    {
        $this->campaigns  = app(GoogleAdsManager::class)->listCampaigns();
        $this->adStatus   = app(GoogleAdsManager::class)->adStatusByCampaign();
        $this->totals30   = app(GoogleAdsManager::class)->totalsLast30Days();
        $this->lastLoaded = now()->format('H:i:s');
    }

    /**
     * De campagnes die zichtbaar zijn in de tabel. Gearchiveerde (REMOVED)
     * campagnes worden standaard verborgen; met $showArchived komen ze erbij.
     *
     * @return array<int,array<string,mixed>>
     */
    public function visibleCampaigns(): array
    {
        if ($this->showArchived) {
            return $this->campaigns;
        }

        return array_values(array_filter($this->campaigns, fn ($c) => $c['status'] !== 'REMOVED'));
    }

    /** Aantal gearchiveerde (verwijderde) campagnes — voor het schakel-label. */
    public function archivedCount(): int
    {
        return count(array_filter($this->campaigns, fn ($c) => $c['status'] === 'REMOVED'));
    }

    /** @return array{impressions:int,clicks:int,cost:float,conversions:float} */
    public function totals(): array
    {
        return [
            'impressions' => array_sum(array_column($this->campaigns, 'impressions')),
            'clicks'      => array_sum(array_column($this->campaigns, 'clicks')),
            'cost'        => array_sum(array_column($this->campaigns, 'cost')),
            'conversions' => array_sum(array_column($this->campaigns, 'conversions')),
        ];
    }

    public function createCampaign(): void
    {
        $budget = (float) str_replace(',', '.', $this->newBudget);
        $cpc    = (float) str_replace(',', '.', $this->newMaxCpc);

        if ($budget < 1 || $cpc < 0.1 || ! app(GoogleAdsManager::class)->profile($this->newProfile)) {
            Notification::make()->title('Controleer het profiel, het budget (≥ €1) en de max. CPC (≥ €0,10).')->danger()->send();

            return;
        }

        $res = app(GoogleAdsManager::class)->createSearchCampaign($this->newProfile, null, $budget, $cpc);

        if (! $res['ok']) {
            Notification::make()->title('Aanmaken mislukt')->body($res['error'])->danger()->send();

            return;
        }

        Notification::make()->title('Campagne aangemaakt — gepauzeerd')->body('Controleer alles en zet hem daarna pas op actief.')->success()->send();
        $this->laden();
    }

    /**
     * Vervangt het structured-snippet-fragment van de gekozen campagne door de
     * (goedgekeurde) versie uit het gekozen profiel. Herstelt een afgekeurd
     * fragment zonder de campagne opnieuw op te bouwen.
     */
    public function syncSnippet(): void
    {
        if ($this->snippetCampaign === '' || ! app(GoogleAdsManager::class)->profile($this->snippetProfile)) {
            Notification::make()->title('Kies een campagne én een profiel.')->danger()->send();

            return;
        }

        $res = app(GoogleAdsManager::class)->syncSnippetFromProfile($this->snippetCampaign, $this->snippetProfile);

        if (! $res['ok']) {
            Notification::make()->title('Fragment bijwerken mislukt')->body($res['error'])->danger()->send();

            return;
        }

        Notification::make()
            ->title('Fragment bijgewerkt')
            ->body(($res['removed'] > 0 ? 'Oud fragment ontkoppeld. ' : '') . 'Google moet de nieuwe versie nog goedkeuren (meestal binnen 1 werkdag).')
            ->success()->send();

        $this->laden();
    }

    public function pause(string $id): void
    {
        $this->klaar(app(GoogleAdsManager::class)->setStatus($id, 'PAUSED'), 'Campagne gepauzeerd');
    }

    public function enable(string $id): void
    {
        $this->klaar(app(GoogleAdsManager::class)->setStatus($id, 'ENABLED'), 'Campagne geactiveerd — hij geeft nu budget uit');
    }

    public function archive(string $id): void
    {
        $this->klaar(app(GoogleAdsManager::class)->removeCampaign($id), 'Campagne gearchiveerd');
    }

    public function saveBudget(string $id): void
    {
        $euro = (float) str_replace(',', '.', (string) ($this->budgets[$id] ?? '0'));

        if ($euro < 1) {
            Notification::make()->title('Budget moet minstens €1 per dag zijn.')->danger()->send();

            return;
        }

        $this->klaar(app(GoogleAdsManager::class)->setBudget($id, $euro), 'Dagbudget bijgewerkt naar € ' . number_format($euro, 2, ',', '.'));
    }

    public function saveMaxCpc(string $id): void
    {
        $euro = (float) str_replace(',', '.', (string) ($this->maxcpcs[$id] ?? '0'));

        if ($euro < 0.1) {
            Notification::make()->title('Het CPC-plafond moet minstens €0,10 zijn.')->danger()->send();

            return;
        }

        $this->klaar(app(GoogleAdsManager::class)->setMaxCpc($id, $euro), 'Max. CPC bijgewerkt naar € ' . number_format($euro, 2, ',', '.'));
    }

    /** @param array{ok:bool,error:?string} $res */
    private function klaar(array $res, string $melding): void
    {
        if (! $res['ok']) {
            Notification::make()->title('Mislukt')->body($res['error'])->danger()->send();

            return;
        }

        Notification::make()->title($melding)->success()->send();
        $this->laden();
    }
}
