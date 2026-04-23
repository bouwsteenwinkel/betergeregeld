<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('accessguard_people', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->string('type', 20)->default('employee'); // employee | contractor | external
			$t->string('first_name', 100);
			$t->string('middle_name', 50)->nullable();
			$t->string('last_name', 100);
			$t->string('email', 190)->nullable();
			$t->string('phone', 40)->nullable();
			$t->string('job_title', 120)->nullable();
			$t->string('department', 120)->nullable();
			$t->string('status', 20)->default('active');
			// active | scheduled_in | scheduled_out | inactive
			$t->date('start_date')->nullable();
			$t->date('end_date')->nullable();
			$t->text('notes')->nullable();
			$t->timestamps();
			$t->softDeletes();

			$t->index(['tenant_id', 'status'], 'agp_tenant_status_idx');
			$t->index(['tenant_id', 'last_name', 'first_name'], 'agp_tenant_name_idx');
			$t->unique(['tenant_id', 'email'], 'agp_tenant_email_uq');
		});

		Schema::create('accessguard_systems', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->string('name', 160);
			$t->string('category', 40)->default('other');
			// saas | on_prem | infra | finance | security | comm | other
			$t->boolean('is_active')->default(true);
			$t->text('notes')->nullable();
			$t->timestamps();

			$t->index(['tenant_id', 'is_active'], 'ags_tenant_active_idx');
			$t->unique(['tenant_id', 'name'], 'ags_tenant_name_uq');
		});

		Schema::create('accessguard_access_cells', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->unsignedBigInteger('person_id');
			$t->unsignedBigInteger('system_id');
			$t->string('access_state', 20)->default('unknown');
			// unknown | has_access | no_access | needs_review
			$t->string('access_level', 60)->nullable();
			$t->string('account_identifier', 160)->nullable();
			$t->timestamp('last_verified_at')->nullable();
			$t->text('notes')->nullable();
			$t->timestamps();

			$t->index(['tenant_id'], 'agc_tenant_idx');
			$t->index(['person_id'], 'agc_person_idx');
			$t->index(['system_id'], 'agc_system_idx');
			$t->unique(['tenant_id', 'person_id', 'system_id'], 'agc_cell_uq');

			$t->foreign('person_id')->references('id')->on('accessguard_people')->cascadeOnDelete();
			$t->foreign('system_id')->references('id')->on('accessguard_systems')->cascadeOnDelete();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('accessguard_access_cells');
		Schema::dropIfExists('accessguard_systems');
		Schema::dropIfExists('accessguard_people');
	}
};
