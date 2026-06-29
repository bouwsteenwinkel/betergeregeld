<?php

/**
 * Plaatsen voor de "plaatsen"-SEO-pagina's van de channel-sites.
 *
 * Bewust een CURATED lijst (de grootste ~140 NL-plaatsen) i.p.v. alle 2500+
 * woonplaatsen — anders krijg je honderden dunne pagina's die SEO juist schaden.
 * Elke plaats wordt een pagina /plaatsen/{slug} met branche-specifieke tekst.
 *
 * Slug = lowercase, spaties → '-', diacritieken vereenvoudigd. De ChannelSite-
 * resolver matcht op slug; de weergavenaam houdt hoofdletters/streepjes.
 *
 * Plaats toevoegen = één regel 'Naam' hieronder.
 */

return [
    'Amsterdam', 'Rotterdam', 'Den Haag', 'Utrecht', 'Eindhoven', 'Groningen',
    'Tilburg', 'Almere', 'Breda', 'Nijmegen', 'Apeldoorn', 'Arnhem', 'Haarlem',
    'Haarlemmermeer', 'Amersfoort', 'Zaanstad', 'Den Bosch', "'s-Hertogenbosch",
    'Zwolle', 'Zoetermeer', 'Leiden', 'Leeuwarden', 'Maastricht', 'Dordrecht',
    'Ede', 'Alphen aan den Rijn', 'Westland', 'Alkmaar', 'Emmen', 'Delft',
    'Venlo', 'Deventer', 'Sittard-Geleen', 'Helmond', 'Oss', 'Amstelveen',
    'Hilversum', 'Heerlen', 'Hengelo', 'Purmerend', 'Schiedam', 'Roosendaal',
    'Spijkenisse', 'Lelystad', 'Almelo', 'Gouda', 'Hoorn', 'Vlaardingen',
    'Assen', 'Bergen op Zoom', 'Capelle aan den IJssel', 'Veenendaal',
    'Katwijk', 'Zeist', 'Nieuwegein', 'Roermond', 'Doetinchem', 'Den Helder',
    'Hardenberg', 'Barneveld', 'Oosterhout', 'Hoogeveen', 'Rijswijk',
    'Woerden', 'Waalwijk', 'Houten', 'Kampen', 'Wijchen', 'Weert', 'Heerhugowaard',
    'Middelburg', 'Zwijndrecht', 'Ridderkerk', 'Drachten', 'Tiel', 'Soest',
    'Veldhoven', 'Uden', 'Zutphen', 'Kerkrade', 'Harderwijk', 'Lansingerland',
    'Nijkerk', 'Etten-Leur', 'Maassluis', 'Gorinchem', 'Sneek', 'Goes',
    'Wageningen', 'Rheden', 'Castricum', 'IJsselstein', 'Geldrop', 'Boxtel',
    'Heerenveen', 'Coevorden', 'Meppel', 'Winterswijk', 'Culemborg', 'Dronten',
    'Steenwijk', 'Tytsjerksteradiel', 'Beverwijk', 'Heemskerk', 'Wassenaar',
    'Voorschoten', 'Leiderdorp', 'Oegstgeest', 'Bussum', 'Naarden', 'Huizen',
    'Weesp', 'Laren', 'Blaricum', 'Bunschoten', 'Nunspeet', 'Putten',
    'Ermelo', 'Epe', 'Voorst', 'Brummen', 'Lochem', 'Zevenaar', 'Duiven',
    'Montferland', 'Aalten', 'Oude IJsselstreek', 'Bronckhorst', 'Berkelland',
    'Renkum', 'Wijk bij Duurstede', 'Rhenen', 'Scherpenzeel', 'Bunnik',
    'De Bilt', 'Stichtse Vecht', 'Vianen', 'Leusden', 'Baarn', 'Eemnes',
    'Purmer', 'Volendam', 'Edam', 'Monnickendam', 'Wormerland', 'Landsmeer',
    'Diemen', 'Ouder-Amstel', 'Uithoorn', 'Aalsmeer', 'Nieuwkoop', 'Bodegraven',
    'Waddinxveen', 'Boskoop', 'Krimpen aan den IJssel', 'Barendrecht',
    'Hendrik-Ido-Ambacht', 'Papendrecht', 'Sliedrecht', 'Hardinxveld-Giessendam',
    'Geertruidenberg', 'Drimmelen', 'Werkendam', 'Zundert', 'Rucphen',
    'Bergeijk', 'Valkenswaard', 'Cranendonck', 'Heeze-Leende', 'Nuenen',
    'Best', 'Son en Breugel', 'Geldrop-Mierlo', 'Cuijk', 'Boxmeer', 'Gennep',
    'Mill', 'Grave', 'Landerd', 'Bernheze', 'Vught', 'Sint-Michielsgestel',
    'Boekel', 'Veghel', 'Meierijstad', 'Gemert-Bakel', 'Laarbeek', 'Asten',
    'Someren', 'Deurne', 'Beek', 'Stein', 'Beekdaelen', 'Brunssum', 'Landgraaf',
    'Voerendaal', 'Simpelveld', 'Gulpen-Wittem', 'Vaals', 'Eijsden-Margraten',
    'Meerssen', 'Valkenburg aan de Geul', 'Nuth', 'Echt-Susteren', 'Maasgouw',
    'Leudal', 'Nederweert', 'Peel en Maas', 'Horst aan de Maas', 'Venray',
    'Bergen (L)', 'Beesel', 'Roggel',
];
