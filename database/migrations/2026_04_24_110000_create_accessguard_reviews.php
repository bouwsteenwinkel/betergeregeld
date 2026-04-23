<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('accessguard_review_cycles', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->char('created_by_user_id', 36)->nullable();
			$t->string('title', 200);
			$t->string('scope', 20)->default('all');
			// all | active_people | category:<cat>
			$t->string('status', 20)->default('planned');
			// planned | active | completed | cancelled
			$t->date('starts_at')->nullable();
			$t->date('due_at')->nullable();
			$t->timestamp('completed_at')->nullable();
			$t->text('notes')->nullable();
			$t->timestamps();

			$t->index(['tenant_id', 'status'], 'agrc_tenant_status_idx');
		});

		Schema::create('accessguard_review_items', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->unsignedBigInteger('cycle_id');
			$t->unsignedBigInteger('person_id');
			$t->unsignedBigInteger('system_id');
			// Snapshot of state at cycle-open so later mutations don't
			// confuse the reviewer about what they're deciding on.
			$t->string('person_label', 220);
			$t->string('system_label', 200);
			$t->string('snapshot_state', 20);
			$t->string('snapshot_level', 60)->nullable();
			$t->string('snapshot_identifier', 160)->nullable();
			$t->string('decision', 20)->nullable();
			// keep | revoke | change | NULL (undecided)
			$t->text('decision_note')->nullable();
			$t->char('decided_by_user_id', 36)->nullable();
			$t->timestamp('decided_at')->nullable();
			$t->timestamps();

			$t->index(['tenant_id'], 'agri_tenant_idx');
			$t->index(['cycle_id', 'decision'], 'agri_cycle_decision_idx');
			$t->unique(['cycle_id', 'person_id', 'system_id'], 'agri_cycle_cell_uq');

			$t->foreign('cycle_id')->references('id')->on('accessguard_review_cycles')->cascadeOnDelete();
		});

		Schema::create('accessguard_review_actions', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->unsignedBigInteger('cycle_id');
			$t->unsignedBigInteger('review_item_id');
			$t->unsignedBigInteger('person_id');
			$t->unsignedBigInteger('system_id');
			$t->string('kind', 20);
			// revoke_access | review_level
			$t->string('title', 200);
			$t->string('status', 20)->default('open');
			// open | done | cancelled
			$t->char('assigned_to_user_id', 36)->nullable();
			$t->date('due_at')->nullable();
			$t->timestamp('completed_at')->nullable();
			$t->text('note')->nullable();
			$t->timestamps();

			$t->index(['tenant_id', 'status'], 'agra_tenant_status_idx');
			$t->index(['cycle_id'], 'agra_cycle_idx');

			$t->foreign('cycle_id')->references('id')->on('accessguard_review_cycles')->cascadeOnDelete();
			$t->foreign('review_item_id')->references('id')->on('accessguard_review_items')->cascadeOnDelete();
		});

		Schema::create('accessguard_review_log', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->unsignedBigInteger('cycle_id')->nullable();
			$t->unsignedBigInteger('review_item_id')->nullable();
			$t->unsignedBigInteger('review_action_id')->nullable();
			$t->unsignedBigInteger('person_id')->nullable();
			$t->unsignedBigInteger('system_id')->nullable();
			$t->char('actor_user_id', 36)->nullable();
			$t->string('event', 40);
			// cycle_created | cycle_completed | cycle_cancelled
			// | decision_set | decision_changed
			// | action_created | action_done | action_cancelled
			// | cell_applied
			$t->json('payload')->nullable();
			$t->timestamp('created_at')->useCurrent();

			$t->index(['tenant_id', 'created_at'], 'agrl_tenant_time_idx');
			$t->index(['cycle_id'], 'agrl_cycle_idx');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('accessguard_review_log');
		Schema::dropIfExists('accessguard_review_actions');
		Schema::dropIfExists('accessguard_review_items');
		Schema::dropIfExists('accessguard_review_cycles');
	}
};
