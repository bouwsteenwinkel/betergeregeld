<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aparte alert-toestand voor TRENDwaarschuwingen (schijf/geheugen loopt vol bij
 * het huidige tempo). Bewust een eigen kolom naast alert_state: die houdt de
 * acute toestand bij (offline/schijf vol) en een trend mag zo'n melding niet
 * overschrijven — je wilt allebei apart kunnen zien afgaan en herstellen.
 * Zelfde patroon als de dimensies op properties (software/security/integrity).
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::table('monitor_servers', function (Blueprint $t) {
			$t->string('trend_alert_state', 20)->default('ok')->after('alerted_at');
			$t->timestamp('trend_alerted_at')->nullable()->after('trend_alert_state');
		});
	}

	public function down(): void
	{
		Schema::table('monitor_servers', function (Blueprint $t) {
			$t->dropColumn(['trend_alert_state', 'trend_alerted_at']);
		});
	}
};
