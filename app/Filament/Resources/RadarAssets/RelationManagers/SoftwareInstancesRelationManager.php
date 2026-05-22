<?php

namespace App\Filament\Resources\RadarAssets\RelationManagers;

use App\Models\AccessGuard\Radar\SoftwareInstance;
use App\Services\AccessGuard\Radar\Freshness\FreshnessCalculator;
use App\Services\AccessGuard\Radar\Freshness\FreshnessStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SoftwareInstancesRelationManager extends RelationManager
{
	protected static string $relationship = 'softwareInstances';

	protected static ?string $title = 'Detected software';

	protected static string|\BackedEnum|null $icon = 'heroicon-m-cube';

	public function form(Schema $schema): Schema
	{
		// Software is detected, not entered by hand. Empty schema keeps
		// the relation manager from rendering create/edit modals.
		return $schema->components([]);
	}

	public function table(Table $table): Table
	{
		return $table
			->recordTitleAttribute('product')
			->defaultSort('last_seen_at', 'desc')
			->columns([
				TextColumn::make('vendor')->sortable(),
				TextColumn::make('product')->searchable()->weight('bold'),
				TextColumn::make('version')
					->placeholder('unknown')
					->badge()
					->color(fn (?string $state) => $state ? 'gray' : 'warning'),
				TextColumn::make('freshness')
					->label('Freshness')
					->badge()
					->state(fn (SoftwareInstance $record) => app(FreshnessCalculator::class)->forInstance($record)->status->value)
					->formatStateUsing(fn (string $state) => FreshnessStatus::from($state)->label())
					->color(fn (string $state) => FreshnessStatus::from($state)->color())
					->icon(fn (string $state) => FreshnessStatus::from($state)->icon())
					->tooltip(fn (SoftwareInstance $record) => app(FreshnessCalculator::class)->forInstance($record)->reason),
				TextColumn::make('detection_method')
					->label('Detected via')
					->badge()
					->color(fn (string $state) => match ($state) {
						'wp_json' => 'success',
						'generator_meta' => 'info',
						'http_header' => 'primary',
						'agent' => 'warning',
						default => 'gray',
					}),
				TextColumn::make('path')->placeholder('—')->toggleable(),
				TextColumn::make('first_detected_at')->dateTime('d-m-Y H:i')->since(),
				TextColumn::make('last_seen_at')->dateTime('d-m-Y H:i')->since(),
			])
			->filters([
				SelectFilter::make('detection_method')
					->options(array_combine(
						SoftwareInstance::DETECTION_METHODS,
						SoftwareInstance::DETECTION_METHODS,
					)),
			])
			->headerActions([])
			->recordActions([
				DeleteAction::make()
					->modalHeading('Delete detection?')
					->modalDescription('Use this only for false positives — a fresh scan will re-detect real software.'),
			])
			->toolbarActions([
				BulkActionGroup::make([
					DeleteBulkAction::make(),
				]),
			]);
	}
}
