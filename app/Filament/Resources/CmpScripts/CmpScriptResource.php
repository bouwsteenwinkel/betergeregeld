<?php

namespace App\Filament\Resources\CmpScripts;

use App\Filament\Resources\CmpScripts\Pages\ManageCmpScripts;
use App\Models\CmpScript;
use App\Models\CmpCategory;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CmpScriptResource extends Resource
{
    protected static ?string $model = CmpScript::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCodeBracket;
    protected static string|\UnitEnum|null $navigationGroup = 'CMP (Cookies)';
    protected static ?int $navigationSort = 30;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'Script';
    protected static ?string $pluralModelLabel = 'Scripts';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('tenant_key')->label('Tenant')->default('bouwsteenwinkel')->required()->maxLength(64),
            TextInput::make('name')
                ->label('Naam')
                ->required()
                ->maxLength(128)
                ->placeholder('Google Analytics 4 / Meta Pixel / etc.'),
            Select::make('category_key')
                ->label('Categorie')
                ->required()
                ->options(fn () => CmpCategory::query()
                    ->orderBy('sort_order')
                    ->pluck('key', 'key')->all())
                ->helperText('Aan welke consent-categorie is dit script gekoppeld? Script wordt alleen geladen als de categorie is goedgekeurd.'),
            Select::make('script_type')
                ->label('Type')
                ->required()
                ->live()
                ->options(['src' => 'External (URL)', 'inline' => 'Inline (raw JS)'])
                ->default('src'),
            TextInput::make('src_url')
                ->label('Script-URL')
                ->maxLength(512)
                ->placeholder('https://www.googletagmanager.com/gtag/js?id=G-XXXXXXX')
                ->visible(fn ($get) => $get('script_type') === 'src')
                ->requiredIf('script_type', 'src'),
            Textarea::make('inline_code')
                ->label('Inline JS')
                ->rows(8)
                ->visible(fn ($get) => $get('script_type') === 'inline')
                ->requiredIf('script_type', 'inline')
                ->helperText('Raw JavaScript-code. Wordt in een try-catch IIFE uitgevoerd.'),
            KeyValue::make('attributes_json')
                ->label('Extra <script>-attributes')
                ->keyLabel('attribute')
                ->valueLabel('waarde')
                ->helperText('Bv. {"data-domain": "bouwsteenwinkel.nl"} voor Plausible.')
                ->visible(fn ($get) => $get('script_type') === 'src'),
            TextInput::make('sort_order')->label('Volgorde')->numeric()->default(50)->required(),
            Toggle::make('is_enabled')->label('Actief')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant_key')->label('Tenant')->searchable()->sortable()->toggleable(),
                TextColumn::make('name')->label('Naam')->searchable()->sortable(),
                TextColumn::make('category_key')->label('Categorie')->badge()->sortable(),
                TextColumn::make('script_type')->label('Type')->badge()
                    ->color(fn (string $state) => $state === 'src' ? 'info' : 'warning'),
                TextColumn::make('src_url')->label('URL')->limit(50)->toggleable(),
                IconColumn::make('is_enabled')->label('Actief')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('tenant_key')->options(fn () => CmpScript::query()->distinct()->pluck('tenant_key', 'tenant_key')->all()),
                SelectFilter::make('category_key')->options(fn () => CmpCategory::query()->distinct()->pluck('key', 'key')->all()),
                SelectFilter::make('script_type')->options(['src' => 'External', 'inline' => 'Inline']),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageCmpScripts::route('/')];
    }
}
