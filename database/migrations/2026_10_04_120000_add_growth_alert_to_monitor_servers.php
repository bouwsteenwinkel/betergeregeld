<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Eigen alert-toestand voor het GROEI-alarm (schijf groeit > N GB per uur).
 *
 * Bewust niet hergebruikt: alert_state en trend_alert_state. Op 04-10-2026 stonden die
 * allebei al weken op 'disk' resp. 'trend' toen de schijf in een avond volliep — een
 * overgang-gebaseerd alarm dat al "aan" staat, meldt niets nieuws. Het groei-alarm moet
 * los daarvan kunnen afgaan en herstellen.
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::table('monitor_servers', function (Blueprint $t) {
			$t->string('growth_alert_state', 20)->default('ok')->after('trend_alerted_at');
			$t->timestamp('growth_alerted_at')->nullable()->after('growth_alert_state');
		});
	}

	public function down(): void
	{
		Schema::table('monitor_servers', function (Blueprint $t) {
			$t->dropColumn(['growth_alert_state', 'growth_alerted_at']);
		});
	}
};
