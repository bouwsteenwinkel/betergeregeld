---
title: "Branche-taxonomie — Betergeregeld nichesites"
---

# Branche-taxonomie — Betergeregeld nichesites

> Canonieke lijst van **lead-branches (sectoren)** en **niche-branches**.
> **Totaal: 202 niche-branches over 14 sectoren.**

Twee niveaus:

- **Lead-branche (sector)** — in code: `app/Models/WebsiteLead.php` → `WebsiteLead::BRANCHES`. Waarmee binnenkomende leads getagd worden.
- **Niche-branche** — in de database (`channel_branches`), beheerbaar in de admin onder **Branches**. Elke niche is een sjabloon (eigen thema + standaard-blueprint) waaronder je één of meer sites hangt.

Bron van waarheid = de database. Aanmaken/aanvullen kan via de admin of via `NicheTaxonomySeeder`.

---

## Bouw / installatie  (`bouw_installatie`) — 30
Loodgieter · Elektricien · CV-installateur · Warmtepomp-installateur · Airco-installateur · Zonnepanelen-installateur · Schilder · Stukadoor · Timmerman · Aannemer · Dakdekker · Dakkapel-specialist · Metselaar · Tegelzetter · Vloerenlegger · Kozijnenbedrijf · Glaszetter · Hovenier · Bestratingsbedrijf · Isolatiebedrijf · Sloopbedrijf · Rioolontstopper · Klusbedrijf · Interieurbouwer · Keukenmonteur · Badkamerspecialist · Zonwering-specialist · Garagedeuren-specialist · Traprenovatie · Asbestverwijdering

## Kapper / schoonheid  (`kapper_beauty`) — 17
Dameskapper · Herenkapper · Barbier · Kapper (algemeen) · Nagelsalon · Schoonheidssalon · Wimperstyliste · Visagist · Pedicure · Permanente make-up · Massagesalon · Zonnestudio · Wellness & spa · Ontharingssalon · Huidtherapeut · Brow bar · Kapper aan huis

## Horeca / restaurant  (`horeca`) — 17
Horeca (algemeen) · Restaurant · Café · Lunchroom · Koffiebar · Cafetaria · Pizzeria · Bezorgrestaurant · Cateraar · Foodtruck · Brasserie · IJssalon · Strandtent · Hotel · Bed & breakfast · Partycentrum · Grillroom

## Winkel / detailhandel  (`detailhandel`) — 20
Bloemist · Slagerij · Bakkerij · Kaaswinkel · Juwelier · Opticien · Fietsenwinkel · Kledingwinkel · Schoenenwinkel · Speelgoedwinkel · Dierenspeciaalzaak · Boekhandel · Cadeauwinkel · Woonwinkel · Slijterij · Drogisterij · Sportwinkel · Meubelzaak · Tuincentrum · Kringloopwinkel

## ZZP / dienstverlening  (`zzp_diensten`) — 24
Fotograaf · Videograaf · Grafisch ontwerper · Webdesigner · Tekstschrijver · Boekhouder · Administratiekantoor · Belastingadviseur · Businesscoach · Loopbaancoach · Consultant · Virtual assistant · Vertaler · Trainer · Marketingbureau · Social media bureau · Notaris · Advocaat · Juridisch adviseur · Architect · Interieurontwerper · Schoonmaakbedrijf · Beveiligingsbedrijf · Uitvaartverzorger

## Sport / fitness  (`sport_fitness`) — 12
Sportschool · Personal trainer · Yogastudio · Pilatesstudio · Dansschool · Vechtsportschool · CrossFit-box · Bootcamp · Tennisschool · Golfschool · Klimhal · Padelclub

## Zorg / praktijk  (`zorg`) — 18
Fysiotherapeut · Tandarts · Huisartsenpraktijk · Psycholoog · Diëtist · Podotherapeut · Logopedist · Ergotherapeut · Osteopaat · Chiropractor · Verloskundige · Orthodontist · Optometrist · Apotheek · Thuiszorg · Kraamzorg · Acupuncturist · Mondhygiënist

## Automotive / garage  (`automotive`) — 12
Autogarage · APK-keuringsstation · Bandenservice · Autoschadeherstel · Car detailing · Autoruit-specialist · Motorzaak · Camperservice · Autobedrijf occasions · Uitlaat & remmen · Auto-airco service · Aanhangwagenspecialist

## Makelaar / vastgoed  (`vastgoed`) — 10
Makelaar · Hypotheekadviseur · Taxateur · VvE-beheer · Vastgoedbeheer · Aankoopmakelaar · Verhuurmakelaar · Bedrijfsmakelaar · Bouwkundig keurder · Energieadviseur

## Onderwijs / opvang  (`onderwijs_opvang`) — 8
Kinderopvang · Gastouderbureau · Muziekschool · Rijschool · Bijlesbureau · Tekenacademie · Zwemschool · Peuterspeelzaal

## Recreatie / vrije tijd  (`recreatie_vrije_tijd`) — 10
Reisbureau · Escape room · Indoor speelparadijs · Bowlingcentrum · Camping · Vakantiepark · Sauna · Museum · Pretpark · Fietsverhuur

## Transport / logistiek  (`transport_logistiek`) — 8
Koeriersdienst · Verhuisbedrijf · Taxibedrijf · Touringcarbedrijf · Self-storage · Autotransport · Grondverzet · Kraanverhuur

## Agrarisch / dieren  (`agrarisch_dieren`) — 10
Manege · Hondentrimsalon · Dierenpension · Hoefsmid · Loonbedrijf · Kwekerij · Boerderijwinkel · Imkerij · Dierenarts · Paardenfysio

## Overig  (`overig`) — 6
Tattooshop · Piercingstudio · Wasserij & stomerij · Slotenmaker · Glazenwasser · Ongediertebestrijding
