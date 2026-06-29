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
                ->description('De blokken die een nieuwe site standaard krijgt (volgorde = hier).')
                ->schema([
                    Repeater::make('blueprint')->label('')
                        ->schema([
                            Select::make('type')->label('Blok')->options(Block::TYPES)->required(),
                            Select::make('status')->label('Start-status')
                                ->options(Block::STATUSES)->default('placeholder'),
                            Toggle::make('locked')->label('Funnel (vast)')->default(false),
                        ])
                        ->columns(3)
                        ->reorderable()->orderColumn()
                        ->defaultItems(0)
                        ->addActionLabel('Blok toevoegen'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Branche')->weight('bold')
                    ->description(fn (Branche $r) => $r->key),
                TextColumn::make('lead_branche')->label('Lead-branche')
                    ->formatStateUsing(fn (?string $s) => WebsiteLead::BRANCHES[$s] ?? $s)->badge(),
                TextColumn::make('blueprint')->label('Blokken')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) : 0),
                TextColumn::make('sites_count')->label('Sites')->counts('sites')->badge(),
                IconColumn::make('active')->label('Actief')->boolean(),
            ])
            ->defaultSort('name');
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
