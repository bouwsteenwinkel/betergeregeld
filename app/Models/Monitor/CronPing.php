<?php

namespace App\Models\Monitor;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eén binnengekomen ping van een bewaakte cron-job. Onveranderlijke historie;
 * de gedenormaliseerde laatste-stand staat op de CronMonitor zelf.
 *
 * @property int $id
 * @property string $cron_monitor_id
 * @property string $status      start | success | fail
 * @property int|null $exit_code
 * @property int|null $duration_ms
 * @property string|null $message
 * @property string|null $source_ip
 * @property Carbon $received_at
 */
class CronPing extends Model
{
	public $timestamps = false;

	protected $table = 'cron_pings';

	protected $fillable = [
		'cron_monitor_id',
		'status',
		'exit_code',
		'duration_ms',
		'message',
		'source_ip',
		'received_at',
	];

	protected $casts = [
		'exit_code'   => 'int',
		'duration_ms' => 'int',
		'received_at' => 'datetime',
	];

	public function monitor(): BelongsTo
	{
		return $this->belongsTo(CronMonitor::class, 'cron_monitor_id');
	}
}
