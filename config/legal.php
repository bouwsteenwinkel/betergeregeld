<?php

/**
 * Juridische/bedrijfsgegevens van de exploitant van de trigger-sites. Gebruikt
 * door de generieke juridische pagina's (privacy, cookies, voorwaarden).
 *
 * ⚠️ VUL DE LEGE VELDEN IN vóór go-live (KvK, statutaire naam, adres, BTW) en
 * laat de teksten door jezelf/een jurist nakijken — dit is een nette basis, geen
 * juridisch advies.
 */

return [
    'operator'   => 'Betergeregeld ICT',            // handelsnaam die de sites exploiteert
    'legal_name' => 'Beter Geregeld ICT',           // statutaire naam — CONTROLEER
    'kvk'        => '',                              // VUL IN (KvK-nummer)
    'btw'        => '',                              // VUL IN (BTW-nummer, optioneel)
    'address'    => '',                             // VUL IN (vestigingsadres)
    'email'      => 'hallo@betergeregeld.nl',
    'phone'      => '085 1303 600',
    'website'    => 'https://betergeregeld.nl',
    'ap_url'     => 'https://www.autoriteitpersoonsgegevens.nl',
    'updated'    => 'juli 2026',                     // "laatst bijgewerkt" — met de hand bijwerken
];
