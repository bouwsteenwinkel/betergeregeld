<?php

namespace Tests\Unit\AccessGuard\Radar\Matching;

use App\Services\AccessGuard\Radar\Matching\VersionRange;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VersionRangeTest extends TestCase
{
	#[DataProvider('cases')]
	public function test_contains(string $range, string $version, bool $expected): void
	{
		$this->assertSame($expected, VersionRange::contains($range, $version), "range='{$range}' version='{$version}'");
	}

	/** @return array<string, array{string,string,bool}> */
	public static function cases(): array
	{
		return [
			// Wildcards
			'wildcard matches anything'    => ['*', '6.4.2', true],
			'wildcard matches empty-ish'   => ['*', '0', true],

			// Single bounds
			'>= matches equal'             => ['>=1.2.3', '1.2.3', true],
			'>= matches newer'             => ['>=1.2.3', '1.3.0', true],
			'>= rejects older'             => ['>=1.2.3', '1.2.2', false],
			'< rejects equal'              => ['<2.0.0', '2.0.0', false],
			'< accepts older'              => ['<2.0.0', '1.9.9', true],

			// Two-sided range (the OSV style)
			'>=A <B mid'                   => ['>=1.0.0 <1.2.5', '1.1.4', true],
			'>=A <B at lower bound'        => ['>=1.0.0 <1.2.5', '1.0.0', true],
			'>=A <B at upper bound'        => ['>=1.0.0 <1.2.5', '1.2.5', false],
			'>=A <B above'                 => ['>=1.0.0 <1.2.5', '1.2.6', false],

			// Equality
			'exact match'                  => ['=6.4.2', '6.4.2', true],
			'bare version equals'          => ['6.4.2', '6.4.2', true],
			'exact mismatch'               => ['=6.4.2', '6.4.3', false],

			// Partial versions / weird WP-style
			'major-only equal'             => ['>=6 <7', '6.4.2', true],
			'4-part WP version'            => ['>=6.4 <6.5', '6.4.2.1', true],
			'pre-release stripped'         => ['>=6.4.0 <6.5.0', '6.4.0-rc1', true],

			// Empty / unparseable safely fail
			'empty version'                => ['>=1.0', '', false],
			'unparseable token'            => ['~1.2.3', '1.2.5', false],
		];
	}
}
