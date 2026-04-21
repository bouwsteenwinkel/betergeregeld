<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('public_id')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('topic')
                    ->searchable(),
                TextColumn::make('website')
                    ->searchable(),
                TextColumn::make('company')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('cms_platform')
                    ->searchable(),
                TextColumn::make('traffic')
                    ->searchable(),
                TextColumn::make('subject')
                    ->searchable(),
                TextColumn::make('ip')
                    ->searchable(),
                TextColumn::make('user_agent')
                    ->searchable(),
                TextColumn::make('referer')
                    ->searchable(),
                TextColumn::make('page_uri')
                    ->searchable(),
                TextColumn::make('session_id')
                    ->searchable(),
                TextColumn::make('payload_hash')
                    ->searchable(),
                TextColumn::make('user_id')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
