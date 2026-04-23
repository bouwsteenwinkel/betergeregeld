<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('bookkeeping_recurring_invoices', function (Blueprint $t) {
			$t->uuid('id')->primary();
			$t->uuid('tenant_id');
			$t->unsignedBigInteger('relation_id');
			$t->string('title', 190);
			$t->enum('frequency', ['monthly'])->default('monthly');
			$t->unsignedTinyInteger('day_of_month')->default(1);
			$t->date('start_date');
			$t->date('end_date')->nullable();
			$t->date('next_run_at');
			$t->timestamp('last_run_at')->nullable();
			$t->unsignedSmallInteger('payment_terms_days')->default(30);
			$t->text('notes')->nullable();
			$t->boolean('auto_send_email')->default(false);
			$t->boolean('is_active')->default(true);
			$t->uuid('created_by_user_id')->nullable();
			$t->timestamps();

			$t->index(['tenant_id', 'is_active', 'next_run_at'], 'brec_inv_tenant_active_next_idx');
			$t->foreign('relation_id')->references('id')->on('bookkeeping_relations');
		});

		Schema::create('bookkeeping_recurring_invoice_lines', function (Blueprint $t) {
			$t->bigIncrements('id');
			$t->uuid('recurring_invoice_id');
			$t->string('description', 500);
			$t->decimal('quantity', 10, 2)->default(1);
			$t->decimal('unit_price', 12, 2);
			$t->unsignedBigInteger('vat_rate_id')->nullable();
			$t->unsignedSmallInteger('sort_order')->default(0);

			$t->index(['recurring_invoice_id', 'sort_order'], 'brec_inv_lines_ri_sort_idx');
			$t->foreign('recurring_invoice_id', 'brec_inv_lines_ri_fk')->references('id')->on('bookkeeping_recurring_invoices')->cascadeOnDelete();
			$t->foreign('vat_rate_id', 'brec_inv_lines_vat_fk')->references('id')->on('bookkeeping_vat_rates')->nullOnDelete();
		});

		Schema::table('bookkeeping_invoices', function (Blueprint $t) {
			$t->uuid('recurring_template_id')->nullable()->after('created_by_user_id');
			$t->index(['tenant_id', 'recurring_template_id'], 'binv_tenant_rec_tpl_idx');
			$t->foreign('recurring_template_id', 'binv_rec_tpl_fk')->references('id')->on('bookkeeping_recurring_invoices')->nullOnDelete();
		});
	}

	public function down(): void
	{
		Schema::table('bookkeeping_invoices', function (Blueprint $t) {
			$t->dropForeign('binv_rec_tpl_fk');
			$t->dropIndex('binv_tenant_rec_tpl_idx');
			$t->dropColumn('recurring_template_id');
		});
		Schema::dropIfExists('bookkeeping_recurring_invoice_lines');
		Schema::dropIfExists('bookkeeping_recurring_invoices');
	}
};
