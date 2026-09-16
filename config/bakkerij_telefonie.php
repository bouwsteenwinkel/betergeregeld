<?php

/**
 * Inhoud van /ai-telefonie-bakkerij op jouw-bakkerij-website.nl (15-09-2026).
 *
 * Alles hier is getoetst aan wat de telefonie aantoonbaar doet (bouwsteenwinkel_v3/telefonie:
 * beleid/*, README): opnemen in NL (EN op verzoek), antwoorden uit kennisbank + dagberichten,
 * terugbelverzoek met naam en nummer (herhaald ter controle), doorverbinden binnen
 * openingstijden, samenvatting per mail, log in het klantportaal. GEEN opnames, GEEN
 * agenda-afspraken, GEEN betalingen. Bestellingen = vastgelegd verzoek, geen kassatransactie.
 *
 * demo_nummer: leeg = de "Bel de demo"-knop wordt niet getoond. Dennis wil een eigen
 * bakkerijdemo (besluit 15-09); zet het nummer hier zodra het in de 3CX en op de
 * telefoniemachine staat (zelfde recept als de garagedemo 5160: DID op trunk Steenbouw,
 * KANALEN + beleid/bakkerij/). Tot die tijd blijft de knop weg — geen nepnummer.
 *
 * Sinds 16-09-2026 staat alleen het bakkerij-eigen deel hier; prijs, eigen nummer, privacy,
 * stappen, algemene FAQ en de rekenhulpcijfers komen uit config/telefonie_basis.php
 * (samengevoegd door App\Support\TelefonieConfig). Prijsonderbouwing:
 * docs/bakkerij/CHANGES-SEO-CONTENT.md.
 */

