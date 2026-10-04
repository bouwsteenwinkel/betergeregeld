<?php

namespace Tests\Feature\Monitor;

use App\Models\Monitor\Metric;
use App\Models\Monitor\Server;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Schijfalarmen van monitor:check-alerts: de drempel (85%) en het groei-alarm (> 2 GB/uur).
 *
 * Aanleiding: op 04-10-2026 liep de productieschijf in een avond van 93% naar 100%, MySQL viel
 * om en alles lag ~8 uur plat zonder dat er een mail kwam.
 *
 * Geen RefreshDatabase (migrate:fresh loopt in dit project stuk op sqlite): alleen de
 * monitor-migraties op de :memory:-wegwerpdatabase, met een harde sqlite-grendel zodat de
 * echte database nooit geraakt wordt. De tabellen die de overige dimensies van het commando
 * lezen (sites, uptime-checks, SocketLabs) worden leeg en minimaal aangemaakt.
 */
class DiskAlertTest extends TestCase
{
	private const TO = 'alarm@example.test';

	protected function setUp(): void
	{
		parent::setUp();

		if (DB::connection()->getDriverName() !== 'sqlite') {
			$this->fail('Deze test mag alleen op de sqlite-wegwerpdatabase draaien.');
		}

		// De monitor-migratie hangt een server_id aan tenants; minimaal voorzetten.
		Schema::create('tenants', function (Blueprint $t) {
			$t->uuid('id')->primary();
		});

		foreach ([
			'2026_06_06_120000_create_monitor_tables.php',
			'2026_06_06_130000_add_alerts_to_monitor_servers.php',
			'2026_08_23_140000_add_trend_alerts_to_monitor_servers.php',
			'2026_10_04_120000_add_growth_alert_to_monitor_servers.php',
		] as $migratie) {
			(require database_path('migrations/' . $migratie))->up();
		}

		Schema::create('seo_properties', function (Blueprint $t) {
			$t->uuid('id')->primary();
			$t->uuid('tenant_id')->nullable();
			$t->boolean('is_active')->default(true);
			$t->boolean('is_demo')->default(false);
			$t->timestamp('integrity_checked_at')->nullable();
			$t->timestamp('software_reported_at')->nullable();
		});
		Schema::create('monitor_checks', function (Blueprint $t) {
			$t->uuid('id')->primary();
			$t->uuid('property_id')->nullable();
			$t->boolean('is_active')->default(true);
			$t->boolean('is_demo')->default(false);
			$t->string('last_status')->nullable();
		});
		Schema::create('socketlabs_events', function (Blueprint $t) {
			$t->id();
		});

		config()->set('monitor.alert_email', self::TO);
		config()->set('socketlabs.webhook_secret', null);
		config()->set('socketlabs.api_key', null);
		config()->set('mail.default', 'array');

		Carbon::setTestNow(Carbon::parse('2026-10-04 12:00:00'));
	}

	protected function tearDown(): void
	{
		Carbon::setTestNow();
		parent::tearDown();
	}

	private function server(?float $diskPercent = 50.0): Server
	{
		$server = Server::create(['name' => 'Productie-VPS', 'ip_address' => '10.0.0.1', 'is_active' => true, 'alerts_enabled' => true]);
		$server->forceFill(['agent_last_seen_at' => now(), 'last_disk_percent' => $diskPercent])->save();

		return $server;
	}

	/** Eén monster per minuut, $vanMin tot en met $totMin minuten geleden, lineair groeiend. */
	private function metrics(Server $server, float $gebruiktNu, float $perUur, int $vanMin, int $totMin = 0, float $totaal = 500.0): void
	{
		for ($m = $vanMin; $m >= $totMin; $m--) {
			$gebruikt = $gebruiktNu - $perUur * $m / 60;
			Metric::create([
				'server_id'     => $server->id,
				'collected_at'  => now()->subMinutes($m),
				'disk_used_gb'  => round($gebruikt, 4),
				'disk_total_gb' => $totaal,
				'disk_percent'  => round($gebruikt / $totaal * 100, 2),
			]);
		}
	}

