<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cron-monitoring (dead-man's-switch).
 *
 *   - cron_monitors  één bewaakte cron-job. Hoort bij een tenant (nullable =
 *                    platform/gedeeld) en optioneel een website (nullable =
 *                    gedeeld binnen de tenant). De job pingt ping_token bij
 *                    succes/start/fout; raakt de heartbeat te oud of meldt de
 *                    job een fout, dan slaat alert_state om en mailt
 *                    cron:check-monitors — net als de server- en SEO-alerts,
 *                    alléén bij OVERGANG.
 *   - cron_pings     ruwe ping-historie per monitor (start/success/fail), met
 *                    exit-code, duur en bron-IP. Begrensd via de prune-job.
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::create('cron_monitors', function (Blueprint $t) {
			$t->uuid('id')->primary();
			$t->uuid('tenant_id')->nullable();          // null = platform / gedeeld over tenants
			$t->string('website', 190)->nullable();     // null = gedeeld binnen de tenant
			$t->string('name', 120);
			$t->char('ping_token', 40)->unique();
			$t->text('description')->nullable();

			// Verwachte cadans: de job moet minstens elke expected_period_minutes
			// draaien; grace_minutes is de speling voordat 'late' wordt gemeld.
			$t->unsignedInteger('expected_period_minutes')->default(1440); // dagelijks
			$t->unsignedInteger('grace_minutes')->default(60);

			$t->boolean('is_active')->default(true);
			$t->boolean('alerts_enabled')->default(true);
			$t->string('notify_email', 190)->nullable(); // override; anders config('monitor.alert_email')

			// Gedenormaliseerde heartbeat-/laatste-signaal-velden voor snelle lijst.
			$t->timestamp('last_ping_at')->nullable();    // laatste SUCCES-ping (de heartbeat)
			$t->timestamp('last_started_at')->nullable(); // laatste start-signaal
			$t->string('last_status', 12)->nullable();    // start | success | fail
			$t->unsignedInteger('last_duration_ms')->nullable();
			$t->integer('last_exit_code')->nullable();
			$t->string('last_message', 500)->nullable();

			// Alert-statemachine (zelfde patroon als monitor_servers.alert_state).
			$t->string('alert_state', 12)->default('ok'); // ok | late | failed
			$t->timestamp('alerted_at')->nullable();

			$t->timestamps();

			$t->index(['is_active', 'alerts_enabled'], 'cronmon_active_idx');
			$t->index('tenant_id', 'cronmon_tenant_idx');
			$t->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
		});

		Schema::create('cron_pings', function (Blueprint $t) {
			$t->id();
			$t->uuid('cron_monitor_id');
			$t->string('status', 12);          // start | success | fail
			$t->integer('exit_code')->nullable();
			$t->unsignedInteger('duration_ms')->nullable();
			$t->string('message', 500)->nullable();
			$t->string('source_ip', 45)->nullable();
			$t->timestamp('received_at')->useCurrent();

			$t->index(['cron_monitor_id', 'received_at'], 'cronping_mon_time_idx');
			$t->foreign('cron_monitor_id')->references('id')->on('cron_monitors')->cascadeOnDelete();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('cron_pings');
		Schema::dropIfExists('cron_monitors');
	}
};
