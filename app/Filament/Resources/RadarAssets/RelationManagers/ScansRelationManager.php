<?php

namespace App\Filament\Resources\RadarAssets\RelationManagers;

use App\Models\AccessGuard\Radar\Scan;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ScansRelationManager extends RelationManager
{
	protected static string $relationship = 'scans';

	protected static ?string $title = 'Scan history';

	protected static string|\BackedEnum|null $icon = 'heroicon-m-clock';

	public function form(Schema $schema): Schema
	{
		return $schema->components([]);
	}

	public function table(Table $table): Table
	{
		return $table
			->recordTitleAttribute('id')
			->defaultSort('started_at', 'desc')
			->columns([
				TextColumn::make('started_at')
					->dateTime('d-m-Y H:i:s')
					->description(fn (Scan $r) => $r->started_at?->diffForHumans()),
				TextColumn::make('scan_type')->badge(),
				TextColumn::make('status')
					->badge()
					->color(fn (string $state) => match ($state) {
						'success' => 'success',
						'error' => 'danger',
						'running' => 'warning',
						'queued' => 'gray',
						'skipped' => 'gray',
						default => 'gray',
					}),
				TextColumn::make('detections_count')->label('Detections')->numeric(),
				TextColumn::make('findings_count')->label('Findings')->numeric()->placeholder('—'),
				TextColumn::make('finished_at')
					->label('Duration')
					->formatStateUsing(function ($state, Scan $r) {
						if (! $state || ! $r->started_at) return '—';
						$ms = $r->started_at->diffInMilliseconds($state);
						return $ms < 1000 ? "{$ms}ms" : round($ms / 1000, 2) . 's';
					}),
				TextColumn::make('error_message')
					->placeholder('—')
					->limit(60)
					->tooltip(fn (Scan $r) => $r->error_message)
					->toggleable(isToggledHiddenByDefault: true),
			])
			->filters([
				SelectFilter::make('status')->options(array_combine(Scan::STATUSES, Scan::STATUSES)),
				SelectFilter::make('scan_type')->options(array_combine(Scan::TYPES, Scan::TYPES)),
			])
			->headerActions([])
			->recordActions([
				Action::make('view_raw')
					->label('Raw output')
					->icon('heroicon-m-code-bracket')
					->modalHeading('Raw scan output')
					->modalContent(fn (Scan $r) => view('filament.radar.scan-raw', ['scan' => $r]))
					->modalSubmitAction(false)
					->modalCancelActionLabel('Close'),
			]);
	}
}
