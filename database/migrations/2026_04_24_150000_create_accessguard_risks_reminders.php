<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('accessguard_risk_flags', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->string('kind', 40);
			// stale_admin | orphan_access | excessive_access |
			// overdue_review | overdue_action | pending_onboarding
			$t->tinyInteger('severity')->default(3); // 1 (low) .. 5 (critical)
			$t->string('subject_type', 40);
			// person | access_cell | access_item | cycle | action | process
			$t->string('subject_id', 64);
			$t->string('title', 200);
			$t->text('description')->nullable();
			$t->string('status', 20)->default('open');
			// open | acknowledged | resolved
			$t->timestamp('detected_at')->useCurrent();
			$t->timestamp('acknowledged_at')->nullable();
			$t->char('acknowledged_by_user_id', 36)->nullable();
			$t->timestamp('resolved_at')->nullable();
			$t->char('resolved_by_user_id', 36)->nullable();
			$t->json('payload')->nullable();
			$t->timestamps();

			$t->index(['tenant_id', 'status', 'severity'], 'agrf_tss_idx');
			$t->index(['tenant_id', 'kind'], 'agrf_tk_idx');
			// Idempotent upsert key: one open row per (kind, subject) per tenant.
			$t->unique(['tenant_id', 'kind', 'subject_type', 'subject_id'], 'agrf_tksub_uq');
		});

		Schema::create('accessguard_reminders', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->string('kind', 40);
			// cycle_due | process_due | action_overdue | person_starting | person_leaving
			$t->string('subject_type', 40);
			$t->string('subject_id', 64);
			$t->string('title', 200);
			$t->text('description')->nullable();
			$t->date('due_at')->nullable();
			$t->string('status', 20)->default('open');
			// open | done | dismissed
			$t->timestamp('dismissed_at')->nullable();
			$t->char('dismissed_by_user_id', 36)->nullable();
			$t->timestamps();

			$t->index(['tenant_id', 'status', 'due_at'], 'agrem_tsd_idx');
			$t->unique(['tenant_id', 'kind', 'subject_type', 'subject_id'], 'agrem_tksub_uq');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('accessguard_reminders');
		Schema::dropIfExists('accessguard_risk_flags');
	}
};
