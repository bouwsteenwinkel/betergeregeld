<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('accessguard_feedback', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36)->nullable();
			$t->char('user_id', 36)->nullable();
			$t->string('category', 30);
			// bug | feature | praise | question | other
			$t->text('message');
			$t->string('page_url', 500)->nullable();
			$t->string('user_agent', 500)->nullable();
			$t->string('status', 20)->default('new');
			// new | triaged | resolved | wontfix
			$t->text('admin_note')->nullable();
			$t->timestamp('resolved_at')->nullable();
			$t->timestamps();

			$t->index(['tenant_id'], 'agf_t_idx');
			$t->index(['status', 'created_at'], 'agf_sc_idx');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('accessguard_feedback');
	}
};
