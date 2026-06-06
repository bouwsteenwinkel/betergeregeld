<?php

namespace App\Filament\Resources\CronMonitors\Tables;

use App\Filament\Resources\CronMonitors\Actions\PingUrlAction;
use App\Filament\Resources\CronMonitors\Actions\RegeneratePingTokenAction;
use App\Models\Monitor\CronMonitor;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class CronMonitorsTable
{
	public static function configure(Table $table): Table
	{
		return $table
			->defaultSort('name')
			->poll('30s')
			->groups([
				Group::make('tenant.name')
					->label('Tenant')
					->getTitleFromRecordUsing(fn (CronMonitor $r) => $r->tenant?->name ?? 'Platform / gedeeld'),
				Group::make('website')
					->label('Website')
					->getTitleFromRecordUsing(fn (CronMonitor $r) => $r->website ?: 'Gedeeld'),
			])
			->columns([
				TextColumn::make('name')
					->label('Cron-job')
					->searchable()
					->weight('bold')
					->description(fn (CronMonitor $r) => trim(($r->tenant?->name ?? 'Platform')
						. ($r->website ? ' · ' . $r->website : ''))),

				TextColumn::make('condition')
					->label('Status')
					->state(fn (CronMonitor $r) => $r->currentCondition())
					->badge()
					->formatStateUsing(fn (string $state) => match ($state) {
						'ok'     => 'OK',
						'late'   => 'Te laat',
						'failed' => 'Fout',
						default  => $state,
					})
					->color(fn (string $state) => match ($state) {
						'ok'     => 'success',
						'late'   => 'warning',
						'failed' => 'danger',
						default  => 'gray',
					})
					->icon(fn (string $state) => match ($state) {
						'ok'     => 'heroicon-m-check-circle',
						'late'   => 'heroicon-m-clock',
						'failed' => 'heroicon-m-x-circle',
						default  => 'heroicon-m-question-mark-circle',
					}),

				TextColumn::make('schedule')
					->label('Cadans')
					->state(fn (CronMonitor $r) => self::humanPeriod($r->expected_period_minutes))
					->description(fn (CronMonitor $r) => "+{$r->grace_minutes} min speling")
					->color('gray'),

				TextColumn::make('last_ping_at')
					->label('Laatste succes')
					->since()
					->placeholder('Nooit')
					->sortable(),

				TextColumn::make('last_status')
					->label('Laatste signaal')
					->badge()
					->formatStateUsing(fn (?string $state) => match ($state) {
						'success' => 'Succes',
						'start'   => 'Gestart',
						'fail'    => 'Fout',
						default   => '—',
					})
					->color(fn (?string $state) => match ($state) {
						'success' => 'success',
						'fail'    => 'danger',
						'start'   => 'info',
						default   => 'gray',
					})
					->toggleable(),

				IconColumn::make('alerts_enabled')
					->label('Alerts')
					->boolean()
					->toggleable(),

				IconColumn::make('is_active')
					->label('Actief')
					->boolean()
					->toggleable(),

				TextColumn::make('ping_token')
					->label('Token')
					->copyable()
					->formatStateUsing(fn (string $state) => substr($state, 0, 8) . '…')
					->toggleable(isToggledHiddenByDefault: true),
			])
			->recordActions([
				PingUrlAction::make(),
				RegeneratePingTokenAction::make(),
				EditAction::make(),
			])
			->toolbarActions([
				BulkActionGroup::make([
					DeleteBulkAction::make(),
				]),
			]);
	}

	private static function humanPeriod(int $minutes): string
	{
		return match (true) {
			$minutes % 10080 === 0 => ($minutes / 10080) . ' week',
			$minutes % 1440 === 0  => ($minutes / 1440 === 1.0) ? 'Dagelijks' : ($minutes / 1440) . ' dagen',
			$minutes % 60 === 0    => ($minutes / 60 === 1.0) ? 'Elk uur' : 'Elke ' . ($minutes / 60) . ' uur',
			default                => "Elke {$minutes} min",
		};
	}
}
