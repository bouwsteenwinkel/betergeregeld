<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;

class ChecklistTemplateItem extends Model
{
	protected $table = 'accessguard_checklist_template_items';

	protected $fillable = [
		'template_id', 'title', 'description', 'sort_order', 'requires_evidence',
	];

	protected function casts(): array
	{
		return ['requires_evidence' => 'boolean'];
	}
}
