<?php

/**
 * Inhoud van /ai-telefonie-aannemer op jouw-aannemer-website.nl (16-09-2026).
 * Alleen het aannemer-eigen deel; de rest komt uit config/telefonie_basis.php.
 * Bij een aannemer is de telefoon vooral: nieuwe aanvragen, leveranciers en onderaannemers,
 * en de opdrachtgever die wil weten wanneer de stukadoor komt. De assistent kan het eerste
 * en het laatste afvangen; het middelste verbindt hij door.
 */

return [
    'demo_nummer'    => '',
    'kantoor_nummer' => '088-2545101',

    'woorden' => [
        'bedrijf'   => 'je aannemersbedrijf',
        'bedrijven' => 'aannemers',
        'klant'     => 'opdrachtgever',
        'klanten'   => 'opdrachtgevers',
        'gegevens'  => 'werkgebied, soorten werk, hoe een offerte bij jou loopt, de lopende projecten die hij mag noemen en wie waarvoor gebeld mag worden',
    ],

    'seo_titel' => 'AI telefonie voor aannemers | AI telefoonassistent die opneemt als jij op de bouw staat',
    'hero' => [
        'eyebrow' => 'AI-telefonie voor aannemers',
        'title'   => 'AI-telefonie voor je aannemersbedrijf: de telefoon wordt opgenomen, ook als jij op de steiger staat',
        'sub'     => 'Een telefonische assistent die opneemt met de naam van je bedrijf, een nieuwe aanvraag netjes uitvraagt (wat, waar, wanneer, budget), leveranciers en onderaannemers doorverbindt met de juiste uitvoerder en de opdrachtgever vertelt wat jij hebt vastgelegd. Op de bouw, in de bus en na vijven.',
        'alt'     => 'De telefonische assistent van een aannemersbedrijf',
        'usps'    => [
            'Neemt altijd op, ook als je een helm op hebt',
            'Een aanvraag komt compleet binnen: adres, werk, wens, termijn',
            'Leveranciers en onderaannemers naar de juiste persoon',
            'Na elk gesprek een samenvatting per mail',
        ],
    ],

    'wanneer_titel' => 'Wanneer de telefoon het meeste stoort',
    'wanneer' => [
        ['t' => 'Op de steiger',                    'b' => 'Je staat met twee man een balk te stellen en de telefoon gaat. Opnemen kan niet; de beller met een verbouwing van tachtigduizend euro belt de volgende aannemer.'],
        ['t' => '"Wanneer komt de stukadoor?"',     'b' => 'De opdrachtgever belt drie keer per week over de planning. Het antwoord staat in je planning; iemand moet het alleen zeggen.'],
        ['t' => 'Leverancier op de lijn',           'b' => 'De bouwmarkt belt over een levering, de kraanverhuur over morgen. Dat moet naar de uitvoerder, niet naar de voicemail.'],
        ['t' => 'Aanvragen in de avond',            'b' => 'Wie een dakkapel wil, belt na het eten. Nu krijgt hij voicemail; met de assistent staat de aanvraag morgenochtend compleet in je mail.'],
    ],

    'gesprekken_titel' => 'Zo klinkt dat, in gewoon Nederlands',
    'gesprekken_lead'  => 'De antwoorden hieronder komen uit de gegevens die het aannemersbedrijf zelf heeft aangeleverd. Zo praat de assistent ook over jouw bedrijf: alleen wat jij hebt doorgegeven.',
    'gesprekken' => [
        ['vraag' => 'We willen een uitbouw aan de achterkant, kunnen jullie daar een offerte voor maken?', 'antwoord' => 'Graag. Mag ik het adres, hoe groot u ongeveer denkt, en wanneer u zou willen starten? Dan lees ik het terug: uitbouw van ongeveer vier bij drie in Amersfoort, start liefst in het voorjaar, op naam van De Groot. U hoort binnen twee werkdagen wanneer we kunnen komen kijken.'],
        ['vraag' => 'Doen jullie ook kleine klussen, een deur afhangen bijvoorbeeld?',    'antwoord' => 'Kleine klussen doen we alleen voor bestaande opdrachtgevers. Voor een losse klus verwijzen we naar Klusbedrijf Van Vliet; wilt u hun nummer?'],
        ['vraag' => 'Ik ben van de bouwmarkt, over de levering van morgen.',               'antwoord' => 'Ik verbind u door met de uitvoerder van dat project. Om welk adres gaat het?'],
        ['vraag' => 'Wanneer komt de stukadoor bij ons?',                                  'antwoord' => 'Volgens de planning die ik heb staat de stukadoor bij u op dinsdag en woensdag. Verandert dat, dan belt de uitvoerder u. Klopt het dat dit over de Lindelaan gaat?'],
        ['vraag' => 'Wat kost een dakkapel bij jullie?',                                   'antwoord' => 'Een dakkapel begint bij ons rond € 9.000, afhankelijk van breedte en afwerking. Een echte prijs krijgt u na een bezoek. Wilt u dat ik een offerteverzoek noteer?'],
        ['vraag' => 'Er lekt water door het dak op de bouw, ik ben de buurman.',            'antwoord' => 'Dat geef ik direct door. Ik verbind u door met de uitvoerder; lukt dat niet, dan belt hij u binnen een kwartier terug. Mag ik uw nummer?'],
    ],

    'kan_titel' => 'Wat de assistent afhandelt',
    'kan' => [
        ['t' => 'Aanvragen uitvragen',             'b' => 'Adres, soort werk, omvang, gewenste start, hoe de beller bij je kwam. Compleet in je mail, klaar om in te plannen.'],
        ['t' => 'Werkgebied en soorten werk',       'b' => 'Wat je doet (verbouw, aanbouw, dakkapel, renovatie) en wat niet, en naar wie je doorverwijst.'],
        ['t' => 'Opdrachtgevers informeren',        'b' => 'Wat jij per project hebt vastgelegd: welke week welke ploeg, wie de uitvoerder is. Niet meer, niet minder.'],
        ['t' => 'Leveranciers en onderaannemers',   'b' => 'Naar de juiste uitvoerder doorverbinden, of een bericht vastleggen als die op de bouw niet opneemt.'],
        ['t' => 'Richtprijzen',                     'b' => 'Vanaf-prijzen die jij opgeeft, met de opmerking dat een echte prijs na een bezoek komt.'],
        ['t' => 'Dagberichten',                     'b' => 'Vorst, ploeg ziek, project X een week vertraagd: één bericht van jou en de assistent zegt het tegen wie ernaar vraagt.'],
    ],

    'niet' => [
        'Een prijs of planning toezeggen die jij niet hebt vastgelegd. Een offerte komt na een bezoek; een datum na overleg.',
        'Iets zeggen over een project tegen iemand die niet de opdrachtgever is. Hij vraagt naam en adres en houdt het daarbij.',
        'Bouwkundig advies geven. "Kan die muur eruit?" gaat naar jou.',
    ],

    'rekenhulp' => [
        'standaard' => [
            'per_dag'         => 12,
            'dagen'           => 5,
            'minuten'         => 3,
            'uurloon'         => 55,
            'oppakken'        => 5,
            'gemist_per_week' => 4,
            'bestelling_pct'  => 5,
            'bestelwaarde'    => 2500,
            'afhandel_pct'    => 60,
        ],
        'teksten' => [
            'lead'         => 'Een rekenvoorbeeld, geen belofte. Zet de schuiven op jouw bedrijf en zie wat de telefoon je nu per maand kost aan uren van jou en je uitvoerder, en welke omzet er in gemiste aanvragen zit. Bij een aannemer is één gemiste verbouwing meer dan een jaar assistent.',
            'deel_label'   => 'Deel daarvan dat een opdracht was geworden',
            'waarde_label' => 'Gemiddelde marge op zo\'n opdracht',
            'uren_tegel'   => 'per maand niet meer aan de telefoon of van de bouw gehaald',
            'tijd_tegel'   => 'aan uren van jou en je uitvoerder die weer naar het werk gaan',
            'omzet_tegel'  => 'marge per maand in aanvragen die nu niemand opneemt',
        ],
    ],

    'faq_titel' => 'Wat aannemers ons vragen over AI-telefonie',
    'faq' => [
        ['q' => 'Kan de assistent de telefoon opnemen voor mijn aannemersbedrijf?', 'a' => 'Ja. Hij neemt op met de naam van je bedrijf, vraagt een nieuwe aanvraag compleet uit, verbindt leveranciers en onderaannemers door naar de juiste uitvoerder en legt de rest vast.'],
        ['q' => 'Kan hij opdrachtgevers vertellen wanneer welke ploeg komt?',        'a' => 'Alleen wat jij per project hebt vastgelegd, en alleen tegen de opdrachtgever zelf (naam en adres). Verandert de planning, dan pas je het aan of zet je een dagbericht.'],
        ['q' => 'Noemt hij prijzen?',                                               'a' => 'Vanaf-prijzen die jij opgeeft, bijvoorbeeld voor een dakkapel of uitbouw, altijd met de opmerking dat de echte prijs na een bezoek komt. Geen offertes aan de telefoon.'],
        ['q' => 'Wat doet hij met leveranciers en onderaannemers?',                  'a' => 'Doorverbinden naar de uitvoerder van dat project; neemt die niet op, dan legt hij het bericht vast en krijgt de uitvoerder het per mail en in het portaal.'],
        ['q' => 'Kan hij onderscheid maken tussen een serieuze aanvraag en een prijsvrager?', 'a' => 'Hij vraagt bij elke aanvraag hetzelfde uit: adres, werk, omvang, termijn en of er al tekeningen zijn. Daarmee zie jij in tien seconden of het de moeite is om te gaan kijken.'],
    ],

    'verder' => [
        ['facet' => 'website',        't' => 'Website voor aannemers',           'b' => 'Projecten, werkgebied en een aanvraagformulier dat dezelfde vragen stelt als de assistent.'],
        ['facet' => 'automatisering', 't' => 'Aanvragen automatisch verwerken',  'b' => 'Een aanvraag van de assistent komt op dezelfde lijst als een aanvraag via je website, met alles erin om te kunnen plannen.'],
        ['facet' => 'klantenportaal', 't' => 'Klantenportaal voor opdrachtgevers', 'b' => 'Planning, meerwerk en foto\'s van de bouw op één plek, zodat er minder gebeld wordt.'],
    ],

    'cta_titel' => 'Vraag een demo aan met jouw soorten werk',
    'cta_tekst' => 'Plan een gesprek van een half uur; dan richten we een proefversie in met jouw werkgebied en aanvraagvragen en bel je zelf alsof je een uitbouw wilt.',
];
