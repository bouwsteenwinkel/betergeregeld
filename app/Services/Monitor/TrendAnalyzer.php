<?php

declare(strict_types=1);

namespace App\Services\Monitor;

use App\Models\Monitor\Server;
use Illuminate\Support\Facades\DB;

/**
 * Trendanalyse op monitor_metrics: niet "is de drempel bereikt", maar "wanneer
 * bereiken we hem bij dit tempo".
 *
 * Aanleiding (23-08-2026): de systeemschijf liep in een maand van 105 naar 55 GB
 * vrij zonder één signaal, omdat de drempel op 90% staat en het gebruik op 86,5%
 * zat. De waarschuwing die je wilt hebben is niet "90% bereikt" maar "op dit
 * tempo vol over 34 dagen" — die geeft je weken in plaats van dagen.
 *
 * Werkt op DAGWAARDEN, niet op losse samples. Een minuutmeting springt met
 * gigabytes op en neer (backups die worden weggeschreven en weer opgeruimd) en
 * een rechte lijn door die ruis zegt niets. Per dag één getal dempt dat.
 */
final class TrendAnalyzer
{
	public function __construct(
		private readonly int $terugkijkDagen,
		private readonly int $minimaalDagen,
	) {
	}

	public static function make(): self
	{
		return new self(
			max(3, (int) config('monitor.trend_lookback_days', 14)),
			max(3, (int) config('monitor.trend_min_days', 7)),
		);
	}

	/**
	 * Schijfgroei in GB per dag en het aantal dagen tot vol.
	 *
	 * @return array{per_dag:float,dagen:?float,vrij_gb:float,gebruikt_gb:float,totaal_gb:float,punten:int}|null
	 */
	public function schijf(Server $server): ?array
	{
		// Per dag de LAATSTE meting: dat is de stand aan het eind van die dag.
		$rijen = DB::table('monitor_metrics')
			->selectRaw('DATE(collected_at) AS d')
			->selectRaw('SUBSTRING_INDEX(GROUP_CONCAT(disk_used_gb ORDER BY collected_at DESC), ",", 1) + 0 AS gebruikt')
			->selectRaw('MAX(disk_total_gb) AS totaal')
			->where('server_id', $server->id)
			->where('collected_at', '>=', now()->subDays($this->terugkijkDagen)->startOfDay())
			->whereNotNull('disk_used_gb')
			->groupBy('d')
			->orderBy('d')
			->get();

		if ($rijen->count() < $this->minimaalDagen) {
			return null;
		}

		$perDag = $this->hellingPerDag($rijen->pluck('gebruikt')->map(fn ($v) => (float) $v)->all());
		$laatste = $rijen->last();
		$gebruikt = (float) $laatste->gebruikt;
		$totaal = (float) $laatste->totaal;
		$vrij = max(0.0, $totaal - $gebruikt);

		return [
			'per_dag'     => round($perDag, 3),
			'dagen'       => $perDag > 0.0 ? round($vrij / $perDag, 1) : null,
			'vrij_gb'     => round($vrij, 1),
			'gebruikt_gb' => round($gebruikt, 1),
			'totaal_gb'   => round($totaal, 1),
			'punten'      => $rijen->count(),
		];
	}

	/**
	 * Geheugengroei in procentpunt per dag en het aantal dagen tot de grens.
	 *
	 * Hier het DAGGEMIDDELDE, niet de laatste meting: geheugen schommelt binnen
	 * een dag tientallen procenten mee met het verkeer. Wat je wilt zien is of de
	 * bodem omhoog kruipt — dat is de handtekening van een lek, en dat is precies
	 * wat een drempel op 90% je pas vertelt als het te laat is.
	 *
	 * @return array{per_dag:float,dagen:?float,nu_pct:float,grens_pct:int,punten:int}|null
	 */
	public function geheugen(Server $server): ?array
	{
		$rijen = DB::table('monitor_metrics')
			->selectRaw('DATE(collected_at) AS d, AVG(mem_percent) AS pct')
			->where('server_id', $server->id)
			->where('collected_at', '>=', now()->subDays($this->terugkijkDagen)->startOfDay())
			->whereNotNull('mem_percent')
			->groupBy('d')
			->orderBy('d')
			->get();

		if ($rijen->count() < $this->minimaalDagen) {
			return null;
		}

		$perDag = $this->hellingPerDag($rijen->pluck('pct')->map(fn ($v) => (float) $v)->all());
		$nu = (float) $rijen->last()->pct;
		$grens = (int) config('monitor.mem_full_percent', 95);

		return [
			'per_dag'   => round($perDag, 3),
			'dagen'     => ($perDag > 0.0 && $nu < $grens) ? round(($grens - $nu) / $perDag, 1) : null,
			'nu_pct'    => round($nu, 1),
			'grens_pct' => $grens,
			'punten'    => $rijen->count(),
		];
	}

	/**
	 * Kleinste-kwadraten helling over opeenvolgende dagwaarden.
	 *
	 * Bewust niet "laatste min eerste gedeeld door dagen": één uitschieter aan
	 * een van beide uiteinden bepaalt dan de hele uitkomst. Op 15-08 sprong de
	 * schijf 7 GB omhoog en een dag later weer terug; met twee punten was dat
	 * een alarm geweest, met een fit door alle dagen niet.
	 *
	 * @param  list<float>  $waarden  één per dag, op volgorde
	 */
	private function hellingPerDag(array $waarden): float
	{
		$n = count($waarden);
		if ($n < 2) {
			return 0.0;
		}

		$gemX = ($n - 1) / 2.0;
		$gemY = array_sum($waarden) / $n;

		$teller = 0.0;
		$noemer = 0.0;
		foreach ($waarden as $i => $y) {
			$dx = $i - $gemX;
			$teller += $dx * ($y - $gemY);
			$noemer += $dx * $dx;
		}

		return $noemer > 0.0 ? $teller / $noemer : 0.0;
	}
}
