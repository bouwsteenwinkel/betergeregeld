<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessEvidence extends Model
{
	public function processItem(): BelongsTo
	{
		return $this->belongsTo(ProcessItem::class, 'process_item_id');
	}


	protected $table = 'accessguard_process_evidence';

	public $timestamps = false;

	protected $fillable = [
		'tenant_id', 'process_item_id', 'file_uuid',
		'original_name', 'stored_path', 'mime', 'size_bytes', 'sha256',
		'uploaded_by_user_id', 'uploaded_at',
	];

	protected function casts(): array
	{
		return [
			'size_bytes' => 'integer',
			'uploaded_at' => 'datetime',
		];
	}
}
