<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Blog\BlogCategory;
use App\Models\Blog\BlogPost;
use App\Models\Blog\BlogTag;
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

	public function show(Request $request, string $locale, string $slug): View
	{
		$post = BlogPost::query()->published()
			->where('locale', $locale)
			->where('slug', $slug)
			->with('category', 'tags')
			->firstOrFail();

		$related = $post->relatedPosts(6);

		$categoryPillar = $post->category->pillarPost();
		if ($categoryPillar && $categoryPillar->id === $post->id) {
			$categoryPillar = null;
		}

		return view('blog.show', compact('post', 'related', 'categoryPillar'));
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
