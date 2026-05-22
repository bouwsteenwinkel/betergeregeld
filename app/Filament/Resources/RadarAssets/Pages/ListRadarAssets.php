<?php

namespace App\Filament\Resources\RadarAssets\Pages;

use App\Filament\Resources\RadarAssets\RadarAssetResource;
use App\Jobs\AccessGuard\SyncRadarCatalogJob;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

class ListRadarAssets extends ListRecords
{
	protected static string $resource = RadarAssetResource::class;

	protected function getHeaderActions(): array
	{
		return [
			Action::make('sync_catalog')
				->label('Sync catalog')
				->icon('heroicon-m-arrow-path')
				->color('gray')
				->requiresConfirmation()
				->modalHeading('Refresh software catalog?')
				->modalDescription('Pulls latest-stable + EOL data from wordpress.org and endoflife.date. Takes ~5–10 seconds.')
				->modalSubmitActionLabel('Sync now')
				->action(function (): void {
					try {
						$results = (new SyncRadarCatalogJob())->handle(app(\Illuminate\Http\Client\Factory::class));
						$summary = collect($results)
							->map(fn (int $n, string $name) => "{$name}: {$n}")
							->implode(' · ');
						Notification::make()
							->title('Catalog synced')
							->body($summary ?: 'No catalog rows updated')
							->success()
							->send();
					} catch (Throwable $e) {
						Notification::make()
							->title('Catalog sync failed')
							->body($e->getMessage())
							->danger()
							->send();
					}
				}),
			CreateAction::make(),
		];
	}
}
