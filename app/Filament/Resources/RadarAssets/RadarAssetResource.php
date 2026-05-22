<?php

namespace App\Filament\Resources\RadarAssets;

use App\Filament\Resources\RadarAssets\Pages\CreateRadarAsset;
use App\Filament\Resources\RadarAssets\Pages\EditRadarAsset;
use App\Filament\Resources\RadarAssets\Pages\ListRadarAssets;
use App\Filament\Resources\RadarAssets\Pages\ViewRadarAsset;
use App\Filament\Resources\RadarAssets\RelationManagers\FindingsRelationManager;
use App\Filament\Resources\RadarAssets\RelationManagers\ScansRelationManager;
use App\Filament\Resources\RadarAssets\RelationManagers\SoftwareInstancesRelationManager;
use App\Filament\Resources\RadarAssets\Schemas\RadarAssetForm;
use App\Filament\Resources\RadarAssets\Schemas\RadarAssetInfolist;
use App\Filament\Resources\RadarAssets\Tables\RadarAssetsTable;
use App\Models\AccessGuard\Radar\Asset;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RadarAssetResource extends Resource
{
	protected static ?string $model = Asset::class;

	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

	protected static string|\UnitEnum|null $navigationGroup = 'AccessGuard';

	protected static ?string $navigationLabel = 'Radar — Assets';

	protected static ?int $navigationSort = 50;

	protected static ?string $modelLabel = 'Radar asset';

	protected static ?string $pluralModelLabel = 'Radar assets';

	protected static ?string $recordTitleAttribute = 'name';

	public static function form(Schema $schema): Schema
	{
		return RadarAssetForm::configure($schema);
	}

	public static function infolist(Schema $schema): Schema
	{
		return RadarAssetInfolist::configure($schema);
	}

	public static function table(Table $table): Table
	{
		return RadarAssetsTable::configure($table);
	}

	public static function getRelations(): array
	{
		return [
			FindingsRelationManager::class,
			SoftwareInstancesRelationManager::class,
			ScansRelationManager::class,
		];
	}

	public static function getPages(): array
	{
		return [
			'index'  => ListRadarAssets::route('/'),
			'create' => CreateRadarAsset::route('/create'),
			'view'   => ViewRadarAsset::route('/{record}'),
			'edit'   => EditRadarAsset::route('/{record}/edit'),
		];
	}
}
