<?php

namespace App\Models\Quality;

use App\Models\Seo\SeoProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Een pagina van een klantwebsite die we periodiek op kwaliteit scannen.
 * Hoort bij een site (seo_properties — de site-entiteit uit de sites-laag).
 *
 * @property int $id
 * @property int $site_id
 * @property string $url
 * @property string $label
 * @property bool $is_active
 * @property string $scan_frequency  daily|weekly|monthly
 */
class MonitoredPage extends Model
{
	protected $table = 'monitored_pages';

	protected $fillable = [
		'site_id', 'url', 'label', 'is_active', 'scan_frequency',
	];

	protected $casts = [
		'is_active' => 'boolean',
	];

	public function site(): BelongsTo
	{
		return $this->belongsTo(SeoProperty::class, 'site_id');
	}

	public function scans(): HasMany
	{
		return $this->hasMany(QualityScan::class);
	}

	public function latestScan(): HasOne
	{
		return $this->hasOne(QualityScan::class)->latestOfMany();
	}

	/** Aantal dagen tussen scans op basis van de frequentie. */
	public function intervalDays(): int
	{
		return match ($this->scan_frequency) {
			'daily'   => 1,
			'monthly' => 30,
			default   => 7, // weekly
		};
	}

	/** Is er sinds de laatste geslaagde scan genoeg tijd verstreken voor een nieuwe? */
	public function isDue(): bool
	{
		$last = $this->scans()->where('status', 'completed')->latest('completed_at')->value('completed_at');

		return $last === null || \Illuminate\Support\Carbon::parse($last)->lt(now()->subDays($this->intervalDays()));
	}
}
