<?php

namespace Tests\Feature\QualityScan;

use App\Services\QualityScan\ContentExtractor;
use App\Services\QualityScan\Data\FetchResult;
use Tests\TestCase;

class ContentExtractorTest extends TestCase
{
	private function extract(string $html, string $url = 'https://klant.nl/test')
	{
		return (new ContentExtractor())->extract(new FetchResult($html, 200, 100, $url));
	}

	public function test_extraheert_meta_en_headings_en_images(): void
	{
		$html = <<<'HTML'
			<!DOCTYPE html><html lang="nl"><head>
			<title>Mijn pagina</title>
			<meta name="description" content="Een korte omschrijving die abrupt">
			<meta name="robots" content="noindex">
			<meta name="generator" content="WordPress 6.4">
			<link rel="canonical" href="https://klant.nl/test">
			<meta property="og:title" content="OG titel">
			</head><body>
			<h1>Eerste</h1><h1>Tweede</h1><h2>Sub</h2>
			<img src="a.jpg" alt=""><img src="b.jpg" alt="logo"><img src="c.jpg" alt="">
			<a href="/intern">interne link</a><a href="https://extern.nl/x">externe link</a>
			<nav>NAVIGATIE NEGEER</nav>
			<p>Zichtbare bodytekst hier.</p>
			<footer>FOOTER NEGEER</footer>
			</body></html>
			HTML;

		$c = $this->extract($html);

		$this->assertSame('Mijn pagina', $c->title);
		$this->assertSame('Een korte omschrijving die abrupt', $c->metaDescription);
		$this->assertSame('noindex', $c->metaRobots);
		$this->assertSame('WordPress 6.4', $c->generator);
		$this->assertSame('nl', $c->lang);
		$this->assertSame('https://klant.nl/test', $c->canonical);
		$this->assertSame('OG titel', $c->ogTags['title']);

		// Twee H1's + één H2, in documentvolgorde.
		$this->assertCount(3, $c->headings);
		$this->assertSame([1, 1, 2], array_column($c->headings, 'level'));

		// Drie afbeeldingen, twee met lege alt.
		$this->assertCount(3, $c->images);
		$this->assertSame(2, count(array_filter($c->images, fn ($i) => $i['alt'] === '')));

		// Alleen de interne link blijft over.
		$this->assertCount(1, $c->links);
		$this->assertSame('/intern', $c->links[0]['href']);

		// nav/footer zijn uit de bodytekst gestript.
		$this->assertStringContainsString('Zichtbare bodytekst', $c->bodyText);
		$this->assertStringNotContainsString('NAVIGATIE NEGEER', $c->bodyText);
		$this->assertStringNotContainsString('FOOTER NEGEER', $c->bodyText);
	}

	public function test_detecteert_cookiebanner_en_https(): void
	{
		$html = '<html><head><title>x</title></head><body><div class="cookie-consent">Wij gebruiken tracking cookies.</div></body></html>';
		$c = $this->extract($html, 'https://klant.nl/');

		$this->assertNotNull($c->cookieText);
		$this->assertStringContainsString('tracking cookies', $c->cookieText);
		$this->assertTrue($c->isHttps);
	}

	public function test_pakket_blijft_onder_de_limiet(): void
	{
		// Lage pakketlimiet zodat de afkap-route gegarandeerd wordt geraakt.
		config(['quality-scan.extract.max_package_chars' => 2000]);

		$long = '<p>' . str_repeat('Lange tekst. ', 5000) . '</p>';
		$c = $this->extract('<html><head><title>x</title></head><body>' . $long . '</body></html>');

		$json = json_encode($c->toArray(), JSON_UNESCAPED_UNICODE);
		$this->assertLessThanOrEqual(2000, mb_strlen($json));
		$this->assertStringContainsString('[afgekapt]', $c->bodyText);
	}
}
