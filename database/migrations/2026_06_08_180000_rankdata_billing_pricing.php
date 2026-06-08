<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rankdata-prijsmodel: per klant (basis = price_monthly) + per extra site
 * (price_per_site boven included_sites), met optionele korting. De korting kan
 * per bureau worden overschreven (agencies.discount_percent) en het bureau kan
 * aan een specifiek Rankdata-plan hangen (agencies.rankdata_plan_id; anders het
 * standaard actieve rankdata-plan).
 */
return new class extends Migration
{
	public function up(): void
	{
		Schema::table('plans', function (Blueprint $t) {
			$t->unsignedSmallInteger('included_sites')->default(1)->after('price_yearly');
			$t->decimal('price_per_site', 8, 2)->nullable()->after('included_sites');
			$t->decimal('discount_percent', 5, 2)->default(0)->after('price_per_site');
		});

		Schema::table('agencies', function (Blueprint $t) {
			$t->unsignedBigInteger('rankdata_plan_id')->nullable()->after('is_active');
			$t->decimal('discount_percent', 5, 2)->nullable()->after('rankdata_plan_id');
		});
	}

	public function down(): void
	{
		Schema::table('plans', function (Blueprint $t) {
			$t->dropColumn(['included_sites', 'price_per_site', 'discount_percent']);
		});

		Schema::table('agencies', function (Blueprint $t) {
			$t->dropColumn(['rankdata_plan_id', 'discount_percent']);
		});
	}
};
