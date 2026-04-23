<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('blog_categories', function (Blueprint $t) {
			$t->id();
			$t->string('slug', 140)->unique();
			$t->string('name', 160);
			$t->string('pillar_title', 200)->nullable();
			$t->text('intro')->nullable();
			$t->integer('sort_order')->default(100);
			$t->timestamps();
		});

		Schema::create('blog_tags', function (Blueprint $t) {
			$t->id();
			$t->string('slug', 80)->unique();
			$t->string('name', 80);
			$t->timestamps();
		});

		Schema::create('blog_posts', function (Blueprint $t) {
			$t->id();
			$t->unsignedBigInteger('category_id');
			$t->string('slug', 180)->unique();
			$t->string('title', 220);
			$t->string('meta_title', 220)->nullable();
			$t->string('excerpt', 320);
			$t->mediumText('body'); // HTML fragment
			$t->integer('reading_time_min')->default(5);
			$t->boolean('is_pillar')->default(false);
			$t->boolean('featured')->default(false);
			$t->timestamp('published_at')->nullable();
			$t->timestamps();

			$t->index(['category_id', 'published_at'], 'bp_cat_pub_idx');
			$t->index(['featured', 'published_at'], 'bp_feat_pub_idx');
			$t->index(['is_pillar', 'published_at'], 'bp_pil_pub_idx');
			$t->foreign('category_id')->references('id')->on('blog_categories')->cascadeOnDelete();
		});

		Schema::create('blog_post_tag', function (Blueprint $t) {
			$t->unsignedBigInteger('post_id');
			$t->unsignedBigInteger('tag_id');
			$t->primary(['post_id', 'tag_id']);
			$t->foreign('post_id')->references('id')->on('blog_posts')->cascadeOnDelete();
			$t->foreign('tag_id')->references('id')->on('blog_tags')->cascadeOnDelete();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('blog_post_tag');
		Schema::dropIfExists('blog_posts');
		Schema::dropIfExists('blog_tags');
		Schema::dropIfExists('blog_categories');
	}
};
