<?php

namespace App\Filament\Resources\MonitorServers\Actions;

use App\Models\Monitor\Server;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Rotates a server's ingest_token. The old token stops working immediately, so
 * the collector on the host must be updated with the new value afterwards.
 */
class RegenerateTokenAction
{
	public static function make(?string $name = 'regenerate_token'): Action
	{
		return Action::make($name)
			->label('Token vernieuwen')
			->icon('heroicon-m-arrow-path')
			->color('warning')
			->requiresConfirmation()
			->modalHeading('Ingest-token vernieuwen?')
			->modalDescription('Het huidige token stopt direct met werken. Werk daarna de collector op de VPS bij met het nieuwe token.')
			->modalSubmitActionLabel('Vernieuwen')
			->action(function (Server $record): void {
				$record->forceFill(['ingest_token' => Str::random(64)])->save();

				Notification::make()
					->title('Token vernieuwd')
					->body('Werk de collector op de VPS bij via de Install-actie.')
					->success()
					->send();
			});
	}
}
