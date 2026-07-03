<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cache van lokale bedrijven (Google Places) per branche + plaats, voor de
 * branche-gerichte "bedrijven in de regio"-sectie op de /plaatsen-pagina's.
 * Voorkomt herhaalde API-calls (kosten/quota). Eén rij per (branche, plaats).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_place_listings', function (Blueprint $table) {
            $table->id();
            $table->string('branche_key')->index();
            $table->string('place_slug')->index();
            $table->json('listings')->nullable();     // genormaliseerde bedrijven
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();
            $table->unique(['branche_key', 'place_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_place_listings');
    }
};
