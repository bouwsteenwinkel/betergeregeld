<?php

/**
 * Verkooplaag voor jouw-bakkerij-website.nl: de vijf facetpagina's (website, webshop,
 * klantenportaal, automatisering, ai). Beter Geregeld verkoopt een weboplossing AAN de bakker.
 *
 * Herschreven 15-09-2026. De vorige versie was het aannemer-sjabloon met alleen het branchewoord
 * vervangen: "laat klanten hun klus zelf volgen", "garantiebewijzen", "kansrijke klussen",
 * "bespaar tot wel 70% tijd" (audit: docs/bakkerij/SEO-CONTENT-AUDIT.md). Nu:
 * - elke zin vanuit de dagelijkse praktijk van een bakkerij (balie, taartbestellingen,
 *   zakelijke klanten, productielijst), geen ICT-taal;
 * - het klantenportaal is voor zakelijke bestelklanten (horeca, kantoren), niet voor "opdrachten";
 * - de ai-facet gaat over de telefoon en verwijst naar de landingspagina /ai-telefonie-bakkerij;
 * - alleen wat aantoonbaar gebouwd kan worden; koppelingen als "kan indien gewenst";
 * - geen getallen die we niet kunnen onderbouwen.
 *
 * Beeld-slots (imageSlot, gallery) zijn ongewijzigd: die verwijzen naar bestaande bestanden in
 * public/channel-media/bakkerij/.
 */

