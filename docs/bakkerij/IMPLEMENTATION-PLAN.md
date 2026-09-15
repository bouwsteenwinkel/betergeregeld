# Implementatieplan — jouw-bakkerij-website.nl

Fase 3 van de opdracht (15-09-2026). Volgt op `SEO-CONTENT-AUDIT.md`. Alles binnen de
bestaande architectuur; geen nieuwe site, geen nieuwe huisstijl. Bestaande URL's blijven
bestaan; de enige nieuwe URL is `/ai-telefonie-bakkerij`.

## 1. Bestanden die veranderen

| bestand | wat |
|---|---|
| `config/bakkerij_landings.php` | alle vijf facetten herschrijven in bakkerijtaal: `website`, `webshop`, `klantenportaal` (nieuw concept: zakelijke bestelklanten), `automatisering` (bakkerijprocessen, geen offertes, geen 70%), `ai` (wordt telefonie; verwijst naar de nieuwe pagina) |
| `config/bakkerij_telefonie.php` (nieuw) | inhoud van de AI-telefonie-landingspagina: hero, situaties, voorbeeldgesprekken, wat wel/niet, werkwijze, FAQ, `demo_nummer` (leeg = knop verborgen), `prijs_tekst` |
| `resources/views/channels/_landing/bakkerij-telefonie.blade.php` (nieuw) | de landingspagina zelf, in de bestaande layout; Service + FAQPage + BreadcrumbList |
| `resources/views/channels/_sales/bakkerij.blade.php` | H1/lead/USP's/"Herken je dit?" bakkerijspecifiek; nieuwe sectie "Te druk om steeds de telefoon op te nemen?" met "Bekijk AI-telefonie" en (als nummer gezet) "Bel de demo"; Groeidiamant-intro "begin waar het meeste oplevert" |
| `resources/views/channels/_landing/bakkerij.blade.php` | CTA per facet: ai-facet → demo-knoppen i.p.v. gratis voorbeeld; `verder`-links met beschrijvende ankers |
| `routes/channels.php` | `GET /ai-telefonie-bakkerij` (alleen voor kanalen die een telefonie-config hebben; anders 404), sitemap-opname |
| `app/Http/Controllers/ChannelSite/ChannelSiteController.php` | route-handler + sitemap-regel voor de telefoniepagina; `header.menu` uit DB blijft leidend |
| `channel_sites.header` (DB, bakkerij) | menu: Diensten · AI-telefonie · Werkwijze · Prijzen · Contact; `#reviews` weg |
| `channel_sites.brand` (DB, bakkerij) | `trustline` "Erkend vakwerk · 4,8 ★" weg uit de demo |
| `resources/views/channels/layout.blade.php` | organisatienaam in JSON-LD en title-suffix: "Beter Geregeld" i.p.v. de branchenaam (geldt voor **alle** kanalen — bewust, want overal fout; kleine, veilige wijziging) |
| `resources/views/channels/places/*.blade.php` | per-kanaal titel/H1-sjabloon uit config (`channel_places.titles.bakkerij`) + consumentenafleider-regel; standaard blijft zoals nu voor de andere kanalen |
| `resources/views/channels/services.blade.php`, `pricing.blade.php`, `vergelijken.blade.php` | alleen waar tekst kanaal-onafhankelijk generiek is: offerte-taal vervangen door neutraal ("aanvragen/bestellingen"); titels van `/vergelijken` en `/diensten` zo dat ze niet met de homepage concurreren |
| `config/channel_faq.php` of bakkerij-override | telefonie- en webshopvragen erbij (alleen antwoorden die we kunnen waarmaken) |
| `blog_posts` (DB) | posts `altijd-bereikbaar-met-ai` en `offerte-grote-bestelling-automatiseren`: link naar `/ai-telefonie-bakkerij` |
| `docs/bakkerij/CHANGES-SEO-CONTENT.md` | eindrapport (fase 6) |

## 2. Routes
- Nieuw: `/ai-telefonie-bakkerij` (200, index,follow, canonical, in sitemap, in menu, in llms.txt).
- `/ai` blijft bestaan (staat in de Groeidiamant en in blogs) en wordt de korte facetpagina die
  naar de landingspagina doorverwijst; **geen** redirect, want de facet-URL hoort bij het
  groeipad-mechanisme van alle kanalen.
