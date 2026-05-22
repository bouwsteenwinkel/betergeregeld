<?php

namespace App\Services\AccessGuard\Radar\Http;

/**
 * Narrow contract that the fingerprinters depend on. Extracted so tests
 * can swap in a fake client (SafeHttpClient is final on purpose to make
 * the SSRF surface auditable in production).
 */
interface HttpClient
{
	/**
	 * @param  array<string, string>  $extraHeaders
	 *
	 * @throws \InvalidArgumentException  on disallowed method or SSRF violation
	 * @throws \Illuminate\Http\Client\ConnectionException  on network failure
	 */
	public function request(string $method, string $url, array $extraHeaders = []): HttpResponse;
}
