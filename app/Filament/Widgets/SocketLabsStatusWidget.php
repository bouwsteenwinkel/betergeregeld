<?php

namespace App\Filament\Widgets;

use App\Services\Monitor\SocketLabsEvaluator;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Statusoverzicht van de SocketLabs-mailmonitoring: queue, bezorging, klachten
 * en API-bereikbaarheid over het ingestelde venster. Voedt zich uit dezelfde
 * evaluator als de alerting, dus dashboard en alerts blijven consistent.
 */
class SocketLabsStatusWidget extends StatsOverviewWidget
{
	protected function getStats(): array
	{
		$eval = app(SocketLabsEvaluator::class);

		if (! $eval->isActive()) {
			return [
				Stat::make('SocketLabs', 'Niet geconfigureerd')
					->description('Stel SOCKETLABS_* in en registreer de webhook')
					->color('gray'),
			];
		}

		$c = $eval->conditions();
		$n = $c['counts'];
		$color = fn (string $s) => $s === 'alert' ? 'danger' : 'success';
		$label = fn (string $s) => $s === 'alert' ? 'ALERT' : 'OK';

		return [
			Stat::make('Queue', $label($c['queue']))
				->description("Deferred {$n['Deferred']} · Queued {$n['Queued']} (laatste {$c['window']} min)")
				->color($color($c['queue'])),
			Stat::make('Bezorging', $label($c['failure']))
				->description("Failed {$n['Failed']} ({$c['failure_rate']}%) · Delivered {$n['Delivered']}")
				->color($color($c['failure'])),
			Stat::make('Klachten', $label($c['complaint']))
				->description("Complaint {$n['Complaint']}")
				->color($color($c['complaint'])),
			Stat::make('API-poll', $label($c['api']))
				->description($c['api_checked_at'] ? 'Laatste poll ' . $c['api_checked_at']->diffForHumans() : 'Nog niet gepolld')
				->color($color($c['api'])),
		];
	}
}
