<?php

/**
 * Inhoud van /ai-telefonie-rijschool op jouw-rijschool-website.nl (16-09-2026).
 * Alleen het rijschool-eigen deel; de rest komt uit config/telefonie_basis.php.
 * Een instructeur zit de hele dag naast een leerling en mag niet opnemen; de telefoon
 * is dus per definitie voicemail. Dat is precies waar de assistent het verschil maakt.
 */

return [
    'demo_nummer'    => '',
    'kantoor_nummer' => '088-2545101',

    'woorden' => [
        'bedrijf'   => 'je rijschool',
        'bedrijven' => 'rijscholen',
        'klant'     => 'leerling',
        'klanten'   => 'leerlingen',
        'gegevens'  => 'lespakketten en prijzen, lesauto\'s, werkgebied, hoe een proefles loopt, wachttijd voor nieuwe leerlingen en de regels rond afzeggen',
    ],

    'seo_titel' => 'AI telefonie voor rijscholen | AI telefoonassistent die opneemt terwijl jij lesgeeft',
    'hero' => [
        'eyebrow' => 'AI-telefonie voor rijscholen',
        'title'   => 'AI-telefonie voor je rijschool: de telefoon wordt opgenomen, ook als jij naast een leerling zit',
        'sub'     => 'Een telefonische assistent die opneemt met de naam van je rijschool, vertelt wat een pakket kost en hoe een proefles werkt, een verzoek voor een proefles vastlegt, een afzegging noteert volgens jouw regels en ouders van een leerling netjes te woord staat. Tijdens de les, tussen twee leerlingen en \'s avonds.',
        'alt'     => 'De telefonische assistent van een rijschool',
        'usps'    => [
            'Neemt altijd op, ook als je lesgeeft',
            'Proeflesverzoeken komen compleet binnen: naam, leeftijd, wanneer, hoe gevonden',
            'Pakketprijzen en afzegregels uit jouw eigen lijst',
            'Na elk gesprek een samenvatting per mail',
        ],
    ],

    'wanneer_titel' => 'Wanneer de telefoon het meeste stoort',
    'wanneer' => [
        ['t' => 'Tijdens de les',                    'b' => 'Je mag niet opnemen, dus je neemt niet op. Acht lessen per dag betekent acht uur onbereikbaar, precies wanneer nieuwe leerlingen bellen.'],
        ['t' => '"Wat kost een pakket?"',            'b' => 'De vraag van elke nieuwe leerling en elke ouder. Het antwoord staat op je site, maar mensen bellen liever. Nu krijgen ze voicemail en bellen ze de volgende rijschool.'],
        ['t' => 'Afzeggen op het laatste moment',    'b' => '"Ik ben ziek, kan de les van morgen niet doorgaan?" om elf uur \'s avonds. De assistent noteert het, zegt wat jouw afzegregel is, en jij ziet het \'s ochtends.'],
        ['t' => 'Ouders die willen weten hoe het gaat', 'b' => 'Een vader belt over de vorderingen. Dat is een gesprek voor jou, niet voor een assistent: hij noteert een terugbelverzoek en zegt wanneer je terugbelt.'],
    ],

    'gesprekken_titel' => 'Zo klinkt dat, in gewoon Nederlands',
    'gesprekken_lead'  => 'De antwoorden hieronder komen uit de gegevens die de rijschool zelf heeft aangeleverd. Zo praat de assistent ook over jouw rijschool: alleen wat jij hebt doorgegeven.',
    'gesprekken' => [
        ['vraag' => 'Wat kost het om bij jullie mijn rijbewijs te halen?',          'antwoord' => 'Een losse les van 60 minuten kost € 55. Het pakket van 30 lessen met examen kost € 1.795 en het pakket van 40 lessen € 2.295. Een proefles van 60 minuten kost € 35 en telt mee als u doorgaat. Zal ik een proefles voor u aanvragen?'],
        ['vraag' => 'Ik wil een proefles, wanneer kan dat?',                         'antwoord' => 'Nieuwe leerlingen kunnen meestal binnen twee weken terecht. Ik noteer uw naam, leeftijd, woonplaats en op welke dagen u kunt; Maarten belt u morgen om een tijd af te spreken. Klopt het nummer 06-1234 5678?'],
        ['vraag' => 'Ik ben ziek, ik kan de les van morgen niet halen.',              'antwoord' => 'Beterschap. Ik geef het door aan Maarten. Onze regel is dat afzeggen tot 24 uur van tevoren gratis is en daarna de les in rekening wordt gebracht; omdat dit ziekte is, beslist Maarten daar zelf over en hoort u dat van hem.'],
        ['vraag' => 'Hoe gaat het met de lessen van mijn zoon?',                      'antwoord' => 'Dat is een vraag voor de instructeur zelf; over vorderingen zeg ik niets. Ik noteer een terugbelverzoek; Maarten belt u vanavond na de laatste les. Mag ik uw naam en nummer?'],
        ['vraag' => 'Geven jullie ook les in een automaat?',                          'antwoord' => 'Ja, we hebben één automaat en twee schakelauto\'s. Let op: met een automaatrijbewijs mag u geen schakelauto rijden. Wilt u een proefles in de automaat?'],
        ['vraag' => 'Kan ik het examen bij jullie aanvragen?',                        'antwoord' => 'Het examen vraagt de rijschool voor u aan bij het CBR zodra uw instructeur zegt dat u eraan toe bent. Ik kan een verzoek noteren dat u er graag over wilt overleggen.'],
    ],

    'kan_titel' => 'Wat de assistent afhandelt',
    'kan' => [
        ['t' => 'Pakketten en prijzen',            'b' => 'Losse les, pakketten, proefles, wat erin zit en wat niet. Uit jouw prijslijst.'],
        ['t' => 'Proeflesverzoeken',                'b' => 'Naam, leeftijd, woonplaats, automaat of schakel, beschikbare dagen. Jij belt terug met een tijd.'],
        ['t' => 'Afzeggingen',                      'b' => 'Wie, welke les, waarom. Hij noemt jouw afzegregel en laat de beslissing bij jou.'],
        ['t' => 'Praktische vragen',                'b' => 'Ophalen thuis of op school, lesduur, wachttijd, welke auto\'s, theorie wel of niet. Uit jouw gegevens.'],
        ['t' => 'Dagberichten',                     'b' => 'Vandaag geen lessen door gladheid, examens vertraagd, volgende week vakantie: één bericht van jou en de assistent zegt het tegen iedere beller.'],
    ],

    'niet' => [
        'Iets zeggen over de vorderingen of het examen van een leerling. Dat is een gesprek tussen instructeur en leerling (of ouder).',
        'Een les inplannen of verzetten. Hij legt het verzoek vast; jij bevestigt de tijd.',
        'Een afzegging kwijtschelden of in rekening brengen. Hij noemt de regel; jij beslist.',
    ],

    'rekenhulp' => [
        'standaard' => [
            'per_dag'         => 8,
            'dagen'           => 6,
            'minuten'         => 2,
            'uurloon'         => 35,
            'oppakken'        => 2,
            'gemist_per_week' => 4,
            'bestelling_pct'  => 10,
            'bestelwaarde'    => 1200,
            'afhandel_pct'    => 75,
        ],
        'teksten' => [
            'lead'         => 'Een rekenvoorbeeld, geen belofte. Zet de schuiven op jouw rijschool en zie welke omzet er in gemiste telefoontjes zit. Bij een rijschool gaat het niet om onderbrekingen (je neemt tijdens de les toch niet op) maar om de nieuwe leerling die voicemail kreeg en de volgende rijschool belde.',
            'deel_label'   => 'Deel daarvan dat een nieuwe leerling was',
            'waarde_label' => 'Gemiddeld lespakket van een nieuwe leerling',
            'uren_tegel'   => 'per maand niet meer aan terugbellen tussen de lessen',
            'tijd_tegel'   => 'aan uren die weer naar lessen gaan in plaats van bellen',
        ],
    ],

    'faq_titel' => 'Wat rijinstructeurs ons vragen over AI-telefonie',
    'faq' => [
        ['q' => 'Kan de assistent de telefoon opnemen voor mijn rijschool?',     'a' => 'Ja. Hij neemt op met de naam van je rijschool, noemt pakketten en prijzen, legt proeflesverzoeken en afzeggingen vast en zorgt dat jij na de les terugbelt met alles al genoteerd.'],
        ['q' => 'Kan hij lessen inplannen?',                                     'a' => 'Nee, bewust niet. Hij legt een verzoek vast met de dagen waarop de leerling kan; jij bevestigt de tijd. Zo blijft je lesrooster van jou.'],
        ['q' => 'Wat zegt hij tegen ouders?',                                    'a' => 'Alles over pakketten, prijzen en praktische zaken. Over de vorderingen of het examen van een leerling zegt hij niets; dat noteert hij als terugbelverzoek voor de instructeur.'],
        ['q' => 'Hoe gaat hij om met afzeggen?',                                 'a' => 'Hij noteert wie, welke les en waarom, noemt jouw afzegregel (bijvoorbeeld 24 uur) en laat de beslissing bij jou. Je ziet het direct in je mail en portaal.'],
        ['q' => 'Ik heb meerdere instructeurs. Kan dat?',                         'a' => 'Ja. Per instructeur leg je vast wie welke leerlingen heeft en naar welk nummer doorverbonden mag worden. Buiten lestijden noteert hij alleen.'],
    ],

    'verder' => [
        ['facet' => 'website',        't' => 'Website voor rijscholen',        'b' => 'Pakketten, prijzen en een proeflesknop op je site, zodat de assistent minder hoeft uit te leggen.'],
        ['facet' => 'automatisering', 't' => 'Verzoeken automatisch verwerken', 'b' => 'Een proeflesverzoek van de assistent komt op dezelfde lijst als een aanmelding via je website.'],
        ['facet' => 'klantenportaal', 't' => 'Leerlingenportaal',              'b' => 'Leerlingen zien zelf hun volgende lessen en zeggen af volgens jouw regels, zonder te bellen.'],
    ],

    'cta_titel' => 'Vraag een demo aan met jouw pakketten',
    'cta_tekst' => 'Plan een gesprek van een half uur; dan richten we een proefversie in met jouw pakketten en afzegregels en bel je zelf alsof je een proefles wilt.',
];
