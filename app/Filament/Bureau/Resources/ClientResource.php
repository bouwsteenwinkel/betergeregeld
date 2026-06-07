<?php

namespace App\Filament\Bureau\Resources;

use App\Filament\Bureau\Resources\Pages\CreateClient;
use App\Filament\Bureau\Resources\Pages\EditClient;
use App\Filament\Bureau\Resources\Pages\ListClients;
use App\Models\Tenant;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Klanten van het ingelogde bureau (in het afgeschermde /bureau-panel). Het
 * bureau ziet/beheert UITSLUITEND de eigen klanten — getEloquentQuery scopt op
 * de agency van de ingelogde gebruiker. Aanmaken maakt in één keer tenant +
 * SEO-property + klant-login aan.
 */
class ClientResource extends Resource
{
	protected static ?string $model = Tenant::class;

	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

	protected static ?string $navigationLabel = 'Klanten';

	protected static ?string $modelLabel = 'klant';

	protected static ?string $pluralModelLabel = 'klanten';

	protected static ?string $recordTitleAttribute = 'name';

	public static function getEloquentQuery(): Builder
	{
		// Altijd scopen op het eigen bureau — fail-safe ook tegen directe URL's.
		$agencyId = auth()->user()?->agency_id;

		return parent::getEloquentQuery()->where('agency_id', $agencyId);
	}

	private static function summary(Tenant $t): array
	{
		static $cache = [];
		if (isset($cache[$t->id])) {
			return $cache[$t->id];
		}
		$prop = DB::table('seo_properties')->where('tenant_id', $t->id)->first();
		$clicks = 0; $impr = 0; $pos = null; $domain = null;
		if ($prop) {
			$domain = preg_replace('/^sc-domain:/', '', $prop->site_url);
			$row = DB::table('seo_query_daily')->where('property_id', $prop->id)
				->where('date', '>=', now()->subDays(30)->toDateString())
				->selectRaw('SUM(clicks) c, SUM(impressions) i, AVG(position) p')->first();
			$clicks = (int) ($row->c ?? 0); $impr = (int) ($row->i ?? 0);
			$pos = $row->p !== null ? round($row->p, 1) : null;
		}
		$uptime = null;
		$check = DB::table('monitor_checks')->where('tenant_id', $t->id)->value('id');
		if ($check) {
			$res = DB::table('monitor_check_results')->where('check_id', $check)->where('checked_at', '>=', now()->subDays(7));
			$total = (clone $res)->count();
			$uptime = $total ? round((clone $res)->where('status', 'up')->count() / $total * 100, 1) : null;
		}

		return $cache[$t->id] = compact('domain', 'clicks', 'impr', 'pos', 'uptime');
	}

	public static function form(Schema $schema): Schema
	{
		return $schema->components([
			Section::make('Klant')->columns(2)->components([
				TextInput::make('name')->label('Naam')->required()->maxLength(120)->placeholder('Klantnaam'),
				Toggle::make('is_active')->label('Actief')->default(true),
			]),
			Section::make('Aanmaken')->columns(2)
				->description('Bij aanmaken worden automatisch een statistieken-property en een klant-login gemaakt.')
				->visibleOn('create')
				->components([
					TextInput::make('domain')->label('Website (domein)')->required()->maxLength(190)
						->placeholder('klant.nl')->helperText('Zonder https:// — bv. klant.nl'),
					TextInput::make('email')->label('Login-e-mail klant')->email()->required()->maxLength(190)
						->placeholder('info@klant.nl'),
					TextInput::make('password')->label('Wachtwoord')->password()->revealable()
						->helperText('Leeg = automatisch genereren (wordt na opslaan getoond).')
						->columnSpanFull(),
				]),
		]);
	}

	public static function table(Table $table): Table
	{
		return $table
			->defaultSort('name')
			->columns([
				TextColumn::make('name')->label('Klant')->searchable()->weight('bold')
					->description(fn (Tenant $r) => self::summary($r)['domain'] ?? '—'),
				TextColumn::make('clicks')->label('Clicks (30d)')
					->state(fn (Tenant $r) => number_format(self::summary($r)['clicks'], 0, ',', '.')),
				TextColumn::make('impr')->label('Impressies')
					->state(fn (Tenant $r) => number_format(self::summary($r)['impr'], 0, ',', '.')),
				TextColumn::make('pos')->label('Gem. positie')
					->state(fn (Tenant $r) => ($p = self::summary($r)['pos']) !== null ? number_format($p, 1, ',', '.') : '—'),
				TextColumn::make('uptime')->label('Uptime 7d')->badge()
					->state(fn (Tenant $r) => ($u = self::summary($r)['uptime']) !== null ? number_format($u, 1, ',', '.') . '%' : '—')
					->color(fn (Tenant $r) => ($u = self::summary($r)['uptime']) === null ? 'gray' : ($u >= 99.5 ? 'success' : ($u >= 98 ? 'warning' : 'danger'))),
				IconColumn::make('is_active')->label('Actief')->boolean()->toggleable(),
			])
			->recordActions([
				Action::make('dashboard')->label('Dashboard')->icon('heroicon-m-arrow-top-right-on-square')->color('primary')
					->url(fn (Tenant $r) => route('rankdata.client', ['locale' => 'nl', 'tenant' => $r->id]))
					->openUrlInNewTab(),
				EditAction::make(),
			]);
	}

	public static function getPages(): array
	{
		return [
			'index'  => ListClients::route('/'),
			'create' => CreateClient::route('/create'),
			'edit'   => EditClient::route('/{record}/edit'),
		];
	}
}
