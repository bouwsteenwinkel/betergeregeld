<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kanaal-onafhankelijke gespreks-root. Eén rij per gesprek, ongeacht of het
 * via telefoon, webchat, WhatsApp of e-mail binnenkwam. contact_* verwijst
 * (morph) naar bv. een BookkeepingRelation.
 */
return new class extends Migration
{
	public function up(): void
	{
		Schema::create('ai_conversations', function (Blueprint $table) {
			$table->id();
			$table->uuid('tenant_id')->index();
			$table->foreignId('ai_agent_id')->nullable()->constrained('ai_agents')->nullOnDelete();
			$table->string('channel_type');                 // voice | web | whatsapp | email
			$table->nullableMorphs('contact');              // contact_id + contact_type (bv. BookkeepingRelation)
			$table->string('status')->default('active');    // active | ended | failed
			$table->string('locale', 8)->default('nl');
			$table->unsignedInteger('message_count')->default(0);
			$table->decimal('cost_eur', 10, 4)->default(0);
			$table->string('sentiment')->nullable();        // positive | neutral | negative
			$table->text('summary')->nullable();
			$table->timestamp('started_at')->nullable();
			$table->timestamp('ended_at')->nullable();
			$table->timestamps();

			$table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
			$table->index(['tenant_id', 'channel_type', 'status']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('ai_conversations');
	}
};