return [
    'demo_nummer'      => '088 254 5170',          // lijn staat sinds 15-09 op de telefoniemachine (KANALEN 31882545170)
    'demo_naam'        => 'Bakkerij Kruimel',      // naam van de fictieve demo-bakkerij
    'demo_noot'        => 'Het demonummer is Bakkerij Kruimel: een bakkerij die niet bestaat, zodat je vrij kunt vragen wat je wilt. Je sluit niets af door te bellen.',
    'demo_titel'       => 'Bel Bakkerij Kruimel op 088 254 5170',
    'demo_lead'        => 'Bakkerij Kruimel bestaat niet, het assortiment wel. Bestel iets, of bel met een van deze bestelnummers en vraag of je bestelling klaar is. Er wordt niets gebakken en niets afgerekend.',

    // De vaste bestellingen in de demo (bouwsteenwinkel_v3/scripts/_aitest-bakkerij-tabellen.php).
    // Alleen zichtbaar als demo_nummer gevuld is. Dagen zijn afstanden vanaf vandaag.
    'demo_kaartjes' => [
        ['kop' => 'Bestelnummer K-1042 · De Wit', 'hoor' => 'Twee appeltaarten staan klaar; vandaag ophalen, al betaald.'],
        ['kop' => 'Bestelnummer K-1057 · Jansen', 'hoor' => 'Slagroomtaart met "Sam 7", overmorgen om half twaalf ophalen.'],
        ['kop' => 'Bestelnummer K-1063 · El Amrani', 'hoor' => 'Veertig belegde broodjes, morgen bezorgd bij het notariskantoor, op rekening.'],
        ['kop' => 'Bestelnummer K-1071 · Bakker', 'hoor' => 'Chocoladetaart over vijf dagen; de vraag over een foto op de taart staat nog open.'],
    ],
    'demo_probeer' => [
        'Bestel zelf een taart voor zaterdag: hij leest de bestelling terug en geeft een bestelnummer.',
        'Vraag of die taart morgen al kan (twee dagen vooruit: hij zegt eerlijk wat de vroegste dag is).',
        'Vraag of er noten in de amandelstaaf zitten (allergenen gaan altijd naar een medewerker).',
        'Bel buiten openingstijden: hij neemt de bestelling gewoon aan.',
    ],
    'kantoor_nummer'   => '088-2545101',
    'infoblad'         => 'bakkerij',         // config/telefonie_infobladen.php, knop naast het demonummer

    // CTA op de homepage (channels/partials/telefonie-home-cta): een telefoonscherm waarop
    // de assistent opneemt en dit gesprek zich uittikt. 'b' = beller, 'a' = assistent.
    'home_cta' => [
        'kicker'      => 'Nieuw: AI-telefonie voor bakkerijen',
        'kop'         => 'De telefoon gaat. Jij hebt deeg aan je handen.',
        'lead'        => 'Een digitale assistent die opneemt met de naam van je bakkerij, openingstijden en assortimentsvragen beantwoordt en een bestelling aanneemt: product opzoeken, besteltermijn bewaken, terugvoorlezen, bestelnummer geven. Bij een allergievraag verwijst hij naar een medewerker. Bel de demo en bestel zelf een taart.',
        'bel_label'   => 'Bel de demo',
        'meer_label'  => 'Zo werkt het voor bakkerijen',
        'noot'        => 'Bakkerij Kruimel bestaat niet; er wordt niets gebakken en niets afgerekend. Vanaf € 89 per maand.',
        'toestel_naam'=> 'Bakkerij Kruimel',
        'toestel_sub' => 'Inkomend gesprek · de assistent neemt op',
        'tijd'        => '07:48',
        // Luisterstrook onder de band: de ingesproken demo op telefonie.betergeregeld.com.
        'luister' => [
            'url'          => 'https://telefonie.betergeregeld.com/demo/bakkerij',
            'titel'        => 'Liever eerst luisteren?',
            'sub'          => 'Vijf ingesproken gesprekken met Bakkerij Kruimel, naast het portaal dat live meebeweegt',
            'duur'         => '± 7 min',
            'hoofdstukken' => ['Verjaardagstaart voor zaterdag', 'Veertig broodjes in de avond', 'Bestelling klaar en een allergievraag', 'Zondagavond: brood en bruidstaart', 'Taart wijzigen en een klacht'],
        ],
        'gesprek' => [
            ['b', 'Kan ik een slagroomtaart bestellen voor zaterdag?'],
            ['a', 'Dat kan. Voor hoeveel personen, en wilt u er een tekst op?'],
            ['b', 'Tien personen, met "Sam 7" erop.'],
            ['a', 'Genoteerd: slagroomtaart voor tien met "Sam 7", zaterdag afhalen. Mag ik uw naam en nummer?'],
            ['b', 'Jansen, 06 1234 5678.'],
            ['a', 'Dank u. Uw bestelnummer is K, twee nul nul vier. De bakkerij bevestigt het nog.'],
        ],
    ],

    'woorden' => [
        'bedrijf'   => 'je bakkerij',
        'bedrijven' => 'bakkerijen',
        'klant'     => 'klant',
        'klanten'   => 'klanten',
        'gegevens'  => 'openingstijden, vestigingen, assortiment, bestelregels en bezorggebied',
    ],
    'prijs_combi_facet' => 'webshop',
    'prijs_combi_label' => 'webshop',

    // Rekenhulp: startstand en teksten voor een bakkerij; de cijfers (prijs, bundel) komen
    // uit telefonie_basis. Een gemiste bestelling van € 18 is het bakkerij-equivalent van
    // een gemiste opdracht.
    'rekenhulp' => [
        'standaard' => [
            'per_dag'         => 12,
            'dagen'           => 6,
            'minuten'         => 2,
            'uurloon'         => 22,
            'gemist_per_week' => 8,
            'bestelling_pct'  => 25,
            'bestelwaarde'    => 18,
            'afhandel_pct'    => 70,
        ],
        'teksten' => [
            'lead'         => 'Een rekenvoorbeeld, geen belofte. Zet de schuiven op jouw bakkerij en zie wat de telefoon je nu per maand kost aan tijd en onderbrekingen, en welke omzet er in gemiste telefoontjes zit.',
            'deel_label'   => 'Deel daarvan dat een bestelling was',
            'waarde_label' => 'Gemiddelde bestelling aan de telefoon',
            'tijd_tegel'   => 'aan loonkosten die vrijkomen voor de winkel en de bakkerij',
        ],
    ],

    'hero' => [
        'eyebrow' => 'AI-telefonie voor bakkerijen',
        'title'   => 'AI-telefonie voor je bakkerij: de telefoon wordt opgenomen, ook als jij geen tijd hebt',
        'sub'     => 'Een telefonische assistent die opneemt met de naam van je bakkerij, openingstijden en assortimentsvragen beantwoordt, een bestel- of terugbelverzoek vastlegt en doorverbindt als het moet. Tijdens de ochtenddrukte, tijdens het bakken en buiten openingstijden.',
        'alt'     => 'De telefonische assistent van een bakkerij',
        'usps'    => [
            'Neemt altijd op, ook buiten openingstijden',
            'Antwoordt uit jouw eigen gegevens, niet uit het internet',
            'Weet hij het niet, dan noteert hij naam en nummer en bel jij terug',
            'Na elk gesprek een samenvatting per mail',
        ],
    ],

    'wanneer_titel' => 'Wanneer de telefoon het meeste stoort',
    'wanneer' => [
        ['t' => 'Zaterdagochtend',              'b' => 'Rij tot de deur, twee man aan de balie, en de telefoon gaat. Opnemen betekent een klant laten wachten; niet opnemen betekent een klant kwijt.'],
        ['t' => 'Tijdens het bakken',            'b' => 'Met deeg aan je handen of een oven die open moet, is elk telefoontje er één te veel. De vraag is meestal simpel; het moment nooit.'],
        ['t' => 'Buiten openingstijden',         'b' => 'Iemand belt zondagavond voor een taart voor dinsdag. Nu krijgt die een voicemail die niemand afluistert.'],
        ['t' => 'Meerdere bellers tegelijk',     'b' => 'Rond feestdagen bellen er drie mensen in vijf minuten. De assistent handelt ze alle drie af, in de goede volgorde.'],
    ],

    'gesprekken_titel' => 'Zo klinkt dat, in gewoon Nederlands',
    'gesprekken_lead'  => 'De antwoorden hieronder komen uit de gegevens die de bakkerij zelf heeft aangeleverd. Zo praat de assistent ook over jouw bakkerij: alleen wat jij hebt doorgegeven.',
    'gesprekken' => [
        ['vraag' => 'Zijn jullie vandaag open?',                                        'antwoord' => 'Ja, vandaag zijn we open tot 17.00 uur. Morgen, zondag, zijn we gesloten.'],
        ['vraag' => 'Kan ik nog een verjaardagstaart bestellen voor zaterdag?',        'antwoord' => 'Dat kan. Voor hoeveel personen, en wilt u er een tekst op? Dan noteer ik: een slagroomtaart voor tien personen met "Sam 7", zaterdag om half twaalf ophalen, op naam van Jansen. Uw bestelnummer is K, twee nul nul vier. De bakkerij bevestigt het nog; u betaalt bij het afhalen.'],
        ['vraag' => 'Tot hoe laat kan ik mijn bestelling ophalen?',                    'antwoord' => 'Bestellingen kunt u ophalen tot sluitingstijd, vandaag 17.00 uur. Lukt dat niet, dan kan ik dat doorgeven.'],
        ['vraag' => 'Bezorgen jullie?',                                                'antwoord' => 'We bezorgen in Hilversum en de dorpen eromheen, op dinsdag en vrijdag. Wilt u dat ik een bezorgverzoek noteer?'],
        ['vraag' => 'Hebben jullie glutenvrij brood?',                                  'antwoord' => 'We hebben een glutenvrij wit en een glutenvrij meergranen, op bestelling en meestal op vrijdag. Voor vragen over allergenen verbind ik u het liefst door met een medewerker, die weet precies wat er wel en niet in zit.'],
        ['vraag' => 'Ik wil 40 belegde broodjes bestellen voor ons bedrijf.',          'antwoord' => 'Dat kan, een dag vooruit. Wilt u ze laten bezorgen of komt u ze halen? Dan noteer ik veertig belegde broodjes, morgen bezorgen om kwart voor twaalf op het Stationsplein, op naam van het notariskantoor. U krijgt een bestelnummer en de bakkerij bevestigt het.'],
        ['vraag' => 'Kan ik een taart zonder noten bestellen?',                        'antwoord' => 'Voor allergieën wil ik niets verkeerd zeggen. Ik leg uw vraag vast en zorg dat een medewerker u terugbelt, of ik verbind u nu door als de winkel open is.'],
    ],

    'kan_titel' => 'Wat de assistent afhandelt',
    'kan' => [
        ['t' => 'Openingstijden en vestigingen',       'b' => 'Per dag, per winkel, met feestdagen en vakanties zoals jij ze doorgeeft.'],
        ['t' => 'Vragen over assortiment en bestellen', 'b' => 'Wat je hebt, wat op bestelling is, hoe ver vooruit, afhalen of bezorgen. Uit jouw eigen gegevens.'],
        ['t' => 'Bestellingen aannemen',                'b' => 'Een taart, een zakelijke bestelling, veertig broodjes: hij zoekt het product op, let op de besteltermijn, leest de bestelling terug en geeft een bestelnummer. Jij bevestigt; geen betaling aan de telefoon.'],
        ['t' => '"Is mijn bestelling klaar?"',           'b' => 'Op bestelnummer en achternaam zegt hij wat erop staat, voor welke dag, en of er al betaald is.'],
        ['t' => 'Dagberichten',                         'b' => 'Vandaag geen krentenbollen, morgen dicht: één bericht van jou en de assistent zegt het tegen iedere beller.'],
    ],

    'niet_titel' => 'Wat we bewust níét laten doen',
    'niet' => [
        'Uitspraken over allergenen die niet uit jouw actuele productinformatie komen. Bij twijfel verwijst de assistent naar een medewerker.',
        'Een bestelling wijzigen of annuleren die al is aangenomen. Een bestelling die hij aanneemt bevestig jij; wijzigen en annuleren gaan via jou.',
    ],

    'faq_titel' => 'Wat bakkers ons vragen over AI-telefonie',
    'faq' => [
        ['q' => 'Kan de assistent de telefoon opnemen voor mijn bakkerij?', 'a' => 'Ja. Hij neemt op met de naam van je bakkerij en handelt de veelvoorkomende vragen af. Wat hij niet weet, legt hij vast als terugbelverzoek.'],
        ['q' => 'Kan hij bestellingen aannemen?',                         'a' => 'Ja. Hij zoekt het product op in jouw assortiment, let op de besteltermijn (een taart twee dagen vooruit, brood vandaag nog), leest de bestelling terug en geeft de beller een bestelnummer. De bestelling staat direct in je portaal en in je mail; jij bevestigt hem. Er wordt niets afgerekend aan de telefoon. Wil je online laten bestellen en betalen, dan is de webshop daarvoor.'],
        ['q' => 'Kan hij openingstijden en productvragen beantwoorden?',   'a' => 'Ja, uit jouw eigen gegevens: openingstijden per vestiging, assortiment, bestelregels, bezorggebied. Verandert er iets, dan geef je dat door of zet je een dagbericht.'],
        ['q' => 'Kan hij vragen over allergenen beantwoorden?',            'a' => 'Alleen als jij daarvoor betrouwbare, actuele productinformatie hebt aangeleverd en dat wilt. Bij twijfel verwijst hij altijd naar een medewerker. Voedselveiligheid laten we niet aan een assistent over.'],
    ],

    'verder_titel' => 'Telefoon, webshop en administratie op elkaar aansluiten',
    'verder' => [
        ['facet' => 'webshop',        't' => 'Webshop voor bakkerijen',              'b' => 'Laat klanten die nu bellen om te bestellen, dat online doen met een afhaaltijd en vooraf betalen.'],
        ['facet' => 'automatisering', 't' => 'Bestellingen automatisch verwerken',   'b' => 'Een verzoek dat de assistent vastlegt, komt op dezelfde productielijst als een webshopbestelling.'],
        ['facet' => 'klantenportaal', 't' => 'Klantenportaal voor zakelijke klanten', 'b' => 'Horeca en kantoren die nu elke avond bellen, bestellen zelf.'],
    ],

    'cta_titel' => 'Hoor het zelf, of vraag een demo aan',
    'cta_tekst' => 'Bel het demonummer en stel de vragen die jouw klanten stellen. Of plan een gesprek van een half uur; dan richten we een proefversie in met jouw openingstijden en assortiment.',
];
