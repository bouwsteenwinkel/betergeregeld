<?php

namespace App\Filament\Resources\Agencies;

use App\Filament\Resources\Agencies\Pages\CreateAgency;
use App\Filament\Resources\Agencies\Pages\EditAgency;
use App\Filament\Resources\Agencies\Pages\ListAgencies;
use App\Models\Agency;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Bureaus (white-label resellers zoals Rankdata) — alleen super-admin beheert ze.
 */
class AgencyResource extends Resource
{
	protected static ?string $model = Agency::class;

	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

	protected static string|\UnitEnum|null $navigationGroup = 'Rankdata';

	protected static ?string $navigationLabel = 'Bureaus';

	protected static ?int $navigationSort = 10;

	protected static ?string $modelLabel = 'bureau';

	protected static ?string $pluralModelLabel = 'bureaus';

	protected static ?string $recordTitleAttribute = 'name';

	public static function canAccess(): bool
	{
		return auth()->user()?->isSuperAdmin() ?? false;
	}

	public static function form(Schema $schema): Schema
	{
		return $schema->components([
			Section::make('Bureau')->columns(2)->components([
				TextInput::make('name')->label('Naam')->required()->maxLength(120)->placeholder('Rankdata'),
				TextInput::make('slug')->label('Slug')->maxLength(64)
					->placeholder('rankdata')->helperText('Leeg = automatisch uit de naam.'),
				TextInput::make('contact_email')->label('Contact-e-mail')->email()->maxLength(190),
				ColorPicker::make('primary_color')->label('Merkkleur')
					->helperText('Voor het white-label klant-dashboard.'),
				Toggle::make('is_active')->label('Actief')->default(true),
			]),
		]);
	}

	public static function table(Table $table): Table
	{
		return $table
			->modifyQueryUsing(fn ($query) => $query->withCount('tenants'))
			->defaultSort('name')
			->columns([
				TextColumn::make('name')->label('Bureau')->searchable()->weight('bold')
					->description(fn (Agency $r) => $r->contact_email),
				TextColumn::make('slug')->label('Slug')->color('gray'),
				TextColumn::make('primary_color')->label('Kleur')->badge()
					->color(fn () => 'gray')->formatStateUsing(fn (?string $s) => $s ?: '—'),
				TextColumn::make('tenants_count')->label('Klanten')->badge()->color('primary'),
				IconColumn::make('is_active')->label('Actief')->boolean(),
			])
			->recordActions([EditAction::make()])
			->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
	}

	public static function getPages(): array
	{
		return [
			'index'  => ListAgencies::route('/'),
			'create' => CreateAgency::route('/create'),
			'edit'   => EditAgency::route('/{record}/edit'),
		];
	}
}
