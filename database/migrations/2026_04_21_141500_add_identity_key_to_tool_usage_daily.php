<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		// Drop test data: NULLs made ON DUPLICATE KEY UPDATE ineffective before this fix.
		DB::table('tool_usage_daily')->truncate();

		Schema::table('tool_usage_daily', function (Blueprint $t) {
			$idx = collect(DB::select('SHOW INDEX FROM tool_usage_daily'))
				->pluck('Key_name')->unique()->all();
			if (in_array('tool_usage_unique', $idx, true)) {
				$t->dropUnique('tool_usage_unique');
			}

			if (! Schema::hasColumn('tool_usage_daily', 'identity_key')) {
				$t->string('identity_key', 80)->after('id');
			}
		});

		Schema::table('tool_usage_daily', function (Blueprint $t) {
			$t->unique(['identity_key', 'tool', 'date'], 'tool_usage_identity_unique');
		});
	}

	public function down(): void
	{
		Schema::table('tool_usage_daily', function (Blueprint $t) {
			$idx = collect(DB::select('SHOW INDEX FROM tool_usage_daily'))
				->pluck('Key_name')->unique()->all();
			if (in_array('tool_usage_identity_unique', $idx, true)) {
				$t->dropUnique('tool_usage_identity_unique');
			}
			if (Schema::hasColumn('tool_usage_daily', 'identity_key')) {
				$t->dropColumn('identity_key');
			}
			$t->unique(['ip_hash', 'user_id', 'tool', 'date'], 'tool_usage_unique');
		});
	}
};
