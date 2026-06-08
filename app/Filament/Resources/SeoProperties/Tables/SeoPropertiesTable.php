<?php

namespace App\Filament\Resources\SeoProperties\Tables;

use App\Models\Seo\SeoProperty;
use App\Services\Seo\GscAccessChecker;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;

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
				Action::make('testGscAccess')
					->label('Toegang testen')
					->icon('heroicon-m-key')
					->color('gray')
					->action(function (SeoProperty $record): void {
						$result = app(GscAccessChecker::class)->check($record);
						$sa = $result['service_account'] ?? 'service-account JSON ontbreekt';

						if ($result['status'] === 'granted') {
							Notification::make()
								->title('GSC-toegang in orde')
								->body($result['message'])
								->success()
								->send();

							return;
						}

						if ($result['status'] === 'pending') {
							$available = ! empty($result['sites'])
								? "\n\nWel toegankelijk voor ons account:\n• " . implode("\n• ", array_keys($result['sites']))
								: "\n\nOns account heeft op dit moment toegang tot geen enkele property.";

							Notification::make()
								->title('Nog geen toegang')
								->body($result['message'] . "\n\nVoeg dit service-account toe in Search Console:\n{$sa}" . $available)
								->warning()
								->persistent()
								->send();

							return;
						}

						Notification::make()
							->title('Fout bij toegangscontrole')
							->body($result['message'])
							->danger()
							->persistent()
							->send();
					}),
				Action::make('importNow')
					->label('Nu importeren')
					->icon('heroicon-m-arrow-down-tray')
					->color('gray')
					->requiresConfirmation()
					->modalHeading('GSC-data nu importeren')
					->modalDescription('Haalt de laatste 30 dagen Search Console-data op voor deze property. Dit kan even duren.')
					->action(function (SeoProperty $record): void {
						$code = Artisan::call('seo:import-gsc', [
							'--property' => $record->id,
							'--days'     => 30,
						]);
						$output = trim(Artisan::output());
						$tail = implode("\n", array_slice(explode("\n", $output), -6));

						$notification = Notification::make()
							->title($code === 0 ? 'Import voltooid' : 'Import met fouten')
							->body($tail !== '' ? $tail : 'Geen output.')
							->persistent();
						$code === 0 ? $notification->success() : $notification->danger();
						$notification->send();
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
