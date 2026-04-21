<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('billing_intents', function (Blueprint $t) {
			$t->uuid('id')->primary();
			$t->uuid('tenant_id')->index();
			$t->uuid('user_id')->nullable()->index();
			$t->foreignId('plan_id');
			$t->enum('period', ['monthly', 'yearly'])->default('monthly');
			$t->decimal('amount', 10, 2);
			$t->string('currency', 3)->default('EUR');
			$t->enum('status', ['pending', 'open', 'paid', 'failed', 'cancelled', 'expired'])->default('pending');
			$t->string('mollie_payment_id', 64)->nullable()->index();
			$t->text('mollie_checkout_url')->nullable();
			$t->json('meta_json')->nullable();
			$t->timestamp('paid_at')->nullable();
			$t->timestamps();

			$t->index(['tenant_id', 'status']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('billing_intents');
	}
};
