<?php

namespace App\Filament\Pages;

use App\Models\ChannelEvent;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

/**
 * First-party funnel-overzicht op basis van channel_events: voorbeeld → planner → boeking,
 * met conversieratio's, per dag/site. Dit is onze eigen grondwaarheid om naast de
 * Meta-/Google-cijfers te leggen (en te zien hoeveel consent-weigering "kost").
 * Alleen super-admins: dit is BSW-interne meting over alle channel-sites.
 */
class FunnelEvents extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-funnel';

    protected static string|\UnitEnum|null $navigationGroup = 'Channel-sites';

    protected static ?string $navigationLabel = 'Funnel-events';

    protected static ?int $navigationSort = 30;

    protected string $view = 'filament.pages.funnel-events';

    /** Periode in dagen (1 = alleen vandaag). */
    public int $days = 30;

    public function getTitle(): string
    {
        return 'Funnel-events';
    }

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    /** De trechter-stappen: event-key => label. */
    public const STAGES = [
        'preview_start' => 'Voorbeeld gestart',
        'preview_ready' => 'Voorbeeld getoond · ViewContent',
        'planner_opened' => 'Planner geopend · Contact',
        'appointment_booked' => 'Afspraak geboekt · Lead',
    ];

    /**
     * Trechter met unieke bezoeken (distinct visit_ref) per stap en de ratio t.o.v. de
     * vorige stap. Unieke bezoeken i.p.v. ruwe events, zodat een dubbele trigger niet dubbel telt.
     *
     * @return array<int,array<string,mixed>>
     */
    public function getFunnel(): array
    {
        $counts = $this->baseQuery()
            ->selectRaw('event, COUNT(*) AS total, COUNT(DISTINCT visit_ref) AS visits')
            ->groupBy('event')
            ->get()
            ->keyBy('event');

        $out = [];
        $prev = null;
        foreach (self::STAGES as $event => $label) {
            $visits = (int) ($counts[$event]->visits ?? 0);
            $total = (int) ($counts[$event]->total ?? 0);
            $out[] = [
                'event' => $event,
                'label' => $label,
                'visits' => $visits,
                'total' => $total,
                // Ratio t.o.v. de vorige stap (conversie van stap naar stap).
                'ratio' => ($prev !== null && $prev > 0) ? round($visits / $prev * 100, 1) : null,
            ];
            $prev = $visits;
        }

        return $out;
    }

    /**
     * Per site: voorbeeld getoond, planner geopend, geboekt + boekingsratio.
     *
     * @return Collection<int,array<string,mixed>>
     */
    public function getPerSite(): Collection
    {
        $rows = $this->baseQuery()
            ->whereIn('event', array_keys(self::STAGES))
            ->selectRaw("site_key, event, COUNT(DISTINCT visit_ref) AS visits")
            ->groupBy('site_key', 'event')
            ->get();

        return $rows->groupBy('site_key')->map(function ($group, $site) {
            $by = $group->keyBy('event');
            $ready = (int) ($by['preview_ready']->visits ?? 0);
            $booked = (int) ($by['appointment_booked']->visits ?? 0);

            return [
                'site' => $site ?: '(onbekend)',
                'preview_ready' => $ready,
                'planner_opened' => (int) ($by['planner_opened']->visits ?? 0),
                'booked' => $booked,
                'rate' => $ready > 0 ? round($booked / $ready * 100, 1) : null,
            ];
        })->sortByDesc('preview_ready')->values();
    }

    private function baseQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $since = now()->subDays(max(1, $this->days) - 1)->startOfDay();

        return ChannelEvent::query()->where('created_at', '>=', $since);
    }
}
