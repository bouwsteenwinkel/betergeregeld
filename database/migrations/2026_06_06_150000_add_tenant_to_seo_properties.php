<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Koppelt een GSC-property aan een tenant, zodat een tenant z'n eigen
 * SEO-cijfers in het beperkte dashboard ziet. Nullable: platform-eigen
 * properties (betergeregeld zelf) hebben geen tenant.
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::table('seo_properties', function (Blueprint $t) {
			$t->uuid('tenant_id')->nullable()->after('id');
			$t->index('tenant_id', 'seoprop_tenant_idx');
			$t->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
		});
	}

	public function down(): void
	{
		Schema::table('seo_properties', function (Blueprint $t) {
			$t->dropForeign(['tenant_id']);
			$t->dropIndex('seoprop_tenant_idx');
			$t->dropColumn('tenant_id');
		});
	}
};
