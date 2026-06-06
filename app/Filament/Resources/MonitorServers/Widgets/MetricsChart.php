<?php

namespace App\Filament\Resources\MonitorServers\Widgets;

use App\Models\Monitor\Metric;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

/**
 * CPU/RAM/disk-verloop over de laatste 24 uur voor één server. Record-bewust:
 * krijgt $record via getWidgetData() van de edit-pagina. Downsampled naar
 * ~180 punten zodat een volle dag (1440 samples) de grafiek niet verstopt.
 */
class MetricsChart extends ChartWidget
{
	public ?Model $record = null;

	protected ?string $heading = 'CPU / RAM / Disk — laatste 24 uur';

	protected int | string | array $columnSpan = 'full';

	protected function getType(): string
	{
		return 'line';
	}

	protected function getData(): array
	{
		if (! $this->record) {
			return ['datasets' => [], 'labels' => []];
		}

		$rows = Metric::query()
			->where('server_id', $this->record->getKey())
			->where('collected_at', '>=', now()->subDay())
			->orderBy('collected_at')
			->get(['collected_at', 'cpu_percent', 'mem_percent', 'disk_percent']);

		// Downsample naar maximaal ~180 punten.
		$step = (int) max(1, ceil($rows->count() / 180));
		$rows = $rows->values()->filter(fn ($r, $i) => $i % $step === 0)->values();

		return [
			'datasets' => [
				[
					'label' => 'CPU %',
					'data' => $rows->pluck('cpu_percent')->all(),
					'borderColor' => '#f59e0b',
					'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
					'tension' => 0.3,
				],
				[
					'label' => 'RAM %',
					'data' => $rows->pluck('mem_percent')->all(),
					'borderColor' => '#14b8a6',
					'backgroundColor' => 'rgba(20, 184, 166, 0.1)',
					'tension' => 0.3,
				],
				[
					'label' => 'Disk %',
					'data' => $rows->pluck('disk_percent')->all(),
					'borderColor' => '#6366f1',
					'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
					'tension' => 0.3,
				],
			],
			'labels' => $rows->map(fn ($r) => $r->collected_at->format('H:i'))->all(),
		];
	}

	protected function getOptions(): array
	{
		return [
			'scales' => [
				'y' => ['min' => 0, 'max' => 100],
			],
		];
	}
}
