# SEO- en contentaudit — jouw-bakkerij-website.nl

Datum: 15-09-2026. Fase 1 (inventarisatie) en fase 2 (audit) van de opdracht "bakkerij-channel
specifieker, commerciëler en beter vindbaar, met AI-telefonie als zelfstandige dienst".
Er is in deze fase **niets gewijzigd**.

> **Randvoorwaarde vooraf.** Op 10-09 is afgesproken dat de teksten van de 16 branche-channels
> tot de hermeting van 4-18 oktober niet veranderen, zodat de meting van de wijzigingen van
> 07/08-09 zuiver blijft. Deze opdracht doorbreekt dat voor bakkerij. Dat is een bewuste keuze
> van Dennis (15-09); in de hermeting telt bakkerij dan niet mee.

---

## 1. Inventarisatie

### Techniek
- Laravel (betergeregeldv2), één codebase voor 204 branches; per branche een `channel_sites`-rij
  (bakkerij: id 60, key `bakkerij`, domein jouw-bakkerij-website.nl, status live, locale nl).
- Routes in `routes/channels.php`: `/`, `/{facet}` (website, webshop, klantenportaal,
  automatisering, ai), `/diensten`, `/groeidiamant`, `/prijzen`, `/werkwijze`, `/cases`,
  `/veelgestelde-vragen`, `/vergelijken`, `/over-ons`, `/contact`, `/afspraak`, `/blog`,
  `/blog/{slug}`, `/plaatsen`, `/plaatsen/provincie/{prov}`, `/plaatsen/{plaats}`, `/voorbeeld`,
  `/voorbeeld-maken`, `/sitemap.xml`, `/robots.txt`, `/llms.txt`, `POST /contact`, `POST /_ev`.
