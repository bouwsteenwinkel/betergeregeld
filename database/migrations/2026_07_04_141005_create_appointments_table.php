<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('type')->default('meet');            // meet (fase 1) | onsite (later)
            $table->string('status')->default('booked');        // held | booked | cancelled | completed | no_show
            $table->dateTime('hold_expires_at')->nullable();    // voor tijdelijke reservering tijdens invullen
            $table->string('google_event_id')->nullable();
            $table->string('meet_url')->nullable();
            $table->string('cancel_token', 64)->nullable()->unique();
            $table->string('source_site')->nullable();          // channel-key waar geboekt is
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['starts_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
