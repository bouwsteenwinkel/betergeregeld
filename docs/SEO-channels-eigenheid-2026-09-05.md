# Channels: waarom ze niet ranken, en wat dat betekent voor ~200 sites

Onderzoek 05-09-2026, op basis van Search Console en een tekstmeting over de
zestien live channel-sites. Nulmeting voor het plan om naar ongeveer 200
branches uit te breiden.

## Samenvatting

De sites zijn technisch in orde en worden op de juiste zoekopdrachten gevonden.
Ze staan alleen te laag om geklikt te worden, en de meest waarschijnlijke reden
is dat **85% van de tekst op elke site woord voor woord gelijk is**.

Bij zestien domeinen valt dat Google misschien niet op. Bij tweehonderd wel, en
dan is het het patroon van een doorway-netwerk: veel domeinen die alleen in een
sleutelwoord verschillen.

## Wat er goed is

Dit is geen technisch probleem, en dat is belangrijk om vast te stellen voordat
er aan de verkeerde knop wordt gedraaid.

- Alle 164 sitemap-URL's van een channel geven 200. Ook 80 willekeurig geloten
  plaatspagina's buiten de sitemap: allemaal 200.
- `index,follow` waar het hoort, canonical aanwezig, eigen sitemap per site.
- De bak **"gecrawld - momenteel niet geindexeerd" staat op 0**. Dat is
  wezenlijk beter dan bouwsteenwinkel.nl, waar er 1.127 in liggen.
- De 349 pagina's op noindex zijn bewust beleid: elke site genereert een
  plaatspagina voor alle 1.195 plaatsen uit `channel_place_facts` en laat er
  ~126 in de sitemap. Geverifieerd: bussum/hilversum/doetinchem (in de sitemap)
  staan op `index,follow`, naarden/weesp/bloemendaal (erbuiten) op
  `noindex,follow`, alle zes met status 200.
- De doelgroep klopt. `jouw-rijschool-website.nl` wordt gevonden op "website
  laten maken rijschool", "rijschool website maken", "ai assistent voor
  rijscholen". `jouw-bakkerij-website.nl` op "bakkerij website laten maken",
  "website laten maken bakkers".

Eén uitzondering: **acupuncturist** wordt gevonden op "acupunctuur zeeland" en
"acupunctuur nieuw vennep" — dus door patienten, niet door acupuncturisten.
Daar trekken de plaatspagina's het verkeerde publiek. Bij de andere branches
speelt dat niet; het is het nakijken waard of meer zorg-branches dit hebben.

## Waar het op vastloopt

```
jouw-rijschool-website.nl, 90 dagen
11 klikken   1,78K vertoningen   CTR 0,6%   gemiddelde positie 39,3

website laten maken rijschool   488 vertoningen   positie 58,5   0 klikken
ai assistent voor rijscholen     63 vertoningen   positie 41,3   0 klikken
rijschool website maken          61 vertoningen   positie 47,4   0 klikken
```

Positie 58 is pagina zes. Nul klikken is daar geen raadsel maar het verwachte
gevolg.

**Externe links: 0.** Interne links: 9.749. betergeregeld.com linkt wel naar
alle zeventien, maar Google telt dat niet als aanbeveling van buiten.

## De meting: 15% eigen tekst

Zestien live sites, tien paginasoorten. Een tekstblok telt als sjabloon zodra
het op 80% of meer van de sites voorkomt. Reproduceerbaar met
`php artisan channel:seo:eigenheid`.

```
PAGINASOORT        SITES  WOORDEN  SJABLOON  EIGEN
/prijzen              16      812       95%     5%
/automatisering       16      838       94%     6%
/klantenportaal       16      878       92%     8%
/vergelijken          16      457       86%    14%
/werkwijze            16      499       84%    16%
/webshop              16      844       83%    17%
/website              16      900       83%    17%
/ai                   16      868       81%    19%
/home                 17      844       80%    20%
/diensten             16     1485       77%    23%
───────────────────────────────────────────────────
gewogen                                        15%
```

