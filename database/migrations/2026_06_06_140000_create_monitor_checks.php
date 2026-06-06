<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * HTTP/TCP-uptimechecks — hardere SLA-bron dan heartbeat alleen.
 *   - monitor_checks         wat we pollen (URL of host:port) + laatste resultaat
 *   - monitor_check_results  time-series up/down voor uptime-% per periode
 * Een check kan aan een server hangen (server_id) zodat de tenant-SLA van die
 * server op echte bereikbaarheid kan rekenen.
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::create('monitor_checks', function (Blueprint $t) {
			$t->uuid('id')->primary();
			$t->uuid('server_id')->nullable();
			$t->string('name', 120);
			$t->string('type', 10)->default('http');
			// http | tcp
			$t->string('target', 500);
			// http: volledige URL · tcp: host:port
			$t->unsignedSmallInteger('expected_code')->nullable();
			// http: verwachte statuscode; leeg = elke 2xx/3xx is up
			$t->unsignedSmallInteger('timeout_seconds')->default(10);
			$t->boolean('is_active')->default(true);
			$t->string('last_status', 10)->default('unknown');
			// up | down | unknown
			$t->unsignedSmallInteger('last_code')->nullable();
			$t->unsignedInteger('last_latency_ms')->nullable();
			$t->timestamp('last_checked_at')->nullable();
			$t->string('last_error', 500)->nullable();
			$t->timestamps();

			$t->index('server_id', 'monchk_server_idx');
			$t->index('is_active', 'monchk_active_idx');
			$t->foreign('server_id')->references('id')->on('monitor_servers')->nullOnDelete();
		});

		Schema::create('monitor_check_results', function (Blueprint $t) {
			$t->id();
			$t->uuid('check_id');
			$t->timestamp('checked_at')->index();
			$t->string('status', 10);
			// up | down
			$t->unsignedInteger('latency_ms')->nullable();
			$t->unsignedSmallInteger('http_code')->nullable();
			$t->string('error', 500)->nullable();

			$t->index(['check_id', 'checked_at'], 'monchkres_chk_time_idx');
			$t->foreign('check_id')->references('id')->on('monitor_checks')->cascadeOnDelete();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('monitor_check_results');
		Schema::dropIfExists('monitor_checks');
	}
};
