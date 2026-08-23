<?php

namespace App\Console\Commands;

use App\Models\Monitor\Check;
use App\Models\Monitor\Server;
use App\Models\Security\SecurityScan;
use App\Models\Seo\SeoImportsLog;
use App\Models\Seo\SeoProperty;
use App\Services\Monitor\TrendAnalyzer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Vergelijkt per server de huidige toestand met de laatst-gemelde (alert_state)
 * en mailt alleen bij een overgang — dus één mail bij offline-gaan, één bij
 * herstel, geen herhaling elke run. Bedoeld om elke paar minuten te draaien.
 */
class MonitorCheckAlerts extends Command
{
	protected $signature = 'monitor:check-alerts';

	protected $description = 'Mail bij offline/volle-schijf-overgangen van gemonitorde servers.';

	public function handle(): int
	{
		$to = config('monitor.alert_email');
		$servers = Server::query()->where('is_active', true)->where('alerts_enabled', true)->get();
		$changes = 0;

		foreach ($servers as $server) {
			$condition = $server->currentCondition();

			if ($condition === $server->alert_state) {
				continue;
			}

			$previous = $server->alert_state;
			$server->forceFill(['alert_state' => $condition, 'alerted_at' => now()])->save();
			$changes++;

			if ($to) {
				[$subject, $body] = $this->message($server, $condition, $previous);
				Mail::raw($body, fn ($m) => $m->to($to)->subject($subject));
			}

			$this->info("{$server->name}: {$previous} → {$condition}");
		}

		$this->info("Klaar — {$changes} overgang(en) van " . $servers->count() . ' server(s).');

		$this->checkTrends($to);
		$this->checkSeoFreshness($to);
		$this->checkUptimeChecks();
		$this->checkSecurity();
		$this->checkSoftware();
		$this->checkIntegrity();
		$this->checkSocketLabs($to);

		return self::SUCCESS;
	}

	/**
	 * File-integrity-alerting: mailt bij een OVERGANG naar/uit afwijkende
	 * WP-core-bestanden (gewijzigd/onverwacht t.o.v. de officiële checksums) —
	 * een sterk hack-/malware-signaal.
	 */
	private function checkIntegrity(): void
	{
		$props = SeoProperty::query()->where('is_active', true)->where('is_demo', false)
			->whereNotNull('integrity_checked_at')->with('tenant.agency')->get();

		foreach ($props as $prop) {
			$count = (int) DB::table('site_integrity_issues')->where('property_id', $prop->id)
				->whereIn('type', ['modified', 'unexpected'])->count();
			$condition = $count > 0 ? 'flagged' : 'ok';
			$previous = $prop->integrity_alert_state;
			if ($condition === $previous) {
				continue;
			}

			$shouldMail = $previous !== null || $condition === 'flagged';
			$prop->forceFill(['integrity_alert_state' => $condition, 'integrity_alerted_at' => now()])->save();
			if (! $shouldMail) {
				continue;
			}

			$recipients = $this->recipientsFor($prop);
			if (empty($recipients)) {
				continue;
			}

			if ($condition === 'flagged') {
				$subject = "[Security] Gewijzigde core-bestanden op {$prop->label}";
				$body = "Op '{$prop->label}' ({$prop->domain()}) wijken {$count} WordPress-core-bestand"
					. ($count === 1 ? '' : 'en') . " af van de officiële WP.org-checksums.\n\n"
					. 'Dit kan wijzen op een hack of malware-injectie — controleer de site direct.';
			} else {
				$subject = "[Security] Core-bestanden weer intact op {$prop->label}";
				$body = "De eerder gemelde afwijkingen in core-bestanden op '{$prop->label}' ({$prop->domain()}) zijn opgelost.";
			}

			Mail::raw($body, fn ($m) => $m->to($recipients)->subject($subject));
			$this->info("INTEGRITY [{$prop->label}]: " . ($previous ?? 'init') . " → {$condition}");
		}
	}

