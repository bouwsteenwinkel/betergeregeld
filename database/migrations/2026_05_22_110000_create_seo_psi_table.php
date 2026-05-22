<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SEO — PageSpeed Insights daily metrics per (property, URL, strategy).
 * Patroon overgenomen van BSW V3's bsw_seo_psi_daily. Trackt Core Web
 * Vitals + Lighthouse-scores over de tijd zodat een trage trend zichtbaar
 * wordt vóórdat het in GSC's ranking effect heeft.
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::create('seo_psi_daily', function (Blueprint $t) {
			$t->id();
			$t->unsignedBigInteger('property_id');
			$t->string('url', 500);
			$t->enum('strategy', ['mobile', 'desktop']);
			$t->date('date');

			// Core Web Vitals (uit field-data, anders lab)
			$t->unsignedInteger('lcp_ms')->nullable();
			$t->decimal('cls', 6, 3)->nullable();
			$t->unsignedInteger('inp_ms')->nullable();
			$t->unsignedInteger('ttfb_ms')->nullable();

			// Lighthouse category-scores (0-100)
			$t->unsignedTinyInteger('performance_score')->nullable();
			$t->unsignedTinyInteger('seo_score')->nullable();
			$t->unsignedTinyInteger('accessibility_score')->nullable();
			$t->unsignedTinyInteger('best_practices_score')->nullable();

			$t->string('error_message', 500)->nullable();
			$t->timestamp('created_at')->useCurrent();

			$t->unique(['property_id', 'url', 'strategy', 'date'], 'uniq_psi');
			$t->index(['property_id', 'date']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('seo_psi_daily');
	}
};
