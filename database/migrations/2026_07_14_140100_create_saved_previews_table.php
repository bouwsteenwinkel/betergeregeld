<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Door de klant opgeslagen voorbeeldsites, gekoppeld aan een website-lead (= account).
 * Een klant kan meerdere previews bewaren en er één als favoriet markeren. De preview
 * zelf blijft een channel_sites-rij (key preview-...); site_key verwijst ernaar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_previews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_lead_id')->constrained('website_leads')->cascadeOnDelete();
            $table->string('site_key')->index();   // = channel_sites.key (preview-...)
            $table->boolean('favorite')->default(false);
            $table->timestamps();

            $table->unique(['website_lead_id', 'site_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_previews');
    }
};
