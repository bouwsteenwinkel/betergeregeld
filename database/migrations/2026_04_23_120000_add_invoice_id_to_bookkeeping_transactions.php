<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('bookkeeping_transactions', function (Blueprint $t) {
			$t->uuid('invoice_id')->nullable()->after('relation_id');
			$t->index(['tenant_id', 'invoice_id']);
			$t->foreign('invoice_id')->references('id')->on('bookkeeping_invoices')->nullOnDelete();
		});
	}

	public function down(): void
	{
		Schema::table('bookkeeping_transactions', function (Blueprint $t) {
			$t->dropForeign(['invoice_id']);
			$t->dropIndex(['tenant_id', 'invoice_id']);
			$t->dropColumn('invoice_id');
		});
	}
};
