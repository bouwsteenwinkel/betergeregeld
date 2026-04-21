<?php

namespace App\Services\Features;

use App\Models\Plan;

/**
 * Lightweight read-only view of a plan's features.
 * Values are stored as strings; helpers interpret them:
 *   - "unlimited" (case-insensitive) → no limit (int|null returns null)
 *   - "0" / "1" → bool
 *   - digits → int
 *   - other → raw string
 */
class FeatureBag
{
	/** @param array<string,string> $features */
	public function __construct(
		public readonly Plan $plan,
		private readonly array $features,
	) {}

	public function has(string $key): bool
	{
		return array_key_exists($key, $this->features);
	}

	public function raw(string $key, ?string $default = null): ?string
	{
		return $this->features[$key] ?? $default;
	}

	public function bool(string $key, bool $default = false): bool
	{
		if (! $this->has($key)) {
			return $default;
		}
		$v = strtolower(trim((string) $this->features[$key]));
		return in_array($v, ['1', 'true', 'yes', 'on'], true);
	}

	/**
	 * Integer limit; returns null for "unlimited" or missing.
	 */
	public function limit(string $key): ?int
	{
		if (! $this->has($key)) {
			return null;
		}
		$v = strtolower(trim((string) $this->features[$key]));
		if ($v === '' || $v === 'unlimited') {
			return null;
		}
		if (ctype_digit($v)) {
			return (int) $v;
		}
		return null;
	}

	public function isUnlimited(string $key): bool
	{
		if (! $this->has($key)) {
			return false;
		}
		return strtolower(trim((string) $this->features[$key])) === 'unlimited';
	}

	/** @return array<string,string> */
	public function all(): array
	{
		return $this->features;
	}
}
