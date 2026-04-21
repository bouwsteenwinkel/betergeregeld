<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('plans', function (Blueprint $t) {
			if (! Schema::hasColumn('plans', 'product')) {
				$t->string('product', 32)->default('cmp')->after('id')->index();
			}
			if (! Schema::hasColumn('plans', 'sort_order')) {
				$t->unsignedSmallInteger('sort_order')->default(0)->after('is_active');
			}
		});

		Schema::table('tenant_subscriptions', function (Blueprint $t) {
			if (! Schema::hasColumn('tenant_subscriptions', 'current_period_starts_at')) {
				$t->dateTime('current_period_starts_at')->nullable()->after('status');
			}
			if (! Schema::hasColumn('tenant_subscriptions', 'cancel_at')) {
				$t->dateTime('cancel_at')->nullable()->after('current_period_ends_at');
			}
			if (! Schema::hasColumn('tenant_subscriptions', 'seats')) {
				$t->unsignedSmallInteger('seats')->default(1)->after('cancel_at');
			}
		});

		Schema::table('plan_features', function (Blueprint $t) {
			$idx = collect(\DB::select('SHOW INDEX FROM plan_features'))
				->pluck('Key_name')->unique()->all();
			if (! in_array('plan_features_plan_feature_unique', $idx, true)) {
				$t->unique(['plan_id', 'feature_key'], 'plan_features_plan_feature_unique');
			}
		});

		if (! Schema::hasTable('tool_usage_daily')) {
			Schema::create('tool_usage_daily', function (Blueprint $t) {
				$t->bigIncrements('id');
				$t->char('ip_hash', 64)->nullable();
				$t->uuid('user_id')->nullable();
				$t->uuid('tenant_id')->nullable();
				$t->string('tool', 64);
				$t->date('date');
				$t->unsignedInteger('count')->default(0);
				$t->timestamp('updated_at')->useCurrent();

				$t->index(['ip_hash', 'tool', 'date'], 'tool_usage_ip_idx');
				$t->index(['user_id', 'tool', 'date'], 'tool_usage_user_idx');
				$t->index(['tenant_id', 'tool', 'date'], 'tool_usage_tenant_idx');
				$t->unique(['ip_hash', 'user_id', 'tool', 'date'], 'tool_usage_unique');
			});
		}
	}

	public function down(): void
	{
		Schema::dropIfExists('tool_usage_daily');

		Schema::table('plan_features', function (Blueprint $t) {
			$idx = collect(\DB::select('SHOW INDEX FROM plan_features'))
				->pluck('Key_name')->unique()->all();
			if (in_array('plan_features_plan_feature_unique', $idx, true)) {
				$t->dropUnique('plan_features_plan_feature_unique');
			}
		});

		Schema::table('tenant_subscriptions', function (Blueprint $t) {
			foreach (['seats', 'cancel_at', 'current_period_starts_at'] as $col) {
				if (Schema::hasColumn('tenant_subscriptions', $col)) {
					$t->dropColumn($col);
				}
			}
		});

		Schema::table('plans', function (Blueprint $t) {
			foreach (['sort_order', 'product'] as $col) {
				if (Schema::hasColumn('plans', $col)) {
					$t->dropColumn($col);
				}
			}
		});
	}
};
