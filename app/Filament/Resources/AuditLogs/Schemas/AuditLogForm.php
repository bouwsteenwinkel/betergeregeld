<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AuditLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('event')
                    ->required(),
                TextInput::make('entity_type')
                    ->default(null),
                TextInput::make('entity_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('page_uri')
                    ->required(),
                TextInput::make('ip')
                    ->required(),
                TextInput::make('user_agent')
                    ->required(),
                TextInput::make('session_id')
                    ->required(),
                Textarea::make('meta_json')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('meta_hash')
                    ->required(),
            ]);
    }
}