	/**
	 * SocketLabs-mailmonitoring: mailt bij een OVERGANG per dimensie — queue
	 * (Deferred/Queued-backlog), failures (bezorgfouten), complaints (klachten)
	 * en silence (geen events meer). State-based, dus één mail per overgang.
	 */
	private function checkSocketLabs(?string $to): void
	{
		$eval = app(\App\Services\Monitor\SocketLabsEvaluator::class);
		if (! $eval->isActive()) {
			return;
		}

		$c = $eval->conditions();
		$status = \App\Models\Monitor\SocketLabsStatus::instance();

		$dims = [
			'queue'     => ['state' => 'queue_state',     'at' => 'queue_alerted_at'],
			'failure'   => ['state' => 'failure_state',   'at' => 'failure_alerted_at'],
			'complaint' => ['state' => 'complaint_state', 'at' => 'complaint_alerted_at'],
			'silence'   => ['state' => 'silence_state',   'at' => 'silence_alerted_at'],
			'api'       => ['state' => 'api_state',       'at' => 'api_alerted_at'],
		];

		foreach ($dims as $dim => $col) {
			$current = $c[$dim];
			$previous = $status->{$col['state']};
			if ($current === $previous) {
				continue;
			}

			$shouldMail = $previous !== null || $current === 'alert';
			$status->forceFill([$col['state'] => $current, $col['at'] => now()])->save();

			if ($shouldMail && $to) {
				[$subject, $body] = $this->socketLabsMessage($dim, $current, $c);
				Mail::raw($body, fn ($m) => $m->to($to)->subject($subject));
			}

			$this->info("SOCKETLABS [{$dim}]: " . ($previous ?? 'init') . " → {$current}");
		}

		$status->forceFill(['counts' => $c['counts'], 'last_evaluated_at' => now()])->save();
	}

	/**
	 * @param array<string,mixed> $c
	 * @return array{0:string,1:string}
	 */
	private function socketLabsMessage(string $dim, string $condition, array $c): array
	{
		$n = $c['counts'];
		$stat = "Venster: laatste {$c['window']} min\n"
			. "Delivered {$n['Delivered']} · Failed {$n['Failed']} · Deferred {$n['Deferred']} · Queued {$n['Queued']} · Complaint {$n['Complaint']}";

		if ($condition === 'ok') {
			$ok = [
				'queue'     => 'SocketLabs-queue weer normaal',
				'failure'   => 'SocketLabs-bezorging weer normaal',
				'complaint' => 'SocketLabs-klachten weer normaal',
				'silence'   => 'SocketLabs ontvangt weer events',
				'api'       => 'SocketLabs-API weer bereikbaar',
			];
			return ["[Mail] HERSTELD: {$ok[$dim]}", "{$ok[$dim]}.\n\n{$stat}"];
		}

		$alert = [
			'queue'     => ['[Mail] ALERT: SocketLabs-queue loopt vol', 'Er stapelen Deferred/Queued-events op — uitgaande mail wordt uitgesteld of de queue loopt vol.'],
			'failure'   => ['[Mail] ALERT: veel SocketLabs-bezorgfouten', "Het Failed-aandeel is {$c['failure_rate']}% (drempel " . config('socketlabs.failure_rate_pct') . '%).'],
			'complaint' => ['[Mail] ALERT: SocketLabs-klachten (spam)', "Er zijn {$n['Complaint']} klacht(en) binnengekomen — let op de sender-reputatie."],
			'silence'   => ['[Mail] ALERT: SocketLabs stil', 'Er zijn ' . config('socketlabs.silence_minutes') . ' min geen events meer ontvangen — mogelijk verstuurt de site geen mail of vallen de webhooks uit.'],
			'api'       => ['[Mail] ALERT: SocketLabs-API onbereikbaar', 'De periodieke SocketLabs-API-poll faalt — controleer de API-key/ServerID en of de API bereikbaar is.'],
		];

		[$subject, $reason] = $alert[$dim];
		return [$subject, "{$reason}\n\n{$stat}\n\nControleer SocketLabs en de mailconfiguratie."];
	}

	/**
	 * Software-alerting: mailt bij een OVERGANG naar/uit kwetsbaar (≥1 gematchte
	 * kwetsbaarheid in de gerapporteerde CMS/plugins/thema's). Alleen voor sites
	 * waar de companion-agent al heeft gerapporteerd.
	 */
	private function checkSoftware(): void
	{
		$props = SeoProperty::query()->where('is_active', true)->where('is_demo', false)
			->whereNotNull('software_reported_at')->with('tenant.agency')->get();

		foreach ($props as $prop) {
			$vulnCount = (int) DB::table('site_vulnerabilities')->where('property_id', $prop->id)->count();
			$condition = $vulnCount > 0 ? 'flagged' : 'ok';
			$previous = $prop->software_alert_state;
			if ($condition === $previous) {
				continue;
			}

			$shouldMail = $previous !== null || $condition === 'flagged';
			$prop->forceFill(['software_alert_state' => $condition, 'software_alerted_at' => now()])->save();
			if (! $shouldMail) {
				continue;
			}

			$recipients = $this->recipientsFor($prop);
			if (empty($recipients)) {
				continue;
			}

			if ($condition === 'flagged') {
				$subject = "[Security] Kwetsbare software op {$prop->label}";
				$body = "Op de site '{$prop->label}' ({$prop->domain()}) zijn {$vulnCount} bekende kwetsbaarhe"
					. ($vulnCount === 1 ? 'id' : 'den') . " gevonden in de geïnstalleerde software (CMS/plugins/thema's).\n\n"
					. 'Bekijk de details in het dashboard en werk de betreffende onderdelen bij.';
			} else {
				$subject = "[Security] Software weer veilig op {$prop->label}";
				$body = "De eerder gemelde kwetsbaarheden op '{$prop->label}' ({$prop->domain()}) zijn opgelost.";
			}

			Mail::raw($body, fn ($m) => $m->to($recipients)->subject($subject));
			$this->info("SOFTWARE [{$prop->label}]: " . ($previous ?? 'init') . " → {$condition}");
		}
	}

