<?php

namespace App\Filament\Resources\DependencyAdvisories\Pages;

use App\Filament\Resources\DependencyAdvisories\DependencyAdvisoryResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class ListDependencyAdvisories extends ListRecords
{
	protected static string $resource = DependencyAdvisoryResource::class;

	protected function getHeaderActions(): array
	{
		return [
			Action::make('audit')
				->label('Nu auditen')
				->icon('heroicon-m-arrow-path')
				->color('gray')
				->requiresConfirmation()
				->modalDescription('Draait composer/npm audit op de geconfigureerde projecten. Dit kan even duren.')
				->action(function (): void {
					Artisan::call('security:audit-deps');
					$tail = implode("\n", array_slice(explode("\n", trim(Artisan::output())), -4));
					Notification::make()
						->title('Audit voltooid')
						->body($tail !== '' ? $tail : 'Klaar.')
						->success()
						->send();
				}),
		];
	}
}
