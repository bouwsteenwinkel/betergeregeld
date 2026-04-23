<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('accessguard_checklist_templates', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->string('kind', 20); // onboarding | offboarding
			$t->string('name', 120);
			$t->boolean('is_default')->default(false);
			$t->timestamps();

			$t->index(['tenant_id', 'kind'], 'agct_tenant_kind_idx');
		});

		Schema::create('accessguard_checklist_template_items', function (Blueprint $t) {
			$t->id();
			$t->unsignedBigInteger('template_id');
			$t->string('title', 200);
			$t->text('description')->nullable();
			$t->integer('sort_order')->default(100);
			$t->boolean('requires_evidence')->default(false);
			$t->timestamps();

			$t->index(['template_id', 'sort_order'], 'agcti_template_sort_idx');
			$t->foreign('template_id')->references('id')->on('accessguard_checklist_templates')->cascadeOnDelete();
		});

		Schema::create('accessguard_processes', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->unsignedBigInteger('person_id');
			$t->string('kind', 20); // onboarding | offboarding
			$t->string('status', 20)->default('active');
			// active | completed | cancelled
			$t->string('title', 200);
			$t->char('created_by_user_id', 36)->nullable();
			$t->date('started_at')->nullable();
			$t->date('due_at')->nullable();
			$t->timestamp('completed_at')->nullable();
			$t->text('notes')->nullable();
			$t->timestamps();

			$t->index(['tenant_id', 'kind', 'status'], 'agp_tkstat_idx');
			$t->index(['person_id'], 'agp_person_idx');
			$t->foreign('person_id')->references('id')->on('accessguard_people')->cascadeOnDelete();
		});

		Schema::create('accessguard_process_items', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->unsignedBigInteger('process_id');
			$t->string('title', 200);
			$t->text('description')->nullable();
			$t->string('status', 20)->default('todo');
			// todo | in_progress | done | blocked | na
			$t->text('status_reason')->nullable(); // for blocked / na
			$t->integer('sort_order')->default(100);
			$t->boolean('requires_evidence')->default(false);
			$t->timestamp('completed_at')->nullable();
			$t->char('completed_by_user_id', 36)->nullable();
			$t->timestamps();

			$t->index(['tenant_id'], 'agpi_tenant_idx');
			$t->index(['process_id', 'sort_order'], 'agpi_process_sort_idx');
			$t->foreign('process_id')->references('id')->on('accessguard_processes')->cascadeOnDelete();
		});

		Schema::create('accessguard_process_evidence', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->unsignedBigInteger('process_item_id');
			$t->uuid('file_uuid');
			$t->string('original_name', 255);
			$t->string('stored_path', 500);
			$t->string('mime', 120);
			$t->unsignedBigInteger('size_bytes');
			$t->string('sha256', 64);
			$t->char('uploaded_by_user_id', 36)->nullable();
			$t->timestamp('uploaded_at')->useCurrent();

			$t->index(['tenant_id'], 'agpe_tenant_idx');
			$t->index(['process_item_id'], 'agpe_item_idx');
			$t->foreign('process_item_id')->references('id')->on('accessguard_process_items')->cascadeOnDelete();
		});

		// Wire actions so they can also originate from a process (offboarding).
		Schema::table('accessguard_review_actions', function (Blueprint $t) {
			$t->unsignedBigInteger('cycle_id')->nullable()->change();
			$t->unsignedBigInteger('review_item_id')->nullable()->change();
			$t->unsignedBigInteger('process_id')->nullable()->after('cycle_id');

			$t->foreign('process_id')->references('id')->on('accessguard_processes')->nullOnDelete();
			$t->index(['process_id'], 'agra_process_idx');
		});
	}

	public function down(): void
	{
		Schema::table('accessguard_review_actions', function (Blueprint $t) {
			$t->dropForeign(['process_id']);
			$t->dropIndex('agra_process_idx');
			$t->dropColumn('process_id');
			$t->unsignedBigInteger('cycle_id')->nullable(false)->change();
			$t->unsignedBigInteger('review_item_id')->nullable(false)->change();
		});

		Schema::dropIfExists('accessguard_process_evidence');
		Schema::dropIfExists('accessguard_process_items');
		Schema::dropIfExists('accessguard_processes');
		Schema::dropIfExists('accessguard_checklist_template_items');
		Schema::dropIfExists('accessguard_checklist_templates');
	}
};
