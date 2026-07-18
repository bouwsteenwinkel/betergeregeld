<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * First-party event-log voor de channel-/funnel-sites: onze eigen grondwaarheid van de
 * funnel-triggers (voorbeeld bekeken, planner geopend, afspraak geboekt, ...), zodat we
 * de Meta-/Google-cijfers kunnen controleren en zien hoeveel we door consent-weigering
 * missen. Bewust dataminimaal — in lijn met CaptureAdAttribution:
 *   - GEEN IP, GEEN user-agent, GEEN query-string, GEEN PII.
 *   - Groepering per bezoek via een sessie-scoped willekeurige ref (geen device-cookie).
 * Daarmee is dit een verwerking onder gerechtvaardigd belang (eigen funnel-meting) zonder
 * dat er informatie op het apparaat wordt geplaatst → geen aparte toestemming nodig.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_events', function (Blueprint $table) {
            $table->id();
            $table->string('event', 60)->index();          // preview_ready, planner_opened, appointment_booked, ...
            $table->string('site_key', 80)->nullable()->index();
            $table->string('visit_ref', 32)->nullable()->index(); // sessie-scoped, hash — groepeert events per bezoek
            $table->string('path', 255)->nullable();        // pad zonder query-string
            $table->json('params')->nullable();             // niet-persoonlijke event-data (seconds, step, ...)
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_events');
    }
};
