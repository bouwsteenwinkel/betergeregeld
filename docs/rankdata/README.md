# Rankdata — multi-tenant SEO/stats-product

Rankdata is een **white-label multi-tenant product** binnen Betergeregeld V2 (Laravel + Filament).
Een bureau (zoals "Rankdata") beheert klanten; elke klant heeft één of meer websites/applicaties
met statistieken (Search Console / SEO, PageSpeed, uptime).

## Hiërarchie

```
Platform (Betergeregeld, super-admin)
 └─ Bureau / agency  (white-label reseller, bv. "Rankdata")
     └─ Klant (tenant)            ← één login, straks één factuur
         ├─ Website A   (SEO + uptime + PageSpeed)
         ├─ Website B
         └─ Applicatie C
```

> **Modelbeslissing (2026-06-07):** een klant = één `tenant` met een **eigen sites-laag** eronder
> (meerdere websites/applicaties). **Elke** site krijgt SEO + uptime + PageSpeed (ook apps).
> Zie [ROADMAP.md](ROADMAP.md) — de sites-laag wordt nog gebouwd; nu is het nog 1 site per klant.

## Toegang & panels

| Rol | Login-URL | Ziet |
|---|---|---|
| **Super-admin** (platform-team, `users.role='admin'`) | `/admin` (Filament) | Alles: alle bureaus + klanten + platform-resources |
| **Bureau-beheerder** (`role='agency'` + `agency_id`) | `/bureau` (eigen Filament-panel) | Uitsluitend de eigen klanten (beheren + dashboards) |
| **Klant** (`role='client'` + `tenant_id`) | `/nl/login` → `/nl/mijn-statistieken` | Alleen de eigen statistieken (leesbaar dashboard) |

De afscherming is **fail-safe**: het `/bureau`-panel registreert alleen `app/Filament/Bureau/Resources`,
dus een bureau kan principieel niets van het platform zien. `User::canAccessPanel()` gate't per panel-id.

## Datamodel

- `agencies` — bureau (name, slug, contact_email, primary_color, logo_path, subdomain, is_active).
- `tenants.agency_id` — koppelt klant aan bureau (null = directe platform-tenant).
- `users.role` — `admin` (super), `agency` (bureau), `client` (klant). `users.agency_id` voor bureau-beheerders. `users.tenant_id` nullable.
- `seo_properties` (per site) — tenant_id, site_url (`sc-domain:host`), label + SEO-data:
  - `seo_query_daily` — Search Console: query, page, clicks, impressions, ctr, position (per dag).
  - `seo_psi_daily` — PageSpeed: performance/lcp/cls/inp per strategy (mobile/desktop).
- `monitor_checks.tenant_id` (per site uptime) + `monitor_check_results` — beschikbaarheid.

> Nu hangen SEO-property en uptime-check direct aan de tenant (1 site). In de sites-laag
> verhuizen ze naar een site-entiteit onder de klant — zie ROADMAP.

## Onderdelen (code)

- Klant-dashboard: `RankdataDashboardController` + `resources/views/rankdata/dashboard.blade.php` (route `/{locale}/rankdata/{tenant}` en `/mijn-statistieken`).
- Super-admin Filament: `app/Filament/Resources/Agencies` (Bureaus) + `app/Filament/Resources/Clients` (Klanten).
- Bureau-panel: `app/Providers/Filament/BureauPanelProvider.php` + `app/Filament/Bureau/Resources/ClientResource` (klantbeheer, gescopet).
- Demo-data: `database/seeders/RankdataDemoSeeder.php`.

## Demo

Bureau **Rankdata** + 3 klanten, wachtwoord `rankdata-demo`:
- Bureau: `demo@rankdata.nl`
- Klanten: `info@bonen-koffie.nl`, `info@fietsxl-amsterdam.nl`, `info@studionoord-interieur.nl`

## Deploy

Pullen + `config:cache` + `filament:optimize` (nieuwe panels/resources!) + `storage:link` (logo's) + `Stop-Process php-cgi` (OPcache).
Migraties zijn meestal al toegepast want dev = prod-DB. Details in `DEPLOY.md` van het project.

Zie ook: [ONBOARDING.md](ONBOARDING.md) · [ROADMAP.md](ROADMAP.md)
