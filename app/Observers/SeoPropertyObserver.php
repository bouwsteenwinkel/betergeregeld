<?php

namespace App\Observers;

use App\Models\Seo\SeoProperty;
use App\Services\Rankdata\SiteProvisioner;

/**
 * Richt automatisch de standaard-monitoring in zodra een site wordt aangemaakt
 * (via super-admin, bureau-panel of seeder) — uptime-check, kwaliteitspagina,
 * security-token. Idempotent.
 */
class SeoPropertyObserver
{
	public function created(SeoProperty $site): void
	{
		app(SiteProvisioner::class)->provision($site);
	}
}
