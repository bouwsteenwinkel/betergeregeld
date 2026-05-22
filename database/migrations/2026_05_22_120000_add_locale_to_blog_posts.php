<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Maak blog_posts multi-lingual.
 *
 * Bestaande 117 posts blijven NL (default). Vertalingen krijgen
 * locale='en' + translation_of_post_id wijst naar de NL-parent.
 *
 * Slugs zijn niet meer globaal uniek — een EN-versie van een post mag
 * dezelfde slug hebben als de NL. Daarom: unique (locale, slug) i.p.v.
 * unique (slug).
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::table('blog_posts', function (Blueprint $t) {
			$t->string('locale', 5)->default('nl')->after('category_id');
			$t->unsignedBigInteger('translation_of_post_id')->nullable()->after('locale');
			$t->index('locale');
			$t->index('translation_of_post_id');
		});

		// Bestaande slug-uniqueness opheffen + locale-aware vervangen.
		// Indexnaam kan per Laravel-versie verschillen; we proberen beide.
		try {
			Schema::table('blog_posts', function (Blueprint $t) {
				$t->dropUnique('blog_posts_slug_unique');
			});
		} catch (\Throwable $e) {
			// Index bestond niet onder deze naam — probeer via raw SQL.
			try {
				DB::statement('ALTER TABLE blog_posts DROP INDEX blog_posts_slug_unique');
			} catch (\Throwable $e2) { /* ignore */ }
		}

		Schema::table('blog_posts', function (Blueprint $t) {
			$t->unique(['locale', 'slug'], 'blog_posts_locale_slug_unique');
		});
	}

	public function down(): void
	{
		Schema::table('blog_posts', function (Blueprint $t) {
			$t->dropUnique('blog_posts_locale_slug_unique');
			$t->dropIndex(['locale']);
			$t->dropIndex(['translation_of_post_id']);
			$t->dropColumn(['locale', 'translation_of_post_id']);
			$t->unique('slug');
		});
	}
};
