<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('public_id'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('status'),
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('topic')
                    ->placeholder('-'),
                TextEntry::make('website')
                    ->placeholder('-'),
                TextEntry::make('company')
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('cms_platform')
                    ->placeholder('-'),
                TextEntry::make('traffic')
                    ->placeholder('-'),
                TextEntry::make('needs_json')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('subject'),
                TextEntry::make('message')
                    ->columnSpanFull(),
                TextEntry::make('ip'),
                TextEntry::make('user_agent'),
                TextEntry::make('referer')
                    ->placeholder('-'),
                TextEntry::make('page_uri'),
                TextEntry::make('session_id'),
                TextEntry::make('payload_hash'),
                TextEntry::make('payload_json')
                    ->columnSpanFull(),
                TextEntry::make('user_id')
                    ->placeholder('-'),
            ]);
    }
}
