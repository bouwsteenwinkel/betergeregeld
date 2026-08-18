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

    public function test_en_suffix_slug_gaat_naar_het_nederlandse_artikel(): void
    {
        // Sinds 18-08-2026 is de Engelse blog uitgefaseerd. Een geïndexeerde URL met
        // '-en' in de slug hoort in ÉÉN hop op het Nederlandse artikel uit te komen,
        // niet op een 410: dat artikel bestaat gewoon, alleen zonder achtervoegsel.
        BlogPost::create([
            'locale' => 'nl', 'channel' => null, 'slug' => 'offertes-sneller-maken',
            'title' => 'x', 'published_at' => now()->subDay(),
        ]);

        $this->get('/nl/blog/offertes-sneller-maken-en')
            ->assertStatus(301)
            ->assertRedirect('/nl/blog/offertes-sneller-maken');
    }

    public function test_en_suffix_gaat_niet_via_de_engelse_blog(): void
    {
        // Voorheen ging /nl/blog/<slug>-en naar /en/blog/<slug>. Die bestemming bestaat
        // niet meer, dus zou dat nu twee hops kosten (en de tweede naar /nl). Het moet
        // in één keer op het Nederlandse artikel uitkomen. Ook als de oude Engelse
        // rijen nog in de database staan — die worden niet meer bediend.
        BlogPost::create([
            'locale' => 'nl', 'channel' => null, 'slug' => 'offertes-sneller-maken',
            'title' => 'x', 'published_at' => now()->subDay(),
        ]);
        BlogPost::create([
            'locale' => 'en', 'channel' => null, 'slug' => 'offertes-sneller-maken-en',
            'title' => 'x', 'published_at' => now()->subDay(),
        ]);

        $this->get('/nl/blog/offertes-sneller-maken-en')
            ->assertStatus(301)
            ->assertRedirect('/nl/blog/offertes-sneller-maken');
    }

    public function test_engelse_blog_url_gaat_naar_de_nederlandse(): void
    {
        // De Engelse blog is uitgefaseerd (18-08-2026). Elke /en/blog-URL 301't naar
        // /nl/blog, met het '-en'-achtervoegsel eraf zodat het bij het bestaande
        // Nederlandse artikel uitkomt en niet bij een 410.
        BlogPost::create([
            'locale' => 'nl', 'channel' => null, 'slug' => 'offertes-sneller-maken',
            'title' => 'x', 'published_at' => now()->subDay(),
        ]);

        $this->get('/en/blog/offertes-sneller-maken-en')
            ->assertStatus(301)
            ->assertRedirect('/nl/blog/offertes-sneller-maken');

        $this->get('/en/blog')->assertStatus(301)->assertRedirect('/nl/blog');
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
