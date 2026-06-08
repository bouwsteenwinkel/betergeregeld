<?php

namespace App\Filament\Resources\SeoProperties\Tables;

use App\Filament\Actions\GscPropertyActions;
use App\Models\Seo\SeoProperty;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SeoPropertiesTable
{
	public static function configure(Table $table): Table
	{
		return $table
			->defaultSort('label')
			->columns([
				TextColumn::make('label')
					->searchable()
					->weight('bold')
					->description(fn (SeoProperty $r) => $r->site_url),
				TextColumn::make('tenant.name')
					->label('Tenant')
					->placeholder('Platform')
					->badge()
					->color('gray'),
				IconColumn::make('is_active')
					->label('Actief')
					->boolean(),
				TextColumn::make('last_imported_date')
					->label('Laatst geïmporteerd')
					->date('d-m-Y')
					->placeholder('Nooit')
					->sortable(),
				TextColumn::make('freshness_alert_state')
					->label('Bewaking')
					->badge()
					->formatStateUsing(fn (?string $state) => $state === 'stale' ? 'Staat stil' : 'Vers')
					->color(fn (?string $state) => $state === 'stale' ? 'danger' : 'success')
					->icon(fn (?string $state) => $state === 'stale' ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
					->tooltip(fn (SeoProperty $r) => $r->freshness_alerted_at
						? 'Sinds ' . $r->freshness_alerted_at->diffForHumans()
						: 'Nog geen statuswijziging'),
				TextColumn::make('last_import_error')
					->label('Laatste fout')
					->placeholder('—')
					->color('danger')
					->wrap()
					->toggleable(),
			])
			->recordActions([
				GscPropertyActions::testAccess(),
				GscPropertyActions::importNow(),
				EditAction::make(),
			])
			->toolbarActions([
				BulkActionGroup::make([
					DeleteBulkAction::make(),
				]),
			]);
	}
}
