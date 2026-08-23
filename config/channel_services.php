<?php

/**
 * Dienstenbeschrijvingen voor de /diensten-pagina van ÁLLE niche-sites.
 * Generiek en trade-aware via dezelfde tokens als channel_places
 * (:trade / :trades / :niche). Eén bron, rolt in één keer uit naar elke site.
 *
 * De vijf diensten volgen de Groeidiamant-fasen (website → webshop →
 * klantenportaal → automatisering → AI).
 */

return [
    'eyebrow' => 'Onze diensten',
    'h1'      => 'Wat we bouwen voor :trades, van website tot slimme AI',
    'intro'   => 'We leveren niet zomaar een website. We bouwen voor :trades en zorgen dat je online gevonden wordt, dat klanten je makkelijk kunnen bereiken en dat je minder tijd kwijt bent aan administratie. Je begint waar je nu staat en breidt uit wanneer je eraan toe bent. Hieronder lees je precies wat elke stap inhoudt en wat je eraan hebt.',

    'services' => [

        'website' => [
            'label'   => 'Website',
            'icon'    => 'globe',
            'tagline' => 'Je professionele uithangbord dat dag en nacht voor je werkt.',
            'intro'   => 'Je website is vaak het eerste wat een klant van je ziet. Wij bouwen een strakke, snelle site die vertrouwen wekt en meteen duidelijk maakt wat je doet en voor wie. Geen standaard sjabloon, maar een site die past bij jouw :trade en je eigen regio. Zo word je gevonden door mensen die nú op zoek zijn, en weten ze direct hoe ze je bereiken.',
            'bullets' => [
                'Een professioneel ontwerp op maat, geen sjabloon van de plank',
                'Vindbaar in Google als iemand in jouw regio een :niche zoekt',
                'Je vakwerk en projecten mooi in beeld',
                'Duidelijke bel- en aanvraagknoppen op elke pagina',
                'Werkt perfect op mobiel, tablet en desktop',
                'Hosting, onderhoud en updates zitten er gewoon bij',
            ],
            'example' => 'Iemand zoekt \'s avonds op de bank naar een :niche in de buurt. Ze komt op jouw site, ziet foto\'s van eerder werk, leest een paar reviews en klikt op "offerte aanvragen". De volgende ochtend ligt de aanvraag in je mailbox, terwijl jij gewoon aan het werk was.',
        ],

        'webshop' => [
            'label'   => 'Webshop',
            'icon'    => 'cart',
            'tagline' => 'Verkoop online, ook als je showroom dicht is.',
            'intro'   => 'Naast je gewone werk kun je online producten, pakketten of cadeaubonnen verkopen. We koppelen een webshop aan je site met iDEAL en andere betaalmethodes, voorraad en automatische bevestigingen. Klanten bestellen zelf, betalen direct en kiezen bezorgen of afhalen. Jij ziet de bestelling binnenkomen en hoeft niets dubbel te doen.',
            'bullets' => [
                'Betalen met iDEAL, creditcard en meer',
                'Gekoppeld aan je voorraad, geen dubbel bijhouden',
                'Bezorgen of afhalen, klant kiest zelf',
                'Automatische orderbevestiging en factuur',
                'Kortingscodes en cadeaubonnen',
                'Extra diensten zoals montage bij te boeken',
            ],
            'example' => 'Een klant koopt online een product en boekt meteen de plaatsing of montage erbij. Terwijl jij op locatie bent, verdient de webshop zichzelf terug. Je zaak is zo eigenlijk 24 uur per dag open.',
        ],

        'klantenportaal' => [
            'label'   => 'Klantenportaal',
            'icon'    => 'lock',
            'tagline' => 'Laat klanten hun eigen zaken online regelen.',
            'intro'   => 'Een klantenportaal is een beveiligde omgeving waar klanten zelf hun afspraken, offertes, facturen en de status van hun project kunnen bekijken. Dat scheelt jou een hoop telefoontjes en mailtjes, en je klant weet altijd waar hij aan toe is.',
            'bullets' => [
                'Een eigen, beveiligde inlog voor elke klant',
                'Afspraken en planning zelf inzien',
                'Offertes en facturen op één plek',
                'De status van het project live volgen',
                'Documenten en foto\'s veilig delen',
                'Veel minder gebel over "hoe ver is het?"',
            ],
            'example' => 'Een klant wil weten wanneer je langskomt. In plaats van te bellen, logt hij in en ziet hij de planning, het ontwerp en de laatste update. Jij houdt je handen vrij voor het echte werk.',
        ],

        'automatisering' => [
            'label'   => 'Automatisering',
            'icon'    => 'gear',
            'tagline' => 'Laat terugkerende klussen zichzelf doen.',
            'intro'   => 'Bij :trades gaat veel tijd op aan steeds dezelfde handelingen: offertes typen, facturen sturen, afspraken inplannen, herinneringen versturen. We automatiseren die stappen en koppelen ze aan je agenda en boekhouding. Zo verdwijnt een groot deel van je administratie naar de achtergrond, zonder dat er iets tussen wal en schip valt.',
            'bullets' => [
                'Offertes en facturen die automatisch de deur uit gaan',
                'Herinneringen en bevestigingen zonder erover na te denken',
                'Koppeling met je agenda en boekhouding',
                'Klanten zelf een afspraak laten plannen',
                'Reviews automatisch verzamelen na een klus',
                'Minder handwerk en minder kans op fouten',
            ],
            'example' => 'Iemand vraagt een offerte aan. Het systeem stuurt automatisch een nette bevestiging, plant een belafspraak in je agenda en herinnert de klant een dag van tevoren. Jij hoeft alleen nog het gesprek te voeren.',
        ],

        'ai' => [
            'label'   => 'AI',
            'icon'    => 'spark',
            'tagline' => 'Een slimme assistent die met je meewerkt.',
            'intro'   => 'Met AI voegen we slimme hulp toe die je tijd en aandacht bespaart. Denk aan een assistent die de eerste vragen van klanten beantwoordt, je offertes helpt voorbereiden of je telefoon en chat aanneemt als je aan het werk bent. Volledig afgestemd op hoe het er bij :trades aan toegaat, en jij houdt altijd de controle.',
            'bullets' => [
                'Een chat die klantvragen direct beantwoordt',
                'Telefoon en berichten aannemen als je niet kunt opnemen',
                'Offertes en teksten sneller voorbereiden',
                'E-mails en reacties helpen opstellen',
                'Klanten 24/7 te woord staan',
                'Altijd onder jouw controle, jij bepaalt de grenzen',
            ],
            'example' => 'Een klant stelt \'s avonds een vraag via de chat. De AI-assistent geeft meteen antwoord, vraagt door en legt de aanvraag netjes klaar. De volgende ochtend zie jij een compleet ingevulde aanvraag, zonder dat je \'s avonds hoefde te reageren.',
        ],

    ],

    'cta_title' => 'Benieuwd wat dit voor jouw :trade zou betekenen?',
];
