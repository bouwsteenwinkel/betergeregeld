<?php

namespace App\Models\Assistant;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAgentChannel extends Model
{
	protected $fillable = [
		'tenant_id', 'ai_agent_id', 'channel_type', 'binding', 'config', 'is_active',
	];

	protected $casts = [
		'config'    => 'array',
		'is_active' => 'bool',
	];

	public function tenant(): BelongsTo
	{
		return $this->belongsTo(Tenant::class, 'tenant_id');
	}

	public function agent(): BelongsTo
	{
		return $this->belongsTo(AiAgent::class, 'ai_agent_id');
	}
}
