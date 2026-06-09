<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Demo-omgeving: een apart Demo-bureau + demoklanten + demo-sites met
 * voorbeelddata, togglebaar via een selector en uitgesloten van de echte
 * scanners/alerts (anders overschrijven die de voorbeelddata).
 */
return new class extends Migration
{
	public function up(): void
	{
		foreach (['agencies', 'tenants', 'seo_properties', 'monitor_checks'] as $table) {
			Schema::table($table, function (Blueprint $t) {
				$t->boolean('is_demo')->default(false)->index();
			});
		}
	}

	public function down(): void
	{
		foreach (['agencies', 'tenants', 'seo_properties', 'monitor_checks'] as $table) {
			Schema::table($table, function (Blueprint $t) {
				$t->dropColumn('is_demo');
			});
		}
	}
};
