<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Eén blok op de pagina van een channel-site. De volgorde (sort) is sleepbaar in
 * de admin. 'type' kiest het blok-type uit de bibliotheek; 'content'/'style' zijn
 * per type. 'status' = de checklist (placeholder → in bewerking → klaar). 'locked'
 * markeert funnel-blokken die niet verwijderd mogen worden (bv. de lead-wizard).
 * 'design_notes' = opdracht voor de designer/Claude bij het bespoke stylen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_site_id')->constrained('channel_sites')->cascadeOnDelete();
            $table->string('type');                       // hero | features | faq | ...
            $table->string('block_key');                  // slug, uniek per site (view-resolutie)
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('enabled')->default(true);
            $table->boolean('locked')->default(false);    // funnel-blok: niet verwijderbaar
            $table->string('status')->default('placeholder'); // placeholder | bewerking | klaar
            $table->json('content')->nullable();
            $table->json('style')->nullable();
            $table->text('design_notes')->nullable();
            $table->timestamps();

            $table->index(['channel_site_id', 'sort']);
            $table->unique(['channel_site_id', 'block_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_blocks');
    }
};
