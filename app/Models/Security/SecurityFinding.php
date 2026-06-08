<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eén bevinding binnen een security-scan: een blacklist-hit, Safe
 * Browsing-melding, mixed-content-resource of broken link.
 */
class SecurityFinding extends Model
{
	protected $table = 'security_findings';

	protected $fillable = [
		'security_scan_id', 'category', 'severity', 'finding', 'url', 'code',
	];

	protected $casts = [
		'code' => 'integer',
	];

	public function scan(): BelongsTo
	{
		return $this->belongsTo(SecurityScan::class, 'security_scan_id');
	}
}
