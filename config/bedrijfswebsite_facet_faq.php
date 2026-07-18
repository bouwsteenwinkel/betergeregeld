<?php

/*
|--------------------------------------------------------------------------
| FAQ per facet — bedrijfswebsite
|--------------------------------------------------------------------------
|
| Zichtbare FAQ + FAQPage-schema op de facet-landingspagina's
| (/website, /webshop, /klantenportaal, /automatisering, /ai). Q&A zijn
| kort en concreet: goed voor bezoekers, voor rich results en voor GEO
| (AI-antwoordmachines citeren graag Q&A). Gerenderd door
| channels/_landing/bedrijfswebsite.blade.php.
|
*/

return [

    'website' => [
        ['q' => 'Wat kost een website laten maken?', 'a' => 'Een website begint bij 799 euro eenmalig plus 39 euro per maand voor hosting en onderhoud, of bij 69 euro per maand met een looptijd van 24 maanden. Je weet het bedrag vooraf en het verandert niet.'],
        ['q' => 'Hoe snel staat mijn website online?', 'a' => 'Een eenpaginawebsite staat meestal binnen een week live. Een uitgebreidere website met een pagina per dienst staat binnen twee tot drie weken online.'],
        ['q' => 'Moet ik zelf teksten en foto\'s aanleveren?', 'a' => 'Nee. Wij schrijven de teksten en richten je Google-profiel in. Heb je eigen foto\'s of teksten, dan gebruiken we die het liefst, want echte beelden van je eigen bedrijf werken beter bij bezoekers en in Google.'],
    ],

    'webshop' => [
        ['q' => 'Wat kan ik verkopen via een webshop?', 'a' => 'Je biedt producten, pakketten en cadeaubonnen online aan, direct af te rekenen. De webshop past bij jouw manier van werken: bezorgen, afhalen of allebei.'],
        ['q' => 'Kan een webshop meegroeien met mijn bestaande website?', 'a' => 'Ja. De webshop koppelt aan je website, zodat bezoekers naadloos van informatie naar bestellen gaan. Je kunt klein beginnen en later uitbreiden.'],
        ['q' => 'Welke betaalmethodes zijn mogelijk?', 'a' => 'Gangbare methodes zoals iDEAL en creditcard via een betaalprovider, zodat klanten veilig en vertrouwd afrekenen.'],
    ],

    'klantenportaal' => [
        ['q' => 'Wat kunnen klanten in een klantenportaal doen?', 'a' => 'Klanten plannen zelf hun afspraak, volgen de status van hun project en vinden documenten en facturen op één plek. Dat scheelt jou telefoontjes en mailtjes.'],
        ['q' => 'Scheelt een klantenportaal me echt tijd?', 'a' => 'Ja. De meeste vragen gaan over planning en status. Als klanten dat zelf kunnen inzien, houd je uren per week over voor echt werk.'],
        ['q' => 'Is een klantenportaal veilig?', 'a' => 'Ja. Elke klant heeft een eigen beveiligde inlog en ziet alleen zijn eigen gegevens.'],
    ],

    'automatisering' => [
        ['q' => 'Wat kan ik in mijn bedrijf automatiseren?', 'a' => 'Offertes, planning, facturen en betaalherinneringen. Een aanvraag wordt een offerte, en facturen en herinneringen gaan er automatisch uit, zonder dubbel typwerk.'],
        ['q' => 'Moet ik mijn huidige systemen vervangen?', 'a' => 'Nee. We koppelen wat je al gebruikt, zodat gegevens tussen je website, agenda en boekhouding doorstromen in plaats van dat je alles dubbel invoert.'],
        ['q' => 'Wat levert automatisering op?', 'a' => 'Minder handwerk, minder fouten en tijdwinst. Ondernemers besparen er vaak meerdere uren per week mee.'],
    ],

    'ai' => [
        ['q' => 'Wat doet een AI-assistent voor mijn bedrijf?', 'a' => 'De assistent neemt telefoon en chat aan, vraagt door naar wat de klant nodig heeft en bereidt je offerte voor, zodat je nooit meer een aanvraag mist.'],
        ['q' => 'Vervangt AI mijn eigen werk?', 'a' => 'Nee. Jij blijft de expert en houdt de controle. De AI doet het voorwerk, jij checkt en verstuurt. Bij een correctie leert het systeem mee.'],
        ['q' => 'Is AI betrouwbaar voor klantcontact?', 'a' => 'De AI kwalificeert aanvragen en zet een concept klaar; jij controleert voordat er iets de deur uitgaat. Zo combineer je snelheid met jouw vakkennis.'],
    ],

];
