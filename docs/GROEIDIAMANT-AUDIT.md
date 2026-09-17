# Groeidiamant-pagina: audit (17-09-2026)

Referentie: https://jouw-advocaat-website.nl/groeidiamant. Alles hieronder is gemeten op de live
advocaat-site en in de code; wat ik niet gemeten heb staat als aanname gemarkeerd.

## 1. Huidige implementatie

| Onderdeel | Waar | Bevinding |
|---|---|---|
| Route | `routes/channels.php:93` → `ChannelSiteController@groeidiamant` | Eén generieke route voor **alle** channel-sites (212 in de DB, 17 live, 195 concept op `/_site/{key}`). |
| View | `resources/views/channels/groeidiamant.blade.php` (94 regels) | Eén template: hero + "Waarom een diamant?" + vijf feature-cards + `partials.sales-trust` + `partials.lead-wizard`. |
| Fase-definitie | `config/groeidiamant.php` | `facets`: website, webshop, klantenportaal, automatisering, **ai** (label "AI", tagline "Slimme assistentie die met je meewerkt"). Ook gebruikt door de wizard, de facet-URL's, sitemap en groeipad. |
| Branche-tekst | `config/channel_groeidiamant_per_branche.php` | **Alleen bakkerij** heeft eigen fase-uitleg (5 zinnen). De andere 211 sites tonen de generieke uitleg uit de view (spreekt over "offertes", "je project", "chat"). |
| Branche-tokens | `App\Support\ChannelTokens` (`:trade`, `:trades`, `:zaak`, …) uit `config/channel_places.php` | Beschikbaar, maar de Groeidiamant-view gebruikt ze **niet**: de pagina noemt de branche nergens. |
| Dienstpagina's | `/{facet}` (`_landing/{key}.blade.php` + `config/{key}_landings.php`, 205 configs met alle vijf facetten), `/ai-telefonie-{branche}` (17 kanalen, `config/channel_telefonie.php`), `/diensten`, `/prijzen`, `/werkwijze`, `/contact`, `/afspraak` | Alle bestaan en geven 200 op advocaat. De Groeidiamant linkt er **geen enkele** aan. |
| CTA's | 4× `.btn`, allemaal "Gratis voorbeeld (aanvragen)" naar `#gratis-voorbeeld` | Eén intentie (website-voorbeeld) voor een pagina die vijf diensten uitlegt. |
| Formulier | `partials/lead-wizard.blade.php`, POST `/contact` → `leadStore` | Stap 1 "Wat wil je vooral bereiken?" met vier doelen, waarvan drie → facet `website` en één → `webshop`. Klantenportaal, automatisering en AI zijn **niet kiesbaar**. `goal` is een vrije string (max 60), `facet` wordt genormaliseerd op `groeidiamant.facets`: eigen opties kunnen zonder backendwijziging. |
| SEO-metadata | `@section('title')`/`description` in de view | Identiek op alle sites: "De Groeidiamant: groeien zonder opnieuw te beginnen · {sitenaam}". Geen branche in title of description. |
| JSON-LD | layout: `ProfessionalService` (@graph, org); breadcrumb-partial: `BreadcrumbList` | Geen `WebPage`, geen `Service`, geen FAQ (er is ook geen FAQ). |
| Inkomende links | footer (Groeidiamant-kaart + tekstlink, 3 links), `partials/groeipad` linkt naar `/{facet}`, niet naar `/groeidiamant` | Alleen vanuit de footer. Niet in het menu. |
| Uitgaande links | Home (breadcrumb) en `#gratis-voorbeeld` | Twee. De pagina is geen hub. |
| Woorden | 866 (incl. gedeelde secties) | Waarvan ~380 uniek voor de pagina; de rest is `sales-trust` + wizard die ook op de home staan. |

## 2. Grootste UX-problemen

