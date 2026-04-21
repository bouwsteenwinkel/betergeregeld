<?php

namespace App\Filament\Resources\ToolEvents;

use App\Filament\Resources\ToolEvents\Pages\CreateToolEvent;
use App\Filament\Resources\ToolEvents\Pages\EditToolEvent;
use App\Filament\Resources\ToolEvents\Pages\ListToolEvents;
use App\Filament\Resources\ToolEvents\Pages\ViewToolEvent;
use App\Filament\Resources\ToolEvents\Schemas\ToolEventForm;
use App\Filament\Resources\ToolEvents\Schemas\ToolEventInfolist;
use App\Filament\Resources\ToolEvents\Tables\ToolEventsTable;
use App\Models\ToolEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ToolEventResource extends Resource
{
    protected static ?string $model = ToolEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCursorArrowRays;

    protected static string|\UnitEnum|null $navigationGroup = 'Activiteit';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'tool';

    public static function form(Schema $schema): Schema
    {
        return ToolEventForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ToolEventInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ToolEventsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListToolEvents::route('/'),
            'create' => CreateToolEvent::route('/create'),
            'view' => ViewToolEvent::route('/{record}'),
            'edit' => EditToolEvent::route('/{record}/edit'),
        ];
    }
}
