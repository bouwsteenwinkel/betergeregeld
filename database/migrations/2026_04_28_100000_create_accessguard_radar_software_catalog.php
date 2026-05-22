<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reference table for "what does the world look like?" — per (vendor,
 * product) we cache the latest stable version, beta channel, and EOL
 * dates. Tenant-agnostic on purpose: the same WordPress 6.7.1 release
 * is current for everyone, so we don't multiply rows by tenant.
 *
 * Joined with accessguard_radar_software_instances at display time
 * (FreshnessCalculator) to derive a per-instance freshness_status:
 *   up_to_date | outdated_safe | outdated_vulnerable | vulnerable | eol | prerelease | unknown
 *
 * Filled by:
 *   - WordPressCoreCatalogIngestor    → wordpress.org core API
 *   - WordPressPluginCatalogIngestor  → wordpress.org plugin API per slug
 *   - EndOfLifeCatalogIngestor        → endoflife.date for OS / runtimes / servers
 *   - manual edits                    → vendor='manual' for products without a public API
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::create('accessguard_radar_software_catalog', function (Blueprint $t) {
			$t->id();
			$t->string('vendor', 120);
			$t->string('product', 120);
			$t->string('latest_stable_version', 80)->nullable();
			$t->string('latest_lts_version', 80)->nullable();
			$t->string('latest_beta_version', 80)->nullable();
			$t->string('source', 30);
			// wp_api | endoflife_date | npm | packagist | manual | wpvuln
			$t->json('release_history')->nullable();
			// [{"version":"6.7.1","released":"2026-04-15"}, ...] — newest first
			$t->json('eol_dates')->nullable();
			// {"7.4":"2022-11-28","8.0":"2023-11-26","8.1":"2024-12-31"}
			// Keys are MAJOR.MINOR strings, values are ISO-8601 dates.
			$t->string('product_url', 500)->nullable();
			// Canonical homepage / changelog so the UI can deep-link.
			$t->timestamp('last_synced_at')->nullable();
			$t->text('last_sync_error')->nullable();
			$t->timestamps();

			$t->unique(['vendor', 'product'], 'agrsc_vp_uq');
			$t->index('source', 'agrsc_source_idx');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('accessguard_radar_software_catalog');
	}
};
