<?php

namespace App\Models\Monitor;

use Illuminate\Database\Eloquent\Model;

/**
 * Eén status-rij (id=1) die de alert-toestand per dimensie bewaart, zodat we —
 * net als de overige monitor-onderdelen — alleen mailen bij een OVERGANG.
 */
class SocketLabsStatus extends Model
{
	protected $table = 'socketlabs_status';

	protected $fillable = [
		'queue_state', 'failure_state', 'complaint_state', 'silence_state',
		'queue_alerted_at', 'failure_alerted_at', 'complaint_alerted_at', 'silence_alerted_at',
		'last_event_at', 'last_evaluated_at', 'counts',
		'api_reachable', 'api_checked_at', 'api_state', 'api_alerted_at',
	];

	protected $casts = [
		'queue_alerted_at'     => 'datetime',
		'failure_alerted_at'   => 'datetime',
		'complaint_alerted_at' => 'datetime',
		'silence_alerted_at'   => 'datetime',
		'last_event_at'        => 'datetime',
		'last_evaluated_at'    => 'datetime',
		'counts'               => 'array',
		'api_reachable'        => 'bool',
		'api_checked_at'       => 'datetime',
		'api_alerted_at'       => 'datetime',
	];

	/** De singleton status-rij ophalen (of aanmaken). */
	public static function instance(): self
	{
		return static::query()->firstOrCreate(['id' => 1]);
	}
}