return [

    'website' => [
        'hero' => [
            'eyebrow' => 'Website voor bakkerijen',
            'title'   => 'Een website voor je bakkerij die klanten laat vinden, kijken en bestellen',
            'sub'     => 'Openingstijden, assortiment, taarten en bestellen op één plek. Gevonden worden als iemand in jouw plaats een bakkerij zoekt, en op een telefoon net zo goed als op een laptop.',
            'note'    => 'Gratis en vrijblijvend een voorbeeld van jóuw site, vaak binnen 1 à 2 dagen',
            'usps'    => [
                'Gevonden in Google op "bakkerij" plus jouw plaats',
                'Openingstijden, vestigingen en assortiment meteen duidelijk',
                'Bestellen of contact opnemen met één knop, ook op mobiel',
            ],
        ],
        'pains' => [
            ['title' => 'Alleen een Facebook-pagina, of een site van jaren terug',
             'text'  => 'Wie je opzoekt vindt oude foto\'s, verkeerde openingstijden of helemaal niets. Dan gaat de klant naar de bakker die wél netjes online staat.'],
            ['title' => 'Elke dag dezelfde vragen aan de balie',
             'text'  => 'Tot hoe laat zijn jullie open? Hebben jullie speltbrood? Kan ik een taart bestellen? Dat hoort op je site te staan, dan hoef jij het niet twintig keer per dag te zeggen.'],
            ['title' => 'Je bent niet vindbaar in Google',
             'text'  => 'Wie zoekt op "bakkerij" en jouw plaats, moet jou zien. Nu staan daar de supermarkt en een concurrent uit het dorp ernaast.'],
        ],
        'zkhw' => [
            'label'        => 'Website',
            'title'        => 'Zo kan het worden: je bakkerij 24/7 open om te bekijken',
            'intro'        => 'Klanten zien je assortiment, foto\'s van je brood en taarten, de openingstijden per vestiging en hoe ze kunnen bestellen. Voordat ze langskomen weten ze al wat je hebt.',
            'brand'        => 'Kruimel',
            'heroTitle'    => 'Bekijk het assortiment en bestel of reserveer online',
            'urlLabel'     => 'jouw-bakkerij.nl',
            'ctaLabel'     => 'Bekijk het volledige voorbeeld',
            'imageSlot'    => 'website-preview',
            'galleryLabel' => 'Een greep uit de winkel',
            'gallery'      => [['slot' => 'gallery1'], ['slot' => 'gallery2'], ['slot' => 'gallery3']],
            'bullets'      => [
                ['title' => 'Gevonden op je eigen plaats',          'text' => 'Teksten en pagina\'s die ingericht zijn op "bakkerij" plus jouw plaats en de dorpen eromheen.'],
                ['title' => 'Openingstijden die kloppen',          'text' => 'Per vestiging, met feestdagen en vakanties. Jij past ze zelf aan, zonder ons te bellen.'],
                ['title' => 'Assortiment met foto\'s',              'text' => 'Brood, banket, taarten, lunch. Met prijzen als je dat wilt, en een knop om te bestellen.'],
                ['title' => 'Taarten en speciale bestellingen',    'text' => 'Een duidelijke pagina voor verjaardagstaarten, bruidstaarten en bestellingen op maat, met wat je moet weten en hoe ver vooruit.'],
                ['title' => 'Goed op een telefoon',                 'text' => 'De meeste klanten zoeken je op hun telefoon, vaak onderweg. De site is daarop gebouwd: snel, leesbaar, één tik om te bellen.'],
                ['title' => 'Zelf bijhouden, of wij doen het',      'text' => 'Nieuw seizoensassortiment of een gewijzigde openingstijd zet je zelf erop. Kom je er niet aan toe, dan doen wij het.'],
            ],
        ],
    ],

    'webshop' => [
        'hero' => [
            'eyebrow' => 'Webshop voor bakkerijen',
            'title'   => 'Online bestellen bij je bakkerij, voor afhalen of bezorgen',
            'sub'     => 'Brood, banket, taarten en belegde broodjes online laten bestellen, met een afhaaldatum en -tijd die past bij je productie. Vooraf betaald, dus geen bestellingen die blijven liggen.',
            'note'    => 'Gratis en vrijblijvend een voorbeeld van jóuw webshop, vaak binnen 1 à 2 dagen',
            'usps'    => [
                'Klant kiest zelf afhaaldatum en -tijd, binnen jouw grenzen',
                'Vooraf betalen met iDEAL; zakelijke klanten op rekening',
                'Bestellingen komen binnen op een lijst, niet via briefjes en WhatsApp',
            ],
        ],
        'pains' => [
            ['title' => 'Taartbestellingen via WhatsApp, mail en briefjes',
             'text'  => 'Wie heeft wat besteld voor zaterdag, en is er al betaald? Dat hoort niet in drie verschillende apps en een schrift naast de kassa te staan.'],
            ['title' => 'Bestellingen die niet worden opgehaald',
             'text'  => 'Een taart die wel is gemaakt en niet is afgehaald, is gewoon verlies. Met vooraf betalen gebeurt dat niet meer.'],
            ['title' => 'Feestdagen zijn chaos',
             'text'  => 'Rond Sinterklaas, kerst en Pasen loopt de telefoon over. Een webshop neemt die bestellingen aan terwijl jij bakt, en sluit vanzelf als de productie vol is.'],
        ],
        'zkhw' => [
            'label'        => 'Webshop',
            'title'        => 'Zo kan het worden: bestellen zonder dat jij aan de telefoon hangt',
            'intro'        => 'Klanten bestellen brood, gebak of een taart, kiezen wanneer ze het ophalen of laten bezorgen en rekenen af. Jij ziet elke ochtend wat er gemaakt moet worden.',
            'brand'        => 'Kruimel',
            'heroTitle'    => 'Bestel online, bezorgd of afgehaald',
            'urlLabel'     => 'shop.jouw-bakkerij.nl',
            'navCta'       => 'Winkelmand',
            'heroBtn'      => 'In winkelmand',
            'ctaLabel'     => 'Bekijk het webshop-voorbeeld',
            'imageSlot'    => 'webshop-preview',
            'galleryLabel' => 'Populair in de winkel',
            'gallery'      => [['slot' => 'gallery4', 'price' => 'vanaf € 24,95'], ['slot' => 'gallery5', 'price' => 'vanaf € 39'], ['slot' => 'gallery6', 'price' => '€ 25']],
            'bullets'      => [
                ['title' => 'Afhaaldatum en -tijd',          'text' => 'De klant kiest een moment dat jij toestaat: minimaal een dag vooruit voor taarten, tot de ochtend zelf voor brood. Per product instelbaar.'],
                ['title' => 'Vooraf betalen met iDEAL',       'text' => 'Direct afgerekend via Mollie. Vaste zakelijke klanten kunnen op rekening bestellen als jij dat toestaat.'],
                ['title' => 'Bezorgen in je eigen gebied',    'text' => 'Bezorgen op postcode of afhalen in de winkel. Bezorgkosten en -dagen bepaal jij.'],
                ['title' => 'Vaste klanten bestellen opnieuw', 'text' => 'Eerdere bestelling herhalen in een paar tikken; gegevens en voorkeuren staan al klaar.'],
            ],
        ],
    ],

    'klantenportaal' => [
        'hero' => [
            'eyebrow' => 'Klantenportaal voor zakelijke klanten',
            'title'   => 'Klantenportaal voor je bakkerij: zakelijke klanten bestellen zelf',
            'sub'     => 'Zonder bellen, mailen of WhatsApp: horeca, kantoren, lunchrooms en andere vaste klanten loggen in, zien hun vaste bestelling, passen aantallen aan, bestellen extra en vinden hun facturen. Jij typt niets meer over.',
            'note'    => 'Gratis en vrijblijvend een voorbeeld van jóuw portaal, vaak binnen 1 à 2 dagen',
            'usps'    => [
                'Vaste bestelling bekijken, aanpassen en herhalen',
                'Facturen en bestelhistorie op één plek',
                'Eigen assortiment en prijzen per klant, als jij dat wilt',
            ],
        ],
        'pains' => [
            ['title' => 'Het restaurant belt elke avond de bestelling door',
             'text'  => 'Zes stokbroden, twintig bolletjes, morgen twee extra. Aan de telefoon, half verstaanbaar, en jij schrijft het op een briefje.'],
            ['title' => 'Elke wijziging is een mailtje',
             'text'  => 'Vrijdag geen levering, maandag dubbel. Dat komt per mail, WhatsApp of via een medewerker, en niet altijd bij degene die de productielijst maakt.'],
            ['title' => 'Facturen zoeken kost tijd',
             'text'  => 'De zakelijke klant belt om een factuur van twee maanden terug. Die hoort hij zelf te kunnen vinden.'],
        ],
        'zkhw' => [
            'label'        => 'Klantenportaal',
            'title'        => 'Zo kan het worden: je zakelijke klanten regelen het zelf',
            'intro'        => 'Elke zakelijke klant heeft een eigen inlog. Daar staat de vaste bestelling, daar past hij aantallen aan of bestelt extra, en daar staan zijn facturen. Wat hij invoert, staat bij jou op de lijst.',
            'brand'        => 'Kruimel',
            'heroTitle'    => 'Uw vaste bestelling, aanpassen en herhalen',
            'urlLabel'     => 'mijn.jouw-bakkerij.nl',
            'navCta'       => 'Inloggen',
            'heroBtn'      => 'Mijn bestellingen',
            'ctaLabel'     => 'Bekijk het portaal-voorbeeld',
            'imageSlot'    => 'klantenportaal-preview',
            'galleryLabel' => 'Wat een zakelijke klant ziet',
            'gallery'      => [['slot' => 'gallery1'], ['slot' => 'gallery2'], ['slot' => 'gallery3']],
            'bullets'      => [
                ['title' => 'Vaste bestelling',              'text' => 'De standaardbestelling per leverdag staat klaar. Aanpassen tot een tijdstip dat jij bepaalt.'],
                ['title' => 'Extra bestellen of overslaan',  'text' => 'Een feest op het kantoor of een week dicht: de klant regelt het zelf, jij ziet het op de lijst.'],
                ['title' => 'Aflevermoment',                 'text' => 'De klant ziet wanneer er geleverd wordt en kan een afwijkende dag vragen.'],
                ['title' => 'Facturen en historie',          'text' => 'Alle facturen en eerdere bestellingen om te bekijken en te downloaden.'],
                ['title' => 'Eigen prijzen per klant',       'text' => 'Zakelijke prijzen of een eigen assortiment per klant, als jouw administratie dat zo heeft ingericht.'],
                ['title' => 'Maatwerk op jouw proces',       'text' => 'Hoe het portaal werkt hangt af van hoe jij levert en factureert. Dat richten we samen in; het is geen standaardpakket.'],
            ],
        ],
    ],

    'automatisering' => [
        'hero' => [
            'eyebrow' => 'Automatisering voor bakkerijen',
            'title'   => 'Minder overtypen, minder fouten, minder terugkerend werk',
            'sub'     => 'Een bestelling komt binnen en staat vanzelf op de productielijst, bij de juiste vestiging en in de administratie. Niet drie keer overtypen, niet vergeten.',
            'note'    => 'We beginnen met wat jou nu de meeste tijd kost; de rest komt later',
            'usps'    => [
                'Webshopbestelling → betaling → bevestiging → productielijst',
                'Zakelijke bestelling → factuur, zonder handwerk',
                'Bestelling voor morgen → pick- of afhaallijst per vestiging',
            ],
        ],
        'pains' => [
            ['title' => 'Dezelfde bestelling drie keer overgetypt',
             'text'  => 'Eén keer uit de mail, één keer op de productielijst, één keer in de boekhouding. Elke keer kan er een nul te veel of een datum verkeerd in.'],
            ['title' => 'De ochtendlijst klopt niet',
             'text'  => 'Een bestelling van gisteravond laat staat er niet op, of staat bij de verkeerde winkel. Dat merk je pas als de klant voor de balie staat.'],
            ['title' => 'Facturen achteraf inkloppen',
             'text'  => 'Aan het eind van de week alle zakelijke leveringen alsnog in de administratie zetten. Werk dat een systeem beter en op tijd kan doen.'],
        ],
        'zkhw' => [
            'label'        => 'Automatisering',
            'title'        => 'Zo kan het worden: de bestelling loopt vanzelf door',
            'intro'        => 'Een bestelling komt binnen via je webshop, portaal of telefoon en loopt door naar bevestiging, productie, aflevering en factuur. Jij kijkt op één lijst wat er vandaag gemaakt moet worden.',
            'brand'        => 'Kruimel',
            'heroTitle'    => 'Bestellingen, productie en facturen in één overzicht',
            'urlLabel'     => 'app.jouw-bakkerij.nl',
            'navCta'       => 'Dashboard',
            'heroBtn'      => 'Bekijk demo',
            'ctaLabel'     => 'Bekijk het automatisering-voorbeeld',
            'imageSlot'    => 'automatisering-preview',
            'galleryLabel' => 'Wat er automatisch loopt',
            'gallery'      => [['slot' => 'gallery4', 'price' => 'Productielijst ✓'], ['slot' => 'gallery5', 'price' => 'Factuur ✓'], ['slot' => 'gallery6', 'price' => 'Bevestiging ✓']],
            'bullets'      => [
                ['title' => 'Van bestelling naar productielijst', 'text' => 'Alles wat voor morgen besteld is, per product en per vestiging, elke ochtend klaar. Ook wat via de telefoon is aangenomen.'],
                ['title' => 'Bevestiging naar de klant',           'text' => 'Bestelling ontvangen, betaling gelukt, staat klaar om af te halen: de klant krijgt vanzelf bericht, jij belt niet.'],
                ['title' => 'Zakelijke bestelling wordt factuur',  'text' => 'Leveringen aan vaste klanten worden per week of per maand gebundeld tot een factuur, zonder dat iemand ze intikt.'],
                ['title' => 'Klantgegevens op één plek',           'text' => 'Nieuwe klanten uit de webshop en het portaal komen in je administratie terecht; geen dubbele adressen.'],
                ['title' => 'Koppelingen met wat je al gebruikt',  'text' => 'Betalingen via Mollie werken standaard. Voor je kassasysteem of boekhouding (zoals Exact Online, e-Boekhouden of Moneybird) bekijken we wat er mogelijk is; dat verschilt per pakket.'],
            ],
        ],
    ],

    // De ai-facet is de korte pagina in het groeipad. De volledige uitleg, voorbeeldgesprekken,
    // prijs en demo staan op /ai-telefonie-bakkerij (config/bakkerij_telefonie.php).
    'ai' => [
        'hero' => [
            'eyebrow' => 'AI-telefonie',
            'title'   => 'De telefoon wordt opgenomen, ook als jij in de bakkerij staat',
            'sub'     => 'Een telefonische assistent die openingstijden geeft, vragen over je assortiment en bestellen beantwoordt, een terugbelverzoek vastlegt en doorverbindt als het moet. Ook buiten openingstijden.',
            'note'    => 'Werkt naast je huidige nummer; je hoeft niets te veranderen aan je website',
            'usps'    => [
                'Neemt op tijdens de ochtenddrukte en buiten openingstijden',
                'Beantwoordt de vragen die je elke dag krijgt',
                'Na elk gesprek een samenvatting per mail',
            ],
        ],
        'pains' => [
            ['title' => 'De telefoon gaat als je met deeg in je handen staat',
             'text'  => 'Zaterdagochtend, rij tot de deur, en de telefoon blijft gaan. Wie niet wordt opgenomen belt de volgende bakker.'],
            ['title' => 'Steeds dezelfde vragen',
             'text'  => '"Zijn jullie vandaag open?", "Hebben jullie glutenvrij brood?", "Tot hoe laat kan ik ophalen?" Vragen die een assistent net zo goed kan beantwoorden.'],
            ['title' => 'Terugbellen schiet erbij in',
             'text'  => 'Na sluitingstijd staan er drie gemiste oproepen zonder naam. De assistent noteert wie belde en waarover, zodat jij weet wie je terugbelt.'],
        ],
        'zkhw' => [
            'label'        => 'AI-telefonie',
            'title'        => 'Zo kan het worden: opgenomen, beantwoord, samengevat',
            'intro'        => 'De assistent neemt op met de naam van je bakkerij, beantwoordt praktische vragen uit jouw eigen gegevens, legt een terugbelverzoek of bestelverzoek vast met naam en nummer, en verbindt door als een medewerker nodig is. Jij krijgt na elk gesprek een samenvatting.',
            'brand'        => 'Kruimel',
            'heroTitle'    => 'Goedemorgen, bakkerij Kruimel. Waarmee kan ik u helpen?',
            'urlLabel'     => 'jouw-bakkerij.nl',
            'navCta'       => 'Bel ons',
            'heroBtn'      => 'Terugbelverzoek',
            'ctaLabel'     => 'Alles over AI-telefonie voor bakkerijen',
            'ctaUrl'       => 'ai-telefonie-bakkerij',
            'imageSlot'    => 'ai-preview',
            'galleryLabel' => 'Wat de assistent afhandelt',
            'gallery'      => [['slot' => 'gallery1', 'price' => 'Openingstijden ✓'], ['slot' => 'gallery2', 'price' => 'Terugbelverzoek ✓'], ['slot' => 'gallery3', 'price' => 'Doorverbinden ✓']],
            'bullets'      => [
                ['title' => 'Neemt op als jij niet kunt',      'text' => 'Tijdens de drukte, tijdens het bakken en buiten openingstijden. In het Nederlands; Engels kan erbij als je dat wilt.'],
                ['title' => 'Antwoordt uit jouw gegevens',      'text' => 'Openingstijden, vestigingen, assortiment, bestellen en afhalen: wat jij aanlevert, kan de assistent vertellen. Weet hij iets niet, dan zegt hij dat en legt hij een terugbelverzoek vast.'],
                ['title' => 'Legt vast wie belde en waarom',    'text' => 'Naam, telefoonnummer en de vraag, herhaald ter controle. Een bestelverzoek voor een taart komt zo netjes bij jou terecht.'],
                ['title' => 'Verbindt door als het moet',       'text' => 'Tijdens openingstijden kan de assistent doorverbinden naar de winkel. Na elk gesprek krijg jij een samenvatting per mail.'],
            ],
        ],
    ],
];
