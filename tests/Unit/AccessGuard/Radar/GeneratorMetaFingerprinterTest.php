<?php

namespace Tests\Unit\AccessGuard\Radar;

use App\Models\AccessGuard\Radar\Asset;
use App\Services\AccessGuard\Radar\Fingerprint\GeneratorMetaFingerprinter;
use App\Services\AccessGuard\Radar\Http\HttpResponse;
use App\Services\AccessGuard\Radar\Http\HttpClient;
use PHPUnit\Framework\TestCase;

class GeneratorMetaFingerprinterTest extends TestCase
{
	public function test_detects_wordpress_with_version(): void
	{
		$html = '<!doctype html><html><head>'
			. '<meta name="generator" content="WordPress 6.4.2">'
			. '</head><body></body></html>';
		$result = $this->runFor($html);

		$this->assertCount(1, $result);
		$this->assertSame('wordpress', $result[0]->vendor);
		$this->assertSame('wordpress', $result[0]->product);
		$this->assertSame('6.4.2', $result[0]->version);
		$this->assertEqualsWithDelta(0.85, $result[0]->confidence, 0.01);
	}

	public function test_detects_drupal_with_paren_url(): void
	{
		$html = '<meta name="generator" content="Drupal 10 (https://www.drupal.org)">';
		$result = $this->runFor($html);

		$this->assertCount(1, $result);
		$this->assertSame('drupal', $result[0]->product);
		$this->assertSame('10', $result[0]->version);
	}

	public function test_detects_joomla_without_version(): void
	{
		$html = '<meta name="generator" content="Joomla! - Open Source Content Management">';
		$result = $this->runFor($html);

		$this->assertCount(1, $result);
		$this->assertSame('joomla', $result[0]->product);
		$this->assertNull($result[0]->version);
		$this->assertEqualsWithDelta(0.50, $result[0]->confidence, 0.01);
	}

	public function test_detects_unknown_generator_keeps_raw_name(): void
	{
		$html = '<meta name="generator" content="MysteryStack 3.1">';
		$result = $this->runFor($html);

		$this->assertCount(1, $result);
		$this->assertSame('unknown', $result[0]->vendor);
		$this->assertSame('MysteryStack', $result[0]->product);
		$this->assertSame('3.1', $result[0]->version);
	}

	public function test_skips_when_response_not_html(): void
	{
		$resp = new HttpResponse(
			status: 200,
			headers: ['content-type' => 'application/json'],
			body: '{"foo":"bar"}',
			finalUrl: 'https://example.com',
			resolvedIp: '1.1.1.1',
		);
		$http = $this->createStub(HttpClient::class);
		$http->method('request')->willReturn($resp);

		$this->assertSame([], (new GeneratorMetaFingerprinter())->fingerprint($this->asset(), $http));
	}

	public function test_returns_empty_when_no_generator_tag(): void
	{
		$this->assertSame([], $this->runFor('<html><head><title>x</title></head></html>'));
	}

	/** @return \App\Services\AccessGuard\Radar\Fingerprint\DetectedSoftware[] */
	private function runFor(string $html): array
	{
		$resp = new HttpResponse(
			status: 200,
			headers: ['content-type' => 'text/html; charset=utf-8'],
			body: $html,
			finalUrl: 'https://example.com',
			resolvedIp: '1.1.1.1',
		);
		$http = $this->createStub(HttpClient::class);
		$http->method('request')->willReturn($resp);
		return (new GeneratorMetaFingerprinter())->fingerprint($this->asset(), $http);
	}

	private function asset(): Asset
	{
		$a = new Asset();
		$a->url = 'https://example.com';
		return $a;
	}
}
