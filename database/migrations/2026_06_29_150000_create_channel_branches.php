<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Branche = sjabloon voor een groep channel-sites. Definieert de standaard-
 * blokkenlijst (blueprint), het standaardthema en de header-opzet. Een nieuwe
 * site van deze branche wordt uit de blueprint gegenereerd (= "de basis").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_branches', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();          // bv. 'kapper'
            $table->string('name');                    // bv. 'Kapper & beauty'
            $table->string('lead_branche')->default('overig'); // map → WebsiteLead::BRANCHES
            $table->json('theme')->nullable();         // default kleur/font-tokens
            $table->json('header')->nullable();        // default header-opzet (menu/cta)
            $table->json('blueprint')->nullable();     // geordende standaard-bloklijst
            $table->json('intake')->nullable();        // wizard: stijl-opties + features
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_branches');
    }
};
