<?php

namespace App\Filament\Resources\RadarAssets\Tables;

use App\Filament\Resources\RadarAssets\Actions\ScanHeadersAction;
use App\Filament\Resources\RadarAssets\Actions\ScanNowAction;
use App\Models\AccessGuard\Radar\Asset;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RadarAssetsTable
{
	public static function configure(Table $table): Table
	{
		return $table
			->defaultSort('updated_at', 'desc')
			->columns([
				TextColumn::make('name')
					->searchable()
					->weight('bold')
					->description(fn (Asset $r) => $r->url ?: $r->hostname),
				TextColumn::make('kind')
					->badge()
					->sortable(),
				TextColumn::make('product')
					->label('Product')
					->placeholder('—')
					->description(fn (Asset $r) => $r->vendor)
					->toggleable(),
				TextColumn::make('current_version')
					->label('Version')
					->placeholder('—')
					->toggleable(),
				TextColumn::make('environment')
					->badge()
					->color(fn (string $state) => match ($state) {
						'production' => 'danger',
						'staging' => 'warning',
						'test' => 'gray',
						default => 'gray',
					}),
				TextColumn::make('is_public')
					->label('Public')
					->badge()
					->color(fn (string $state) => match ($state) { 'yes' => 'danger', 'no' => 'success', default => 'gray' })
					->toggleable(),
				TextColumn::make('criticality')
					->badge()
					->color(fn (string $state) => match ($state) {
						'critical' => 'danger', 'high' => 'warning',
						'medium' => 'gray', 'low' => 'success', default => 'gray',
					}),
				TextColumn::make('status')
					->badge()
					->color(fn (string $state) => match ($state) {
						'active' => 'success', 'ignored' => 'gray',
						'deleted' => 'danger', default => 'warning',
					}),
				TextColumn::make('last_scanned_at')
					->label('Last scan')
					->since()
					->placeholder('Never')
					->sortable(),
				TextColumn::make('tenant_id')
					->label('Tenant')
					->formatStateUsing(fn (?string $state) => $state ? substr($state, 0, 8) : '—')
					->toggleable(isToggledHiddenByDefault: true),
			])
			->filters([
				SelectFilter::make('kind')->options(self::pairs(Asset::KINDS)),
				SelectFilter::make('environment')->options(self::pairs(Asset::ENVIRONMENTS)),
				SelectFilter::make('criticality')->options(self::pairs(Asset::CRITICALITY)),
				SelectFilter::make('status')->options(self::pairs(Asset::STATUSES))->default('active'),
			])
			->recordActions([
				ScanNowAction::make(),
				ScanHeadersAction::make(),
				ViewAction::make(),
				EditAction::make(),
			])
			->toolbarActions([
				BulkActionGroup::make([
					DeleteBulkAction::make(),
				]),
			]);
	}

	/**
	 * @param  string[]  $values
	 * @return array<string,string>
	 */
	private static function pairs(array $values): array
	{
		return array_combine($values, array_map(
			fn (string $v) => ucfirst(str_replace('_', ' ', $v)),
			$values,
		));
	}
}
