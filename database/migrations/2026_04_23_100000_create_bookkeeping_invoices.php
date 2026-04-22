<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('bookkeeping_tenant_settings', function (Blueprint $t) {
			$t->uuid('tenant_id')->primary();
			$t->string('company_name', 190)->nullable();
			$t->string('address', 190)->nullable();
			$t->string('postal_code', 16)->nullable();
			$t->string('city', 120)->nullable();
			$t->char('country', 2)->nullable();
			$t->string('kvk_number', 20)->nullable();
			$t->string('vat_number', 20)->nullable();
			$t->string('iban', 34)->nullable();
			$t->string('email', 190)->nullable();
			$t->string('phone', 50)->nullable();
			$t->unsignedSmallInteger('default_payment_terms_days')->default(30);
			$t->text('invoice_footer')->nullable();
			$t->timestamps();
		});

		Schema::create('bookkeeping_invoices', function (Blueprint $t) {
			$t->uuid('id')->primary();
			$t->uuid('tenant_id');
			$t->unsignedBigInteger('relation_id');
			$t->string('invoice_number', 24);
			$t->date('issue_date');
			$t->date('due_date')->nullable();
			$t->enum('status', ['draft', 'sent', 'paid', 'cancelled'])->default('draft');
			$t->decimal('subtotal', 12, 2)->default(0);
			$t->decimal('vat_amount', 12, 2)->default(0);
			$t->decimal('total', 12, 2)->default(0);
			$t->text('notes')->nullable();
			$t->string('reference', 80)->nullable();
			$t->unsignedSmallInteger('payment_terms_days')->nullable();
			$t->timestamp('sent_at')->nullable();
			$t->timestamp('paid_at')->nullable();
			$t->uuid('created_by_user_id')->nullable();
			$t->timestamps();

			$t->unique(['tenant_id', 'invoice_number']);
			$t->index(['tenant_id', 'status', 'issue_date']);
			$t->index(['tenant_id', 'relation_id']);
			$t->foreign('relation_id')->references('id')->on('bookkeeping_relations');
		});

		Schema::create('bookkeeping_invoice_lines', function (Blueprint $t) {
			$t->bigIncrements('id');
			$t->uuid('invoice_id');
			$t->string('description', 500);
			$t->decimal('quantity', 10, 2)->default(1);
			$t->decimal('unit_price', 12, 2);
			$t->unsignedBigInteger('vat_rate_id')->nullable();
			$t->unsignedSmallInteger('sort_order')->default(0);
			$t->timestamps();

			$t->foreign('invoice_id')->references('id')->on('bookkeeping_invoices')->cascadeOnDelete();
			$t->foreign('vat_rate_id')->references('id')->on('bookkeeping_vat_rates')->nullOnDelete();
			$t->index(['invoice_id', 'sort_order']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('bookkeeping_invoice_lines');
		Schema::dropIfExists('bookkeeping_invoices');
		Schema::dropIfExists('bookkeeping_tenant_settings');
	}
};
