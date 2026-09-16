<?php

/**
 * Inhoud van /ai-telefonie-uitlaat-remmen op jouw-uitlaat-remmen-website.nl (16-09-2026).
 * Alleen het vak-eigen deel; de rest komt uit config/telefonie_basis.php.
 *
 * demo_nummer: dezelfde openbare garagedemo als de autogarage (088 254 5160, Autobedrijf
 * De Wissel). Een uitlaat- en remmenspecialist is een garage met een smaller menu; de demo
 * laat precies zien wat er telt: status op kenteken, prijsvraag, afspraakverzoek.
 */

return [
    'demo_nummer'    => '088 254 5160',
    'demo_naam'      => 'Autobedrijf De Wissel',
    'demo_noot'      => 'Het demonummer is Autobedrijf De Wissel: een algemene garage die niet bestaat. Bij jou praat de assistent over uitlaten en remmen; de manier van werken is hetzelfde.',
    'demo_titel'     => 'Bel Autobedrijf De Wissel op 088 254 5160',
    'demo_lead'      => 'De demo is een algemene garage die niet bestaat. Bel met een van deze kentekens en achternamen en vraag of de auto klaar is of hoe laat de afspraak is. Er wordt niets gerepareerd en niets afgerekend.',
    'demo_kaartjes' => [
        ['kop' => '12-KLM-3 · De Wit',    'hoor' => 'Grote onderhoudsbeurt en APK klaar, € 386,40. Hij zegt dat de Golf vandaag tot half zes opgehaald kan worden.'],
        ['kop' => '7-XRP-88 · Jansen',    'hoor' => 'Start slecht, wacht op een startmotor. Hij zegt dat het onderdeel besteld is en wanneer het verwacht wordt.'],
        ['kop' => '3-VBH-21 · Bakker',    'hoor' => 'Onderhoudsbeurt morgen om acht uur. Hij bevestigt de tijd en wat er op de planning staat.'],
        ['kop' => 'Zonder achternaam',    'hoor' => 'Noem alleen een kenteken: hij vraagt om de achternaam en geeft anders niets prijs.'],
    ],
    'demo_probeer' => [
        'Vraag wat het kost om je remblokken te laten vervangen: hij noemt alleen wat de garage heeft opgegeven.',
        'Zeg dat je remmen piepen en vraag of dat gevaarlijk is: hij verbindt door of noteert een terugbelverzoek, hij oordeelt niet zelf.',
        'Bel buiten openingstijden: hij legt je verzoek vast en verbindt niet door.',
    ],
    'kantoor_nummer' => '088-2545101',

    'woorden' => [
        'bedrijf'   => 'je uitlaat- en remmenspecialist',
        'bedrijven' => 'uitlaat- en remmenspecialisten',
        'klant'     => 'klant',
        'klanten'   => 'klanten',
        'gegevens'  => 'openingstijden, wat je doet en niet doet, richtprijzen per klus, wachttijden en of de klant kan wachten',
    ],

    'seo_titel' => 'AI telefonie voor uitlaat- en remmenspecialisten | AI telefoonassistent die opneemt terwijl jij de auto op de brug hebt',
    'hero' => [
        'eyebrow' => 'AI-telefonie voor uitlaat- en remmenspecialisten',
        'title'   => 'AI-telefonie voor je uitlaat- en remmenspecialist: de telefoon wordt opgenomen, ook als de auto op de brug staat',
        'sub'     => 'Een telefonische assistent die opneemt met de naam van je bedrijf, zegt wat een uitlaat of set remblokken kost, of de klant kan wachten, of de auto klaar is, en een afspraakverzoek noteert. Tijdens de klus, in de ochtendpiek en buiten openingstijden.',
        'alt'     => 'De telefonische assistent van een uitlaat- en remmenspecialist',
        'usps'    => [
            'Neemt altijd op, ook als de sleutel in je hand zit',
            'Richtprijzen en wachttijden uit jouw eigen lijst',
            '"Is mijn auto klaar?" op kenteken en achternaam',
            'Na elk gesprek een samenvatting per mail',
        ],
    ],

    'wanneer_titel' => 'Wanneer de telefoon het meeste stoort',
    'wanneer' => [
        ['t' => '"Wat kost een nieuwe uitlaat?"',    'b' => 'Elke dag tien keer, en elke keer hetzelfde antwoord: het hangt van de auto af, maar een middendemper zit tussen dit en dat. De assistent zegt dat ook, uit jouw prijslijst.'],
        ['t' => 'Klant wil wachten',                 'b' => '"Kan ik erop wachten?" is een planningsvraag die nu de monteur onder de auto vandaan haalt. De assistent kent jouw regel: remblokken ja, een hele uitlaatlijn nee.'],
        ['t' => 'Remmen piepen, klant is bang',      'b' => 'Wie een piep hoort wil geruststelling. De assistent geeft die niet zelf, maar verbindt door of zorgt dat je terugbelt, zodat er niets verkeerds gezegd wordt.'],
        ['t' => 'Buiten openingstijden',             'b' => 'Wie \'s avonds een kapotte uitlaat heeft, belt drie garages. Wie opneemt en een afspraakverzoek noteert, heeft de klant morgen.'],
    ],

    'gesprekken_titel' => 'Zo klinkt dat, in gewoon Nederlands',
    'gesprekken_lead'  => 'De antwoorden hieronder komen uit de gegevens die de specialist zelf heeft aangeleverd. Zo praat de assistent ook over jouw bedrijf: alleen wat jij hebt doorgegeven.',
    'gesprekken' => [
        ['vraag' => 'Wat kost het om de remblokken voor te vervangen op een Golf uit 2017?', 'antwoord' => 'Remblokken vóór vervangen kost bij ons meestal tussen € 120 en € 160, inclusief montage en btw; de exacte prijs hangt van het type af. Wilt u dat ik een afspraakverzoek noteer? Dan krijgt u vooraf een vaste prijs.'],
        ['vraag' => 'Mijn uitlaat maakt lawaai, kan ik er vandaag mee langskomen?',    'antwoord' => 'Voor een controle kunt u vandaag langskomen tot vier uur; we kijken er dan meteen naar. Wilt u dat ik uw kenteken en naam noteer, zodat de werkplaats weet dat u komt?'],
        ['vraag' => 'Kan ik erop wachten?',                                             'antwoord' => 'Bij remblokken of een demper meestal wel, dat duurt ongeveer een uur. Bij een complete uitlaatlijn plannen we een dagdeel en kunt u beter een leenauto of een lift regelen.'],
        ['vraag' => 'Mijn remmen piepen, is dat gevaarlijk?',                           'antwoord' => 'Daar wil ik niets verkeerds over zeggen zonder dat een monteur ernaar kijkt. Ik verbind u door met de werkplaats, of ik noteer dat u wordt teruggebeld. Wat heeft u liever?'],
        ['vraag' => 'Is mijn auto klaar? Kenteken 12-KLM-3, De Wit.',                  'antwoord' => 'Dank u, mevrouw De Wit. De auto is klaar, het bedrag is € 386,40 en u kunt hem vandaag tot half zes ophalen.'],
        ['vraag' => 'Doen jullie ook APK?',                                             'antwoord' => 'Nee, wij doen uitlaten, remmen en schokdempers. Voor APK verwijzen we naar Autobedrijf Van Dam om de hoek; wilt u hun nummer?'],
    ],

    'kan_titel' => 'Wat de assistent afhandelt',
    'kan' => [
        ['t' => 'Richtprijzen per klus',           'b' => 'Remblokken, remschijven, demper, katalysator, flexibel: de bandbreedte die jij opgeeft, met de opmerking dat de vaste prijs na een blik onder de auto komt.'],
        ['t' => 'Wachten of brengen',               'b' => 'Of de klant erop kan wachten, hoe lang het duurt, en wat je doet bij grotere klussen. Jouw regels.'],
        ['t' => '"Is mijn auto klaar?"',             'b' => 'Op kenteken en achternaam de status uit jouw werkplaatssysteem, als dat gekoppeld is.'],
        ['t' => 'Afspraakverzoeken',                'b' => 'Kenteken, klacht, wanneer het uitkomt. Hij leest het terug en de werkplaats bevestigt de tijd.'],
        ['t' => 'Dagberichten',                     'b' => 'Vandaag vol, geen wachtklussen meer, morgen om twaalf uur dicht: één bericht van jou en de assistent zegt het tegen iedere beller.'],
    ],

    'niet' => [
        'Zeggen of iets veilig is. "Kan ik nog rijden met deze remmen?" gaat altijd naar een monteur.',
        'Een vaste prijs noemen voor werk dat eerst bekeken moet worden. Hij noemt de bandbreedte die jij opgeeft.',
        'Een afspraak inplannen zonder dat jij hem bevestigt.',
    ],

    'rekenhulp' => [
        'standaard' => [
            'per_dag'         => 20,
            'dagen'           => 5,
            'minuten'         => 1.5,
            'uurloon'         => 40,
            'oppakken'        => 4,
            'gemist_per_week' => 6,
            'bestelling_pct'  => 25,
            'bestelwaarde'    => 200,
            'afhandel_pct'    => 70,
        ],
        'teksten' => [
            'lead'         => 'Een rekenvoorbeeld, geen belofte. Zet de schuiven op jouw werkplaats en zie wat de telefoon je nu per maand kost aan monteurs die onder de auto vandaan komen, en welke omzet er in gemiste telefoontjes zit.',
            'deel_label'   => 'Deel daarvan dat een klus was geworden',
            'waarde_label' => 'Gemiddelde klus die je zo misloopt',
            'uren_tegel'   => 'per maand niet meer aan de telefoon of onder de auto vandaan gehaald',
            'tijd_tegel'   => 'aan monteursuren die weer naar de brug gaan',
        ],
    ],

    'faq_titel' => 'Wat uitlaat- en remmenspecialisten ons vragen over AI-telefonie',
    'faq' => [
        ['q' => 'Kan de assistent de telefoon opnemen voor mijn werkplaats?',       'a' => 'Ja. Hij neemt op met de naam van je bedrijf, noemt richtprijzen en wachttijden uit jouw lijst, zegt of een auto klaar is en legt afspraak- en terugbelverzoeken vast.'],
        ['q' => 'Noemt hij prijzen voor remmen en uitlaten?',                         'a' => 'De bandbreedte die jij opgeeft, per klus en eventueel per autoklasse. Een vaste prijs pas na een blik onder de auto; dat zegt hij er ook bij.'],
        ['q' => 'Wat zegt hij als iemand vraagt of hij nog veilig kan rijden?',        'a' => 'Niets wat een monteur zou moeten zeggen. Hij verbindt door of noteert een terugbelverzoek. Veiligheid laten we niet aan een assistent over.'],
        ['q' => 'Kan hij zeggen of de klant kan wachten?',                            'a' => 'Ja, volgens jouw regel: welke klussen wachtklussen zijn en hoe lang ze duren. Bij twijfel noteert hij het als vraag voor de werkplaats.'],
        ['q' => 'Werkt hij ook als ik geen werkplaatssysteem heb?',                   'a' => 'Ja. Zonder koppeling beantwoordt hij de vragen die geen systeem nodig hebben en legt hij de rest vast als verzoek. "Is mijn auto klaar?" gaat dan naar de werkplaats.'],
    ],

    'verder' => [
        ['facet' => 'website',        't' => 'Website voor uitlaat- en remmenspecialisten', 'b' => 'Richtprijzen en een afspraakknop op je site, zodat de assistent minder hoeft uit te leggen.'],
        ['facet' => 'automatisering', 't' => 'Verzoeken automatisch verwerken',           'b' => 'Een afspraakverzoek van de assistent komt op dezelfde werkplaatsplanning als een aanvraag via je website.'],
        ['facet' => 'klantenportaal', 't' => 'Klantenportaal voor zakelijke klanten',      'b' => 'Wagenparkbeheerders bekijken zelf welke auto klaar staat.'],
    ],

    'cta_titel' => 'Hoor het zelf, of vraag een demo aan',
    'cta_tekst' => 'Bel het demonummer en vraag wat remblokken kosten. Of plan een gesprek van een half uur; dan richten we een proefversie in met jouw prijslijst en wachtregels.',
];
