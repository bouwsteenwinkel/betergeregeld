<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Archief van de intake achter een voorbeeldsite die verliep zonder bewaard te worden.
 * De preview zelf (channel_sites + blocks) gaat na 48u weg; wat de bezoeker intypte is
 * de enige overgebleven waarde en beantwoordt de vraag "wie gebruikt de tool maar
 * converteert niet, en hoeveel zijn dat er".
 *
 * Alleen NIET-opgeëiste previews landen hier: bewaart iemand zijn voorbeeld, dan is er
 * een website_leads-rij met alles erin en blijft de site staan.
 *
 * Over persoonsgegevens: bij een niet-bewaarde preview bestaat er geen e-mailadres, en
 * IP/user-agent leggen we bewust niet vast, dus er zit geen direct identificerend veld
 * in deze tabel. Let wel: company/usp zijn vrije tekst, en bij een eenmanszaak kan een
 * handelsnaam ("Jan de Vries Loodgieter") herleidbaar zijn tot een natuurlijk persoon.
 * Behandel dit dus als bedrijfsdata met die kanttekening, niet als gegarandeerd anoniem.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preview_intakes', function (Blueprint $table) {
            $table->id();

            // = channel_sites.key (preview-...). Uniek zodat een herhaalde archiveerslag
            // (cleanup die de site daarna niet kon verwijderen) geen dubbele rij oplevert.
            $table->string('site_key', 64)->unique();

            $table->string('company', 190)->nullable();
            $table->string('business_type', 190)->nullable();
            $table->string('place', 190)->nullable();
            $table->string('goal', 120)->nullable();
            $table->string('sfeer', 120)->nullable();
            $table->text('usp')->nullable();
            $table->text('key_services')->nullable();

            $table->string('source_channel', 120)->nullable()->index();

            // Wanneer de bezoeker de tool gebruikte; created_at = wanneer wij archiveerden.
            $table->timestamp('preview_created_at')->nullable()->index();
            $table->timestamp('expired_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preview_intakes');
    }
};
