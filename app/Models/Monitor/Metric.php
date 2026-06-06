<?php

namespace App\Models\Monitor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One time-series resource sample pushed by a host collector. Append-only;
 * uses a single created_at (no updated_at), so timestamps are managed manually.
 *
 * @property int $id
 * @property string $server_id
 * @property \Carbon\Carbon $collected_at
 */
class Metric extends Model
{
	protected $table = 'monitor_metrics';

	public $timestamps = false;

	protected $fillable = [
		'server_id',
		'collected_at',
		'cpu_percent',
		'mem_used_mb',
		'mem_total_mb',
		'mem_percent',
		'disk_used_gb',
		'disk_total_gb',
		'disk_percent',
		'uptime_seconds',
		'load_json',
	];

	protected $casts = [
		'collected_at' => 'datetime',
		'created_at' => 'datetime',
		'cpu_percent' => 'float',
		'mem_used_mb' => 'integer',
		'mem_total_mb' => 'integer',
		'mem_percent' => 'float',
		'disk_used_gb' => 'float',
		'disk_total_gb' => 'float',
		'disk_percent' => 'float',
		'uptime_seconds' => 'integer',
		'load_json' => 'array',
	];

	public function server(): BelongsTo
	{
		return $this->belongsTo(Server::class);
	}
}
