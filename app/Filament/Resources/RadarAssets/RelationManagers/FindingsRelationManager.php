<?php

namespace App\Filament\Resources\RadarAssets\RelationManagers;

use App\Models\AccessGuard\Radar\Finding;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FindingsRelationManager extends RelationManager
{
	protected static string $relationship = 'findings';

	protected static ?string $title = 'Findings';

	protected static string|\BackedEnum|null $icon = 'heroicon-m-shield-exclamation';

	public function form(Schema $schema): Schema
	{
		// Findings are matcher output, not user-entered. Status changes
		// happen via row actions below.
		return $schema->components([]);
	}

	public function table(Table $table): Table
	{
		return $table
			->recordTitleAttribute('id')
			->defaultSort('risk_score', 'desc')
			->columns([
				TextColumn::make('check_type')
					->label('Type')
					->badge()
					->formatStateUsing(fn (?string $state) => $state ?: 'cve')
					->color(fn (?string $state) => match ($state) {
						'security_headers' => 'warning',
						'tls'              => 'info',
						'cookies', 'cmp'   => 'gray',
						default            => 'danger',
					})
					->sortable(),
				TextColumn::make('title')
					->label('Bevinding')
					->searchable()
					->weight('bold')
					->wrap()
					->placeholder('—')
					->description(fn (Finding $r) => $r->detail
						?: ($r->vulnerability?->cve_id
							? $r->vulnerability->cve_id . ' — ' . ($r->softwareInstance?->product ?? 'unknown')
							: $r->finding_key)),
				TextColumn::make('severity')
					->badge()
					->color(fn (string $state) => match ($state) {
						'critical' => 'danger',
						'high'     => 'warning',
						'medium'   => 'gray',
						'low'      => 'success',
						default    => 'gray',
					}),
				TextColumn::make('risk_score')
					->label('Score')
					->numeric()
					->sortable(),
				TextColumn::make('vulnerability.cisa_kev')
					->label('KEV')
					->badge()
					->formatStateUsing(fn ($state) => $state ? 'Actively exploited' : '—')
					->color(fn ($state) => $state ? 'danger' : 'gray')
					->toggleable(),
				TextColumn::make('vulnerability.cvss_score')
					->label('CVSS')
					->placeholder('—')
					->toggleable(),
				TextColumn::make('status')
					->badge()
					->color(fn (string $state) => match ($state) {
						'new', 'reopened'     => 'danger',
						'confirmed', 'in_progress', 'planned' => 'warning',
						'patched', 'mitigated', 'resolved'    => 'success',
						'false_positive', 'ignored', 'accepted_risk' => 'gray',
						default               => 'gray',
					}),
				TextColumn::make('first_detected_at')
					->dateTime('d-m-Y H:i')
					->since()
					->toggleable(),
				TextColumn::make('last_detected_at')
					->dateTime('d-m-Y H:i')
					->since(),
			])
			->filters([
				SelectFilter::make('check_type')
					->label('Type')
					->options(array_combine(Finding::CHECK_TYPES, Finding::CHECK_TYPES)),
				SelectFilter::make('severity')->options([
					'critical' => 'Critical',
					'high' => 'High',
					'medium' => 'Medium',
					'low' => 'Low',
				]),
				SelectFilter::make('status')->options(array_combine(Finding::STATUSES, Finding::STATUSES))
					->default('new'),
			])
			->headerActions([])
			->recordActions([
				Action::make('mark_patched')
					->label('Patched')
					->icon('heroicon-m-check-circle')
					->color('success')
					->visible(fn (Finding $r) => ! in_array($r->status, ['patched', 'resolved', 'false_positive'], true))
					->action(function (Finding $record): void {
						$record->update(['status' => 'patched', 'resolved_at' => now()]);
						Notification::make()->title('Marked as patched')->success()->send();
					}),
				Action::make('mark_false_positive')
					->label('False positive')
					->icon('heroicon-m-x-circle')
					->color('gray')
					->requiresConfirmation()
					->modalDescription('Verbergt deze bevinding totdat een nieuwe scan hem opnieuw oppikt met een hogere confidence.')
					->visible(fn (Finding $r) => $r->status !== 'false_positive')
					->action(function (Finding $record): void {
						$record->update(['status' => 'false_positive', 'resolved_at' => now()]);
						Notification::make()->title('Marked as false positive')->success()->send();
					}),
				Action::make('accept_risk')
					->label('Accept risk')
					->icon('heroicon-m-hand-raised')
					->color('warning')
					->requiresConfirmation()
					->modalDescription('Markeert de bevinding als bewust geaccepteerd risico. Verschijnt nog wel in audit logs.')
					->visible(fn (Finding $r) => $r->status !== 'accepted_risk')
					->action(function (Finding $record): void {
						$record->update(['status' => 'accepted_risk', 'accepted_until' => now()->addDays(90)]);
						Notification::make()->title('Risk accepted for 90 days')->success()->send();
					}),
			])
			->toolbarActions([
				BulkActionGroup::make([]),
			]);
	}
}
