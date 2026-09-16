<?php

/**
 * Wat op ELKE AI-telefonie-landingspagina hetzelfde is (16-09-2026). Wordt door
 * App\Support\TelefonieConfig samengevoegd met config/{key}_telefonie.php van het kanaal;
 * het kanaal wint, en lijsten van het kanaal komen vóór de *_basis-lijsten hieronder.
 *
 * Alles hier is getoetst aan wat de telefonie aantoonbaar doet (bouwsteenwinkel_v3/telefonie,
 * README + beleid/*): opnemen in NL (EN op verzoek), antwoorden uit kennisbank + dagberichten,
 * terugbelverzoek met naam en nummer (herhaald ter controle), doorverbinden binnen
 * openingstijden, samenvatting per mail, log in het klantportaal. GEEN opnames, GEEN
 * agenda-afspraken, GEEN betalingen. Wat de assistent per vak wél en niet mag (allergenen,
 * medisch advies, juridisch advies, prijsopgaven) staat in de kanaalconfig, niet hier.
 *
 * Plaatshouders: :bedrijf, :bedrijven, :klant, :klanten, :gegevens (zie TelefonieConfig).
 *
 * prijs: besluit 16-09-2026 (extra gesprek € 0,30 -> € 0,45 op basis van de gemeten
 * kostprijs, zie docs/bakkerij/CHANGES-SEO-CONTENT.md). Eén prijs voor alle kanalen;
 * de rekenhulp leest dezelfde cijfers uit 'rekenhulp'.
 */

