<?php

/*
|--------------------------------------------------------------------------
| Kanalen met een eigen AI-telefonie-landingspagina
|--------------------------------------------------------------------------
|
| key => configbestand. Voor een kanaal in deze lijst bestaat
| /ai-telefonie-{key} (view channels/_landing/{key}-telefonie), staat de pagina
| in de sitemap en in llms.txt, en verwijst de ai-facet ernaar.
| Kanalen die hier niet in staan krijgen op dat pad een 404.
|
| 15-09-2026: bakkerij als eerste (docs/bakkerij/SEO-CONTENT-AUDIT.md).
|
*/

return [
    'bakkerij' => 'bakkerij_telefonie',
];
