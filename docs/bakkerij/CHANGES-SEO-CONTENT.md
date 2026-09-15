# Eindrapport — jouw-bakkerij-website.nl (fase 6)

Datum: 15-09-2026. Commits `6808336` (audit + plan), `7846cfe` (implementatie), `903c5ff` (breedte
1400 px, eerder op de dag). Live gezet via de deploy-webhook; alle pagina's live gecontroleerd.

## 1. Wat gewijzigd is, en waarom

**Branchevreemde tekst weg (CRITICAL).** `config/bakkerij_landings.php` was het aannemer-sjabloon
met het branchewoord vervangen. Alle vijf facetten zijn herschreven vanuit de bakkerij: balie,
taartbestellingen, zakelijke klanten, productielijst. Meting na afloop over 24 pagina's:
klus 0, garantiebewijs 0, "70% tijd" 0, chat 0; "offerte" komt alleen nog voor in de blogpost over
grote bestellingen, waar het hoort.

**AI-telefonie als zelfstandige dienst (CRITICAL).** Nieuwe landingspagina, in het menu (desktop en
mobiel), op de homepage als eigen sectie, in het dienstenoverzicht, op de prijzenpagina, in de
FAQ, in de sitemap en in llms.txt. De ai-facet (`/ai`) is de korte versie en verwijst door.

**Homepage op de kernterm (HIGH).** H1 "Website, webshop en slimme automatisering voor jouw
bakkerij", title "Website, webshop en AI-telefonie voor bakkerijen laten maken". Google koos tot nu
`/vergelijken` en `/diensten` voor "website laten maken bakkerij"; die twee hebben nu een titel
op hun eigen intentie (vergelijken resp. overzicht).

**Claims (HIGH).** "bespaar tot wel 70% tijd" weg; de verzonnen "Erkend vakwerk · 4,8 ★" uit het
demo-merk verwijderd; `/cases` ongewijzigd gelaten maar heet in de audit als aandachtspunt (zie §8).

