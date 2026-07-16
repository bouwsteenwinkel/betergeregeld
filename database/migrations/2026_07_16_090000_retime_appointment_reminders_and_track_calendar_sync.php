<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Twee wijzigingen aan appointments:
 *
 * 1. De herinnerings-cadans gaat van "24 uur + 1 uur vooraf" naar "2 dagen vooraf +
 *    de ochtend van de afspraak". De oude kolomnamen coderen de oude cadans en zijn
 *    niet te hergebruiken zonder te liegen over wat er verstuurd is, dus: nieuwe
 *    kolommen, oude weg.
 *
 *    De 24u-stempel gaat wél mee naar reminder_2d_sent_at. Allebei zijn "de vroege
 *    herinnering, ruim genoeg vooraf om te verzetten", en de mail die eruit gaat is
 *    letterlijk dezelfde. Zonder deze overzetting start de kolom op NULL en stuurt de
 *    eerste run na de deploy die mail nóg een keer, aan iedereen met een afspraak binnen
 *    twee dagen die 'm gisteren al kreeg.
 *
 *    De 1u-stempel gaat NIET mee: die hoort bij een moment (vlak voor aanvang) dat in de
 *    nieuwe cadans niet meer bestaat. Wie gisteren zijn 24u-mail kreeg en vandaag een
 *    afspraak heeft, krijgt vanochtend gewoon zijn dag-van-mail. Dat is geen dubbele
 *    mail, maar een andere.
 *
 * 2. calendar_synced_at / calendar_error maken zichtbaar of het agenda-event er echt
 *    is gekomen. Dat was eerder onzichtbaar: een mislukte Google-call liet de afspraak
 *    gewoon staan zonder enig spoor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dateTime('reminder_2d_sent_at')->nullable()->after('cancel_token');
            $table->dateTime('reminder_day_of_sent_at')->nullable()->after('reminder_2d_sent_at');
            $table->dateTime('calendar_synced_at')->nullable()->after('meet_url');
            $table->string('calendar_error', 255)->nullable()->after('calendar_synced_at');
        });

        // De vroege herinnering die al ging, telt als de vroege herinnering. Anders
        // stuurt de eerste run na de deploy 'm nog eens.
        DB::table('appointments')->whereNotNull('reminder_24h_sent_at')->update([
            'reminder_2d_sent_at' => DB::raw('reminder_24h_sent_at'),
        ]);

        // Afspraken die al een agenda-event hebben, hebben aantoonbaar gesynct; zonder
        // deze stempel zou het admin-overzicht ze na de deploy als "niet gesynct" tonen.
        DB::table('appointments')->whereNotNull('google_event_id')->update([
            'calendar_synced_at' => DB::raw('created_at'),
        ]);

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['reminder_24h_sent_at', 'reminder_1h_sent_at']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dateTime('reminder_24h_sent_at')->nullable()->after('cancel_token');
            $table->dateTime('reminder_1h_sent_at')->nullable()->after('reminder_24h_sent_at');
            $table->dropColumn(['reminder_2d_sent_at', 'reminder_day_of_sent_at', 'calendar_synced_at', 'calendar_error']);
        });
    }
};
