<?php

/**
 * Inhoud van /ai-telefonie-autogarage op jouw-autogarage-website.nl (16-09-2026).
 * Alleen het garage-eigen deel; de rest komt uit config/telefonie_basis.php.
 *
 * demo_nummer: 088 254 5160 is de OPENBARE garagedemo "Autobedrijf De Wissel" (verzonnen,
 * Hilversum) op de telefoniemachine (KANALEN 31882545160, beleid/garage/). De kaartjes
 * hieronder zijn de vaste voertuigen uit scripts/_aitest-garage-tabellen.php in V3; een
 * gesprek verandert niets aan die auto's, dus de demo blijft kloppen. Identificatie aan de
 * telefoon = kenteken + achternaam.
 */

return [
    'demo_nummer'    => '088 254 5160',
    'demo_naam'      => 'Autobedrijf De Wissel',
    'demo_noot'      => 'Het demonummer is Autobedrijf De Wissel: een garage die niet bestaat, zodat je vrij kunt vragen wat je wilt. Je sluit niets af door te bellen.',
    'demo_titel'     => 'Bel Autobedrijf De Wissel op 088 254 5160',
    'demo_lead'      => 'De Wissel bestaat niet, de werkplaats wel. Bel met een van deze kentekens en de bijbehorende achternaam en vraag of de auto klaar is, wanneer de APK verloopt of hoe laat de afspraak is. Er wordt niets gerepareerd en niets afgerekend.',
    'demo_kaartjes' => [
        ['kop' => '12-KLM-3 · De Wit',    'hoor' => 'Grote onderhoudsbeurt en APK klaar, € 386,40. Hij zegt dat de Golf vandaag tot half zes opgehaald kan worden.'],
        ['kop' => '7-XRP-88 · Jansen',    'hoor' => 'Start slecht, wacht op een startmotor. Hij zegt dat het onderdeel besteld is en wanneer het verwacht wordt.'],
        ['kop' => 'KP-482-T · El Amrani', 'hoor' => 'Niets in de werkplaats, APK verloopt over twaalf dagen. Hij biedt aan een afspraakverzoek te noteren.'],
        ['kop' => '3-VBH-21 · Bakker',    'hoor' => 'Onderhoudsbeurt morgen om acht uur. Hij bevestigt de tijd en wat er op de planning staat.'],
    ],
    'demo_probeer' => [
        'Vraag naar een kenteken zonder de juiste achternaam: hij geeft niets prijs.',
        'Vraag wat een APK kost en of je een leenauto kunt krijgen.',
        'Bel buiten openingstijden: hij verbindt niet door maar legt je verzoek vast.',
        'Vraag of hij de auto morgen kan inplannen: hij noteert het als verzoek, de garage bevestigt.',
    ],
    'kantoor_nummer' => '088-2545101',
    'infoblad'       => 'garage',           // config/telefonie_infobladen.php, knop naast het demonummer

    // CTA op de homepage (channels/partials/telefonie-home-cta): een telefoonscherm waarop
    // de assistent opneemt en dit gesprek zich uittikt. 'b' = beller, 'a' = assistent.
    'home_cta' => [
        'kicker'      => 'Nieuw: AI-telefonie voor autogarages',
        'kop'         => 'De telefoon gaat. Niemand hoeft onder de brug vandaan.',
        'lead'        => 'Een digitale assistent die opneemt met de naam van je garage, op kenteken en achternaam zegt of de auto klaar is, wat een APK kost en wanneer de keuring verloopt, en een afspraakverzoek noteert. Bij een technische vraag verbindt hij door. Bel de demo en vraag of de Golf al klaar is.',
        'bel_label'   => 'Bel de demo',
        'meer_label'  => 'Zo werkt het voor garages',
        'noot'        => 'Autobedrijf De Wissel bestaat niet; er wordt niets gerepareerd en niets afgerekend. Vanaf € 89 per maand.',
        'toestel_naam'=> 'Autobedrijf De Wissel',
        'toestel_sub' => 'Inkomend gesprek · de assistent neemt op',
        'tijd'        => '08:03',
        'gesprek' => [
            ['b', 'Is mijn auto al klaar? Kenteken 12-KLM-3.'],
            ['a', 'Dat kijk ik na. Mag ik uw achternaam?'],
            ['b', 'De Wit.'],
            ['a', 'Dank u, mevrouw De Wit. De grote beurt en de APK zijn klaar; het bedrag is € 386,40.'],
            ['b', 'Tot hoe laat kan ik hem ophalen?'],
            ['a', 'Vandaag tot half zes. Betalen kan aan de balie met pin.'],
        ],
    ],

    'woorden' => [
        'bedrijf'   => 'je garage',
        'bedrijven' => 'autogarages',
        'klant'     => 'klant',
        'klanten'   => 'klanten',
        'gegevens'  => 'openingstijden, diensten, tarieven voor APK en beurten, leenauto-regels en de werkplaatsplanning als je die wilt koppelen',
    ],

    'seo_titel' => 'AI telefonie voor autogarages | AI telefoonassistent die opneemt als jij onder de brug staat',
    'hero' => [
        'eyebrow' => 'AI-telefonie voor autogarages',
        'title'   => 'AI-telefonie voor je garage: de telefoon wordt opgenomen, ook als iedereen in de werkplaats staat',
        'sub'     => 'Een telefonische assistent die opneemt met de naam van je garage, zegt of een auto klaar is, wanneer de APK verloopt, wat een beurt kost, een afspraakverzoek noteert en doorverbindt als het moet. Tijdens de ochtendpiek, onder de brug en buiten openingstijden.',
        'alt'     => 'De telefonische assistent van een autogarage',
        'usps'    => [
            'Neemt altijd op, ook als de balie leeg is',
            '"Is mijn auto klaar?" beantwoord op kenteken en achternaam',
            'Antwoordt uit jouw eigen gegevens, niet uit het internet',
            'Na elk gesprek een samenvatting per mail',
        ],
    ],

    'wanneer_titel' => 'Wanneer de telefoon het meeste stoort',
    'wanneer' => [
        ['t' => 'Acht uur \'s ochtends',        'b' => 'Vijf klanten brengen hun auto, de telefoon gaat, en de monteur die opneemt staat met vieze handen aan de balie. Elk gesprek kost twee mensen tijd.'],
        ['t' => '"Is mijn auto al klaar?"',     'b' => 'De helft van de telefoontjes. Het antwoord staat op de werkorder, maar iemand moet ervoor uit de put komen.'],
        ['t' => 'APK-herinnering',              'b' => 'Wie een brief van de RDW krijgt belt de garage. Buiten openingstijden krijgt hij voicemail en belt hij de volgende.'],
        ['t' => 'Onderdeel besteld, klant belt', 'b' => 'Drie keer per dag dezelfde vraag over dezelfde startmotor. De assistent zegt wat de status is en belooft niets wat de leverancier niet waarmaakt.'],
    ],

    'gesprekken_titel' => 'Zo klinkt dat, in gewoon Nederlands',
    'gesprekken_lead'  => 'De antwoorden hieronder komen uit de gegevens die de garage zelf heeft aangeleverd. Zo praat de assistent ook over jouw garage: alleen wat jij hebt doorgegeven.',
    'gesprekken' => [
        ['vraag' => 'Is mijn auto al klaar? Kenteken 12-KLM-3.',              'antwoord' => 'Mag ik uw achternaam? Dank u, mevrouw De Wit. De grote beurt en de APK zijn klaar, het bedrag is € 386,40 en u kunt de auto vandaag tot half zes ophalen.'],
        ['vraag' => 'Wat kost een APK bij jullie?',                             'antwoord' => 'Een APK kost € 49,50 inclusief afmelden bij de RDW. Reparaties die eruit komen, doen we alleen na overleg met u. Wilt u dat ik een afspraakverzoek noteer?'],
        ['vraag' => 'Kan ik morgen langskomen voor een piepend geluid bij het remmen?', 'antwoord' => 'Ik noteer een afspraakverzoek voor morgen: remmen controleren, kenteken 3-VBH-21, op naam van Bakker. De werkplaats bevestigt de tijd nog per telefoon of sms.'],
        ['vraag' => 'Hebben jullie een leenauto?',                             'antwoord' => 'Ja, bij een beurt of reparatie kunt u een leenauto vragen voor € 10 per dag, zolang er een beschikbaar is. Zal ik dat bij uw verzoek zetten?'],
        ['vraag' => 'Wanneer verloopt mijn APK?',                              'antwoord' => 'Op kenteken KP-482-T en de naam El Amrani zie ik dat de APK over twaalf dagen verloopt. Wilt u dat ik een afspraakverzoek noteer?'],
        ['vraag' => 'Mijn motorlampje brandt, kan ik doorrijden?',              'antwoord' => 'Daar wil ik niets verkeerds over zeggen zonder dat een monteur de auto ziet. Ik verbind u door met de werkplaats, of ik noteer dat u wordt teruggebeld. Wat heeft u liever?'],
    ],

    'kan_titel' => 'Wat de assistent afhandelt',
    'kan' => [
        ['t' => '"Is mijn auto klaar?"',            'b' => 'Op kenteken en achternaam zegt hij wat de status is, wat er gedaan is en wat het kost, uit jouw werkplaatssysteem als dat gekoppeld is.'],
        ['t' => 'APK, beurten en tarieven',          'b' => 'Wat een APK, kleine of grote beurt kost, wat erbij zit, en of er een leenauto is. Uit jouw prijslijst.'],
        ['t' => 'Afspraakverzoeken',                 'b' => 'Kenteken, klacht, wanneer het uitkomt. Hij leest het terug en de werkplaats bevestigt de tijd.'],
        ['t' => 'Onderdelen en status',              'b' => 'Onderdeel besteld, wacht op leverancier, klaar voor montage: hij zegt wat erin staat en belooft geen datum die er niet staat.'],
        ['t' => 'Dagberichten',                      'b' => 'Vandaag geen APK meer, brug in storing, vrijdag om drie uur dicht: één bericht van jou en de assistent zegt het tegen iedere beller.'],
    ],

    'niet' => [
        'Een werkplaatsafspraak inplannen zonder dat jij hem bevestigt. Hij noteert het verzoek; jij bepaalt de tijd.',
        'Technisch advies geven over een klacht. "Kan ik doorrijden met dit lampje?" gaat altijd naar een monteur.',
        'Iets zeggen over een auto zonder kenteken én achternaam. Nummerweergave is te vervalsen; die combinatie niet.',
    ],

    'rekenhulp' => [
        'standaard' => [
            'per_dag'         => 25,
            'dagen'           => 5,
            'minuten'         => 1.5,
            'uurloon'         => 40,
            'oppakken'        => 4,
            'gemist_per_week' => 6,
            'bestelling_pct'  => 25,
            'bestelwaarde'    => 250,
            'afhandel_pct'    => 70,
        ],
        'teksten' => [
            'lead'         => 'Een rekenvoorbeeld, geen belofte. Zet de schuiven op jouw garage en zie wat de telefoon je nu per maand kost aan monteurs die uit de put komen, en welke omzet er in gemiste telefoontjes zit.',
            'deel_label'   => 'Deel daarvan dat een beurt of reparatie was geworden',
            'waarde_label' => 'Gemiddelde werkorder die je zo misloopt',
            'uren_tegel'   => 'per maand niet meer aan de telefoon of uit de werkplaats gehaald',
            'tijd_tegel'   => 'aan monteursuren die weer naar de brug gaan',
        ],
    ],

    'faq_titel' => 'Wat garagehouders ons vragen over AI-telefonie',
    'faq' => [
        ['q' => 'Kan de assistent de telefoon opnemen voor mijn garage?',    'a' => 'Ja. Hij neemt op met de naam van je garage, beantwoordt vragen over APK, beurten, tarieven en openingstijden, en legt afspraak- en terugbelverzoeken vast.'],
        ['q' => 'Kan hij zeggen of een auto klaar is?',                       'a' => 'Ja, op kenteken en achternaam. Met een koppeling op je werkplaatssysteem leest hij de status live; zonder koppeling werkt hij met een lijst die jij bijhoudt of legt hij de vraag vast voor de werkplaats.'],
        ['q' => 'Kan hij werkplaatsafspraken maken?',                         'a' => 'Hij noteert een afspraakverzoek met kenteken, klacht en voorkeursmoment. Jij bevestigt de tijd. Zo blijft de planning van de werkplaats van de werkplaats.'],
        ['q' => 'Wat zegt hij bij een technische vraag?',                     'a' => 'Niets wat een monteur zou moeten zeggen. Bij "kan ik doorrijden" of "wat is dat geluid" verbindt hij door of noteert hij een terugbelverzoek.'],
        ['q' => 'Werkt hij ook voor een bandenservice of schadeherstel?',     'a' => 'Ja, de kennisbank is per bedrijf. Doe je geen schade, dan zegt hij dat en verwijst hij door naar wie jij opgeeft.'],
    ],

    'verder' => [
        ['facet' => 'website',        't' => 'Website voor autogarages',       'b' => 'APK-prijs, openingstijden en een afspraakknop op je site, zodat de assistent minder hoeft uit te leggen.'],
        ['facet' => 'automatisering', 't' => 'Verzoeken automatisch verwerken', 'b' => 'Een afspraakverzoek van de assistent komt op dezelfde werkplaatsplanning als een aanvraag via je website.'],
        ['facet' => 'klantenportaal', 't' => 'Klantenportaal voor leasemaatschappijen', 'b' => 'Zakelijke klanten met tien auto\'s bekijken zelf welke klaar staan.'],
    ],

    'cta_titel' => 'Hoor het zelf, of vraag een demo aan',
    'cta_tekst' => 'Bel het demonummer en vraag of de auto met kenteken 12-KLM-3 klaar is. Of plan een gesprek van een half uur; dan richten we een proefversie in met jouw tarieven en openingstijden.',
];
