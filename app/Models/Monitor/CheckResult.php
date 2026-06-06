<?php

namespace App\Models\Monitor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One up/down outcome of a check. Append-only time-series.
 *
 * @property int $id
 * @property string $check_id
 * @property \Carbon\Carbon $checked_at
 * @property string $status
 */
class CheckResult extends Model
{
	protected $table = 'monitor_check_results';

	public $timestamps = false;

	protected $fillable = [
		'check_id',
		'checked_at',
		'status',
		'latency_ms',
		'http_code',
		'error',
	];

	protected $casts = [
		'checked_at' => 'datetime',
		'latency_ms' => 'integer',
		'http_code' => 'integer',
	];

	public function check(): BelongsTo
	{
		return $this->belongsTo(Check::class);
	}
}
