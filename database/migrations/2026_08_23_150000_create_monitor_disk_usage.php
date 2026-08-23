<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Schijfgebruik per map, als tijdreeks.
 *
 * De agent meet elke minuut alleen het TOTAAL van C:. Daardoor was op
 * 23-08-2026 wel te zien dát de schijf met ~1,45 GB per dag volliep, maar niet
 * waardoor — en dat is precies de vraag die je wilt beantwoorden voordat je
 * hem opruimt. Eén rij per map per meting, zodat je naast de stand ook de
 * groei per map kunt zien.
 *
 * Bewust dagelijks en niet per minuut: een map uitrekenen betekent hem
 * volledig doorlopen, en dat kost op een volle schijf minuten. Zie
 * tools/monitor-agent/schijfgebruik.ps1.
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::create('monitor_disk_usage', function (Blueprint $t) {
			$t->id();
			$t->uuid('server_id');
			$t->timestamp('measured_at')->index();

			// 'map' = hoofdmap van de schijf, 'vhost' = site-map,
			// 'logs' = logmap binnen een vhost, 'bestand' = los groot bestand.
			$t->string('soort', 16)->default('map');
			$t->string('pad', 400);
			$t->unsignedBigInteger('bytes');
			$t->timestamp('created_at')->nullable();

			$t->index(['server_id', 'measured_at']);
			$t->index(['server_id', 'pad']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('monitor_disk_usage');
	}
};
