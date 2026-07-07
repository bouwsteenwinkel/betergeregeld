<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Beurten binnen een gesprek. role=tool legt een function-call + resultaat vast.
 * tenant_id is gedenormaliseerd voor snelle per-tenant scoping/analytics.
 */
return new class extends Migration
{
	public function up(): void
	{
		Schema::create('ai_messages', function (Blueprint $table) {
			$table->id();
			$table->uuid('tenant_id')->index();
			$table->foreignId('ai_conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
			$table->string('role');                         // system | user | assistant | tool
			$table->longText('content')->nullable();
			$table->string('tool_name')->nullable();        // bij function-calling
			$table->json('tool_payload')->nullable();       // args + resultaat
			$table->unsignedInteger('input_tokens')->nullable();
			$table->unsignedInteger('output_tokens')->nullable();
			$table->string('audio_ref')->nullable();        // S3-referentie audiofragment (spraak)
			$table->timestamps();

			$table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
			$table->index(['ai_conversation_id', 'id']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('ai_messages');
	}
};
