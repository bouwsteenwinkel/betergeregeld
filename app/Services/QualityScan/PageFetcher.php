<?php

namespace App\Services\QualityScan;

use App\Services\QualityScan\Contracts\PageFetcherInterface;
use App\Services\QualityScan\Data\FetchResult;
use App\Services\QualityScan\Exceptions\PageFetchException;
use Composer\CaBundle\CaBundle;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Haalt de HTML van een pagina op via de Laravel HTTP-client. Timeout 15s,
 * max 3 redirects, herkenbare user-agent. CA-bundle expliciet meegegeven zodat
 * uitgaande HTTPS ook op Windows-PHP werkt (geen system-CA-store).
 */
class PageFetcher implements PageFetcherInterface
{
	public function fetch(string $url): FetchResult
	{
		$start = microtime(true);

		try {
			$resp = Http::timeout((int) config('quality-scan.fetch.timeout', 15))
				->withHeaders(['User-Agent' => (string) config('quality-scan.fetch.user_agent', 'BeterGeregeld-Monitor/1.0')])
				->withOptions([
					'allow_redirects' => ['max' => (int) config('quality-scan.fetch.max_redirects', 3)],
					'verify'          => CaBundle::getSystemCaRootBundlePath(),
				])
				->get($url);
		} catch (ConnectionException $e) {
			throw new PageFetchException('Netwerkfout bij ophalen pagina: ' . $e->getMessage(), null, $this->ms($start));
		}

		$duration = $this->ms($start);

		if ($resp->status() >= 400) {
			throw new PageFetchException('HTTP ' . $resp->status() . ' bij ophalen van de pagina.', $resp->status(), $duration);
		}

		return new FetchResult(
			html: $resp->body(),
			httpStatus: $resp->status(),
			durationMs: $duration,
			finalUrl: $url,
		);
	}

	private function ms(float $start): int
	{
		return (int) round((microtime(true) - $start) * 1000);
	}
}
