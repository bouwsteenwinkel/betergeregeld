<?php

namespace Tests\Unit\AccessGuard\Radar\Freshness;

use App\Models\AccessGuard\Radar\SoftwareCatalogEntry;
use App\Models\AccessGuard\Radar\SoftwareInstance;
use App\Services\AccessGuard\Radar\Freshness\FreshnessCalculator;
use App\Services\AccessGuard\Radar\Freshness\FreshnessStatus;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class FreshnessCalculatorTest extends TestCase
{
	private FreshnessCalculator $calc;

	protected function setUp(): void
	{
		parent::setUp();
		$this->calc = new FreshnessCalculator();
		// Pin "now" so EOL date assertions don't drift over time.
		Carbon::setTestNow('2026-04-25');
	}

	protected function tearDown(): void
	{
		Carbon::setTestNow();
		parent::tearDown();
	}

	public function test_up_to_date_when_versions_match(): void
	{
		$verdict = $this->calc->calculate(
			$this->instance('wordpress', 'wordpress', '6.7.1'),
			$this->catalog('wordpress', 'wordpress', '6.7.1'),
		);
		$this->assertSame(FreshnessStatus::UpToDate, $verdict->status);
		$this->assertSame(0, $verdict->versionsBehind);
	}

	public function test_outdated_safe_when_older_than_latest(): void
	{
		$verdict = $this->calc->calculate(
			$this->instance('wordpress', 'wordpress', '6.4.2'),
			$this->catalog('wordpress', 'wordpress', '6.7.1', releaseHistory: [
				['version' => '6.7.1'], ['version' => '6.7.0'], ['version' => '6.6.2'],
				['version' => '6.5.5'], ['version' => '6.4.2'],
			]),
		);
		$this->assertSame(FreshnessStatus::OutdatedSafe, $verdict->status);
		$this->assertSame(4, $verdict->versionsBehind);
	}

	public function test_eol_when_minor_version_past_eol_date(): void
	{
		$verdict = $this->calc->calculate(
			$this->instance('php', 'php', '7.4.33'),
			$this->catalog('php', 'php', '8.3.10', eolDates: [
				'7.4' => '2022-11-28',
				'8.0' => '2023-11-26',
				'8.3' => '2027-12-31',
			]),
		);
		$this->assertSame(FreshnessStatus::Eol, $verdict->status);
		$this->assertNotNull($verdict->eolDate);
	}

	public function test_eol_does_not_trigger_when_date_is_future(): void
	{
		$verdict = $this->calc->calculate(
			$this->instance('php', 'php', '8.3.10'),
			$this->catalog('php', 'php', '8.3.10', eolDates: [
				'8.3' => '2027-12-31',
			]),
		);
		$this->assertSame(FreshnessStatus::UpToDate, $verdict->status);
	}

	public function test_prerelease_detected_regardless_of_catalog(): void
	{
		$verdict = $this->calc->calculate(
			$this->instance('wordpress', 'wordpress', '6.8-RC1'),
			$this->catalog('wordpress', 'wordpress', '6.7.1'),
		);
		$this->assertSame(FreshnessStatus::Prerelease, $verdict->status);
	}

	public function test_unknown_when_version_missing(): void
	{
		$verdict = $this->calc->calculate(
			$this->instance('apache', 'httpd', null),
			$this->catalog('apache', 'httpd', '2.4.62'),
		);
		$this->assertSame(FreshnessStatus::Unknown, $verdict->status);
	}

	public function test_unknown_when_no_catalog_entry(): void
	{
		$verdict = $this->calc->calculate(
			$this->instance('weirdsoft', 'thing', '1.0'),
			null,
		);
		$this->assertSame(FreshnessStatus::Unknown, $verdict->status);
	}

	public function test_detected_newer_than_catalog_treated_as_up_to_date(): void
	{
		$verdict = $this->calc->calculate(
			$this->instance('nginx', 'nginx', '1.27.0'),
			$this->catalog('nginx', 'nginx', '1.26.0'),
		);
		$this->assertSame(FreshnessStatus::UpToDate, $verdict->status);
	}

	public function test_eol_overrides_outdated(): void
	{
		// PHP 7.4.33 is BOTH outdated and EOL. EOL wins.
		$verdict = $this->calc->calculate(
			$this->instance('php', 'php', '7.4.33'),
			$this->catalog('php', 'php', '8.3.10', eolDates: ['7.4' => '2022-11-28']),
		);
		$this->assertSame(FreshnessStatus::Eol, $verdict->status);
	}

	private function instance(string $vendor, string $product, ?string $version): SoftwareInstance
	{
		$si = new SoftwareInstance();
		$si->vendor = $vendor;
		$si->product = $product;
		$si->version = $version;
		return $si;
	}

	private function catalog(
		string $vendor,
		string $product,
		string $latest,
		array $releaseHistory = [],
		array $eolDates = [],
	): SoftwareCatalogEntry {
		$c = new SoftwareCatalogEntry();
		$c->vendor = $vendor;
		$c->product = $product;
		$c->latest_stable_version = $latest;
		$c->source = 'manual';
		$c->release_history = $releaseHistory;
		$c->eol_dates = $eolDates;
		return $c;
	}
}
