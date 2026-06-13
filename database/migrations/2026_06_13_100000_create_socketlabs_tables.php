<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SocketLabs e-mail-monitoring: ruwe deliverability-events (uit de event-
 * webhooks) + één status-rij die de alert-toestand per dimensie bijhoudt
 * (state-based alerting, net als de overige monitor-onderdelen).
 */
return new class extends Migration
{
	public function up(): void
	{
		Schema::create('socketlabs_events', function (Blueprint $table) {
			$table->id();
			$table->string('type', 32)->index();          // Queued/Deferred/Delivered/Failed/Complaint
			$table->string('message_id')->nullable();
			$table->string('server_id', 32)->nullable();
			$table->string('to_address')->nullable();
			$table->string('from_address')->nullable();
			$table->string('subject')->nullable();
			$table->string('failure_type', 32)->nullable(); // Permanent/Temporary/Suppressed
			$table->string('failure_code', 16)->nullable();
			$table->string('deferral_code', 16)->nullable();
			$table->text('reason')->nullable();
			$table->timestamp('occurred_at')->nullable()->index();
			$table->timestamp('created_at')->nullable();

			$table->index(['type', 'occurred_at']);
		});

		Schema::create('socketlabs_status', function (Blueprint $table) {
			$table->id();
			// per dimensie: 'ok' of 'alert' (null = nog niet geïnitialiseerd)
			$table->string('queue_state', 16)->nullable();
			$table->string('failure_state', 16)->nullable();
			$table->string('complaint_state', 16)->nullable();
			$table->string('silence_state', 16)->nullable();
			$table->timestamp('queue_alerted_at')->nullable();
			$table->timestamp('failure_alerted_at')->nullable();
			$table->timestamp('complaint_alerted_at')->nullable();
			$table->timestamp('silence_alerted_at')->nullable();
			$table->timestamp('last_event_at')->nullable();   // laatste ontvangen event (any type)
			$table->timestamp('last_evaluated_at')->nullable();
			$table->json('counts')->nullable();               // laatste venster-snapshot voor het dashboard
			// API-poll (vangnet): bereikbaarheid van de SocketLabs v2 API.
			$table->boolean('api_reachable')->nullable();
			$table->timestamp('api_checked_at')->nullable();
			$table->string('api_state', 16)->nullable();
			$table->timestamp('api_alerted_at')->nullable();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('socketlabs_events');
		Schema::dropIfExists('socketlabs_status');
	}
};
