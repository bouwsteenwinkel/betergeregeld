<?php

namespace App\Services\AccessGuard\Radar\Freshness;

/**
 * Per-SoftwareInstance freshness verdict. Stored implicitly (computed
 * on the fly by FreshnessCalculator) — we don't denormalise it onto
 * software_instances because the truth changes whenever the catalog
 * is re-synced, not when the asset is re-scanned.
 *
 * Order matters for severity ranking — `worse()` returns the more
 * urgent of two statuses, used when an asset has multiple software
 * components and we want a single asset-level rollup.
 */
enum FreshnessStatus: string
{
	case UpToDate           = 'up_to_date';
	case Prerelease         = 'prerelease';
	case OutdatedSafe       = 'outdated_safe';
	case OutdatedVulnerable = 'outdated_vulnerable';
	case Vulnerable         = 'vulnerable';
	case Eol                = 'eol';
	case Unknown            = 'unknown';

	public function label(): string
	{
		return match ($this) {
			self::UpToDate           => 'Up to date',
			self::Prerelease         => 'Pre-release',
			self::OutdatedSafe       => 'Outdated',
			self::OutdatedVulnerable => 'Outdated + CVE',
			self::Vulnerable         => 'Vulnerable',
			self::Eol                => 'End of life',
			self::Unknown            => 'Unknown',
		};
	}

	public function color(): string
	{
		return match ($this) {
			self::UpToDate           => 'success',
			self::Prerelease         => 'warning',
			self::OutdatedSafe       => 'warning',
			self::OutdatedVulnerable => 'danger',
			self::Vulnerable         => 'danger',
			self::Eol                => 'danger',
			self::Unknown            => 'gray',
		};
	}

	public function icon(): string
	{
		return match ($this) {
			self::UpToDate           => 'heroicon-m-check-circle',
			self::Prerelease         => 'heroicon-m-beaker',
			self::OutdatedSafe       => 'heroicon-m-clock',
			self::OutdatedVulnerable => 'heroicon-m-exclamation-triangle',
			self::Vulnerable         => 'heroicon-m-shield-exclamation',
			self::Eol                => 'heroicon-m-no-symbol',
			self::Unknown            => 'heroicon-m-question-mark-circle',
		};
	}

	/** Severity 0 (lowest) → 6 (highest) — for sorting and rollup */
	public function severity(): int
	{
		return match ($this) {
			self::Unknown            => 0,
			self::UpToDate           => 1,
			self::Prerelease         => 2,
			self::OutdatedSafe       => 3,
			self::OutdatedVulnerable => 4,
			self::Vulnerable         => 5,
			self::Eol                => 6,
		};
	}

	public function worse(self $other): self
	{
		return $this->severity() >= $other->severity() ? $this : $other;
	}
}
