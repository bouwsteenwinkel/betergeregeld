<?php

namespace App\Filament\Resources\CronMonitors\RelationManagers;

use App\Models\Monitor\CronPing;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Read-only ping-historie van een monitor (laatste binnenkomende signalen).
 */
class PingsRelationManager extends RelationManager
{
	protected static string $relationship = 'pings';

	protected static ?string $title = 'Ping-historie';

	protected static string|\BackedEnum|null $icon = 'heroicon-m-signal';

	public function table(Table $table): Table
	{
		return $table
			->defaultSort('received_at', 'desc')
			->columns([
				TextColumn::make('received_at')
					->label('Tijdstip')
					->dateTime('d-m-Y H:i:s')
					->description(fn (CronPing $r) => $r->received_at?->diffForHumans()),
				TextColumn::make('status')
					->label('Signaal')
					->badge()
					->formatStateUsing(fn (string $state) => match ($state) {
						'success' => 'Succes', 'start' => 'Gestart', 'fail' => 'Fout', default => $state,
					})
					->color(fn (string $state) => match ($state) {
						'success' => 'success', 'fail' => 'danger', 'start' => 'info', default => 'gray',
					}),
				TextColumn::make('exit_code')
					->label('Exit')
					->placeholder('—'),
				TextColumn::make('duration_ms')
					->label('Duur')
					->formatStateUsing(fn (?int $state) => $state === null ? '—' : "{$state} ms"),
				TextColumn::make('message')
					->label('Bericht')
					->placeholder('—')
					->wrap()
					->limit(80),
				TextColumn::make('source_ip')
					->label('Bron-IP')
					->placeholder('—')
					->toggleable(isToggledHiddenByDefault: true),
			]);
	}
}
