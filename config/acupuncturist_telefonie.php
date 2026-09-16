<?php

/**
 * Inhoud van /ai-telefonie-acupuncturist op jouw-acupuncturist-website.nl (16-09-2026).
 * Alleen het praktijk-eigen deel; de rest komt uit config/telefonie_basis.php.
 * Een behandelaar zit een uur met een patiënt en kan niet opnemen. De assistent zegt NIETS
 * medisch: geen "helpt acupunctuur bij ..."; wel wat een behandeling kost, hoe vergoeding
 * werkt (algemeen, zoals de praktijk het aanlevert) en wat een eerste afspraak inhoudt.
 */

return [
    'demo_nummer'    => '',
    'kantoor_nummer' => '088-2545101',

    'woorden' => [
        'bedrijf'   => 'je praktijk',
        'bedrijven' => 'acupuncturisten',
        'klant'     => 'patiënt',
        'klanten'   => 'patiënten',
        'gegevens'  => 'tarieven, hoe een eerste consult verloopt, bij welke verzekeraars en onder welke aanvullende pakketten je behandelingen worden vergoed, praktijktijden en de afzegregels',
    ],

    'seo_titel' => 'AI telefonie voor acupuncturisten | AI telefoonassistent die opneemt terwijl jij behandelt',
    'hero' => [
        'eyebrow' => 'AI-telefonie voor acupuncturisten',
        'title'   => 'AI-telefonie voor je praktijk: de telefoon wordt opgenomen, ook als jij een patiënt behandelt',
        'sub'     => 'Een telefonische assistent die opneemt met de naam van je praktijk, uitlegt wat een eerste consult inhoudt en kost, hoe vergoeding via de aanvullende verzekering werkt, een afspraakverzoek of afzegging vastlegt en medische vragen zonder omwegen bij jou neerlegt. Tijdens de behandeling, in de lunchpauze en \'s avonds.',
        'alt'     => 'De telefonische assistent van een acupunctuurpraktijk',
        'usps'    => [
            'Neemt altijd op, ook als je naalden zet',
            'Geen medisch advies, wel alle praktische antwoorden',
            'Tarieven en vergoeding uit jouw eigen gegevens',
            'Na elk gesprek een samenvatting per mail',
        ],
    ],

    'wanneer_titel' => 'Wanneer de telefoon het meeste stoort',
    'wanneer' => [
        ['t' => 'Tijdens de behandeling',            'b' => 'Een patiënt ligt met naalden en jij kunt niet weg. Wie nu belt en voicemail krijgt, belt vaak niet terug: een nieuwe patiënt is kwetsbaar en aarzelt toch al.'],
        ['t' => '"Wordt het vergoed?"',              'b' => 'De eerste vraag van bijna iedereen. Het antwoord hangt af van verzekeraar en pakket; jij hebt het op een rijtje, de assistent zegt het.'],
        ['t' => 'Afzeggen op de dag zelf',           'b' => 'Ziek, file, kind ziek. De assistent noteert het, noemt jouw afzegregel en jij ziet het tussen twee patiënten door.'],
        ['t' => 'Vragen die je niet aan de telefoon wilt', 'b' => '"Helpt acupunctuur bij migraine?" is een gesprek in de behandelkamer, niet aan de telefoon. De assistent zegt dat ook, vriendelijk, en noteert een terugbelverzoek.'],
    ],

    'gesprekken_titel' => 'Zo klinkt dat, in gewoon Nederlands',
    'gesprekken_lead'  => 'De antwoorden hieronder komen uit de gegevens die de praktijk zelf heeft aangeleverd. Zo praat de assistent ook over jouw praktijk: alleen wat jij hebt doorgegeven.',
    'gesprekken' => [
        ['vraag' => 'Wat kost een behandeling en wordt het vergoed?',                'antwoord' => 'Een eerste consult van 75 minuten kost € 95, een vervolgbehandeling van 45 minuten € 70. Vergoeding loopt via de aanvullende verzekering; bij de meeste verzekeraars valt het onder alternatieve geneeswijzen. Wat u terugkrijgt hangt van uw pakket af; dat ziet u in uw polis. Zal ik een afspraakverzoek noteren?'],
        ['vraag' => 'Ik wil graag een afspraak maken.',                              'antwoord' => 'Graag. Bent u al eerder bij ons geweest? Dan noteer ik uw naam, nummer en op welke dagdelen u kunt; de praktijk belt u vandaag nog terug met een tijd. Nieuwe patiënten kunnen meestal binnen twee weken terecht.'],
        ['vraag' => 'Helpt acupunctuur bij migraine?',                               'antwoord' => 'Dat is een vraag die de acupuncturist graag met u bespreekt; aan de telefoon wil ik daar niets over zeggen. Ik noteer een terugbelverzoek; u wordt vanmiddag gebeld. Of zal ik een kennismakingsgesprek voor u aanvragen?'],
        ['vraag' => 'Ik moet mijn afspraak van vanmiddag afzeggen, ik ben ziek.',      'antwoord' => 'Beterschap. Ik geef het door. Onze regel is dat afzeggen tot 24 uur van tevoren kosteloos is; bij ziekte beslist de praktijk daar zelf over. Wilt u meteen een nieuwe voorkeur doorgeven?'],
        ['vraag' => 'Heb ik een verwijzing van de huisarts nodig?',                   'antwoord' => 'Nee, voor acupunctuur is geen verwijzing nodig. Sommige verzekeraars vragen wel dat de behandelaar is aangesloten bij een beroepsvereniging; dat is bij ons het geval.'],
        ['vraag' => 'Waar kan ik parkeren?',                                          'antwoord' => 'Achter het pand is een parkeerplaats voor patiënten; de ingang is aan de zijkant. Komt u met de trein, dan is het acht minuten lopen vanaf het station.'],
    ],

    'kan_titel' => 'Wat de assistent afhandelt',
    'kan' => [
        ['t' => 'Tarieven en vergoeding',            'b' => 'Wat een consult kost, hoe de aanvullende verzekering werkt, bij welke beroepsvereniging je bent aangesloten. Uit jouw gegevens, zonder beloftes over bedragen.'],
        ['t' => 'Afspraakverzoeken',                  'b' => 'Nieuwe of bestaande patiënt, naam, nummer, voorkeursdagdelen. Jij plant in en bevestigt.'],
        ['t' => 'Afzeggingen',                        'b' => 'Wie, welke afspraak, waarom. Hij noemt jouw afzegregel en laat de beslissing bij jou.'],
        ['t' => 'Praktische vragen',                  'b' => 'Wat een eerste consult inhoudt, hoe lang het duurt, kleding, parkeren, bereikbaarheid. Uit jouw gegevens.'],
        ['t' => 'Dagberichten',                       'b' => 'Praktijk gesloten, waarneming door een collega, later open: één bericht van jou en de assistent zegt het tegen iedere beller.'],
    ],

    'niet' => [
        'Iets zeggen over klachten, behandelingen of resultaten. "Helpt het bij ...?" gaat altijd naar jou, als terugbelverzoek of kennismakingsgesprek.',
        'Een vergoedingsbedrag toezeggen. Hij legt uit hoe het werkt en verwijst naar de polis.',
        'Afspraken inplannen of verzetten. Hij legt het verzoek vast; jij bevestigt.',
    ],

    'rekenhulp' => [
        'standaard' => [
            'per_dag'         => 8,
            'dagen'           => 5,
            'minuten'         => 2.5,
            'uurloon'         => 40,
            'oppakken'        => 2,
            'gemist_per_week' => 6,
            'bestelling_pct'  => 20,
            'bestelwaarde'    => 300,
            'afhandel_pct'    => 70,
        ],
        'teksten' => [
            'lead'         => 'Een rekenvoorbeeld, geen belofte. Zet de schuiven op jouw praktijk en zie welke omzet er in gemiste telefoontjes zit. Reken een nieuwe patiënt als een behandeltraject van een paar consulten, niet als één afspraak.',
            'deel_label'   => 'Deel daarvan dat een nieuwe patiënt was',
            'waarde_label' => 'Wat een behandeltraject je gemiddeld oplevert',
            'uren_tegel'   => 'per maand niet meer aan terugbellen tussen patiënten door',
            'tijd_tegel'   => 'aan uren die weer naar behandelen gaan in plaats van bellen',
        ],
    ],

    'faq_titel' => 'Wat acupuncturisten ons vragen over AI-telefonie',
    'faq' => [
        ['q' => 'Kan de assistent de telefoon opnemen voor mijn praktijk?',      'a' => 'Ja. Hij neemt op met de naam van je praktijk, beantwoordt vragen over tarieven, vergoeding en de gang van zaken, en legt afspraakverzoeken en afzeggingen vast.'],
        ['q' => 'Geeft hij medisch advies?',                                     'a' => 'Nee, nooit. Bij elke vraag over klachten of behandelingen zegt hij dat de acupuncturist dat graag bespreekt en noteert hij een terugbelverzoek. Dat staat hard in zijn opdracht.'],
        ['q' => 'Wat zegt hij over vergoeding?',                                 'a' => 'Wat jij aanlevert: dat het via de aanvullende verzekering loopt, bij welke beroepsvereniging je bent aangesloten en dat de patiënt het bedrag in de polis vindt. Geen toezeggingen over euro\'s.'],
        ['q' => 'Hoe zit het met de privacy van patiënten?',                     'a' => 'Hij bewaart naam, nummer en het verzoek; geen klachten, geen medische gegevens, geen opnames. De samenvattingen staan alleen in jouw portaal en mail. We sluiten een verwerkersovereenkomst.'],
        ['q' => 'Kan hij afspraken inplannen in mijn agenda?',                    'a' => 'Bewust niet. Hij noteert wanneer de patiënt kan; jij bevestigt de tijd. Zo houd jij de regie over je behandelagenda.'],
    ],

    'verder' => [
        ['facet' => 'website',        't' => 'Website voor acupuncturisten',    'b' => 'Tarieven, vergoeding en een afspraakknop op je site, zodat de assistent minder hoeft uit te leggen.'],
        ['facet' => 'automatisering', 't' => 'Verzoeken automatisch verwerken', 'b' => 'Een afspraakverzoek van de assistent komt op dezelfde lijst als een aanvraag via je website.'],
        ['facet' => 'klantenportaal', 't' => 'Patiëntenportaal',                 'b' => 'Patiënten zien hun afspraken en zeggen af volgens jouw regels, zonder te bellen.'],
    ],

    'cta_titel' => 'Vraag een demo aan met jouw tarieven',
    'cta_tekst' => 'Plan een gesprek van een half uur; dan richten we een proefversie in met jouw tarieven en vergoedingsregels en bel je zelf alsof je een nieuwe patiënt bent.',
];
