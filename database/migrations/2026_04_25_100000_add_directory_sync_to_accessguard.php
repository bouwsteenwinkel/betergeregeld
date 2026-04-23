<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('accessguard_people', function (Blueprint $t) {
			$t->string('external_source', 30)->nullable()->after('department');
			$t->string('external_id', 120)->nullable()->after('external_source');
			$t->timestamp('last_sign_in_at')->nullable()->after('external_id');
			$t->timestamp('last_synced_at')->nullable()->after('last_sign_in_at');

			$t->index(['tenant_id', 'external_source', 'external_id'], 'agp_ext_idx');
		});

		Schema::create('accessguard_directory_connections', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->string('provider', 30); // m365 | google (future)
			$t->string('external_tenant_id', 120)->nullable(); // Azure tenantId
			$t->string('display_name', 200)->nullable(); // "contoso.onmicrosoft.com"
			$t->text('access_token_enc')->nullable();
			$t->text('refresh_token_enc')->nullable();
			$t->timestamp('expires_at')->nullable();
			$t->string('scopes', 500)->nullable();
			$t->string('status', 20)->default('connected');
			// connected | disconnected | error
			$t->timestamp('last_synced_at')->nullable();
			$t->string('last_sync_status', 20)->nullable(); // ok | error
			$t->text('last_sync_message')->nullable();
			$t->integer('last_sync_users_seen')->default(0);
			$t->integer('last_sync_users_created')->default(0);
			$t->integer('last_sync_users_updated')->default(0);
			$t->timestamps();

			$t->index(['tenant_id', 'provider'], 'agdc_t_prov_idx');
			$t->unique(['tenant_id', 'provider'], 'agdc_t_prov_uq');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('accessguard_directory_connections');
		Schema::table('accessguard_people', function (Blueprint $t) {
			$t->dropIndex('agp_ext_idx');
			$t->dropColumn(['external_source', 'external_id', 'last_sign_in_at', 'last_synced_at']);
		});
	}
};
