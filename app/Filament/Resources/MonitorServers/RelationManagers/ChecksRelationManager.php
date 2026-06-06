<?php

namespace App\Filament\Resources\MonitorServers\RelationManagers;

use App\Models\Monitor\Check;
use App\Services\Monitor\CheckRunner;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChecksRelationManager extends RelationManager
{
	protected static string $relationship = 'checks';

	protected static ?string $title = 'Uptime-checks';

	protected static string|\BackedEnum|null $icon = 'heroicon-m-signal';

	public function form(Schema $schema): Schema
	{
		return $schema
			->columns(2)
			->components([
				TextInput::make('name')
					->required()
					->maxLength(120)
					->placeholder('Website bereikbaar'),
				Select::make('type')
					->options(['http' => 'HTTP', 'tcp' => 'TCP'])
					->default('http')
					->required(),
				TextInput::make('target')
					->required()
					->maxLength(500)
					->columnSpanFull()
					->helperText('HTTP: volledige URL (https://…) · TCP: host:port'),
				TextInput::make('expected_code')
					->label('Verwachte HTTP-code')
					->numeric()
					->placeholder('leeg = elke 2xx/3xx is up')
					->helperText('Alleen voor HTTP-checks.'),
				TextInput::make('timeout_seconds')
					->label('Timeout (s)')
					->numeric()
					->default(10)
					->required(),
				Toggle::make('is_active')
					->label('Actief')
					->default(true),
			]);
	}

	public function table(Table $table): Table
	{
		return $table
			->recordTitleAttribute('name')
			->columns([
				TextColumn::make('name')
					->weight('bold')
					->description(fn (Check $r) => $r->target),
				TextColumn::make('type')
					->badge()
					->formatStateUsing(fn (string $state) => strtoupper($state)),
				TextColumn::make('last_status')
					->label('Status')
					->badge()
					->formatStateUsing(fn (string $state) => match ($state) {
						'up' => 'Up', 'down' => 'Down', default => 'Onbekend',
					})
					->color(fn (string $state) => match ($state) {
						'up' => 'success', 'down' => 'danger', default => 'gray',
					}),
				TextColumn::make('last_code')
					->label('Code')
					->placeholder('—'),
				TextColumn::make('last_latency_ms')
					->label('Latency')
					->formatStateUsing(fn (?int $state) => $state === null ? '—' : "{$state} ms"),
				TextColumn::make('last_checked_at')
					->label('Laatst')
					->since()
					->placeholder('Nooit'),
				IconColumn::make('is_active')
					->label('Actief')
					->boolean(),
			])
			->headerActions([
				CreateAction::make(),
			])
			->recordActions([
				Action::make('check_now')
					->label('Check nu')
					->icon('heroicon-m-bolt')
					->color('primary')
					->action(function (Check $record): void {
						$result = app(CheckRunner::class)->run($record);
						$code = $result->http_code ? " (HTTP {$result->http_code})" : '';

						Notification::make()
							->title("Check: {$result->status}{$code}")
							->body("Latency: {$result->latency_ms} ms" . ($result->error ? " · {$result->error}" : ''))
							->color($result->status === 'up' ? 'success' : 'danger')
							->send();
					}),
				EditAction::make(),
			])
			->toolbarActions([
				BulkActionGroup::make([
					DeleteBulkAction::make(),
				]),
			]);
	}
}
