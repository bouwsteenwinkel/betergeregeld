<?php

namespace App\Filament\Resources\ToolEvents\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ToolEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tool')
                    ->required(),
                TextInput::make('event')
                    ->required(),
                TextInput::make('session_id')
                    ->required(),
                Textarea::make('meta_json')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
