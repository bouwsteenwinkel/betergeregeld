<?php

namespace App\Services\AccessGuard\Radar\Matching;

/**
 * Tiny semver-ish range parser & comparator. Handles the subset of
 * range syntax we actually emit from CISA KEV ('*') and OSV (`>=A <B`).
 *
 * Deliberately NOT pulling in composer/semver — we want zero deps and
 * predictable behaviour on weird WordPress version strings ("6.4.2.1",
 * "8.3.10-pl0"). For those, parse() ignores anything after a non-numeric
 * char in each component.
 *
 * Supported syntax:
 *   *                              → matches everything
 *   >= A     |   < B               → single bound
 *   >= A < B                       → range
 *   = A     |   A     |   ==A      → exact match
 *   <= A    |   > A                → also supported
 *
 * Unknown / unparseable strings: contains() returns false (fail-safe —
 * we don't flag a vuln we can't reason about).
 */
final class VersionRange
{
	public static function contains(string $range, string $version): bool
	{
		$range = trim($range);
		if ($range === '' || $range === '*') return true;
		if ($version === '') return false;

		$detected = self::parse($version);
		// Split by whitespace; each token is one constraint
		$tokens = preg_split('/\s+/', $range, -1, PREG_SPLIT_NO_EMPTY) ?: [];
		foreach ($tokens as $tok) {
			if (! self::matchesConstraint($tok, $detected)) {
				return false;
			}
		}
		return true;
	}

	/** @param  int[]  $version */
	private static function matchesConstraint(string $tok, array $version): bool
	{
		// Pull the operator off the front
		if (preg_match('~^(>=|<=|==|!=|>|<|=)?\s*(.+)$~', $tok, $m)) {
			$op = $m[1] ?: '=';
			$ref = self::parse(trim($m[2]));
		} else {
			return false;
		}

		$cmp = self::compare($version, $ref);
		return match ($op) {
			'>='     => $cmp >= 0,
			'<='     => $cmp <= 0,
			'>'      => $cmp >  0,
			'<'      => $cmp <  0,
			'!='     => $cmp !== 0,
			'==', '=' => $cmp === 0,
			default  => false,
		};
	}

	/** @return int[] */
	public static function parse(string $version): array
	{
		// Drop pre-release / build suffixes so "6.4.2-rc1" → "6.4.2"
		$core = preg_replace('~[-+].*$~', '', $version) ?? $version;
		$out = [];
		foreach (explode('.', $core) as $part) {
			if (! preg_match('~^(\d+)~', $part, $m)) break;
			$out[] = (int) $m[1];
		}
		return $out;
	}

	/**
	 * @param  int[]  $a
	 * @param  int[]  $b
	 */
	public static function compare(array $a, array $b): int
	{
		$len = max(count($a), count($b));
		for ($i = 0; $i < $len; $i++) {
			$x = $a[$i] ?? 0;
			$y = $b[$i] ?? 0;
			if ($x !== $y) return $x < $y ? -1 : 1;
		}
		return 0;
	}
}
