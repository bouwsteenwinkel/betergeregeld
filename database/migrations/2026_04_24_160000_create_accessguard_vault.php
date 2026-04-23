<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('accessguard_vault_credentials', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->unsignedBigInteger('system_id')->nullable();
			$t->unsignedBigInteger('access_item_id')->nullable();
			$t->string('name', 160);
			$t->string('type', 20)->default('password');
			// password | token | api_key | ssh_key | cert | other
			$t->string('username', 190)->nullable();
			$t->longText('secret_enc');
			// Laravel Crypt payload (base64 JSON of iv/value/mac)
			$t->json('metadata')->nullable();
			// free-form: endpoint, notes, rotation instructions, etc.
			$t->date('expires_at')->nullable();
			$t->unsignedSmallInteger('rotation_interval_days')->nullable();
			$t->timestamp('last_rotated_at')->nullable();
			$t->char('created_by_user_id', 36);
			$t->timestamps();

			$t->index(['tenant_id', 'system_id'], 'agvc_tsys_idx');
			$t->index(['tenant_id', 'access_item_id'], 'agvc_tai_idx');
			$t->index(['tenant_id', 'expires_at'], 'agvc_texp_idx');

			$t->foreign('system_id')->references('id')->on('accessguard_systems')->nullOnDelete();
			$t->foreign('access_item_id')->references('id')->on('accessguard_access_items')->nullOnDelete();
		});

		Schema::create('accessguard_vault_acl', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->unsignedBigInteger('credential_id');
			$t->char('user_id', 36);
			$t->boolean('can_view')->default(true);
			$t->boolean('can_decrypt')->default(false);
			$t->boolean('can_rotate')->default(false);
			$t->boolean('can_admin')->default(false);
			$t->timestamp('granted_at')->useCurrent();
			$t->char('granted_by_user_id', 36)->nullable();
			$t->timestamp('revoked_at')->nullable();
			$t->char('revoked_by_user_id', 36)->nullable();

			$t->index(['tenant_id', 'credential_id'], 'agva_tc_idx');
			$t->index(['user_id'], 'agva_u_idx');
			$t->unique(['credential_id', 'user_id'], 'agva_cu_uq');

			$t->foreign('credential_id')->references('id')->on('accessguard_vault_credentials')->cascadeOnDelete();
		});

		Schema::create('accessguard_vault_access_log', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->unsignedBigInteger('credential_id')->nullable();
			$t->char('user_id', 36)->nullable();
			$t->string('action', 30);
			// created | updated | viewed | decrypted | rotated | deleted
			// | acl_granted | acl_revoked
			$t->string('ip_hash', 64)->nullable();
			$t->json('meta')->nullable();
			$t->timestamp('occurred_at')->useCurrent();

			$t->index(['tenant_id', 'credential_id', 'occurred_at'], 'agvlog_tco_idx');
			$t->index(['user_id'], 'agvlog_u_idx');

			$t->foreign('credential_id')->references('id')->on('accessguard_vault_credentials')->nullOnDelete();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('accessguard_vault_access_log');
		Schema::dropIfExists('accessguard_vault_acl');
		Schema::dropIfExists('accessguard_vault_credentials');
	}
};
