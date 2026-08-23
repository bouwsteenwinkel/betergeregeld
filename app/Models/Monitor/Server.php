<?php

namespace App\Models\Monitor;

use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A VPS we monitor. The collector on the host authenticates with ingest_token
 * and pushes samples; agent_last_seen_at + last_* are refreshed on every push.
 *
 * @property string $id
 * @property string $name
 * @property string|null $hostname
 * @property string|null $ip_address
 * @property bool $is_active
 * @property string $ingest_token
 * @property Carbon|null $agent_last_seen_at
 * @property float|null $last_cpu_percent
 * @property float|null $last_mem_percent
 * @property float|null $last_disk_percent
 */
class Server extends Model
{
	use HasUuids;

	protected $table = 'monitor_servers';
	protected $keyType = 'string';
	public $incrementing = false;

	protected $fillable = [
		'name',
		'hostname',
		'ip_address',
		'provider',
		'location',
		'is_active',
		'alerts_enabled',
		'ingest_token',
		'notes',
	];

	protected $casts = [
		'is_active' => 'bool',
		'alerts_enabled' => 'bool',
		'agent_last_seen_at' => 'datetime',
		'alerted_at' => 'datetime',
		'trend_alerted_at' => 'datetime',
		'last_cpu_percent' => 'float',
		'last_mem_percent' => 'float',
		'last_disk_percent' => 'float',
	];

	protected static function booted(): void
	{
		static::creating(function (Server $server): void {
			if (empty($server->ingest_token)) {
				$server->ingest_token = Str::random(64);
			}
		});
	}

	public function metrics(): HasMany
	{
		return $this->hasMany(Metric::class);
	}

	public function tenants(): HasMany
	{
		return $this->hasMany(Tenant::class);
	}

	public function checks(): HasMany
	{
		return $this->hasMany(Check::class);
	}

	/**
	 * Uptime op basis van echte HTTP/TCP-checks van deze server. Null als er
	 * geen actieve checks of nog geen resultaten zijn (dan valt de SLA terug
	 * op de heartbeat-meting).
	 */
	public function checkUptimePercent(int $hours = 24): ?float
	{
		$checkIds = $this->checks()->where('is_active', true)->pluck('id');

		if ($checkIds->isEmpty()) {
			return null;
		}

		$query = CheckResult::whereIn('check_id', $checkIds)
			->where('checked_at', '>=', now()->subHours($hours));

		$total = $query->count();

		if ($total === 0) {
			return null;
		}

		$up = (clone $query)->where('status', 'up')->count();

		return round($up / $total * 100, 2);
	}

	/**
	 * online | stale | offline | unknown — derived from heartbeat recency.
	 */
	public function status(): string
	{
		if (! $this->agent_last_seen_at) {
			return 'unknown';
		}

		$age = abs(now()->diffInSeconds($this->agent_last_seen_at));

		return match (true) {
			$age <= (int) config('monitor.online_within') => 'online',
			$age >= (int) config('monitor.offline_after') => 'offline',
			default => 'stale',
		};
	}

	/**
	 * Alert-waardige toestand: offline (heartbeat weg) of disk (schijf vol).
	 * CPU/RAM worden bewust niet gealarmeerd — die pieken te vaak. Een server
	 * die nog nooit contact had (status 'unknown') telt als ok (net toegevoegd).
	 */
	public function currentCondition(): string
	{
		if ($this->status() === 'offline') {
			return 'offline';
		}

		if ($this->last_disk_percent !== null && $this->last_disk_percent >= (int) config('monitor.disk_warn')) {
			return 'disk';
		}

		return 'ok';
	}

	/**
	 * Heartbeat-based uptime over the last $hours: received samples / expected
	 * samples, capped at 100. No separate ping subsystem needed for fase 1.
	 */
	public function uptimePercent(int $hours = 24): float
	{
		$interval = max(1, (int) config('monitor.interval_seconds'));
		$expected = ($hours * 3600) / $interval;

		if ($expected <= 0) {
			return 100.0;
		}

		$received = $this->metrics()
			->where('collected_at', '>=', now()->subHours($hours))
			->count();

		return round(min(100, ($received / $expected) * 100), 2);
	}
}
