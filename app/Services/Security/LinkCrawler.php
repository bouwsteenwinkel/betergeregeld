<?php

namespace App\Services\Security;

use Composer\CaBundle\CaBundle;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Haalt één pagina op en controleert: mixed content (http-resources op een
 * https-pagina) en broken links (4xx/5xx of geen reactie). Diepte 0 (de
 * opgegeven pagina); aantal gecheckte links begrensd via config.
 */
class LinkCrawler
{
	private const UA = 'BeterGeregeld-Security/1.0';

	/**
	 * @return array{mixed_content:array<int,string>, broken:array<int,array{url:string,code:int}>, links_checked:int, error:?string}
	 */
	public function scan(string $pageUrl): array
	{
		$timeout = (int) config('security.crawl.timeout', 10);
		$maxLinks = (int) config('security.crawl.max_links', 40);
		$empty = ['mixed_content' => [], 'broken' => [], 'links_checked' => 0, 'error' => null];

		try {
			$resp = Http::timeout($timeout)
				->withHeaders(['User-Agent' => self::UA])
				->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
				->get($pageUrl);
		} catch (\Throwable $e) {
			return ['mixed_content' => [], 'broken' => [], 'links_checked' => 0, 'error' => $e->getMessage()];
		}

		if (! $resp->ok()) {
			return ['mixed_content' => [], 'broken' => [], 'links_checked' => 0, 'error' => 'Pagina niet bereikbaar (HTTP ' . $resp->status() . ')'];
		}

		$crawler = new Crawler($resp->body(), $pageUrl);
		$isHttps = str_starts_with(strtolower($pageUrl), 'https://');

		// Mixed content: http:// resources op een https-pagina.
		$mixed = [];
		if ($isHttps) {
			foreach ($crawler->filterXPath('//img[@src]|//script[@src]|//link[@href]|//iframe[@src]|//source[@src]') as $node) {
				$attr = $node->getAttribute('src') ?: $node->getAttribute('href');
				if ($attr && str_starts_with(strtolower($attr), 'http://')) {
					$mixed[$attr] = true;
				}
			}
		}

		// Links verzamelen (absoluut, dedupe, alleen http(s)), tot maxLinks.
		$links = [];
		foreach ($crawler->filterXPath('//a[@href]') as $a) {
			$abs = $this->absolutize($a->getAttribute('href'), $pageUrl);
			if ($abs !== null) {
				$links[$abs] = true;
			}
			if (count($links) >= $maxLinks) {
				break;
			}
		}

		$broken = [];
		$checked = 0;
		foreach (array_keys($links) as $link) {
			$checked++;
			$code = $this->statusOf($link, $timeout);
			if ($code === 0 || $code >= 400) {
				$broken[] = ['url' => $link, 'code' => $code];
			}
		}

		return ['mixed_content' => array_keys($mixed), 'broken' => $broken, 'links_checked' => $checked, 'error' => null];
	}

	private function absolutize(?string $href, string $base): ?string
	{
		$href = trim((string) $href);
		if ($href === '' || str_starts_with($href, '#')
			|| preg_match('#^(mailto:|tel:|javascript:|data:)#i', $href)) {
			return null;
		}
		if (preg_match('#^https?://#i', $href)) {
			return $href;
		}
		if (str_starts_with($href, '//')) {
			return 'https:' . $href;
		}

		$b = parse_url($base);
		$host = $b['host'] ?? null;
		if (! $host) {
			return null;
		}
		$scheme = $b['scheme'] ?? 'https';
		$port = isset($b['port']) ? ':' . $b['port'] : '';
		if (str_starts_with($href, '/')) {
			return $scheme . '://' . $host . $port . $href;
		}

		$path = $b['path'] ?? '/';
		$dir = substr($path, 0, strrpos($path, '/') + 1);

		return $scheme . '://' . $host . $port . $dir . $href;
	}

	private function statusOf(string $url, int $timeout): int
	{
		try {
			$resp = Http::timeout($timeout)
				->withHeaders(['User-Agent' => self::UA])
				->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath(), 'allow_redirects' => true])
				->head($url);
			$code = $resp->status();

			// Sommige servers weigeren HEAD (403/405) → probeer GET.
			if ($code === 0 || $code === 403 || $code === 405) {
				$resp = Http::timeout($timeout)
					->withHeaders(['User-Agent' => self::UA])
					->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath(), 'allow_redirects' => true])
					->get($url);
				$code = $resp->status();
			}

			return $code;
		} catch (\Throwable) {
			return 0;
		}
	}
}
