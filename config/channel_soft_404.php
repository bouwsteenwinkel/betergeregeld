<?php

/*
|--------------------------------------------------------------------------
| Kanalen die 404's naar hun eigen homepage sturen
|--------------------------------------------------------------------------
|
| Voor de hier genoemde channel-site-keys wordt een verkeerde of verwijderde
| URL (geblokkeerde pagina, onbekende URL, niet-bestaande plaats/blog-slug)
| niet als foutpagina getoond, maar als redirect naar de eigen homepage.
|
| Alle andere kanalen houden hun normale 404 (nette not-found-pagina).
| Werkt samen met config/channel_page_blocklist.php.
|
*/

return [
    // Leeg: alle kanalen tonen nu een echte 404 (nette not-found-pagina) i.p.v.
    // een redirect naar de eigen homepage. 'bedrijfswebsite' is hier verwijderd
    // (2026-07-18) omdat soft-404's naar home door Google als soft-404 worden
    // aangemerkt en crawl-budget verspillen. Zet een channel-key terug als je
    // voor dat kanaal bewust UX (home) boven SEO-correctheid (404) verkiest.
];
