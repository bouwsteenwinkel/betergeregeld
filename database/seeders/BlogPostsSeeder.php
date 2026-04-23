<?php

namespace Database\Seeders;

use Database\Seeders\Blog\BlogAccessGuardSeeder;
use Database\Seeders\Blog\BlogAccessReviewsSeeder;
use Database\Seeders\Blog\BlogBookkeepingSeeder;
use Database\Seeders\Blog\BlogComplianceSeeder;
use Database\Seeders\Blog\BlogIntroSeeder;
use Database\Seeders\Blog\BlogM365Seeder;
use Database\Seeders\Blog\BlogOffboardingSeeder;
use Database\Seeders\Blog\BlogPrivacySeeder;
use Database\Seeders\Blog\BlogSecuritySeeder;
use Illuminate\Database\Seeder;

/**
 * Orchestrates all 9 blog-cluster seeders in the right order:
 * AccessGuard first, then clusters that reuse its slugs with their own
 * categories (offboarding, access-reviews). The upsert-on-slug pattern
 * in BlogSeedHelper means the last run wins for category assignment.
 */
class BlogPostsSeeder extends Seeder
{
	public function run(): void
	{
		$this->call([
			BlogIntroSeeder::class,
			BlogAccessGuardSeeder::class,
			BlogComplianceSeeder::class,
			BlogOffboardingSeeder::class,
			BlogAccessReviewsSeeder::class,
			BlogM365Seeder::class,
			BlogBookkeepingSeeder::class,
			BlogPrivacySeeder::class,
			BlogSecuritySeeder::class,
		]);
	}
}
