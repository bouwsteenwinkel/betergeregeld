<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Slug uniek per (locale, channel) i.p.v. (locale) globaal, zodat elke channel-
 * site dezelfde nette blog-slug kan hebben (bv. /blog/wat-kost-een-website op
 * elke niche). De hoofd-site (channel = null) houdt z'n bestaande unieke slugs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropUnique('blog_posts_locale_slug_unique');
            $table->unique(['locale', 'channel', 'slug'], 'blog_posts_locale_channel_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropUnique('blog_posts_locale_channel_slug_unique');
            $table->unique(['locale', 'slug'], 'blog_posts_locale_slug_unique');
        });
    }
};
