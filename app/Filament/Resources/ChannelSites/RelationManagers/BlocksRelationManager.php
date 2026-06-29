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
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('block_key')
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('type')->label('Type')->badge()
                    ->formatStateUsing(fn (string $s) => Block::TYPES[$s] ?? $s),
                TextColumn::make('block_key')->label('Sleutel')->color('gray'),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (string $s) => Block::STATUSES[$s] ?? $s)
                    ->color(fn (string $s) => match ($s) {
                        'klaar' => 'success', 'bewerking' => 'warning', default => 'gray',
                    }),
                IconColumn::make('enabled')->label('Zichtbaar')->boolean(),
                IconColumn::make('locked')->label('Funnel')->boolean()
                    ->trueIcon('heroicon-m-lock-closed')->falseIcon('heroicon-m-lock-open')
                    ->color(fn (bool $state) => $state ? 'warning' : 'gray'),
            ])
            ->headerActions([
                CreateAction::make()->label('Blok toevoegen')
                    ->mutateDataUsing(function (array $data): array {
                        $data['locked'] = in_array($data['type'] ?? '', Block::FUNNEL_TYPES, true);
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->hidden(fn (Block $record) => $record->locked),
            ]);
    }
}