Ter vergelijking: de **plaatspagina's zijn 62% eigen**, want die gebruiken de
lokale data al.

De branchetekst die er staat is goed geschreven. Er is te weinig van:

> "Wie zoekt op 'rijschool' en jouw plaats, moet jóu vinden. Nu vissen andere
> rijscholen die leerlingen weg."

De overige 85% is de generieke verkoopmachine: hosting, back-ups, SSL, "we
zetten 'm live", de prijstabel, de vergelijking.

## De aanwijzing dat dit het is

De pagina's met de meeste vertoningen krijgen nul klikken; de weinige klikken
komen van de pagina's met de meeste eigen tekst.

```
rijschool  /vergelijken   533 vertoningen   0 klikken   14% eigen
rijschool  /ai            129 vertoningen   1 klik      19% eigen
rijschool  /plaatsen/…    losse klikken                 62% eigen
acupunct.  /plaatsen/…    alle 6 klikken                62% eigen
```

Elke klik op beide sites komt van een plaatspagina of een blogartikel, geen
enkele van een sjabloonpagina. **Let op: dit zijn dertig klikken in totaal.** Te
weinig voor een harde conclusie, genoeg voor een richting.

## Het materiaal ligt er al

`channel_place_listings`: **18.885 rijen, 17 branches x 1.195 plaatsen**, met
echte gegevens per markt.

```json
{"name":"Mijn Rijschool Utrecht","address":"Europalaan 20, Utrecht",
 "rating":4.9,"reviews":458,"website":"https://mijnrijschoolutrecht.nl/"}
```

Wie er zit, hoe ze scoren, en of ze een website hebben. Dat is per branche en
per plaats uniek, en het is precies het argument dat verkocht wordt: *"in
Utrecht hebben zeven van de twaalf rijscholen geen eigen site, en de bovenste
drie hebben er wel een."*

Dat verklaart ook waarom de plaatspagina's het beste doen: die gebruiken deze
data. De verkooppagina's — website, webshop, prijzen, vergelijken — niet.

## Advies

1. **De listings-data naar de verkooppagina's brengen.** Een wijziging in de
   generatie-pipeline, geen schrijfwerk per site, dus het schaalt meteen naar
   200. Begin bij `/prijzen` (95% sjabloon) en `/vergelijken` (86%, en met 533
   vertoningen de grootste bron van niets).
2. **Opnieuw meten met `channel:seo:eigenheid`.** Van 15% naar boven de 40% is
   controleerbaar; "meer branchetekst" niet.
3. **Eerst een branche op de eerste pagina krijgen, dan pas uitbreiden.** Lukt
   het bij rijschool niet, dan lukt het bij tweehonderd ook niet. Lukt het wel,
   dan weet je het recept voordat je het honderdvoudig uitrolt.
4. **De zeven live channels zonder Search Console-property toevoegen**:
   badkamerspecialist, dietist, golfschool, klusbedrijf, loodgieter,
   uitlaat-remmen, yogastudio. Zonder property is er geen enkel cijfer.
5. **Externe links.** Nul is het echte getal. Zolang dat nul blijft, is de rest
   schuiven aan de marge.

## Wat er op 05-09-2026 is gebouwd (advies 1)

`App\Services\ChannelSites\BrancheMarktcijfers` dicht `channel_place_listings`
per branche samen en `channels.partials.marktcijfers` zet dat op `/prijzen` en
`/vergelijken`, met twee verschillende invalshoeken zodat die twee pagina's
elkaars duplicaat niet worden.

Meting voor en na, met `php artisan channel:seo:eigenheid --lokaal`:

```
                 VOOR   NA
/prijzen           5%   17%
/vergelijken      14%   33%
```

