<?php

namespace App\Filament\Resources\RadarFindings;

use App\Filament\Resources\RadarFindings\Pages\ListRadarFindings;
use App\Filament\Resources\RadarFindings\Pages\ViewRadarFinding;
use App\Filament\Resources\RadarFindings\Schemas\RadarFindingInfolist;
use App\Filament\Resources\RadarFindings\Tables\RadarFindingsTable;
use App\Models\AccessGuard\Radar\Finding;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RadarFindingResource extends Resource
{
	protected static ?string $model = Finding::class;

	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

	protected static string|\UnitEnum|null $navigationGroup = 'AccessGuard';

	protected static ?string $navigationLabel = 'Radar — Findings';

	protected static ?int $navigationSort = 51;

	protected static ?string $modelLabel = 'Finding';

	protected static ?string $pluralModelLabel = 'Findings';

	protected static ?string $recordTitleAttribute = 'id';

	public static function infolist(Schema $schema): Schema
	{
		return RadarFindingInfolist::configure($schema);
	}

	public static function table(Table $table): Table
	{
		return RadarFindingsTable::configure($table);
	}

	public static function getPages(): array
	{
		return [
			'index' => ListRadarFindings::route('/'),
			'view'  => ViewRadarFinding::route('/{record}'),
		];
	}

	public static function getNavigationBadge(): ?string
	{
		$count = static::$model::query()
			->whereIn('status', ['new', 'reopened'])
			->where('severity', 'critical')
			->count();
		return $count > 0 ? (string) $count : null;
	}

	public static function getNavigationBadgeColor(): ?string
	{
		return 'danger';
	}
}
