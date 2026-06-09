<?php

namespace App\Filament\Resources\SiteHealth;

use App\Filament\Resources\SiteHealth\Pages\ListSiteHealth;
use App\Models\Seo\SeoProperty;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

/**
 * Platform-breed health-overzicht: alle actieve sites met hun rode vlaggen
 * (uptime, SEO-versheid, malware/blacklist, kwetsbaarheden, file-integrity,
 * kwaliteit) in één super-admin-scherm.
 */
class SiteHealthResource extends Resource
{
	protected static ?string $model = SeoProperty::class;

	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

	protected static string|\UnitEnum|null $navigationGroup = 'Monitoring';

	protected static ?string $navigationLabel = 'Site-health';

	protected static ?int $navigationSort = 1;

	protected static ?string $modelLabel = 'site';

	protected static ?string $pluralModelLabel = 'site-health';

	/** @var array<int,array<string,mixed>> */
	private static array $cache = [];

	public static function canAccess(): bool
	{
		return auth()->user()?->isSuperAdmin() ?? false;
	}

	/** @return array<string,mixed> */
	public static function health(SeoProperty $p): array
	{
		if (isset(self::$cache[$p->id])) {
			return self::$cache[$p->id];
		}

		$id = $p->id;
		$uptime = DB::table('monitor_checks')->where('property_id', $id)->value('last_status');

		$sec = DB::table('security_scans')->where('property_id', $id)->where('status', 'completed')
			->orderByDesc('completed_at')->first();
		$secFlagged = $sec ? ((bool) $sec->blacklisted || $sec->safe_browsing === 'flagged') : false;

		$vulns = (int) DB::table('site_vulnerabilities')->where('property_id', $id)->count();
		$integ = (int) DB::table('site_integrity_issues')->where('property_id', $id)
			->whereIn('type', ['modified', 'unexpected'])->count();

		$pageIds = DB::table('monitored_pages')->where('site_id', $id)->pluck('id');
		$score = $pageIds->isNotEmpty()
			? DB::table('quality_scans')->whereIn('monitored_page_id', $pageIds)->where('status', 'completed')
				->orderByDesc('completed_at')->value('score')
			: null;

		$seoStale = $p->freshness_alert_state === 'stale';

		$danger = $uptime === 'down' || $secFlagged || $vulns > 0 || $integ > 0 || ($score !== null && $score < 50);
		$warn = $seoStale || ($score !== null && $score < 70);
		$overall = $danger ? 'danger' : ($warn ? 'warning' : 'ok');

		return self::$cache[$id] = [
			'uptime'  => $uptime,
			'sec'     => $secFlagged,
			'vulns'   => $vulns,
			'integ'   => $integ,
			'score'   => $score !== null ? (int) $score : null,
			'stale'   => $seoStale,
			'overall' => $overall,
		];
	}

	public static function table(Table $table): Table
	{
		return $table
			->modifyQueryUsing(fn ($query) => $query->where('is_active', true)->with('tenant'))
			->defaultSort('label')
			->columns([
				TextColumn::make('overall')->label('Status')->badge()
					->state(fn (SeoProperty $r) => match (self::health($r)['overall']) {
						'danger' => 'Actie nodig', 'warning' => 'Let op', default => 'OK',
					})
					->color(fn (SeoProperty $r) => self::health($r)['overall'] === 'ok' ? 'success' : self::health($r)['overall']),
				TextColumn::make('label')->label('Site')->weight('bold')->searchable()
					->description(fn (SeoProperty $r) => $r->domain()),
				TextColumn::make('tenant.name')->label('Klant')->placeholder('Platform')->badge()->color('gray'),
				TextColumn::make('uptime')->label('Uptime')->badge()
					->state(fn (SeoProperty $r) => match (self::health($r)['uptime']) {
						'up' => 'Online', 'down' => 'Offline', default => '—',
					})
					->color(fn (SeoProperty $r) => match (self::health($r)['uptime']) {
						'up' => 'success', 'down' => 'danger', default => 'gray',
					}),
				TextColumn::make('seo')->label('SEO')->badge()
					->state(fn (SeoProperty $r) => self::health($r)['stale'] ? 'Staat stil' : 'OK')
					->color(fn (SeoProperty $r) => self::health($r)['stale'] ? 'danger' : 'success'),
				TextColumn::make('malware')->label('Malware/blacklist')->badge()
					->state(fn (SeoProperty $r) => self::health($r)['sec'] ? 'Geflagd' : 'Schoon')
					->color(fn (SeoProperty $r) => self::health($r)['sec'] ? 'danger' : 'success'),
				TextColumn::make('vulns')->label('Kwetsbaarheden')->badge()
					->state(fn (SeoProperty $r) => (string) self::health($r)['vulns'])
					->color(fn (SeoProperty $r) => self::health($r)['vulns'] > 0 ? 'danger' : 'success'),
				TextColumn::make('integ')->label('Core-integriteit')->badge()
					->state(fn (SeoProperty $r) => self::health($r)['integ'] > 0 ? self::health($r)['integ'] . ' afwijkend' : 'Intact')
					->color(fn (SeoProperty $r) => self::health($r)['integ'] > 0 ? 'danger' : 'success'),
				TextColumn::make('quality')->label('Kwaliteit')->badge()
					->state(fn (SeoProperty $r) => ($s = self::health($r)['score']) !== null ? (string) $s : '—')
					->color(fn (SeoProperty $r) => ($s = self::health($r)['score']) === null ? 'gray' : ($s >= 70 ? 'success' : ($s >= 50 ? 'warning' : 'danger'))),
			])
			->recordActions([])
			->toolbarActions([]);
	}

	public static function getPages(): array
	{
		return [
			'index' => ListSiteHealth::route('/'),
		];
	}
}
