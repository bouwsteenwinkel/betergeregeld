<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetLocale;
use App\Models\Blog\BlogCategory;
use App\Models\Blog\BlogPost;
use App\Models\Blog\BlogTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
	public function index(Request $request, string $locale): View
	{
		$featured = BlogPost::query()->published()->where('locale', $locale)->where('featured', true)
			->with('category')->orderByDesc('published_at')->limit(3)->get();

		$pillars = BlogPost::query()->published()->where('locale', $locale)->where('is_pillar', true)
			->with('category')->orderBy('category_id')->get();

		$recent = BlogPost::query()->published()->where('locale', $locale)
			->whereNotIn('id', $featured->pluck('id'))
			->with('category')
			->orderByDesc('published_at')
			->paginate(12)
			->withQueryString();

		$categories = BlogCategory::query()
			->orderBy('sort_order')
			->withCount(['posts' => fn ($q) => $q->whereNotNull('published_at')->where('locale', $locale)])
			->get();

		return view('blog.index', compact('featured', 'pillars', 'recent', 'categories'));
	}

	public function category(Request $request, string $locale, string $categorySlug): View
	{
		$category = BlogCategory::query()->where('slug', $categorySlug)->firstOrFail();

		$pillar = $category->posts()->where('locale', $locale)->where('is_pillar', true)->first();

		$posts = BlogPost::query()->published()->where('locale', $locale)
			->where('category_id', $category->id)
			->where('is_pillar', false)
			->with('category')
			->orderByDesc('published_at')
			->paginate(20)
			->withQueryString();

		$otherCategories = BlogCategory::query()
			->where('id', '!=', $category->id)
			->orderBy('sort_order')
			->withCount(['posts' => fn ($q) => $q->whereNotNull('published_at')->where('locale', $locale)])
			->get();

		return view('blog.category', compact('category', 'pillar', 'posts', 'otherCategories'));
	}

	public function show(Request $request, string $locale, string $slug): View|RedirectResponse
	{
		// Dedup EN-content: een deel van de EN-posts bestaat dubbel — de schone slug én
		// dezelfde met een '-en'-suffix (historische import-cruft), allebei 200. Dat is
		// duplicate content. 301 de '-en'-variant naar de schone slug zodat Google de
		// ranking consolideert op één URL.
		$canonical = $this->enCanonicalSlug($locale, $slug);
		if ($canonical !== $slug) {
			return redirect()->route('blog.show', ['locale' => 'en', 'slug' => $canonical], 301);
		}

		$post = BlogPost::query()->published()
			->where('locale', $locale)
			->where('slug', $slug)
			->with('category', 'tags')
			->first();

		if (! $post) {
			return $this->resolveMissingPost($locale, $slug);
		}

		$related = $post->relatedPosts(6);

		$categoryPillar = $post->category->pillarPost();
		if ($categoryPillar && $categoryPillar->id === $post->id) {
			$categoryPillar = null;
		}

		return view('blog.show', compact('post', 'related', 'categoryPillar'));
	}

	/**
	 * Een niet-gevonden blog-URL netjes afhandelen i.p.v. een kale 404:
	 *  - bestaat de slug (gepubliceerd) in een ANDERE ondersteunde locale, dan is dit een
	 *    wrong-locale-URL (bv. /nl/blog/<slug>-en waar de EN-post op /en/blog/<slug>-en leeft,
	 *    zoals Google die verkeerd indexeerde) → 301 naar de juiste locale, zodat indexering
	 *    en link-equity meeverhuizen;
	 *  - bestaat 'ie nergens meer gepubliceerd (post verwijderd/gedepubliceerd), dan 410 Gone:
	 *    Google dropt die sneller dan een 404 en blijft 'm niet eindeloos herproberen.
	 */
	private function resolveMissingPost(string $locale, string $slug): RedirectResponse
	{
		$elsewhere = BlogPost::query()->published()
			->forChannel(null) // alleen hoofdsite-posts; niet per ongeluk naar een channel-site
			->where('slug', $slug)
			->whereIn('locale', SetLocale::SUPPORTED)
			->orderByDesc('published_at')
			->first();

		if ($elsewhere) {
			// Als het doel zelf een EN-'-en'-duplicaat is, meteen naar de schone slug —
			// zo blijft het één redirect (/nl/blog/X-en → /en/blog/X) i.p.v. een keten.
			$targetSlug = $this->enCanonicalSlug($elsewhere->locale, $slug);

			return redirect()->route('blog.show', ['locale' => $elsewhere->locale, 'slug' => $targetSlug], 301);
		}

		abort(410);
	}

	/**
	 * De canonieke slug voor een EN-post: is $slug een '-en'-duplicaat (import-cruft) met
	 * een gepubliceerde schone tweeling, dan de schone slug; anders $slug ongewijzigd.
	 * Alleen voor EN — bij andere locales verandert er niets.
	 */
	private function enCanonicalSlug(string $locale, string $slug): string
	{
		if ($locale !== 'en' || ! str_ends_with($slug, '-en')) {
			return $slug;
		}
		$base = substr($slug, 0, -3);
		$hasTwin = BlogPost::query()->published()->forChannel(null)
			->where('locale', 'en')->where('slug', $base)->exists();

		return $hasTwin ? $base : $slug;
	}

	public function tag(Request $request, string $locale, string $tagSlug): View
	{
		$tag = BlogTag::query()->where('slug', $tagSlug)->firstOrFail();
		$posts = $tag->posts()
			->whereNotNull('published_at')
			->where('locale', $locale)
			->with('category')
			->orderByDesc('published_at')
			->paginate(20);

		return view('blog.tag', compact('tag', 'posts'));
	}
}
