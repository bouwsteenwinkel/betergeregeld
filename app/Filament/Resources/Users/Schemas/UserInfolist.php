<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('tenant_id'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('role'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('last_login_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('status'),
                TextEntry::make('email_verified_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
