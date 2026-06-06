<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bron-modus voor cron-monitors. Een "bron" houdt één ping_token vast en maakt
 * via de ping-endpoint (?job=NAAM) automatisch per-job onder-monitors aan —
 * bedoeld voor externe projecten met veel cron-jobs (bijv. Bouwsteenwinkel),
 * zodat je niet per job een apart token hoeft te beheren. De bron zelf alarmeert
 * niet op zijn eigen heartbeat; de onder-monitors dragen de status.
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::table('cron_monitors', function (Blueprint $t) {
			$t->boolean('is_source')->default(false)->after('alerts_enabled');
		});
	}

	public function down(): void
	{
		Schema::table('cron_monitors', function (Blueprint $t) {
			$t->dropColumn('is_source');
		});
	}
};
