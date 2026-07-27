<?php

/**
 * Etalage op de homepage van jouw-bedrijfswebsite.nl: echte sites die we hebben gebouwd.
 *
 * De beelden zijn schermafdrukken van de pagina zelf (geen browserbalk eromheen), gemaakt met
 * `node scripts/site-screenshots.mjs opdrachten.json --zichtbaar` en daarna door
 * ChannelImageGenerator::optimize() omgezet naar webp-varianten. Ze staan in
 * public/channel-media/bedrijfswebsite/ als werk-<slug>-<n>.png + -480/-800/-1200.webp.
 *
 * Minimaal drie beelden per site: de kaart wisselt ze om de paar seconden af.
 *
 * `url` is bewust leeg voor sites die nog op een testomgeving staan — dan toont de kaart geen
 * link. Zet 'm pas als de site op zijn eigen domein live staat.
 */
return [
    'titel' => 'Dit hebben we gemaakt',
    'intro' => 'Van webshop tot vacaturebank. Schuif erdoorheen en kijk zelf.',

    'items' => [
        [
            'slug' => 'bouwsteenwinkel',
            'naam' => 'Bouwsteenwinkel',
            'soort' => 'Webshop + verhuur',
            'tekst' => 'Een webwinkel waar klanten LEGO-sets huren in plaats van kopen. Met eigen '
                .'voorraadbeheer, een klantenportaal en een abonnement, allemaal aan elkaar geknoopt.',
            'url' => 'https://www.bouwsteenwinkel.nl/',
            'beelden' => [
                ['slot' => 'werk-bouwsteenwinkel-1', 'alt' => 'Startpagina van Bouwsteenwinkel'],
                ['slot' => 'werk-bouwsteenwinkel-2', 'alt' => 'Het setoverzicht met filters op Bouwsteenwinkel'],
                ['slot' => 'werk-bouwsteenwinkel-3', 'alt' => 'Een setpagina op Bouwsteenwinkel'],
            ],
        ],
        [
            'slug' => '24werk',
            'naam' => '24Werk',
            'soort' => 'Vacaturebank',
            'tekst' => 'Duizenden vacatures, doorzoekbaar op plaats, vakgebied en dienstverband. '
                .'Werkgevers plaatsen zelf, kandidaten solliciteren direct.',
            'url' => 'https://24werk.com/',
            'beelden' => [
                ['slot' => 'werk-24werk-1', 'alt' => 'Startpagina van 24Werk'],
                ['slot' => 'werk-24werk-2', 'alt' => 'Het vacatureoverzicht met filters op 24Werk'],
                ['slot' => 'werk-24werk-3', 'alt' => 'Een vacaturepagina op 24Werk'],
            ],
        ],
        [
            'slug' => 'hemkes',
            'naam' => 'Hemkes Maritime',
            'soort' => 'Webshop, techniek',
            'tekst' => 'Communicatie- en stroomtechniek voor de scheepvaart. Een uitgebreid '
                .'assortiment, zakelijk bestellen en een kenniscentrum met uitleg per product.',
            'url' => '',
            'beelden' => [
                ['slot' => 'werk-hemkes-1', 'alt' => 'Startpagina van Hemkes Maritime'],
                ['slot' => 'werk-hemkes-2', 'alt' => 'Een categoriepagina van Hemkes Maritime'],
                ['slot' => 'werk-hemkes-3', 'alt' => 'Het kenniscentrum van Hemkes Maritime'],
            ],
        ],
        [
            'slug' => 'fiftycal',
            'naam' => 'Fifty-Cal',
            'soort' => 'Webshop, kleding',
            'tekst' => 'Een eigen kledingmerk met een strakke collectiepagina, duidelijke '
                .'productpagina\'s en een afrekening die gewoon werkt.',
            'url' => '',
            'beelden' => [
                ['slot' => 'werk-fiftycal-1', 'alt' => 'Startpagina van Fifty-Cal'],
                ['slot' => 'werk-fiftycal-2', 'alt' => 'De collectiepagina van Fifty-Cal'],
                ['slot' => 'werk-fiftycal-3', 'alt' => 'Een productpagina van Fifty-Cal'],
            ],
        ],
    ],
];
