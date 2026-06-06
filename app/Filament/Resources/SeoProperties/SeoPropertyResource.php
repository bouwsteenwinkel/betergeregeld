<?php

namespace App\Filament\Resources\SeoProperties;

use App\Filament\Resources\SeoProperties\Pages\CreateSeoProperty;
use App\Filament\Resources\SeoProperties\Pages\EditSeoProperty;
use App\Filament\Resources\SeoProperties\Pages\ListSeoProperties;
use App\Filament\Resources\SeoProperties\Schemas\SeoPropertyForm;
use App\Filament\Resources\SeoProperties\Tables\SeoPropertiesTable;
use App\Models\Seo\SeoProperty;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SeoPropertyResource extends Resource
{
	protected static ?string $model = SeoProperty::class;

	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlassCircle;

	protected static string|\UnitEnum|null $navigationGroup = 'SEO';

	protected static ?string $navigationLabel = 'GSC Properties';

	protected static ?int $navigationSort = 10;

	protected static ?string $modelLabel = 'GSC-property';

	protected static ?string $pluralModelLabel = 'GSC-properties';

	protected static ?string $recordTitleAttribute = 'label';

	public static function canAccess(): bool
	{
		return auth()->user()?->isSuperAdmin() ?? false;
	}

	public static function form(Schema $schema): Schema
	{
		return SeoPropertyForm::configure($schema);
	}

	public static function table(Table $table): Table
	{
		return SeoPropertiesTable::configure($table);
	}

	public static function getPages(): array
	{
		return [
			'index'  => ListSeoProperties::route('/'),
			'create' => CreateSeoProperty::route('/create'),
			'edit'   => EditSeoProperty::route('/{record}/edit'),
		];
	}
}
