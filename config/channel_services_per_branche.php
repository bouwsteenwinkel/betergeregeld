<?php

/**
 * Per-branche overschrijving van channel_services.php voor de /diensten-pagina.
 *
 * channel_services.php is generiek (offertes, klussen, chat) en klopt niet voor elke branche.
 * Hier per branche-key dezelfde structuur; wat hier staat vervangt de generieke tekst
 * (array_replace_recursive), wat ontbreekt valt terug op het generieke bestand.
 *
 * 15-09-2026: bakkerij (docs/bakkerij/SEO-CONTENT-AUDIT.md). 'ai' is hier AI-telefonie en linkt
 * via 'url' naar de landingspagina in plaats van naar de facetpagina.
 */

return [
    'bakkerij' => [
        'eyebrow'   => 'Onze diensten',
        'title'     => 'Alle diensten voor bakkerijen: website, webshop, klantenportaal, automatisering en AI-telefonie',
        'h1'        => 'Wat we bouwen voor bakkerijen',
        'intro'     => 'Vijf diensten die je los van elkaar kunt afnemen, en die op elkaar aansluiten als je ze combineert. Een website waarop klanten je vinden en je assortiment zien, een webshop met afhaaltijd en vooraf betalen, een portaal waarin zakelijke klanten zelf bestellen, automatisering die bestellingen op de productielijst zet, en een telefoon die wordt opgenomen als jij bakt. Begin met wat je nu het meeste oplevert.',
        'cta_title' => 'Benieuwd wat dit voor jouw bakkerij zou betekenen?',
        'services'  => [
            'website' => [
                'label'   => 'Website',
                'tagline' => 'Gevonden worden, assortiment en openingstijden laten zien, bestellen of bellen met één knop.',
                'intro'   => 'De meeste klanten zoeken je op hun telefoon: "bakkerij" plus de plaats. Wij bouwen een site die daarop gevonden wordt en meteen laat zien wat je hebt, wanneer je open bent en hoe iemand bestelt of belt. Met foto\'s van je brood en taarten, een pagina voor speciale bestellingen en openingstijden per vestiging die jij zelf bijhoudt.',
                'bullets' => [
                    'Gevonden op "bakkerij" plus jouw plaats en de dorpen eromheen',
                    'Openingstijden per vestiging, met feestdagen en vakanties',
                    'Assortiment met foto\'s en prijzen, als je dat wilt',
                    'Een pagina voor taarten en bestellingen op maat',
                    'Snel en leesbaar op een telefoon, één tik om te bellen',
                    'Zelf aanpassen, of wij doen het voor je',
                ],
                'example' => 'Iemand zoekt zaterdagochtend "bakkerij" en de naam van jouw dorp. Ze ziet jouw site bovenaan, ziet dat je tot vijf uur open bent en dat je vandaag nog appeltaart hebt. Ze belt niet; ze komt.',
            ],
            'webshop' => [
                'label'   => 'Webshop',
                'tagline' => 'Brood, banket en taarten online bestellen, met afhaaldatum en vooraf betalen.',
                'intro'   => 'Klanten bestellen brood, gebak of een taart, kiezen een afhaaldatum en -tijd binnen de grenzen die jij stelt, en betalen vooraf met iDEAL. Zakelijke klanten kunnen op rekening. Jij ziet elke ochtend op één lijst wat er gemaakt en klaargezet moet worden. Rond feestdagen sluit de shop vanzelf als de productie vol is.',
                'bullets' => [
                    'Afhaaldatum en -tijd per product instelbaar',
                    'Vooraf betalen met iDEAL via Mollie',
                    'Bezorgen in je eigen gebied, of afhalen in de winkel',
                    'Taarten en speciale bestellingen met de wensen erbij',
                    'Vaste klanten herhalen een eerdere bestelling in een paar tikken',
                    'Bestellingen op één lijst, niet in WhatsApp en een schrift',
                ],
                'example' => 'Een klant bestelt dinsdagavond een verjaardagstaart voor zaterdag, met naam en leeftijd erop, en betaalt meteen. Woensdagochtend staat hij op je productielijst voor vrijdag. Zaterdag om tien uur staat ze aan de balie met haar bestelnummer.',
            ],
            'klantenportaal' => [
                'label'   => 'Klantenportaal',
                'tagline' => 'Zakelijke klanten bekijken, wijzigen en herhalen hun bestelling zelf.',
                'intro'   => 'Horeca, kantoren, lunchrooms en andere vaste klanten krijgen een eigen inlog. Daar staat hun vaste bestelling per leverdag, daar passen ze aantallen aan of bestellen ze extra, en daar vinden ze hun facturen. Wat zij invoeren staat bij jou op de lijst. Hoe het precies werkt hangt af van hoe jij levert en factureert; dat richten we samen in.',
                'bullets' => [
                    'Vaste bestelling per leverdag, aanpassen tot een tijdstip dat jij bepaalt',
                    'Extra bestellen, een dag overslaan of een week dichtmelden',
                    'Aflevermoment inzien en een afwijkende dag vragen',
                    'Facturen en bestelhistorie bekijken en downloaden',
                    'Eigen assortiment en prijzen per klant, als je administratie dat kent',
                    'Maatwerk op jouw proces, geen standaardpakket',
                ],
                'example' => 'Het restaurant op de hoek belde elke avond om half elf de bestelling door. Nu logt de chef in, zet de aantallen voor morgen erin en bestelt er twee brioches bij. Om zes uur \'s ochtends staat het op jouw lijst, zonder dat er iemand heeft gebeld.',
            ],
            'automatisering' => [
                'label'   => 'Automatisering',
                'tagline' => 'Bestellingen vanzelf op de productielijst, in de administratie en bevestigd naar de klant.',
                'intro'   => 'Een bestelling die binnenkomt via de webshop, het portaal of de telefoon loopt door naar bevestiging, productielijst, aflevering en factuur zonder dat iemand hem overtypt. Zakelijke leveringen worden per week of maand gebundeld tot een factuur. Betalingen via Mollie werken standaard; voor je kassa of boekhouding (zoals Exact Online, e-Boekhouden of Moneybird) bekijken we wat er mogelijk is.',
                'bullets' => [
                    'Webshopbestelling → betaling → bevestiging → productielijst',
                    'Zakelijke bestelling → verzamelfactuur per week of maand',
                    'Bestelling voor morgen → pick- of afhaallijst per vestiging',
                    'Klantgegevens één keer invoeren, overal kloppen',
                    'Bevestiging en "staat klaar"-bericht naar de klant zonder bellen',
                    'Koppeling met kassa of boekhouding waar dat kan',
                ],
                'example' => 'Om zeven uur \'s ochtends print de productie één lijst: alles wat voor vandaag is besteld, per vestiging, met de taartteksten erbij. Aan het eind van de maand staan alle zakelijke leveringen als factuur klaar in de boekhouding.',
            ],
            'ai' => [
                'label'   => 'AI-telefonie',
                'tagline' => 'De telefoon wordt opgenomen, ook als jij in de bakkerij staat.',
                'url'     => 'ai-telefonie-bakkerij',
                'intro'   => 'Een telefonische assistent neemt op met de naam van je bakkerij. Hij beantwoordt de vragen die je elke dag krijgt uit jouw eigen gegevens, legt terugbel- en bestelverzoeken vast met naam en nummer, en verbindt door als een medewerker nodig is. Ook buiten openingstijden. Na elk gesprek krijg je een samenvatting per mail. Werkt los van je website; je kunt hem ook als enige dienst afnemen.',
                'bullets' => [
                    'Openingstijden, vestigingen en assortimentsvragen beantwoorden',
                    'Terugbel- en bestelverzoek vastleggen, herhaald ter controle',
                    'Doorverbinden tijdens openingstijden',
                    'Buiten openingstijden gewoon opnemen',
                    'Samenvatting per mail na elk gesprek, geen opnames',
                    'Bij allergievragen altijd naar een medewerker',
                ],
                'example' => 'Zaterdagochtend, rij tot de deur. Iemand belt of ze nog een taart voor morgen kan bestellen. De assistent legt uit dat taarten twee dagen vooruit gaan, noteert haar naam en nummer en wat ze wil. Jij belt na de drukte terug en hebt de bestelling toch.',
            ],
        ],
    ],
];
