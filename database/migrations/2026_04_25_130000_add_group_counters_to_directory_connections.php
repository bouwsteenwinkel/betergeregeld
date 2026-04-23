<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('accessguard_directory_connections', function (Blueprint $t) {
			$t->integer('last_sync_groups_seen')->default(0)->after('last_sync_users_updated');
			$t->integer('last_sync_groups_upserted')->default(0)->after('last_sync_groups_seen');
			$t->integer('last_sync_memberships_seen')->default(0)->after('last_sync_groups_upserted');
		});
	}

	public function down(): void
	{
		Schema::table('accessguard_directory_connections', function (Blueprint $t) {
			$t->dropColumn(['last_sync_groups_seen', 'last_sync_groups_upserted', 'last_sync_memberships_seen']);
		});
	}
};
