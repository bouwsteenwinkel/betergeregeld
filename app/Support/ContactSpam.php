<?php

namespace App\Support;

/**
 * Spamherkenning voor het contactformulier van betergeregeld.com.
 *
 * WAAROM. Het formulier had geen enkele drempel. Gemeten 12-09-2026: van de 648
 * inzendingen waren er ruim 100 spam, vrijwel allemaal casino- en SEO-bots, in vlagen
 * vanaf hetzelfde IP (30 van één adres). Zolang niemand die meldingen las viel het niet
 * op; sinds er een mail uitgaat naar dennis@ wel.
 *
 * OPZET. Geen harde blokkade op één kenmerk maar een score: bots stapelen signalen
 * (Cyrillisch + BBCode + twee links + een naam als "Roberticoma"), een echte aanvraag
 * hooguit één (iemand die een linkje meestuurt). Vanaf 3 punten gaat het bericht als
 * 'spam' de database in en gaat er GEEN mail uit. Het bericht wordt wel bewaard: dat is
 * het vangnet, en het maakt een verkeerde beoordeling terug te vinden.
 *
 * Het lokvakje en de tijdcontrole staan hier NIET in: die horen bij het formulier zelf
 * (ContactController), want ze zeggen niets over de inhoud.
 */
final class ContactSpam
{
    /** Vanaf deze score bewaren we het bericht als spam en mailen we niet. */
    public const DREMPEL = 3;

