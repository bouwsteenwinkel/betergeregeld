# Rankdata — beslissingen & roadmap

## Modelbeslissingen (2026-06-07)

- **Sites-laag onder de klant.** Een klant (`tenant`) heeft een eigen laag van **websites/applicaties**;
  niet "elke site = aparte klant". Eén klant-login ziet al z'n sites.
- **Elke site krijgt SEO + uptime + PageSpeed** — ook applicaties (dus geen "alleen-uptime" type).
- **Demo:** één demo-klant krijgt 2 websites zodat het multi-site-model concreet zichtbaar is.
- **Bureau in eigen Filament-panel** (`/bureau`), niet in het platform-`/admin` (security: fail-safe afscherming).
- **Klant in een leesbaar front-end dashboard**, niet in Filament.

## Gebouwd (live)

| Onderdeel | Commit |
|---|---|
| Bureau-laag (`agencies`) + rollen + demo-seed | 4cf5868 |
| Leesbaar klant-dashboard (SEO/PSI/uptime) | 09441ae |
| Super-admin Filament: Bureaus + Klanten | 9d88708 |
| Bureau uit platform-admin (security) + tijdelijke front-end portal | 9046dc9 |
| Fix: `/rankdata/{tenant}` 404 (locale positioneel gebonden) | c79da3c |
| Afgeschermd bureau-panel `/bureau` + klantbeheer + branding | a6da41a |

## Nog te bouwen

1. **Sites-laag** (de eerstvolgende grote stap)
   - Site-entiteit onder de klant (naam, URL, type website/app, is_active).
   - `seo_property` en `monitor_check` koppelen aan een site i.p.v. direct aan de tenant.
   - Bureau-panel: per klant sites toevoegen/beheren ("website toevoegen").
   - Klant-dashboard: meerdere sites tonen (overzicht + per-site view / siteswitcher).
   - Demo-klant met 2 websites.
2. **Echte Search Console-koppeling per klant** (de geparkeerde "stap 1")
   - Per klant-property service-account-toegang verifiëren + `seo:import-gsc` per property laten lopen.
   - Nu draait alles op `RankdataDemoSeeder` (geseede cijfers).
3. **PageSpeed API-key** — eigen Google-key voor genoeg dagquota over meerdere klanten.
4. **Facturatie / plannen per bureau** — koppelen aan de bestaande `Plans` / `TenantSubscriptions` / Mollie.
5. **Branding-verfijning** — per-agency primary-color óók in het bureau-panel (nu fixed Indigo; kleur zit al wel op de klant-dashboards). Subdomein-routing (`rankdata.betergeregeld.com`) is infra-werk.

## Open vragen / later

- Hoe ver gaat facturatie (per klant, per site, per bureau)?
- Eigen domeinen per bureau (volledige white-label) — DNS/vhost.
- Rapportage/export (PDF per klant) als verkoopargument.
