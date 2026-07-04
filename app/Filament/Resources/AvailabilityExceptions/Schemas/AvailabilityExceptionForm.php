<?php

namespace App\Filament\Resources\AvailabilityExceptions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class AvailabilityExceptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->required(),
                Select::make('kind')
                    ->options(['block' => 'Block', 'extra' => 'Extra'])
                    ->default('block')
                    ->required(),
                TimePicker::make('start_time'),
                TimePicker::make('end_time'),
                TextInput::make('note')
                    ->default(null),
            ]);
    }
}
