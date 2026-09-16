<?php

/**
 * Inhoud van /ai-telefonie-administratiekantoor op jouw-administratiekantoor-website.nl
 * (16-09-2026). Alleen het kantoor-eigen deel; de rest komt uit config/telefonie_basis.php.
 * De telefoon van een administratiekantoor is: klanten met "is mijn aangifte al gedaan",
 * "welke stukken moet ik aanleveren", de Belastingdienst-brief die ze niet snappen, en
 * nieuwe ondernemers die een prijs willen. Geen fiscaal advies aan de telefoon.
 */

return [
    'demo_nummer'    => '',
    'kantoor_nummer' => '088-2545101',

    'woorden' => [
        'bedrijf'   => 'je administratiekantoor',
        'bedrijven' => 'administratiekantoren',
        'klant'     => 'klant',
        'klanten'   => 'klanten',
        'gegevens'  => 'diensten en pakketprijzen, aanleverregels en deadlines (btw, IB, jaarrekening), wie welke klanten doet, en wat hij wel en niet over een dossier mag zeggen',
    ],

    'seo_titel' => 'AI telefonie voor administratiekantoren | AI telefoonassistent voor de btw-piek en het aangifteseizoen',
    'hero' => [
        'eyebrow' => 'AI-telefonie voor administratiekantoren',
        'title'   => 'AI-telefonie voor je administratiekantoor: de telefoon wordt opgenomen, ook in de laatste week van het kwartaal',
        'sub'     => 'Een telefonische assistent die opneemt met de naam van je kantoor, uitlegt welke stukken een klant moet aanleveren en wanneer, zegt wat een pakket kost, een nieuwe ondernemer netjes uitvraagt, en elke inhoudelijke vraag over een dossier of een brief van de Belastingdienst als terugbelverzoek bij de juiste collega legt. Tijdens de btw-piek, in maart en na vijven.',
        'alt'     => 'De telefonische assistent van een administratiekantoor',
        'usps'    => [
            'Neemt altijd op, ook op de 28e van de maand',
            'Aanleverregels en deadlines uit jouw eigen werkwijze',
            'Geen fiscaal advies aan de telefoon, wel de juiste collega',
            'Na elk gesprek een samenvatting per mail',
        ],
    ],

    'wanneer_titel' => 'Wanneer de telefoon het meeste stoort',
    'wanneer' => [
        ['t' => 'De laatste week van het kwartaal',   'b' => 'Btw-aangiftes af, iedereen belt met "heb je mijn bonnetjes ontvangen?", en elke oproep haalt een medewerker uit een aangifte.'],
        ['t' => 'Brief van de Belastingdienst',       'b' => 'Een klant heeft een blauwe envelop en is ongerust. Hij wil nu iemand spreken. De assistent stelt gerust met wat jij vastlegt ("stuur hem in, we kijken ernaar") en noteert wie terugbelt.'],
        ['t' => '"Wat moet ik aanleveren?"',          'b' => 'Bankafschriften, verkoopfacturen, inkoopbonnen, de kilometerregistratie: het staat op je aanleverlijst. De assistent leest hem voor, per pakket.'],
        ['t' => 'Nieuwe ondernemers',                 'b' => 'Een starter belt voor een prijs. Nu krijgt hij voicemail; met de assistent hoort hij wat het pakket kost en staat zijn aanvraag compleet in je mail.'],
    ],

    'gesprekken_titel' => 'Zo klinkt dat, in gewoon Nederlands',
    'gesprekken_lead'  => 'De antwoorden hieronder komen uit de gegevens die het kantoor zelf heeft aangeleverd. Zo praat de assistent ook over jouw kantoor: alleen wat jij hebt doorgegeven.',
    'gesprekken' => [
        ['vraag' => 'Wat kost het om mijn administratie bij jullie te laten doen? Ik ben zzp\'er.', 'antwoord' => 'Voor zzp\'ers hebben we een pakket van € 95 per maand: boekhouding, btw-aangifte per kwartaal en de aangifte inkomstenbelasting. Wilt u meer omzet of personeel, dan maken we een prijs op maat. Zal ik een kennismakingsgesprek voor u aanvragen?'],
        ['vraag' => 'Welke stukken moet ik aanleveren voor de btw-aangifte?',                    'antwoord' => 'Uw verkoopfacturen, inkoopbonnen en bankafschriften van het kwartaal, uiterlijk de tiende van de maand erna, via de app of per mail. Heeft u een auto van de zaak, dan ook de kilometerregistratie.'],
        ['vraag' => 'Is mijn btw-aangifte al ingediend?',                                        'antwoord' => 'Dat kan ik aan de telefoon niet nakijken; ik wil u geen verkeerd antwoord geven. Ik noteer uw vraag en Sandra, die uw dossier doet, belt u vandaag terug. Mag ik uw bedrijfsnaam?'],
        ['vraag' => 'Ik heb een brief van de Belastingdienst over een naheffing, wat moet ik doen?', 'antwoord' => 'Stuur de brief naar ons via de app of per mail, dan kijken we ernaar voordat de termijn verloopt. Ik noteer een terugbelverzoek voor Sandra. Wanneer is de reactietermijn?'],
        ['vraag' => 'Mag ik mijn nieuwe laptop aftrekken?',                                     'antwoord' => 'Dat hangt van uw situatie af en daar wil ik aan de telefoon niets over zeggen. Ik noteer de vraag voor uw contactpersoon; die neemt het mee bij de volgende aangifte of belt u terug.'],
        ['vraag' => 'Wanneer moet de aangifte inkomstenbelasting binnen zijn?',                  'antwoord' => 'Voor onze klanten geldt uitstel tot 1 mei van het jaar erop via de uitstelregeling. Wij vragen uw stukken vóór 1 maart, zodat er tijd is om vragen te stellen.'],
    ],

    'kan_titel' => 'Wat de assistent afhandelt',
    'kan' => [
        ['t' => 'Aanleverregels en deadlines',       'b' => 'Wat, wanneer en hoe aanleveren, per pakket. Btw-kwartalen, IB, jaarrekening, salaris. Uit jouw werkwijze.'],
        ['t' => 'Pakketten en prijzen',               'b' => 'Wat een zzp-pakket of een bv-pakket kost en wat erin zit, met de opmerking dat maatwerk na een gesprek komt.'],
        ['t' => 'Kennismakingsverzoeken',             'b' => 'Rechtsvorm, omzet, personeel ja of nee, huidige boekhouder, waar het om gaat. Compleet in je mail.'],
        ['t' => 'Naar de juiste collega',             'b' => 'Wie welke klanten doet, wie salaris doet, wie fiscaal. Hij verbindt door of legt een terugbelverzoek bij de goede persoon.'],
        ['t' => 'Brieven van de Belastingdienst',     'b' => 'Geruststellen met wat jij vastlegt, vragen om de brief in te sturen, de termijn noteren en de juiste collega laten terugbellen.'],
        ['t' => 'Dagberichten',                       'b' => 'Btw-deadline nadert, kantoor dicht tussen kerst en oud en nieuw, app tijdelijk in storing: één bericht van jou en de assistent zegt het tegen iedere beller.'],
    ],

    'niet' => [
        'Fiscaal of boekhoudkundig advies geven. "Mag ik dit aftrekken?" gaat altijd naar een medewerker.',
        'Iets zeggen over de stand van een dossier of aangifte. Hij weet het niet, en zegt dat eerlijk.',
        'Een prijs op maat toezeggen. Hij noemt pakketprijzen; maatwerk komt na een gesprek.',
    ],

    'rekenhulp' => [
        'standaard' => [
            'per_dag'         => 20,
            'dagen'           => 5,
            'minuten'         => 3,
            'uurloon'         => 45,
            'oppakken'        => 6,
            'gemist_per_week' => 4,
            'bestelling_pct'  => 10,
            'bestelwaarde'    => 1000,
            'afhandel_pct'    => 55,
        ],
        'teksten' => [
            'lead'         => 'Een rekenvoorbeeld, geen belofte. Zet de schuiven op jouw kantoor en zie wat de telefoon je nu per maand kost aan medewerkers die uit een aangifte gehaald worden, en welke omzet er in gemiste telefoontjes van nieuwe ondernemers zit. Bij administratie is de onderbreking het grootste getal: een aangifte hervatten kost meer dan het gesprek zelf.',
            'deel_label'   => 'Deel daarvan dat een nieuwe klant was',
            'waarde_label' => 'Wat een nieuwe klant het eerste jaar oplevert',
            'uren_tegel'   => 'per maand niet meer aan de telefoon of uit een aangifte gehaald',
            'tijd_tegel'   => 'aan loonkosten die vrijkomen voor aangiftes en jaarrekeningen',
        ],
    ],

    'faq_titel' => 'Wat administratiekantoren ons vragen over AI-telefonie',
    'faq' => [
        ['q' => 'Kan de assistent de telefoon opnemen voor mijn kantoor?',        'a' => 'Ja. Hij neemt op met de naam van je kantoor, beantwoordt vragen over aanleveren, deadlines en pakketten, verbindt door naar de juiste collega en legt de rest vast als terugbelverzoek.'],
        ['q' => 'Geeft hij fiscaal advies?',                                       'a' => 'Nee, nooit. Bij elke vraag over aftrekposten, btw-tarieven of een specifieke situatie zegt hij dat een medewerker dat beoordeelt en noteert hij een terugbelverzoek voor de juiste collega.'],
        ['q' => 'Kan hij zeggen of een aangifte al is ingediend?',                  'a' => 'Niet zonder koppeling op je systeem, en ook dan alleen tegen de klant zelf na identificatie. Zonder koppeling zegt hij eerlijk dat hij het niet weet en laat hij de behandelaar terugbellen.'],
        ['q' => 'Hoe gaat hij om met een brief van de Belastingdienst?',            'a' => 'Hij vraagt de klant de brief in te sturen, noteert de reactietermijn en zorgt dat de behandelaar terugbelt. Hij zegt niets over wat de brief betekent.'],
        ['q' => 'Werkt hij ook voor de salarisadministratie?',                      'a' => 'Ja, als je die aanbiedt: hij weet wie salaris doet, welke stukken daarvoor nodig zijn en wanneer mutaties binnen moeten. Vragen van werknemers van klanten verwijst hij naar hun werkgever, tenzij jij anders aangeeft.'],
    ],

    'verder' => [
        ['facet' => 'website',        't' => 'Website voor administratiekantoren', 'b' => 'Pakketten, aanleverlijst en deadlines op je site, zodat de assistent minder hoeft uit te leggen.'],
        ['facet' => 'automatisering', 't' => 'Verzoeken automatisch verwerken',   'b' => 'Een terugbelverzoek van de assistent komt op de takenlijst van de juiste behandelaar.'],
        ['facet' => 'klantenportaal', 't' => 'Klantenportaal',                    'b' => 'Klanten uploaden stukken en zien de stand van hun aangifte zelf, zonder te bellen.'],
    ],

    'cta_titel' => 'Vraag een demo aan met jouw aanleverregels',
    'cta_tekst' => 'Plan een gesprek van een half uur; dan richten we een proefversie in met jouw pakketten en deadlines en bel je zelf alsof je een zzp\'er met een blauwe envelop bent.',
];
