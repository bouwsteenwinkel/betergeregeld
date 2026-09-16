<?php

/**
 * Inhoud van /ai-telefonie-advocaat op jouw-advocaat-website.nl (16-09-2026).
 * Alleen het kantoor-eigen deel; de rest komt uit config/telefonie_basis.php.
 * Bij een advocatenkantoor telt vertrouwelijkheid boven alles: de assistent bevestigt
 * nooit dat iemand cliënt is, geeft geen juridisch advies en zegt niets over een zaak.
 * Wat hij wél doet: een eerste intake netjes uitvragen, rechtsgebieden en tarieven
 * noemen, spoed (aanhouding, kort geding, deurwaarder) herkennen en doorverbinden.
 */

return [
    'demo_nummer'    => '',
    'kantoor_nummer' => '088-2545101',

    'woorden' => [
        'bedrijf'   => 'je kantoor',
        'bedrijven' => 'advocatenkantoren',
        'klant'     => 'cliënt',
        'klanten'   => 'cliënten',
        'gegevens'  => 'rechtsgebieden, welke advocaat wat doet, tarieven en of je op toevoeging werkt, hoe een intake loopt, en de spoedregeling voor aanhoudingen en termijnen',
    ],

    'seo_titel' => 'AI telefonie voor advocatenkantoren | AI telefoonassistent die opneemt als de zitting loopt',
    'hero' => [
        'eyebrow' => 'AI-telefonie voor advocaten',
        'title'   => 'AI-telefonie voor je kantoor: de telefoon wordt opgenomen, ook als jij op zitting bent',
        'sub'     => 'Een telefonische assistent die opneemt met de naam van je kantoor, een nieuwe zaak in eigen woorden uitvraagt zonder te oordelen, rechtsgebieden en tarieven noemt, spoed herkent en doorverbindt, en bestaande cliënten met een terugbelverzoek bij hun advocaat legt zonder iets over de zaak te zeggen. Op zitting, in bespreking en na kantoortijd.',
        'alt'     => 'De telefonische assistent van een advocatenkantoor',
        'usps'    => [
            'Neemt altijd op, ook als het secretariaat lunch heeft',
            'Bevestigt nooit dat iemand cliënt is; zegt niets over een zaak',
            'Intake in de woorden van de beller, compleet in je mail',
            'Na elk gesprek een samenvatting in je beveiligde portaal',
        ],
    ],

    'wanneer_titel' => 'Wanneer de telefoon het meeste stoort',
    'wanneer' => [
        ['t' => 'Op zitting',                          'b' => 'De hele ochtend in de rechtbank, het secretariaat is met twee dossiers bezig, en een nieuwe cliënt met een ontslagzaak van vijfduizend euro honorarium belt het volgende kantoor.'],
        ['t' => '"Kan ik mijn advocaat even spreken?"', 'b' => 'Een bestaande cliënt wil weten hoe het staat. Dat is een gesprek voor de advocaat, niet voor het secretariaat en niet voor een assistent. Maar hij moet wel netjes worden opgevangen.'],
        ['t' => 'Aanhouding om zes uur \'s avonds',    'b' => 'Familie belt: iemand zit vast. Dat kan niet wachten tot morgen. De assistent herkent het en verbindt door naar de piketregeling die jij vastlegt.'],
        ['t' => '"Wat kost een advocaat?"',            'b' => 'Uurtarief, eerste gesprek, toevoeging, rechtsbijstandverzekering: de vraag van elke nieuwe beller, en het antwoord staat op je website. De assistent zegt het, zonder een zaak te beoordelen.'],
    ],

    'gesprekken_titel' => 'Zo klinkt dat, in gewoon Nederlands',
    'gesprekken_lead'  => 'De antwoorden hieronder komen uit de gegevens die het kantoor zelf heeft aangeleverd. Zo praat de assistent ook over jouw kantoor: alleen wat jij hebt doorgegeven, en nooit iets over een cliënt of een zaak.',
    'gesprekken' => [
        ['vraag' => 'Ik ben ontslagen en wil weten of ik daar iets tegen kan doen.',           'antwoord' => 'Dat is een vraag voor een advocaat arbeidsrecht; daar zeg ik zelf niets over. Ik noteer graag uw situatie in uw eigen woorden, uw naam en nummer, en wanneer u het ontslag heeft gekregen, want termijnen kunnen kort zijn. Mr. De Vries belt u vandaag nog terug.'],
        ['vraag' => 'Wat kost een advocaat bij jullie?',                                         'antwoord' => 'Ons uurtarief is € 225 exclusief btw. Het eerste gesprek van een half uur is kosteloos. Komt u in aanmerking voor gesubsidieerde rechtsbijstand, dan werken we op toevoeging; dat bespreekt de advocaat in het eerste gesprek.'],
        ['vraag' => 'Mijn broer is vanmiddag aangehouden, wat moet ik doen?',                    'antwoord' => 'Dat is spoed. Ik verbind u nu door met de piketadvocaat. Lukt dat niet, dan noteer ik uw nummer en de naam van uw broer en belt hij u binnen een kwartier terug.'],
        ['vraag' => 'Ik ben cliënt van mr. Jansen en wil weten hoe het met mijn zaak staat.',     'antwoord' => 'Over zaken zeg ik niets; dat bespreekt mr. Jansen met u. Ik noteer een terugbelverzoek met uw naam en nummer. Wilt u er een korte reden bij zetten?'],
        ['vraag' => 'Bent u het kantoor van mevrouw Bakker? Ze zou daar cliënt zijn.',           'antwoord' => 'Of iemand cliënt bij ons is, kan ik niet bevestigen, ook niet aan familie. Als mevrouw Bakker zelf belt, helpen we haar graag verder.'],
        ['vraag' => 'Doen jullie ook echtscheidingen?',                                          'antwoord' => 'Ja, familierecht is een van onze rechtsgebieden; mr. El Amrani doet echtscheidingen en omgangsregelingen. Zal ik een eerste gesprek voor u aanvragen?'],
    ],

    'kan_titel' => 'Wat de assistent afhandelt',
    'kan' => [
        ['t' => 'Intake van nieuwe zaken',            'b' => 'Rechtsgebied, situatie in de woorden van de beller, tegenpartij (voor de conflictcheck), termijnen, hoe gevonden. Zonder oordeel, compleet in je mail.'],
        ['t' => 'Rechtsgebieden en advocaten',         'b' => 'Wat het kantoor doet, wie wat doet, en wat je doorverwijst.'],
        ['t' => 'Tarieven en toevoeging',              'b' => 'Uurtarief, eerste gesprek, gesubsidieerde rechtsbijstand, rechtsbijstandverzekering. Zoals jij het aanlevert.'],
        ['t' => 'Spoed herkennen',                     'b' => 'Aanhouding, kort geding, deurwaarder voor de deur, verlopende termijn: doorverbinden naar de piket- of spoedregeling die jij vastlegt.'],
        ['t' => 'Bestaande cliënten',                  'b' => 'Terugbelverzoek bij de eigen advocaat, zonder een woord over de zaak. Tijdens kantoortijd doorverbinden als de advocaat dat wil.'],
        ['t' => 'Dagberichten',                        'b' => 'Kantoor gesloten, advocaat de hele week op zitting, piket bij een ander kantoor: één bericht van jou en de assistent zegt het tegen iedere beller.'],
    ],

    'niet' => [
        'Juridisch advies geven of een zaak beoordelen. "Heb ik een kans?" gaat altijd naar een advocaat.',
        'Bevestigen dat iemand cliënt is, of iets zeggen over een zaak, ook niet aan familie of een wederpartij.',
        'Een advocaat toezeggen of een intake accepteren. Hij legt vast; het kantoor beslist na de conflictcheck.',
    ],

    'rekenhulp' => [
        'standaard' => [
            'per_dag'         => 15,
            'dagen'           => 5,
            'minuten'         => 3,
            'uurloon'         => 45,
            'oppakken'        => 5,
            'gemist_per_week' => 3,
            'bestelling_pct'  => 10,
            'bestelwaarde'    => 2000,
            'afhandel_pct'    => 50,
        ],
        'teksten' => [
            'lead'         => 'Een rekenvoorbeeld, geen belofte. Zet de schuiven op jouw kantoor en zie wat de telefoon je nu per maand kost aan secretariaatsuren, en welke omzet er in gemiste telefoontjes van nieuwe cliënten zit. Eén gemiste zaak is meer dan een jaar assistent.',
            'deel_label'   => 'Deel daarvan dat een zaak was geworden',
            'waarde_label' => 'Gemiddeld honorarium van zo\'n zaak',
            'uren_tegel'   => 'per maand niet meer aan de telefoon op het secretariaat',
            'tijd_tegel'   => 'aan secretariaatsuren die vrijkomen voor dossiers',
        ],
    ],

    'faq_titel' => 'Wat advocaten ons vragen over AI-telefonie',
    'faq' => [
        ['q' => 'Kan de assistent de telefoon opnemen voor mijn kantoor?',            'a' => 'Ja. Hij neemt op met de naam van je kantoor, vraagt nieuwe zaken uit zonder te oordelen, noemt rechtsgebieden en tarieven, herkent spoed en legt terugbelverzoeken van cliënten bij hun advocaat.'],
        ['q' => 'Hoe zit het met de geheimhoudingsplicht?',                            'a' => 'Hij bevestigt nooit dat iemand cliënt is en zegt niets over een zaak, tegen niemand. Wat hij vastlegt staat alleen in jouw beveiligde portaal en mail; geen opnames. We sluiten een verwerkersovereenkomst; welke verwerkers erachter zitten staat daarin benoemd.'],
        ['q' => 'Geeft hij juridisch advies?',                                         'a' => 'Nee, nooit. Bij elke vraag naar kansen, termijnen of wat iemand moet doen, zegt hij dat een advocaat dat beoordeelt en noteert hij de situatie voor de intake. Bij spoed verbindt hij door.'],
        ['q' => 'Kan hij een conflictcheck doen?',                                     'a' => 'Niet zelf. Hij vraagt bij een intake wel de naam van de wederpartij, zodat jij de conflictcheck kunt doen voordat je terugbelt.'],
        ['q' => 'Wat doet hij bij een aanhouding of een kort geding?',                  'a' => 'Doorverbinden naar het piket- of spoednummer dat jij opgeeft, ook buiten kantoortijd als jij dat wilt. Neemt niemand op, dan noteert hij naam, nummer en wat er speelt en zegt hij dat er binnen een afgesproken tijd wordt teruggebeld.'],
    ],

    'verder' => [
        ['facet' => 'website',        't' => 'Website voor advocatenkantoren',   'b' => 'Rechtsgebieden, tarieven en een intakeformulier dat dezelfde vragen stelt als de assistent.'],
        ['facet' => 'automatisering', 't' => 'Intakes automatisch verwerken',    'b' => 'Een intake van de assistent komt op dezelfde lijst als een aanvraag via je website, klaar voor de conflictcheck.'],
        ['facet' => 'klantenportaal', 't' => 'Cliëntenportaal',                  'b' => 'Cliënten zien stukken en afspraken in een beveiligde omgeving, zodat er minder gebeld hoeft te worden.'],
    ],

    'cta_titel' => 'Vraag een demo aan met jouw rechtsgebieden',
    'cta_tekst' => 'Plan een gesprek van een half uur; dan richten we een proefversie in met jouw rechtsgebieden, tarieven en spoedregeling en bel je zelf alsof je net ontslagen bent.',
];
