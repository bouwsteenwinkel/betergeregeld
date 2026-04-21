<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('event')
                    ->searchable(),
                TextColumn::make('entity_type')
                    ->searchable(),
                TextColumn::make('entity_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('page_uri')
                    ->searchable(),
                TextColumn::make('ip')
                    ->searchable(),
                TextColumn::make('user_agent')
                    ->searchable(),
                TextColumn::make('session_id')
                    ->searchable(),
                TextColumn::make('meta_hash')
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
