<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alerting-state per server. alert_state houdt de laatst-gemelde toestand bij
 * (ok | offline | disk) zodat monitor:check-alerts alleen mailt bij een
 * overgang — geen herhaalde meldingen elke run.
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::table('monitor_servers', function (Blueprint $t) {
			$t->boolean('alerts_enabled')->default(true)->after('is_active');
			$t->string('alert_state', 20)->default('ok')->after('alerts_enabled');
			$t->timestamp('alerted_at')->nullable()->after('alert_state');
		});
	}

	public function down(): void
	{
		Schema::table('monitor_servers', function (Blueprint $t) {
			$t->dropColumn(['alerts_enabled', 'alert_state', 'alerted_at']);
		});
	}
};
