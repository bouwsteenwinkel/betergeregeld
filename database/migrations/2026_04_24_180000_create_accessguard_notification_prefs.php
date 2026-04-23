<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('accessguard_notification_prefs', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->char('user_id', 36);
			$t->boolean('digest_enabled')->default(true);
			$t->boolean('instant_critical_enabled')->default(true);
			$t->timestamp('last_digest_sent_at')->nullable();
			$t->string('unsubscribe_token', 64);
			$t->timestamps();

			$t->unique(['tenant_id', 'user_id'], 'agnp_tu_uq');
			$t->index(['unsubscribe_token'], 'agnp_utok_idx');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('accessguard_notification_prefs');
	}
};
