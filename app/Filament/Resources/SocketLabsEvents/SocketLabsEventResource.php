<?php

namespace App\Filament\Resources\SocketLabsEvents;

use App\Filament\Resources\SocketLabsEvents\Pages\ManageSocketLabsEvents;
use App\Models\Monitor\SocketLabsEvent;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SocketLabsEventResource extends Resource
{
	protected static ?string $model = SocketLabsEvent::class;
	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;
	protected static string|\UnitEnum|null $navigationGroup = 'Monitoring';
	protected static ?int $navigationSort = 70;
	protected static ?string $recordTitleAttribute = 'message_id';
	protected static ?string $modelLabel = 'Mail-event (SocketLabs)';
	protected static ?string $pluralModelLabel = 'SocketLabs e-mail';

	/** Read-only — events komen binnen via de webhook. */
	public static function canCreate(): bool { return false; }
	public static function canEdit($record): bool { return false; }
	public static function canDelete($record): bool { return false; }

	public static function form(Schema $schema): Schema
	{
		return $schema->components([
			TextInput::make('type')->disabled(),
			TextInput::make('occurred_at')->disabled(),
			TextInput::make('to_address')->label('Ontvanger')->disabled(),
			TextInput::make('from_address')->label('Afzender')->disabled(),
			TextInput::make('subject')->label('Onderwerp')->disabled(),
			TextInput::make('failure_type')->disabled(),
			TextInput::make('failure_code')->disabled(),
			TextInput::make('deferral_code')->disabled(),
			Textarea::make('reason')->label('Reden')->disabled()->rows(3),
			TextInput::make('message_id')->disabled(),
		]);
	}

	public static function table(Table $table): Table
	{
		return $table
			->columns([
				TextColumn::make('occurred_at')->label('Tijd')->dateTime('d-m H:i:s')->sortable(),
				TextColumn::make('type')->label('Type')->badge()->sortable()
					->color(fn (string $state) => match ($state) {
						'Delivered' => 'success',
						'Failed', 'Complaint' => 'danger',
						'Deferred' => 'warning',
						default => 'gray',
					}),
				TextColumn::make('to_address')->label('Ontvanger')->searchable()->limit(32),
				TextColumn::make('failure_type')->label('Soort')->toggleable(),
				TextColumn::make('failure_code')->label('Code')->toggleable(),
				TextColumn::make('deferral_code')->label('Deferral')->toggleable(),
				TextColumn::make('reason')->label('Reden')->limit(50)->tooltip(fn ($state) => $state),
				TextColumn::make('subject')->label('Onderwerp')->limit(30)->toggleable(isToggledHiddenByDefault: true),
			])
			->defaultSort('occurred_at', 'desc')
			->filters([
				SelectFilter::make('type')->options([
					'Delivered' => 'Delivered',
					'Failed'    => 'Failed',
					'Deferred'  => 'Deferred',
					'Queued'    => 'Queued',
					'Complaint' => 'Complaint',
				]),
				SelectFilter::make('failure_type')->options([
					'Permanent'  => 'Permanent',
					'Temporary'  => 'Temporary',
					'Suppressed' => 'Suppressed',
				]),
			])
			->recordActions([ViewAction::make()])
			->toolbarActions([]);
	}

	public static function getPages(): array
	{
		return ['index' => ManageSocketLabsEvents::route('/')];
	}
}
