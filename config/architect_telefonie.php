<?php

/**
 * Inhoud van /ai-telefonie-architect op jouw-architect-website.nl (16-09-2026).
 * Alleen het bureau-eigen deel; de rest komt uit config/telefonie_basis.php.
 * Een architect belt weinig maar de gesprekken zijn lang en de opdrachten groot. De
 * assistent vraagt een nieuw project uit (locatie, wat, budget, fase, vergunning) en
 * houdt aannemers, gemeente en adviseurs bij de juiste projectarchitect.
 */

return [
    'demo_nummer'    => '',
    'kantoor_nummer' => '088-2545101',

    'woorden' => [
        'bedrijf'   => 'je bureau',
        'bedrijven' => 'architectenbureaus',
        'klant'     => 'opdrachtgever',
        'klanten'   => 'opdrachtgevers',
        'gegevens'  => 'soorten opdrachten en werkgebied, hoe een eerste gesprek en een offerte lopen, wie welk project doet, en wat hij mag zeggen over lopende projecten',
    ],

    'seo_titel' => 'AI telefonie voor architecten | AI telefoonassistent die opneemt als jij aan de tekentafel zit',
    'hero' => [
        'eyebrow' => 'AI-telefonie voor architecten',
        'title'   => 'AI-telefonie voor je bureau: de telefoon wordt opgenomen, ook als jij in een bouwvergadering zit',
        'sub'     => 'Een telefonische assistent die opneemt met de naam van je bureau, een nieuw project netjes uitvraagt (locatie, wat, budget, fase, vergunningsplicht), aannemers en de gemeente doorverbindt met de juiste projectarchitect en de opdrachtgever vertelt wat jij hebt vastgelegd. Op de bouw, in bespreking en na zessen.',
        'alt'     => 'De telefonische assistent van een architectenbureau',
        'usps'    => [
            'Neemt altijd op, ook als het hele bureau in een presentatie zit',
            'Een nieuw project komt compleet binnen: locatie, wens, budget, fase',
            'Aannemers en gemeente naar de juiste projectarchitect',
            'Na elk gesprek een samenvatting per mail',
        ],
    ],

    'wanneer_titel' => 'Wanneer de telefoon het meeste stoort',
    'wanneer' => [
        ['t' => 'In de bouwvergadering',            'b' => 'Twee uur op de bouw met aannemer en constructeur. Wie dan belt met een nieuwbouwwoning van vierhonderdduizend euro, krijgt voicemail en belt een ander bureau.'],
        ['t' => 'De aannemer met een detailvraag',  'b' => 'De timmerman staat met een tekening in de hand en belt over een kozijnmaat. Dat moet nu naar de projectarchitect, niet naar het algemene nummer.'],
        ['t' => '"Wat kost een architect?"',        'b' => 'Iedereen die voor het eerst belt, vraagt het. Percentage van de bouwsom, uurtarief, fasen: jouw uitleg, elke keer hetzelfde.'],
        ['t' => 'Vergunningsvragen',                'b' => '"Mag ik zonder vergunning uitbouwen?" is een vraag voor jou, niet voor een assistent. Hij noteert hem en zegt eerlijk dat de architect terugbelt.'],
    ],

    'gesprekken_titel' => 'Zo klinkt dat, in gewoon Nederlands',
    'gesprekken_lead'  => 'De antwoorden hieronder komen uit de gegevens die het bureau zelf heeft aangeleverd. Zo praat de assistent ook over jouw bureau: alleen wat jij hebt doorgegeven.',
    'gesprekken' => [
        ['vraag' => 'We willen een woning laten ontwerpen op een kavel in Blaricum.',           'antwoord' => 'Mooi. Mag ik het adres van de kavel, of u al een bouwbudget in gedachten heeft, en in welke fase u zit: oriënteren, kavel gekocht, of al een schets? Dan lees ik het terug en belt Jeroen u deze week voor een eerste gesprek. Het eerste gesprek is bij ons kosteloos.'],
        ['vraag' => 'Wat kost een architect?',                                                    'antwoord' => 'Voor woningen werken we met een percentage van de bouwsom, meestal tussen de acht en twaalf procent afhankelijk van de fasen die we doen. Voor kleinere verbouwingen een uurtarief van € 115. Een echte offerte komt na het eerste gesprek.'],
        ['vraag' => 'Ik ben van Bouwbedrijf Koning, over de kozijnmaten op de Lindelaan.',        'antwoord' => 'Ik verbind u door met Sanne, de projectarchitect van de Lindelaan. Neemt zij niet op, dan noteer ik uw vraag en belt ze u binnen het uur terug.'],
        ['vraag' => 'Mag ik mijn garage zonder vergunning ombouwen tot kantoor?',                 'antwoord' => 'Dat hangt van het bestemmingsplan en de situatie af en daar wil ik geen verkeerd antwoord op geven. Ik noteer uw adres en vraag; de architect belt u terug. Wilt u dat ik meteen een eerste gesprek aanvraag?'],
        ['vraag' => 'Hoe staat het met onze vergunningaanvraag?',                                 'antwoord' => 'Op naam van Van Dam, Meentweg, zie ik dat de aanvraag op 2 september is ingediend en de gemeente acht weken heeft. Verandert er iets, dan hoort u het van Jeroen.'],
        ['vraag' => 'Doen jullie ook interieur?',                                                 'antwoord' => 'Ja, interieurontwerp doen we bij onze eigen projecten en als losse opdracht voor woningen en kantoren. Zal ik een eerste gesprek aanvragen?'],
    ],

    'kan_titel' => 'Wat de assistent afhandelt',
    'kan' => [
        ['t' => 'Nieuwe projecten uitvragen',        'b' => 'Locatie, soort opdracht, budget, fase, vergunning, hoe de beller bij je kwam. Compleet in je mail, klaar voor een eerste gesprek.'],
        ['t' => 'Werkwijze en honorarium',            'b' => 'Hoe een traject loopt, welke fasen, hoe je rekent. Jouw uitleg, zonder offerte aan de telefoon.'],
        ['t' => 'Aannemers, adviseurs, gemeente',     'b' => 'Naar de projectarchitect van dat project doorverbinden, of het bericht vastleggen met project en vraag.'],
        ['t' => 'Opdrachtgevers informeren',          'b' => 'Wat jij per project hebt vastgelegd: fase, ingediend, verwachte reactie. Alleen tegen de opdrachtgever zelf.'],
        ['t' => 'Dagberichten',                       'b' => 'Bureau gesloten, projectarchitect op vakantie, gemeente traag deze maand: één bericht van jou en de assistent zegt het tegen wie ernaar vraagt.'],
    ],

    'niet' => [
        'Iets zeggen over vergunningen, bestemmingsplannen of bouwregels. Elke inhoudelijke vraag wordt een terugbelverzoek.',
        'Een honorarium of planning toezeggen. Hij noemt jouw manier van rekenen; een offerte komt na het eerste gesprek.',
        'Iets over een project zeggen tegen iemand die niet de opdrachtgever of een betrokken partij is.',
    ],

    'rekenhulp' => [
        'standaard' => [
            'per_dag'         => 8,
            'dagen'           => 5,
            'minuten'         => 4,
            'uurloon'         => 60,
            'oppakken'        => 8,
            'gemist_per_week' => 2,
            'bestelling_pct'  => 5,
            'bestelwaarde'    => 5000,
            'afhandel_pct'    => 50,
        ],
        'teksten' => [
            'lead'         => 'Een rekenvoorbeeld, geen belofte. Zet de schuiven op jouw bureau en zie wat de telefoon je nu per maand kost aan onderbroken ontwerpwerk, en welk honorarium er in gemiste telefoontjes zit. Een architect wordt weinig gebeld, maar één gemist project is een kwartaal omzet.',
            'deel_label'   => 'Deel daarvan dat een opdracht was geworden',
            'waarde_label' => 'Gemiddeld honorarium van zo\'n opdracht',
            'uren_tegel'   => 'per maand niet meer aan de telefoon of uit een ontwerp gehaald',
            'tijd_tegel'   => 'aan uren die weer naar ontwerpen gaan in plaats van bellen',
            'omzet_tegel'  => 'honorarium per maand in aanvragen die nu niemand opneemt',
        ],
    ],

    'faq_titel' => 'Wat architecten ons vragen over AI-telefonie',
    'faq' => [
        ['q' => 'Kan de assistent de telefoon opnemen voor mijn bureau?',       'a' => 'Ja. Hij neemt op met de naam van je bureau, vraagt nieuwe projecten uit, legt uit hoe je werkt en rekent, en verbindt aannemers, adviseurs en gemeente door naar de juiste projectarchitect.'],
        ['q' => 'Past een AI-assistent bij een ontwerpbureau?',                  'a' => 'Hij praat in de woorden die jij aanlevert en begroet met de naam van je bureau. Wie belt merkt vooral dat er wordt opgenomen en dat zijn vraag compleet aankomt. De stem kiezen we samen.'],
        ['q' => 'Geeft hij antwoord op vergunningsvragen?',                       'a' => 'Nee. Bij elke vraag over bestemmingsplan, vergunningsvrij bouwen of bouwregels zegt hij dat de architect dat beoordeelt en noteert hij adres en vraag als terugbelverzoek.'],
        ['q' => 'Kan hij opdrachtgevers de stand van hun project vertellen?',      'a' => 'Alleen wat jij per project hebt vastgelegd, bijvoorbeeld "ingediend op 2 september, gemeente heeft acht weken", en alleen tegen de opdrachtgever zelf.'],
        ['q' => 'Wat doet hij met aannemers en constructeurs?',                    'a' => 'Doorverbinden naar de projectarchitect; neemt die niet op, dan legt hij project en vraag vast en krijgt de architect het direct per mail en in het portaal.'],
    ],

    'verder' => [
        ['facet' => 'website',        't' => 'Website voor architecten',        'b' => 'Portfolio, werkwijze en een aanvraagformulier dat dezelfde vragen stelt als de assistent.'],
        ['facet' => 'automatisering', 't' => 'Aanvragen automatisch verwerken', 'b' => 'Een nieuw project van de assistent komt op dezelfde lijst als een aanvraag via je website.'],
        ['facet' => 'klantenportaal', 't' => 'Klantenportaal voor opdrachtgevers', 'b' => 'Tekeningen, planning en vergunningstatus op één plek, zodat er minder gebeld wordt.'],
    ],

    'cta_titel' => 'Vraag een demo aan met jouw werkwijze',
    'cta_tekst' => 'Plan een gesprek van een half uur; dan richten we een proefversie in met jouw soorten opdrachten en honorariumuitleg en bel je zelf alsof je een kavel hebt gekocht.',
];
