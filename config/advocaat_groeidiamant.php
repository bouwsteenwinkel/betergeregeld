<?php

// Groeidiamant-pagina, branche-laag advocaat. Let op: AI is hier nooit een vervanger van
// juridisch advies; de assistent inventariseert, legt vast en routeert. Zie ook advocaat_telefonie.php.
return [
    'woorden' => ['bedrijf' => 'je kantoor', 'bedrijven' => 'advocatenkantoren'],

    'seo_titel'        => 'Digitale groei voor advocaten: website, automatisering & AI | Groeidiamant',
    'seo_omschrijving' => 'Ontdek hoe je advocatenkantoor stap voor stap groeit met een website, online dienstverlening, cliëntenportaal, automatisering en AI-telefonie. Begin waar dat voor jouw kantoor logisch is.',

    'hero' => [
        'titel' => 'Van website tot AI: laat je advocatenkantoor stap voor stap digitaal groeien',
        'lead'  => 'Begin met wat jouw kantoor nu nodig heeft. Een sterke website, online dienstverlening, een cliëntenportaal, automatisering of AI-telefonie. Alles kan op elkaar aansluiten zonder dat je later opnieuw hoeft te beginnen.',
    ],

    'situaties' => [
        'website'        => ['t' => 'Cliënten vinden ons kantoor niet, of zien niet wat we doen', 'b' => 'Rechtsgebieden, tarieven en contact moeten in één oogopslag duidelijk zijn.'],
        'webshop'        => ['t' => 'Ik wil intakes en consulten online laten aanvragen', 'b' => 'Een intake, consult of vaste-prijsdienst aanvragen zonder telefoontje, waar passend direct betaald.'],
        'klantenportaal' => ['t' => 'Cliënten bellen en mailen om de stand van hun zaak', 'b' => 'Documenten, afspraken en berichten in een beveiligde omgeving.'],
        'automatisering' => ['t' => 'Intakegegevens worden twee keer overgetypt', 'b' => 'Van website naar dossier zonder handwerk, met conflictcheck-gegevens erbij.'],
        'ai'             => ['t' => 'De telefoon gaat terwijl ik op zitting of in gesprek ben', 'b' => 'Eerste intakevragen opvangen, terugbelverzoeken vastleggen, spoed herkennen.'],
    ],

    'fasen' => [
        'website' => [
            'voor'        => 'Een professionele website die expertise en rechtsgebieden duidelijk maakt en potentiële cliënten snel helpt contact op te nemen.',
            'voorbeelden' => ['Gevonden op "advocaat arbeidsrecht" plus je plaats', 'Per rechtsgebied een pagina met wat je doet en wat het kost', 'Intakeformulier dat de juiste vragen stelt'],
            'resultaat'   => 'Beter gevonden op je rechtsgebieden en meer serieuze aanvragen.',
            'cta'         => 'Bekijk websites voor advocaten',
        ],
        'webshop' => [
            'titel'       => 'Online dienstverlening',
            'voor'        => 'Laat cliënten bijvoorbeeld een intake, juridisch consult of vaste-prijsdienst online aanvragen en waar passend direct betalen.',
            'voorbeelden' => ['Eerste consult van een half uur online boeken', 'Vaste-prijsdiensten (contractcheck, incasso) aanvragen en betalen', 'Aanvraag komt met alle gegevens binnen'],
            'resultaat'   => 'Meer aanvragen digitaal, minder heen-en-weer per mail.',
            'cta'         => 'Bekijk online dienstverlening voor advocaten',
        ],
        'klantenportaal' => [
            'titel'       => 'Cliëntenportaal',
            'voor'        => 'Geef cliënten een beveiligde omgeving voor afspraken, documenten, berichten en dossierinformatie.',
            'voorbeelden' => ['Stukken veilig delen in plaats van per mail', 'Afspraken en termijnen op één plek', 'Cliënt ziet zelf wat de stand is'],
            'resultaat'   => 'Minder "hoe staat het ermee"-telefoontjes en veiliger documentverkeer.',
            'cta'         => 'Bekijk cliëntenportalen voor advocaten',
        ],
        'automatisering' => [
            'voor'        => 'Laat intakegegevens, afspraken, documenten, herinneringen en administratieve stappen automatisch doorlopen tussen website en interne systemen.',
            'voorbeelden' => ['Intake van de site rechtstreeks in het dossier', 'Herinneringen voor afspraken en termijnen', 'Koppeling met je praktijksoftware, waar die het toelaat'],
            'resultaat'   => 'Minder overtypen en minder gemiste termijnen.',
            'cta'         => 'Bekijk automatisering voor advocaten',
        ],
        'ai' => [
            'voor'        => 'Laat veelvoorkomende gesprekken en eerste intakevragen opvangen, terugbelverzoeken vastleggen en aanvragen bij de juiste advocaat terechtkomen. Zonder juridisch advies: dat blijft bij jou.',
            'voorbeelden' => ['Rechtsgebied en situatie in de woorden van de beller genoteerd', 'Spoed herkend en doorverbonden', 'Nooit een oordeel over de zaak'],
            'resultaat'   => 'Bereikbaar tijdens zittingen; intakes komen compleet binnen.',
            'cta'         => 'Bekijk AI-telefonie voor advocaten',
        ],
    ],

    'praktijk' => [
        'intro'   => 'Zo kan het lopen bij een kantoor met drie advocaten:',
        'stappen' => [
            ['fase' => 'website',        't' => 'Eerst de website',      'b' => 'Het kantoor begint met een nieuwe website waarop arbeidsrecht en ondernemingsrecht goed vindbaar zijn, met per rechtsgebied een intakeformulier.'],
            ['fase' => 'klantenportaal', 't' => 'Dan het cliëntenportaal', 'b' => 'Daarna komt er een cliëntenportaal voor documenten en afspraken, zodat stukken niet meer per mail heen en weer gaan.'],
            ['fase' => 'automatisering', 't' => 'Dan de koppeling',      'b' => 'Vervolgens worden de intakeformulieren gekoppeld aan de dossieradministratie, zodat gegevens niet opnieuw hoeven te worden ingevoerd.'],
            ['fase' => 'ai',             't' => 'Later AI-telefonie',    'b' => 'Later vangt AI-telefonie veelvoorkomende gesprekken en terugbelverzoeken op wanneer iedereen op zitting of in gesprek is.'],
        ],
    ],

    'telefonie' => [
        'titel'  => 'Niet ieder telefoontje hoeft je zitting of gesprek te onderbreken',
        'tekst'  => 'Een AI-telefoonassistent vangt veelvoorkomende gesprekken op als jij of het secretariaat niet beschikbaar is. Hij noemt rechtsgebieden en tarieven, noteert een nieuwe zaak in de woorden van de beller en legt terugbelverzoeken vast. Juridisch advies geeft hij niet; dat blijft bij de advocaat.',
        'vragen' => ['Behandelen jullie arbeidsrecht?', 'Kan ik een afspraak maken?', 'Doen jullie huurgeschillen?', 'Kan iemand mij terugbellen?'],
        'cta'    => 'Bekijk AI-telefonie voor advocaten',
    ],

    'faq' => [
        ['q' => 'Kan ik ook alleen AI-telefonie gebruiken?', 'a' => 'Ja. AI-telefonie werkt op zichzelf: een nummer, jouw rechtsgebieden en tarieven, en de assistent neemt op. Hij geeft geen juridisch advies en zegt niets over lopende zaken; dat leggen we bij de inrichting vast.'],
        ['q' => 'Geeft de AI-assistent juridisch advies?', 'a' => 'Nee. "Heb ik een kans?" gaat altijd naar een advocaat. De assistent inventariseert het rechtsgebied, noteert de situatie in de woorden van de beller, verzamelt gegevens voor de conflictcheck en legt een terugbelverzoek vast.'],
        ['q' => 'Kunnen jullie koppelen met bestaande systemen?', 'a' => 'Vaak wel, maar niet altijd. Of een koppeling met je praktijksoftware mogelijk is, hangt af van dat pakket. We bekijken dat vooraf en zeggen eerlijk wat kan; wat niet kan, blijft handwerk of gaat via een export.'],
    ],
];
