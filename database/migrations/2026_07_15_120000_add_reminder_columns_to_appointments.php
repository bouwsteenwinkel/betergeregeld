<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Twee losse kolommen en niet één 'reminder_sent_at': de 24u- en de
            // 1u-herinnering zijn onafhankelijk, en met één kolom zou de eerste
            // verzending de tweede permanent blokkeren.
            $table->dateTime('reminder_24h_sent_at')->nullable()->after('cancel_token');
            $table->dateTime('reminder_1h_sent_at')->nullable()->after('reminder_24h_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['reminder_24h_sent_at', 'reminder_1h_sent_at']);
        });
    }
};
