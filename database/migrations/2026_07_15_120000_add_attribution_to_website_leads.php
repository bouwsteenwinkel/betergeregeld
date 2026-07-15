<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Klik-herkomst op de lead. Het verkoopmodel sluit telefonisch af, dus zonder deze
 * kolommen is er later geen offline conversion import naar Google Ads mogelijk en
 * leert Ads nooit welke zoekwoorden klanten opleveren.
 *
 * Lengtes zijn 1:1 overgenomen van de bestaande user_acquisition-tabel, zodat er in
 * dit project één conventie voor herkomst-velden blijft. Index op gclid omdat de
 * conversie-upload daarop matcht, en op utm_campaign voor de rapportage per campagne.
 * Alles nullable: verreweg de meeste leads komen niet uit een advertentie.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_leads', function (Blueprint $table) {
            // gbraid/wbraid vervangen de gclid bij iOS-verkeer zonder app-tracking-
            // toestemming; zonder die twee mist juist het iPhone-verkeer in de rapportage.
            $table->string('gclid', 120)->nullable()->index()->after('source');
            $table->string('gbraid', 120)->nullable()->after('gclid');
            $table->string('wbraid', 120)->nullable()->after('gbraid');
            $table->string('utm_source', 120)->nullable()->after('wbraid');
            $table->string('utm_medium', 120)->nullable()->after('utm_source');
            $table->string('utm_campaign', 200)->nullable()->index()->after('utm_medium');
            $table->string('utm_term', 200)->nullable()->after('utm_campaign');
            $table->string('utm_content', 200)->nullable()->after('utm_term');
        });
    }

    public function down(): void
    {
        Schema::table('website_leads', function (Blueprint $table) {
            $table->dropColumn([
                'gclid', 'gbraid', 'wbraid',
                'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
            ]);
        });
    }
};
