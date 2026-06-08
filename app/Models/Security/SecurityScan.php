<?php

namespace App\Models\Security;

use App\Models\Seo\SeoProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eén security-scan-run voor een site (seo_property): malware/blacklist +
 * mixed-content/broken-links. Samenvatting gedenormaliseerd; details in
 * security_findings.
 */
class SecurityScan extends Model
{
	protected $table = 'security_scans';

	protected $fillable = [
		'property_id', 'status', 'started_at', 'completed_at',
		'safe_browsing', 'blacklisted', 'mixed_content_count',
		'broken_link_count', 'links_checked', 'error_message',
	];

	protected $casts = [
		'started_at'          => 'datetime',
		'completed_at'        => 'datetime',
		'blacklisted'         => 'boolean',
		'mixed_content_count' => 'integer',
		'broken_link_count'   => 'integer',
		'links_checked'       => 'integer',
	];

	public function property(): BelongsTo
	{
		return $this->belongsTo(SeoProperty::class, 'property_id');
	}

	public function findings(): HasMany
	{
		return $this->hasMany(SecurityFinding::class);
	}

	/** Kritieke toestand: op een blacklist of door Safe Browsing gemarkeerd. */
	public function isFlagged(): bool
	{
		return $this->blacklisted || $this->safe_browsing === 'flagged';
	}
}
