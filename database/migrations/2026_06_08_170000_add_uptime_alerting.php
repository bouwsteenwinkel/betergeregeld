<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Downtime-alerting voor uptime-checks (up->down / down->up), state-based zodat
 * het niet spamt — zelfde patroon als de server- en SEO-freshness-alerts.
 *
 * - monitor_checks.alert_state / alerted_at: laatst-gemelde toestand per check.
 * - seo_properties.notify_email: optioneel per-klant alert-adres; valt anders
 *   terug op het bureau-contact en daarna het platform-adres.
 */
return new class extends Migration
{
	public function up(): void
	{
		Schema::table('monitor_checks', function (Blueprint $t) {
			$t->string('alert_state', 16)->nullable()->after('last_checked_at');
			$t->timestamp('alerted_at')->nullable()->after('alert_state');
		});

		Schema::table('seo_properties', function (Blueprint $t) {
			$t->string('notify_email', 190)->nullable()->after('label');
		});
	}

	public function down(): void
	{
		Schema::table('monitor_checks', function (Blueprint $t) {
			$t->dropColumn(['alert_state', 'alerted_at']);
		});

		Schema::table('seo_properties', function (Blueprint $t) {
			$t->dropColumn('notify_email');
		});
	}
};