	/**
	 * Security-alerting: mailt bij een OVERGANG naar/uit 'flagged' (op een
	 * blacklist of door Safe Browsing gemarkeerd) op basis van de laatste
	 * security-scan. Mixed content/broken links zijn dashboard-only (geen mail).
	 */
	private function checkSecurity(): void
	{
		foreach (SeoProperty::query()->where('is_active', true)->where('is_demo', false)->with('tenant.agency')->get() as $prop) {
			$scan = SecurityScan::query()->where('property_id', $prop->id)
				->where('status', 'completed')->latest('completed_at')->first();
			if (! $scan) {
				continue;
			}

			$condition = $scan->isFlagged() ? 'flagged' : 'ok';
			$previous = $prop->security_alert_state;
			if ($condition === $previous) {
				continue;
			}

			$shouldMail = $previous !== null || $condition === 'flagged';
			$prop->forceFill(['security_alert_state' => $condition, 'security_alerted_at' => now()])->save();
			if (! $shouldMail) {
				continue;
			}

			$recipients = $this->recipientsFor($prop);
			if (empty($recipients)) {
				continue;
			}

			[$subject, $body] = $this->securityMessage($prop, $scan, $condition);
			Mail::raw($body, fn ($m) => $m->to($recipients)->subject($subject));
			$this->info("SECURITY [{$prop->label}]: " . ($previous ?? 'init') . " → {$condition}");
		}
	}

	/**
	 * @return array{0:string,1:string}
	 */
	private function securityMessage(SeoProperty $prop, SecurityScan $scan, string $condition): array
	{
		$domain = $prop->domain();

		if ($condition === 'ok') {
			return [
				"[Security] HERSTELD: {$prop->label} weer schoon",
				"De beveiligingswaarschuwing voor '{$prop->label}' ({$domain}) is opgeheven — niet langer op een blacklist of door Safe Browsing gemarkeerd.",
			];
		}

		$reasons = [];
		if ($scan->blacklisted) {
			$reasons[] = 'staat op een blacklist';
		}
		if ($scan->safe_browsing === 'flagged') {
			$reasons[] = 'is door Google Safe Browsing gemarkeerd (malware/phishing)';
		}

		return [
			"[Security] ALERT: {$prop->label} — beveiligingsprobleem",
			"De site '{$prop->label}' ({$domain}) " . implode(' en ', $reasons) . ".\n\n"
				. 'Controleer de site direct en onderneem actie.',
		];
	}

	/**
	 * Downtime-alerting voor uptime-checks: mailt bij up->down en weer bij
	 * down->up, state-based (één mail per overgang) net als de server-alerts.
	 * Een nieuwe check wordt stil geïnitialiseerd, behalve als hij meteen down
	 * is — dat is direct actiewaardig.
	 */
	private function checkUptimeChecks(): void
	{
		$checks = Check::query()
			->where('is_active', true)
			->where('is_demo', false)
			->whereIn('last_status', ['up', 'down'])
			->with('property.tenant.agency')
			->get();

		$changes = 0;

		foreach ($checks as $check) {
			$current = (string) $check->last_status;
			$previous = $check->alert_state;

			if ($current === $previous) {
				continue;
			}

			$shouldMail = $previous !== null || $current === 'down';
			$check->forceFill(['alert_state' => $current, 'alerted_at' => now()])->save();

			if (! $shouldMail) {
				continue; // stille initialisatie van een nieuwe, bereikbare check
			}

			$recipients = $this->recipientsFor($check->property);
			if (! empty($recipients)) {
				[$subject, $body] = $this->checkMessage($check, $current);
				Mail::raw($body, fn ($m) => $m->to($recipients)->subject($subject));
			}

			$label = $check->property?->label ?? $check->name;
			$this->info("CHECK [{$label}]: " . ($previous ?? 'init') . " → {$current}");
			$changes++;
		}

		$this->info("Klaar — {$changes} check-overgang(en) van " . $checks->count() . ' check(s).');
	}

