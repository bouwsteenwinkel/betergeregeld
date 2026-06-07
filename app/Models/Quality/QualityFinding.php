<?php

namespace App\Models\Quality;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eén bevinding binnen een scan — deterministisch of door de AI gevonden.
 *
 * @property int $id
 * @property int $quality_scan_id
 * @property string $check_id
 * @property string $source    deterministic|ai
 * @property string $status    pass|warn|fail
 * @property string $severity  laag|middel|hoog
 * @property string $finding
 * @property string|null $suggestion
 * @property string|null $element
 */
class QualityFinding extends Model
{
	protected $table = 'quality_findings';

	protected $fillable = [
		'quality_scan_id', 'check_id', 'source', 'status', 'severity',
		'finding', 'suggestion', 'element',
	];

	public function scan(): BelongsTo
	{
		return $this->belongsTo(QualityScan::class, 'quality_scan_id');
	}
}
