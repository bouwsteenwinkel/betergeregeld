<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('accessguard_access_profiles', function (Blueprint $t) {
			$t->string('external_source', 30)->nullable()->after('description');
			$t->string('external_id', 120)->nullable()->after('external_source');
			$t->timestamp('last_synced_at')->nullable()->after('external_id');

			// Synced groups need unique (tenant, source, id). Keep the existing
			// (tenant_id, name) unique — on name collision the sync service
			// appends a " — M365" suffix before insert.
			$t->unique(['tenant_id', 'external_source', 'external_id'], 'agap_ext_uq');
		});

		Schema::create('accessguard_profile_members', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->unsignedBigInteger('profile_id');
			$t->unsignedBigInteger('person_id');
			$t->string('source', 30)->default('manual'); // manual | m365 | google
			$t->timestamp('last_synced_at')->nullable();
			$t->timestamps();

			$t->index(['tenant_id', 'profile_id'], 'agpm_tp_idx');
			$t->index(['tenant_id', 'person_id'], 'agpm_tper_idx');
			$t->unique(['tenant_id', 'profile_id', 'person_id'], 'agpm_tpp_uq');

			$t->foreign('profile_id')->references('id')->on('accessguard_access_profiles')->cascadeOnDelete();
			$t->foreign('person_id')->references('id')->on('accessguard_people')->cascadeOnDelete();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('accessguard_profile_members');
		Schema::table('accessguard_access_profiles', function (Blueprint $t) {
			$t->dropUnique('agap_ext_uq');
			$t->dropColumn(['external_source', 'external_id', 'last_synced_at']);
		});
	}
};