	/**
	 * Aan wie gaat een melding over deze site? Per-klant notify_email →
	 * bureau-contact → platform-vangnet. Minstens één adres, of leeg.
	 *
	 * @return string[]
	 */
	private function recipientsFor(?SeoProperty $prop): array
	{
		$emails = [];
		if ($prop) {
			$emails[] = $prop->notify_email;
			$emails[] = $prop->tenant?->agency?->contact_email;
		}
		$emails = array_values(array_unique(array_filter($emails)));

		if (! empty($emails)) {
			return $emails;
		}

		$platform = config('monitor.alert_email');

		return $platform ? [$platform] : [];
	}

	/**
	 * @return array{0:string,1:string}
	 */
	private function checkMessage(Check $check, string $condition): array
	{
		$label   = $check->property?->label ?? $check->name;
		$target  = $check->target;
		$when    = $check->last_checked_at?->diffForHumans() ?? 'zojuist';
		$code    = $check->last_code ?? '-';
		$latency = $check->last_latency_ms !== null ? "{$check->last_latency_ms} ms" : '-';

		if ($condition === 'up') {
			return [
				"[Monitoring] HERSTELD: {$label} is weer bereikbaar",
				"De site '{$label}' ({$target}) is weer online.\n\n"
					. "HTTP-status: {$code}\nResponstijd: {$latency}\nGecontroleerd: {$when}",
			];
		}

		return [
			"[Monitoring] OFFLINE: {$label} is onbereikbaar",
			"De site '{$label}' ({$target}) reageert niet zoals verwacht.\n\n"
				. "HTTP-status: {$code}\nResponstijd: {$latency}\nGecontroleerd: {$when}\n\n"
				. 'Controleer de site en de server.',
		];
	}

	/**
	 * SEO-import-versheid: mailt — net als de server-alerts, alléén bij OVERGANG —
	 * wanneer een GSC-property al > config('seo.freshness_alert_days') dagen geen
	 * succesvolle import had (stale), en opnieuw één mail bij herstel. Zo valt een
	 * stilgevallen import (scheduler/cron weg, of credential weg) niet meer
	 * wekenlang ongemerkt uit.
	 */
	private function checkSeoFreshness(?string $to): void
	{
		$days = (int) config('seo.freshness_alert_days', 3);

		foreach (SeoProperty::query()->where('is_active', true)->where('is_demo', false)->with('tenant.agency')->get() as $prop) {
			$condition = $prop->freshnessCondition($days);
			$previous = $prop->freshness_alert_state ?? 'ok';

			if ($condition === $previous) {
				continue;
			}

			$prop->forceFill([
				'freshness_alert_state' => $condition,
				'freshness_alerted_at'  => now(),
			])->save();

			$this->info("SEO [{$prop->label}]: {$previous} → {$condition}");

			$recipients = $this->recipientsFor($prop);
			if (empty($recipients)) {
				continue;
			}

			if ($condition === 'stale') {
				$last = SeoImportsLog::query()
					->where('property_id', $prop->id)
					->where('status', 'success')
					->latest('id')
					->value('finished_at');
				$when = $last
					? \Illuminate\Support\Carbon::parse($last)->diffForHumans()
					: 'nooit / geen succesvolle import';

				$subject = "[SEO] ALERT: Search Console-import staat stil — {$prop->label}";
				$body = "De Search Console-import voor '{$prop->label}' ({$prop->site_url}) is al > {$days} dagen niet succesvol geweest.\n\n"
					. "Laatste succesvolle import: {$when}\n"
					. 'Laatste foutmelding: ' . ($prop->last_import_error ?? '-') . "\n\n"
					. "Controleer:\n"
					. "  • draait de scheduler-cron (php artisan schedule:run) op de server?\n"
					. "  • staat de service-account-JSON er (storage/app/google-api.json of GOOGLE_API_KEY_PATH)?\n\n"
					. 'Handmatig bijwerken: php artisan seo:import-gsc';

				Mail::raw($body, fn ($m) => $m->to($recipients)->subject($subject));
			} elseif ($previous === 'stale') {
				$subject = "[SEO] HERSTELD: Search Console-import draait weer — {$prop->label}";
				$body = "De Search Console-import voor '{$prop->label}' ({$prop->site_url}) is weer up-to-date.";

				Mail::raw($body, fn ($m) => $m->to($recipients)->subject($subject));
			}
		}
	}

