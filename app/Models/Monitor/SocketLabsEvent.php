<?php

namespace App\Models\Monitor;

use Illuminate\Database\Eloquent\Model;

/**
 * Eén deliverability-event uit een SocketLabs event-webhook.
 * (Tracking/opens/clicks slaan we niet op — alleen bezorg-/queue-signalen.)
 *
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $occurred_at
 */
class SocketLabsEvent extends Model
{
	protected $table = 'socketlabs_events';

	public $timestamps = false;

	protected $fillable = [
		'type', 'message_id', 'server_id', 'to_address', 'from_address',
		'subject', 'failure_type', 'failure_code', 'deferral_code', 'reason',
		'occurred_at', 'created_at',
	];

	protected $casts = [
		'occurred_at' => 'datetime',
		'created_at'  => 'datetime',
	];
}
