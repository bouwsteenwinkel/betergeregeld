<?php

namespace App\Filament\Resources\ChannelSites;

use App\Filament\Resources\ChannelSites\Pages\CreateChannelSite;
use App\Filament\Resources\ChannelSites\Pages\EditChannelSite;
use App\Filament\Resources\ChannelSites\Pages\ListChannelSites;
use App\Filament\Resources\ChannelSites\RelationManagers\BlocksRelationManager;
use App\Models\Channel\Branche;
use App\Models\Channel\Site;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ChannelSiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|\UnitEnum|null $navigationGroup = 'Channel-sites';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Channel-site';

    protected static ?string $pluralModelLabel = 'Channel-sites';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Site')
                ->columns(2)
                ->schema([
                    Select::make('channel_branche_id')->label('Branche')
                        ->relationship('branche', 'name')
                        ->options(Branche::orderBy('name')->pluck('name', 'id'))
                        ->required()
                        ->helperText('Bepaalt de blueprint waaruit de basis wordt gegenereerd.'),
                    TextInput::make('key')->label('Sleutel')->required()
                        ->alphaDash()->unique(ignoreRecord: true)
                        ->helperText('Preview op /_site/<sleutel>. Niet meer wijzigen na live.'),
                    TextInput::make('name')->label('Naam')->required(),
                    TextInput::make('locale')->label('Taal')->default('nl')->maxLength(5),
                ]),

            Section::make('Domein & status')
                ->columns(2)
                ->schema([
                    TextInput::make('domain')->label('Domein')
                        ->placeholder('jouwdomein.nl')
                        ->helperText('Zonder https://. Leeg = nog concept (alleen preview).'),
                    Select::make('status')->label('Status')
                        ->options(['draft' => 'Concept (preview)', 'live' => 'Live (op domein)'])
                        ->default('draft')->required(),
                ]),

            Section::make('Thema (override)')
                ->description('Overschrijft het branche-thema. Leeg = branche-default.')
                ->collapsed()
                ->schema([
                    KeyValue::make('theme')->label('')->keyLabel('Token')->valueLabel('Waarde')
                        ->helperText('primary, accent, ink, muted, bg, surface, font, font_url, radius'),
                ]),

            Section::make('Merk & contact')
                ->collapsed()
                ->schema([
                    KeyValue::make('brand')->label('')->keyLabel('Veld')->valueLabel('Waarde')
                        ->helperText('logo_text, logo_image, phone, email, address, kvk, endorsement, endorsement_url'),
                ]),

            Section::make('Header (site-specifiek)')
                ->description('Eigen menu + knop. Leeg = standaard (Home/Plaatsen/Blog/Over ons + “Gratis voorbeeld”).')
                ->columns(2)
                ->collapsed()
                ->schema([
                    Repeater::make('header.menu')->label('Menu-items')
                        ->schema([
                            TextInput::make('label')->label('Label')->required(),
                            TextInput::make('href')->label('Link')
                                ->placeholder('#galerij · over-ons · https://…')
                                ->helperText('Leeg = home · #anker · pad · volledige URL'),
                        ])
                        ->columns(2)->columnSpanFull()->reorderable()->defaultItems(0)
                        ->addActionLabel('Menu-item toevoegen'),
                    TextInput::make('header.cta.label')->label('Knop-tekst')->placeholder('Gratis voorbeeld'),
                    TextInput::make('header.cta.href')->label('Knop-link')->placeholder('#gratis-voorbeeld'),
                ]),

            Section::make('SEO-meta')
                ->columns(1)
                ->collapsed()
                ->schema([
                    TextInput::make('meta.home_title')->label('Title')->maxLength(255),
                    TextInput::make('meta.home_description')->label('Meta-description')->maxLength(255),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Site')->weight('bold')
                    ->description(fn (Site $r) => '/_site/' . $r->key)->searchable(['name', 'key']),
                TextColumn::make('branche.name')->label('Branche')->badge()->searchable(),
                TextColumn::make('domain')->label('Domein')->placeholder('— concept —')
                    ->url(fn (Site $r) => $r->domain ? 'https://' . $r->domain : null, true)->searchable(),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'live' ? 'Live' : 'Concept')
                    ->color(fn (string $state) => $state === 'live' ? 'success' : 'gray'),

                // ── Gereedheid per site (voortgang van de bespoke build) ──────────
                IconColumn::make('domainRegistered')->label('Domein')->boolean()
                    ->tooltip('Domein geregistreerd bij OpenProvider (via "Domeinstatus verversen")')
                    ->state(fn (Site $r) => $r->domainRegistered()),
                IconColumn::make('dnsOk')->label('DNS')->boolean()
                    ->tooltip('apex + www wijzen naar de VPS')
                    ->state(fn (Site $r) => $r->dnsOk()),
                IconColumn::make('contentPrefilled')->label('Tekst')->boolean()
                    ->tooltip('Website-tekst / blokken ingevuld')
                    ->state(fn (Site $r) => $r->contentPrefilled()),
                IconColumn::make('hasLogo')->label('Logo')->boolean()
                    ->tooltip('Eigen logo aanwezig')
                    ->state(fn (Site $r) => $r->hasLogo()),
                TextColumn::make('heroImages')->label('Hero')->badge()
                    ->tooltip('Hero-beelden (5×): website + webshop/portaal/automatisering/ai')
                    ->state(fn (Site $r) => $r->heroImageCount() . '/5')
                    ->color(fn (Site $r) => match (true) {
                        $r->heroImageCount() >= 5 => 'success',
                        $r->heroImageCount() === 0 => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('outcomeImages')->label('Oplevert')->badge()
                    ->tooltip('“Wat het oplevert”-beelden (5×): de facet-previews')
                    ->state(fn (Site $r) => $r->outcomeImageCount() . '/5')
                    ->color(fn (Site $r) => match (true) {
                        $r->outcomeImageCount() >= 5 => 'success',
                        $r->outcomeImageCount() === 0 => 'gray',
                        default => 'warning',
                    }),

                TextColumn::make('blocks_count')->label('Blokken')->counts('blocks')->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Gewijzigd')->since()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(['draft' => 'Concept', 'live' => 'Live']),
                SelectFilter::make('channel_branche_id')->label('Branche')
                    ->relationship('branche', 'name'),
            ])
            ->recordUrl(fn (Site $r) => EditChannelSite::getUrl(['record' => $r]))
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [BlocksRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListChannelSites::route('/'),
            'create' => CreateChannelSite::route('/create'),
            'edit'   => EditChannelSite::route('/{record}/edit'),
        ];
    }
}
