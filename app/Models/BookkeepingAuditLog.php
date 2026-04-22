<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookkeepingAuditLog extends Model
{
	public const UPDATED_AT = null; // only created_at

	protected $table = 'bookkeeping_audit_log';

	protected $casts = [
		'changes' => 'array',
		'created_at' => 'datetime',
	];

	protected $fillable = [
		'tenant_id', 'user_id', 'user_email',
		'entity_type', 'entity_id', 'action',
		'changes', 'ip_hash', 'user_agent', 'created_at',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