	/**
	 * Trendwaarschuwingen: schijf of geheugen loopt vol bij het huidige tempo.
	 *
	 * Waarom naast de drempels: die zeggen "90% bereikt" en dat is op een schijf
	 * van 400 GB pas een signaal als je nog dagen hebt. Deze zegt het weken van
	 * tevoren. Transitie-gebaseerd zoals de rest — één mail als de prognose onder
	 * de grens zakt, één als het weer goed komt, geen herhaling elke tien minuten.
	 */
	private function checkTrends(?string $to): void
	{
		$grensDagen = (int) config('monitor.trend_warn_days', 45);
		$analyse = TrendAnalyzer::make();

		$servers = Server::query()->where('is_active', true)->where('alerts_enabled', true)->get();

		foreach ($servers as $server) {
			$schijf = $analyse->schijf($server);
			$geheugen = $analyse->geheugen($server);

			$redenen = [];

			if ($schijf !== null && $schijf['dagen'] !== null && $schijf['dagen'] <= $grensDagen) {
				$redenen[] = sprintf(
					'Schijf: +%s GB/dag, nog %s GB vrij van %s GB — vol over ongeveer %s dagen.',
					number_format($schijf['per_dag'], 2, ',', '.'),
					number_format($schijf['vrij_gb'], 1, ',', '.'),
					number_format($schijf['totaal_gb'], 0, ',', '.'),
					number_format($schijf['dagen'], 0, ',', '.')
				);
			}

			if ($geheugen !== null && $geheugen['dagen'] !== null && $geheugen['dagen'] <= $grensDagen) {
				$redenen[] = sprintf(
					'Geheugen: +%s procentpunt/dag, nu %s%% — op %s%% over ongeveer %s dagen.',
					number_format($geheugen['per_dag'], 2, ',', '.'),
					number_format($geheugen['nu_pct'], 1, ',', '.'),
					$geheugen['grens_pct'],
					number_format($geheugen['dagen'], 0, ',', '.')
				);
			}

			$conditie = $redenen === [] ? 'ok' : 'trend';

			if ($conditie === ($server->trend_alert_state ?? 'ok')) {
				continue;
			}

			$vorige = $server->trend_alert_state ?? 'ok';
			$server->forceFill([
				'trend_alert_state' => $conditie,
				'trend_alerted_at'  => now(),
			])->save();

			if ($to) {
				$url = route('filament.admin.resources.monitor-servers.index');

				if ($conditie === 'trend') {
					$onderwerp = "[Monitoring] TREND: {$server->name} — loopt vol";
					$tekst = "Server '{$server->name}' loopt bij het huidige tempo vol.

"
						. implode("
", $redenen)
						. "

Gemeten over de laatste " . (int) config('monitor.trend_lookback_days', 14)
						. " dagen, op dagwaarden. Dit is een prognose, geen storing —
"
						. "er is nu nog tijd om het op te lossen.

{$url}";
				} else {
					$onderwerp = "[Monitoring] TREND HERSTELD: {$server->name}";
					$tekst = "De prognose voor '{$server->name}' is weer buiten de waarschuwingsgrens "
						. "van {$grensDagen} dagen.

{$url}";
				}

				Mail::raw($tekst, fn ($m) => $m->to($to)->subject($onderwerp));
			}

			$this->info("{$server->name} (trend): {$vorige} → {$conditie}");
		}
	}

	/**
	 * @return array{0:string,1:string}
	 */
	private function message(Server $server, string $condition, string $previous): array
	{
		$lastSeen = $server->agent_last_seen_at?->diffForHumans() ?? 'nooit';
		$disk = $server->last_disk_percent !== null ? "{$server->last_disk_percent}%" : 'onbekend';
		$url = route('filament.admin.resources.monitor-servers.index');

		if ($condition === 'ok') {
			$subject = "[Monitoring] HERSTELD: {$server->name}";
			$body = "Server '{$server->name}' is weer normaal (was: {$previous}).\n\n"
				. "Laatste contact: {$lastSeen}\nSchijfgebruik: {$disk}\n\n{$url}";

			return [$subject, $body];
		}

		$reden = $condition === 'offline'
			? "De server stuurt geen heartbeat meer (laatste contact: {$lastSeen})."
			: "De systeemschijf zit vol: {$disk} gebruikt.";

		$subject = "[Monitoring] ALERT: {$server->name} — " . ($condition === 'offline' ? 'OFFLINE' : 'SCHIJF VOL');
		$body = "Server '{$server->name}' ({$server->ip_address}) heeft een probleem.\n\n"
			. "{$reden}\n\nLaatste contact: {$lastSeen}\nSchijfgebruik: {$disk}\n\nBekijk: {$url}";

		return [$subject, $body];
	}
}
