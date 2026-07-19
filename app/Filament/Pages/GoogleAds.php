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

    /** Nieuwe-campagne-formulier. */
    public string $newUrl = 'https://jouw-bedrijfswebsite.nl';

    public string $newBudget = '20';

    public string $newMaxCpc = '1.5';

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

        if ($budget < 1 || $cpc < 0.1 || ! filter_var($this->newUrl, FILTER_VALIDATE_URL)) {
            Notification::make()->title('Controleer de URL, het budget (≥ €1) en de max. CPC (≥ €0,10).')->danger()->send();

            return;
        }

        $name = 'bedrijfswebsite · Search · ' . now()->format('Y-m-d H:i');
        $res  = app(GoogleAdsManager::class)->createSearchCampaign($name, $this->newUrl, $budget, $cpc);

        if (! $res['ok']) {
            Notification::make()->title('Aanmaken mislukt')->body($res['error'])->danger()->send();

            return;
        }

        Notification::make()->title('Campagne aangemaakt — gepauzeerd')->body('Controleer alles en zet hem daarna pas op actief.')->success()->send();
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
