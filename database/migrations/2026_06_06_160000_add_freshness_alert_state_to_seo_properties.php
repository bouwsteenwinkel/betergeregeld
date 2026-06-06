<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('seo_properties', function (Blueprint $table) {
            $table->string('freshness_alert_state', 16)->default('ok')->after('last_import_error');
            $table->timestamp('freshness_alerted_at')->nullable()->after('freshness_alert_state');
        });
    }

    public function down(): void
    {
        Schema::table('seo_properties', function (Blueprint $table) {
            $table->dropColumn(['freshness_alert_state', 'freshness_alerted_at']);
        });
    }
};
