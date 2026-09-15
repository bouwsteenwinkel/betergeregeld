<?php

/*
|--------------------------------------------------------------------------
| Kanalen waarvan de plaatsenpagina's niet geïndexeerd worden
|--------------------------------------------------------------------------
|
| Voor de hier genoemde channel-site-keys krijgen /plaatsen, de provincie-
| overzichten en alle /plaatsen/{plaats}-pagina's `noindex,follow`, en ze
| verdwijnen uit de sitemap. De pagina's zelf blijven bestaan en bereikbaar.
|
| Waarom (15-09-2026, gemeten in Search Console over 56 dagen): bij kanalen
| waar de branchenaam samenvalt met wat consumenten zoeken, trekken de
| plaatsenpagina's vrijwel alleen consumenten en bedrijfsnamen — geen
| ondernemers die een website willen. Aandeel vertoningen op ondernemers-
| termen ("website laten maken ...", "ai telefoniste ..."):
|
|   uitlaat-remmen         0%   (2.677 vert., waarvan 2.537 "autovisie alphen aan den rijn")
|   administratiekantoor   0%   (107)
|   architect              0%   (14)
|   apotheek               2%   (983, "apotheek oisterwijk" enz.)
|   acupuncturist          3%   (162)
|   loodgieter            14%   (1.228, "loodgieter amstelveen" enz.)
|   badkamerspecialist    16%   (2.193)
|   klusbedrijf           25%   (102)
|
| Ter vergelijking: bakkerij 66%, rijschool 58%, yogastudio 52%, dietist 45% —
| daar doen de plaatsenpagina's wél hun werk en blijven ze geïndexeerd.
|
| Nog LEEG: de channel-teksten zitten tot de hermeting van 4-18 oktober 2026 in
| een meting die niet verstoord mag worden. Besluit (Dennis, 15-09-2026): na die
| hermeting de acht keys hierboven hier inzetten. Een deploy is dan het enige
| dat nodig is; robots-tags en sitemap volgen automatisch.
|
*/

return [
    // 'uitlaat-remmen',
    // 'administratiekantoor',
    // 'architect',
    // 'apotheek',
    // 'acupuncturist',
    // 'loodgieter',
    // 'badkamerspecialist',
    // 'klusbedrijf',
];
