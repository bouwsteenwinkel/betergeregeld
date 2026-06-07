<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Een bureau (white-label reseller) dat meerdere klanten (tenants) beheert,
 * bijv. "Rankdata". Super-admin ziet alle bureaus; een bureau-beheerder
 * (users.role='agency' + agency_id) ziet alleen de eigen klanten.
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $contact_email
 * @property string|null $primary_color
 * @property bool $is_active
 */
class Agency extends Model
{
	use HasUuids;

	protected $table = 'agencies';

	protected $keyType = 'string';

	public $incrementing = false;

	protected $fillable = [
		'name',
		'slug',
		'contact_email',
		'primary_color',
		'logo_path',
		'subdomain',
		'is_active',
	];

	protected $casts = [
		'is_active' => 'bool',
	];

	protected static function booted(): void
	{
		static::creating(function (Agency $agency): void {
			if (empty($agency->slug)) {
				$agency->slug = Str::slug($agency->name);
			}
		});
	}

	public function tenants(): HasMany
	{
		return $this->hasMany(Tenant::class);
	}

	public function users(): HasMany
	{
		return $this->hasMany(User::class);
	}

	/** Het merkkleur voor het white-label klant-dashboard (met veilige fallback). */
	public function brandColor(): string
	{
		return $this->primary_color ?: '#0f766e';
	}

	/** Publieke logo-URL (uit de public-disk) of null. */
	public function logoUrl(): ?string
	{
		return $this->logo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->logo_path) : null;
	}
}
