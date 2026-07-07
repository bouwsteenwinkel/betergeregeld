<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Koppelt een agent aan een transport-kanaal:
 *   voice    → binding = DID/telefoonnummer (per-tenant routering vanuit 3CX/SIP)
 *   web      → binding = widget-key
 *   whatsapp → binding = WhatsApp-nummer
 *   email    → binding = mailbox
 */
return new class extends Migration
{
	public function up(): void
	{
		Schema::create('ai_agent_channels', function (Blueprint $table) {
			$table->id();
			$table->uuid('tenant_id')->index();
			$table->foreignId('ai_agent_id')->constrained('ai_agents')->cascadeOnDelete();
			$table->string('channel_type');            // voice | web | whatsapp | email
			$table->string('binding')->nullable();     // DID / widget-key / wa-nummer / mailbox
			$table->json('config')->nullable();
			$table->boolean('is_active')->default(true);
			$table->timestamps();

			$table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
			$table->unique(['channel_type', 'binding']); // een DID/binding hoort bij max. één agent
			$table->index(['tenant_id', 'channel_type']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('ai_agent_channels');
	}
};
