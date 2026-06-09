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
		'rankdata_plan_id',
		'discount_percent',
	];

	protected $casts = [
		'is_active' => 'bool',
		'discount_percent' => 'float',
	];

	/** Het Rankdata-plan van dit bureau (null = het standaard actieve rankdata-plan). */
	public function rankdataPlan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
	{
		return $this->belongsTo(Plan::class, 'rankdata_plan_id');
	}

	/**
	 * Vind het bureau bij een hostnaam, op basis van het subdomein t.o.v. het
	 * platform-domein (config('app.url')). Bijv. rankdata.betergeregeld.com →
	 * agency met subdomain 'rankdata'. Voor white-label-branding per subdomein.
	 */
	public static function resolveFromHost(?string $host): ?self
	{
		if (! $host) {
			return null;
		}
		$host = preg_replace('/^www\./', '', strtolower($host));
		$base = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));

		if ($base === '' || ! str_ends_with($host, '.' . $base)) {
			return null;
		}
		$sub = substr($host, 0, -(strlen($base) + 1));
		if ($sub === '' || str_contains($sub, '.')) {
			return null; // alleen een enkel subdomein-label
		}

		return static::query()->where('subdomain', $sub)->where('is_active', true)->first();
	}

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
