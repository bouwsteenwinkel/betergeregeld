<?php

namespace App\Models\AccessGuard\Radar;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scan extends Model
{
	public const TYPES = ['http_probe', 'agent_pull', 'feed_match', 'manual'];
	public const STATUSES = ['queued', 'running', 'success', 'error', 'skipped'];

	protected $table = 'accessguard_radar_scans';

	protected $fillable = [
		'tenant_id', 'asset_id', 'scan_type', 'status',
		'detections_count', 'findings_count',
		'started_at', 'finished_at',
		'error_message', 'raw_output',
	];

	protected function casts(): array
	{
		return [
			'started_at' => 'datetime',
			'finished_at' => 'datetime',
			'raw_output' => 'array',
		];
	}

	public function asset(): BelongsTo
	{
		return $this->belongsTo(Asset::class, 'asset_id');
	}
}
