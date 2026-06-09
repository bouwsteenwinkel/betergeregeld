<?php

namespace App\Filament\Resources\Clients;

use App\Filament\Resources\Clients\Pages\ListClients;
use App\Models\Tenant;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Klanten-overzicht voor het bureau (Rankdata) én de super-admin: per klant een
 * samenvatting (SEO 30d + uptime) en een knop naar het leesbare dashboard.
 * Afgeschermd: een bureau-beheerder ziet alleen de eigen klanten.
 */
class ClientResource extends Resource
{
	protected static ?string $model = Tenant::class;

	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

	protected static string|\UnitEnum|null $navigationGroup = 'Rankdata';

	protected static ?string $navigationLabel = 'Klanten';

	protected static ?int $navigationSort = 20;

	protected static ?string $modelLabel = 'klant';

	protected static ?string $pluralModelLabel = 'klanten';

	public static function canAccess(): bool
	{
		$u = auth()->user();

		return (bool) ($u?->isSuperAdmin() || $u?->isAgencyAdmin());
	}

	public static function getEloquentQuery(): Builder
	{
		$q = parent::getEloquentQuery()->whereNotNull('agency_id')->with('agency');
		$u = auth()->user();
		if ($u && $u->isAgencyAdmin()) {
			$q->where('agency_id', $u->agency_id);
		}

		return $q;
	}

	/** Eén keer per klant de samenvatting ophalen (gememoïseerd binnen het verzoek). */
	private static function summary(Tenant $t): array
	{
		static $cache = [];
		if (isset($cache[$t->id])) {
			return $cache[$t->id];
		}

		$prop = DB::table('seo_properties')->where('tenant_id', $t->id)->first();
		$seo = ['clicks' => 0, 'impr' => 0, 'pos' => null, 'domain' => null];
		if ($prop) {
			$seo['domain'] = preg_replace('/^sc-domain:/', '', $prop->site_url);
			$row = DB::table('seo_query_daily')->where('property_id', $prop->id)
				->where('date', '>=', now()->subDays(30)->toDateString())
				->selectRaw('SUM(clicks) c, SUM(impressions) i, AVG(position) p')->first();
			$seo['clicks'] = (int) ($row->c ?? 0);
			$seo['impr'] = (int) ($row->i ?? 0);
			$seo['pos'] = $row->p !== null ? round($row->p, 1) : null;
		}

		$uptime = null;
		$check = DB::table('monitor_checks')->where('tenant_id', $t->id)->value('id');
		if ($check) {
			$res = DB::table('monitor_check_results')->where('check_id', $check)
				->where('checked_at', '>=', now()->subDays(7));
			$total = (clone $res)->count();
			$uptime = $total ? round((clone $res)->where('status', 'up')->count() / $total * 100, 1) : null;
		}

		return $cache[$t->id] = $seo + ['uptime' => $uptime];
	}

	public static function table(Table $table): Table
	{
		return $table
			->defaultSort('name')
			->columns([
				TextColumn::make('name')->label('Klant')->searchable()->weight('bold')
					->description(fn (Tenant $r) => self::summary($r)['domain'] ?? '—'),
				TextColumn::make('agency.name')->label('Bureau')->badge()->color('gray'),
				TextColumn::make('clicks')->label('Clicks (30d)')
					->state(fn (Tenant $r) => number_format(self::summary($r)['clicks'], 0, ',', '.')),
				TextColumn::make('impr')->label('Impressies (30d)')
					->state(fn (Tenant $r) => number_format(self::summary($r)['impr'], 0, ',', '.')),
				TextColumn::make('pos')->label('Gem. positie')
					->state(fn (Tenant $r) => ($p = self::summary($r)['pos']) !== null ? number_format($p, 1, ',', '.') : '—'),
				TextColumn::make('uptime')->label('Uptime 7d')->badge()
					->state(fn (Tenant $r) => ($u = self::summary($r)['uptime']) !== null ? number_format($u, 1, ',', '.') . '%' : '—')
					->color(fn (Tenant $r) => ($u = self::summary($r)['uptime']) === null ? 'gray' : ($u >= 99.5 ? 'success' : ($u >= 98 ? 'warning' : 'danger'))),
				TextColumn::make('monthly_price')->label('Maandprijs')->badge()->color('success')
					->state(fn (Tenant $r) => '€ ' . number_format(app(\App\Services\Rankdata\RankdataBilling::class)->costForTenant($r)['total'], 2, ',', '.')),
			])
			->filters([
				\Filament\Tables\Filters\TernaryFilter::make('is_demo')->label('Demo')
					->placeholder('Alles')->trueLabel('Alleen demo')->falseLabel('Zonder demo')
					->default(false),
			])
			->recordActions([
				Action::make('dashboard')->label('Dashboard')->icon('heroicon-m-arrow-top-right-on-square')
					->color('primary')
					->url(fn (Tenant $r) => route('rankdata.client', ['locale' => 'nl', 'tenant' => $r->id]))
					->openUrlInNewTab(),
				\App\Filament\Actions\RankdataReportAction::make(),
			]);
	}

	public static function getPages(): array
	{
		return [
			'index' => ListClients::route('/'),
		];
	}
}
