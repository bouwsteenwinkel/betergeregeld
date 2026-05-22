<?php

namespace Tests\Unit\AccessGuard\Radar;

use App\Models\AccessGuard\Radar\Asset;
use App\Services\AccessGuard\Radar\Fingerprint\HttpHeaderFingerprinter;
use App\Services\AccessGuard\Radar\Http\HttpResponse;
use App\Services\AccessGuard\Radar\Http\HttpClient;
use PHPUnit\Framework\TestCase;

class HttpHeaderFingerprinterTest extends TestCase
{
	public function test_parses_versioned_server_and_powered_by(): void
	{
		$asset = $this->asset('https://example.com');
		$http = $this->mockClient(new HttpResponse(
			status: 200,
			headers: [
				'server' => 'nginx/1.24.0 (Ubuntu)',
				'x-powered-by' => 'PHP/8.3.10',
			],
			body: '',
			finalUrl: 'https://example.com',
			resolvedIp: '93.184.216.34',
		));

		$results = (new HttpHeaderFingerprinter())->fingerprint($asset, $http);

		$this->assertCount(2, $results);
		$nginx = $this->find($results, 'nginx');
		$this->assertNotNull($nginx);
		$this->assertSame('1.24.0', $nginx->version);
		$this->assertEqualsWithDelta(0.90, $nginx->confidence, 0.01);

		$php = $this->find($results, 'php');
		$this->assertNotNull($php);
		$this->assertSame('8.3.10', $php->version);
		$this->assertEqualsWithDelta(0.85, $php->confidence, 0.01);
	}

	public function test_unversioned_server_drops_to_lower_confidence(): void
	{
		$http = $this->mockClient(new HttpResponse(
			status: 200,
			headers: ['server' => 'Apache'],
			body: '',
			finalUrl: 'https://example.com',
			resolvedIp: '93.184.216.34',
		));
		$results = (new HttpHeaderFingerprinter())->fingerprint(
			$this->asset('https://example.com'),
			$http,
		);
		$this->assertCount(1, $results);
		$this->assertNull($results[0]->version);
		$this->assertEqualsWithDelta(0.55, $results[0]->confidence, 0.01);
		$this->assertSame('apache', $results[0]->vendor);
		$this->assertSame('httpd', $results[0]->product);
	}

	public function test_returns_empty_when_url_missing(): void
	{
		$asset = new Asset();
		$asset->url = null;
		$http = $this->mockClient(new HttpResponse(200, [], '', '', ''));
		$this->assertSame([], (new HttpHeaderFingerprinter())->fingerprint($asset, $http));
	}

	public function test_swallows_network_errors(): void
	{
		$asset = $this->asset('https://example.com');
		$http = $this->createStub(HttpClient::class);
		$http->method('request')->willThrowException(new \RuntimeException('boom'));
		$this->assertSame([], (new HttpHeaderFingerprinter())->fingerprint($asset, $http));
	}

	private function asset(string $url): Asset
	{
		$a = new Asset();
		$a->url = $url;
		return $a;
	}

	private function mockClient(HttpResponse $resp): HttpClient
	{
		$mock = $this->createStub(HttpClient::class);
		$mock->method('request')->willReturn($resp);
		return $mock;
	}

	/** @param  \App\Services\AccessGuard\Radar\Fingerprint\DetectedSoftware[]  $list */
	private function find(array $list, string $product): ?\App\Services\AccessGuard\Radar\Fingerprint\DetectedSoftware
	{
		foreach ($list as $d) {
			if (str_contains(strtolower($d->vendor . ' ' . $d->product), $product)) {
				return $d;
			}
		}
		return null;
	}
}
