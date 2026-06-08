<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Security-monitoring fase 2: CMS/plugin/thema-inventaris per site (gepusht door
 * de companion-plugin) + de Wordfence-vuln-feed + de gematchte kwetsbaarheden
 * per site. Ingest-token + software_alert_state op de site.
 */
return new class extends Migration
{
	public function up(): void
	{
		// Wat een site draait (gepusht via de companion-plugin).
		Schema::create('site_components', function (Blueprint $t) {
			$t->id();
			$t->unsignedBigInteger('property_id');
			$t->enum('type', ['core', 'plugin', 'theme']);
			$t->string('slug', 190);
			$t->string('name', 190)->nullable();
			$t->string('version', 40)->nullable();
			$t->string('latest_version', 40)->nullable();
			$t->boolean('has_update')->default(false);
			$t->boolean('wp_active')->default(true);
			$t->boolean('vulnerable')->default(false);
			$t->unsignedInteger('vuln_count')->default(0);
			$t->timestamp('reported_at')->nullable();
			$t->timestamps();

			$t->unique(['property_id', 'type', 'slug'], 'sitecomp_unique');
			$t->foreign('property_id')->references('id')->on('seo_properties')->cascadeOnDelete();
		});

		// Wordfence-vuln-feed (lokaal gecachet) — één rij per (vuln × software × versiebereik).
		Schema::create('vulnerabilities', function (Blueprint $t) {
			$t->id();
			$t->string('source', 20)->default('wordfence');
			$t->string('ext_id', 64);
			$t->enum('software_type', ['core', 'plugin', 'theme']);
			$t->string('slug', 190);
			$t->string('title', 255);
			$t->string('severity', 16)->nullable();   // low|medium|high|critical
			$t->decimal('cvss', 4, 1)->nullable();
			$t->string('cve', 32)->nullable();
			$t->string('from_version', 40)->nullable();
			$t->boolean('from_inclusive')->default(true);
			$t->string('to_version', 40)->nullable();
			$t->boolean('to_inclusive')->default(false);
			$t->string('patched_in', 40)->nullable();
			$t->string('reference', 500)->nullable();
			$t->timestamp('imported_at')->nullable();

			$t->index(['software_type', 'slug'], 'vuln_software_idx');
			$t->index(['ext_id', 'slug', 'from_version', 'to_version'], 'vuln_dedupe_idx');
		});

		// Gematchte kwetsbaarheden per site (opnieuw opgebouwd bij elke ingest).
		Schema::create('site_vulnerabilities', function (Blueprint $t) {
			$t->id();
			$t->unsignedBigInteger('property_id');
			$t->enum('type', ['core', 'plugin', 'theme']);
			$t->string('slug', 190);
			$t->string('name', 190)->nullable();
			$t->string('version', 40)->nullable();
			$t->string('title', 255);
			$t->string('severity', 16)->nullable();
			$t->string('cve', 32)->nullable();
			$t->string('patched_in', 40)->nullable();
			$t->string('reference', 500)->nullable();
			$t->timestamps();

			$t->index('property_id', 'sitevuln_property_idx');
			$t->foreign('property_id')->references('id')->on('seo_properties')->cascadeOnDelete();
		});

		Schema::table('seo_properties', function (Blueprint $t) {
			$t->string('security_ingest_token', 64)->nullable()->unique()->after('security_alerted_at');
			$t->string('software_alert_state', 16)->nullable()->after('security_ingest_token');
			$t->timestamp('software_alerted_at')->nullable()->after('software_alert_state');
			$t->timestamp('software_reported_at')->nullable()->after('software_alerted_at');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('site_vulnerabilities');
		Schema::dropIfExists('vulnerabilities');
		Schema::dropIfExists('site_components');
		Schema::table('seo_properties', function (Blueprint $t) {
			$t->dropColumn(['security_ingest_token', 'software_alert_state', 'software_alerted_at', 'software_reported_at']);
		});
	}
};
