<?php

namespace App\Models\Monitor;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An HTTP/TCP uptime check. Results stream into monitor_check_results; the
 * last_* columns hold the most recent outcome for fast rendering.
 *
 * @property string $id
 * @property string|null $server_id
 * @property string $type
 * @property string $target
 * @property string $last_status
 */
class Check extends Model
{
	use HasUuids;

	protected $table = 'monitor_checks';
	protected $keyType = 'string';
	public $incrementing = false;

	protected $fillable = [
		'server_id',
		'property_id',
		'name',
		'type',
		'target',
		'expected_code',
		'timeout_seconds',
		'is_active',
		'is_demo',
		'alert_state',
		'alerted_at',
	];

	protected $casts = [
		'is_active' => 'bool',
		'is_demo' => 'bool',
		'expected_code' => 'integer',
		'timeout_seconds' => 'integer',
		'last_code' => 'integer',
		'last_latency_ms' => 'integer',
		'last_checked_at' => 'datetime',
		'alerted_at' => 'datetime',
	];

	public function server(): BelongsTo
	{
		return $this->belongsTo(Server::class);
	}

	/** De site (seo_property) waar deze uptime-check bij hoort (null = los/server-check). */
	public function property(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Seo\SeoProperty::class, 'property_id');
	}

	public function results(): HasMany
	{
		return $this->hasMany(CheckResult::class);
	}

	public function uptimePercent(int $hours = 24): ?float
	{
		$query = $this->results()->where('checked_at', '>=', now()->subHours($hours));
		$total = $query->count();

		if ($total === 0) {
			return null;
		}

		$up = (clone $query)->where('status', 'up')->count();

		return round($up / $total * 100, 2);
	}
}
