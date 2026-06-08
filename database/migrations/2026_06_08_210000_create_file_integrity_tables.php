<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Security-monitoring fase 3: file-integrity van WP-core. De companion-plugin
 * vergelijkt core-bestanden tegen de officiële WP.org-checksums en pusht de
 * afwijkingen (gewijzigd/ontbrekend) mee. Eén rij per afwijkend bestand.
 */
return new class extends Migration
{
	public function up(): void
	{
		Schema::create('site_integrity_issues', function (Blueprint $t) {
			$t->id();
			$t->unsignedBigInteger('property_id');
			$t->enum('type', ['modified', 'missing', 'unexpected']);
			$t->string('path', 500);
			$t->timestamps();

			$t->index('property_id', 'integ_property_idx');
			$t->foreign('property_id')->references('id')->on('seo_properties')->cascadeOnDelete();
		});

		Schema::table('seo_properties', function (Blueprint $t) {
			$t->timestamp('integrity_checked_at')->nullable()->after('software_reported_at');
			$t->string('integrity_alert_state', 16)->nullable()->after('integrity_checked_at');
			$t->timestamp('integrity_alerted_at')->nullable()->after('integrity_alert_state');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('site_integrity_issues');
		Schema::table('seo_properties', function (Blueprint $t) {
			$t->dropColumn(['integrity_checked_at', 'integrity_alert_state', 'integrity_alerted_at']);
		});
	}
};
