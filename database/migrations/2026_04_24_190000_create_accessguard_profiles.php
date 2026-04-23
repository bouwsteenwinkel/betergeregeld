<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('accessguard_access_profiles', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->string('name', 120);
			$t->text('description')->nullable();
			$t->boolean('is_active')->default(true);
			$t->timestamps();

			$t->index(['tenant_id', 'is_active'], 'agap_ta_idx');
			$t->unique(['tenant_id', 'name'], 'agap_tn_uq');
		});

		Schema::create('accessguard_profile_items', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->unsignedBigInteger('profile_id');
			$t->unsignedBigInteger('system_id');
			$t->unsignedBigInteger('access_item_id')->nullable();
			$t->string('default_state', 20)->default('has_access');
			// has_access | no_access | needs_review
			$t->timestamps();

			$t->index(['profile_id'], 'agpi_p_idx');
			$t->index(['tenant_id'], 'agpi_t_idx');
			$t->foreign('profile_id')->references('id')->on('accessguard_access_profiles')->cascadeOnDelete();
			$t->foreign('system_id')->references('id')->on('accessguard_systems')->cascadeOnDelete();
			$t->foreign('access_item_id')->references('id')->on('accessguard_access_items')->nullOnDelete();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('accessguard_profile_items');
		Schema::dropIfExists('accessguard_access_profiles');
	}
};
