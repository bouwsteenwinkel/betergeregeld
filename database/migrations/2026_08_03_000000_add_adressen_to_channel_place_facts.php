<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aantal adressen per woonplaats — de maat voor de grootte van een PLAATS.
 *
 * `inwoners` is een gemeentecijfer (CBS levert het niet per woonplaats), dus Anloo
 * en Annen krijgen daar beide 25.845 omdat ze in Aa en Hunze liggen. Daarmee is het
 * onbruikbaar om een dorp van een stad te onderscheiden. Het aantal adressen komt
 * uit de BAG via PDOK en is wél per woonplaats: Anloo 197, Annen 1.887, Beilen
 * 6.053, Bussum 19.514, Assen 39.100 (gemeten 03-08-2026).
 *
 * Waarom dat nodig was: de indexerings-gating stond op het aantal gevonden bedrijven,
 * maar die lijst is afgekapt op 9, waardoor alle 984 plaatsen in dezelfde bak vielen
 * en er niets te selecteren was.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channel_place_facts', function (Blueprint $table) {
            $table->unsignedInteger('adressen')->nullable()->after('inwoners');
        });
    }

    public function down(): void
    {
        Schema::table('channel_place_facts', function (Blueprint $table) {
            $table->dropColumn('adressen');
        });
    }
};
