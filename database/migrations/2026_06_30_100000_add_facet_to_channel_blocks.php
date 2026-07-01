<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blokken kunnen nu aan een Groeidiamant-fase hangen: facet = null → "basis"
 * (zichtbaar in elke fase), of een fase-key → alleen in die fase. De pagina van
 * een fase = basis-blokken + die-fase-blokken, in sort-volgorde.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channel_blocks', function (Blueprint $table) {
            $table->string('facet')->nullable()->after('type');
            $table->index(['channel_site_id', 'facet']);
        });
    }

    public function down(): void
    {
        Schema::table('channel_blocks', function (Blueprint $table) {
            $table->dropIndex(['channel_site_id', 'facet']);
            $table->dropColumn('facet');
        });
    }
};