**Plaatsen en provincies (MEDIUM).** Geen de-indexering (66% ondernemersintentie). Wel: titel, kop en
lead op de bakker gericht, met één zin voor de broodzoeker ("Zoek je een bakker in :city? Kijk in de
lijst hieronder"). Provinciepagina's: van "Bakkerij in Drenthe" naar "Website laten maken voor je
bakkerij in Drenthe".

**Organisatienaam.** Titles eindigen nu op "· Beter Geregeld ICT" en `ProfessionalService.name`
is "Beter Geregeld ICT" (via `brand.footer_name` in de database; alleen bakkerij, de andere
kanalen blijven tot de hermeting ongemoeid).

## 2. Nieuwe pagina's
- `/ai-telefonie-bakkerij` — Service + FAQPage + BreadcrumbList; title "AI telefonie voor
  bakkerijen | AI telefoonassistent die opneemt als jij bakt"; 4 hero-USP's, 4 situaties, 7
  voorbeeldgesprekken, 8 dingen die hij doet, 5 die hij bewust niet doet, eigen nummer via
  doorschakelen, privacy, 4 stappen, prijs, 12 FAQ's, links naar webshop/automatisering/portaal.

## 3. Gewijzigde pagina's
`/` · `/website` · `/webshop` · `/klantenportaal` · `/automatisering` · `/ai` · `/diensten` ·
`/prijzen` · `/vergelijken` (titel) · `/veelgestelde-vragen` (+7 vragen) · `/groeidiamant` (uitleg
per stap) · `/plaatsen/provincie/*` (12) · `/plaatsen/*` (161, titel/kop/lead-varianten) ·
blogposts `altijd-bereikbaar-met-ai` en `offerte-grote-bestelling-automatiseren` (link naar
telefoniepagina) · menu · footer-/organisatienaam.

## 4. SEO-aanpassingen
- Eén intentie per pagina: home = laten maken; telefoniepagina = AI telefonie/telefoonassistent
  bakkerij; webshop = online bestellen; automatisering = bakkerij automatiseren; vergelijken =
  zelf of uitbesteden.
- Structured data: Service en FAQPage op de telefoniepagina, Organization-naam gecorrigeerd,
  FAQPage op /veelgestelde-vragen uitgebreid (16 vragen).
- Sitemap 211 → 212 URL's; robots.txt ongewijzigd; canonicals ongewijzigd; geen redirects nodig,
  geen URL's verwijderd.
- Hero-afbeeldingen op home en telefoniepagina met `width/height` (CLS).
- Interne links met beschrijvende ankers tussen telefonie ↔ webshop ↔ automatisering ↔ portaal.

## 5. AI-telefonie: wat er is geclaimd en waarop dat rust
Alles op de pagina is getoetst aan `bouwsteenwinkel_v3/telefonie` (beleid, README, waakhond):
opnemen, antwoorden uit kennisbank + dagberichten, terugbelverzoek met herhaling ter controle,
doorverbinden binnen openingstijden, samenvatting per mail, log in het klantportaal, geen
opnames, geen agenda-afspraken, geen betalingen. Allergenen: alleen met betrouwbare actuele
productinformatie, anders naar een medewerker. Engels en "beller hoort dat hij met een assistent
spreekt" staan als **inrichtbaar**, niet als standaard.

**Demonummer.** Dennis wil een eigen bakkerijdemo. `config/bakkerij_telefonie.php` →
`demo_nummer` staat leeg; zolang dat zo is toont de pagina "Vraag een demo aan" + "Stel een vraag"
en géén belknop. Zodra het nummer bestaat (zelfde recept als de garagedemo 5160: DID op de trunk,
KANALEN, `beleid/bakkerij/`, fictieve "Bakkerij Kruimel"), het nummer invullen en deployen: de
knoppen "Bel de demo" verschijnen op de telefoniepagina, de homepage en de facetpagina vanzelf.

**Prijsadvies (verwerkt op de pagina, aan te passen in dezelfde config):**
vanaf **€ 89 per maand** tot 200 gesprekken, **€ 0,30** per gesprek daarboven, eenmalig **€ 295**
inrichten en samen testen, maandelijks opzegbaar.
Onderbouwing: het kioskscherm laat zien dat een gesprek ons ± $0,11-0,16 aan OpenAI Realtime
kost (5 gesprekken = $0,54; 44 gesprekken deze maand = $6,81) plus een fractie van het
ElevenLabs-quotum (7% van 124k tekens gebruikt). 200 gesprekken ≈ € 30 aan directe kosten; de
€ 89 dekt dat, het nummer en het bijhouden van de kennisbank. De € 295 dekt het inrichtgesprek,
het omzetten van de gegevens en het testbellen (2-3 uur). Marktreferentie: NL-aanbieders van
AI-receptionisten zitten tussen € 49 en € 199 per maand; € 89 is midden in dat veld met een
duidelijk inbegrepen volume. Wil je scherper instappen: € 69 met 100 gesprekken werkt ook, maar
laat de eenmalige inrichting staan — daar zit ons echte werk.

## 6. Koppelingen: wat er genoemd wordt
Als "werkt standaard": Mollie (betalingen) — bewezen op Bouwsteenwinkel. Als "we bekijken wat
mogelijk is" met voorbeelden: Exact Online, e-Boekhouden, Moneybird (boekhouding) en
kassasystemen in het algemeen. Nergens staat dat een kassa- of boekhoudkoppeling standaard
bestaat.

## 7. Lokale SEO
Zie §1. Architectuur blijft: 12 provinciepagina's (index) + plaatspagina's die de bestaande
gating halen (161 in de sitemap), geen uitbreiding. Meting over vier weken: verschuift het
verkeer van "bakker anjum"-achtige termen naar "website laten maken bakkerij …"?

## 8. Handmatig controleren
1. **De pagina's zelf lezen** — met name `/ai-telefonie-bakkerij` en `/klantenportaal`; de tekst
   is nieuw en jouw toon telt.
2. **`/cases`** heet "Voorbeelden en cases · Zo ziet groei eruit" maar bevat geen echte case
   (besluit 15-09: geen klantnamen). Voorstel: hernoemen naar "Voorbeelden" of de pagina uit het
   menu laten tot er een case is. Niet aangepast, want gedeeld met alle kanalen.
3. **Header-badge "Live binnen enkele dagen"** tegenover FAQ "binnen twee weken live" — gedeelde
   layout, niet aangepast. Kies één belofte.
4. **llms.txt en AI-crawlers**: de WAF-regel van 15-09 blokkeert ClaudeBot/GPTBot behalve op
   `robots.txt`. `llms.txt` is juist voor hen bedoeld. Uitzondering toevoegen op de zones, of
   accepteren dat het bestand alleen door mensen/Google wordt gelezen.
5. **Twee falende tests** (`AppointmentConfirmedTest`) falen ook zonder deze wijzigingen: de
   sqlite-testdatabase mist `channel_sites`. Los van deze opdracht.

## 9. Ontbrekende informatie
- Het bakkerij-demonummer (zie §5).
- Definitieve prijs (nu het advies).
- Cases: bewust geen; zodra een bakkerij klant is, hoort die op `/cases` en op de telefoniepagina.
- Kassasystemen die bakkerijen echt gebruiken (om concreter te kunnen zijn dan "kassasysteem").

## 10. Verdere aanbevelingen
1. Google Ads op "website laten maken bakkerij" en "ai telefonie bakkerij" naar deze twee pagina's;
   de landingspagina's zijn er nu klaar voor.
2. De bakkerijdemo bouwen (beleid + nummer); daarna de ansichtkaart-actie (Canva) ook voor bakkers.
3. Hermeting rond 15-10: Search Console per pagina (home vs. vergelijken op de kernterm;
   telefoniepagina op AI-termen), beacon-bezoeken en het weekoverzicht van aanvragen.
4. Als het werkt: hetzelfde recept (config per branche, telefoniepagina) voor apotheek en
   autogarage — de twee kanalen waar al een demonummer voor bestaat.
