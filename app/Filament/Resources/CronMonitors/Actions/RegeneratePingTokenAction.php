<?php

namespace App\Filament\Resources\CronMonitors\Actions;

use App\Models\Monitor\CronMonitor;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Roteert het ping_token van een monitor. De oude ping-URL werkt direct niet
 * meer, dus werk daarna de cron-job bij met de nieuwe URL.
 */
class RegeneratePingTokenAction
{
	public static function make(?string $name = 'regenerate_ping_token'): Action
	{
		return Action::make($name)
			->label('URL vernieuwen')
			->icon('heroicon-m-arrow-path')
			->color('warning')
			->requiresConfirmation()
			->modalHeading('Ping-URL vernieuwen?')
			->modalDescription('De huidige ping-URL stopt direct met werken. Werk daarna de cron-job bij met de nieuwe URL.')
			->modalSubmitActionLabel('Vernieuwen')
			->action(function (CronMonitor $record): void {
				$record->forceFill(['ping_token' => Str::random(40)])->save();

				Notification::make()
					->title('Ping-URL vernieuwd')
					->body('Werk de cron-job bij via de "Ping-URL"-actie.')
					->success()
					->send();
			});
	}
}
