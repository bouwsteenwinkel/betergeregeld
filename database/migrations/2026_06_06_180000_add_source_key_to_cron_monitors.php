<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stabiele sleutel waarmee interne Laravel-scheduler-jobs hun eigen monitor
 * terugvinden (bijv. 'seo:import-gsc'). Null voor handmatig in de UI aangemaakte
 * monitors; uniek wanneer gezet, zodat firstOrCreate idempotent provisioned.
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::table('cron_monitors', function (Blueprint $t) {
			$t->string('source_key', 64)->nullable()->unique()->after('ping_token');
		});
	}

	public function down(): void
	{
		Schema::table('cron_monitors', function (Blueprint $t) {
			$t->dropColumn('source_key');
		});
	}
};
