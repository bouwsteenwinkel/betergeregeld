<?php

namespace App\Services\AccessGuard\Radar\Http;

/**
 * Plain DTO for a SafeHttpClient response. Headers are lowercased so
 * fingerprinters can read them case-insensitively without re-normalising.
 */
final class HttpResponse
{
	/**
	 * @param  array<string, string>  $headers  lowercased keys, comma-joined values
	 */
	public function __construct(
		public readonly int $status,
		public readonly array $headers,
		public readonly string $body,
		public readonly string $finalUrl,
		public readonly string $resolvedIp,
	) {}

	public function header(string $name): ?string
	{
		return $this->headers[strtolower($name)] ?? null;
	}

	public function isHtml(): bool
	{
		$ct = $this->header('content-type') ?? '';
		return str_contains($ct, 'text/html') || str_contains($ct, 'application/xhtml');
	}
}
