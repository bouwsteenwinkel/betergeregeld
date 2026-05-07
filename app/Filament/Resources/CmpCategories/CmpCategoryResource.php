<?php

namespace App\Filament\Resources\CmpCategories;

use App\Filament\Resources\CmpCategories\Pages\ManageCmpCategories;
use App\Models\CmpCategory;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CmpCategoryResource extends Resource
{
    protected static ?string $model = CmpCategory::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
    protected static string|\UnitEnum|null $navigationGroup = 'CMP (Cookies)';
    protected static ?int $navigationSort = 20;
    protected static ?string $recordTitleAttribute = 'key';
    protected static ?string $modelLabel = 'Categorie';
    protected static ?string $pluralModelLabel = 'Categorieën';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('tenant_key')->label('Tenant')->default('bouwsteenwinkel')->required()->maxLength(64),
            TextInput::make('key')
                ->label('Sleutel')
                ->required()
                ->maxLength(32)
                ->placeholder('necessary, functional, analytics, marketing')
                ->helperText('Short-key zoals "analytics". Wordt gebruikt door scripts om te koppelen.'),
            TextInput::make('sort_order')->label('Volgorde')->numeric()->default(50)->required(),
            Toggle::make('is_required')->label('Verplicht (necessary)')->helperText('Kan niet worden uitgeschakeld door bezoeker.'),
            Toggle::make('is_enabled')->label('Actief')->default(true)->helperText('Toon in banner/voorkeuren-modal.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant_key')->label('Tenant')->searchable()->sortable(),
                TextColumn::make('key')->label('Sleutel')->searchable()->sortable()->badge(),
                IconColumn::make('is_required')->label('Verplicht')->boolean(),
                IconColumn::make('is_enabled')->label('Actief')->boolean(),
                TextColumn::make('sort_order')->label('Volgorde')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('tenant_key')->options(fn () => CmpCategory::query()->distinct()->pluck('tenant_key', 'tenant_key')->all()),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageCmpCategories::route('/')];
    }
}
