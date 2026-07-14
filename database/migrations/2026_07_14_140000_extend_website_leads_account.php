<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Maakt van een website-lead een lichtgewicht, wachtwoordloos klant-account:
 *   consent          = opt-in voor reminder-mails (AVG)
 *   token_hash/_at   = sha256-token voor de persoonlijke "mijn voorbeelden"-link
 *   reminder_*       = cadence-tracking (bevestiging + 2 reminders, dag 1 en 4)
 *   unsubscribed_at  = afmelding (stopt reminders)
 *   saved_at         = moment waarop de klant een voorbeeld opsloeg
 * Alle kolommen zijn nullable/default, dus bestaande leads blijven geldig.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_leads', function (Blueprint $table) {
            $table->boolean('consent')->default(false)->after('source');
            $table->string('token_hash', 64)->nullable()->index()->after('consent');
            $table->timestamp('token_expires_at')->nullable()->after('token_hash');
            $table->unsignedTinyInteger('reminder_stage')->default(0)->after('token_expires_at');
            $table->timestamp('next_reminder_at')->nullable()->index()->after('reminder_stage');
            $table->timestamp('unsubscribed_at')->nullable()->after('next_reminder_at');
            $table->timestamp('saved_at')->nullable()->after('unsubscribed_at');
        });
    }

    public function down(): void
    {
        Schema::table('website_leads', function (Blueprint $table) {
            $table->dropColumn([
                'consent', 'token_hash', 'token_expires_at',
                'reminder_stage', 'next_reminder_at', 'unsubscribed_at', 'saved_at',
            ]);
        });
    }
};
