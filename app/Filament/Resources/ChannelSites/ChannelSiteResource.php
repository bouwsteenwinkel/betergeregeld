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

                IconColumn::make('sslDone')->label('SSL')->boolean()
                    ->tooltip('Plesk-alias + Let\'s Encrypt gedaan')
                    ->state(fn (Site $r) => filled(data_get($r->meta, 'plesk_provisioned_at'))),
                IconColumn::make('searchDone')->label('Search')->boolean()
                    ->tooltip('Google Search Console ingeregeld')
                    ->state(fn (Site $r) => filled(data_get($r->meta, 'gsc_provisioned_at'))),

                TextColumn::make('blocks_count')->label('Blokken')->counts('blocks')->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Gewijzigd')->since()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(['draft' => 'Concept', 'live' => 'Live']),
                SelectFilter::make('channel_branche_id')->label('Branche')
                    ->relationship('branche', 'name'),
                SelectFilter::make('lead_branche')->label('Lead branche')
                    ->options(\App\Models\WebsiteLead::BRANCHES)
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query, array $data) => $query->when(
                        $data['value'] ?? null,
                        fn (\Illuminate\Database\Eloquent\Builder $q, $v) => $q->whereHas(
                            'branche',
                            fn (\Illuminate\Database\Eloquent\Builder $b) => $b->where('lead_branche', $v),
                        ),
                    )),
                SelectFilter::make('hero_filled')->label('Hero-beelden')
                    ->options([
                        'vol'   => 'Ja — compleet (5/5)',
                        'deels' => 'Deels (1–4/5)',
                        'leeg'  => 'Nee — leeg (0/5)',
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        $v = $data['value'] ?? null;
                        if ($v === null || $v === '') {
                            return $query;
                        }
                        $ids = \App\Models\Channel\Site::all(['id', 'key'])
                            ->filter(fn (\App\Models\Channel\Site $s) => match ($v) {
                                'vol'   => $s->heroImageCount() >= 5,
                                'deels' => $s->heroImageCount() > 0 && $s->heroImageCount() < 5,
                                'leeg'  => $s->heroImageCount() === 0,
                                default => false,
                            })->pluck('id')->all();

                        return $query->whereIn('id', $ids);
                    }),
            ])
            // Filter/zoek/sortering in de sessie bewaren, zodat je na een Edit
            // terugkomt op het overzicht mét je ingestelde filter (bv. lead-branche).
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession()
            ->recordActions([
                \Filament\Actions\Action::make('provisionGsc')
                    ->label('Search')
                    ->icon('heroicon-m-magnifying-glass')
                    ->color('gray')
                    ->visible(fn (Site $r) => filled($r->domain))
                    ->requiresConfirmation()
                    ->modalHeading('Google Search Console inregelen')
                    ->modalDescription(fn (Site $r) => "Verifieert {$r->domain} via een DNS-TXT-record (OpenProvider), voegt de property toe in Search Console en dient de sitemap in. Alleen zinvol voor een live, bereikbaar domein waarvan de DNS in OpenProvider staat.")
                    ->modalSubmitActionLabel('Ja, inregelen')
                    ->action(function (Site $r): void {
                        try {
                            $res = app(\App\Services\Seo\GscProvisioner::class)->provision($r);
                            $body = collect($res['steps'])->map(fn ($v, $k) => "• {$k}: {$v}")->implode("\n");
                            $n = \Filament\Notifications\Notification::make()
                                ->title($res['ok'] ? 'Search Console ingeregeld' : 'Nog niet compleet')
                                ->body($body);
                            ($res['ok'] ? $n->success() : $n->warning())->persistent()->send();
                        } catch (\Throwable $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Inregelen mislukt')->body($e->getMessage())
                                ->danger()->persistent()->send();
                        }
                    }),
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
