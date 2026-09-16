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
 * prijs: advies 15-09-2026, zie docs/bakkerij/CHANGES-SEO-CONTENT.md voor de onderbouwing.
 * Dennis stelt het definitieve bedrag vast; tekst hieronder is wat de pagina toont.
 */

return [
    'demo_nummer'      => '088 254 5170',          // lijn staat sinds 15-09 op de telefoniemachine (KANALEN 31882545170)
    'demo_bakkerij'    => 'Bakkerij Kruimel',      // naam van de fictieve demo-bakkerij

    // De vaste bestellingen in de demo (bouwsteenwinkel_v3/scripts/_aitest-bakkerij-tabellen.php).
    // Alleen zichtbaar als demo_nummer gevuld is. Dagen zijn afstanden vanaf vandaag.
    'demo_kaartjes' => [
        ['nummer' => 'K-1042', 'naam' => 'De Wit',    'hoor' => 'Twee appeltaarten staan klaar; vandaag ophalen, al betaald.'],
        ['nummer' => 'K-1057', 'naam' => 'Jansen',    'hoor' => 'Slagroomtaart met "Sam 7", overmorgen om half twaalf ophalen.'],
        ['nummer' => 'K-1063', 'naam' => 'El Amrani', 'hoor' => 'Veertig belegde broodjes, morgen bezorgd bij het notariskantoor, op rekening.'],
        ['nummer' => 'K-1071', 'naam' => 'Bakker',    'hoor' => 'Chocoladetaart over vijf dagen; de vraag over een foto op de taart staat nog open.'],
    ],
    'demo_probeer' => [
        'Bestel zelf een taart voor zaterdag: hij leest de bestelling terug en geeft een bestelnummer.',
        'Vraag of die taart morgen al kan (twee dagen vooruit: hij zegt eerlijk wat de vroegste dag is).',
        'Vraag of er noten in de amandelstaaf zitten (allergenen gaan altijd naar een medewerker).',
        'Bel buiten openingstijden: hij neemt de bestelling gewoon aan.',
    ],
    'kantoor_nummer'   => '088-2545101',

    'prijs' => [
        'vanaf'        => '€ 89',
        'periode'      => 'per maand',
        'inbegrepen'   => 'tot 200 gesprekken per maand, inrichting van je kennisbank, dagberichten en de samenvattingen per mail',
        'extra'        => '€ 0,45 per gesprek daarboven',
        'eenmalig'     => '€ 295 voor het inrichten en samen testen',
        'opzeg'        => 'maandelijks opzegbaar, geen contract',
        'toelichting'  => 'Een gemiddeld gesprek van anderhalve minuut kost ons zelf ongeveer twintig cent aan spraak en rekentijd; een lang bestelgesprek het dubbele. Wat je betaalt is vooral de inrichting, het nummer en het bijhouden van jouw gegevens. Je assortiment mag zo groot zijn als je wilt: de assistent zoekt erin op, hij leert het niet uit zijn hoofd, dus dat kost niets extra.',
    ],

    // Rekenhulp op de telefoniepagina ("wat levert het op"). De prijzen hier moeten
    // gelijk blijven aan 'prijs' hierboven; de standaardwaarden zijn de startstand van de
    // schuiven en bewust bescheiden gekozen. Het is een rekenvoorbeeld, geen belofte:
    // de tekst op de pagina zegt dat ook.
    'rekenhulp' => [
        'maandprijs'           => 89,
        'inbegrepen_gesprekken'=> 200,
        'extra_per_gesprek'    => 0.45,
        'standaard' => [
            'per_dag'        => 12,   // telefoontjes per werkdag
            'dagen'          => 6,    // werkdagen per week
            'minuten'        => 2,    // gemiddelde duur per gesprek
            'uurloon'        => 22,   // loonkosten per uur, alles erin
            'oppakken'       => 3,    // minuten om de draad weer op te pakken na een onderbreking
            'gemist_per_week'=> 8,    // gesprekken die nu niet opgenomen worden (druk, gesloten)
            'bestelling_pct' => 25,   // deel daarvan dat een bestelling was
            'bestelwaarde'   => 18,   // gemiddelde bestelling aan de telefoon
            'afhandel_pct'   => 70,   // deel van de gesprekken dat de assistent zelf afrondt
        ],
    ],

    'hero' => [
        'eyebrow' => 'AI-telefonie voor bakkerijen',
        'title'   => 'AI-telefonie voor je bakkerij: de telefoon wordt opgenomen, ook als jij geen tijd hebt',
        'sub'     => 'Een telefonische assistent die opneemt met de naam van je bakkerij, openingstijden en assortimentsvragen beantwoordt, een bestel- of terugbelverzoek vastlegt en doorverbindt als het moet. Tijdens de ochtenddrukte, tijdens het bakken en buiten openingstijden.',
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
        ['t' => 'Terugbelverzoek',                      'b' => 'Naam en telefoonnummer, herhaald ter controle, met waar het over ging.'],
        ['t' => 'Bestellingen aannemen',                'b' => 'Een taart, een zakelijke bestelling, veertig broodjes: hij zoekt het product op, let op de besteltermijn, leest de bestelling terug en geeft een bestelnummer. Jij bevestigt; geen betaling aan de telefoon.'],
        ['t' => '"Is mijn bestelling klaar?"',           'b' => 'Op bestelnummer en achternaam zegt hij wat erop staat, voor welke dag, en of er al betaald is.'],
        ['t' => 'Doorverbinden',                        'b' => 'Tijdens openingstijden naar de winkel of een collega, als de beller of de vraag daarom vraagt.'],
        ['t' => 'Samenvatting',                         'b' => 'Na elk gesprek een korte samenvatting per mail, en alles terug te lezen in je eigen portaal.'],
        ['t' => 'Dagberichten',                         'b' => 'Vandaag geen krentenbollen, morgen dicht: één bericht van jou en de assistent zegt het tegen iedere beller.'],
        ['t' => 'Nederlands, en Engels als je dat wilt', 'b' => 'Standaard Nederlands. Engels kan erbij worden ingericht voor bellers die geen Nederlands spreken.'],
    ],

    'niet_titel' => 'Wat we bewust níét laten doen',
    'niet' => [
        'Uitspraken over allergenen die niet uit jouw actuele productinformatie komen. Bij twijfel verwijst de assistent naar een medewerker.',
        'Betalingen aannemen, of een bestelling wijzigen die vandaag al klaarstaat. Een bestelling die hij aanneemt bevestig jij; wijzigen en annuleren gaan via jou.',
        'Gesprekken opnemen. Er wordt een samenvatting gemaakt, geen geluidsopname.',
        'Afspraken in een agenda zetten. Dat kan wel via je website; aan de telefoon houden we het bij een verzoek.',
        'Iets verzinnen. Weet de assistent het niet, dan zegt hij dat en legt hij een terugbelverzoek vast.',
    ],

    'nummer_titel' => 'Je houdt je eigen telefoonnummer',
    'nummer_tekst' => 'De assistent krijgt een eigen nummer. Jouw bestaande nummer schakel je door: altijd, alleen bij geen gehoor, of alleen buiten openingstijden. Dat stel je in bij je telefoonaanbieder; wij helpen daarbij. Wil je later stoppen, dan zet je de doorschakeling uit en is alles weer zoals het was.',

    'stappen_titel' => 'Zo richten we het in',
    'stappen' => [
        ['t' => 'Gesprek van een half uur',   'b' => 'Wat bellen mensen nu, wat wil je dat de assistent zegt, en wanneer moet hij doorverbinden?'],
        ['t' => 'Jouw gegevens erin',          'b' => 'Openingstijden, vestigingen, assortiment, bestelregels, bezorggebied. Jij levert aan, wij zetten het om in de kennisbank.'],
        ['t' => 'Samen testbellen',            'b' => 'Je belt zelf en hoort hoe hij reageert. Wat niet klopt passen we aan, tot jij tevreden bent.'],
        ['t' => 'Doorschakelen en meekijken',  'b' => 'Je zet de doorschakeling aan. De eerste weken kijken we mee naar de samenvattingen en scherpen we bij.'],
    ],

    'privacy_titel' => 'Privacy en AVG',
    'privacy_tekst' => 'De assistent bewaart wat nodig is om een verzoek af te handelen: naam, telefoonnummer en de inhoud van het verzoek. Geen geluidsopnames. De samenvattingen staan in je eigen portaal en worden niet gebruikt om iets te trainen. Wil je dat bellers aan het begin horen dat ze met een assistent spreken, dan richten we dat zo in.',

    'faq' => [
        ['q' => 'Wat is AI-telefonie?',                                   'a' => 'Een computerprogramma dat de telefoon opneemt, luistert, in gewoon Nederlands antwoordt en vastlegt wat de beller wil. Het antwoordt uit gegevens die jij hebt aangeleverd, niet uit het internet.'],
        ['q' => 'Kan de assistent de telefoon opnemen voor mijn bakkerij?', 'a' => 'Ja. Hij neemt op met de naam van je bakkerij en handelt de veelvoorkomende vragen af. Wat hij niet weet, legt hij vast als terugbelverzoek.'],
        ['q' => 'Kan hij bestellingen aannemen?',                         'a' => 'Ja. Hij zoekt het product op in jouw assortiment, let op de besteltermijn (een taart twee dagen vooruit, brood vandaag nog), leest de bestelling terug en geeft de beller een bestelnummer. De bestelling staat direct in je portaal en in je mail; jij bevestigt hem. Er wordt niets afgerekend aan de telefoon. Wil je online laten bestellen en betalen, dan is de webshop daarvoor.'],
        ['q' => 'Kan hij openingstijden en productvragen beantwoorden?',   'a' => 'Ja, uit jouw eigen gegevens: openingstijden per vestiging, assortiment, bestelregels, bezorggebied. Verandert er iets, dan geef je dat door of zet je een dagbericht.'],
        ['q' => 'Kan hij vragen over allergenen beantwoorden?',            'a' => 'Alleen als jij daarvoor betrouwbare, actuele productinformatie hebt aangeleverd en dat wilt. Bij twijfel verwijst hij altijd naar een medewerker. Voedselveiligheid laten we niet aan een assistent over.'],
        ['q' => 'Wat gebeurt er als hij het antwoord niet weet?',           'a' => 'Dan zegt hij dat eerlijk, noteert naam en telefoonnummer en de vraag, en jij belt terug. Tijdens openingstijden kan hij ook doorverbinden.'],
        ['q' => 'Kan ik mijn huidige telefoonnummer behouden?',             'a' => 'Ja. Je schakelt je bestaande nummer door naar de assistent, altijd of alleen bij geen gehoor of buiten openingstijden. Stoppen is de doorschakeling uitzetten.'],
        ['q' => 'Werkt hij buiten openingstijden?',                         'a' => 'Ja, 24 uur per dag. Buiten openingstijden verbindt hij niet door maar legt hij verzoeken vast; die staan de volgende ochtend in je mail.'],
        ['q' => 'Krijg ik een samenvatting van gesprekken?',                'a' => 'Na elk gesprek een korte samenvatting per mail, en alle gesprekken terug te lezen in je eigen portaal. Geen geluidsopnames.'],
        ['q' => 'Wat kost AI-telefonie?',                                   'a' => 'Vanaf € 89 per maand tot 200 gesprekken, daarboven € 0,45 per gesprek, en eenmalig € 295 voor het inrichten en samen testen. Maandelijks opzegbaar.'],
        ['q' => 'Hoe snel kan het worden ingericht?',                       'a' => 'Meestal binnen een week na het eerste gesprek, afhankelijk van hoe snel jouw gegevens compleet zijn. Het testbellen doen we samen.'],
        ['q' => 'Moet ik een nieuwe website hebben?',                       'a' => 'Nee. AI-telefonie werkt los van je website. Je kunt hem ook als enige dienst afnemen.'],
    ],

    'cta_titel' => 'Hoor het zelf, of vraag een demo aan',
    'cta_tekst' => 'Bel het demonummer en stel de vragen die jouw klanten stellen. Of plan een gesprek van een half uur; dan richten we een proefversie in met jouw openingstijden en assortiment.',
];
