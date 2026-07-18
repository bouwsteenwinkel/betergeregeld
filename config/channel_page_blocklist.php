<?php

/*
|--------------------------------------------------------------------------
| Per-kanaal pagina-blocklist
|--------------------------------------------------------------------------
|
| De channel-site routes zijn gedeeld door alle kanalen (routes/channels.php).
| Met deze lijst maak je specifieke pagina's ONbereikbaar voor één kanaal,
| zonder de gedeelde routes/views voor de andere kanalen te raken. De
| BlockChannelPages-middleware geeft een 404 op het eerste padsegment als dat
| in de lijst voor het actieve kanaal staat.
|
| Belangrijk:
|  - Alleen "veilige" requests (GET/HEAD) worden geblokkeerd. POST-endpoints
|    (zoals POST /contact) blijven altijd werken.
|  - /bedankt is de succespagina van het lead-formulier. Blokkeer 'm ALLEEN als
|    geen enkele pagina van dat kanaal het lead-formulier (lead-wizard) meer toont.
|    Voor bedrijfswebsite is dat zo: alle CTA's gaan naar de voorbeeld-tool, die
|    direct een concept toont, dus /bedankt is daar overbodig.
|  - Een kanaal zonder entry hier wordt volledig ongemoeid gelaten.
|
| De sleutel is de channel-site-key; de waarde is een lijst van eerste
| padsegmenten (dus 'plaatsen' dekt ook '/plaatsen/...').
|
*/

return [

    // Betergeregeld eigen marketingsite: uitgeklede one-pager (homepage + tool +
    // plaatsen/blog/legal). De losse content-pagina's en facet-landings staan als
    // secties op de homepage, dus als aparte URL zijn ze verwijderd.
    'bedrijfswebsite' => [
        // blok-demo
        'voorbeeld',
        // bedankpagina van het lead-formulier: op dit kanaal ongebruikt (alle CTA's
        // gaan naar de voorbeeld-tool, die direct een concept toont).
        'bedankt',
        // NB: de facet-landings 'website', 'webshop', 'klantenportaal',
        // 'automatisering' en 'ai' zijn BEWUST NIET geblokkeerd — ze zijn echte,
        // indexeerbare head-term-landingspagina's (channels/_landing/bedrijfswebsite
        // + config/bedrijfswebsite_landings.php).
        // losse content-pagina's (bestaan als secties op de homepage)
        'diensten', 'groeidiamant', 'prijzen', 'werkwijze', 'cases',
        'veelgestelde-vragen', 'vergelijken', 'over-ons',
        // GET /contact (de contactpagina). POST /contact (lead-formulier) blijft
        // werken omdat de middleware alleen veilige requests blokkeert.
        'contact',
    ],

];
