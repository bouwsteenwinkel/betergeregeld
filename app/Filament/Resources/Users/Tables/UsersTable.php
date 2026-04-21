<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
	public static function configure(Table $table): Table
	{
		return $table
			->defaultSort('created_at', 'desc')
			->columns([
				TextColumn::make('email')->label('E-mail')->searchable()->sortable(),
				TextColumn::make('tenant.name')->label('Tenant')->searchable()->sortable()->toggleable(),
				TextColumn::make('role')->badge()->color(fn (string $state) => $state === 'admin' ? 'danger' : 'gray'),
				TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
					'active' => 'success',
					'pending' => 'warning',
					'suspended' => 'danger',
					default => 'gray',
				}),
				IconColumn::make('is_active')->label('Actief')->boolean(),
				TextColumn::make('last_login_at')->label('Laatst ingelogd')->since()->sortable(),
				TextColumn::make('email_verified_at')->label('Geverifieerd')->since()->sortable()->toggleable(),
				TextColumn::make('created_at')->label('Aangemaakt')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
			])
			->filters([
				SelectFilter::make('role')->options(['admin' => 'Admin', 'user' => 'User']),
				SelectFilter::make('status')->options(['pending' => 'Pending', 'active' => 'Active', 'suspended' => 'Suspended']),
				TernaryFilter::make('is_active')->label('Actief'),
				TernaryFilter::make('email_verified_at')
					->label('E-mail geverifieerd')
					->nullable()
					->placeholder('Alle')
					->trueLabel('Geverifieerd')
					->falseLabel('Niet geverifieerd'),
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
