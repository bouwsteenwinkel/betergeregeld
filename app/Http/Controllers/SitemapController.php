<?php

namespace App\Http\Controllers;

use App\Models\Blog\BlogCategory;
use App\Models\Blog\BlogPost;
use App\Models\Blog\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Dynamic sitemap.xml + RSS feed — both generated from current database
 * state. No cached files on disk, so blog edits go live immediately.
 *
 * Sitemap covers all publicly reachable URLs: home + main pages in both
 * locales, tool landings, and the full blog graph (posts, categories,
 * tags). Google Search Console picks this up at /sitemap.xml.
 *
 * RSS at /blog/rss — standards-compliant RSS 2.0 for feed readers and
 * third-party syndication.
 */
class SitemapController extends Controller
{
	public function sitemap(Request $request): Response
	{
		$locales = ['nl', 'en'];
		$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		foreach ($locales as $locale) {
			$xml .= $this->url(url("/{$locale}"), '1.0', 'daily');
			$xml .= $this->url(url("/{$locale}/over"), '0.6', 'monthly');
			$xml .= $this->url(url("/{$locale}/accessguard"), '0.9', 'weekly');
			$xml .= $this->url(url("/{$locale}/accessguard/demo"), '0.7', 'monthly');
			$xml .= $this->url(url("/{$locale}/prijzen"), '0.8', 'weekly');
			$xml .= $this->url(url("/{$locale}/blog"), '0.8', 'daily');
		}

		foreach (BlogCategory::query()->get(['slug', 'updated_at']) as $cat) {
			foreach ($locales as $locale) {
				$xml .= $this->url(
					url("/{$locale}/blog/categorie/{$cat->slug}"),
					'0.7',
					'weekly',
					$cat->updated_at,
				);
			}
		}

		foreach (BlogTag::query()->get(['slug', 'updated_at']) as $tag) {
			foreach ($locales as $locale) {
				$xml .= $this->url(
					url("/{$locale}/blog/tag/{$tag->slug}"),
					'0.5',
					'monthly',
					$tag->updated_at,
				);
			}
		}

		foreach (BlogPost::query()->published()->get(['slug', 'updated_at', 'is_pillar']) as $post) {
			foreach ($locales as $locale) {
				$xml .= $this->url(
					url("/{$locale}/blog/{$post->slug}"),
					$post->is_pillar ? '0.8' : '0.6',
					'monthly',
					$post->updated_at,
				);
			}
		}

		$xml .= '</urlset>' . "\n";

		return response($xml, 200, [
			'Content-Type' => 'application/xml; charset=utf-8',
			'Cache-Control' => 'public, max-age=3600',
		]);
	}

	public function rss(Request $request, string $locale): Response
	{
		abort_unless(in_array($locale, ['nl', 'en'], true), 404);

		$posts = BlogPost::query()
			->published()
			->with('category')
			->orderByDesc('published_at')
			->limit(50)
			->get();

		$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel>' . "\n";
		$xml .= '<title>' . e(config('app.name')) . ' — Blog</title>' . "\n";
		$xml .= '<link>' . url("/{$locale}/blog") . '</link>' . "\n";
		$xml .= '<description>Praktische gidsen over toegangsbeheer, compliance, offboarding en MKB-administratie.</description>' . "\n";
		$xml .= '<language>' . e($locale) . '</language>' . "\n";
		$xml .= '<atom:link href="' . e(url("/{$locale}/blog/rss")) . '" rel="self" type="application/rss+xml" />' . "\n";

		if ($latest = $posts->first()) {
			$xml .= '<lastBuildDate>' . $latest->published_at->toRfc2822String() . '</lastBuildDate>' . "\n";
		}

		foreach ($posts as $post) {
			$url = url("/{$locale}/blog/{$post->slug}");
			$xml .= '<item>' . "\n";
			$xml .= '  <title>' . e($post->title) . '</title>' . "\n";
			$xml .= '  <link>' . $url . '</link>' . "\n";
			$xml .= '  <guid isPermaLink="true">' . $url . '</guid>' . "\n";
			$xml .= '  <pubDate>' . $post->published_at->toRfc2822String() . '</pubDate>' . "\n";
			$xml .= '  <category>' . e($post->category->name) . '</category>' . "\n";
			$xml .= '  <description><![CDATA[' . $post->excerpt . ']]></description>' . "\n";
			$xml .= '</item>' . "\n";
		}

		$xml .= '</channel></rss>' . "\n";

		return response($xml, 200, [
			'Content-Type' => 'application/rss+xml; charset=utf-8',
			'Cache-Control' => 'public, max-age=1800',
		]);
	}

	private function url(string $loc, string $priority, string $changefreq, $lastmod = null): string
	{
		$x = "  <url>\n";
		$x .= '    <loc>' . e($loc) . "</loc>\n";
		if ($lastmod) {
			$x .= '    <lastmod>' . $lastmod->toIso8601String() . "</lastmod>\n";
		}
		$x .= '    <changefreq>' . $changefreq . "</changefreq>\n";
		$x .= '    <priority>' . $priority . "</priority>\n";
		$x .= "  </url>\n";
		return $x;
	}
}
