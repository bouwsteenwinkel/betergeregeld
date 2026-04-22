<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('bookkeeping_transactions', function (Blueprint $t) {
			$t->string('bank_reference', 128)->nullable()->after('invoice_number');
			$t->string('import_source', 190)->nullable()->after('bank_reference');
			$t->index(['tenant_id', 'bank_reference']);
		});
	}

	public function down(): void
	{
		Schema::table('bookkeeping_transactions', function (Blueprint $t) {
			$t->dropIndex(['tenant_id', 'bank_reference']);
			$t->dropColumn(['bank_reference', 'import_source']);
		});
	}
};
