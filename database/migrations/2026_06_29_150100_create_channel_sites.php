<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Eén concrete channel-site. Hangt aan een branche (voor de blueprint/standaard),
 * heeft een eigen domein + status (draft/live), eigen thema-/merk-/header-overrides
 * en SEO-meta. De feitelijke pagina-inhoud zit in channel_blocks.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_branche_id')->nullable()->constrained('channel_branches')->nullOnDelete();
            $table->string('key')->unique();           // bv. 'dameskapper'
            $table->string('name');
            $table->string('domain')->nullable();      // productie-host, of null zolang concept
            $table->string('status')->default('draft'); // draft | live
            $table->string('locale', 5)->default('nl');
            $table->json('theme')->nullable();         // override van branche-thema
            $table->json('brand')->nullable();         // logo/telefoon/email/adres/endorsement
            $table->json('meta')->nullable();          // home_title / home_description
            $table->json('header')->nullable();        // site-specifieke header (logo/menu/cta)
            $table->string('legacy_view')->nullable(); // tijdelijke bespoke blade (transitie)
            $table->timestamps();

            $table->index(['status', 'domain']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_sites');
    }
};
