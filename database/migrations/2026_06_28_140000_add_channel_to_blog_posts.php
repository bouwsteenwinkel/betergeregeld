<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Channel-scoping + AI-herkomst voor blogposts. channel = null → hoofd-site
 * (betergeregeld.nl); channel = <key> → een channel-site. AI-velden leggen
 * vast welke posts automatisch zijn gegenereerd en nog gereviewd moeten worden
 * (published_at = null = concept in de admin).
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::table('blog_posts', function (Blueprint $t) {
			$t->string('channel', 40)->nullable()->after('locale');
			$t->boolean('ai_generated')->default(false)->after('featured');
			$t->string('ai_model', 60)->nullable()->after('ai_generated');
			$t->json('ai_prompt_meta')->nullable()->after('ai_model');

			$t->index(['channel', 'published_at'], 'bp_channel_pub_idx');
		});
	}

	public function down(): void
	{
		Schema::table('blog_posts', function (Blueprint $t) {
			$t->dropIndex('bp_channel_pub_idx');
			$t->dropColumn(['channel', 'ai_generated', 'ai_model', 'ai_prompt_meta']);
		});
	}
};
