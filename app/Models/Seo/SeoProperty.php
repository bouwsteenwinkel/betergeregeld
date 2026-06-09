<?php

namespace App\Models\Seo;

use App\Models\Tenant;
use App\Observers\SeoPropertyObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eén GSC-property die we monitoren. site_url is óf 'sc-domain:host' óf
 * 'https://host/' (URL-prefix). Beide vormen worden geaccepteerd door de
 * GSC API, maar 'sc-domain' is doorgaans accurater omdat het alle
 * sub-domeinen en protocollen samenvoegt.
 */
#[ObservedBy([SeoPropertyObserver::class])]
class SeoProperty extends Model
{
	protected $table = 'seo_properties';

	protected $fillable = [
		'tenant_id', 'site_url', 'label', 'type', 'is_active', 'is_demo', 'notify_email',
		'last_imported_date', 'last_import_error',
		'freshness_alert_state', 'freshness_alerted_at',
	];

	public function tenant(): BelongsTo
	{
		return $this->belongsTo(Tenant::class);
	}

	public function queries(): HasMany
	{
		return $this->hasMany(SeoQueryDaily::class, 'property_id');
	}

	/** De uptime-check van deze site (monitor_checks.property_id). */
	public function check(): HasMany
	{
		return $this->hasMany(\App\Models\Monitor\Check::class, 'property_id');
	}

	/** Schoon domein zonder de sc-domain:-prefix. */
	public function domain(): string
	{
		return preg_replace('/^sc-domain:/', '', (string) $this->site_url);
	}

	protected function casts(): array
	{
		return [
			'is_active'            => 'boolean',
			'is_demo'              => 'boolean',
			'last_imported_date'   => 'date',
			'freshness_alerted_at' => 'datetime',
		];
	}

	/**
	 * Versheid van de GSC-import: 'stale' als er > $days dagen geen SUCCESVOLLE
	 * import is geweest (gestopte scheduler óf telkens falende import), anders 'ok'.
	 * Voedt de freshness-alert in monitor:check-alerts.
	 */
	public function freshnessCondition(int $days): string
	{
		$lastSuccess = SeoImportsLog::query()
			->where('property_id', $this->id)
			->where('status', 'success')
			->latest('id')
			->value('finished_at');

		if (! $lastSuccess || \Illuminate\Support\Carbon::parse($lastSuccess)->lt(now()->subDays($days))) {
			return 'stale';
		}

		return 'ok';
	}
}
