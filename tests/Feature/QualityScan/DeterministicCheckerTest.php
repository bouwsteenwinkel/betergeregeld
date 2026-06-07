<?php

namespace Tests\Feature\QualityScan;

use App\Services\QualityScan\Data\ExtractedContent;
use App\Services\QualityScan\DeterministicChecker;
use Tests\TestCase;

class DeterministicCheckerTest extends TestCase
{
	/** Bouwt een ExtractedContent die standaard alle checks passeert; override per test. */
	private function content(array $o = []): ExtractedContent
	{
		// array_merge zodat een expliciete null-override (bv. ['lang'=>null]) blijft staan.
		$o = array_merge([
			'title' => 'Een nette paginatitel met een prima lengte erin',
			'metaDescription' => str_repeat('inhoud die past ', 9), // ~144 tekens
			'metaRobots' => null, 'canonical' => 'https://klant.nl/pagina', 'ogTags' => [],
			'generator' => null, 'lang' => 'nl', 'headings' => [['level' => 1, 'text' => 'H1']],
			'images' => [], 'links' => [], 'bodyText' => 'tekst', 'cookieText' => null,
			'finalUrl' => 'https://klant.nl/pagina', 'isHttps' => true,
		], $o);

		return new ExtractedContent(
			$o['title'], $o['metaDescription'], $o['metaRobots'], $o['canonical'], $o['ogTags'],
			$o['generator'], $o['lang'], $o['headings'], $o['images'], $o['links'],
			$o['bodyText'], $o['cookieText'], $o['finalUrl'], $o['isHttps'],
		);
	}

	/** @return string|null status van een check-id, of null als die check niet voorkomt */
	private function statusOf(array $findings, string $checkId): ?string
	{
		foreach ($findings as $f) {
			if ($f->checkId === $checkId) {
				return $f->status;
			}
		}

		return null;
	}

	private function check(ExtractedContent $c): array
	{
		return (new DeterministicChecker())->check($c);
	}

	public function test_baseline_passeert_kernchecks(): void
	{
		$f = $this->check($this->content());
		$this->assertSame('pass', $this->statusOf($f, 'title_aanwezig'));
		$this->assertSame('pass', $this->statusOf($f, 'meta_description_aanwezig'));
		$this->assertSame('pass', $this->statusOf($f, 'h1_aantal'));
		$this->assertSame('pass', $this->statusOf($f, 'noindex_detectie'));
	}

	public function test_title_checks(): void
	{
		$this->assertSame('fail', $this->statusOf($this->check($this->content(['title' => ''])), 'title_aanwezig'));
		$this->assertSame('warn', $this->statusOf($this->check($this->content(['title' => 'Kort'])), 'title_lengte'));
	}

	public function test_meta_description_checks(): void
	{
		$this->assertSame('fail', $this->statusOf($this->check($this->content(['metaDescription' => ''])), 'meta_description_aanwezig'));
		$this->assertSame('warn', $this->statusOf($this->check($this->content(['metaDescription' => 'te kort'])), 'meta_description_lengte'));
	}

	public function test_h1_checks(): void
	{
		$this->assertSame('fail', $this->statusOf($this->check($this->content(['headings' => []])), 'h1_aantal'));
		$this->assertSame('warn', $this->statusOf($this->check($this->content(['headings' => [['level' => 1, 'text' => 'a'], ['level' => 1, 'text' => 'b']]])), 'h1_aantal'));
	}

	public function test_heading_hierarchie(): void
	{
		$headings = [['level' => 1, 'text' => 'a'], ['level' => 2, 'text' => 'b'], ['level' => 5, 'text' => 'c']];
		$this->assertSame('warn', $this->statusOf($this->check($this->content(['headings' => $headings])), 'heading_hierarchie'));
	}

	public function test_alt_teksten(): void
	{
		$leeg = [['src' => 'a', 'alt' => ''], ['src' => 'b', 'alt' => ''], ['src' => 'c', 'alt' => 'ok']];
		$this->assertSame('fail', $this->statusOf($this->check($this->content(['images' => $leeg])), 'alt_teksten_aanwezig'));

		$generiek = [['src' => 'a', 'alt' => 'foto'], ['src' => 'b', 'alt' => 'foto'], ['src' => 'c', 'alt' => 'foto']];
		$this->assertSame('warn', $this->statusOf($this->check($this->content(['images' => $generiek])), 'alt_teksten_generiek'));
	}

	public function test_noindex_generator_lang_canonical_en_mixed_content(): void
	{
		$this->assertSame('fail', $this->statusOf($this->check($this->content(['metaRobots' => 'noindex, follow'])), 'noindex_detectie'));
		$this->assertSame('warn', $this->statusOf($this->check($this->content(['generator' => 'Wix'])), 'generator_tag'));
		$this->assertSame('warn', $this->statusOf($this->check($this->content(['lang' => null])), 'lang_attribuut'));
		$this->assertSame('warn', $this->statusOf($this->check($this->content(['lang' => 'en'])), 'lang_attribuut'));
		$this->assertSame('warn', $this->statusOf($this->check($this->content(['canonical' => null])), 'canonical_aanwezig'));
		$this->assertSame('fail', $this->statusOf($this->check($this->content(['canonical' => 'https://ander.nl/x'])), 'canonical_aanwezig'));

		$mixed = ['images' => [['src' => 'http://onveilig.nl/a.jpg', 'alt' => 'x']]];
		$this->assertSame('warn', $this->statusOf($this->check($this->content($mixed)), 'mixed_content'));
	}
}
