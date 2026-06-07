<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sites-laag: een klant (tenant) kan meerdere websites/applicaties hebben. Een
 * seo_property fungeert als de "site" (heeft al tenant_id + site_url + label);
 * we voegen alleen een type toe (website|app). De uptime-check verhuist van
 * tenant-niveau naar site-niveau via monitor_checks.property_id.
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::table('seo_properties', function (Blueprint $t) {
			$t->string('type', 16)->default('website')->after('label'); // website | app
		});

		Schema::table('monitor_checks', function (Blueprint $t) {
			$t->unsignedBigInteger('property_id')->nullable()->after('tenant_id');
			$t->index('property_id', 'monchk_property_idx');
			$t->foreign('property_id')->references('id')->on('seo_properties')->nullOnDelete();
		});

		// Backfill: bestaande tenant-checks koppelen aan de (enige) property van die tenant.
		DB::statement(
			'UPDATE monitor_checks mc
			 JOIN (SELECT tenant_id, MIN(id) AS pid FROM seo_properties GROUP BY tenant_id) sp
			   ON sp.tenant_id = mc.tenant_id
			 SET mc.property_id = sp.pid
			 WHERE mc.property_id IS NULL AND mc.tenant_id IS NOT NULL'
		);
	}

	public function down(): void
	{
		Schema::table('monitor_checks', function (Blueprint $t) {
			$t->dropForeign(['property_id']);
			$t->dropIndex('monchk_property_idx');
			$t->dropColumn('property_id');
		});
		Schema::table('seo_properties', function (Blueprint $t) {
			$t->dropColumn('type');
		});
	}
};
