<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Echte feiten per plaats, als basis onder de plaatspagina's.
 *
 * config/nl_places.php kent per plaats alleen naam + provincie. De teksten
 * konden daardoor alleen in formulering verschillen, niet in inhoud: twee
 * willekeurige plaatspagina's deelden 96% van hun woorden. Google haalde ze
 * daarom niet op ("gevonden, momenteel niet geïndexeerd", 684 stuks).
 *
 * Deze tabel vult dat gat met gegevens die per plaats écht verschillen en
 * controleerbaar zijn: gemeente en coördinaten uit de PDOK-locatieserver
 * (open data, geen sleutel nodig), en daaruit afgeleid de afstand tot de
 * vestiging en de werkelijke buurplaatsen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_place_facts', function (Blueprint $table) {
            $table->string('slug', 120)->primary();
            $table->string('naam', 120);
            $table->string('gemeente', 120)->nullable();
            $table->string('provincie', 80)->nullable();
            $table->decimal('lat', 9, 6)->nullable();
            $table->decimal('lon', 9, 6)->nullable();
            // Hemelsbreed, in hele kilometers — genoeg voor "op 12 km van".
            $table->unsignedSmallInteger('afstand_km')->nullable();
            // Slugs van de dichtstbijzijnde plaatsen, komma-gescheiden.
            $table->string('buren', 255)->nullable();
            $table->unsignedInteger('inwoners')->nullable();
            $table->string('bron', 40)->default('pdok');
            $table->timestamp('opgehaald_op')->nullable();
            $table->index('gemeente');
            $table->index('provincie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_place_facts');
    }
};