- Geen URL's verwijderd; geen redirects nodig.

## 3. Content per pagina (kort)
- **Home**: H1 "Website, webshop en slimme automatisering voor jouw bakkerij"; lead noemt online
  bestellen, zakelijke klanten en de telefoon; USP's: gevonden worden op "bakkerij + plaats",
  bestellingen online in plaats van aan de telefoon, telefoon opgenomen als je in de bakkerij
  staat. "Herken je dit?" met vier bakkerijsituaties (ochtenddrukte aan de telefoon,
  taartbestellingen via WhatsApp/briefjes, zakelijke klanten die bellen om te herhalen, site
  zonder assortiment/openingstijden). AI-telefonie-sectie. Groeidiamant met "elke stap ook los".
- **Website**: lokaal gevonden, openingstijden, vestigingen, assortiment, taarten, foto's, bestellen,
  mobiel, snelheid, contact.
- **Webshop**: brood, banket, taarten (ook op maat), gebak, lunch/belegde broodjes, feestdagen,
  afhaaldatum en -tijd, bezorging, vooraf betalen, zakelijk, herhaalbestelling. Alleen wat
  gebouwd kan worden (het platform doet dit al bij Bouwsteenwinkel: betalen via Mollie,
  bezorging/afhalen, herhaalbestelling via account).
- **Klantenportaal**: horeca/kantoren/lunchrooms: vaste bestelling bekijken en aanpassen, extra
  bestellen, herhalen, aflevermoment, facturen, historie; maatwerk afhankelijk van proces.
- **Automatisering**: bestelling → betaling → bevestiging → productielijst; zakelijke bestelling →
  factuur; bestelling → juiste vestiging/afhaallijst; klantgegevens → boekhouding. "Minder
  overtypen, minder fouten." Koppelingen: "we bekijken wat met jouw kassa/boekhouding kan".
- **AI-telefonie (nieuw)**: wat, waarom, wanneer het stoort, voorbeeldgesprekken, wat bij "weet
  ik niet" (terugbelverzoek/doorverbinden), eigen nummer behouden (via doorschakelen), buiten
  openingstijden, samenvatting per mail + portaal, geen opnames, allergenen alleen met
  betrouwbare actuele productinformatie en anders verwijzen naar een medewerker, implementatie
  (kennisbank + dagberichten + terugbelvensters), prijs (tekst uit config), FAQ, CTA's.

## 4. SEO-aanpassingen
- Title/H1 per pagina op één intentie: home = "website/webshop bakkerij laten maken";
  telefoniepagina = "AI telefonie bakkerij / AI telefoonassistent"; webshop = "webshop bakkerij /
  online bestellen"; automatisering = "bakkerij automatiseren".
- `/vergelijken`: title "Zelf een website bouwen of laten maken? De vergelijking voor bakkerijen"
  (intentie: vergelijken, niet "laten maken"); `/diensten`: "Alle diensten voor bakkerijen".
- Structured data: Service op de telefoniepagina, FAQPage daar en op /veelgestelde-vragen,
  Organization-naam "Beter Geregeld ICT" overal.
- Interne links met beschrijvende ankers tussen webshop ↔ telefonie ↔ automatisering.
- Plaatsen/provincies: ondernemersgerichte titel, consumentenafleider; geen de-indexering.
- Sitemap: +1 URL. Robots ongewijzigd.

## 5. Volgorde van uitvoeren
1. Config + nieuwe telefoniepagina + route (nog niet in menu) → lokaal renderen.
2. Facetteksten en homepage.
3. Layout-naam, plaatsen-titels, gedeelde pagina's (kleine tekstingrepen).
4. DB: menu, brand-trustline, blog-links.
5. Validatie (fase 5): lokaal alle routes 200, schema-JSON geldig, mobiel/desktop renderen,
   sitemap, dan deploy + paginacache leeg, live nameten.
6. Eindrapport.

## 6. Wat er nog van Dennis nodig is
Zie audit §3: demonummer, prijsindicatie, cases/klantnamen, koppelingen. Zonder antwoord bouw ik
met: knop verborgen, "prijs na een gesprek van een half uur", geen cases, "we bekijken wat kan".
