<?php

namespace App\Services;

use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

class TextDiffService
{
	/**
	 * Produces a line-oriented diff between two texts.
	 *
	 * Return shape:
	 *   ['lines' => [ ['type' => 'equal'|'added'|'removed', 'text' => '...'], ... ],
	 *    'stats' => ['added' => int, 'removed' => int, 'equal' => int] ]
	 */
	public function diff(string $left, string $right): array
	{
		$builder = new UnifiedDiffOutputBuilder('', false);
		$differ = new Differ($builder);
		$diffLines = $differ->diffToArray($left, $right);

		$lines = [];
		$stats = ['added' => 0, 'removed' => 0, 'equal' => 0];

		foreach ($diffLines as [$text, $marker]) {
			$type = match ((int) $marker) {
				Differ::ADDED => 'added',
				Differ::REMOVED => 'removed',
				default => 'equal',
			};
			$stats[$type]++;
			$lines[] = ['type' => $type, 'text' => $text];
		}

		return ['lines' => $lines, 'stats' => $stats];
	}
}
