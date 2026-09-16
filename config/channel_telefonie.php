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
| 16-09-2026: alle 17 live kanalen, één view (channels/_landing/telefonie) met per kanaal
| een eigen config plus config/telefonie_basis.php; zie App\Support\TelefonieConfig.
|
*/

return [
    'bakkerij'             => 'bakkerij_telefonie',
    'loodgieter'           => 'loodgieter_telefonie',
    'autogarage'           => 'autogarage_telefonie',
    'uitlaat-remmen'       => 'uitlaat-remmen_telefonie',
    'aannemer'             => 'aannemer_telefonie',
    'badkamerspecialist'   => 'badkamerspecialist_telefonie',
    'klusbedrijf'          => 'klusbedrijf_telefonie',
    'rijschool'            => 'rijschool_telefonie',
    'golfschool'           => 'golfschool_telefonie',
    'yogastudio'           => 'yogastudio_telefonie',
    'acupuncturist'        => 'acupuncturist_telefonie',
    'apotheek'             => 'apotheek_telefonie',
    'dietist'              => 'dietist_telefonie',
    'administratiekantoor' => 'administratiekantoor_telefonie',
    'advocaat'             => 'advocaat_telefonie',
    'architect'            => 'architect_telefonie',
    'bedrijfswebsite'      => 'bedrijfswebsite_telefonie',
];
