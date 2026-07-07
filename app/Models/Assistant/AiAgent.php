<?php

namespace App\Models\Assistant;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiAgent extends Model
{
	protected $fillable = [
		'tenant_id', 'name', 'locale', 'model', 'voice',
		'system_prompt', 'business_context', 'temperature', 'is_active',
	];

	protected $casts = [
		'temperature' => 'float',
		'is_active'   => 'bool',
	];

	public function tenant(): BelongsTo
	{
		return $this->belongsTo(Tenant::class, 'tenant_id');
	}

	public function channels(): HasMany
	{
		return $this->hasMany(AiAgentChannel::class);
	}

	public function conversations(): HasMany
	{
		return $this->hasMany(AiConversation::class);
	}
}
