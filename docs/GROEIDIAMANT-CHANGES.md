# Groeidiamant-pagina: wat er veranderd is (17-09-2026)

Audit: `GROEIDIAMANT-AUDIT.md`. Plan: `GROEIDIAMANT-PLAN.md`. Route `/groeidiamant`, formulier
(POST `/contact`), facet-URL's en footer-links zijn ongewijzigd.

## 1. Gewijzigd

| Bestand | Wat |
|---|---|
| `resources/views/channels/groeidiamant.blade.php` | Volledig nieuw. Elf secties in de volgorde uit de opdracht. Alle tekst uit `$c`. |
| `app/Support/GroeidiamantConfig.php` | Nieuw. Merge basis ← landings-terugval ← branche-laag; tokens; links per fase; `bron` per onderdeel. |
| `config/groeidiamant_basis.php` | Nieuw. Generieke basis (hero, situaties, fasen, praktijk, instap, telefonie, waarom, FAQ, slot, formulier, SEO-patronen). |
| `config/channel_groeidiamant.php` | Nieuw. Register site-key → branche-configbestand (17 live kanalen). |
| `config/{key}_groeidiamant.php` × 17 | Nieuw. Branche-laag voor aannemer, acupuncturist, administratiekantoor, advocaat, apotheek, architect, autogarage, badkamerspecialist, bakkerij, bedrijfswebsite, dietist, golfschool, klusbedrijf, loodgieter, rijschool, uitlaat-remmen, yogastudio. |
| `config/channel_groeidiamant_per_branche.php` | Verwijderd (alleen bakkerij; opgegaan in `bakkerij_groeidiamant.php`). |
| `config/groeidiamant.php` | Label fase 5 "AI" → "AI-telefonie", nieuwe tagline. Key `ai` ongewijzigd (URL's, leads, sitemap, wizard blijven werken). Zichtbaar in de groeipad-selector op de homes en in de admin. |
| `app/Http/Controllers/ChannelSite/ChannelSiteController.php` | `groeidiamant()` geeft `GroeidiamantConfig::for($site)` mee. |
| `resources/views/channels/partials/lead-wizard.blade.php` | `$goals` en `$goalVraag` overschrijfbaar; standaardgedrag op alle andere pagina's ongewijzigd. |
| `resources/views/channels/_landing/telefonie.blade.php` | Terug-link "stap 5 van de Groeidiamant" (hub in twee richtingen). |

## 2. UX

- Hero: branche-H1, lead, primaire CTA "Ontdek jouw volgende stap" (scrolt naar de keuzehulp), secundaire "Gratis websitevoorbeeld aanvragen".
- **De diamant zelf is de presentatie**: een SVG met vijf facetten (stap 1 in de punt, stap 5 in de kroon). Hover, tik of tab op een facet toont eronder wat die stap voor déze branche betekent; klik springt naar de fase-kaart. Geen library, ~25 regels JS, werkt zonder JS (facetten zijn gewone links), `prefers-reduced-motion` uit.
- "Waar sta je nu?": vijf herkenbare situaties (branche-specifiek), elk een klikbare kaart naar de bijbehorende fase.
- Vijf fase-kaarten met nummer, titel, "Wat is het?", "Wat betekent dit voor [branche]?" (+ drie voorbeelden), "Wat levert het op?" en een dienst-CTA.
- Resultaat-strook, praktijk-tijdlijn (Begin / Daarna / Later), instap-sectie, uitgelichte AI-telefonie (donker, belbubbels met branche-vragen, demonummer als dat bestaat), korte "Waarom", FAQ, eind-CTA "Bespreek mijn situatie" (→ `/afspraak?onderwerp=groeidiamant`), wizard.
- Gemeten: 22 kanalen gerenderd (17 live + fysiotherapeut, kapper, tandarts, hovenier, notaris), allemaal 200 (bedrijfswebsite 404 = de blocklist), nergens een ongevulde plaatshouder, nergens "klus" op advocaat. Wizard: keuze "AI-telefonie" zet `goal=ai`, `facet=ai`, formulier POST naar `/contact` ongewijzigd.
- Mobiel (390 px gemeten): één kolom, situatiekaarten als rijen, fase-kolommen gestapeld, geen elementen buiten het scherm (body heeft `overflow-x: clip`, zoals de rest van de site).
- Toegankelijkheid: één H1, H2's volgens §19, facetten focusbaar met zichtbare focusring, `aria-live` op de uitlegkaart, iconen als SVG met tekstlabels, bestaande focus-states uit de layout.

## 3. SEO

- Title en description per branche (`seo_titel`/`seo_omschrijving` in de branche-laag; basis-patroon met `:trades` voor de rest).
- H2's: Waar sta je nu? · De vijf stappen van de Groeidiamant · Wat levert iedere stap op? · Zo kan dit er in de praktijk uitzien · Je hoeft niet bij stap 1 te beginnen · [AI-telefonie-kop per branche] · Waarom de Groeidiamant? · Veelgestelde vragen · Welke volgende stap past bij jou?
- JSON-LD: `WebPage` (met `about`: vijf `Service`-items met naam, URL en branchezin), `FAQPage` (exact de zichtbare vragen), `BreadcrumbList` (bestaand), `ProfessionalService` (layout, bestaand).
- Interne links (advocaat gemeten): `/website`, `/webshop`, `/klantenportaal`, `/automatisering`, `/ai-telefonie-advocaat`, `/afspraak`, elk met beschrijvende ankertekst ("Bekijk cliëntenportalen voor advocaten"). Was: 0 dienstlinks.
- Duplicate content: per branche verschillen H1, intro, vijf fase-omschrijvingen, voorbeelden, resultaten, situaties, praktijkcase, AI-vragen, FAQ-nuance, CTA-ankers en SEO-metadata. Grofweg 55-60 % van de tekst is branche-specifiek op de 17 live kanalen.

## 4. CTA-strategie

A. Oriëntatie: "Ontdek jouw volgende stap" (hero) · B. Dienst: "Bekijk … voor [branche]" (per fase, resultaatstrook, tijdlijn, AI-sectie) · C. Conversie: "Bespreek mijn situatie" (eind-CTA), "Gratis websitevoorbeeld aanvragen" (hero, eind-CTA, wizard). Demonummer als extra CTA op kanalen met een demo (bakkerij 5170, autogarage 5160, apotheek 5150).

## 5. AI-telefonie

- Fase 5 heet "AI-telefonie & slimme assistentie"; link naar `/ai-telefonie-{key}` op de 17 kanalen met telefonie-config, anders `/ai`.
- Eigen sectie met branche-kop, uitleg wat de assistent wél en niet doet (advocaat: geen juridisch advies; apotheek: medicatievragen naar de apotheker; garage: geen technisch oordeel), vier voorbeeldvragen (branche-laag, anders uit `gesprekken` van de telefonie-config), demonummer, CTA.
- Situatiekaart 5, instapkaart "AI-telefonie kan los", FAQ "Kan ik ook alleen AI-telefonie gebruiken?" met branche-antwoord.
- Wizard: "AI-telefonie" is nu kiesbaar als startpunt (facet `ai`).

## 6. Contentarchitectuur

```
GroeidiamantConfig::for($site)
  basis (groeidiamant_basis.php)
  ← per fase 'voor' + 'voorbeelden' uit {key}_landings.php (hero.sub, hero.usps)
  ← {key}_groeidiamant.php (woorden, seo, hero, situaties, fasen, praktijk, telefonie, faq, instap, slot)
  + {key}_telefonie.php: demo_nummer, gesprekken → vragen, woorden
  tokens: :zaak :trade :trades :bedrijf :bedrijven :Bedrijf
  'bron' => per onderdeel basis|landings|branche|telefonie
```

Geen `if ($key === 'advocaat')` in code; alles via config. Nieuwe branche = één bestand + één regel in `channel_groeidiamant.php`.

## 7. Volledig branche-specifiek (17)

aannemer, acupuncturist, administratiekantoor, advocaat, apotheek, architect, autogarage, badkamerspecialist, bakkerij, bedrijfswebsite, dietist, golfschool, klusbedrijf, loodgieter, rijschool, uitlaat-remmen, yogastudio (= alle live kanalen).

Let op: op **bedrijfswebsite** is `/groeidiamant` bewust geblokkeerd (`config/channel_page_blocklist.php`, one-pager); de branche-laag staat klaar voor als dat ooit verandert.

## 8. Nog op terugvalcontent (195 conceptsites op `/_site/{key}`)

Alle overige kanalen: fase-teksten uit hun eigen landings-config (branche-specifiek, gemeten op fysiotherapeut: "Verkoop verzorgingsproducten en cadeaubonnen online…"), hero/situaties/praktijk/AI-vragen/FAQ uit de basis met `:zaak`/`:trades` ingevuld ("laat je praktijk stap voor stap…"). Bij live-gang van een kanaal: branche-laag toevoegen (30 min per kanaal). `GroeidiamantConfig::bronnen($site)` geeft per onderdeel de laag terug.

## 9. Openstaand

- De VPS-deploy is geblokkeerd door lokale wijzigingen in `config/autogarage_telefonie.php` en `config/bakkerij_telefonie.php` op de server (`git checkout --` nodig, zie eerdere melding). Tot die tijd staat dit alleen in git.
- De bestaande facet-landing `/klantenportaal` op advocaat heeft een auto-gegenereerde titel met "klus" ("Laat klanten hun klus zelf volgen"). Buiten deze opdracht; wel de pagina waar stap 3 naartoe linkt.
- De groeipad-selector op de homes toont nu "AI-telefonie" als vijfde stap (langer label); op 390 px staan de stappen al onder elkaar, dus geen probleem gemeten, wel even meekijken op de live homes na deploy.
- Geen nieuwe routes; `/groeidiamant` staat in de sitemap (bestond al).

## 10. Vervolg

1. Branche-laag schrijven zodra een conceptsite live gaat (template: `advocaat_groeidiamant.php`).
2. `/klantenportaal`- en `/webshop`-landings van dienstverlenende branches (advocaat, architect, administratiekantoor) nalopen op productentaal.
3. Menu-item "Groeidiamant" overwegen (nu alleen footer + telefoniepagina + groeipad naar facetten).
4. Na deploy: Search Console URL-inspectie op `jouw-advocaat-website.nl/groeidiamant` en `jouw-bakkerij-website.nl/groeidiamant`.
