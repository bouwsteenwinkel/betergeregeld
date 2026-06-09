<?php

namespace App\Services\Rankdata;

use App\Services\Ai\AnthropicClient;
use Illuminate\Support\Facades\DB;

/**
 * "Vraag het je rapport": beantwoordt een klantvraag in begrijpelijk Nederlands
 * UITSLUITEND op basis van de data van die ene site (SEO/uptime/PageSpeed/
 * kwaliteit/security/software). Geen verzonnen cijfers.
 */
class SiteChatService
{
	public function answer(int $siteId, string $question): string
	{
		$prop = DB::table('seo_properties')->where('id', $siteId)->first();
		if (! $prop) {
			return 'Deze site is niet gevonden.';
		}
		$domain = preg_replace('/^sc-domain:/', '', (string) $prop->site_url);
		$context = $this->context($siteId, (string) $prop->label, $domain);

		$user = "Je bent de data-assistent voor de website \"{$prop->label}\" ({$domain}). "
			. "Beantwoord de vraag van de klant KORT en begrijpelijk in het Nederlands, UITSLUITEND op basis van "
			. "de gegevens hieronder. Verzin geen cijfers; als het antwoord niet uit de gegevens blijkt, zeg dat "
			. "eerlijk en verwijs naar wat er wél bekend is. Geen technisch jargon.\n\n"
			. "VRAAG VAN DE KLANT:\n{$question}\n\nBESCHIKBARE GEGEVENS:\n{$context}";

		$text = app(AnthropicClient::class)->chat([
			'model'       => 'claude-haiku-4-5-20251001',
			'user'        => $user,
			'max_tokens'  => 600,
			'temperature' => 0.3,
		]);

		return $text ?: 'Het antwoord kon op dit moment niet worden opgehaald. Probeer het zo opnieuw.';
	}

	private function context(int $id, string $label, string $domain): string
	{
		$lines = ["Site: {$label} ({$domain})"];
		$since = now()->subDays(30)->toDateString();

		// SEO (Search Console, 30 dagen).
		$base = DB::table('seo_query_daily')->where('property_id', $id)->where('date', '>=', $since);
		$tot = (clone $base)->selectRaw('SUM(clicks) c, SUM(impressions) i, AVG(position) p')->first();
		if ($tot && ($tot->c || $tot->i)) {
			$clicks = (int) $tot->c;
			$impr = (int) $tot->i;
			$lines[] = "SEO 30d: {$clicks} klikken, {$impr} vertoningen, CTR "
				. ($impr ? round($clicks / $impr * 100, 1) : 0) . '%, gem. positie ' . round((float) $tot->p, 1) . '.';

			$daily = (clone $base)->selectRaw('date, SUM(clicks) c')->groupBy('date')->orderBy('date')->pluck('c')->all();
			$last7 = array_sum(array_slice($daily, -7));
			$prev7 = array_sum(array_slice($daily, -14, 7));
			if ($prev7 > 0) {
				$lines[] = 'Klik-trend: laatste 7d ' . $last7 . ' vs ' . $prev7 . ' de week ervoor ('
					. ($last7 >= $prev7 ? '+' : '') . round(($last7 - $prev7) / $prev7 * 100) . '%).';
			}

			$tq = (clone $base)->select('query')->selectRaw('SUM(clicks) c, AVG(position) p')
				->groupBy('query')->orderByRaw('SUM(clicks) DESC')->limit(5)->get();
			foreach ($tq as $q) {
				$lines[] = "Top-zoekwoord: \"{$q->query}\" — " . (int) $q->c . ' klikken, positie ' . round((float) $q->p, 1) . '.';
			}
		} else {
			$lines[] = 'SEO: nog geen Search Console-data gekoppeld.';
		}

		// PageSpeed.
		foreach (['mobile' => 'mobiel', 'desktop' => 'desktop'] as $s => $nl) {
			$row = DB::table('seo_psi_daily')->where('property_id', $id)->where('strategy', $s)->orderByDesc('date')->first();
			if ($row) {
				$lines[] = "PageSpeed {$nl}: prestatie {$row->performance_score}/100, LCP "
					. round($row->lcp_ms / 1000, 1) . "s, SEO-score {$row->seo_score}.";
			}
		}

		// Uptime (7 dagen).
		$check = DB::table('monitor_checks')->where('property_id', $id)->first();
		if ($check) {
			$rows = DB::table('monitor_check_results')->where('check_id', $check->id)
				->where('checked_at', '>=', now()->subDays(7))->get();
			if ($rows->count()) {
				$up = $rows->where('status', 'up')->count();
				$current = $rows->last()->status ?? 'unknown';
				$lines[] = 'Uptime 7d: ' . round($up / $rows->count() * 100, 2) . '% (huidige status: '
					. ($current === 'up' ? 'online' : ($current === 'down' ? 'offline' : 'onbekend')) . ').';
			}
		}

		// Kwaliteit.
		$pageIds = DB::table('monitored_pages')->where('site_id', $id)->pluck('id');
		if ($pageIds->isNotEmpty()) {
			$scan = DB::table('quality_scans')->whereIn('monitored_page_id', $pageIds)
				->where('status', 'completed')->orderByDesc('completed_at')->first();
			if ($scan && $scan->score !== null) {
				$lines[] = "Paginakwaliteit: {$scan->score}/100.";
				$f = DB::table('quality_findings')->where('quality_scan_id', $scan->id)
					->whereIn('status', ['fail', 'warn'])->limit(5)->pluck('finding')->all();
				foreach ($f as $finding) {
					$lines[] = 'Kwaliteitspunt: ' . $finding;
				}
			}
		}

		// Security.
		$sec = DB::table('security_scans')->where('property_id', $id)->where('status', 'completed')
			->orderByDesc('completed_at')->first();
		if ($sec) {
			$lines[] = 'Beveiliging: ' . ($sec->blacklisted ? 'STAAT OP BLACKLIST' : 'geen blacklist')
				. ', malware-check ' . ($sec->safe_browsing ?: 'onbekend')
				. ", {$sec->mixed_content_count} mixed-content, {$sec->broken_link_count} broken links.";
		}

		// Software.
		$updates = (int) DB::table('site_components')->where('property_id', $id)->where('has_update', true)->count();
		$vulnRows = DB::table('site_vulnerabilities')->where('property_id', $id)->limit(8)->get();
		if ($updates || $vulnRows->count()) {
			$lines[] = "Software: {$updates} updates beschikbaar, " . $vulnRows->count() . ' bekende kwetsbaarheden.';
			foreach ($vulnRows as $v) {
				$lines[] = "Kwetsbaarheid: {$v->name} {$v->version} ({$v->severity}) — {$v->title}.";
			}
		}
		$integ = (int) DB::table('site_integrity_issues')->where('property_id', $id)->count();
		if ($integ) {
			$lines[] = "File-integriteit: {$integ} afwijkend/ontbrekend kernbestand(en).";
		}

		return implode("\n", $lines);
	}
}
