<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * De branche-blueprint (standaard-blokkenlijst) wordt een echte relatie i.p.v. een
 * JSON-veld, zodat de admin de volgorde via slepen DIRECT kan opslaan (relatie =
 * meteen gepersisteerd), net als de blokken per site. De bestaande JSON-blueprints
 * worden hier eenmalig overgezet naar rijen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_blueprint_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_branche_id')->constrained('channel_branches')->cascadeOnDelete();
            $table->string('type');
            $table->unsignedInteger('sort')->default(0);
            $table->string('status')->default('placeholder');
            $table->boolean('locked')->default(false);
            $table->timestamps();

            $table->index(['channel_branche_id', 'sort']);
        });

        // Bestaande JSON-blueprints → rijen.
        foreach (DB::table('channel_branches')->get() as $branche) {
            $blueprint = json_decode($branche->blueprint ?? '[]', true) ?: [];
            $sort = 0;
            foreach ($blueprint as $b) {
                if (empty($b['type'])) {
                    continue;
                }
                DB::table('channel_blueprint_blocks')->insert([
                    'channel_branche_id' => $branche->id,
                    'type'    => $b['type'],
                    'sort'    => $sort += 10,
                    'status'  => $b['status'] ?? 'placeholder',
                    'locked'  => ! empty($b['locked']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_blueprint_blocks');
    }
};
