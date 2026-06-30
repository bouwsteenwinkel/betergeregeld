<?php

namespace App\Filament\Resources\ChannelBranches;

use App\Filament\Resources\ChannelBranches\Pages\CreateChannelBranche;
use App\Filament\Resources\ChannelBranches\Pages\EditChannelBranche;
use App\Filament\Resources\ChannelBranches\Pages\ListChannelBranches;
use App\Models\Channel\Block;
use App\Models\Channel\Branche;
use App\Models\WebsiteLead;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChannelBrancheResource extends Resource
{
    protected static ?string $model = Branche::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static string|\UnitEnum|null $navigationGroup = 'Channel-sites';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Branche';

    protected static ?string $pluralModelLabel = 'Branches';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Branche')
                ->columns(2)
                ->schema([
                    TextInput::make('key')->label('Sleutel')->required()
                        ->alphaDash()->unique(ignoreRecord: true)
                        ->helperText('Technische key, bv. "kapper". Niet meer wijzigen na gebruik.'),
                    TextInput::make('name')->label('Naam')->required(),
                    Select::make('lead_branche')->label('Lead-branche')
                        ->options(WebsiteLead::BRANCHES)->searchable()
                        ->helperText('Waarmee binnenkomende leads getagd worden.'),
                    Toggle::make('active')->label('Actief')->default(true),
                ]),

            Section::make('Standaard-thema')
                ->description('Kleur/font-tokens die een nieuwe site van deze branche standaard krijgt.')
                ->collapsed()
                ->schema([
                    KeyValue::make('theme')->label('')
                        ->keyLabel('Token')->valueLabel('Waarde')
                        ->helperText('primary, accent, ink, muted, bg, surface, font, font_url, radius'),
                ]),

            Section::make('Blueprint — standaard-blokkenlijst')
                ->description('De standaard-blokken beheer je hieronder (na opslaan): sleep ze in volgorde — dat wordt direct opgeslagen.')
                ->visible(fn (?Branche $record) => $record !== null)
                ->schema([]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Branche')->weight('bold')
                    ->description(fn (Branche $r) => $r->key),
                TextColumn::make('lead_branche')->label('Lead-branche')
                    ->formatStateUsing(fn (?string $state) => WebsiteLead::BRANCHES[$state] ?? $state)->badge(),
                TextColumn::make('blueprint')->label('Blokken')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) : 0),
                TextColumn::make('sites_count')->label('Sites')->counts('sites')->badge(),
                IconColumn::make('active')->label('Actief')->boolean(),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [\App\Filament\Resources\ChannelBranches\RelationManagers\BlueprintBlocksRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListChannelBranches::route('/'),
            'create' => CreateChannelBranche::route('/create'),
            'edit'   => EditChannelBranche::route('/{record}/edit'),
        ];
    }
}