	/** @return array<int,array{subject:string,body:string,to:string}> */
	private function mails(?string $bevat = null): array
	{
		$uit = [];
		foreach (Mail::mailer('array')->getSymfonyTransport()->messages() as $sent) {
			$mail = $sent->getOriginalMessage();
			$uit[] = [
				'subject' => (string) $mail->getSubject(),
				'body'    => (string) $mail->getTextBody(),
				'to'      => $mail->getTo()[0]->getAddress(),
			];
		}

		return array_values(array_filter($uit, fn ($m) => $bevat === null || str_contains($m['subject'], $bevat)));
	}

	private function run_(): void
	{
		$this->artisan('monitor:check-alerts')->assertSuccessful();
	}

	public function test_drempel_staat_standaard_op_85_procent(): void
	{
		$this->assertSame(85, (int) config('monitor.disk_warn'));
	}

	public function test_84_procent_geeft_geen_alarm(): void
	{
		$server = $this->server(84.0);

		$this->run_();

		$this->assertSame([], $this->mails());
		$this->assertSame('ok', $server->fresh()->alert_state);
	}

	public function test_86_procent_alarmeert_een_keer_en_meldt_herstel(): void
	{
		$server = $this->server(86.0);

		$this->run_();
		$this->run_(); // tweede run: zelfde toestand, geen herhaling

		$alarmen = $this->mails('SCHIJF VOL');
		$this->assertCount(1, $alarmen);
		$this->assertSame(self::TO, $alarmen[0]['to']);
		$this->assertStringContainsString('86%', $alarmen[0]['body']);
		$this->assertSame('disk', $server->fresh()->alert_state);

		$server->forceFill(['last_disk_percent' => 70.0])->save();
		$this->run_();

		$herstel = $this->mails('HERSTELD');
		$this->assertCount(1, $herstel);
		$this->assertStringContainsString('Productie-VPS', $herstel[0]['subject']);
		$this->assertSame('ok', $server->fresh()->alert_state);
		$this->assertCount(2, $this->mails());
	}

	public function test_groei_van_2_5_gb_per_uur_alarmeert_met_prognose(): void
	{
		$server = $this->server(60.0);
		$this->metrics($server, 302.5, 2.5, 70);

		$this->run_();
		$this->run_(); // geen herhaling

		$mails = $this->mails('SCHIJF GROEIT SNEL');
		$this->assertCount(1, $mails);
		$this->assertStringContainsString('+2,5 GB per uur', $mails[0]['body']);
		// 500 GB totaal, ~302,3 GB gebruikt => ~197,7 GB vrij / 2,5 GB per uur = ~79 uur.
		$this->assertStringContainsString('In dit tempo vol over ongeveer 79 uur', $mails[0]['body']);
		$this->assertSame('groei', $server->fresh()->growth_alert_state);
		$this->assertCount(1, $this->mails(), 'alleen het groei-alarm, geen drempel- of trendmail');
	}

	public function test_groei_herstelt_als_het_tempo_zakt(): void
	{
		$server = $this->server(60.0);
		$this->metrics($server, 302.5, 2.5, 70);
		$this->run_();

		Metric::query()->delete();
		$this->metrics($server, 303.0, 0.2, 70);
		$this->run_();

		$this->assertCount(1, $this->mails('HERSTELD'));
		$this->assertSame('ok', $server->fresh()->growth_alert_state);
	}

	public function test_groei_van_1_gb_per_uur_geeft_geen_alarm(): void
	{
		$server = $this->server(60.0);
		$this->metrics($server, 301.0, 1.0, 70);

		$this->run_();

		$this->assertSame([], $this->mails());
		$this->assertSame('ok', $server->fresh()->growth_alert_state);
	}

	public function test_zonder_meting_van_een_uur_geleden_geen_alarm(): void
	{
		$server = $this->server(60.0);
		// Alleen de laatste 20 minuten, wel met een moordend tempo: geen beginmeting = geen oordeel.
		$this->metrics($server, 310.0, 30.0, 20);

		$this->run_();

		$this->assertSame([], $this->mails());
		$this->assertSame('ok', $server->fresh()->growth_alert_state);
	}

	public function test_zonder_recente_metingen_geen_alarm(): void
	{
		$server = $this->server(60.0);
		// Agent zweeg de laatste 15 minuten: geen eindmeting = geen oordeel.
		$this->metrics($server, 310.0, 30.0, 70, 15);

		$this->run_();

		$this->assertSame([], $this->mails('GROEIT'));
		$this->assertSame('ok', $server->fresh()->growth_alert_state);
	}
}
