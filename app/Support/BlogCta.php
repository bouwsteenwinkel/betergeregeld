<?php

namespace App\Support;

/**
 * Kiest het doorverwijsblok onder een blogpost (config/blog_cta.php): de eerste regel waarvan de slug-regex
 * past of waarvan de categorie overeenkomt. Null als er niets past.
 */
final class BlogCta
{
	/**
	 * @return array{title:string, text:string, url:string, button:string}|null
	 */
	public static function voor(string $slug, ?string $categorie): ?array
	{
		foreach ((array) config('blog_cta', []) as $regel) {
			$slugPast = isset($regel['slug']) && preg_match($regel['slug'], $slug) === 1;
			$categoriePast = $categorie !== null && in_array($categorie, (array) ($regel['categories'] ?? []), true);
			if ($slugPast || $categoriePast) {
				return [
					'title' => (string) $regel['title'],
					'text' => (string) $regel['text'],
					'url' => (string) $regel['url'],
					'button' => (string) $regel['button'],
				];
			}
		}

		return null;
	}
}
