<?php

namespace Tests\Feature\Blog;

use App\Models\Blog\BlogPost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Niet-gevonden blog-URL's netjes afhandelen i.p.v. een kale 404:
 *  - wrong-locale (slug leeft in een andere locale) → 301 naar de juiste locale;
 *  - echt verwijderd → 410 Gone.
 * Zie BlogController::resolveMissingPost. Minimale eigen tabel (geen RefreshDatabase:
 * migrate:fresh loopt in dit project stuk op sqlite, zie Tests\Concerns\SchedulingSchema).
 */
class BlogMissingPostTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() !== 'sqlite') {
            $this->fail('Alleen op de sqlite-wegwerpdatabase.');
        }
        Schema::create('blog_posts', function ($t) {
            $t->id();
            $t->unsignedBigInteger('category_id')->nullable();
            $t->string('locale', 5)->default('nl');
            $t->string('channel')->nullable();
            $t->string('slug');
            $t->string('title')->default('');
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
        });
    }

    public function test_wrong_locale_url_redirect_301_naar_juiste_locale(): void
    {
        // EN-post met '-en'-slug leeft op /en/blog/... ; Google crawlde 'm verkeerd op /nl/.
        BlogPost::create([
            'locale' => 'en', 'channel' => null, 'slug' => 'offertes-sneller-maken-en',
            'title' => 'x', 'published_at' => now()->subDay(),
        ]);

        $this->get('/nl/blog/offertes-sneller-maken-en')
            ->assertStatus(301)
            ->assertRedirect('/en/blog/offertes-sneller-maken-en');
    }

    public function test_wrong_locale_en_duplicaat_redirect_in_een_hop_naar_schone_slug(): void
    {
        // /nl/blog/<slug>-en waar de EN-post een '-en'-duplicaat is mét schone tweeling:
        // moet in ÉÉN hop naar /en/blog/<slug> (niet eerst naar /en/blog/<slug>-en).
        BlogPost::create([
            'locale' => 'en', 'channel' => null, 'slug' => 'offertes-sneller-maken',
            'title' => 'x', 'published_at' => now()->subDay(),
        ]);
        BlogPost::create([
            'locale' => 'en', 'channel' => null, 'slug' => 'offertes-sneller-maken-en',
            'title' => 'x', 'published_at' => now()->subDay(),
        ]);

        $this->get('/nl/blog/offertes-sneller-maken-en')
            ->assertStatus(301)
            ->assertRedirect('/en/blog/offertes-sneller-maken');
    }

    public function test_en_dubbele_slug_301_naar_schone_versie(): void
    {
        // Beide EN-versies gepubliceerd: de schone slug + de '-en'-duplicaat.
        // De '-en'-variant moet 301'en naar de schone (dedup duplicate content).
        BlogPost::create([
            'locale' => 'en', 'channel' => null, 'slug' => 'offertes-sneller-maken',
            'title' => 'x', 'published_at' => now()->subDay(),
        ]);
        BlogPost::create([
            'locale' => 'en', 'channel' => null, 'slug' => 'offertes-sneller-maken-en',
            'title' => 'x', 'published_at' => now()->subDay(),
        ]);

        $this->get('/en/blog/offertes-sneller-maken-en')
            ->assertStatus(301)
            ->assertRedirect('/en/blog/offertes-sneller-maken');
    }

    public function test_verwijderde_post_geeft_410(): void
    {
        $this->get('/nl/blog/bestaat-echt-niet')->assertStatus(410);
    }

    public function test_ongepubliceerde_post_telt_niet_als_doel(): void
    {
        // Bestaat wel maar niet gepubliceerd → geen redirect, gewoon 410.
        BlogPost::create([
            'locale' => 'en', 'channel' => null, 'slug' => 'concept-post',
            'title' => 'x', 'published_at' => null,
        ]);

        $this->get('/nl/blog/concept-post')->assertStatus(410);
    }
}
