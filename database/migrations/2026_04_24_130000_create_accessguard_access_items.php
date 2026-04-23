<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('accessguard_access_items', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->unsignedBigInteger('system_id');
			$t->string('name', 160);
			$t->string('type', 40)->default('role');
			// role | licence | account | key | badge | group | other
			$t->text('description')->nullable();
			$t->boolean('is_active')->default(true);
			$t->integer('sort_order')->default(100);
			$t->timestamps();

			$t->index(['tenant_id', 'system_id', 'is_active'], 'agai_tsia_idx');
			$t->unique(['tenant_id', 'system_id', 'name'], 'agai_tsn_uq');

			$t->foreign('system_id')->references('id')->on('accessguard_systems')->cascadeOnDelete();
		});

		Schema::create('accessguard_person_access_items', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->unsignedBigInteger('person_id');
			$t->unsignedBigInteger('system_id');
			$t->unsignedBigInteger('access_item_id');
			$t->string('access_state', 20)->default('unknown');
			// unknown | has_access | no_access | needs_review
			$t->string('access_level', 60)->nullable();
			$t->string('account_identifier', 160)->nullable();
			$t->timestamp('last_verified_at')->nullable();
			$t->text('notes')->nullable();
			$t->timestamps();

			$t->index(['tenant_id'], 'agpai_tenant_idx');
			$t->index(['person_id'], 'agpai_person_idx');
			$t->index(['access_item_id', 'access_state'], 'agpai_item_state_idx');
			$t->unique(['tenant_id', 'person_id', 'access_item_id'], 'agpai_cell_uq');

			$t->foreign('person_id')->references('id')->on('accessguard_people')->cascadeOnDelete();
			$t->foreign('system_id')->references('id')->on('accessguard_systems')->cascadeOnDelete();
			$t->foreign('access_item_id')->references('id')->on('accessguard_access_items')->cascadeOnDelete();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('accessguard_person_access_items');
		Schema::dropIfExists('accessguard_access_items');
	}
};
