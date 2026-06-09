<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Houdt bij wanneer voor een klant het laatste PDF-rapport per e-mail is
 * verstuurd (maandelijks of handmatig). Voedt de "laatst verzonden"-weergave.
 */
return new class extends Migration
{
	public function up(): void
	{
		Schema::table('tenants', function (Blueprint $t) {
			$t->timestamp('report_sent_at')->nullable();
		});
	}

	public function down(): void
	{
		Schema::table('tenants', function (Blueprint $t) {
			$t->dropColumn('report_sent_at');
		});
	}
};
