<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SEO / Google Search Console-integratie
 *
 * - seo_properties     : welke GSC-properties we monitoren
 * - seo_query_daily    : per (property, date, query, page) de stats
 * - seo_imports_log    : audit-trail van API-calls + fouten
 *
 * Patroon overgenomen van BSW V3 (bsw_seo_*-tabellen) — daar draait
 * dezelfde structuur sinds ~maart 2026. Service-account JWT-auth en
 * idempotente per-dag-import zijn 1-op-1 vertaald naar Laravel.
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::create('seo_properties', function (Blueprint $t) {
			$t->id();
			// GSC site-URL: 'sc-domain:betergeregeld.com' (Domain property)
			// of 'https://example.com/' (URL-prefix property)
			$t->string('site_url', 255)->unique();
			$t->string('label', 120);
			$t->boolean('is_active')->default(true);
			$t->date('last_imported_date')->nullable();
			$t->string('last_import_error', 500)->nullable();
			$t->timestamps();
			$t->index('is_active');
		});

		Schema::create('seo_query_daily', function (Blueprint $t) {
			$t->id();
			$t->unsignedBigInteger('property_id');
			$t->date('date');
			$t->string('query', 500);
			$t->string('page', 500);
			$t->unsignedInteger('clicks')->default(0);
			$t->unsignedInteger('impressions')->default(0);
			$t->decimal('ctr', 7, 5)->default(0);
			$t->decimal('position', 7, 3)->default(0);
			$t->timestamp('created_at')->useCurrent();

			$t->index(['property_id', 'date']);
			// MariaDB-style functional index hint not portable; we use
			// a SHA1-hash column for uniqueness because query/page can
			// each be 500 chars (too long for a direct unique index).
		});

		// Generated row_hash + unique key — moet via raw SQL omdat
		// Laravel Blueprint geen native GENERATED-cols ondersteunt voor
		// alle drivers consistent.
		DB::statement("
			ALTER TABLE seo_query_daily
			ADD COLUMN row_hash CHAR(40) AS (SHA1(CONCAT(query, '|', page))) STORED,
			ADD UNIQUE KEY uniq_row (property_id, date, row_hash)
		");

		Schema::create('seo_imports_log', function (Blueprint $t) {
			$t->id();
			$t->unsignedBigInteger('property_id');
			$t->date('target_date');
			$t->enum('status', ['pending', 'success', 'failed', 'skipped'])->default('pending');
			$t->unsignedInteger('rows_imported')->default(0);
			$t->string('error_message', 500)->nullable();
			$t->timestamp('started_at')->useCurrent();
			$t->timestamp('finished_at')->nullable();

			$t->index(['property_id', 'target_date']);
			$t->index(['status', 'started_at']);
		});

		// Seed: betergeregeld.com als active property. Idempotent.
		DB::table('seo_properties')->updateOrInsert(
			['site_url' => 'sc-domain:betergeregeld.com'],
			[
				'label'      => 'Beter Geregeld',
				'is_active'  => true,
				'created_at' => now(),
				'updated_at' => now(),
			]
		);
	}

	public function down(): void
	{
		Schema::dropIfExists('seo_imports_log');
		Schema::dropIfExists('seo_query_daily');
		Schema::dropIfExists('seo_properties');
	}
};
