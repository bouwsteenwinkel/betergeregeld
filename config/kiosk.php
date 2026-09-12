<?php

// Kiosk-schermen: alleen-lezen weergaven voor een permanent scherm (Raspberry Pi)
// zonder login. Toegang met een geheime sleutel in de URL (?key=<sleutel>).
// Alleen de SHA-256-hash van de sleutel staat hier; de sleutel zelf zit alleen in
// de URL op het scherm. Sleutel intrekken = deze hash leegmaken (of vervangen)
// en opnieuw deployen (config:cache).
return [
    'token_hash' => env('KIOSK_TOKEN_HASH', '7bd86e1b7d39572c4a34ad8fef7fb274e0e2f9b576e3cec4bab60646933f1f28'),
];
