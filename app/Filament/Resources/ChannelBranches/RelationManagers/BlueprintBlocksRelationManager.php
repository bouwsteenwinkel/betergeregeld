<?php

namespace App\Filament\Resources\ChannelBranches\RelationManagers;

use App\Models\Channel\Block;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Standaard-blokkenlijst van een branche. Sleep de rijen om de volgorde te
 * bepalen — dat wordt direct opgeslagen (relatie). Hieruit wordt de basis van
 * een nieuwe site gegenereerd.
 */
class BlueprintBlocksRelationManager extends RelationManager
{
    protected static string $relationship = 'blueprintBlocks';

    protected static ?string $title = 'Blueprint — standaard-blokkenlijst';

    protected static string|\BackedEnum|null $icon = 'heroicon-m-squares-2x2';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Select::make('type')->label('Blok')
                ->options(Block::TYPES)->required()->live()
                ->disabled(fn ($record) => $record?->locked),
            Select::make('status')->label('Start-status')
                ->options(Block::STATUSES)->default('placeholder')->required(),
            Toggle::make('locked')->label('Funnel (vast)')
                ->helperText('Funnel-blokken (lead-wizard) worden automatisch vergrendeld.')
                ->disabled()->dehydrated(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                \Filament\Tables\Columns\ViewColumn::make('card')->label('Blok')
                    ->view('filament.columns.block-card'),
            ])
            ->headerActions([
                CreateAction::make()->label('Blok toevoegen')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['locked'] = in_array($data['type'] ?? '', Block::FUNNEL_TYPES, true);
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->hidden(fn ($record) => $record->locked),
            ]);
    }
}
