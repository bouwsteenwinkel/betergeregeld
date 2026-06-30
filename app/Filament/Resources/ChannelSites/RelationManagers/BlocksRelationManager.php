<?php

namespace App\Filament\Resources\ChannelSites\RelationManagers;

use App\Filament\Support\BlockContentSchema;
use App\Models\Channel\Block;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BlocksRelationManager extends RelationManager
{
    protected static string $relationship = 'blocks';

    protected static ?string $title = 'Blokken';

    protected static string|\BackedEnum|null $icon = 'heroicon-m-squares-2x2';

    /** Fase-key => label, voor de Fase-select en het filter. */
    public static function facetOptions(): array
    {
        return collect(config('groeidiamant.facets', []))
            ->mapWithKeys(fn ($d, $k) => [$k => trim(($d['nr'] ?? '') . '. ' . ($d['label'] ?? $k), '. ')])
            ->all();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Blok')
                ->columns(2)
                ->schema([
                    Select::make('type')->label('Type')
                        ->options(Block::TYPES)->required()->live()
                        ->disabled(fn (?Block $record) => $record?->locked)
                        ->afterStateUpdated(fn ($state, $set, ?Block $record) => $record ? null : $set('block_key', $state)),
                    Select::make('facet')->label('Fase')
                        ->options(self::facetOptions())
                        ->placeholder('Basis — in elke fase')
                        ->helperText('Leeg = basis (zichtbaar in elke fase). Anders: alleen in deze fase.'),
                    TextInput::make('block_key')->label('Sleutel')->required()
                        ->alphaDash()
                        ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule, $livewire) => $rule->where('channel_site_id', $livewire->getOwnerRecord()->getKey()))
                        ->helperText('Uniek per site — bepaalt de bespoke view-naam.'),
                    Select::make('status')->label('Status')
                        ->options(Block::STATUSES)->default('placeholder')->required(),
                    Toggle::make('enabled')->label('Zichtbaar')->default(true),
                    Textarea::make('design_notes')->label('Design-opdracht (voor designer/Claude)')
                        ->rows(2)->columnSpanFull()
                        ->placeholder('bv. hero groter, foto rechts, warmere tint'),
                ]),

            Section::make('Inhoud')
                ->columns(2)
                ->visible(fn ($get) => filled($get('type')) && ! in_array($get('type'), ['groeipad', 'wizard'], true))
                ->schema(fn ($get) => BlockContentSchema::for((string) $get('type'))),

            Section::make('Fase-varianten (Groeidiamant)')
                ->description('Optioneel: per groeifase afwijkende inhoud. Leeg laten = de inhoud hierboven.')
                ->visible(fn ($get) => filled($get('type')) && ! in_array($get('type'), ['groeipad', 'wizard'], true))
                ->collapsed()
                ->schema(fn ($get) => self::facetSections((string) $get('type'))),
        ]);
    }

    /** Per niet-default Groeidiamant-fase een (collapsed) override-sectie. */
    private static function facetSections(string $type): array
    {
        if ($type === '' || in_array($type, ['groeipad', 'wizard'], true)) {
            return [];
        }
        $default = (string) config('groeidiamant.default', 'website');
        $out = [];
        foreach ((array) config('groeidiamant.facets', []) as $key => $def) {
            if ($key === $default) {
                continue;
            }
            $label = $def['label'] ?? $key;
            $out[] = Section::make(trim(($def['nr'] ?? '') . '. ' . $label, '. '))
                ->description("Overschrijft de inhoud voor de fase “{$label}”. Leeg = standaard.")
                ->columns(2)
                ->collapsed()
                ->schema(BlockContentSchema::for($type, 'content.facets.' . $key));
        }
        return $out;
    }

    /** Verwijdert lege fase-overrides zodat ze de basis-inhoud niet wissen. */
    public static function pruneFacets(array $data): array
    {
        $facets = data_get($data, 'content.facets');
        if (! is_array($facets)) {
            return $data;
        }
        $clean = [];
        foreach ($facets as $fk => $fv) {
            $vals = array_filter((array) $fv, fn ($v) => ! ($v === null || $v === '' || $v === []));
            if ($vals) {
                $clean[$fk] = $vals;
            }
        }
        if ($clean) {
            data_set($data, 'content.facets', $clean);
        } else {
            unset($data['content']['facets']);
        }
        return $data;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('block_key')
            ->description('Elk blok hoort bij een fase. “Basis” = zichtbaar in élke fase. Kies hieronder een fase om alléén die blokken (+ basis) te zien; een nieuw blok dat je dan toevoegt komt automatisch in die fase. Sleep om de volgorde te bepalen.')
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                \Filament\Tables\Columns\ViewColumn::make('card')->label('Blok')
                    ->view('filament.columns.block-card'),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('facet')->label('Toon fase')
                    ->placeholder('Alles (basis + alle fases)')
                    ->options(self::facetOptions())
                    ->query(fn ($query, array $data) => filled($data['value'] ?? null)
                        ? $query->where(fn ($q) => $q->whereNull('facet')->orWhere('facet', $data['value']))
                        : $query),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->headerActions([
                CreateAction::make()->label('Blok toevoegen')
                    ->mutateFormDataUsing(function (array $data, $livewire): array {
                        // Geen fase gekozen in het formulier? Pak de actief gefilterde fase.
                        if (blank($data['facet'] ?? null)) {
                            $active = data_get($livewire, 'tableFilters.facet.value');
                            if (filled($active)) {
                                $data['facet'] = $active;
                            }
                        }
                        $data['locked'] = in_array($data['type'] ?? '', Block::FUNNEL_TYPES, true);
                        return self::pruneFacets($data);
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateFormDataUsing(fn (array $data): array => self::pruneFacets($data)),
                DeleteAction::make()->hidden(fn (Block $record) => $record->locked),
            ]);
    }
}