- Views: bakkerij heeft **twee eigen views** (`channels/_sales/bakkerij.blade.php` = home,
  `channels/_landing/bakkerij.blade.php` = de vijf facetpagina's) en deelt de rest met alle
  branches (`channels/services|pricing|faq|werkwijze|cases|vergelijken|about|contact|afspraak|
  places/*|blog/*`, layout `channels/layout.blade.php`).
- Facetteksten: `config/bakkerij_landings.php` (hero, pains, "zo kan het worden" per facet).
- Gedeelde teksten worden ingevuld met branche-tokens (`App\Support\ChannelTokens`: `:trade`,
  `:trades`, `:zaak`, …). Voor bakkerij: trade = bakkerij, trades = bakkerijen.
- Merk/menu in de DB: `brand` (logo "Kruimel" — dat is de **demo-bakkerij** voor /voorbeeld),
  `header.menu` = Diensten / Werkwijze / Reviews / Contact, CTA "Gratis voorbeeld".
- Paginacache: `CacheChannelPage` (1 uur, alleen GET zonder query). Warm-up-crawler draait.
- Plaatsen: 161 plaatspagina's + 12 provinciepagina's in de sitemap; dunne plaatsen krijgen
  `noindex` via `channel_places.index_min_businesses` (=5) en de schaal-toets van PlaceBusinessFinder.
  Sinds 15-09 bestaat `config/channel_places_noindex.php` (per kanaal alle plaatsen op noindex);
  bakkerij staat daar bewust **niet** in (66% ondernemersintentie).
- Blog: 20 posts (nov 2025 – jul 2026), bakkerijspecifiek van onderwerp.
- Bescherming: honeypot op het leadformulier, Cloudflare WAF (crawlers + nep-browsers sinds 15-09).

### Zoekverkeer (Search Console, 90 dagen t/m 12-09)
- 24 zoektermen met vertoningen, **0 klikken** op zoektermniveau, 2 klikken op paginaniveau.
- Het kerncluster bestaat, maar we staan op pagina 5-6:
  `bakkerij website laten maken` 30 vert. pos 59 · `website laten maken bakkerijen` 26 / 53 ·
  `website laten maken bakkers` 18 / 58 · `website laten maken bakker` 17 / 53 ·
  `website voor bakkerijen` 2 / 42. Samen ≈ 93 vertoningen.
- **Google kiest de verkeerde pagina** voor dat cluster: `/vergelijken` (79 vert., pos 50) en
  `/diensten` (73, pos 66) — niet de homepage. De homepage-H1 ("Meer klanten en verkoop uit je
  eigen regio") bevat het woord *website* niet.
- Consumentenverkeer via plaatsen: `bakker anjum` 20 vert. pos 12, `bakkerij anjum` 10 / 11 — via
  `/plaatsen/provincie/friesland` (62 vert., pos 12, 1 klik). Dat zijn mensen die brood zoeken.
- AI-telefonie: **nul** vertoningen op enige AI-term. Logisch: de term staat nergens op de site.
- Verkeer (IIS, 7 dagen): ~5.000 botpagina's, 12 pagina's met hard mensbewijs, 77 browsersessies
  met JS. De echte bezoekers volgen het pad `/` → `/ai` → `/werkwijze` → `/prijzen` → `/contact`
  en sturen niets in. Sinds juli: 0 leads via dit kanaal.

---

## 2. Bevindingen

### A. Branchevreemde tekst — CRITICAL
Gevonden op de live site (zichtbare tekst, per pagina):

| pagina | termen |
|---|---|
| `/klantenportaal` | "Laat klanten hun **klus** zelf volgen" (title + H1), "status van de klus", "wanneer je komt", "Offerte, facturen en garantie", "garantiebewijzen", "gekoppeld aan hun opdracht" |
| `/ai` | "Veel vragen, weinig serieuze **klussen**", "alleen de **kansrijke** klussen terugbelt" (2×) |
| `/` | "Elke gemiste aanvraag is een misgelopen **klus**", "Offerteaanvragen rechtstreeks in je mailbox", "offertes typen" |
| `/automatisering` | "offerte(s)" 7×, "bespaar tot wel **70% tijd**" |
| `/diensten` | "offertes" 8×, "klussen" 3× |
| `/website` | "klus" 1× |
| `/prijzen`, `/groeidiamant`, `/blog` | "offertes/offerteaanvragen" |

Bron: `config/bakkerij_landings.php` (klantenportaal- en ai-facet zijn een kopie van het
aannemer/klusbedrijf-sjabloon met alleen `:trade` vervangen) en de gedeelde pagina's
(`channel_services`, `pricing`, `groeidiamant`) die in offerte-taal zijn geschreven. Voor een bakker
zijn "offertes" hooguit relevant bij zakelijke bestellingen; "klus", "garantiebewijs", "wanneer je
komt" nooit.

Het klantenportaal is het ergst: het hele concept (afspraak inplannen, klus volgen, garantiebewijs)
is dat van een installateur. Voor een bakkerij is een portaal iets anders: zakelijke klanten
(horeca, kantoren) die zelf hun vaste bestelling bekijken, aanpassen en herhalen.

### B. AI-telefonie is onzichtbaar als dienst — CRITICAL
- Er is geen pagina, geen menu-item en geen zoekterm die "telefonie" bevat. `/ai` heet
  "AI-assistent", staat als vijfde stap van de Groeidiamant en presenteert zichzelf als iets dat
  "telefoon **en chat**" aanneemt.
- Claims op `/ai` die niet aantoonbaar zijn: **chat** (er is geen chatproduct), "zoekt de status
  van een bestelling op" (bestaat bij Bouwsteenwinkel als maatwerk, niet standaard), "alles terug
  te zien in je portaal" (klantportaal bestaat: telefonie.betergeregeld.com — dat klopt wel).
- Er staat **geen demonummer**. Er zijn er twee die live zijn: 088 254 5150 (apotheek) en
  088 254 5160 (garage, "Autobedrijf De Wissel"). Geen bakkerijdemo. De sterkste CTA voor deze
  dienst ("bel en hoor het zelf") ontbreekt daardoor.
- Wat de telefonie aantoonbaar doet (uit `bouwsteenwinkel_v3/telefonie/beleid/*` en de README):
  opnemen in NL (EN/DE/AR op verzoek), openingstijden en praktische vragen beantwoorden uit een
  kennisbank plus dagberichten, terugbelverzoek vastleggen met naam en nummer (herhaalt ter
  controle), doorverbinden tijdens openingstijden, samenvatting per mail na elk gesprek, log in het
  klantportaal, **geen** opnames, **geen** agenda-afspraken (bewust uitgezet), geen betalingen.
  Bestellingen vastleggen kan als terugbel-/orderverzoek (tekst), niet als kassatransactie.

### C. Homepage en zoekintentie — HIGH
- H1 "Meer klanten en verkoop uit je eigen regio" zegt niet wat we bouwen en voor wie; de
  title zegt het wel. Google kiest daardoor `/vergelijken` en `/diensten` voor "website laten maken
  bakkerij" — twee generieke pagina's op positie 50-66. Kannibalisatie op de belangrijkste term.
- De hero-USP's zijn generiek ("Gevonden worden als iemand een bakkerij zoekt in jouw regio" is
  zelfs de consumentenkant). Geen woord over bestellen, taarten, afhalen, zakelijke klanten.
- "Herken je dit?"-kaarten: 2 van 4 zijn aannemerstaal (aanvragen/klus, offertes typen).
- Geen AI-telefonie-sectie op de homepage; de Groeidiamant toont AI als "later, stap 5".

### D. Groeidiamant — MEDIUM
Het concept staat er goed (groeipad-partial, `/groeidiamant`-pagina), maar overal als volgorde:
"jouw stap / later". Nergens staat dat je bij stap 5 kunt beginnen. Voor AI-telefonie is dat
precies de klant: een bakker mét website die alleen de telefoon wil laten opnemen.

### E. Vertrouwen en claims — HIGH
- "bespaar tot wel 70% tijd" (`/automatisering`) — geen basis.
- Demo-merk "Kruimel" heeft in `brand.trustline` "Erkend vakwerk · 4,8 ★" — dat staat alleen op
  `/voorbeeld` (noindex, fictieve bakkerij), maar het is een verzonnen beoordeling en hoort er niet
  te staan.
- `/cases` toont geen enkele echte case (alleen "zo zou het kunnen") maar heet "Voorbeelden en
  cases · Zo ziet groei eruit" — belooft meer dan er staat.
- Header-badge "Live binnen enkele dagen" tegenover FAQ "doorgaans binnen twee weken live".
- Menu-item "Reviews" (`#reviews`) in de DB-config, terwijl er geen reviews zijn (het menu op de
  live site toont gelukkig Diensten/Werkwijze/Prijzen/Contact).
- Wie zit erachter: alleen "betergeregeld" in de footer en info@betergeregeld.com; `/over-ons`
  heet "Over Bakkerij". JSON-LD `ProfessionalService.name` = "Bakkerij", `WebSite.name` =
  "Bakkerij", titles eindigen op "· Bakkerij". Een bezoeker ziet nergens "Beter Geregeld ICT" als
  bedrijf.

### F. Technische SEO — LOW (grotendeels op orde)
Goed: canonical per pagina, `robots.txt` open + sitemap, `noindex` op /voorbeeld en dunne plaatsen,
BreadcrumbList op alle subpagina's, FAQPage op /veelgestelde-vragen, Service+OfferCatalog op
/diensten, Open Graph met echte hero-afbeelding, self-hosted CMP-script, één stylesheet,
lazy-loading op de hero-image via preload/fetchpriority, paginacache 1 uur, llms.txt.

Aandachtspunten:
- 4 van de 5 `<img>` op de homepage zonder `width/height` (CLS-risico; de hero heeft ze wel via
  styling).
- Geen hreflang (alleen NL — prima, alleen benoemen).
- `/groeipad` geeft 404 (menu-anker heet `#diensten`, geen probleem, wel een dode variant in
  oude links: controleren in interne links).
- Organisatienaam in structured data is de branchenaam (zie E).
- Titles: "· Bakkerij" als sitenaam-suffix is zwak; "| Beter Geregeld" is beter en consistent met
  betergeregeld.com.

### G. Lokale SEO (plaatsen) — MEDIUM
- 161 plaatspagina's + 12 provincies in de sitemap; per plaats identieke opbouw met plaatsnaam,
  buurplaatsen en (waar Places-data is) een lijstje echte bakkerijen. Provinciepagina's zijn
  dun (H1 "Bakkerij in Drenthe", lijst plaatsen).
- Ze doen wél iets: `/plaatsen/provincie/friesland` haalt 62 vertoningen en 1 klik. Maar op
  "bakker anjum" — consumenten die brood zoeken. Bij bakkerij is 66% van de vertoningen
  ondernemersintentie, dus **niet** wegdrukken zoals bij apotheek/loodgieter; wel de
  consumentenzoekers van de plaatsenpagina's afleiden ("Zoek je een bakker? Dit is een pagina
  voor bakkers die een website willen") en de titel/H1 van provincie- en plaatspagina's op
  ondernemersintentie zetten ("Website laten maken voor je bakkerij in Assen").
- Voorstel architectuur: provinciepagina's blijven (12, indexeerbaar, met echte inhoud per regio),
  plaatspagina's alleen indexeerbaar waar de gating al aanslaat (nu 161) en met een
  ondernemersgerichte titel; geen uitbreiding.

### H. Conversie — HIGH
- Overal dezelfde primaire CTA "Gratis voorbeeld aanvragen" (goed voor website/webshop), ook op
  `/ai` waar het niet past. Geen "Bel de demo", geen "Vraag een demo aan".
- Formulier (lead-wizard) werkt en mailt intern; honeypot; sinds juli 0 inzendingen op dit kanaal.
- Telefoonnummer in header/footer: 088-2545101 (hoofdlijn Beter Geregeld) — goed.
- Echte bezoekers komen tot `/prijzen` en `/contact` en haken af. `/prijzen` is generiek
  ("Wat kost een website voor bakkerijen?" maar de inhoud is de standaardpakketten met
  offerte-taal).

### I. Navigatie — MEDIUM
Diensten / Werkwijze / Prijzen / Contact + CTA. Geen AI-telefonie. Mobiel menu identiek.
Eén item erbij ("AI-telefonie") past; niet meer.

### J. Blog/kennisbank — LOW
20 posts, onderwerpen zijn bakkerijspecifiek en bruikbaar (aanbetaling grote taart, allergenen
online tonen, broodabonnement, altijd bereikbaar met AI). Ze linken naar de facetpagina's.
Architectuur is geschikt; geen nieuwe massa nodig. Twee posts (`altijd-bereikbaar-met-ai`,
`offerte-grote-bestelling-automatiseren`) moeten naar de nieuwe AI-telefoniepagina gaan linken.

---

## 3. Prioriteiten

| prio | wat | waar |
|---|---|---|
| CRITICAL | Klantenportaal-facet herschrijven voor zakelijke bestelklanten; klus/offerte/garantie eruit op alle facetten | `config/bakkerij_landings.php` |
| CRITICAL | AI-telefonie als zelfstandige dienst: nieuwe landingspagina `/ai-telefonie-bakkerij`, onware claims van `/ai` weg, demonummer configureerbaar | nieuwe view + config + route |
| HIGH | Homepage: H1/lead/USP's/"Herken je dit?" bakkerijspecifiek met de kernterm; AI-telefonie-sectie met CTA's | `_sales/bakkerij.blade.php` |
| HIGH | Claims: "70% tijd" weg; "Erkend vakwerk · 4,8 ★" weg; `/cases` eerlijk benoemen; "Live binnen enkele dagen" gelijktrekken | config + DB brand + views |
| HIGH | Kannibalisatie: `/vergelijken` en `/diensten` niet meer op de kernterm laten concurreren (titels/koppen verscherpen richting hun eigen intentie), homepage de kernterm geven | views |
| HIGH | CTA-hiërarchie per dienst: AI = "Bel de demo" + "Vraag demo aan"; website/webshop = gratis voorbeeld | views |
| MEDIUM | Groeidiamant: "begin waar het meeste oplevert", elke stap los afneembaar | groeipad-partial (tekst), `/groeidiamant` |
| MEDIUM | Navigatie: "AI-telefonie" erbij (desktop + mobiel) | DB `header.menu` |
| MEDIUM | Plaatsen/provincies: ondernemersgerichte title/H1 + consumentenafleider | places-views (gedeeld! → per-kanaal tekst via config) |
| MEDIUM | Organisatienaam en title-suffix "Beter Geregeld" | layout + JSON-LD |
| LOW | `img` width/height op home; interne links met beschrijvende ankers; FAQ uitbreiden met telefonie-vragen | views/config |

**Wat ik van Dennis nodig heb (zie ook fase 6):**
1. Komt er een bakkerij-demonummer, of gebruiken we tot die tijd 5160 (garage) als "hoor hoe het
   klinkt"? (Ik zet het nummer in config; leeg = knop verborgen.)
2. Prijsindicatie AI-telefonie: mag er een "vanaf"-bedrag op, of alleen "prijs na gesprek"?
3. Mag Apotheek Avereest / een andere klant als voorbeeld genoemd worden? Zo niet: geen cases.
4. Welke koppelingen mogen we als "mogelijk" noemen: kassa (welke?), boekhouding (Exact/e-Boekhouden?),
   bezorgdienst? Zonder antwoord schrijf ik "we bekijken wat er met jouw systemen kan".
