<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AuditLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('event'),
                TextEntry::make('entity_type')
                    ->placeholder('-'),
                TextEntry::make('entity_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('page_uri'),
                TextEntry::make('ip'),
                TextEntry::make('user_agent'),
                TextEntry::make('session_id'),
                TextEntry::make('meta_json')
                    ->columnSpanFull(),
                TextEntry::make('meta_hash'),
            ]);
    }
}
