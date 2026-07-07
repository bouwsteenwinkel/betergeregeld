<?php

namespace App\Models\Assistant;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessage extends Model
{
	protected $fillable = [
		'tenant_id', 'ai_conversation_id', 'role', 'content',
		'tool_name', 'tool_payload', 'input_tokens', 'output_tokens', 'audio_ref',
	];

	protected $casts = [
		'tool_payload'  => 'array',
		'input_tokens'  => 'int',
		'output_tokens' => 'int',
	];

	public function tenant(): BelongsTo
	{
		return $this->belongsTo(Tenant::class, 'tenant_id');
	}

	public function conversation(): BelongsTo
	{
		return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
	}
}
