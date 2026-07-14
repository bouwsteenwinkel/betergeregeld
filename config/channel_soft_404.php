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
    'bedrijfswebsite',
];
