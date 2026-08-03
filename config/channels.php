<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Meld-adres voor nieuwe leads van de kanaalsites
    |--------------------------------------------------------------------------
    |
    | Waar de interne melding heen gaat zodra iemand op een kanaalsite een
    | voorbeeld aanvraagt of de intake-wizard doorloopt.
    |
    | Dit bestand bestond niet, terwijl de code al op `channels.lead_mail_to`
    | leunde (PreviewToolController). De aanroep viel dus altijd terug op
    | `mail.from.address` — wat werkt, maar niemand had gekozen; het adres was
    | een bijproduct van de afzenderinstelling. Nu expliciet, en per omgeving
    | te zetten met CHANNELS_LEAD_MAIL_TO.
    |
    | Blijft leeg? Dan valt hij nog steeds terug op het afzenderadres, en pas
    | als ook dát leeg is gaat er niets uit (met een regel in het log, zodat een
    | gemiste lead niet ongemerkt verdwijnt).
    |
    */

    'lead_mail_to' => env('CHANNELS_LEAD_MAIL_TO', env('MAIL_FROM_ADDRESS')),

];
