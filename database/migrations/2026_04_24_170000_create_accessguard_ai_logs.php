<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('accessguard_ai_logs', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->char('user_id', 36)->nullable();
			$t->string('subject_type', 40);
			// risk_flag | cycle | action | credential | process
			$t->string('subject_id', 64);
			$t->string('prompt_hash', 64);
			// sha256 of the templated prompt for caching/dedupe
			$t->string('model', 60);
			$t->json('response_json')->nullable();
			$t->unsignedInteger('tokens_in')->default(0);
			$t->unsignedInteger('tokens_out')->default(0);
			$t->unsignedInteger('duration_ms')->default(0);
			$t->string('status', 20)->default('ok');
			// ok | cached | rate_limited | api_error | blocked
			$t->string('error_message', 500)->nullable();
			$t->string('ip_hash', 64)->nullable();
			$t->timestamp('created_at')->useCurrent();

			$t->index(['tenant_id', 'created_at'], 'agai_tc_idx');
			$t->index(['tenant_id', 'subject_type', 'subject_id'], 'agai_tsub_idx');
			$t->index(['prompt_hash', 'created_at'], 'agai_pc_idx');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('accessguard_ai_logs');
	}
};
