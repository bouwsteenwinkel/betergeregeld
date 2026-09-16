<?php

/**
 * Inhoud van /ai-telefonie-dietist op jouw-dietist-website.nl (16-09-2026).
 * Alleen het praktijk-eigen deel; de rest komt uit config/telefonie_basis.php.
 * Een diëtist zit in consult en neemt niet op. De assistent doet het praktische deel
 * (vergoeding uit de basisverzekering, verwijzing, tarieven, afspraakverzoek) en geeft
 * NOOIT voedingsadvies.
 */

return [
    'demo_nummer'    => '',
    'kantoor_nummer' => '088-2545101',

    'woorden' => [
        'bedrijf'   => 'je praktijk',
        'bedrijven' => 'diëtisten',
        'klant'     => 'cliënt',
        'klanten'   => 'cliënten',
        'gegevens'  => 'tarieven, hoe vergoeding uit de basis- en aanvullende verzekering bij jou werkt, of een verwijzing nodig is, praktijktijden en locaties, en de afzegregels',
    ],

    'seo_titel' => 'AI telefonie voor diëtisten | AI telefoonassistent die opneemt terwijl jij in consult zit',
    'hero' => [
        'eyebrow' => 'AI-telefonie voor diëtisten',
        'title'   => 'AI-telefonie voor je praktijk: de telefoon wordt opgenomen, ook als jij in consult zit',
        'sub'     => 'Een telefonische assistent die opneemt met de naam van je praktijk, uitlegt hoe de drie uur uit de basisverzekering werken, of een verwijzing nodig is en wat een consult kost, een afspraakverzoek of afzegging vastlegt, en elke vraag over eten en klachten bij jou neerlegt. Tijdens het consult, tussen twee cliënten en \'s avonds.',
        'alt'     => 'De telefonische assistent van een diëtistenpraktijk',
        'usps'    => [
            'Neemt altijd op, ook als je in consult zit',
            'Vergoeding en verwijzing helder uitgelegd, uit jouw gegevens',
            'Geen voedingsadvies aan de telefoon, wel een terugbelverzoek',
            'Na elk gesprek een samenvatting per mail',
        ],
    ],

    'wanneer_titel' => 'Wanneer de telefoon het meeste stoort',
    'wanneer' => [
        ['t' => 'Tijdens het consult',              'b' => 'Een cliënt vertelt iets kwetsbaars en de telefoon gaat. Je neemt niet op, terecht. De nieuwe cliënt aan de andere kant krijgt voicemail en belt een collega.'],
        ['t' => '"Wordt het vergoed?"',             'b' => 'Drie uur uit de basisverzekering, eigen risico, aanvullend, verwijzing van de huisarts: het is elke keer dezelfde uitleg en hij kost vijf minuten.'],
        ['t' => 'Verwijzingen van de huisarts',     'b' => 'De praktijkassistente van de huisarts belt om een cliënt aan te melden. Dat moet vandaag nog een plek krijgen, niet in de voicemail.'],
        ['t' => 'Afzeggen en verzetten',            'b' => 'Ziek, file, kind ziek. De assistent noteert het, noemt jouw afzegregel en jij ziet het na het consult.'],
    ],

    'gesprekken_titel' => 'Zo klinkt dat, in gewoon Nederlands',
    'gesprekken_lead'  => 'De antwoorden hieronder komen uit de gegevens die de praktijk zelf heeft aangeleverd. Zo praat de assistent ook over jouw praktijk: alleen wat jij hebt doorgegeven.',
    'gesprekken' => [
        ['vraag' => 'Wordt een diëtist vergoed?',                                        'antwoord' => 'Ja, drie uur per jaar uit de basisverzekering; die vallen wel onder het eigen risico. Sommige aanvullende pakketten vergoeden meer uren. Een verwijzing van de huisarts is bij ons niet verplicht, maar sommige verzekeraars vragen er wel om; dat staat in uw polis.'],
        ['vraag' => 'Wat kost een consult als ik het zelf betaal?',                      'antwoord' => 'Een intake van 60 minuten kost € 95 en een vervolgconsult van 30 minuten € 47,50. Zal ik een afspraakverzoek noteren?'],
        ['vraag' => 'Ik wil een afspraak maken, ik heb een verwijzing voor diabetes.',    'antwoord' => 'Graag. Ik noteer uw naam, geboortedatum, nummer en op welke dagdelen u kunt; Laura belt u vandaag nog terug met een tijd. Neemt u de verwijsbrief mee naar het eerste consult?'],
        ['vraag' => 'Mag ik met diabetes fruit eten?',                                    'antwoord' => 'Dat is precies waar het consult voor is; aan de telefoon wil ik daar niets over zeggen. Ik noteer een terugbelverzoek, of zal ik meteen een afspraakverzoek noteren?'],
        ['vraag' => 'Ik bel namens huisartsenpraktijk De Brink, we willen een patiënt aanmelden.', 'antwoord' => 'Fijn. Ik noteer de naam en geboortedatum van de patiënt, de reden van verwijzing en het nummer van de praktijk. Laura neemt vandaag contact op met de patiënt.'],
        ['vraag' => 'Doen jullie ook consulten via beeldbellen?',                         'antwoord' => 'Ja, vervolgconsulten kunnen via beeldbellen; de intake doen we in de praktijk. Wilt u dat ik dat bij uw verzoek noteer?'],
    ],

    'kan_titel' => 'Wat de assistent afhandelt',
    'kan' => [
        ['t' => 'Vergoeding en verwijzing',          'b' => 'De drie uur uit de basisverzekering, eigen risico, aanvullend, wel of geen verwijzing. Uit jouw uitleg, zonder bedragen toe te zeggen.'],
        ['t' => 'Afspraakverzoeken',                  'b' => 'Nieuwe of bestaande cliënt, reden in de woorden van de cliënt, voorkeursdagdelen, praktijk of beeldbellen. Jij plant in en bevestigt.'],
        ['t' => 'Aanmeldingen van verwijzers',        'b' => 'Huisarts, POH of specialist: naam en geboortedatum van de patiënt, reden, contactgegevens. Compleet in je mail.'],
        ['t' => 'Afzeggingen',                        'b' => 'Wie, welke afspraak, waarom. Hij noemt jouw afzegregel en laat de beslissing bij jou.'],
        ['t' => 'Praktische vragen',                  'b' => 'Tarieven, locaties, hoe lang een intake duurt, wat meenemen, parkeren. Uit jouw gegevens.'],
        ['t' => 'Dagberichten',                       'b' => 'Vandaag alleen beeldbellen, praktijk dicht, wachttijd nieuwe cliënten drie weken: één bericht van jou en de assistent zegt het tegen iedere beller.'],
    ],

    'niet' => [
        'Voedingsadvies geven of iets zeggen over klachten, gewicht of medicatie. Elke inhoudelijke vraag wordt een terugbelverzoek of afspraakverzoek.',
        'Een vergoedingsbedrag toezeggen. Hij legt uit hoe het werkt en verwijst naar de polis.',
        'Afspraken inplannen of verzetten. Hij legt het verzoek vast; jij bevestigt.',
    ],

    'rekenhulp' => [
        'standaard' => [
            'per_dag'         => 8,
            'dagen'           => 5,
            'minuten'         => 3,
            'uurloon'         => 40,
            'oppakken'        => 2,
            'gemist_per_week' => 6,
            'bestelling_pct'  => 20,
            'bestelwaarde'    => 250,
            'afhandel_pct'    => 70,
        ],
        'teksten' => [
            'lead'         => 'Een rekenvoorbeeld, geen belofte. Zet de schuiven op jouw praktijk en zie welke omzet er in gemiste telefoontjes zit. Reken een nieuwe cliënt als een traject van intake plus een paar vervolgconsulten.',
            'deel_label'   => 'Deel daarvan dat een nieuwe cliënt was',
            'waarde_label' => 'Wat een behandeltraject je gemiddeld oplevert',
            'uren_tegel'   => 'per maand niet meer aan terugbellen tussen consulten door',
            'tijd_tegel'   => 'aan uren die weer naar consulten gaan in plaats van bellen',
        ],
    ],

    'faq_titel' => 'Wat diëtisten ons vragen over AI-telefonie',
    'faq' => [
        ['q' => 'Kan de assistent de telefoon opnemen voor mijn praktijk?',       'a' => 'Ja. Hij neemt op met de naam van je praktijk, legt vergoeding en verwijzing uit zoals jij dat aanlevert, noteert afspraakverzoeken en afzeggingen en neemt aanmeldingen van verwijzers aan.'],
        ['q' => 'Geeft hij voedingsadvies?',                                      'a' => 'Nee, nooit. Bij elke vraag over eten, klachten of gewicht zegt hij dat dat in het consult thuishoort en noteert hij een terugbel- of afspraakverzoek. Dat staat hard in zijn opdracht.'],
        ['q' => 'Wat zegt hij over vergoeding?',                                  'a' => 'Wat jij aanlevert: de drie uur uit de basisverzekering, het eigen risico, aanvullende pakketten en of een verwijzing nodig is. Geen toezeggingen over bedragen; daarvoor verwijst hij naar de polis.'],
        ['q' => 'Hoe zit het met de privacy van cliënten?',                       'a' => 'Hij bewaart naam, nummer en het verzoek; geen klachten, geen medische gegevens, geen opnames. De samenvattingen staan alleen in jouw portaal en mail. We sluiten een verwerkersovereenkomst.'],
        ['q' => 'Kan hij aanmeldingen van huisartsen aannemen?',                   'a' => 'Ja. Naam en geboortedatum van de patiënt, reden van verwijzing en de contactgegevens van de praktijk. Jij neemt daarna zelf contact op met de patiënt.'],
    ],

    'verder' => [
        ['facet' => 'website',        't' => 'Website voor diëtisten',          'b' => 'Vergoeding, tarieven en een afspraakknop op je site, zodat de assistent minder hoeft uit te leggen.'],
        ['facet' => 'automatisering', 't' => 'Verzoeken automatisch verwerken', 'b' => 'Een afspraakverzoek of aanmelding van de assistent komt op dezelfde lijst als een aanvraag via je website.'],
        ['facet' => 'klantenportaal', 't' => 'Cliëntenportaal',                 'b' => 'Cliënten zien hun afspraken en zeggen af volgens jouw regels, zonder te bellen.'],
    ],

    'cta_titel' => 'Vraag een demo aan met jouw tarieven',
    'cta_tekst' => 'Plan een gesprek van een half uur; dan richten we een proefversie in met jouw tarieven en vergoedingsuitleg en bel je zelf alsof je een nieuwe cliënt bent.',
];
