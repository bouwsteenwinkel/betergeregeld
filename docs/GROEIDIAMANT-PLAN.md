# Groeidiamant-pagina: implementatieplan (17-09-2026)

Uitgangspunt: de audit in `GROEIDIAMANT-AUDIT.md`. De route `/groeidiamant`, het formulier
(POST `/contact`), de facet-URL's en de footer-links blijven ongewijzigd.

## Contentarchitectuur

Zelfde patroon als AI-telefonie (`TelefonieConfig`): één basis, één laag per branche, tokens.

```
config/groeidiamant_basis.php          generieke basis: fasen (wat/resultaat/route), keuzehulp,
                                       instap-uitleg, "waarom", FAQ, CTA-labels, SEO-patronen
config/{key}_groeidiamant.php          branche-laag (advocaat, bakkerij, autogarage, + 14 live)
config/channel_groeidiamant.php        site-key → configbestand (zoals channel_telefonie.php)
app/Support/GroeidiamantConfig.php     merge basis ← landings-fallback ← branche; vult :zaak,
                                       :trade, :trades, :bedrijf, :bedrijven; berekent de links
```

Fallback-volgorde per fase (`voor`, `voorbeelden`):
1. branche-config (`{key}_groeidiamant.php`)
2. landings-config van de site (`{key}_landings.{facet}.hero.sub` + `hero.usps`), branche-specifiek genoeg voor de 195 conceptsites
3. basis (generiek, met `:zaak`)

Voor de AI-sectie: `{key}_telefonie.php` (`gesprekken[].vraag`, `woorden`, `demo_nummer`) als die bestaat; anders de vragen uit de branche-config; anders basis.

`config/channel_groeidiamant_per_branche.php` (alleen bakkerij) gaat op in `bakkerij_groeidiamant.php` en wordt verwijderd.

## Bestanden die wijzigen

| Bestand | Wat |
|---|---|
| `resources/views/channels/groeidiamant.blade.php` | Volledig nieuw, volgorde uit de opdracht (§30). |
| `resources/views/channels/partials/lead-wizard.blade.php` | `$goals` overschrijfbaar via view-variabele; standaardgedrag ongewijzigd. Eerste vraag op de Groeidiamant: "Waar wil je mee beginnen?" met de vijf diensten + "Weet ik nog niet". |
| `config/groeidiamant.php` | Label fase 5: "AI" → "AI-telefonie & AI"; tagline. Keys blijven (`ai`), dus URL's en leads breken niet. |
| `app/Http/Controllers/ChannelSite/ChannelSiteController.php` | `groeidiamant()` geeft `GroeidiamantConfig::voor($site)` mee. |
| `config/channel_groeidiamant.php` + 17 × `config/{key}_groeidiamant.php` | Nieuw. |
| `config/groeidiamant_basis.php`, `app/Support/GroeidiamantConfig.php` | Nieuw. |
| `resources/views/channels/_landing/telefonie.blade.php` | Terug-link naar de Groeidiamant (hub in twee richtingen). |
| `docs/GROEIDIAMANT-CHANGES.md` | Eindrapport. |

Geen nieuwe routes. Links per fase: website/webshop/klantenportaal/automatisering → `$site->url('{facet}')`
(bestaande landings), ai → `$site->url('ai-telefonie-{branche}')` als er een telefonie-config is, anders `$site->url('ai')`.

## Paginaopbouw (nieuw)

1. **Hero** — eyebrow "De Groeidiamant van Betergeregeld", H1 "Van website tot AI: laat je :zaak stap voor stap digitaal groeien" (branche-override mogelijk), lead, primaire CTA "Ontdek jouw volgende stap" (→ `#waar-sta-je`), secundaire "Gratis websitevoorbeeld aanvragen" (→ `#gratis-voorbeeld`). Rechts: de diamant als **interactief facet**: vijf vlakken, de fase waar je muis/vinger op staat licht op met de ingevulde branchezin (creatieve presentatie, puur CSS/SVG + 20 regels JS, geen library).
2. **Waar sta je nu?** (`#waar-sta-je`) — vijf klikbare situatiekaarten "Ik heb nog geen sterke website" → website, … "Ik wil minder telefoontjes zelf afhandelen" → AI-telefonie. Kaart = probleem + richting + pijl; link naar de fase-kaart op de pagina (`#fase-website`) met daar de dienst-CTA.
3. **De vijf stappen** — per fase een volwaardige kaart: nummer, titel, "Wat is het?", "Wat betekent dit voor :trades?", "Wat levert het op?", CTA "Bekijk … voor :trades".
4. **Wat levert iedere stap op?** — compacte tabel/strook met per fase het resultaat (branche-aangescherpt waar beschikbaar).
5. **Zo kan dit er in de praktijk uitzien** — branche-scenario als tijdlijn (config-gedreven, 4 stappen met "later"-markering).
6. **Je hoeft niet bij stap 1 te beginnen** — vier instapkaarten (bestaande site blijft, automatisering los, AI-telefonie los, portaal op bestaande omgeving) + de nuancezin over koppelingen.
7. **AI-telefonie voor :trades** — opvallende sectie (donker/accent), H2 branche-specifiek, vier voorbeeldvragen als "belbubbels", demo-nummer als die bestaat, CTA "Bekijk AI-telefonie voor :trades".
8. **Waarom de Groeidiamant?** — drie zinnen.
9. **Veelgestelde vragen** — zes vragen, branche-nuance via config, `FAQPage`-schema.
10. **Welke volgende stap past bij jou?** — eind-CTA: "Bespreek mijn situatie" (→ `/afspraak`) + "Gratis websitevoorbeeld aanvragen".
11. **Formulier** — wizard met eerste vraag "Waar wil je mee beginnen?".

## SEO

- Title: `{seo_titel}` per branche, patroon "Digitale groei voor :trades: website, automatisering & AI-telefonie | Groeidiamant".
- Description per branche (patroon + override).
- Eén H1, H2's exact zoals §19 van de opdracht.
- JSON-LD: `WebPage` (+ `about` Service-lijst met de vijf diensten en hun URL's), `FAQPage` met dezelfde vragen als zichtbaar, `BreadcrumbList` blijft via de partial.
- Canonical blijft uit de layout.

## Formulier

Alleen de eerste stap wijzigt op deze pagina (opties + vraagtekst); velden, volgorde, validatie en backend blijven. `goal`-waarden: `website`, `webshop`, `klantenportaal`, `automatisering`, `ai`, `weet_niet` (facet resp. gelijk aan de key; `weet_niet` → `website`, de default). `leadStore` normaliseert het facet al.

## Validatie

advocaat, bakkerij, autogarage (+ steekproef rijschool als concept-fallback-check via `/_site/`): branchetekst verschilt, links 200, geen verkeerde branchewoorden ("klus" op advocaat), mobiel 390 px, metadata, console, formulier POST, `/groeidiamant` blijft.
