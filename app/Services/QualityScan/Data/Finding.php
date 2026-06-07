<?php

namespace App\Services\QualityScan\Data;

/**
 * Eén bevinding (deterministisch of door AI), klaar om als quality_finding te
 * worden opgeslagen.
 */
final class Finding
{
	public function __construct(
		public readonly string $source,    // deterministic | ai
		public readonly string $checkId,
		public readonly string $status,    // pass | warn | fail
		public readonly string $severity,  // laag | middel | hoog
		public readonly string $finding,
		public readonly ?string $suggestion = null,
		public readonly ?string $element = null,
	) {
	}

	public static function deterministic(string $checkId, string $status, string $severity, string $finding, ?string $suggestion = null, ?string $element = null): self
	{
		return new self('deterministic', $checkId, $status, $severity, $finding, $suggestion, $element);
	}

	public function toArray(): array
	{
		return [
			'source'     => $this->source,
			'check_id'   => $this->checkId,
			'status'     => $this->status,
			'severity'   => $this->severity,
			'finding'    => $this->finding,
			'suggestion' => $this->suggestion,
			'element'    => $this->element !== null ? mb_substr($this->element, 0, 500) : null,
		];
	}
}
