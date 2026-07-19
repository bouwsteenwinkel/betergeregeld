<?php

/**
 * "Dit zit er allemaal in" — de ondersteunende diensten die standaard meekomen
 * bij een website/pakket van Betergeregeld. Bewust puur als geruststelling
 * gepresenteerd (geen losse verkoop, geen prijzen): bevestig dat we het doen
 * EN goed doen, zodat de bezoeker geen stille twijfels overhoudt.
 *
 * Gerenderd door resources/views/channels/partials/included-services.blade.php
 * op de channel-home's en de bedrijfswebsite-salespagina.
 *
 * icon = emoji (asset-vrij). title = korte naam. text = één zin "zo doen wij dit".
 */

return [

    'heading'  => 'Alles zit erin. Dit regelen wij voor je.',
    'intro'    => 'Je hoeft niets apart te regelen, te betalen of bij te houden. Het onderstaande hoort er standaard bij, en we doen het goed.',
    'footnote' => 'Standaard inbegrepen in je pakket. Je hebt er geen omkijken naar.',

    'items' => [
        [
            'icon'  => 'server',
            'title' => 'Hosting',
            'text'  => 'Snelle, Nederlandse hosting die we zelf beheren en bewaken. Je site is snel en blijft in de lucht.',
        ],
        [
            'icon'  => 'mail',
            'title' => 'E-mailhosting',
            'text'  => 'Professionele e-mail op je eigen domein, netjes ingesteld en betrouwbaar bezorgd.',
        ],
        [
            'icon'  => 'shield',
            'title' => 'SSL en beveiliging',
            'text'  => 'Een geldig slotje en actieve beveiliging tegen misbruik. Bezoekers en Google zien dat je site veilig is.',
        ],
        [
            'icon'  => 'save',
            'title' => 'Back-ups en herstel',
            'text'  => 'Automatische back-ups, zodat er nooit iets verloren gaat. Mocht er iets misgaan, dan zetten we het snel terug.',
        ],
        [
            'icon'  => 'wrench',
            'title' => 'Updates en onderhoud',
            'text'  => 'Wij houden alles up-to-date en draaiend op de achtergrond. Jij hebt er geen omkijken naar.',
        ],
        [
            'icon'  => 'search',
            'title' => 'SEO-optimalisatie',
            'text'  => 'Je site is technisch op orde en vindbaar in Google, met de juiste structuur, snelheid en teksten.',
        ],
        [
            'icon'  => 'globe',
            'title' => 'Domeinbeheer',
            'text'  => 'We regelen en beheren je domeinnaam en alle instellingen. Eén aanspreekpunt, geen gedoe met losse partijen.',
        ],
        [
            'icon'  => 'link',
            'title' => 'Koppelingen en integraties',
            'text'  => 'Koppelingen met je agenda, boekhouding of betaalprovider, zodat je systemen met elkaar praten.',
        ],
        [
            'icon'  => 'bolt',
            'title' => 'Performance en snelheid',
            'text'  => 'We houden laadtijd en mobiel gebruik scherp. Een snelle site houdt bezoekers vast en scoort beter.',
        ],
        [
            'icon'  => 'language',
            'title' => 'Meertaligheid en toegankelijkheid',
            'text'  => 'Meertalig waar nodig en toegankelijk voor iedereen, ook op mobiel en met een screenreader.',
        ],
    ],

];
