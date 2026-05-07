<?php

namespace App\Filament\Resources\CmpTexts;

use App\Filament\Resources\CmpTexts\Pages\ManageCmpTexts;
use App\Models\CmpText;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CmpTextResource extends Resource
{
    protected static ?string $model = CmpText::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;
    protected static string|\UnitEnum|null $navigationGroup = 'CMP (Cookies)';
    protected static ?int $navigationSort = 40;
    protected static ?string $recordTitleAttribute = 'text_key';
    protected static ?string $modelLabel = 'Tekst';
    protected static ?string $pluralModelLabel = 'Teksten';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('tenant_key')->label('Tenant')->default('bouwsteenwinkel')->required()->maxLength(64),
            Select::make('lang')
                ->label('Taal')
                ->required()
                ->options(['nl' => 'Nederlands', 'en' => 'English', 'de' => 'Deutsch', 'fr' => 'Français'])
                ->default('nl'),
            TextInput::make('text_key')
                ->label('Sleutel')
                ->required()
                ->maxLength(64)
                ->helperText('Bv. banner.title, banner.btn_accept_all, cat.analytics.name')
                ->placeholder('banner.title'),
            Textarea::make('text_value')->label('Waarde')->required()->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant_key')->label('Tenant')->searchable()->toggleable(),
                TextColumn::make('lang')->label('Taal')->badge()->sortable(),
                TextColumn::make('text_key')->label('Sleutel')->searchable()->sortable()->copyable(),
                TextColumn::make('text_value')->label('Waarde')->limit(80)->wrap(),
            ])
            ->defaultSort('text_key')
            ->filters([
                SelectFilter::make('tenant_key')->options(fn () => CmpText::query()->distinct()->pluck('tenant_key', 'tenant_key')->all()),
                SelectFilter::make('lang')->options(['nl' => 'NL', 'en' => 'EN', 'de' => 'DE', 'fr' => 'FR']),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageCmpTexts::route('/')];
    }
}
