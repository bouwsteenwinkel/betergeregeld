<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Security-monitoring fase 1 (agent-loos): malware/blacklist (Safe Browsing +
 * DNSBL) en mixed-content/broken-links per site. Eén scan-run per site met
 * gedenormaliseerde samenvatting + losse findings, zelfde patroon als de
 * quality-scan. security_alert_state op de site voedt de transitie-alert.
 */
return new class extends Migration
{
	public function up(): void
	{
		Schema::create('security_scans', function (Blueprint $t) {
			$t->id();
			$t->unsignedBigInteger('property_id'); // seo_properties.id (de site)
			$t->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
			$t->timestamp('started_at')->nullable();
			$t->timestamp('completed_at')->nullable();
			$t->enum('safe_browsing', ['ok', 'flagged', 'unknown'])->nullable();
			$t->boolean('blacklisted')->default(false);
			$t->unsignedInteger('mixed_content_count')->default(0);
			$t->unsignedInteger('broken_link_count')->default(0);
			$t->unsignedInteger('links_checked')->default(0);
			$t->text('error_message')->nullable();
			$t->timestamps();

			$t->index(['property_id', 'status'], 'secscan_property_status_idx');
			$t->foreign('property_id')->references('id')->on('seo_properties')->cascadeOnDelete();
		});

		Schema::create('security_findings', function (Blueprint $t) {
			$t->id();
			$t->unsignedBigInteger('security_scan_id');
			$t->string('category', 32);                 // safe_browsing|blacklist|mixed_content|broken_link
			$t->enum('severity', ['laag', 'middel', 'hoog']);
			$t->text('finding');
			$t->string('url', 1000)->nullable();
			$t->unsignedSmallInteger('code')->nullable(); // HTTP-status bij broken_link
			$t->timestamps();

			$t->index('security_scan_id', 'secfind_scan_idx');
			$t->foreign('security_scan_id')->references('id')->on('security_scans')->cascadeOnDelete();
		});

		Schema::table('seo_properties', function (Blueprint $t) {
			$t->string('security_alert_state', 16)->nullable()->after('freshness_alerted_at');
			$t->timestamp('security_alerted_at')->nullable()->after('security_alert_state');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('security_findings');
		Schema::dropIfExists('security_scans');
		Schema::table('seo_properties', function (Blueprint $t) {
			$t->dropColumn(['security_alert_state', 'security_alerted_at']);
		});
	}
};
