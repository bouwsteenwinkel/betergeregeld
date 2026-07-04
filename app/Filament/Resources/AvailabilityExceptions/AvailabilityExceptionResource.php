<?php

namespace App\Filament\Resources\AvailabilityExceptions;

use App\Filament\Resources\AvailabilityExceptions\Pages\CreateAvailabilityException;
use App\Filament\Resources\AvailabilityExceptions\Pages\EditAvailabilityException;
use App\Filament\Resources\AvailabilityExceptions\Pages\ListAvailabilityExceptions;
use App\Filament\Resources\AvailabilityExceptions\Schemas\AvailabilityExceptionForm;
use App\Filament\Resources\AvailabilityExceptions\Tables\AvailabilityExceptionsTable;
use App\Models\AvailabilityException;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AvailabilityExceptionResource extends Resource
{
    protected static ?string $model = AvailabilityException::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNoSymbol;

    protected static string|\UnitEnum|null $navigationGroup = 'Afspraken';

    protected static ?string $navigationLabel = 'Uitzonderingen / blokkades';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return AvailabilityExceptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AvailabilityExceptionsTable::configure($table);
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
            'index' => ListAvailabilityExceptions::route('/'),
            'create' => CreateAvailabilityException::route('/create'),
            'edit' => EditAvailabilityException::route('/{record}/edit'),
        ];
    }
}
