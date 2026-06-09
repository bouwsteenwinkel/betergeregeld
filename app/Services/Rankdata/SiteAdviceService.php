<?php

namespace App\Services\Rankdata;

use App\Services\Ai\AnthropicClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Bouwt per site een korte, geprioriteerde lijst met concrete actiepunten in
 * begrijpelijk Nederlands, op basis van de actuele bevindingen (kwetsbaarheden,
 * kwaliteit, security, integriteit, PageSpeed, uptime). AI-gegenereerd via
 * Claude en gecachet op een hash van de bevindingen — herberekent alleen als
 * er iets verandert. Null als er niets te doen is.
 */
class SiteAdviceService
{
	public function for(int $siteId): ?string
	{
		$summary = $this->collect($siteId);
		if ($summary === '') {
			return null; // geen issues → geen advies
		}

		$key = 'site-advice:' . $siteId . ':' . md5($summary);

		return Cache::remember($key, now()->addDays(7), function () use ($summary) {
			$client = app(AnthropicClient::class);
			$user = "Je bent een vriendelijke websitebeheerder die een niet-technische ondernemer adviseert. "
				. "Maak op basis van onderstaande bevindingen een KORTE, geprioriteerde lijst met concrete acties "
				. "in begrijpelijk Nederlands. Regels: baseer je UITSLUITEND op de gegeven bevindingen en verzin "
				. "GEEN extra punten; één actie per bevinding (vergelijkbare bevindingen mag je samenvoegen); "
				. "urgentste eerst; elk punt één zin die met een werkwoord begint; geen technisch jargon; geef per "
				. "punt kort aan waarom. Gebruik een genummerde lijst, geen kop erboven. Bevindingen:\n\n" . $summary;

			$text = $client->chat([
				'model'       => 'claude-haiku-4-5-20251001',
				'user'        => $user,
				'max_tokens'  => 500,
				'temperature' => 0.3,
			]);

			return $text ?: 'Er zijn aandachtspunten gevonden; bekijk de details hieronder op deze pagina.';
		});
	}

	private function collect(int $siteId): string
	{
		$id = $siteId;
		$lines = [];

		// Kwetsbaarheden (software).
		$vulns = DB::table('site_vulnerabilities')->where('property_id', $id)
			->orderByRaw("FIELD(severity,'critical','high','medium','low')")->limit(8)->get();
		foreach ($vulns as $v) {
			$lines[] = "- Kwetsbaarheid in {$v->name} {$v->version} ({$v->severity}): {$v->title}"
				. ($v->patched_in ? " — opgelost in versie {$v->patched_in}" : '');
		}

		// Beschikbare updates.
		$updates = DB::table('site_components')->where('property_id', $id)->where('has_update', true)
			->limit(8)->get();
		foreach ($updates as $c) {
			$lines[] = "- Update beschikbaar voor {$c->name}: {$c->version} → " . ($c->latest_version ?: 'nieuwste');
		}

		// Security-scan (malware/blacklist + mixed/broken).
		$sec = DB::table('security_scans')->where('property_id', $id)->where('status', 'completed')
			->orderByDesc('completed_at')->first();
		if ($sec) {
			if ($sec->blacklisted) {
				$lines[] = '- De site staat op een blacklist (spam/malware-lijst).';
			}
			if ($sec->safe_browsing === 'flagged') {
				$lines[] = '- Google Safe Browsing markeert de site (malware/phishing).';
			}
			if ($sec->mixed_content_count > 0) {
				$lines[] = "- {$sec->mixed_content_count} onveilige (http) elementen op beveiligde pagina's (mixed content).";
			}
			if ($sec->broken_link_count > 0) {
				$lines[] = "- {$sec->broken_link_count} kapotte links (404/500) op de site.";
			}
		}

		// File-integriteit.
		$integ = DB::table('site_integrity_issues')->where('property_id', $id)
			->whereIn('type', ['modified', 'unexpected'])->count();
		if ($integ > 0) {
			$lines[] = "- {$integ} WordPress-kernbestand(en) wijken af van het origineel (mogelijk gehackt).";
		}

		// Kwaliteit.
		$pageIds = DB::table('monitored_pages')->where('site_id', $id)->pluck('id');
		if ($pageIds->isNotEmpty()) {
			$scan = DB::table('quality_scans')->whereIn('monitored_page_id', $pageIds)
				->where('status', 'completed')->orderByDesc('completed_at')->first();
			if ($scan) {
				if ($scan->score !== null && $scan->score < 70) {
					$lines[] = "- De paginakwaliteitsscore is laag ({$scan->score}/100).";
				}
				$findings = DB::table('quality_findings')->where('quality_scan_id', $scan->id)
					->whereIn('status', ['fail', 'warn'])
					->orderByRaw("FIELD(severity,'hoog','middel','laag')")->limit(5)->get();
				foreach ($findings as $f) {
					$lines[] = "- Kwaliteit ({$f->severity}): {$f->finding}";
				}
			}
		}

		// PageSpeed.
		foreach (['mobile' => 'mobiel', 'desktop' => 'desktop'] as $strategy => $label) {
			$row = DB::table('seo_psi_daily')->where('property_id', $id)->where('strategy', $strategy)
				->orderByDesc('date')->first();
			if ($row && $row->performance_score !== null && $row->performance_score < 70) {
				$lines[] = "- Lage PageSpeed-prestatie op {$label} ({$row->performance_score}/100).";
			}
		}

		// Uptime.
		$check = DB::table('monitor_checks')->where('property_id', $id)->first();
		if ($check && $check->last_status === 'down') {
			$lines[] = '- De site is op dit moment onbereikbaar (down).';
		}

		return implode("\n", $lines);
	}
}
