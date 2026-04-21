<?php

namespace App\Filament\Resources\ToolEvents\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ToolEventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('tool'),
                TextEntry::make('event'),
                TextEntry::make('session_id'),
                TextEntry::make('meta_json')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }
}
