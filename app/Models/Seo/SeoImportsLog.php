<?php

namespace App\Models\Seo;

use Illuminate\Database\Eloquent\Model;

/**
 * Audit-trail per import-poging. Status doorloopt:
 *   pending → success / failed / skipped
 */
class SeoImportsLog extends Model
{
	protected $table = 'seo_imports_log';

	public $timestamps = false; // started_at + finished_at, geen updated_at

	protected $fillable = [
		'property_id', 'target_date', 'status', 'rows_imported',
		'error_message', 'started_at', 'finished_at',
	];

	protected function casts(): array
	{
		return [
			'target_date'   => 'date',
			'started_at'    => 'datetime',
			'finished_at'   => 'datetime',
			'rows_imported' => 'integer',
		];
	}
}
