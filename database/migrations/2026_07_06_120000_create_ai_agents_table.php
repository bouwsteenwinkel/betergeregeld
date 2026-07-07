<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI-Assistent: per tenant één of meer agents (persona + model + stem).
 * Kanaal-onafhankelijk brein; kanalen koppelen via ai_agent_channels.
 */
return new class extends Migration
{
	public function up(): void
	{
		Schema::create('ai_agents', function (Blueprint $table) {
			$table->id();
			$table->uuid('tenant_id')->index();
			$table->string('name');
			$table->string('locale', 8)->default('nl');
			$table->string('model')->default('gpt-realtime-mini'); // OpenAI Realtime model
			$table->string('voice')->nullable();                   // stem-id (realtime)
			$table->text('system_prompt')->nullable();             // persona / instructies
			$table->text('business_context')->nullable();          // extra bedrijfsinfo in prompt
			$table->decimal('temperature', 3, 2)->default(0.70);
			$table->boolean('is_active')->default(true);
			$table->timestamps();

			$table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('ai_agents');
	}
};
