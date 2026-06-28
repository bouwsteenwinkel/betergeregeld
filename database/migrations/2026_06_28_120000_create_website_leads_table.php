<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Website-leads: bedrijven die we breed benaderen / die zich aanmelden om een
 * website te laten maken. Backbone voor de intake-flow, het CRM en de outreach.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_leads', function (Blueprint $table) {
            $table->id();

            // Bedrijf + contact
            $table->string('company')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();

            // Locatie (voor de 50 km-Bussum-afspraaklogica)
            $table->string('postcode', 12)->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->unsignedInteger('distance_km')->nullable();   // hemelsbreed vanaf Bussum
            $table->boolean('within_radius')->nullable();         // <= 50 km

            // Branche (sector) — bepaalt o.a. welke voorbeeldsite we vooraf maken.
            $table->string('branche')->nullable();                // bv. horeca, kapper, loodgieter, ...
            // Kanaal/campagne waar de lead vandaan komt (alle kanalen → 1 admin).
            $table->string('channel', 60)->nullable();            // bv. branche-landing, ads, koude-acquisitie

            // Aanvraag
            $table->string('current_website')->nullable();        // bestaande site (indien)
            $table->text('message')->nullable();                  // wensen
            $table->string('source', 40)->default('intake');      // intake | outreach | manual

            // Voorbeeldsite (vooraf klaargezet, getoond tijdens de afspraak)
            $table->string('preview_url')->nullable();
            $table->string('preview_status', 16)->nullable();     // todo|building|ready|sent

            // Pijplijn
            $table->string('status', 32)->default('new');         // new|contacted|appointment|quoted|won|lost
            $table->string('assigned_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('contacted_at')->nullable();

            // Afspraak (bezoek <=50km Bussum, anders Google Meet)
            $table->dateTime('appointment_at')->nullable();
            $table->string('appointment_type', 16)->nullable();   // onsite | meet
            $table->string('appointment_status', 16)->nullable(); // requested|confirmed|done|cancelled
            $table->string('meet_link')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('appointment_at');
            $table->index('email');
            $table->index('branche');
            $table->index('channel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_leads');
    }
};
