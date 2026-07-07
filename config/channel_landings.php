<?php

/**
 * Koppelt een channel-site-key aan z'n landings-config (de facet-content voor de
 * bespoke _sales/_landing-pagina's, twee-lagen-model).
 *
 * Een nieuwe niche dezelfde structuur geven als badkamerspecialist:
 *   1) voeg hier  <site-key> => '<config-bestandsnaam>'  toe;
 *   2) maak  config/<config-bestandsnaam>.php  (zelfde opzet als badkamer_landings.php);
 *   3) maak  resources/views/channels/_sales/<site-key>.blade.php
 *        en  resources/views/channels/_landing/<site-key>.blade.php.
 */

return [
    'badkamerspecialist' => 'badkamer_landings',
    'rijschool'          => 'rijschool_landings',
    'klusbedrijf'        => 'klusbedrijf_landings',
    'loodgieter'         => 'loodgieter_landings',
];
