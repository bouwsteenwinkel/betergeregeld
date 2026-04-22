<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('bookkeeping_audit_log', function (Blueprint $t) {
			$t->bigIncrements('id');
			$t->uuid('tenant_id');
			$t->uuid('user_id')->nullable();
			$t->string('user_email', 190)->nullable();
			$t->string('entity_type', 32);
			$t->string('entity_id', 64);
			$t->enum('action', ['created', 'updated', 'deleted']);
			$t->json('changes')->nullable();
			$t->char('ip_hash', 64)->nullable();
			$t->string('user_agent', 300)->nullable();
			$t->timestamp('created_at')->useCurrent();

			$t->index(['tenant_id', 'created_at']);
			$t->index(['tenant_id', 'entity_type', 'entity_id']);
			$t->index(['tenant_id', 'user_id']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('bookkeeping_audit_log');
	}
};
