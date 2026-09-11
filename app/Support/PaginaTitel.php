<?php

namespace App\Support;

/**
 * De <title> van een pagina: de eigen titel, met de merknaam erachter als dat past.
 *
 * WAAROM. Het blogsjabloon plakte er ", Blog, Beter Geregeld" achter, en de
 * generator zette de merknaam zelf al in meta_title (" — Beter Geregeld ICT").
 * Gemeten 11-09-2026 over de 572 NL-posts: 556 titels waren langer dan de ~60
 * tekens die Google in de resultaten laat zien, en 91 droegen de merknaam dubbel.
 * Op positie 4-10 klikte 1,1% van de zoekers, waar 3 tot 8% gebruikelijk is.
 *
 * De regel is daarom: eerst elke merknaam eraf die er al achter hing, dan
 * " | Beter Geregeld" erachter zolang het geheel binnen 60 tekens blijft. Past
 * dat niet, dan gaat de titel alleen; Google zet de sitenaam er zelf al boven.
 * De titel zelf wordt nooit ingekort: een afgekapte zin leest slechter dan een
 * titel die Google zelf afbreekt.
 */
final class PaginaTitel
{
	public const MERK = 'Beter Geregeld';

	public const MAX = 60;

	public static function met(string $titel): string
	{
		$titel = self::zonderMerk($titel);
		$metMerk = $titel . ' | ' . self::MERK;

		return mb_strlen($metMerk) <= self::MAX ? $metMerk : $titel;
	}

	/**
	 * Haalt een merknaam-staart weg: " — Beter Geregeld ICT", " | Beter Geregeld",
	 * ", Blog, Beter Geregeld", " - Betergeregeld". Alleen aan het eind, zodat een
	 * titel die óver Beter Geregeld gaat heel blijft.
	 */
	public static function zonderMerk(string $titel): string
	{
		$titel = trim($titel);
		$schoon = preg_replace('/\s*[—–|,:-]\s*(?:Blog\s*[—–|,-]\s*)?Beter\s?Geregeld(?:\s+ICT)?\s*$/iu', '', $titel);

		return ($schoon === null || $schoon === '') ? $titel : $schoon;
	}
}