return [
    'prijs' => [
        'vanaf'        => '€ 89',
        'periode'      => 'per maand',
        'inbegrepen'   => 'tot 200 gesprekken per maand, inrichting van je kennisbank, dagberichten en de samenvattingen per mail',
        'extra'        => '€ 0,45 per gesprek daarboven',
        'eenmalig'     => '€ 295 voor het inrichten en samen testen',
        'opzeg'        => 'maandelijks opzegbaar, geen contract',
        'toelichting'  => 'Een gemiddeld gesprek van anderhalve minuut kost ons zelf ongeveer twintig cent aan spraak en rekentijd; een lang gesprek het dubbele. Wat je betaalt is vooral de inrichting, het nummer en het bijhouden van jouw gegevens. Je kennisbank mag zo groot zijn als je wilt: de assistent zoekt erin op, hij leert het niet uit zijn hoofd, dus dat kost niets extra.',
    ],

    // Rekenhulp: cijfers voor alle kanalen; 'standaard' en 'teksten' overschrijft elk kanaal
    // met wat bij het vak past (een gemiste bestelling is iets anders dan een gemiste klus).
    'rekenhulp' => [
        'maandprijs'            => 89,
        'inbegrepen_gesprekken' => 200,
        'extra_per_gesprek'     => 0.45,
        'standaard' => [
            'per_dag'         => 12,
            'dagen'           => 5,
            'minuten'         => 2,
            'uurloon'         => 25,
            'oppakken'        => 3,
            'gemist_per_week' => 8,
            'bestelling_pct'  => 25,
            'bestelwaarde'    => 50,
            'afhandel_pct'    => 70,
        ],
        'teksten' => [
            'lead'         => 'Een rekenvoorbeeld, geen belofte. Zet de schuiven op :bedrijf en zie wat de telefoon je nu per maand kost aan tijd en onderbrekingen, en welke omzet er in gemiste telefoontjes zit.',
            'deel_label'   => 'Deel daarvan dat een opdracht was',
            'waarde_label' => 'Gemiddelde waarde van zo\'n opdracht',
            'uren_tegel'   => 'per maand niet meer aan de telefoon of erdoor onderbroken',
            'tijd_tegel'   => 'aan loonkosten die vrijkomen voor het werk zelf',
            'omzet_tegel'  => 'omzet per maand in telefoontjes die nu niemand opneemt',
        ],
    ],

    'gesprekken_lead' => 'De antwoorden hieronder komen uit de gegevens die het bedrijf zelf heeft aangeleverd. Zo praat de assistent ook over :bedrijf: alleen wat jij hebt doorgegeven.',

    'kan_titel' => 'Wat de assistent afhandelt',
    // Elk kanaal levert zelf een 'Dagberichten'-kaart in zijn eigen woorden; hier alleen wat
    // voor elk vak letterlijk hetzelfde is.
    'kan_basis' => [
        ['t' => 'Terugbelverzoek',                      'b' => 'Naam en telefoonnummer, herhaald ter controle, met waar het over ging.'],
        ['t' => 'Doorverbinden',                        'b' => 'Tijdens openingstijden naar jou of een collega, als de beller of de vraag daarom vraagt.'],
        ['t' => 'Samenvatting',                         'b' => 'Na elk gesprek een korte samenvatting per mail, en alles terug te lezen in je eigen portaal.'],
        ['t' => 'Nederlands, en Engels als je dat wilt', 'b' => 'Standaard Nederlands. Engels kan erbij worden ingericht voor bellers die geen Nederlands spreken.'],
    ],

    'niet_titel' => 'Wat we bewust níét laten doen',
    'niet_basis' => [
        'Gesprekken opnemen. Er wordt een samenvatting gemaakt, geen geluidsopname.',
        'Afspraken in een agenda zetten. Dat kan wel via je website; aan de telefoon houden we het bij een verzoek dat jij bevestigt.',
        'Betalingen aannemen of iets afrekenen.',
        'Iets verzinnen. Weet de assistent het niet, dan zegt hij dat en legt hij een terugbelverzoek vast.',
    ],

    'nummer_titel' => 'Je houdt je eigen telefoonnummer',
    'nummer_tekst' => 'De assistent krijgt een eigen nummer. Jouw bestaande nummer schakel je door: alleen bij geen gehoor of bezet (ons advies), altijd, of alleen buiten openingstijden. Dat stel je in bij je telefoonaanbieder; wij helpen daarbij. Doorverbinden doet hij naar je mobiel of een tweede lijn, zodat het gesprek niet rondloopt. Wil je later stoppen, dan zet je de doorschakeling uit en is alles weer zoals het was.',

    'privacy_titel' => 'Privacy en AVG',
    'privacy_tekst' => 'De assistent bewaart wat nodig is om een verzoek af te handelen: naam, telefoonnummer en de inhoud van het verzoek. Geen geluidsopnames. De samenvattingen staan in je eigen portaal en worden niet gebruikt om iets te trainen. Wil je dat bellers aan het begin horen dat ze met een assistent spreken, dan richten we dat zo in.',

    'stappen_titel' => 'Zo richten we het in',
    'stappen' => [
        ['t' => 'Gesprek van een half uur',   'b' => 'Wat bellen mensen nu, wat wil je dat de assistent zegt, en wanneer moet hij doorverbinden?'],
        ['t' => 'Jouw gegevens erin',          'b' => 'Je levert :gegevens aan; wij zetten het om in de kennisbank.'],
        ['t' => 'Samen testbellen',            'b' => 'Je belt zelf en hoort hoe hij reageert. Wat niet klopt passen we aan, tot jij tevreden bent.'],
        ['t' => 'Doorschakelen en meekijken',  'b' => 'Je zet de doorschakeling aan. De eerste weken kijken we mee naar de samenvattingen en scherpen we bij.'],
    ],

    'faq_basis' => [
        ['q' => 'Wat is AI-telefonie?',                        'a' => 'Een computerprogramma dat de telefoon opneemt, luistert, in gewoon Nederlands antwoordt en vastlegt wat de beller wil. Het stelt zich voor als de digitale assistent van :bedrijf, dus de beller weet met wie hij praat.'],
        ['q' => 'Wat gebeurt er als hij het antwoord niet weet?', 'a' => 'Dan zegt hij dat eerlijk, noteert naam en telefoonnummer en de vraag, en jij belt terug. Tijdens openingstijden kan hij ook doorverbinden.'],
        ['q' => 'Kan ik mijn huidige telefoonnummer behouden?',   'a' => 'Ja. Je schakelt je bestaande nummer door naar de assistent: bij geen gehoor, altijd, of buiten openingstijden. Stoppen is de doorschakeling uitzetten.'],
        ['q' => 'Werkt hij buiten openingstijden?',               'a' => 'Ja, 24 uur per dag. Buiten openingstijden verbindt hij niet door maar legt hij verzoeken vast; die staan de volgende ochtend in je mail.'],
        ['q' => 'Krijg ik een samenvatting van gesprekken?',      'a' => 'Na elk gesprek een korte samenvatting per mail, en alle gesprekken terug te lezen in je eigen portaal. Geen geluidsopnames.'],
        ['q' => 'Wat kost AI-telefonie?',                         'a' => 'Vanaf € 89 per maand tot 200 gesprekken, daarboven € 0,45 per gesprek, en eenmalig € 295 voor het inrichten en samen testen. Maandelijks opzegbaar.'],
        ['q' => 'Hoe snel kan het worden ingericht?',             'a' => 'Meestal binnen een week na het eerste gesprek, afhankelijk van hoe snel jouw gegevens compleet zijn. Het testbellen doen we samen.'],
        ['q' => 'Moet ik een nieuwe website hebben?',             'a' => 'Nee. AI-telefonie werkt los van je website. Je kunt hem ook als enige dienst afnemen.'],
    ],

    // Twee vragen die ook op /veelgestelde-vragen van het kanaal komen.
    'faq_site' => [
        ['q' => 'Kan een AI-assistent de telefoon opnemen voor :bedrijven?', 'a' => 'Ja. Een telefonische assistent die opneemt met de naam van :bedrijf, vragen beantwoordt uit jouw eigen gegevens, terugbelverzoeken vastlegt en doorverbindt als het moet. Ook buiten openingstijden. Werkt los van je website; alle details staan op de pagina AI-telefonie.'],
        ['q' => 'Wat kost AI-telefonie?',                                     'a' => 'Vanaf € 89 per maand tot 200 gesprekken, daarboven € 0,45 per gesprek, en eenmalig € 295 voor het inrichten en samen testen. Maandelijks opzegbaar.'],
    ],

    'verder_titel' => 'Telefoon, website en administratie op elkaar aansluiten',
    'verder_basis' => [
        ['facet' => 'website',        't' => 'Website voor :bedrijven',       'b' => 'De vragen die de assistent het vaakst hoort, horen ook op je site te staan. Dan hoeft er minder gebeld te worden.'],
        ['facet' => 'automatisering', 't' => 'Verzoeken automatisch verwerken', 'b' => 'Een terugbelverzoek dat de assistent vastlegt, landt op dezelfde lijst als een aanvraag via je website.'],
        ['facet' => 'klantenportaal', 't' => 'Klantenportaal',                 'b' => 'Vaste :klanten die nu bellen om iets op te vragen, regelen het zelf.'],
    ],

    'cta_titel' => 'Hoor het zelf, of vraag een demo aan',
    'cta_tekst' => 'Plan een gesprek van een half uur; dan richten we een proefversie in met jouw :gegevens en bel je zelf om te horen hoe hij reageert.',
];
