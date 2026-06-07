<?php

namespace Tests\Feature\QualityScan;

use App\Services\QualityScan\Data\Finding;
use App\Services\QualityScan\QualityScanService;
use Tests\TestCase;

class ScoreTest extends TestCase
{
	private function score(array $findings): int
	{
		return app(QualityScanService::class)->computeScore($findings);
	}

	private function f(string $status, string $severity = 'laag'): Finding
	{
		return new Finding('deterministic', 'x', $status, $severity, 'finding');
	}

	public function test_alleen_passes_geeft_volle_score(): void
	{
		$this->assertSame(100, $this->score([$this->f('pass'), $this->f('pass', 'hoog')]));
	}

	public function test_weging_per_ernst(): void
	{
		// fail hoog -15, fail middel -8, fail laag -4, warn -2
		$this->assertSame(85, $this->score([$this->f('fail', 'hoog')]));
		$this->assertSame(92, $this->score([$this->f('fail', 'middel')]));
		$this->assertSame(96, $this->score([$this->f('fail', 'laag')]));
		$this->assertSame(98, $this->score([$this->f('warn')]));
		$this->assertSame(100 - 15 - 8 - 2, $this->score([
			$this->f('fail', 'hoog'), $this->f('fail', 'middel'), $this->f('warn'),
		]));
	}

	public function test_score_zakt_niet_onder_nul(): void
	{
		$findings = array_fill(0, 20, $this->f('fail', 'hoog'));
		$this->assertSame(0, $this->score($findings));
	}
}
