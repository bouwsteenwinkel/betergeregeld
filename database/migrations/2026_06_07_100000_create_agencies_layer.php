<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bureau-laag (white-label reseller) boven de tenants.
 *
 *   agencies            een bureau zoals "Rankdata" dat meerdere klanten (tenants)
 *                       beheert. Super-admin (platform) ziet alle bureaus; een
 *                       bureau-beheerder ziet alleen z'n eigen klanten.
 *   tenants.agency_id   koppelt een klant aan een bureau (null = directe
 *                       platform-tenant, bv. Beter Geregeld zelf).
 *   users.agency_id     gezet voor bureau-beheerders; samen met role='agency'
 *                       bepaalt dit de afgeschermde scope.
 *
 * Rollen (users.role): 'admin' = platform/super-admin (bestaand), 'agency' =
 * bureau-beheerder, 'client' = klantgebruiker (geen admin-panel, eigen dashboard).
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::create('agencies', function (Blueprint $t) {
			$t->uuid('id')->primary();
			$t->string('name', 120);
			$t->string('slug', 64)->unique();
			$t->string('contact_email', 190)->nullable();
			$t->string('primary_color', 9)->nullable();   // hex voor white-label klant-dashboard
			$t->boolean('is_active')->default(true);
			$t->timestamps();
		});

		Schema::table('tenants', function (Blueprint $t) {
			$t->uuid('agency_id')->nullable()->after('id');
			$t->index('agency_id', 'tenants_agency_idx');
			$t->foreign('agency_id')->references('id')->on('agencies')->nullOnDelete();
		});

		Schema::table('users', function (Blueprint $t) {
			$t->uuid('agency_id')->nullable()->after('tenant_id');
			$t->index('agency_id', 'users_agency_idx');
			$t->foreign('agency_id')->references('id')->on('agencies')->nullOnDelete();
		});

		// Per-klant uptime: een check kan aan een tenant hangen (de klant-site),
		// los van welke VPS 'm uitvoert. server_id blijft de organisatorische ouder.
		Schema::table('monitor_checks', function (Blueprint $t) {
			$t->uuid('tenant_id')->nullable()->after('server_id');
			$t->index('tenant_id', 'monchk_tenant_idx');
			$t->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
		});
	}

	public function down(): void
	{
		Schema::table('monitor_checks', function (Blueprint $t) {
			$t->dropForeign(['tenant_id']);
			$t->dropIndex('monchk_tenant_idx');
			$t->dropColumn('tenant_id');
		});
		Schema::table('users', function (Blueprint $t) {
			$t->dropForeign(['agency_id']);
			$t->dropIndex('users_agency_idx');
			$t->dropColumn('agency_id');
		});
		Schema::table('tenants', function (Blueprint $t) {
			$t->dropForeign(['agency_id']);
			$t->dropIndex('tenants_agency_idx');
			$t->dropColumn('agency_id');
		});
		Schema::dropIfExists('agencies');
	}
};
