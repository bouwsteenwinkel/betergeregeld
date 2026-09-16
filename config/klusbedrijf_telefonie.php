<?php

/**
 * Inhoud van /ai-telefonie-klusbedrijf op jouw-klusbedrijf-website.nl (16-09-2026).
 * Alleen het klusbedrijf-eigen deel; de rest komt uit config/telefonie_basis.php.
 * Een klusser is meestal alleen: elke gemiste oproep is een gemiste klus, en elke
 * opgenomen oproep een onderbreking van de klus waar hij staat.
 */

return [
    'demo_nummer'    => '',
    'kantoor_nummer' => '088-2545101',

    'woorden' => [
        'bedrijf'   => 'je klusbedrijf',
        'bedrijven' => 'klusbedrijven',
        'klant'     => 'klant',
        'klanten'   => 'klanten',
        'gegevens'  => 'werkgebied, wat je wel en niet doet, uurtarief en voorrijkosten, wanneer je bereikbaar bent en hoe ver vooruit je vol zit',
    ],

    'seo_titel' => 'AI telefonie voor klusbedrijven | AI telefoonassistent die opneemt als jij op de trap staat',
    'hero' => [
        'eyebrow' => 'AI-telefonie voor klusbedrijven',
        'title'   => 'AI-telefonie voor je klusbedrijf: de telefoon wordt opgenomen, ook als jij op de trap staat',
        'sub'     => 'Een telefonische assistent die opneemt met de naam van je bedrijf, zegt of je in die plaats komt en wat je per uur rekent, een klus netjes uitvraagt en als verzoek klaarzet, en spoed doorverbindt naar je mobiel. Op de klus, in de bus en \'s avonds.',
        'alt'     => 'De telefonische assistent van een klusbedrijf',
        'usps'    => [
            'Neemt altijd op, ook als je met een boormachine in je hand staat',
            'Elke klus komt compleet binnen: wat, waar, wanneer, foto\'s gevraagd',
            'Uurtarief en voorrijkosten uit jouw eigen lijst',
            'Na elk gesprek een samenvatting per mail',
        ],
    ],

    'wanneer_titel' => 'Wanneer de telefoon het meeste stoort',
    'wanneer' => [
        ['t' => 'Je bent alleen',                   'b' => 'Geen kantoor, geen collega: elke oproep die je niet opneemt is een klant die de volgende klusser belt. En elke oproep die je wél opneemt kost een kwartier op de klus waar je staat.'],
        ['t' => 'Terugbellen in de bus',            'b' => 'Aan het eind van de dag vijf gemiste oproepen terugbellen. Twee nemen niet meer op, één heeft al iemand, en het is inmiddels zeven uur.'],
        ['t' => '"Wat kost een uur?"',              'b' => 'De helft van de telefoontjes is een prijsvraag. Het antwoord is elke keer hetzelfde; de onderbreking ook.'],
        ['t' => 'Klussen die je niet doet',         'b' => '"Kunnen jullie ook een cv-ketel vervangen?" Nee, en dat mag de assistent zeggen, met de naam van de installateur die je aanbeveelt.'],
    ],

    'gesprekken_titel' => 'Zo klinkt dat, in gewoon Nederlands',
    'gesprekken_lead'  => 'De antwoorden hieronder komen uit de gegevens die het klusbedrijf zelf heeft aangeleverd. Zo praat de assistent ook over jouw bedrijf: alleen wat jij hebt doorgegeven.',
    'gesprekken' => [
        ['vraag' => 'Ik wil een schutting laten plaatsen en een deur afhangen, wat kost dat?', 'antwoord' => 'We rekenen € 52 per uur en € 25 voorrijkosten, exclusief materiaal en btw. Een schutting van tien meter is meestal een dag werk. Wilt u dat ik een klusverzoek noteer? Dan mailt u foto\'s en krijgt u vooraf een inschatting.'],
        ['vraag' => 'Komen jullie ook in Leusden?',                                             'antwoord' => 'Ja, Leusden zit in ons werkgebied. Waar kan ik u mee helpen?'],
        ['vraag' => 'Wanneer kunnen jullie?',                                                    'antwoord' => 'We zitten tot eind volgende maand vol; daarna is er ruimte. Ik noteer uw klus en u hoort binnen twee werkdagen welke week kan. Klopt het nummer 06-1234 5678?'],
        ['vraag' => 'Mijn wc loopt over, kunnen jullie nu komen?',                              'antwoord' => 'Dat is spoed. Ik verbind u door met Bas op zijn mobiel. Neemt hij niet op, dan noteer ik uw adres en belt hij u zo snel mogelijk terug.'],
        ['vraag' => 'Kunnen jullie ook een cv-ketel vervangen?',                                 'antwoord' => 'Dat doen we niet; daarvoor verwijzen we naar Installatiebedrijf Jansen in Amersfoort. Wilt u hun nummer?'],
        ['vraag' => 'Ik wil mijn hele woonkamer laten stuken en schilderen.',                    'antwoord' => 'Dan noteer ik een klusverzoek: woonkamer stuken en schilderen, ongeveer 35 vierkante meter, Populierenlaan 8 in Leusden, op naam van Kok. Bas komt eerst kijken en maakt dan een vaste prijs. U hoort deze week wanneer.'],
    ],

    'kan_titel' => 'Wat de assistent afhandelt',
    'kan' => [
        ['t' => 'Klus uitvragen',                  'b' => 'Wat, waar, hoe groot, wanneer het uitkomt, en de vraag om foto\'s te mailen. Compleet in je mail, klaar om een prijs op te maken.'],
        ['t' => 'Werkgebied en wat je doet',       'b' => 'In welke plaatsen je komt, welke klussen je doet en wat je liever doorverwijst, met de naam die jij opgeeft.'],
        ['t' => 'Uurtarief en voorrijkosten',      'b' => 'Precies zoals jij ze doorgeeft, met de opmerking dat een vaste prijs na foto\'s of een bezoek komt.'],
        ['t' => 'Spoed naar je mobiel',            'b' => 'Overlopende wc, kapotte deur na inbraak: doorverbinden naar het nummer dat jij opgeeft, of een terugbelverzoek als je niet opneemt.'],
        ['t' => 'Dagberichten',                    'b' => 'Vol tot eind van de maand, deze week op vakantie, morgen niet bereikbaar: één bericht van jou en de assistent zegt het tegen iedere beller.'],
    ],

    'niet' => [
        'Een datum of een vaste prijs toezeggen. Hij legt het verzoek vast; jij bepaalt wanneer en voor hoeveel.',
        'Klussen aannemen die jij niet doet. Hij zegt dat eerlijk en verwijst door als jij een naam hebt opgegeven.',
        'Doe-het-zelf-advies geven. "Kan ik die muur zelf doorbreken?" gaat naar jou.',
    ],

    'rekenhulp' => [
        'standaard' => [
            'per_dag'         => 8,
            'dagen'           => 5,
            'minuten'         => 2,
            'uurloon'         => 45,
            'oppakken'        => 5,
            'gemist_per_week' => 5,
            'bestelling_pct'  => 25,
            'bestelwaarde'    => 350,
            'afhandel_pct'    => 65,
        ],
        'teksten' => [
            'lead'         => 'Een rekenvoorbeeld, geen belofte. Zet de schuiven op jouw bedrijf en zie wat de telefoon je nu per maand kost aan onderbroken klussen, en welke omzet er in gemiste telefoontjes zit. Wie alleen werkt, mist per definitie de oproepen die tijdens een klus komen.',
            'deel_label'   => 'Deel daarvan dat een klus was geworden',
            'waarde_label' => 'Gemiddelde klus die je zo misloopt',
            'uren_tegel'   => 'per maand niet meer aan de telefoon of van de trap gehaald',
            'tijd_tegel'   => 'aan uren die weer naar klussen gaan in plaats van bellen',
        ],
    ],

    'faq_titel' => 'Wat klussers ons vragen over AI-telefonie',
    'faq' => [
        ['q' => 'Kan de assistent de telefoon opnemen voor mijn klusbedrijf?',  'a' => 'Ja. Hij neemt op met de naam van je bedrijf, kent je werkgebied en tarieven, vraagt een klus compleet uit en legt hem vast als verzoek. Spoed verbindt hij door naar je mobiel.'],
        ['q' => 'Ik werk alleen. Heeft dit dan zin?',                             'a' => 'Juist dan. Elke oproep tijdens een klus is nu een gemiste oproep. De assistent neemt op, vraagt uit en jij belt \'s avonds terug met een inschatting in plaats van met de vraag "waar ging het over?".'],
        ['q' => 'Noemt hij mijn uurtarief?',                                      'a' => 'Als jij dat wilt, ja: uurtarief, voorrijkosten en de opmerking dat een vaste prijs na foto\'s of een bezoek komt. Wil je liever geen prijzen aan de telefoon, dan legt hij alleen het verzoek vast.'],
        ['q' => 'Kan hij zeggen wanneer ik kan?',                                 'a' => 'Hij zegt wat jij hebt opgegeven, bijvoorbeeld "vol tot eind volgende maand". Een echte datum bevestig jij zelf.'],
        ['q' => 'Wat doet hij bij spoed?',                                        'a' => 'Doorverbinden naar het nummer dat jij opgeeft. Neem je niet op, dan noteert hij adres en nummer en zegt hij dat je zo snel mogelijk terugbelt. Buiten de tijden die jij opgeeft verbindt hij niet door.'],
    ],

    'verder' => [
        ['facet' => 'website',        't' => 'Website voor klusbedrijven',       'b' => 'Werkgebied, tarieven en een aanvraagformulier met fotoupload, zodat de assistent minder hoeft uit te vragen.'],
        ['facet' => 'automatisering', 't' => 'Klusverzoeken automatisch verwerken', 'b' => 'Een klus van de assistent komt op dezelfde lijst als een aanvraag via je website.'],
        ['facet' => 'klantenportaal', 't' => 'Klantenportaal voor vaste klanten', 'b' => 'Verhuurders en VvE\'s die nu bellen om een klus te melden, zetten hem zelf op de lijst.'],
    ],

    'cta_titel' => 'Vraag een demo aan met jouw werkgebied',
    'cta_tekst' => 'Plan een gesprek van een half uur; dan richten we een proefversie in met jouw werkgebied en tarieven en bel je zelf alsof je een schutting wilt.',
];
