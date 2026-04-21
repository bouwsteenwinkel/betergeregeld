<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToolUsageDaily extends Model
{
	protected $table = 'tool_usage_daily';
	public $timestamps = false;

	protected $casts = [
		'date' => 'date',
		'count' => 'int',
	];

	protected $fillable = [
		'identity_key',
		'ip_hash',
		'user_id',
		'tenant_id',
		'tool',
		'date',
		'count',
		'updated_at',
	];
}
