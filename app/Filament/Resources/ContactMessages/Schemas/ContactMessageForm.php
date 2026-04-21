<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('public_id')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('new'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('topic')
                    ->default(null),
                TextInput::make('website')
                    ->url()
                    ->default(null),
                TextInput::make('company')
                    ->default(null),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                TextInput::make('cms_platform')
                    ->default(null),
                TextInput::make('traffic')
                    ->default(null),
                Textarea::make('needs_json')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('subject')
                    ->required(),
                Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('ip')
                    ->required(),
                TextInput::make('user_agent')
                    ->required(),
                TextInput::make('referer')
                    ->default(null),
                TextInput::make('page_uri')
                    ->required(),
                TextInput::make('session_id')
                    ->required(),
                TextInput::make('payload_hash')
                    ->required(),
                Textarea::make('payload_json')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('user_id')
                    ->default(null),
            ]);
    }
}
