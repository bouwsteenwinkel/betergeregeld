<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CaseEvent
 * 
 * @property string $id
 * @property string $tenant_id
 * @property string $case_id
 * @property Carbon $ts
 * @property string $actor
 * @property string $channel
 * @property string $event_type
 * @property string|null $message
 * @property string|null $payload
 * 
 * @property Case $case
 * @property Tenant $tenant
 *
 * @package App\Models
 */
class CaseEvent extends Model
{
	protected $table = 'case_events';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'ts' => 'datetime'
	];

	protected $fillable = [
		'tenant_id',
		'case_id',
		'ts',
		'actor',
		'channel',
		'event_type',
		'message',
		'payload'
	];

	public function case()
	{
		return $this->belongsTo(SupportCase::class);
	}

	public function tenant()
	{
		return $this->belongsTo(Tenant::class);
	}
}
