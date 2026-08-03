<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kanalen waar de voorbeeldsite wordt AANGEVRAAGD in plaats van gegenereerd
    |--------------------------------------------------------------------------
    |
    | De tool die binnen een minuut een concept neerzet blijft bestaan — code,
    | routes en al. Voor de kanalen hieronder komt er alleen een ander formulier
    | vóór: de bezoeker vraagt een voorbeeld aan en krijgt het binnen één
    | werkdag, gemaakt door een mens.
    |
    | Waarom parkeren en niet weggooien: de tool werkt, en een gegenereerd
    | concept is een sterk verkoopmiddel zodra we het als startpunt gebruiken in
    | plaats van als eindproduct. Zet een kanaal uit deze lijst en de oude flow
    | staat er meteen weer.
    |
    | De tool zelf blijft bereikbaar met ?tool=1, zodat we hem intern kunnen
    | blijven gebruiken om een eerste opzet te maken vóór het belletje.
    |
    */

    'kanalen' => ['bedrijfswebsite'],

    /*
    | Wat we beloven op het formulier en de bevestiging. Op één plek, zodat de
    | belofte op de pagina, in de bevestigingsmail en in de interne melding
    | nooit uit elkaar kan lopen.
    */
    'levertijd' => 'binnen 1 werkdag',

];
