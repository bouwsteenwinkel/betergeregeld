<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('bookkeeping_relations', function (Blueprint $t) {
			$t->bigIncrements('id');
			$t->uuid('tenant_id');
			$t->string('name', 190);
			$t->enum('type', ['client', 'supplier', 'both'])->default('both');
			$t->string('email', 190)->nullable();
			$t->string('phone', 50)->nullable();
			$t->string('kvk_number', 20)->nullable();
			$t->string('vat_number', 20)->nullable();
			$t->string('iban', 34)->nullable();
			$t->string('address', 190)->nullable();
			$t->string('postal_code', 16)->nullable();
			$t->string('city', 120)->nullable();
			$t->char('country', 2)->nullable();
			$t->text('notes')->nullable();
			$t->boolean('is_active')->default(true);
			$t->timestamps();

			$t->index(['tenant_id', 'type', 'is_active']);
			$t->index(['tenant_id', 'name']);
		});

		Schema::table('bookkeeping_transactions', function (Blueprint $t) {
			$t->unsignedBigInteger('relation_id')->nullable()->after('category_id');
			$t->index(['tenant_id', 'relation_id']);
			$t->foreign('relation_id')->references('id')->on('bookkeeping_relations')->nullOnDelete();
		});
	}

	public function down(): void
	{
		Schema::table('bookkeeping_transactions', function (Blueprint $t) {
			$t->dropForeign(['relation_id']);
			$t->dropIndex(['tenant_id', 'relation_id']);
			$t->dropColumn('relation_id');
		});
		Schema::dropIfExists('bookkeeping_relations');
	}
};
