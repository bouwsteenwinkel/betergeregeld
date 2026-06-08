<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Model;

/**
 * Eén composer/npm-advisory op een eigen code-project (platform-niveau).
 */
class DependencyAdvisory extends Model
{
	protected $table = 'dependency_advisories';

	public $timestamps = false;

	protected $fillable = [
		'ecosystem', 'project', 'package', 'severity', 'title',
		'advisory_id', 'cve', 'fixed_in', 'link', 'imported_at',
	];

	protected $casts = [
		'imported_at' => 'datetime',
	];
}
