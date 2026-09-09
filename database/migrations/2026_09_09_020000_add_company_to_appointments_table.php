<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bedrijfsnaam bij een afspraak.
 *
 * Bij een handmatig ingeplande afspraak weet je vaak wél met welk bedrijf je
 * spreekt, en dat is precies wat je wilt terugzien in je agenda: "Kennismaking
 * — Jan Jansen (Bakkerij Jansen)" zegt meer dan alleen een naam.
 *
 * Optioneel, want een particulier heeft er geen. Nullable dus, en de kolom komt
 * naast `name` te staan omdat ze samen gelezen worden.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('company')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('company');
        });
    }
};
