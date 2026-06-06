<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * VPS Monitoring — foundation tables.
 *
 *   - monitor_servers   the VPSes we watch (super-admin managed); each row carries
 *                       an ingest_token the on-host collector authenticates with,
 *                       plus denormalised last_* values for fast list rendering.
 *   - monitor_metrics   time-series resource samples pushed by the collector.
 *
 * Tenants are linked to a server via tenants.server_id so a tenant can see the
 * SLA/uptime of the VPS it runs on (uptime is derived from heartbeat continuity:
 * received samples / expected samples over a window).
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::create('monitor_servers', function (Blueprint $t) {
			$t->uuid('id')->primary();
			$t->string('name', 120);
			$t->string('hostname', 255)->nullable();
			$t->string('ip_address', 45)->nullable();
			$t->string('provider', 80)->nullable();
			$t->string('location', 80)->nullable();
			$t->boolean('is_active')->default(true);
			$t->char('ingest_token', 64)->unique();
			$t->string('agent_version', 40)->nullable();
			$t->timestamp('agent_last_seen_at')->nullable();
			// Denormalised latest readings (percent) — kept in sync on each ingest
			// so the server list/badges render without touching the metrics table.
			$t->decimal('last_cpu_percent', 5, 2)->nullable();
			$t->decimal('last_mem_percent', 5, 2)->nullable();
			$t->decimal('last_disk_percent', 5, 2)->nullable();
			$t->text('notes')->nullable();
			$t->timestamps();

			$t->index('is_active', 'monsrv_active_idx');
		});

		Schema::create('monitor_metrics', function (Blueprint $t) {
			$t->id();
			$t->uuid('server_id');
			$t->timestamp('collected_at')->index();
			$t->decimal('cpu_percent', 5, 2)->nullable();
			$t->unsignedInteger('mem_used_mb')->nullable();
			$t->unsignedInteger('mem_total_mb')->nullable();
			$t->decimal('mem_percent', 5, 2)->nullable();
			$t->decimal('disk_used_gb', 10, 2)->nullable();
			$t->decimal('disk_total_gb', 10, 2)->nullable();
			$t->decimal('disk_percent', 5, 2)->nullable();
			$t->unsignedBigInteger('uptime_seconds')->nullable();
			// Free-form extras the agent may send: per-disk, network counters, services.
			$t->json('load_json')->nullable();
			$t->timestamp('created_at')->useCurrent();

			$t->index(['server_id', 'collected_at'], 'monmet_srv_time_idx');
			$t->foreign('server_id')->references('id')->on('monitor_servers')->cascadeOnDelete();
		});

		Schema::table('tenants', function (Blueprint $t) {
			$t->uuid('server_id')->nullable()->after('plan');
			$t->index('server_id', 'tenants_server_idx');
			$t->foreign('server_id')->references('id')->on('monitor_servers')->nullOnDelete();
		});
	}

	public function down(): void
	{
		Schema::table('tenants', function (Blueprint $t) {
			$t->dropForeign(['server_id']);
			$t->dropIndex('tenants_server_idx');
			$t->dropColumn('server_id');
		});
		Schema::dropIfExists('monitor_metrics');
		Schema::dropIfExists('monitor_servers');
	}
};
