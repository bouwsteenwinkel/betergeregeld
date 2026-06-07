<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * White-label branding per bureau: logo + (sub)domein, naast de bestaande
 * primary_color. Het bureau-panel en de klant-dashboards gebruiken deze.
 * subdomain is voor latere echte subdomein-routing (rankdata.betergeregeld.com);
 * nu vooral opgeslagen + getoond.
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::table('agencies', function (Blueprint $t) {
			$t->string('logo_path', 255)->nullable()->after('primary_color');
			$t->string('subdomain', 80)->nullable()->unique()->after('logo_path');
		});
	}

	public function down(): void
	{
		Schema::table('agencies', function (Blueprint $t) {
			$t->dropColumn(['logo_path', 'subdomain']);
		});
	}
};
