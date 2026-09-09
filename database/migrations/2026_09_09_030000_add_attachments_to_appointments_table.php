<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * De Drive-bestanden die aan de uitnodiging hangen.
 *
 * Alleen de id's van de gekozen bestanden; naam en link halen we bij Google op
 * als we ze nodig hebben. Een naam die wij bewaren loopt uit de pas zodra iemand
 * het bestand in Drive hernoemt, en dan staat er in onze administratie iets
 * anders dan in de uitnodiging.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->json('attachments')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
    }
};
