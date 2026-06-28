<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Branche-gestuurde uitvraag + voorbereiding op Google Agenda/Meet-koppeling.
 *   answers  = antwoorden op de (deels branche-specifieke) intake-vragen
 *   features = gekozen gewenste functies (bv. online-afspraken, menu, reserveren)
 *   google_* = haakjes voor de latere agenda/meet-koppeling
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_leads', function (Blueprint $table) {
            $table->json('answers')->nullable()->after('message');
            $table->json('features')->nullable()->after('answers');
            $table->string('google_event_id')->nullable()->after('meet_link');
            $table->string('google_calendar_id')->nullable()->after('google_event_id');
        });
    }

    public function down(): void
    {
        Schema::table('website_leads', function (Blueprint $table) {
            $table->dropColumn(['answers', 'features', 'google_event_id', 'google_calendar_id']);
        });
    }
};
