<?php

namespace App\Http\Controllers;

use App\Models\Blog\BlogPost;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ServiceController extends Controller
{
	public function index(string $locale): View
	{
		return view('pages.services-index', [
			'services' => config('services_catalog'),
		]);
	}

	public function show(string $locale, string $slug): View
	{
		$service = config("services_catalog.$slug");
		abort_if(! $service, 404);

		return view('pages.service', [
			'slug'         => $slug,
			'service'      => $service,
			'relatedPosts' => $this->relatedPosts($slug),
		]);
	}

	/**
	 * Top-4 blog-posts die bij deze service horen. Match via categorieën
	 * (sterker signaal) en tags (zwakker). Volgorde: pillars eerst, daarna
	 * meest recent. Geen match → lege lijst → widget rendert niet.
	 */
	private function relatedPosts(string $serviceSlug): Collection
	{
		$map = config("service_blog_links.$serviceSlug");
		if (!is_array($map)) {
			return collect();
		}
		$categories = $map['categories'] ?? [];
		$tags       = $map['tags'] ?? [];
		if (empty($categories) && empty($tags)) {
			return collect();
		}

		return BlogPost::query()
			->published()
			->with('category')
			->where(function ($q) use ($categories, $tags) {
				if (!empty($categories)) {
					$q->orWhereHas('category', fn ($cq) => $cq->whereIn('slug', $categories));
				}
				if (!empty($tags)) {
					$q->orWhereHas('tags', fn ($tq) => $tq->whereIn('slug', $tags));
				}
			})
			->orderByDesc('is_pillar')
			->orderByDesc('published_at')
			->limit(4)
			->get();
	}
}
