<?php

namespace App\Services\QualityScan\Data;

/** Het resultaat van het ophalen van een pagina. */
final class FetchResult
{
	public function __construct(
		public readonly string $html,
		public readonly int $httpStatus,
		public readonly int $durationMs,
		public readonly string $finalUrl,
	) {
	}
}
