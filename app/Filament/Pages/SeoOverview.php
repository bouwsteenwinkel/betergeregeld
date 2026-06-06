<?php

namespace App\Filament\Pages;

use App\Models\Seo\SeoQueryDaily;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

/**
 * Beperkte, tenant-facing SEO-weergave: de Search Console-cijfers van de
 * property/properties die aan de tenant gekoppeld zijn (laatste 28 dagen).
 * Super-admins beheren de properties via de GSC Properties-resource.
 */
class SeoOverview extends Page
{
	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlassCircle;

	protected static string|\UnitEnum|null $navigationGroup = 'SEO';

	protected static ?string $navigationLabel = 'SEO-prestaties';

	protected static ?int $navigationSort = 20;

	protected string $view = 'filament.pages.seo-overview';

	public function getTitle(): string
	{
		return 'SEO-prestaties';
	}

	public static function shouldRegisterNavigation(): bool
	{
		return self::propertyIds()->isNotEmpty();
	}

	public static function canAccess(): bool
	{
		return self::propertyIds()->isNotEmpty();
	}

	protected static function propertyIds(): Collection
	{
		$tenant = auth()->user()?->tenant;

		if (! $tenant) {
			return collect();
		}

		return $tenant->seoProperties()->where('is_active', true)->pluck('id');
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getSeoData(): array
	{
		$ids = self::propertyIds();

		if ($ids->isEmpty()) {
			return [];
		}

		$since = now()->subDays(28)->toDateString();

		$totals = SeoQueryDaily::query()
			->whereIn('property_id', $ids)
			->where('date', '>=', $since)
			->selectRaw('SUM(clicks) as clicks, SUM(impressions) as impressions, AVG(position) as position')
			->first();

		$clicks = (int) ($totals->clicks ?? 0);
		$impressions = (int) ($totals->impressions ?? 0);

		$topQueries = SeoQueryDaily::query()
			->whereIn('property_id', $ids)
			->where('date', '>=', $since)
			->selectRaw('query, SUM(clicks) as clicks, SUM(impressions) as impressions, AVG(position) as position')
			->groupBy('query')
			->orderByDesc('clicks')
			->limit(10)
			->get();

		return [
			'clicks'      => $clicks,
			'impressions' => $impressions,
			'ctr'         => $impressions > 0 ? round($clicks / $impressions * 100, 2) : 0.0,
			'position'    => round((float) ($totals->position ?? 0), 1),
			'top_queries' => $topQueries,
		];
	}
}