1. **De pagina beantwoordt geen vraag van de bezoeker.** Er is geen "waar sta ik?"-moment; de vijf fasen zijn leeskaarten zonder klik.
2. **Eén CTA voor alles.** Wie AI-telefonie of een portaal overweegt, krijgt "gratis websitevoorbeeld".
3. **Het merkverhaal staat vooraan** ("Waarom een diamant?" 2 alinea's over hoe een diamant ontstaat) vóór de inhoud.
4. **Stap 5 heet "AI"** met tagline "Slimme assistentie"; de concrete dienst (AI-telefonie, met demo-nummer en eigen pagina op alle 17 live kanalen) is onzichtbaar.
5. **Geen praktijkvoorbeeld, geen FAQ, geen instap-uitleg.** De boodschap "je hoeft niet bij stap 1 te beginnen" staat in één bijzin in de hero.
6. Mobiel: de logo-kaart in de hero neemt de volle breedte (300px beeld) boven de tekst; de rest is een enkele kolom van kaarten. Werkt, maar geen flow.

## 3. SEO-problemen

- Title/description/H1 identiek op 212 sites, zonder branche → duplicate content en geen zoekintentie.
- H1 "Groei zonder ooit opnieuw te beginnen" is een slogan; H2's ("Waarom een diamant?", "Van eerste website tot slimme assistent") dragen geen zoekwoorden.
- Geen interne links naar de vijf dienstpagina's, dus geen hubfunctie; de dienstpagina's linken zelf ook niet terug.
- Geen `WebPage`/`Service`/`FAQPage`-schema; wel org + breadcrumb.
- Fase-uitleg is op 211 sites letterlijk hetzelfde (alleen bakkerij afwijkend).

## 4. CTA-problemen

Vier keer dezelfde conversie-CTA, geen oriëntatie-CTA ("wat past bij mij"), geen dienst-CTA ("bekijk AI-telefonie voor advocaten"), en de wizard laat drie van de vijf diensten niet kiezen.

## 5. Duplicate-content-risico

Hoog: 100 % generiek op 211 van 212 sites. Aandeel branche-specifiek nu ≈ 0 % (bakkerij ≈ 15 %).

## 6. Branche-specifieke tekortkomingen (advocaat als voorbeeld)

- "Verkoop producten of complete pakketten online … bezorgen of afhalen" (webshop) — irrelevant voor een kantoor.
- "Laat klanten … hun project volgen" (portaal), "Offertes, facturen en planning die zichzelf doen" — klusbedrijf-taal.
- AI: "telefoon en chat aanneemt … je offerte voorbereidt" — geen woord over intake, rechtsgebied of conflictcheck; en niets over wat de assistent **niet** doet (juridisch advies), terwijl `config/advocaat_telefonie.php` dat al keurig heeft.
- De bestaande facet-landing `/klantenportaal` heeft op advocaat de titel "Laat klanten hun klus zelf volgen" (auto-gegenereerd). Buiten scope van deze opdracht, maar wel de pagina waar stap 3 straks naartoe linkt; genoteerd als vervolgpunt.

## 7. Huidige interne links

In: footer (2 plekken, alle sites). Uit: Home, `#gratis-voorbeeld`. Dat is alles.

## 8. Technische architectuur (wat we hergebruiken)

- **Tokens**: `ChannelTokens::map($site->get('places'), $site->brancheKey())` levert `:trade`, `:trades`, `:zaak` (advocaat → kantoor, architect → architectenbureau, dietist → praktijk).
- **Landings-configs**: `config/{key}_landings.php` (205 stuks, alle vijf facetten) met per facet `hero.title/sub/usps`, `pains[]`, `zkhw.bullets[]`. Branche-specifiek genoeg om als **fallback per fase** te dienen (sub + usps), zodat de 195 conceptsites niet handmatig hoeven.
- **Telefonie-configs**: `config/{key}_telefonie.php` (17 live kanalen) met `woorden` (bedrijf/bedrijven), `gesprekken[]` (vraag/antwoord), `kan[]`, `niet[]`, `demo_nummer`, `cta_*`. Bron voor de AI-telefonie-sectie en de voorbeeldvragen.
- **Merge-patroon**: `App\Support\TelefonieConfig` (basis + branche, placeholders, ChannelTokens). De Groeidiamant krijgt dezelfde opzet.
- **Design system**: layout-CSS met `--c-*` tokens, `.hero`, `.kicker`, `.eyebrow`, `.feature-card`, `.card`, `.grid.cols-N`, `.btn/.btn-ghost`, `.steps/.step-num`, `.cta-band`, `partials.faq-accordion`, `partials.icon` (SVG, nooit emoji), `partials.breadcrumb` (met BreadcrumbList).
- **Formulier**: `lead-wizard` accepteert al `$facet`; `$goals` is lokaal gedefinieerd maar eenvoudig overschrijfbaar via een view-variabele zonder de backend te raken.
- **Cache**: `CacheChannelPage`-middleware; na deploy `channel:cache-clear` (zie `docs`/memory: overlevende php-cgi-worker houdt oude config vast → deploy 2-3× + cache-clear).

## 9. Kanalen die deze template gebruiken

Alle 212 channel-sites (route is generiek). Live (17): aannemer, acupuncturist, administratiekantoor, advocaat, apotheek, architect, autogarage, badkamerspecialist, bakkerij, bedrijfswebsite, dietist, golfschool, klusbedrijf, loodgieter, rijschool, uitlaat-remmen, yogastudio. Precies deze 17 hebben ook een telefonie-config.
