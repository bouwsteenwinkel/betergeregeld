<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI-kwaliteitscheck — datamodel.
 *
 *   monitored_pages   pagina's van klantwebsites die we periodiek scannen.
 *                     Gekoppeld aan een site (seo_properties — de site-entiteit
 *                     uit de sites-laag).
 *   quality_scans     één scan-run per pagina: status, timing, http, hash (om
 *                     ongewijzigde pagina's over te slaan), AI-kosten en score.
 *   quality_findings  individuele bevindingen (deterministisch of AI) per scan.
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::create('monitored_pages', function (Blueprint $t) {
			$t->id();
			$t->unsignedBigInteger('site_id'); // seo_properties.id (de site)
			$t->string('url', 500);
			$t->string('label', 120);
			$t->boolean('is_active')->default(true);
			$t->enum('scan_frequency', ['daily', 'weekly', 'monthly'])->default('weekly');
			$t->timestamps();

			$t->index(['site_id', 'is_active'], 'monpage_site_active_idx');
			$t->foreign('site_id')->references('id')->on('seo_properties')->cascadeOnDelete();
		});

		Schema::create('quality_scans', function (Blueprint $t) {
			$t->id();
			$t->unsignedBigInteger('monitored_page_id');
			$t->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
			$t->timestamp('started_at')->nullable();
			$t->timestamp('completed_at')->nullable();
			$t->unsignedSmallInteger('http_status')->nullable();
			$t->unsignedInteger('fetch_duration_ms')->nullable();
			$t->char('raw_input_hash', 64)->nullable();          // SHA-256 van de gedestilleerde input
			$t->string('ai_model', 80)->nullable();
			$t->unsignedInteger('ai_input_tokens')->nullable();
			$t->unsignedInteger('ai_output_tokens')->nullable();
			$t->unsignedTinyInteger('score')->nullable();        // 0-100
			$t->text('error_message')->nullable();
			$t->timestamps();

			$t->index(['monitored_page_id', 'status'], 'qscan_page_status_idx');
			$t->index('raw_input_hash', 'qscan_hash_idx');
			$t->foreign('monitored_page_id')->references('id')->on('monitored_pages')->cascadeOnDelete();
		});

		Schema::create('quality_findings', function (Blueprint $t) {
			$t->id();
			$t->unsignedBigInteger('quality_scan_id');
			$t->string('check_id', 64);
			$t->enum('source', ['deterministic', 'ai']);
			$t->enum('status', ['pass', 'warn', 'fail']);
			$t->enum('severity', ['laag', 'middel', 'hoog']);
			$t->text('finding');
			$t->text('suggestion')->nullable();
			// element = korte citatie van het betreffende fragment. Varchar (geen text)
			// zodat het in de unieke index past die dubbele bevindingen voorkomt.
			$t->string('element', 500)->nullable();
			$t->timestamps();

			$t->unique(['quality_scan_id', 'check_id', 'element'], 'qfind_unique');
			$t->foreign('quality_scan_id')->references('id')->on('quality_scans')->cascadeOnDelete();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('quality_findings');
		Schema::dropIfExists('quality_scans');
		Schema::dropIfExists('monitored_pages');
	}
};
