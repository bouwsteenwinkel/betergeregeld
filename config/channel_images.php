<?php

/**
 * CHANNEL-IMAGES — geautomatiseerde, art-directed beeldgeneratie per channel-site.
 *
 * Probleem: 1000 niche-sites kunnen niet handmatig van echte foto's worden
 * voorzien. Oplossing: genereer per BRANCHE (niet per site) documentaire,
 * niet-AI-aanvoelende beelden, met de anti-slop-regels in de prompt gebakken.
 * Eén branche-recept bedient alle sites in die branche.
 *
 * Pijplijn:  config (dit bestand) -> ImagePromptBuilder -> ChannelImageGenerator
 *            -> public/channel-media/<key>/<slot>.png  -> $site->image('<slot>')
 *
 * Commando:  php artisan channels:images {key?} --all
 *            (zonder OPENAI_API_KEY draait het in fake-mode: het schrijft een
 *             SVG-preview met de échte prompt erin, zodat je de pijplijn ziet)
 */

return [

    // Provider / model (OpenAI images, key uit config/accessguard.php).
    'model'   => env('CHANNEL_IMAGES_MODEL', 'gpt-image-1'),
    'quality' => env('CHANNEL_IMAGES_QUALITY', 'high'),

    // Waar de gegenereerde beelden landen (publiek, per kanaal-key).
    'path' => 'channel-media',

    // De beeld-slots die een site gebruikt (formaat = OpenAI image-size).
    'slots' => [
        'hero'   => ['size' => '1536x1024', 'ratio' => '3/2'],  // liggend, full-width hero-beeld
        'detail' => ['size' => '1536x1024', 'ratio' => '3/2'],  // liggend, detail/sfeer
        // Galerij: 6 vierkante werk-foto's per sector (gedeeld door alle niches).
        'gallery1' => ['size' => '1024x1024', 'ratio' => '1/1'],
        'gallery2' => ['size' => '1024x1024', 'ratio' => '1/1'],
        'gallery3' => ['size' => '1024x1024', 'ratio' => '1/1'],
        'gallery4' => ['size' => '1024x1024', 'ratio' => '1/1'],
        'gallery5' => ['size' => '1024x1024', 'ratio' => '1/1'],
        'gallery6' => ['size' => '1024x1024', 'ratio' => '1/1'],
    ],

    // ── DE ART-DIRECTIE (anti-AI-look) — geldt voor ELKE generatie ────────────
    // Dit is de kern: het dwingt documentaire echtheid af i.p.v. stock/AI-gevoel.
    'style' => implode(' ', [
        'Moderne, heldere editorial-merkfotografie met veel natuurlijk daglicht; frisse, eigentijdse uitstraling.',
        'Echte Nederlandse context, authentiek en geloofwaardig, maar helder en verzorgd (geen sombere of rommelige sfeer).',
        'Schone, rustige compositie met ruimte rondom het onderwerp; scherpe details en natuurlijke, levendige kleuren.',
        'Hedendaags, recent materiaal en apparatuur, verzorgd en up-to-date; nadrukkelijk geen gedateerde of jaren-90-uitstraling.',
        'Mensen mogen in beeld, natuurlijk bezig met het werk (vaak van opzij), zonder geposeerde stockglimlach of frontaal poseren.',
        'Full-frame camera-look, professioneel en licht belicht, alsof gemaakt door een hedendaagse merkfotograaf.',
    ]),

    // Wat NOOIT in beeld mag (negative prompt).
    'negative' => implode(', ', [
        'donker of somber', 'ouderwets, gedateerd, vintage, jaren-90-uitstraling', 'grauw, mat of vergeeld',
        'zware schaduwen of onderbelichting', 'rommelige of vieze omgeving',
        'stockfoto-uitstraling', 'geposeerde lachende-diverse-team-foto', 'plastic of wasachtige huid',
        'vervormde handen of vingers', '3D-render', 'illustratie of cartoon', 'tekst, letters, logo of watermerk',
        'oversaturated', 'overdreven HDR', 'kunstmatige lens flare', 'AI-gloed',
    ]),

    // ── PER-BRANCHE RECEPTEN ──────────────────────────────────────────────────
    // Sleutel = WebsiteLead::BRANCHES. Per slot een onderwerp-beschrijving.
    // Eén recept bedient alle sites binnen die branche.
    'branches' => [

        'bouw_installatie' => [
            'hero'   => 'Een Nederlandse installateur in werkkleding die geconcentreerd een cv-ketel of meterkast nakijkt. Handen met gereedschap close in beeld, daglicht door een raam, een nette bestelbus vaag op de achtergrond.',
            'detail' => 'Close-up van vakkundige handen die een koperen leiding of een groepenkast afwerken, gereedschap binnen handbereik, schone werkplek.',
        ],
        'horeca' => [
            'hero'   => 'Het warme interieur van een Nederlands restaurant of cafe vlak voor openingstijd, netjes gedekte tafels, zacht avondlicht.',
            'detail' => 'Handen van een kok die een gerecht opmaakt op een bord, verse ingredienten, keukenlicht.',
        ],
        'kapper_beauty' => [
            'hero'   => 'Een lichte, stijlvolle Nederlandse kapsalon met natuurlijk licht en planten, een lege stoel voor de spiegel, rustige sfeer.',
            'detail' => 'Vakkundige handen die haar knippen of stylen, focus op het vakmanschap en het gereedschap, niet op een frontaal gezicht.',
        ],
        'detailhandel' => [
            'hero'   => 'Het interieur van een verzorgde Nederlandse speciaalzaak, mooi uitgestalde producten, daglicht uit de etalage.',
            'detail' => 'Handen die een product inpakken of overhandigen aan de toonbank, persoonlijke service.',
        ],
        'sport_fitness' => [
            'hero'   => 'Een rustige, goed verlichte Nederlandse sportschool of studio kort voor de les, schone vloer en apparatuur, ochtendlicht.',
            'detail' => 'Detail van handen op een halter of een trainer die iets aanwijst, echte inspanning, geen geposeerde glimlach.',
        ],
        'zorg' => [
            'hero'   => 'Een warme, rustige Nederlandse praktijk- of wachtruimte met natuurlijk licht, planten en een nette inrichting, vertrouwenwekkend.',
            'detail' => 'Handen die zorgvuldig iets noteren of voorbereiden, ingetogen en respectvol, geen herkenbare patient.',
        ],
        'automotive' => [
            'hero'   => 'Een opgeruimde Nederlandse autogarage met een auto op de brug, gereedschap aan de wand, werkplaatslicht.',
            'detail' => 'Handen van een monteur die aan een motor werkt, vuil van het echte werk, gereedschap close in beeld.',
        ],
        'vastgoed' => [
            'hero'   => 'Een aantrekkelijke Nederlandse woning of straat met goed licht, vanaf de stoep gefotografeerd, uitnodigend.',
            'detail' => 'Handen die een sleutel overhandigen voor een voordeur, of een plattegrond op tafel, persoonlijk moment.',
        ],
        'zzp_diensten' => [
            'hero'   => 'Een Nederlandse zelfstandige ondernemer geconcentreerd aan het werk in zijn eigen werkomgeving, daglicht, echte spullen op tafel.',
            'detail' => 'Handen aan het werk met het gereedschap of de laptop van het vak, focus op vakmanschap.',
        ],

        'onderwijs_opvang' => [
            'hero'   => 'Een lichte, vriendelijke Nederlandse leer- of opvangruimte met natuurlijk licht, opgeruimde tafels en materialen, rustige sfeer voordat de groep binnenkomt.',
            'detail' => 'Handen die lesmateriaal of speelgoed klaarleggen, warme en verzorgde omgeving, geen herkenbare kindergezichten.',
        ],

        // Autorijschool: moderne, heldere beelden die écht over rijles gaan.
        'rijschool' => [
            'hero'     => 'Een moderne Nederlandse lesauto (recente compacte hatchback met dubbele bediening) op een rustige straat of oefenterrein bij helder daglicht; een rij-instructeur naast een leerling, ontspannen bezig met de rijles, frisse en eigentijdse sfeer, veel ruimte en lucht rondom.',
            'detail'   => 'Close-up van handen op het stuur en de versnellingspook in een schoon, modern auto-interieur met daglicht door de voorruit; instructeur wijst rustig mee.',
            'gallery1' => 'Een leerling geconcentreerd achter het stuur van een moderne lesauto, van opzij gezien, helder daglicht.',
            'gallery2' => 'Rij-instructeur en leerling in gesprek naast de lesauto op een oefenterrein, ontspannen en modern.',
            'gallery3' => 'Dashboard, stuur en pedalen met dubbele bediening in een recente, schone lesauto bij daglicht.',
            'gallery4' => 'Een moderne lesauto rijdend op een rustige Nederlandse straat bij mooi helder weer.',
            'gallery5' => 'Handen die een lesplanning of theorie op een tablet doornemen in een licht, modern auto-interieur.',
            'gallery6' => 'Een blije geslaagde leerling bij de lesauto, natuurlijk moment, helder daglicht, moderne uitstraling.',
        ],

        // Badkamerspecialist: de hero toont de VAKMAN (de doelgroep), niet een
        // badkamer als product — dit is een site die websites verkoopt AAN
        // badkamerspecialisten, niet badkamers aan consumenten. De galerij toont
        // wél het afgeleverde vakwerk (portfolio).
        'badkamerspecialist' => [
            'hero'     => 'Een Nederlandse badkamerspecialist in nette werkkleding, zelfverzekerd en tevreden, in een net opgeleverde moderne badkamer; hij kijkt op zijn smartphone naar een nieuwe klantaanvraag. De focus ligt volledig op de vakman zelf; de badkamer is subtiel en zacht onscherp op de achtergrond, niet als showroom of als product uitgelicht. Documentair en echt, natuurlijk daglicht, ruimte rondom het onderwerp.',
            'detail'   => 'Een strak detail van vakwerk in een moderne badkamer: een nette tegelnaad of een designkraan, schoon en verzorgd, helder daglicht.',
            'gallery1' => 'Een moderne inloopdouche met grote tegels en een glazen wand, licht en strak.',
            'gallery2' => 'Een vrijstaand bad bij een raam met daglicht in een rustige, moderne badkamer.',
            'gallery3' => 'Een strak badmeubel met waskom en spiegel, verzorgd en eigentijds, helder licht.',
            'gallery4' => 'Een detail van net gelegd tegelwerk met perfecte voegen in een moderne badkamer.',
            'gallery5' => 'Een badkamerspecialist die geconcentreerd aan het tegelen of monteren is, van opzij, modern en licht.',
            'gallery6' => 'Een compleet afgeleverde moderne badkamer in totaalbeeld, strak en licht, klaar voor gebruik.',
        ],
        'recreatie_vrije_tijd' => [
            'hero'   => 'Een uitnodigende Nederlandse vrijetijdslocatie met goed licht en ruimte, netjes ingericht en klaar voor gasten, ontspannen sfeer.',
            'detail' => 'Detail van de beleving of het materiaal ter plaatse, echte texturen, natuurlijk licht.',
        ],
        'transport_logistiek' => [
            'hero'   => 'Een opgeruimd Nederlands transport- of logistiekbedrijf met een nette bestelwagen of vrachtwagen op het terrein, ochtendlicht, echte werkomgeving.',
            'detail' => 'Handen die iets inladen of vastzetten, dozen of materiaal, focus op zorgvuldig werk.',
        ],
        'agrarisch_dieren' => [
            'hero'   => 'Een verzorgde Nederlandse agrarische locatie of dieren-omgeving met daglicht en ruimte, rustige en echte sfeer, geen geposeerde beelden.',
            'detail' => 'Handen die met dieren, planten of gereedschap bezig zijn, aardse texturen en natuurlijk licht.',
        ],

        // Fallback voor branches zonder eigen recept.
        '_default' => [
            'hero'   => 'Een Nederlandse vakman of ondernemer aan het werk in zijn eigen omgeving, documentaire stijl, handen en gereedschap centraal, daglicht.',
            'detail' => 'Close-up van handen aan het werk met het gereedschap van het vak, echte werkplek.',
        ],
    ],
];
