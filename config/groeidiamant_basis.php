<?php

/**
 * DE GROEIDIAMANT-PAGINA (/groeidiamant): de generieke basis.
 *
 * De pagina beantwoordt één vraag: "waar staat mijn bedrijf nu en wat is de logische
 * volgende stap?" Wat voor elk kanaal hetzelfde is staat hier één keer. De branche-laag
 * (config/{key}_groeidiamant.php, geregistreerd in config/channel_groeidiamant.php)
 * overschrijft per sleutel; ontbreekt die, dan haalt App\Support\GroeidiamantConfig de
 * fase-teksten uit de landings-config van de site (config/{key}_landings.php).
 *
 * Plaatshouders: :zaak (kantoor/praktijk/bakkerij), :trade, :trades, :bedrijf ("je kantoor"),
 * :bedrijven ("advocatenkantoren"). Zie GroeidiamantConfig voor de bronnen.
 *
 * Toon: nuchter Nederlands. Geen "transformeer", geen garanties; koppelingen en portalen
 * "kunnen worden ingericht", "afhankelijk van het bestaande systeem".
 */
return [

    'seo_titel'       => 'Digitale groei voor :trades: website, automatisering & AI-telefonie | Groeidiamant',
    'seo_omschrijving' => 'Ontdek hoe :bedrijf stap voor stap groeit met een website, online diensten, een klantenportaal, automatisering en AI-telefonie. Begin waar dat voor jou logisch is.',

    'hero' => [
        'eyebrow' => 'De Groeidiamant van Betergeregeld',
        'titel'   => 'Van website tot AI: laat :bedrijf stap voor stap digitaal groeien',
        'lead'    => 'Begin met wat :bedrijf nu nodig heeft: een sterke website, online diensten, een klantenportaal, automatisering of AI-telefonie. Alles sluit op elkaar aan, dus je hoeft later niet opnieuw te beginnen.',
        'cta'     => 'Ontdek jouw volgende stap',
        'cta2'    => 'Gratis websitevoorbeeld aanvragen',
        'motto'   => 'Je begint waar dat voor :bedrijf logisch is.',
    ],

    // "Waar sta je nu?" -- vijf herkenbare situaties, elk naar één fase.
    'situaties' => [
        'website'        => ['t' => 'Ik heb nog geen sterke website', 'b' => 'Of een site van jaren terug. Klanten vinden je niet, of haken af.', 'naar' => 'Begin met de website'],
        'webshop'        => ['t' => 'Ik wil online verkopen of diensten laten aanvragen', 'b' => 'Bestellen, boeken of aanvragen zonder dat er iemand aan de telefoon hoeft.', 'naar' => 'Bekijk online diensten'],
        'klantenportaal' => ['t' => 'Ik wil klanten zelf meer laten regelen', 'b' => 'Afspraken, documenten, status en facturen op één plek, zonder mailtjes heen en weer.', 'naar' => 'Bekijk het klantenportaal'],
        'automatisering' => ['t' => 'Ik doe te veel handmatig', 'b' => 'Gegevens overtypen, herinneringen sturen, lijstjes bijhouden. Dat kan vanzelf.', 'naar' => 'Bekijk automatisering'],
        'ai'             => ['t' => 'Ik wil minder telefoontjes zelf hoeven afhandelen', 'b' => 'De telefoon wordt opgenomen als jij geen tijd hebt; verzoeken komen bij de juiste persoon.', 'naar' => 'Bekijk AI-telefonie'],
    ],

    // De vijf fasen. 'voor' en 'voorbeelden' zijn de branche-onderdelen; hier de generieke
    // terugval. 'route' = pad op de site; 'ai' krijgt in GroeidiamantConfig de telefoniepagina.
    'fasen' => [
        'website' => [
            'titel'      => 'Website',
            'wat'        => 'Een professionele website die laat zien wat :bedrijf doet, gevonden wordt in Google en het makkelijk maakt om contact op te nemen. De basis waar de andere stappen op aansluiten.',
            'voor'       => 'Een website die duidelijk maakt wat :bedrijf doet en voor wie, en die in jouw regio gevonden wordt.',
            'voorbeelden' => ['Gevonden in Google in je eigen regio', 'Diensten en werkwijze meteen duidelijk', 'Aanvragen rechtstreeks in je mailbox'],
            'resultaat'  => 'Beter gevonden en professioneler overkomen.',
            'cta'        => 'Bekijk websites voor :trades',
            'route'      => 'website',
        ],
        'webshop' => [
            'titel'      => 'Webshop / online diensten',
            'wat'        => 'Producten, pakketten of diensten online laten bestellen of aanvragen, gekoppeld aan je site, met veilig betalen waar dat past.',
            'voor'       => 'Laat klanten van :bedrijf online bestellen of een dienst aanvragen, ook buiten openingstijden.',
            'voorbeelden' => ['Bestellen of aanvragen zonder telefoontje', 'Veilig betalen met iDEAL of op rekening', 'Gekoppeld aan je site, geen dubbele administratie'],
            'resultaat'  => 'Meer aanvragen en bestellingen digitaal afgehandeld.',
            'cta'        => 'Bekijk online diensten voor :trades',
            'route'      => 'webshop',
        ],
        'klantenportaal' => [
            'titel'      => 'Klantenportaal',
            'wat'        => 'Een beveiligde omgeving waarin klanten zelf inloggen voor afspraken, documenten, status en facturen. Wat erin zit richten we per bedrijf in.',
            'voor'       => 'Geef klanten van :bedrijf een eigen omgeving voor afspraken, documenten en de stand van zaken.',
            'voorbeelden' => ['Klanten zien zelf waar het staat', 'Documenten en facturen op één plek', 'Minder mail en telefoon over de status'],
            'resultaat'  => 'Minder mail en telefoon; klanten regelen meer zelf.',
            'cta'        => 'Bekijk klantenportalen voor :trades',
            'route'      => 'klantenportaal',
        ],
        'automatisering' => [
            'titel'      => 'Automatisering',
            'wat'        => 'Koppelingen en stappen die vanzelf lopen: gegevens van een aanvraag komen op de juiste plek, herinneringen gaan uit, niets wordt drie keer overgetypt. We bekijken per systeem welke koppeling mogelijk is.',
            'voor'       => 'Laat aanvragen, afspraken, documenten en herinneringen van :bedrijf automatisch doorlopen tussen website en interne systemen.',
            'voorbeelden' => ['Aanvraag komt meteen in het juiste systeem', 'Herinneringen en bevestigingen gaan vanzelf', 'Niets dubbel invoeren'],
            'resultaat'  => 'Minder handmatig werk en minder fouten.',
            'cta'        => 'Bekijk automatisering voor :trades',
            'route'      => 'automatisering',
        ],
        'ai' => [
            'titel'      => 'AI-telefonie & slimme assistentie',
            'wat'        => 'Een telefonische assistent die opneemt met de naam van :bedrijf, antwoordt uit jouw eigen gegevens, een terugbelverzoek vastlegt als hij het niet weet en doorverbindt als het moet. Ook los af te nemen, zonder de andere stappen.',
            'voor'       => 'Laat veelvoorkomende gesprekken opvangen, terugbelverzoeken vastleggen en aanvragen bij de juiste persoon van :bedrijf terechtkomen.',
            'voorbeelden' => ['De telefoon wordt opgenomen als jij geen tijd hebt', 'Antwoord uit jouw eigen gegevens, niets verzonnen', 'Terugbelverzoek met naam, nummer en waar het over ging'],
            'resultaat'  => 'Betere bereikbaarheid en minder onderbrekingen.',
            'cta'        => 'Bekijk AI-telefonie voor :trades',
            'route'      => 'ai',
        ],
    ],

    // "Zo kan dit er in de praktijk uitzien" -- generiek scenario; de branche-laag vervangt dit.
    'praktijk' => [
        'intro'   => 'Geen twee bedrijven beginnen op dezelfde plek. Zo kan het lopen:',
        'stappen' => [
            ['fase' => 'website',        't' => 'Eerst de website',     'b' => ':Bedrijf begint met een nieuwe website waarop de diensten en de regio goed vindbaar zijn.'],
            ['fase' => 'klantenportaal', 't' => 'Dan het portaal',      'b' => 'Daarna komt er een klantenportaal voor afspraken, documenten en de stand van zaken.'],
            ['fase' => 'automatisering', 't' => 'Dan de koppelingen',   'b' => 'Aanvragen worden gekoppeld aan de interne administratie, zodat gegevens niet opnieuw worden ingevoerd.'],
            ['fase' => 'ai',             't' => 'Later AI-telefonie',   'b' => 'AI-telefonie vangt veelvoorkomende gesprekken en terugbelverzoeken op als niemand beschikbaar is.'],
        ],
    ],

    // "Je hoeft niet bij stap 1 te beginnen"
    'instap' => [
        'lead' => 'Heb je al een goede website? Dan kun je direct starten met automatisering, een klantenportaal of AI-telefonie. We bekijken welke bestaande systemen kunnen blijven en waar koppelingen mogelijk zijn.',
        'punten' => [
            ['t' => 'Je website kan blijven',            'b' => 'Een goede bestaande site hoeft niet vervangen te worden. De volgende stappen sluiten erop aan.'],
            ['t' => 'Automatisering kan los',             'b' => 'Koppelingen tussen wat je al gebruikt, zonder eerst iets anders te bouwen. Per systeem bekijken we wat kan.'],
            ['t' => 'AI-telefonie kan los',               'b' => 'Een telefoonnummer, jouw gegevens, en de assistent neemt op. Vanaf de eerste maand, zonder andere stappen.'],
            ['t' => 'Het portaal sluit aan op wat er is', 'b' => 'Een klantenportaal kan op een bestaande omgeving aansluiten, afhankelijk van het systeem dat je nu gebruikt.'],
        ],
    ],

    // AI-telefonie uitgelicht. 'vragen' komt uit de telefonie-config als die er is.
    'telefonie' => [
        'titel' => 'Niet ieder telefoontje hoeft je werk te onderbreken',
        'tekst' => 'Een AI-telefoonassistent vangt veelvoorkomende gesprekken op als jij of je medewerkers niet beschikbaar zijn. Hij antwoordt uit de gegevens die :bedrijf zelf aanlevert, zegt het als hij iets niet weet, en legt een terugbelverzoek vast.',
        'vragen' => ['Tot hoe laat zijn jullie open?', 'Kan ik een afspraak maken?', 'Wat kost dat ongeveer?', 'Kan iemand mij terugbellen?'],
        'cta'   => 'Bekijk AI-telefonie voor :trades',
    ],

    'waarom' => [
        'titel' => 'Waarom de Groeidiamant?',
        'tekst' => 'Digitale groei hoeft geen groot alles-of-niets-project te zijn. Je begint met wat nu het meeste oplevert en voegt later oplossingen toe die aansluiten op wat er al staat. Elke stap bouwt voort op de vorige; niets hoeft opnieuw.',
    ],

    // Zes vragen; de branche-laag kan een antwoord vervangen of een vraag toevoegen (faq_extra).
    'faq' => [
        ['q' => 'Moet ik alle vijf stappen doorlopen?', 'a' => 'Nee. De vijf stappen sluiten op elkaar aan, maar elke stap is los af te nemen. Veel bedrijven beginnen met de website; wie die al heeft, begint bij de stap die nu het meeste oplevert.'],
        ['q' => 'Kan ik ook alleen AI-telefonie gebruiken?', 'a' => 'Ja. AI-telefonie werkt op zichzelf: een nummer, jouw gegevens, en de assistent neemt op. Een website of portaal van ons is daarvoor niet nodig.'],
        ['q' => 'Moet mijn huidige website vervangen worden?', 'a' => 'Niet per se. Een goede bestaande site kan blijven staan; we bekijken dan hoe de volgende stap erop aansluit. Is de site verouderd, dan is een nieuwe vaak de logische eerste stap.'],
        ['q' => 'Kunnen jullie koppelen met bestaande systemen?', 'a' => 'Vaak wel, maar niet altijd. Het hangt af van het systeem en of het een koppeling toelaat. We bekijken dat vooraf per systeem en zeggen eerlijk wat kan en wat niet.'],
        ['q' => 'Kan ik later uitbreiden?', 'a' => 'Ja, dat is precies het idee. Wat er staat blijft staan; een volgende stap bouwt erop voort. Je hoeft niet nu al te weten welke stappen je later wilt.'],
        ['q' => 'Waar kan ik het beste beginnen?', 'a' => 'Bij wat nu het meeste tijd kost of het meeste oplevert. Geen goede website? Daar. Veel telefoontjes met dezelfde vragen? AI-telefonie. Twijfel je, dan bespreken we het in een gesprek van een half uur.'],
    ],

    'slot' => [
        'titel' => 'Welke volgende stap past bij jou?',
        'tekst' => 'Vertel in een gesprek van een half uur waar :bedrijf nu staat; dan zeggen we eerlijk welke stap het meeste oplevert. Liever eerst iets zien? Vraag een gratis voorbeeld van je website aan.',
        'cta'   => 'Bespreek mijn situatie',
        'cta2'  => 'Gratis websitevoorbeeld aanvragen',
    ],

    // Eerste vraag van het formulier op deze pagina.
    'formulier' => [
        'vraag'  => 'Waar wil je mee beginnen?',
        'opties' => [
            ['value' => 'website',        'label' => 'Website',                  'icon' => 'globe',  'facet' => 'website'],
            ['value' => 'webshop',        'label' => 'Webshop / online diensten','icon' => 'cart',   'facet' => 'webshop'],
            ['value' => 'klantenportaal', 'label' => 'Klantenportaal',           'icon' => 'lock',   'facet' => 'klantenportaal'],
            ['value' => 'automatisering', 'label' => 'Automatisering',           'icon' => 'gear',   'facet' => 'automatisering'],
            ['value' => 'ai',             'label' => 'AI-telefonie',             'icon' => 'phone',  'facet' => 'ai'],
            ['value' => 'weet_niet',      'label' => 'Weet ik nog niet',         'icon' => 'chat',   'facet' => 'website'],
        ],
    ],
];
