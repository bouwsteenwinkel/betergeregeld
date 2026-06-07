<?php

namespace App\Services\QualityScan\Exceptions;

use RuntimeException;

/** Domeinspecifieke fout bij het ophalen van een pagina (netwerk of HTTP). */
class PageFetchException extends RuntimeException
{
	public function __construct(
		string $message,
		public readonly ?int $httpStatus = null,
		public readonly int $durationMs = 0,
	) {
		parent::__construct($message);
	}
}
