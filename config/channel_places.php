<?php

/**
 * Generieke content-engine voor de /plaatsen-pagina's van ÁLLE niche-sites.
 *
 * De zware SEO-copy staat hier ÉÉN keer, trade-aware via tokens. Elke niche-
 * branche levert alleen een klein setje tokens (branche.places) — trade-noun,
 * niche-woord en de Google-Places-zoekopdracht — zodat dezelfde varianten voor
 * elke branche werken en het in één keer uitrolt naar honderden sites.
 *
 * Tokens die in de varianten vervangen worden:
 *   :city   plaatsnaam            :region  provincie          :full  plaats (context)
 *   :trade  bedrijfsnaam-enkelv.  :trades  meervoud           (bv. 'badkamerbedrijf' / '...bedrijven')
 *   :niche  product/dienst        :niches  meervoud           (bv. 'badkamer' / 'badkamers')
 *   :service wat wij leveren      (default 'website')
 *
 * Per plaats wordt deterministisch (slug-hash) één variant per blok gekozen →
 * unieke tekst per pagina, geen duplicate content. Zie ChannelPlaceContent.
 */

return [

    // SEO-gating: een plaats-pagina wordt pas geïndexeerd (en in de sitemap gezet)
    // als er minstens zoveel echte lokale bedrijven in cache staan. Dunne plaatsen
    // krijgen noindex, zodat er geen doorway-/thin-content-pagina's ontstaan.
    //
    // Verhoogd van 3 → 5 (2026-07): jonge channel-sites boden Google honderden
    // plaatspagina's tegelijk aan (badkamer ~909 bij drempel 3), waardoor die in
    // "Gevonden, niet geïndexeerd" bleven hangen. Met drempel 5 concentreren we
    // het crawl-budget op de plaatsen met genoeg echte bedrijven (dus substantiële
    // pagina's) en zakt badkamer naar ~692 sterkere URL's.
    'index_min_businesses' => 5,

    // Minimaal aantal ADRESSEN in de woonplaats (channel_place_facts.adressen, uit de
    // BAG via PDOK). Dit is de echte schaal-maat; index_min_businesses hierboven is dat
    // niet, want Google Places geeft nooit meer dan 9 resultaten terug: gemeten
    // 03-08-2026 zaten alle 984 "sterke" plaatsen van bedrijfswebsite in de bak 5-9,
    // dus er viel met die drempel niets te selecteren. `inwoners` helpt ook niet, dat
    // is een gemeentecijfer (Anloo en Annen krijgen beide 25.845 = Aa en Hunze).
    //
    // 4.000 adressen ≈ 8.000 inwoners. Gekozen op de volledige meting van 03-08-2026
    // (alle 1.195 plaatsen verrijkt), met Google's eigen oordeel als ijkpunt: van de
    // 1.026 aangeboden URL's had het er 343 geïndexeerd en 684 afgewezen met "Gevonden
    // — momenteel niet geïndexeerd". Wat de drempel oplevert aan plaatspagina's:
    //
    //   drempel 2.500 → 528   (houdt dorpen: Balk 2.596, Beetsterzwaag 2.327)
    //   drempel 4.000 → 392   ← hier: elk echt stadje blijft (Axel 4.833, Boekel 4.873)
    //   drempel 5.000 → 327   (snijdt die er net af)
    //
    // 392 ligt dicht bij die 343, dus we bieden ongeveer aan wat Google ook wil hebben
    // in plaats van 984 pagina's waarvan het tweederde weigert. Ter kalibratie: Anloo
    // 197, Annen 1.887, Beilen 6.053, Bussum 19.514, Assen 39.100, Amsterdam 579.486.
    // 44 plaatsen staan op 0 adressen: buurtschappen die niet als woonplaats in de BAG
    // bestaan — precies wat je wilt uitsluiten.
    //
    // Eén getal om aan te draaien. Blijft de bak "Gevonden, niet geïndexeerd" over een
    // paar maanden groot, dan hoger; komen de plaatspagina's juist goed door, dan lager.
    // Zet op 0 om de grens helemaal uit te zetten.
    'index_min_addresses' => 4000,

    // Fallback-tokens als een branche (nog) geen eigen waarden heeft.
    'defaults' => [
        'trade'   => 'bedrijf',
        'trades'  => 'bedrijven',
        'niche'   => 'vak',
        'niches'  => 'diensten',
        'service' => 'website',
    ],

    // Index-pagina (/plaatsen).
    'index' => [
        'eyebrow'   => 'Heel Nederland',
        'h1'        => 'Voor :trades door heel Nederland',
        'intro'     => 'Heb je een :trade en wil je online meer klanten binnenhalen? We helpen :trades door heel Nederland aan een :service die gevonden wordt en aanvragen oplevert. Kies je plaats en zie wat we in jouw regio doen.',
        'pick_h2'   => 'Kies je provincie',
        'pick_note' => 'Kies je provincie en daarna je plaats. Staat je plaats er niet bij? We werken door heel Nederland. Vraag gerust een gratis voorbeeld aan.',
    ],

    // Labels voor de "echte bedrijven in de regio"-sectie (Google Places).
    'business' => [
        'label' => 'Bedrijven in de regio :city',
        'intro' => 'Zo ziet het landschap in :city en omgeving eruit. Zorg dat jouw :trade hiertussen opvalt met een sterke online aanwezigheid.',
        'query' => ':niche :city',
        'limit' => 8,
        'min_reviews' => 3,
    ],

    // Tekstvarianten per blok. Deterministisch één per plaats gekozen.
    'variants' => [

        'meta_title' => [
            ':service voor je :trade in :city',
            ':trade in :city online laten groeien',
            'Meer aanvragen voor je :trade in :city',
            ':service laten maken voor je :trade in :city',
            'Online groeien met je :trade in :city',
        ],

        'meta_description' => [
            'Heb je een :trade in :city? Wij zorgen dat je online gevonden wordt en meer aanvragen binnenhaalt met een strakke :service. Vooraf een gratis voorbeeld van jóuw bedrijf in :city.',
            'Word gevonden in :city (:region) als iemand een :niche zoekt. Wij bouwen :trades een :service die klanten oplevert. Vooraf krijg je gratis en vrijblijvend een voorbeeld.',
            ':trades in :city en omgeving groeien online met betergeregeld: een :service, webshop en slimme tools die aanvragen opleveren. Bekijk hoe.',
            'Een :service voor je :trade in :city die klussen oplevert: vindbaar in Google, je vakwerk in beeld en aanvragen rechtstreeks in je mailbox.',
            'Meer klanten voor je :trade in :city? Wij regelen je online groei van begin tot eind. Vooraf een gratis voorbeeld, en je weet je prijs voordat je iets beslist.',
        ],

        'h1' => [
            ':service voor je :trade in :city',
            ':trade in :city? Word online gevonden',
            'Online groeien met je :trade in :city',
            'Meer aanvragen voor je :trade in :city',
        ],

        'hero_lead' => [
            'Heb je een :trade in :city en omgeving? Wij zorgen dat je online gevonden wordt door mensen die in :city een :niche zoeken, met een :service die aanvragen oplevert. Vooraf een gratis voorbeeld van jóuw bedrijf in :city.',
            'Wie in :city een :niche zoekt, begint bij Google. Zorg dat jouw :trade daar bovenaan staat. Wij bouwen een strakke :service die je vakwerk laat zien en klanten binnenhaalt. Vooraf krijg je gratis en vrijblijvend een voorbeeld.',
            'Voor :trades in :city (:region): een professionele :service, online verkopen en slimme tools die met je meegroeien. Je begint waar je nu staat en breidt uit wanneer je eraan toe bent.',
            'Meer klussen uit :city en omgeving? Wij regelen de online kant van je :trade van A tot Z: vindbaar in Google, aanvragen in je mailbox en een site waar klanten vertrouwen in krijgen.',
        ],

        'intro' => [
            'Steeds meer :trades in :city ontdekken dat online gevonden worden het verschil maakt tussen een volle en een lege agenda. Wie een :niche zoekt, kijkt eerst op zijn telefoon en kiest het bedrijf dat er professioneel uitziet en makkelijk te bereiken is. Daar helpen wij je mee.',
            'In :city en de rest van :region is de concurrentie tussen :trades groot. Een verouderde site of alleen een social-pagina kost je klussen. Met een strakke :service, je mooiste projecten in beeld en een duidelijke aanvraagknop val je op tussen de rest.',
            'Een :trade runnen in :city betekent dat je het druk hebt met het echte werk, niet met techniek. Daarom nemen wij de online kant uit handen: een :service die vindbaar is, aanvragen die vanzelf binnenkomen en tools die je administratie lichter maken. Jij bouwt, wij zorgen dat de klanten je vinden.',
            'Klanten in :city verwachten dat ze een :trade online kunnen vinden, bekijken en aanvragen, het liefst \'s avonds vanaf de bank. Wij bouwen een :service die daarop is gemaakt: snel, vindbaar in :city en omgeving, en gericht op het binnenhalen van aanvragen.',
        ],

        'wie' => [
            'Van eenmanszaken tot gevestigde bedrijven: we helpen :trades in :city van elke omvang. Begin je net en wil je vooral gevonden worden? Dan is een professionele :service de eerste stap. Draai je al langer mee en wil je online verkopen of je administratie automatiseren? Dan bouwen we dat er gewoon op voort.',
            'Onze klanten in :city zijn :trades die meer uit hun bedrijf willen halen zonder er zelf een technicus bij te worden. De één wil simpelweg meer aanvragen, de ander wil een webshop of een klantenportaal. Voor elke fase hebben we een passende stap.',
            'Werk je in en rond :city en merk je dat mond-tot-mondreclame niet meer genoeg is? Dan ben je precies wie we helpen. We zorgen dat nieuwe klanten uit :city je online vinden, zodat je niet afhankelijk blijft van toeval.',
        ],

        'trust' => [
            'Betergeregeld helpt ondernemers door heel Nederland online groeien, dus ook :trades in :city (:region). Vaste prijs vooraf en een Nederlands team dat opneemt en meedenkt. We zetten eerst een gratis voorbeeld van jóuw bedrijf klaar, zodat je precies ziet wat je krijgt voordat je iets beslist.',
            'Geen technisch gedoe en geen verrassingen achteraf. Voor :trades in :city werken we met een duidelijke prijs, een vast aanspreekpunt en een aanpak die met je meegroeit. Bij jou op locatie in de regio of gewoon online, net wat jou uitkomt.',
            'Het vertrouwen van ondernemers in :city en heel Nederland is waar we het van moeten hebben. Daarom leveren we echt maatwerk, denken we mee over wat jouw :trade nodig heeft, en zijn we bereikbaar als er iets is. Je begint klein en breidt uit wanneer het jou uitkomt.',
        ],

        'cta_title' => [
            'Klaar om te groeien met je :trade in :city?',
            'Meer aanvragen in :city? Begin hier.',
            'Jouw :trade online in :city.',
            'Zet de eerste stap in :city.',
        ],

        'cta' => [
            'Vraag gratis en vrijblijvend een voorbeeld aan van jouw bedrijf in :city. Je zit nergens aan vast en ziet meteen wat mogelijk is.',
            'Benieuwd hoe jouw :trade er online uit zou zien in :city? Vraag een gratis voorbeeld aan, wij zetten het voor je klaar.',
            'Nog één stap tot meer klanten uit :city. Vraag je gratis voorbeeld aan en zie zelf hoe het werkt.',
            'In :city hoef je geen omzet meer mis te lopen aan een concurrent die beter vindbaar is. Vraag vandaag je gratis voorbeeld aan.',
        ],

        // FAQ: nu een lijst van SETS in plaats van één vaste lijst. ChannelPlaceContent
        // kiest er per plaats één, met dezelfde deterministische keuze als de andere
        // blokken. Voorheen stond deze FAQ woord voor woord op élke plaatspagina, en dat
        // was een flinke hap uit de 31% tekstoverlap die ik op 03-08-2026 tussen vier
        // plaatspagina's van bedrijfswebsite meette. Zet je er weer een losse {q,a}-lijst
        // neer, dan valt de code terug op het oude gedrag.
        'faq' => [
        [
            [
                'q' => 'Werken jullie ook voor :trades in :city?',
                'a' => 'Ja. We helpen :trades door heel Nederland, dus zeker ook in :city en de rest van :region. Waar je bedrijf ook zit, we zetten vooraf een gratis voorbeeld klaar dat op jouw zaak in :city is afgestemd.',
            ],
            [
                'q' => 'Wat kost een :service voor mijn :trade?',
                'a' => 'Je krijgt vooraf een duidelijke, vaste prijs en geen verrassingen achteraf. Wat het precies wordt hangt af van wat je nodig hebt (alleen een site, of ook een webshop, portaal of automatisering). Het gratis voorbeeld is altijd vrijblijvend.',
            ],
            [
                'q' => 'Hoe snel sta ik online?',
                'a' => 'Vaak hebben we binnen 1 à 2 dagen een eerste voorbeeld van jouw bedrijf in :city klaar. Daarna stellen we het samen scherp en zetten we het live. Je hoeft zelf bijna niets te doen, behalve wat foto\'s en gegevens aanleveren.',
            ],
            [
                'q' => 'Wordt mijn :trade dan ook gevonden in :city?',
                'a' => 'Daar is het hele opzet op gericht. We zorgen dat je site vindbaar is als iemand in :city en omgeving een :niche zoekt, met de juiste teksten en een duidelijke aanvraagknop, zodat aanvragen rechtstreeks bij je binnenkomen.',
            ],
        ],

        // Set 2 — nadruk op wat de ondernemer zelf moet doen en op de bestaande site.
        [
            [
                'q' => 'Ik heb al een site, kan die niet gewoon beter?',
                'a' => 'Soms wel. We kijken eerst naar wat je hebt: staat de basis goed, dan is bijschaven vaak genoeg en zeggen we dat ook. Levert opnieuw beginnen meer op, dan leggen we uit waarom. Het voorbeeld dat we voor je bedrijf in :city klaarzetten laat het verschil meteen zien.',
            ],
            [
                'q' => 'Hoeveel werk is het voor mij?',
                'a' => 'Een uurtje, verdeeld over een paar momenten. Wij schrijven de teksten, richten de site in en regelen het technische deel. Van jou hebben we foto\'s nodig, je gegevens, en een keer een half uur aan de telefoon om te horen wat je in :city precies doet en voor wie.',
            ],
            [
                'q' => 'Wat als ik later iets wil veranderen?',
                'a' => 'Dat kun je zelf, en anders doen wij het. Kleine aanpassingen zoals openingstijden of een nieuwe foto zijn zo gedaan. Groeit je :trade en wil je een webshop of een klantenportaal erbij, dan bouwen we dat op dezelfde site verder uit.',
            ],
            [
                'q' => 'Zitten jullie in de buurt van :city?',
                'a' => 'We werken door heel :region en de rest van Nederland, en het meeste gaat online en telefonisch. Dat scheelt jou reistijd en ons ook, en het houdt de prijs laag. Wil je iemand spreken, dan bellen of videobellen we op een moment dat jou schikt.',
            ],
        ],

        // Set 3 — nadruk op aanvragen, opzegbaarheid en eigendom.
        [
            [
                'q' => 'Levert een site in :city echt aanvragen op?',
                'a' => 'Dat is waar we op bouwen. Iemand die in :city een :niche zoekt moet je vinden, meteen zien dat je het doet, en met één druk op de knop contact kunnen opnemen. Die aanvraag komt rechtstreeks in je mailbox, met alles wat je nodig hebt om terug te bellen.',
            ],
            // Let op: deze twee antwoorden volgen de algemene voorwaarden en het prijsblok
            // (config/bedrijfswebsite_facet_faq.php), niet een mooiere versie daarvan. De
            // voorwaarden zeggen dat aangeleverde content van de klant blijft maar dat
            // onderliggende systemen en sjablonen ons eigendom blijven, en er zijn twee
            // prijsvormen waarvan één een looptijd van 24 maanden heeft. Wie hier iets
            // ruimers belooft, schrijft een claim die de voorwaarden tegenspreken.
            [
                'q' => 'Van wie zijn mijn teksten en foto\'s straks?',
                'a' => 'Die blijven van jou, net als je domeinnaam. Wat je aanlevert is en blijft je eigen materiaal. Het systeem waarop je site draait, met de sjablonen en de techniek eronder, blijft van ons; daar betaal je het onderhoud voor. Zo staat het ook in de voorwaarden.',
            ],
            [
                'q' => 'Wat kost een site voor mijn :trade?',
                'a' => 'Er zijn twee vormen. Je betaalt 799 euro eenmalig plus 39 euro per maand voor hosting en onderhoud, of je spreidt het naar 69 euro per maand bij een looptijd van 24 maanden. Je weet het bedrag vooraf en het verandert niet.',
            ],
            [
                'q' => 'Wat gebeurt er met mijn plek in Google?',
                'a' => 'Die nemen we mee. Staat je huidige site al ergens op voor :city, dan zetten we de adressen netjes door zodat je die posities niet kwijtraakt. Dat is het soort werk dat je niet ziet, en dat je wel merkt als het niet gebeurt.',
            ],
        ],
        ],
    ],
];
