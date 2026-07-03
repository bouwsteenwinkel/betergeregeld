<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Plaatsen-theming per niche-branche. Elke branche krijgt eigen (SEO-)teksten
 * voor de /plaatsen-pagina's van z'n trigger-sites, geënt op de niche. Losstaand
 * van het algemene betergeregeld.com — dit hangt aan de channel-sites.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channel_branches', function (Blueprint $table) {
            $table->json('places')->nullable()->after('intake');
        });
    }

    public function down(): void
    {
        Schema::table('channel_branches', function (Blueprint $table) {
            $table->dropColumn('places');
        });
    }
};
