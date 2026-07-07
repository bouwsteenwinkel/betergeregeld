<?php

namespace App\Models\Assistant;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AiConversation extends Model
{
	protected $fillable = [
		'tenant_id', 'ai_agent_id', 'channel_type', 'contact_id', 'contact_type',
		'status', 'locale', 'message_count', 'cost_eur', 'sentiment', 'summary',
		'started_at', 'ended_at',
	];

	protected $casts = [
		'message_count' => 'int',
		'cost_eur'      => 'float',
		'started_at'    => 'datetime',
		'ended_at'      => 'datetime',
	];

	public function tenant(): BelongsTo
	{
		return $this->belongsTo(Tenant::class, 'tenant_id');
	}

	public function agent(): BelongsTo
	{
		return $this->belongsTo(AiAgent::class, 'ai_agent_id');
	}

	public function contact(): MorphTo
	{
		return $this->morphTo();
	}

	public function messages(): HasMany
	{
		return $this->hasMany(AiMessage::class);
	}
}