    /**
     * @param array<string,mixed> $data velden van het formulier
     * @return array{score:int,redenen:array<int,string>}
     */
    public static function beoordeel(array $data): array
    {
        $naam    = trim((string) ($data['name'] ?? ''));
        $email   = trim((string) ($data['email'] ?? ''));
        $bericht = (string) ($data['message'] ?? '');
        $subject = (string) ($data['subject'] ?? '');
        $website = (string) ($data['website'] ?? '');
        $alles   = $naam . ' ' . $email . ' ' . $subject . ' ' . $bericht;

        $score = 0;
        $redenen = [];
        $tel = function (int $punten, string $reden) use (&$score, &$redenen) {
            $score += $punten;
            $redenen[] = $reden;
        };

        // Cyrillisch of Grieks in een Nederlands/Engels formulier: bijna altijd een bot.
        if (preg_match('/\p{Cyrillic}{3,}|\p{Greek}{3,}/u', $alles)) {
            $tel(3, 'cyrillisch of grieks schrift');
        }

        // Forum-opmaak die op een website niets betekent.
        if (preg_match('/\[(url|link|b|img)[=\]]/i', $alles)) {
            $tel(3, 'BBCode');
        }

        // Links in het bericht. Eén mag: mensen sturen hun eigen site mee.
        $links = preg_match_all('~https?://~i', $bericht);
        if ($links >= 3) {
            $tel(3, "{$links} links in het bericht");
        } elseif ($links === 2) {
            $tel(2, 'twee links in het bericht');
        }

        // Woorden die in onze aanvragen niet voorkomen.
        $woorden = ['casino', 'kazino', 'bonus', 'gambling', 'viagra', 'cialis', 'porn', 'sex dating',
                    'crypto', 'bitcoin', 'forex', 'binary option', 'loan offer', 'backlink', 'link building',
                    'guest post', 'seo services', 'boost your ranking', 'increase your traffic', 'telegram',
                    // 15-09-2026: gokreclame, 419-mails en SEO-pitches die hier nog tussendoor kwamen.
                    'mega slots', 'slot of this year', 'spin once', 'prize expires', '500k', 'play t',
                    'finds you well', 'head of equity', 'research head', 'investment opportunity',
                    'indexing status', 'seo and aio', 'more visibility', 'test submission'];
        $woordTreffers = 0;
        foreach ($woorden as $woord) {
            if (stripos($alles, $woord) !== false) {
                $tel(2, "woord \"{$woord}\"");
                if (++$woordTreffers === 2) {
                    break;
                }
            }
        }

        // Bot-namen: hoofdletter middenin ("JosephwaK", "BrianSpice") of een aan elkaar
        // geplakte voornaam+woord zonder spatie terwijl er wél een link in het bericht staat
        // ("Roberticoma", "Dennislax"). Dat laatste alleen samen met een link, want een
        // eenwoordsnaam op zich is niet verdacht.
        if ($naam !== '' && !str_contains($naam, ' ')) {
            if (preg_match('/^[A-Z][a-z]+[A-Z]/', $naam)) {
                $tel(2, 'naam ziet eruit als bot-naam');
            } elseif ($links >= 1 && mb_strlen($naam) >= 8) {
                $tel(2, 'eenwoordsnaam met een link in het bericht');
            }
        }

        // Herhaling binnen 24 uur: bots komen in vlagen terug (8x "Roberticoma" op één dag).
        // BEWUST hoogstens 2 punten, en dus nooit genoeg om alléén op herhaling als spam te
        // gelden: een kantoor met één IP, een klant die zich bedenkt of twee collega's die
        // allebei schrijven zijn geen spam. Dat ging in de eerste opzet mis — toen telde ik
        // over de hele geschiedenis en werd 559 van de 648 berichten onterecht aangemerkt.
        $eerder = (int) ($data['eerdere_inzendingen_24u'] ?? 0);
        if ($eerder >= 3) {
            $tel(2, "{$eerder} eerdere inzendingen in 24 uur");
        }

        // Onderwerpregel die niets met de aanvraag te maken heeft, is op zichzelf zwak;
        // daarom maar één punt.
        if (preg_match('/\b(interesting news|good news|hi there|dear sir|hello dear)\b/i', $subject . ' ' . $bericht)) {
            $tel(1, 'standaard spam-aanhef');
        }

        // Wegwerp- en bulkdomeinen die we in de echte aanvragen nooit zien.
        if (preg_match('/@(mail\.ru|bk\.ru|list\.ru|inbox\.ru|rambler\.ru|yandex\.[a-z]+|.*\.top|.*\.xyz)$/i', $email)) {
            $tel(2, 'verdacht e-maildomein');
        }

        // Een bericht zonder enige Nederlandse of Engelse stopwoorden én met een link:
        // meestal geplakte reclame.
        if ($links >= 1 && !preg_match('/\b(ik|we|wij|jullie|graag|vraag|kunnen|hebben|would|we|our|need|looking|please)\b/i', $bericht)) {
            $tel(1, 'geen gewone zinnen, wel een link');
        }

        // Website-veld met een ander domein dan waar het bericht over gaat is normaal,
        // maar een link in het NAAM-veld nooit.
        if (preg_match('~https?://|www\.~i', $naam)) {
            $tel(3, 'link in het naamveld');
        }
        unset($website);

        // ── Aanvullingen 15-09-2026 ────────────────────────────────────────────────
        // Van de 36 berichten die tussen 01-08 en 14-09 als 'new' doorkwamen was er niet
        // één echt. Vier families die de regels hierboven misten:

        // 1. Wartaal: een bericht of naam die één aaneengesloten reeks tekens is
        //    ("ydpjsdneyqytokiwjnvurodwsltntw", "NAYUYUTY465753NERT"). Een mens typt een
        //    spatie.
        $berichtStrak = trim($bericht);
        if ($berichtStrak !== '' && !str_contains($berichtStrak, ' ') && mb_strlen($berichtStrak) >= 10) {
            $tel(3, 'bericht is één reeks tekens zonder spaties');
        }
        if ($naam !== '' && !str_contains($naam, ' ') && preg_match('/[a-z][A-Z].*[a-z][A-Z]|\d{4,}/', $naam)) {
            $tel(2, 'naam is wartaal (wisselend hoofdlettergebruik of cijferreeks)');
        }
        //    Ook wartaal mét spaties ("Egjnjmfnefjwdifj fkmdkdwdwkdwjj"): twee keer zeven of
        //    meer medeklinkers achter elkaar komt in geen taal van onze klanten voor
        //    ("angstschreeuw" haalt er één, vandaar de eis van twee).
        if (preg_match_all('/[b-df-hj-np-tv-z]{7,}/i', $naam . ' ' . $berichtStrak) >= 2) {
            $tel(3, 'woorden zonder klinkers');
        }

        // 2. "Wat is uw prijs" in twaalf talen, telkens één zin, telkens dezelfde afzender
        //    ("Hola, quería saber tu precio", "Szia, meg akartam tudni az árát"). Kort
        //    bericht + het woord voor prijs in een taal die onze klanten niet schrijven.
        if (mb_strlen($berichtStrak) <= 120
            && preg_match('/\b(precio|prezzo|prys|cijenu|çmimin|harga|pretium|kainą|árát|prezioa|intengo|cena|preț|cenu|hinta|fiyat|pris)\b/iu', $berichtStrak)) {
            $tel(3, 'prijsvraag in een vreemde taal');
        }

        // 3. Engelse verkoopmail en gokreclame. Eén zin is 2 punten; zulke berichten
        //    stapelen er meestal twee ("I wanted to reach out ... ever considered").
        $verkoop = ['i wanted to reach out', 'wanted to reach out', 'i just visited', 'ever considered',
                    'hope you\'re doing well', 'hope you’re doing well', 'regional distributor', 'automated system that',
                    'let you know about our new', 'try now', 'jackpot', 'spins', 'this slot', 'slot wins',
                    'collecting customer reviews', 'quick growth audit', 'competitive edge for', 'enhance visibility',
                    'automated system', "we've spent the last", 'thoughtful article', 'feel free to visit',
                    'noted speaking with', 'one of our regional'];
        $geraakt = 0;
        foreach ($verkoop as $zin) {
            if (stripos($alles, $zin) !== false) {
                $geraakt++;
                $tel(2, "verkooptekst \"{$zin}\"");
                if ($geraakt === 2) {
                    break;
                }
            }
        }

        // 4. Afzender doet zich voor als ons eigen domein. Wij mailen onszelf niet via
        //    het contactformulier; een prospect met @betergeregeld.com bestaat niet.
        if (preg_match('/@betergeregeld\.(com|nl|be)$/i', $email)) {
            $tel(3, 'afzender gebruikt ons eigen domein');
        }

        // 5. Dezelfde naam die vaker terugkomt over een maand (12× "RobertDeeni" in zes weken).
        //    Zwak op zichzelf, sterk in combinatie met de bot-naamregel hierboven.
        $herhaaldeNaam = (int) ($data['zelfde_naam_30d'] ?? 0);
        if ($naam !== '' && $herhaaldeNaam >= 3) {
            $tel(1, "naam kwam {$herhaaldeNaam}× eerder voor in 30 dagen");
        }

        return ['score' => $score, 'redenen' => $redenen];
    }

    public static function isSpam(array $data): bool
    {
        return self::beoordeel($data)['score'] >= self::DREMPEL;
    }
}
