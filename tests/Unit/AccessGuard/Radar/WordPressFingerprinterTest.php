<?php

namespace Tests\Unit\AccessGuard\Radar;

use App\Models\AccessGuard\Radar\Asset;
use App\Services\AccessGuard\Radar\Fingerprint\WordPressFingerprinter;
use App\Services\AccessGuard\Radar\Http\HttpResponse;
use App\Services\AccessGuard\Radar\Http\HttpClient;
use PHPUnit\Framework\TestCase;
use Throwable;

class WordPressFingerprinterTest extends TestCase
{
	public function test_returns_empty_when_wp_login_returns_404(): void
	{
		$http = $this->scriptedClient([
			'HEAD https://shop.test/wp-login.php' => new HttpResponse(404, [], '', '', '1.1.1.1'),
		]);
		$this->assertSame([],
			(new WordPressFingerprinter())->fingerprint($this->asset('https://shop.test'), $http)
		);
	}

	public function test_detects_core_plugins_and_theme(): void
	{
		$homepage = '<!doctype html><html><head>'
			. '<link rel="stylesheet" href="https://shop.test/wp-content/plugins/woocommerce/assets/css/woocommerce.css?ver=8.5.1">'
			. '<script src="https://shop.test/wp-content/plugins/contact-form-7/includes/js/index.js?ver=5.9"></script>'
			. '<link rel="stylesheet" href="https://shop.test/wp-content/themes/storefront/style.css?ver=4.5.0">'
			. '</head><body></body></html>';

		$readme = '<html><body><h1>WordPress</h1><p>Version 6.4.2</p></body></html>';
		$themeCss = "/*\nTheme Name: Storefront\nVersion: 4.5.1\n*/";

		$http = $this->scriptedClient([
			'HEAD https://shop.test/wp-login.php' => new HttpResponse(200, [], '', '', '1.1.1.1'),
			'GET https://shop.test'              => new HttpResponse(200, ['content-type' => 'text/html'], $homepage, '', '1.1.1.1'),
			'GET https://shop.test/readme.html'  => new HttpResponse(200, ['content-type' => 'text/html'], $readme, '', '1.1.1.1'),
			'GET https://shop.test/wp-content/themes/storefront/style.css'
				=> new HttpResponse(200, ['content-type' => 'text/css'], $themeCss, '', '1.1.1.1'),
		]);

		$results = (new WordPressFingerprinter())->fingerprint($this->asset('https://shop.test'), $http);

		// Core
		$core = $this->find($results, 'wordpress', 'wordpress');
		$this->assertNotNull($core, 'WP core not detected');
		$this->assertSame('6.4.2', $core->version);

		// Plugins
		$wc = $this->find($results, 'wordpress', 'woocommerce');
		$this->assertNotNull($wc, 'WooCommerce not detected');
		$this->assertSame('8.5.1', $wc->version);

		$cf7 = $this->find($results, 'wordpress', 'contact-form-7');
		$this->assertNotNull($cf7, 'CF7 not detected');
		$this->assertSame('5.9', $cf7->version);

		// Theme — version comes from style.css header, NOT the ?ver in HTML
		$theme = $this->find($results, 'wordpress-theme', 'storefront');
		$this->assertNotNull($theme, 'Storefront theme not detected');
		$this->assertSame('4.5.1', $theme->version);
		$this->assertEqualsWithDelta(0.85, $theme->confidence, 0.01);
	}

	public function test_plugin_without_version_query_is_lower_confidence(): void
	{
		$homepage = '<html><head>'
			. '<link href="/wp-content/plugins/yoast-seo/css/main.css">'
			. '</head><body></body></html>';

		$http = $this->scriptedClient([
			'HEAD https://x.test/wp-login.php' => new HttpResponse(200, [], '', '', '1.1.1.1'),
			'GET https://x.test'               => new HttpResponse(200, ['content-type' => 'text/html'], $homepage, '', '1.1.1.1'),
			'GET https://x.test/readme.html'   => new HttpResponse(404, [], '', '', '1.1.1.1'),
		]);

		$results = (new WordPressFingerprinter())->fingerprint($this->asset('https://x.test'), $http);
		$yoast = $this->find($results, 'wordpress', 'yoast-seo');
		$this->assertNotNull($yoast);
		$this->assertNull($yoast->version);
		$this->assertEqualsWithDelta(0.55, $yoast->confidence, 0.01);
	}

	private function asset(string $url): Asset
	{
		$a = new Asset();
		$a->url = $url;
		return $a;
	}

	/**
	 * @param  array<string, HttpResponse|Throwable>  $script
	 *         Keys are "METHOD URL"; URLs match leading-prefix so plugin
	 *         GETs with query strings still hit "GET https://shop.test".
	 */
	private function scriptedClient(array $script): HttpClient
	{
		$mock = $this->createStub(HttpClient::class);
		$mock->method('request')->willReturnCallback(
			function (string $method, string $url) use ($script) {
				$key = "{$method} {$url}";
				if (isset($script[$key])) {
					$resp = $script[$key];
					if ($resp instanceof Throwable) throw $resp;
					return $resp;
				}
				// Prefix match on path — readme.html and style.css must hit
				// even when the call adds nothing extra.
				foreach ($script as $pattern => $resp) {
					[$m, $u] = explode(' ', $pattern, 2);
					if ($m === $method && str_starts_with($url, $u)) {
						if ($resp instanceof Throwable) throw $resp;
						return $resp;
					}
				}
				return new HttpResponse(404, [], '', $url, '1.1.1.1');
			}
		);
		return $mock;
	}

	/** @param  \App\Services\AccessGuard\Radar\Fingerprint\DetectedSoftware[]  $list */
	private function find(array $list, string $vendor, string $product): ?\App\Services\AccessGuard\Radar\Fingerprint\DetectedSoftware
	{
		foreach ($list as $d) {
			if ($d->vendor === $vendor && $d->product === $product) {
				return $d;
			}
		}
		return null;
	}
}
