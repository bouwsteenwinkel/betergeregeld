<?php

namespace Database\Seeders\Blog;

use App\Models\Blog\BlogCategory;
use App\Models\Blog\BlogPost;
use App\Models\Blog\BlogTag;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * Shared helper for every blog-seeder file. Lets per-cluster seeders stay
 * narrow (just an array of posts) while the upsert logic lives in one place.
 *
 * Re-seeding behaviour: idempotent on slug. Existing posts get their
 * title/excerpt/body/tags refreshed; new ones get inserted; nothing is
 * deleted. Tag list is replaced in full on each run so cross-links stay
 * accurate.
 */
class BlogSeedHelper
{
	/**
	 * @param array<string, string|null> $categoryData  [slug, name, pillar_title, intro, sort_order]
	 * @param array<int, array<string, mixed>> $posts   Each: [slug,title,excerpt,body,tags[],is_pillar?,featured?,meta_title?,published_offset_days?]
	 */
	public static function seedCluster(array $categoryData, array $posts): void
	{
		$category = BlogCategory::updateOrCreate(
			['slug' => $categoryData['slug']],
			[
				'name' => $categoryData['name'],
				'pillar_title' => $categoryData['pillar_title'] ?? null,
				'intro' => $categoryData['intro'] ?? null,
				'sort_order' => $categoryData['sort_order'] ?? 100,
			],
		);

		$basePublished = CarbonImmutable::create(2025, 9, 1, 9, 0, 0);

		foreach ($posts as $p) {
			$offset = (int) ($p['published_offset_days'] ?? 0);
			$readingTime = max(2, (int) ceil(str_word_count(strip_tags($p['body'])) / 200));

			$post = BlogPost::updateOrCreate(
				['slug' => $p['slug']],
				[
					'category_id' => $category->id,
					'title' => $p['title'],
					'meta_title' => $p['meta_title'] ?? null,
					'excerpt' => $p['excerpt'],
					'body' => $p['body'],
					'reading_time_min' => $readingTime,
					'is_pillar' => (bool) ($p['is_pillar'] ?? false),
					'featured' => (bool) ($p['featured'] ?? false),
					'published_at' => $basePublished->addDays($offset),
				],
			);

			$tagIds = [];
			foreach (($p['tags'] ?? []) as $tagName) {
				$tag = BlogTag::updateOrCreate(
					['slug' => Str::slug($tagName)],
					['name' => $tagName],
				);
				$tagIds[] = $tag->id;
			}
			$post->tags()->sync($tagIds);
		}
	}
}
