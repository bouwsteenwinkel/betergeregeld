<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Groeidiamant-facet (groeifase) waarop de lead binnenkwam: website | webshop |
 * vindbaarheid | marketing | onderhoud (zie config/groeidiamant.php). Bepaalt
 * o.a. welke intake-blokken zijn getoond en wat de klant precies wil.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_leads', function (Blueprint $table) {
            $table->string('facet', 40)->default('website')->after('branche');
            $table->index('facet');
        });
    }

    public function down(): void
    {
        Schema::table('website_leads', function (Blueprint $table) {
            $table->dropIndex(['facet']);
            $table->dropColumn('facet');
        });
    }
};
