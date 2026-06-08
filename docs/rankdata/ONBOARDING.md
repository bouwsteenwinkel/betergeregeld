# Rankdata — een nieuwe klant aansluiten

Een klant = **één tenant + één login + één of meer sites** (websites/applicaties).
Per site meten we SEO (Search Console), uptime en PageSpeed.

## Wat we van de klant nodig hebben

Per **site** (website/applicatie):

1. **Het domein / de URL** — bv. `klant.nl`, `webshop.klant.nl`.
2. **Toegang tot hun Google Search Console** — dit is de enige stap die klant-medewerking vereist.
   De klant voegt óns service-account toe als gebruiker op hun Search Console-property:
   - Search Console → **Instellingen → Gebruikers en machtigingen → Gebruiker toevoegen**
   - e-mail: **`969018493175-compute@developer.gserviceaccount.com`**
   - rol **"Beperkt"** (alleen lezen) volstaat
   - Per site herhalen.
   - Zonder dit kunnen we **geen zoekdata** tonen.
3. **Property-vorm** in Search Console: **domein-property** (`sc-domain:` — vangt alle subdomeinen +
   http/https, aanbevolen) of een URL-prefix-property.

Per **klant** (eenmalig):

4. **Contactgegevens + een e-mailadres** voor de klant-login op het dashboard.

**Niet** nodig van de klant: uptime en PageSpeed meten wij zélf, alleen op basis van de URL —
daar hoeft de klant niets voor te doen.

## Wat er aan onze kant gebeurt

1. Bureau maakt in `/bureau` één **klant** aan (tenant + login).
2. **Per site** (super-admin: **SEO → GSC Properties**):
   - `seo_property` aanmaken met de exacte `site_url` (`sc-domain:domein` of `https://domein/`). Het formulier toont het service-account-mailadres dat de klant moet toevoegen.
   - **Verifiëren met de knop "Toegang testen"** (per rij): toont direct of de klant ons account toegang heeft gegeven — en zo niet, welke properties ons account wél mag lezen (handig om de juiste `site_url` te kiezen). Geen wachten op de geplande import om een 403 te ontdekken.
   - **Eerste import met de knop "Nu importeren"** (30 dagen backfill); daarna draait de dagelijkse cron `seo:import-gsc` automatisch verder. CLI-alternatief: `php artisan seo:import-gsc --property=ID --days=30` (haalt tot ~16 maanden historie op).
   - **Uptime-check** aanmaken op de URL (begint meteen te meten).
   - **PageSpeed** inplannen (`seo:run-psi`).
3. Klant logt in op `/nl/login` → ziet al z'n sites met hun cijfers.

## Service-account (referentie)

- e-mail: `969018493175-compute@developer.gserviceaccount.com`
- Google-project: `fluent-oarlock-341621`
- Key-bestand: `storage/app/google-api.json` (gitignored — handmatig op prod plaatsen; gedeeld met bouwsteenwinkel_v3).

## Aandachtspunten / nog te bouwen

- **Echte GSC-koppeling per klant-property werkt** (import loopt al per actieve property; onboarding-verificatie via "Toegang testen" + "Nu importeren"). Demo-tenants draaien nog op geseede data — die staan los van echte properties. Bureau-panel (`/bureau`) heeft de toegang-test-actie nog niet; daar gebeurt onboarding nu via de super-admin **GSC Properties**-resource. Zie [ROADMAP.md](ROADMAP.md).
- **PageSpeed-quota**: de gratis (key-loze) PSI-API heeft een lage dagquota; voor meerdere klanten is een eigen Google PageSpeed API-key nodig.
- **Uptime**: de checker draait al; alleen per-site-koppeling moet mee in de sites-laag.
