<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecklistTemplate extends Model
{
	public const KINDS = ['onboarding', 'offboarding'];

	protected $table = 'accessguard_checklist_templates';

	protected $fillable = [
		'tenant_id', 'kind', 'name', 'is_default',
	];

	protected function casts(): array
	{
		return ['is_default' => 'boolean'];
	}

	public function items(): HasMany
	{
		return $this->hasMany(ChecklistTemplateItem::class, 'template_id')->orderBy('sort_order');
	}
}
