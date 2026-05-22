<?php

namespace App\Services\AccessGuard\Radar\Freshness;

/**
 * Result of FreshnessCalculator. Carries enough context for the UI to
 * render a tooltip like "WP 6.4.2 — 3 versions behind, latest is 6.7.1
 * (released 2026-04-15)".
 */
final class FreshnessVerdict
{
	public function __construct(
		public readonly FreshnessStatus $status,
		public readonly string $reason,
		public readonly ?string $latestStable = null,
		public readonly ?int $versionsBehind = null,
		public readonly ?\DateTimeInterface $eolDate = null,
	) {}
}
