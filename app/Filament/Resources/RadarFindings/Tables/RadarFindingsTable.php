<?php

namespace App\Filament\Resources\RadarFindings\Tables;

use App\Models\AccessGuard\Radar\Finding;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RadarFindingsTable
{
	public static function configure(Table $table): Table
	{
		return $table
			->defaultSort('risk_score', 'desc')
			->columns([
				TextColumn::make('asset.name')
					->label('Asset')
					->searchable()
					->weight('bold')
					->description(fn (Finding $record) => $record->asset?->url ?: $record->asset?->hostname),
				TextColumn::make('softwareInstance.product')
					->label('Component')
					->description(fn (Finding $record) => $record->softwareInstance?->version
						? "v{$record->softwareInstance->version}"
						: null),
				TextColumn::make('vulnerability.cve_id')
					->label('CVE')
					->placeholder('—')
					->searchable(),
				TextColumn::make('severity')
					->badge()
					->color(fn (string $state) => match ($state) {
						'critical' => 'danger',
						'high'     => 'warning',
						'medium'   => 'gray',
						'low'      => 'success',
						default    => 'gray',
					}),
				TextColumn::make('risk_score')->label('Score')->numeric()->sortable(),
				TextColumn::make('vulnerability.cisa_kev')
					->label('KEV')
					->badge()
					->formatStateUsing(fn ($state) => $state ? 'Active' : '—')
					->color(fn ($state) => $state ? 'danger' : 'gray'),
				TextColumn::make('status')
					->badge()
					->color(fn (string $state) => match ($state) {
						'new', 'reopened' => 'danger',
						'confirmed', 'in_progress', 'planned' => 'warning',
						'patched', 'mitigated', 'resolved'    => 'success',
						default               => 'gray',
					}),
				TextColumn::make('asset.tenant_id')
					->label('Tenant')
					->formatStateUsing(fn (?string $state) => $state ? substr($state, 0, 8) : '—')
					->toggleable(isToggledHiddenByDefault: true),
				TextColumn::make('first_detected_at')
					->dateTime('d-m-Y H:i')
					->since()
					->toggleable(),
			])
			->filters([
				SelectFilter::make('severity')->options([
					'critical' => 'Critical', 'high' => 'High',
					'medium' => 'Medium', 'low' => 'Low',
				]),
				SelectFilter::make('status')->options(array_combine(Finding::STATUSES, Finding::STATUSES)),
				TernaryFilter::make('vulnerability.cisa_kev')
					->label('CISA KEV (actively exploited)')
					->queries(
						true: fn ($q) => $q->whereHas('vulnerability', fn ($v) => $v->where('cisa_kev', true)),
						false: fn ($q) => $q->whereHas('vulnerability', fn ($v) => $v->where('cisa_kev', false)),
						blank: fn ($q) => $q,
					),
			])
			->recordActions([
				ViewAction::make(),
			]);
	}
}
