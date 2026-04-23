<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('accessguard_review_items', function (Blueprint $t) {
			$t->unsignedBigInteger('access_item_id')->nullable()->after('system_id');
			$t->string('snapshot_item_name', 160)->nullable()->after('snapshot_identifier');

			$t->foreign('access_item_id')->references('id')->on('accessguard_access_items')->nullOnDelete();
			$t->index(['access_item_id'], 'agri_access_item_idx');

			$t->dropUnique('agri_cycle_cell_uq');
		});

		// MariaDB/MySQL treat NULL as distinct in unique indexes, so keying on
		// (cycle, person, system, access_item_id) naturally allows either a
		// single cell-level row (access_item_id = NULL) OR many per-item rows.
		Schema::table('accessguard_review_items', function (Blueprint $t) {
			$t->unique(['cycle_id', 'person_id', 'system_id', 'access_item_id'], 'agri_cycle_cell_item_uq');
		});

		Schema::table('accessguard_review_actions', function (Blueprint $t) {
			$t->unsignedBigInteger('access_item_id')->nullable()->after('system_id');

			$t->foreign('access_item_id')->references('id')->on('accessguard_access_items')->nullOnDelete();
			$t->index(['access_item_id'], 'agra_access_item_idx');
		});
	}

	public function down(): void
	{
		Schema::table('accessguard_review_actions', function (Blueprint $t) {
			$t->dropForeign(['access_item_id']);
			$t->dropIndex('agra_access_item_idx');
			$t->dropColumn('access_item_id');
		});

		Schema::table('accessguard_review_items', function (Blueprint $t) {
			$t->dropUnique('agri_cycle_cell_item_uq');
			$t->unique(['cycle_id', 'person_id', 'system_id'], 'agri_cycle_cell_uq');
			$t->dropForeign(['access_item_id']);
			$t->dropIndex('agri_access_item_idx');
			$t->dropColumn(['access_item_id', 'snapshot_item_name']);
		});
	}
};
