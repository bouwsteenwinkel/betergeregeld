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
        'Documentaire reportagefotografie, eerlijk en ongeposeerd.',
        'Natuurlijk daglicht, echte Nederlandse context en locatie.',
        'Echte texturen, materialen en kleine imperfecties; niets gladgestreken.',
        'Realistische, ingetogen kleuren; geen oververzadiging, geen zware vignet of nep-bokeh.',
        'Focus op handen, gereedschap, materiaal en de werkomgeving in plaats van frontale gezichten,',
        'om het kunstmatige gezichts-effect te vermijden; mensen mogen, maar van opzij of bezig met het werk.',
        '35mm-look, natuurlijke scherptediepte, alsof gemaakt door een vakfotograaf op locatie.',
    ]),

    // Wat NOOIT in beeld mag (negative prompt).
    'negative' => implode(', ', [
        'stockfoto-uitstraling', 'geposeerde lachende-diverse-team-foto', 'plastic of wasachtige huid',
        'vervormde handen of vingers', '3D-render', 'illustratie of cartoon', 'tekst, letters, logo of watermerk',
        'oversaturated', 'HDR-look', 'kunstmatige lens flare', 'AI-gloed',
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
