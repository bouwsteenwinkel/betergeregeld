<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('bookkeeping_invoice_reminders', function (Blueprint $t) {
			$t->bigIncrements('id');
			$t->uuid('tenant_id');
			$t->uuid('invoice_id');
			$t->enum('kind', ['pre_due', 'due', 'overdue_7', 'overdue_21']);
			$t->timestamp('sent_at')->useCurrent();
			$t->string('sent_to_email', 190)->nullable();
			$t->boolean('was_manual')->default(false);

			$t->unique(['invoice_id', 'kind']);
			$t->index(['tenant_id', 'sent_at']);
			$t->foreign('invoice_id')->references('id')->on('bookkeeping_invoices')->cascadeOnDelete();
		});

		Schema::table('bookkeeping_tenant_settings', function (Blueprint $t) {
			$t->boolean('auto_reminders_enabled')->default(true)->after('default_payment_terms_days');
		});
	}

	public function down(): void
	{
		Schema::table('bookkeeping_tenant_settings', function (Blueprint $t) {
			$t->dropColumn('auto_reminders_enabled');
		});
		Schema::dropIfExists('bookkeeping_invoice_reminders');
	}
};
