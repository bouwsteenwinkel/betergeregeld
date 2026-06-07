<?php

namespace App\Services\QualityScan;

use App\Services\QualityScan\Contracts\ContentExtractorInterface;
use App\Services\QualityScan\Data\ExtractedContent;
use App\Services\QualityScan\Data\FetchResult;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Destilleert uit ruwe HTML een compact, gestructureerd pakket: meta-gegevens,
 * headings, afbeeldingen, interne links, zichtbare body-tekst en een eventuele
 * cookiebanner. Het totale pakket blijft onder ~12.000 tekens.
 */
class ContentExtractor implements ContentExtractorInterface
{
	public function extract(FetchResult $result): ExtractedContent
	{
		$cfg = config('quality-scan.extract');
		$crawler = new Crawler($result->html, $result->finalUrl);

		$title = $this->firstText($crawler, 'head title');
		$metaDescription = $this->meta($crawler, 'name', 'description');
		$metaRobots = $this->meta($crawler, 'name', 'robots');
		$generator = $this->meta($crawler, 'name', 'generator');
		$canonical = $this->firstAttr($crawler, 'link[rel="canonical"]', 'href');
		$lang = $this->firstAttr($crawler, 'html', 'lang');

		$ogTags = [
			'title'       => $this->meta($crawler, 'property', 'og:title'),
			'description' => $this->meta($crawler, 'property', 'og:description'),
			'type'        => $this->meta($crawler, 'property', 'og:type'),
		];

		$headings = [];
		$crawler->filter('h1, h2, h3, h4, h5, h6')->each(function (Crawler $n) use (&$headings) {
			$text = trim(preg_replace('/\s+/u', ' ', $n->text('')));
			if ($text !== '') {
				$headings[] = ['level' => (int) substr(strtolower($n->nodeName()), 1), 'text' => mb_substr($text, 0, 200)];
			}
		});

		$images = [];
		$crawler->filter('img')->each(function (Crawler $n) use (&$images, $cfg) {
			if (count($images) >= $cfg['max_images']) {
				return;
			}
			$images[] = ['src' => mb_substr((string) $n->attr('src'), 0, 300), 'alt' => mb_substr((string) $n->attr('alt'), 0, 200)];
		});

		$host = parse_url($result->finalUrl, PHP_URL_HOST);
		$links = [];
		$crawler->filter('a[href]')->each(function (Crawler $n) use (&$links, $cfg, $host) {
			if (count($links) >= $cfg['max_links']) {
				return;
			}
			$href = (string) $n->attr('href');
			if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:') || str_starts_with($href, 'javascript:')) {
				return;
			}
			$linkHost = parse_url($href, PHP_URL_HOST);
			$internal = $linkHost === null || $linkHost === $host; // relatief of zelfde host
			if (! $internal) {
				return;
			}
			$anchor = trim(preg_replace('/\s+/u', ' ', $n->text('')));
			$links[] = ['href' => mb_substr($href, 0, 300), 'anchor' => mb_substr($anchor, 0, 120)];
		});

		// Body-tekst + cookiebanner via de onderliggende DOMDocument (na de
		// structured extractie, want we verwijderen er nodes uit).
		$dom = $crawler->count() ? $crawler->getNode(0)?->ownerDocument : null;
		$bodyText = $dom ? $this->visibleBodyText($dom, (int) $cfg['max_body_chars']) : '';
		$cookieText = $dom ? $this->cookieText($dom) : null;

		$content = new ExtractedContent(
			title: $title,
			metaDescription: $metaDescription,
			metaRobots: $metaRobots,
			canonical: $canonical,
			ogTags: $ogTags,
			generator: $generator,
			lang: $lang,
			headings: $headings,
			images: $images,
			links: $links,
			bodyText: $bodyText,
			cookieText: $cookieText,
			finalUrl: $result->finalUrl,
			isHttps: str_starts_with(strtolower($result->finalUrl), 'https://'),
		);

		return $this->fitToLimit($content, (int) $cfg['max_package_chars']);
	}

	private function firstText(Crawler $c, string $selector): ?string
	{
		$node = $c->filter($selector);

		return $node->count() ? (trim($node->first()->text('')) ?: null) : null;
	}

	private function firstAttr(Crawler $c, string $selector, string $attr): ?string
	{
		$node = $c->filter($selector);

		return $node->count() ? ($node->first()->attr($attr) ?: null) : null;
	}

	private function meta(Crawler $c, string $key, string $value): ?string
	{
		$node = $c->filter('meta[' . $key . '="' . $value . '"]');

		return $node->count() ? (trim((string) $node->first()->attr('content')) ?: null) : null;
	}

	private function visibleBodyText(\DOMDocument $dom, int $max): string
	{
		$xpath = new \DOMXPath($dom);
		$remove = [];
		foreach ($xpath->query('//script|//style|//noscript|//nav|//footer|//template|//svg') as $node) {
			$remove[] = $node;
		}
		foreach ($remove as $node) {
			$node->parentNode?->removeChild($node);
		}
		$body = $xpath->query('//body')->item(0);
		$text = $body ? $body->textContent : $dom->textContent;
		$text = trim(preg_replace('/\s+/u', ' ', (string) $text));

		return mb_substr($text, 0, $max);
	}

	private function cookieText(\DOMDocument $dom): ?string
	{
		$xpath = new \DOMXPath($dom);
		$q = "//*[contains(translate(@id,'COOKIE','cookie'),'cookie') or contains(translate(@class,'COOKIE','cookie'),'cookie')]";
		$best = null;
		foreach ($xpath->query($q) as $node) {
			$t = trim(preg_replace('/\s+/u', ' ', (string) $node->textContent));
			if ($t !== '' && (mb_strlen($t) > mb_strlen((string) $best))) {
				$best = $t;
			}
		}

		return $best !== null ? mb_substr($best, 0, 1500) : null;
	}

	/** Houdt het totale pakket onder de limiet door de body-tekst iteratief af te kappen. */
	private function fitToLimit(ExtractedContent $content, int $maxPackage): ExtractedContent
	{
		$arr = $content->toArray();
		if (mb_strlen(json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) <= $maxPackage) {
			return $content;
		}

		$base = $content->bodyText;
		$body = $base;
		for ($i = 0; $i < 6; $i++) {
			$arr['body_text'] = $body;
			$len = mb_strlen(json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
			if ($len <= $maxPackage) {
				break;
			}
			$cut = max(0, mb_strlen($body) - ($len - $maxPackage) - 20); // -20 marge voor JSON-escaping
			$body = ($cut > 0 ? mb_substr($base, 0, $cut) : '') . ' [afgekapt]';
		}

		return new ExtractedContent(
			$content->title, $content->metaDescription, $content->metaRobots, $content->canonical,
			$content->ogTags, $content->generator, $content->lang, $content->headings,
			$content->images, $content->links, $body, $content->cookieText, $content->finalUrl, $content->isHttps,
		);
	}
}
