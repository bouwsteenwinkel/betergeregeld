<?php

namespace App\Services\QualityScan;

use App\Services\QualityScan\Data\ExtractedContent;
use App\Services\QualityScan\Data\Finding;

/**
 * Voert de deterministische (gratis, betrouwbare) checks uit op het
 * geëxtraheerde pakket. Elke check levert een Finding op (pass/warn/fail).
 * Drempelwaarden komen uit config/quality-scan.php.
 */
class DeterministicChecker
{
	/** @return Finding[] */
	public function check(ExtractedContent $c): array
	{
		$th = config('quality-scan.thresholds');
		$lang = (string) config('quality-scan.expected_lang', 'nl');
		$f = [];

		// title_aanwezig + title_lengte
		if (empty($c->title)) {
			$f[] = Finding::deterministic('title_aanwezig', 'fail', 'hoog', 'De paginatitel (<title>) ontbreekt.', 'Voeg een beschrijvende <title> toe van 30-60 tekens.');
		} else {
			$f[] = Finding::deterministic('title_aanwezig', 'pass', 'laag', 'Paginatitel aanwezig.');
			$len = mb_strlen($c->title);
			if ($len < $th['title_min'] || $len > $th['title_max']) {
				$f[] = Finding::deterministic('title_lengte', 'warn', 'laag', "De titel is {$len} tekens; ideaal is {$th['title_min']}-{$th['title_max']}.", 'Pas de lengte van de titel aan.', $c->title);
			} else {
				$f[] = Finding::deterministic('title_lengte', 'pass', 'laag', "Titellengte ({$len} tekens) is in orde.");
			}
		}

		// meta_description_aanwezig + lengte
		if (empty($c->metaDescription)) {
			$f[] = Finding::deterministic('meta_description_aanwezig', 'fail', 'middel', 'De meta description ontbreekt.', 'Voeg een meta description toe van 120-160 tekens.');
		} else {
			$f[] = Finding::deterministic('meta_description_aanwezig', 'pass', 'laag', 'Meta description aanwezig.');
			$len = mb_strlen($c->metaDescription);
			if ($len < $th['meta_desc_min'] || $len > $th['meta_desc_max']) {
				$f[] = Finding::deterministic('meta_description_lengte', 'warn', 'laag', "De meta description is {$len} tekens; ideaal is {$th['meta_desc_min']}-{$th['meta_desc_max']}.", 'Pas de lengte aan.', $c->metaDescription);
			} else {
				$f[] = Finding::deterministic('meta_description_lengte', 'pass', 'laag', "Lengte meta description ({$len} tekens) is in orde.");
			}
		}

		// h1_aantal
		$h1 = count(array_filter($c->headings, fn ($h) => $h['level'] === 1));
		if ($h1 === 0) {
			$f[] = Finding::deterministic('h1_aantal', 'fail', 'hoog', 'Er is geen H1-kop op de pagina.', 'Voeg precies één H1 toe.');
		} elseif ($h1 > 1) {
			$f[] = Finding::deterministic('h1_aantal', 'warn', 'middel', "Er zijn {$h1} H1-koppen; gebruik er precies één.");
		} else {
			$f[] = Finding::deterministic('h1_aantal', 'pass', 'laag', 'Precies één H1 aanwezig.');
		}

		// heading_hierarchie
		if ($skip = $this->headingSkip($c->headings)) {
			$f[] = Finding::deterministic('heading_hierarchie', 'warn', 'laag', "Er wordt een heading-niveau overgeslagen ({$skip}).", 'Gebruik opeenvolgende heading-niveaus.', $skip);
		} else {
			$f[] = Finding::deterministic('heading_hierarchie', 'pass', 'laag', 'Heading-hiërarchie is opeenvolgend.');
		}

		// alt-teksten (alleen als er afbeeldingen zijn)
		if (($n = count($c->images)) > 0) {
			$empty = count(array_filter($c->images, fn ($i) => trim((string) $i['alt']) === ''));
			if ($empty / $n > $th['alt_empty_ratio']) {
				$pct = (int) round($empty / $n * 100);
				$f[] = Finding::deterministic('alt_teksten_aanwezig', 'fail', 'middel', "{$pct}% van de afbeeldingen ({$empty}/{$n}) heeft geen alt-tekst.", 'Voeg beschrijvende alt-teksten toe.');
			} else {
				$f[] = Finding::deterministic('alt_teksten_aanwezig', 'pass', 'laag', 'Alt-teksten zijn grotendeels aanwezig.');
			}

			$alts = array_filter(array_map(fn ($i) => trim((string) $i['alt']), $c->images), fn ($a) => $a !== '');
			$dupes = array_filter(array_count_values($alts), fn ($cnt) => $cnt >= $th['alt_generic_count']);
			if ($dupes) {
				$dup = (string) array_key_first($dupes);
				$f[] = Finding::deterministic('alt_teksten_generiek', 'warn', 'laag', "Meerdere afbeeldingen delen dezelfde alt-tekst (\"{$dup}\").", 'Maak alt-teksten uniek en beschrijvend.', $dup);
			} else {
				$f[] = Finding::deterministic('alt_teksten_generiek', 'pass', 'laag', 'Geen overmatig herhaalde alt-teksten.');
			}
		}

		// generator_tag
		if (! empty($c->generator)) {
			$f[] = Finding::deterministic('generator_tag', 'warn', 'laag', "De generator-tag verraadt de gebruikte techniek: \"{$c->generator}\".", 'Verwijder de generator-meta-tag.', $c->generator);
		} else {
			$f[] = Finding::deterministic('generator_tag', 'pass', 'laag', 'Geen generator-tag die de techniek prijsgeeft.');
		}

		// noindex_detectie
		if ($c->metaRobots && str_contains(strtolower($c->metaRobots), 'noindex')) {
			$f[] = Finding::deterministic('noindex_detectie', 'fail', 'hoog', 'De pagina staat op noindex en wordt niet door Google geïndexeerd.', 'Verwijder noindex als de pagina vindbaar moet zijn.', $c->metaRobots);
		} else {
			$f[] = Finding::deterministic('noindex_detectie', 'pass', 'laag', 'Geen noindex; de pagina is indexeerbaar.');
		}

		// canonical_aanwezig
		if (empty($c->canonical)) {
			$f[] = Finding::deterministic('canonical_aanwezig', 'warn', 'laag', 'Er is geen canonical-URL opgegeven.', 'Voeg een canonical-link toe.');
		} else {
			$canHost = parse_url($c->canonical, PHP_URL_HOST);
			$pageHost = parse_url($c->finalUrl, PHP_URL_HOST);
			if ($canHost && $pageHost && $canHost !== $pageHost) {
				$f[] = Finding::deterministic('canonical_aanwezig', 'fail', 'hoog', "De canonical wijst naar een ander domein ({$canHost}).", 'Laat de canonical naar het eigen domein wijzen.', $c->canonical);
			} else {
				$f[] = Finding::deterministic('canonical_aanwezig', 'pass', 'laag', 'Canonical-URL aanwezig en op het eigen domein.');
			}
		}

		// lang_attribuut
		if (empty($c->lang)) {
			$f[] = Finding::deterministic('lang_attribuut', 'warn', 'laag', 'Het lang-attribuut op <html> ontbreekt.', "Zet lang=\"{$lang}\".");
		} elseif (! str_starts_with(strtolower($c->lang), strtolower($lang))) {
			$f[] = Finding::deterministic('lang_attribuut', 'warn', 'laag', "Het lang-attribuut is \"{$c->lang}\" maar er werd \"{$lang}\" verwacht.", 'Controleer de ingestelde paginataal.', $c->lang);
		} else {
			$f[] = Finding::deterministic('lang_attribuut', 'pass', 'laag', 'Lang-attribuut is correct ingesteld.');
		}

		// mixed_content (alleen op https-pagina's)
		if ($c->isHttps) {
			$resources = array_merge(array_column($c->images, 'src'), array_column($c->links, 'href'));
			$insecure = array_values(array_filter($resources, fn ($u) => str_starts_with(strtolower((string) $u), 'http://')));
			if ($insecure) {
				$cnt = count($insecure);
				$f[] = Finding::deterministic('mixed_content', 'warn', 'middel', "{$cnt} resource(s) worden via onveilig http:// geladen op een https-pagina.", 'Laad alle resources via https.', $insecure[0]);
			} else {
				$f[] = Finding::deterministic('mixed_content', 'pass', 'laag', 'Geen mixed content gevonden.');
			}
		}

		return $f;
	}

	/** Geeft de eerste overgeslagen heading-overgang terug (bv. "H2 → H4"), of null. */
	private function headingSkip(array $headings): ?string
	{
		$prev = 0;
		foreach ($headings as $h) {
			$lvl = (int) $h['level'];
			if ($prev > 0 && $lvl > $prev + 1) {
				return "H{$prev} → H{$lvl}";
			}
			$prev = $lvl;
		}

		return null;
	}
}
