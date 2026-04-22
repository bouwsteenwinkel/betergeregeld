<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('bookkeeping_categories', function (Blueprint $t) {
			$t->bigIncrements('id');
			$t->uuid('tenant_id')->nullable()->index();
			$t->string('name', 120);
			$t->enum('type', ['expense', 'income', 'both'])->default('expense');
			$t->boolean('is_active')->default(true);
			$t->unsignedSmallInteger('sort_order')->default(0);
			$t->timestamps();

			$t->index(['tenant_id', 'type', 'is_active']);
		});

		Schema::create('bookkeeping_vat_rates', function (Blueprint $t) {
			$t->bigIncrements('id');
			$t->uuid('tenant_id')->nullable()->index();
			$t->string('name', 32);
			$t->decimal('rate', 5, 2);
			$t->date('effective_from');
			$t->date('effective_to')->nullable();
			$t->boolean('is_default')->default(false);
			$t->timestamps();

			$t->index(['tenant_id', 'effective_from']);
		});

		Schema::create('bookkeeping_transactions', function (Blueprint $t) {
			$t->uuid('id')->primary();
			$t->uuid('tenant_id');
			$t->uuid('created_by_user_id')->nullable();
			$t->enum('type', ['expense', 'income']);
			$t->date('transaction_date');
			$t->decimal('amount', 12, 2);
			$t->unsignedBigInteger('vat_rate_id')->nullable();
			$t->boolean('vat_included')->default(true);
			$t->boolean('vat_deductible')->default(true);
			$t->unsignedBigInteger('category_id')->nullable();
			$t->string('description', 500);
			$t->string('counterparty', 190)->nullable();
			$t->string('invoice_number', 64)->nullable();
			$t->string('receipt_path', 500)->nullable();
			$t->timestamps();

			$t->index(['tenant_id', 'transaction_date']);
			$t->index(['tenant_id', 'type', 'transaction_date']);
			$t->index(['tenant_id', 'category_id']);
			$t->index(['tenant_id', 'invoice_number']);
			$t->foreign('vat_rate_id')->references('id')->on('bookkeeping_vat_rates')->nullOnDelete();
			$t->foreign('category_id')->references('id')->on('bookkeeping_categories')->nullOnDelete();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('bookkeeping_transactions');
		Schema::dropIfExists('bookkeeping_vat_rates');
		Schema::dropIfExists('bookkeeping_categories');
	}
};