Het doel van 40% is dus nog niet gehaald. Op `/vergelijken` scheelt het niet
veel meer; op `/prijzen` blijft het achter, en dat is verklaarbaar: het grootste
tekstblok daar is de prijstabel, en die prijzen zijn per branche werkelijk
gelijk. Ze verschillend láten lijken zou verzinsel zijn. De volgende echte winst
zit in de pakket-omschrijvingen en de vergelijkingstabel, die nu nog in
algemeenheden praten.

Wat de cijfers per branche laten zien -- en meteen het bewijs dat er iets te
zeggen valt:

```
BRANCHE                AANB.  /PLAATS  STER  MED.REV  KOPLOPER
rijschool               4663      4,5  4,75       42       124
bakkerij                5416      4,7  4,43       89       278
administratiekantoor    3124      3,3  4,57        7        13
dietist                 6002      5,4  4,38       95       720
acupuncturist           4962      4,7  4,88       13        35
```

**Eén advies uit dit document is onderweg ingetrokken.** Het idee was om te
schrijven: "in Utrecht hebben zeven van de twaalf rijscholen geen eigen site."
Gemeten over alle branches heeft **91 tot 99% wél een website**. Dat argument
bestaat niet, en het staat dus ook niet op de pagina's.

Twee dingen zijn bewust niet gedaan: geen bedrijfsnamen op de verkooppagina's
(een verkeerd getal verdwijnt in een gemiddelde, een verkeerde naam niet), en
geen "drukste plaats" (de Places-zoekopdracht kapt af op acht, dus die koploper
is een meetartefact -- bij rijschool rolde het dorp Aalst eruit).

## Bijvangst: 2.595 plaatspagina's toonden een supermarkt

Bij het aggregeren kwam Action als aannemer naar boven, en Albert Heijn als
dietist. Google Places vult een magere zoekopdracht aan met de dichtstbijzijnde
grote zaak.

```
listings totaal                      94.625
duidelijk fout (retailketen)          3.551   3,8%
plaatspagina's geraakt                2.595   14% van 18.885
```

Juist die plaatspagina's zijn de enige die klikken opleveren. Opgelost met
`channel_places.uitsluiten_hosts` en `PlaceBusinessFinder::schoon()`, dat
filtert bij het **lezen** -- zo geldt het meteen voor de 18.885 rijen die er al
liggen, zonder de betaalde API opnieuw af te lopen.

`indexableSlugs()` telt na hetzelfde filter, anders zou een plaats met drie
supermarkten in de sitemap belanden terwijl de pagina op noindex staat.

## Los hiervan: betergeregeld.com zelf

4.155 vertoningen, **nul klikken** in 90 dagen, terwijl sommige pagina's op
positie 6 staan.

**Dit is GEEN fout in de site.** Bij het onderzoek leek het alsof de vertaalde
blogpagina's een canonical naar `/nl/` droegen en daarmee zichzelf als duplicaat
aanmerkten. Dat was een meetfout: `curl -L` volgde de omleiding en las de
Nederlandse bestemmingspagina alsof het de Spaanse was.

Zonder redirects te volgen:

```
/nl/blog/wachtwoordmanager-kiezen-mkb-praktisch   200
/en/  /de/  /es/  /fr/                            301  ->  /nl/...
```

Alle vreemdtalige blog-URL's sturen netjes door. Zie
`BlogController::resolveMissingPost()` en `App\Support\Hreflang` — daar staat
uitgebreid beschreven waarom, inclusief de afhandeling van de `-en`-import-cruft
en de 410's die eruit moesten.

De verklaring voor de vertoningen zonder klikken is dan ook eenvoudig: Google
heeft die oude `/es/`- en `/fr/`-URL's nog in de index uit de tijd dat ze wél
inhoud gaven, en toont ze aan anderstalige zoekers die vervolgens een
Nederlandse titel zien. Dat ruimt zichzelf op zodra hij de 301's verwerkt heeft.
Geen ingreep nodig; wel iets om over een maand opnieuw te bekijken.

**Les voor de volgende meting:** status en inhoud apart bekijken. Een `curl -L`
verbergt precies het antwoord waar je naar op zoek bent.
