# Echte online afspraken op klantsites — plan

**Status:** voorstel, nog niet gebouwd. Geschreven 2026-07-15.

## Waar we nu staan

Er zijn twee dingen die "booking" heten en ze hebben niets met elkaar te maken.

**1. De platform-agenda (echt, werkt).** `channels/partials/booking.blade.php` →
`POST /afspraak/boeken` → `AppointmentController::book()` → `BookingService::book()`.
Dit boekt in **onze eigen** agenda: een kennismaking met Betergeregeld. Er is precies
één agenda voor het hele platform. `SlotEngine` bepaalt vrije momenten uit
`availability_rules` (of `config/scheduling.default_hours` als die tabel leeg is),
minus interne afspraken en minus de Google-agenda via `CalendarGateway`.

**2. Het demo-blok (illustratie, boekt niets).** `channels/blocks/booking.blade.php`,
gezet door `PreviewSiteGenerator::blocksFrom()` op gegenereerde previews. Laat zien
hoe online afspreken eruitziet op de site van de ondernemer. Sinds 2026-07-15 rendert
dit blok alleen nog op previews, zegt het er expliciet bij dat er niets verstuurd of
afgeschreven wordt, en verwijst het naar `/afspraak` voor een echt gesprek met ons.

**Wat er dus niet is:** een ondernemer die klant wordt, kan zijn eigen klanten geen
afspraken laten maken. Dat is wat dit plan beschrijft.

## Waarom het niet "even koppelen" is

Het bestaande systeem is gebouwd rond één agenda, die van ons. Concreet:

- `appointments` heeft **geen `site_id`, `tenant_id` of `user_id`**. `source_site` is
  een vrije string die als label wordt meegeschreven en waar nergens op gefilterd wordt.
  Een boeking op site A blokkeert dus het slot op site B.
- `availability_rules` en `availability_exceptions` gelden **platform-breed**. Er is geen
  manier om te zeggen "de kapper werkt di–za, de garage ma–vr".
- `SlotEngine` en `BookingService` kennen geen eigenaar; ze rekenen over "alle" afspraken.
- De Filament-resources hebben geen scoping: elke beheerder ziet alles.
- Mollie is volledig gebouwd rond **tenant-abonnementen**: `BillingIntent` vereist
  `plan_id` + `period`, en `syncFromGateway()` → `activateSubscription()` maakt altijd een
  `TenantSubscription`. Een aanbetaling voor een knipbeurt past daar niet in.
- Aanbetalingen gaan naar **ons** Mollie-account. Geld van de klant van de kapper hoort
  naar de kapper, niet naar ons. Dat is de kern van de zaak, en het is de reden dat dit
  geen technische bijzaak is maar een ontwerpkeuze met juridische kanten.

## Te nemen beslissingen (deze eerst)

**A. Van wie is het geld?** Drie routes, aflopend in gedoe en oplopend in eenvoud:

1. **Mollie Connect (OAuth)** — de klant koppelt zijn eigen Mollie-account, wij maken
   betalingen namens hem aan. Geld gaat direct naar de klant. Wij kunnen desgewenst een
   fee inhouden. Vereist een Mollie-partneraccount en OAuth-flow. Netst, meeste werk.
2. **Eigen API-key per klant** — de klant plakt zijn Mollie-key in zijn instellingen. Wij
   bewaren die versleuteld. Simpel, maar wij beheren andermans betaalsleutels: dat wil je
   goed dichttimmeren (encryptie at rest, geen logging, intrekbaar).
3. **Geen aanbetaling in v1** — afspraken zonder geld. Verreweg het minste werk en dekt
   waarschijnlijk 80% van de vraag. Aanbetalingen zijn een aparte fase.

**Advies: begin met 3.** De waarde zit in "klanten kunnen zelf een moment kiezen"; de
aanbetaling is een extra dat je erbij kunt bouwen zodra er betalende klanten zijn die er
echt om vragen. Het scheelt je de hele Connect/keybeheer-discussie in v1.

**B. Wiens agenda?** Koppelt de klant zijn eigen Google-agenda (dan hebben we per site een
refresh-token nodig; nu ligt er één token in `Storage::get('google-agenda.json')`), of
beheert hij zijn openingstijden alleen bij ons? Het laatste is veel eenvoudiger en voor
veel vakmensen genoeg.

**C. Waar beheert de klant dit?** Er is nu geen klantportaal voor channel-sites. Filament
is van ons. Zonder beheer-UI moet jij elke openingstijd handmatig invoeren, wat niet
schaalt. Dit is waarschijnlijk het grootste onderschatte deel.

## Voorgestelde aanpak (v1, zonder geld)

1. **Eigenaarschap in het datamodel.** Migratie: `site_id` (nullable FK) op
   `appointments`, `availability_rules`, `availability_exceptions`. `null` = de
   platform-agenda (onze kennismakingen), zodat bestaande data en de huidige widget
   ongemoeid blijven. Dit is de kritieke stap; alles hierna leunt erop.
2. **Scope door de engine.** `SlotEngine` en `BookingService` krijgen een verplichte
   site-context. Zonder context = platform-agenda (huidig gedrag). `busyPeriods()` alleen
   voor de agenda van die site.
3. **Endpoints per site.** `/afspraak/beschikbaarheid` en `/afspraak/boeken` krijgen een
   site-variant, bijvoorbeeld onder `/_site/{key}/…` of met een site-parameter die
   server-side gevalideerd wordt (nooit de site uit de request vertrouwen zonder check).
4. **Het blok echt maken.** `channels/blocks/booking.blade.php` krijgt een echte modus
   die tegen die endpoints praat, met behoud van de demo-modus voor previews. De
   `$isPreview`-schakelaar die er nu in zit, is precies het aanknopingspunt.
5. **Beheer.** Minimaal: openingstijden en uitzonderingen per site in Filament, met
   scoping. Beter: een klantportaal, maar dat is een eigen project.
6. **Bevestigingsmail per site.** `AppointmentConfirmation` gaat nu uit naam van ons.

## Losse eindjes in het bestaande systeem (los van dit plan)

Deze staan er nu al en verdienen aandacht, ongeacht of v1 doorgaat:

- **Twee concurrerende slot-generators.** `app/Support/Intake/AppointmentSlots.php` heeft
  hardcoded tijden (`09:00/11:00/14:00/16:00`, `MIN_LEAD_HOURS = 2`) en weet niets van
  `AvailabilityRule`, de Google-agenda of bestaande afspraken. Dat voedt de lead-wizard,
  waarvan de uitkomst via `ChannelSiteController:~460` de échte `BookingService` in gaat.
  De wizard kan dus een moment aanbieden dat `SlotEngine` afkeurt (config wil 4 uur notice
  en hele uren), en `SlotTakenException` wordt daar **stil geslikt**: de bezoeker denkt dat
  hij geboekt heeft, en er staat niets in de agenda. Dit is een echte bug.
- **`cancel_token` wordt gegenereerd maar er is geen annuleerroute.** `BookingService::cancel()`
  heeft geen enkele caller. Annuleren kan alleen via Filament, en dan blijft het
  Google-event staan.
- **`hold_minutes` is dode config.** De status `held` wordt nergens geschreven, alleen gelezen.
- **Nul tests** op `SlotEngine`, `BookingService` en `AppointmentController`.

## Wat hier bewust niet in staat

Aanbetalingen innen op een preview. Een preview is een demo van een bedrijf dat geen klant
is en niets van die site weet; daar echt geld innen op ons eigen Mollie-account is geen
optie, ongeacht hoe het technisch zou kunnen.
