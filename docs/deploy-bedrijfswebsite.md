# Deploy-runbook: kanaal bedrijfswebsite (jouw-bedrijfswebsite.nl)

Go-live-draaiboek voor het channel-kanaal `bedrijfswebsite`. Loop dit van boven naar
beneden door; de volgorde is niet vrijblijvend.

> **Dit document is een aanvulling op [`DEPLOY.md`](../DEPLOY.md).** Alles wat platform-breed
> is (`.env`-basis, OPcache, storage-symlink, bestandsrechten, scheduler-cron, Mollie,
> rollback-principe) staat daar en wordt hier niet herhaald. Hier staat alleen wat
> specifiek is voor dit kanaal.

Platform-aannames zijn identiek aan DEPLOY.md: Plesk-Windows, PHP 8.3, MariaDB, IIS/FastCGI.
Commando's draai je vanuit de project-root op de server.

## 0. Geautomatiseerde check vooraf

```powershell
php artisan channels:preflight --channel=bedrijfswebsite
```

Dit command controleert het merendeel van de handmatige stappen uit hoofdstuk 3
(mailconfiguratie, CMP-tenant en -domein, migraties, site-status, AI-sleutels,
beschikbaarheid). Draai het twee keer: eenmaal vóór de deploy (om te zien wat je te
wachten staat) en eenmaal ná de deploy plus handwerk (dan hoort het schoon te zijn).

Het runbook blijft leidend voor de stappen die preflight niet kán zien: de IIS-timeout,
SPF/DKIM en de DNS-kant.

## 1. Uitgangssituatie: wat er nu live misgaat

De live site draait code van vóór de laatste reeks commits op `main`. Er is nooit een
Plesk-pull gedaan. Zichtbare symptomen op het live domein:

| Symptoom | Oorzaak | Opgelost door |
|----------|---------|---------------|
| Kop luidt nog "Zie jouw nieuwe website in 60 seconden" | oude homepage-copy | `e8cd24a` + `bc7b1fa` |
| `{plaats}` staat letterlijk in de tekst op de branchepagina's | placeholder werd niet vervangen | `0f3c458` |
| Voorbeeld-tool levert vermoedelijk een 404 op de preview | greedy catch-all van de domeingroep ving `/_site/...` af | **`6c25ef6`** |

`6c25ef6` is de belangrijkste. De catch-all route van de domeingroep (`Route::any '{any}'
where '.*'`) ving elk pad af, inclusief `/_site/preview-xxx`, en domein-routes staan altijd
vooraan in de routecollectie. De fix sluit `_site/`-paden uit via een negatieve lookahead.
Zonder deze pull is de funnel op het live domein stuk: de bezoeker doorloopt de generatie
van circa 35 seconden en landt daarna op een 404.

Ga er dus van uit dat de tool live **niet** werkt tot deze deploy erdoor is, en verifieer
dat expliciet in hoofdstuk 4.

## 2. Deploy-volgorde

Houd deze volgorde aan. Caches vóór de migraties draaien betekent dat je oude config
cachet; een FastCGI-restart vóór de caches betekent dat OPcache de oude bytecode vasthoudt.

### 2.1 Back-up

```powershell
mysqldump -u betergeregeld -p db_betergeregeld > backup-bedrijfswebsite-%date:~-4%%date:~3,2%%date:~0,2%.sql
```

Doe dit altijd. Migraties zijn in prod niet terug te draaien (zie hoofdstuk 5).

### 2.2 Git pull

Plesk → Domains → betergeregeld.com → **Git** → repository selecteren → **Pull Updates**.
Controleer daarna dat `6c25ef6` daadwerkelijk binnen is:

```powershell
git log --oneline -1
git log --oneline | Select-String 6c25ef6    # moet een regel teruggeven
```

Is de tweede regel leeg, dan is de pull niet gelukt of wijst de deploy naar een andere
branch. Niet verder gaan.

### 2.3 Migraties

```powershell
php artisan migrate --force
```

`--force` is hier **verplicht**, niet optioneel. Zonder die vlag vraagt Laravel in
`APP_ENV=production` om een interactieve bevestiging, en de Plesk-console of een
scheduled task kan die prompt niet beantwoorden. Het command hangt of breekt af, en
je hebt dan half gemigreerd.

Deze migraties horen na afloop aanwezig te zijn (de lijst is een oriëntatie, geen
afvinklijst: `migrate --force` draait sowieso alles wat openstaat):

```
2026_06_28_120000_create_website_leads_table.php
2026_06_28_130000_add_intake_to_website_leads.php
2026_06_29_120000_add_facet_to_website_leads.php
2026_07_14_140000_extend_website_leads_account.php
2026_07_14_140100_create_saved_previews_table.php
2026_07_04_141000_create_availability_rules_table.php
2026_07_04_141002_create_availability_exceptions_table.php
2026_07_04_141005_create_appointments_table.php
2026_07_15_120000_add_reminder_columns_to_appointments.php
2026_07_16_090000_retime_appointment_reminders_and_track_calendar_sync.php
```

De laatste zet de herinnerings-cadans om naar 2 dagen + de dag zelf en **verwijdert** de
oude kolommen `reminder_24h_sent_at`/`reminder_1h_sent_at`. Afspraken die vlak voor deze
deploy al een 24u-mail kregen, krijgen daarna gewoon hun dag-van-mail: een ander bericht,
geen dubbele.

Let op de datumvolgorde: de `07_04`-scheduling-migraties zijn later toegevoegd dan de
`07_14`-migraties. Controleer daarom op aanwezigheid, niet op batchnummer:

```powershell
php artisan migrate:status | Select-String "Pending" | Measure-Object -Line   # verwacht 0
```

**Draai geen `db:seed` voor dit kanaal.** `BedrijfswebsiteSiteSeeder` doet een
`updateOrCreate` met `'status' => 'draft'` hardcoded en overschrijft ook `brand`, `meta`,
`theme` en `header`. Een re-seed op prod zet een live site dus stilzwijgend terug op draft
en gooit je handwerk uit hoofdstuk 3 weg. Zie 3.6 voor de gerichte alternatieven.

### 2.4 Caches

```powershell
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

`route:cache` is voor dit kanaal extra belangrijk: de routevolgorde bepaalt of de
`/_site/`-fix uit `6c25ef6` werkt. Faalt `route:cache` op een closure-route, dan draait de
app nog steeds, alleen trager (zie DEPLOY.md hoofdstuk 2).

Wijzig je hierna nog iets in `.env` (hoofdstuk 3), dan moet `config:clear` +
`config:cache` er opnieuw overheen. Een gecachete config leest `.env` niet meer.

### 2.5 FastCGI-restart

Plesk → **Services** → FastCGI herstarten. Nodig omdat `opcache.validate_timestamps=0`
staat: zonder restart blijft de oude bytecode draaien en lijkt de deploy niets gedaan
te hebben.

## 3. Handwerk: wat niet uit git komt

Dit zijn de stappen die geen enkele pull voor je regelt. Sla er één over en de site is
technisch live maar functioneel stuk, meestal zonder foutmelding.

### 3.1 MAIL_FROM_ADDRESS (kritiek)

`config('mail.from.address')` is de **afzender** van alle uitgaande mail. Staat hier de
voorbeeldwaarde, dan vertrekt er mail namens een niet-bestaand domein en filtert de
ontvanger hem weg.

```ini
MAIL_FROM_ADDRESS="no-reply@betergeregeld.com"
MAIL_FROM_NAME="Beter Geregeld"
```

Verifiëren:

```powershell
php artisan tinker --execute="echo config('mail.from.address');"   # nooit hello@example.com
```

**De ontvanger van interne meldingen is losgetrokken** van deze afzender. Nieuwe boeking
(`AppointmentController::notifyInternal`), nieuwe lead (`ChannelSiteController::notifyInternal`)
en de agenda-/mailstoringen uit `BookingService` gaan naar `SCHEDULING_NOTIFY_EMAIL`
(default `info@bouwsteenwinkel.nl`). Dat is bewust: `MAIL_FROM_ADDRESS` is een afzender,
geen postbus die iemand leest, en op de voorbeeldwaarde `hello@example.com` verdwenen al
die meldingen geruisloos.

```ini
SCHEDULING_NOTIFY_EMAIL=info@bouwsteenwinkel.nl
SCHEDULING_ORGANIZER_EMAIL=info@bouwsteenwinkel.nl
```

`SCHEDULING_ORGANIZER_EMAIL` is het Reply-To op de bevestigings- en herinneringsmails; die
nodigen expliciet uit om te antwoorden, dus dit moet een postbus zijn die je leest.

"Voorbeeld opgeslagen" (`SavePreviewController`) gebruikt nog wél `mail.from.address` als
ontvanger; die is in deze ronde niet aangeraakt.

### 3.2 MAIL_MAILER + SPF/DKIM

```ini
MAIL_MAILER=smtp
```

`log` en `array` zijn geen prod-waarden: bij `log` verdwijnt alles in
`storage/logs/laravel.log` en vertrekt er niets. Een typefout in `MAIL_MAILER` geeft pas
een exception op het moment van verzenden, en die wordt door de `try/catch` in de
notify-methodes opgeslokt. Controleer dus expliciet:

```powershell
php artisan tinker --execute="echo config('mail.default');"        # verwacht: smtp
```

Let ook op `MAIL_USERNAME`/`MAIL_PASSWORD`: in `.env.example` staat daar de letterlijke
string `null`, en `MAIL_HOST` staat default op `127.0.0.1`.

SPF en DKIM zijn losse handelingen bij de DNS-provider, niet in Plesk:

- **SPF**: de verzendende host moet in de TXT-record van het afzenderdomein staan.
- **DKIM**: sleutel aanzetten en de publieke record publiceren.

Zonder beide landen bevestigingsmails van afspraken in de spam. Test met een echt
Gmail-adres en bekijk **Originele weergave**: `SPF: PASS` en `DKIM: PASS`.

### 3.3 CMP: marketing-categorie aanzetten

De channel-sites laden de CMP met tenant `channels`, hardcoded in
`resources/views/channels/layout.blade.php`. Die tenant bestaat alleen als de seed ooit
gedraaid is (er zijn geen migraties voor die tabellen):

```powershell
php artisan cmp:seed-channels
```

Zet daarna de marketing-categorie aan. Blijft die uit, dan kan de bezoeker er nooit mee
instemmen en blijft `ad_storage` permanent op denied, wat betekent dat Google Ads-conversies
nooit binnenkomen.

CMP-admin → tenant `channels` → categorie **marketing** → inschakelen.

Controle in de database (`cmp_categories` heeft geen `name`-kolom, de labels zitten in
`cmp_texts`):

```sql
SELECT `key`, is_required, is_enabled FROM cmp_categories WHERE tenant_key = 'channels';
```

Verwacht vier rijen, alle vier met `is_enabled = 1`:

```
necessary   req=1  en=1
functional  req=0  en=1
analytics   req=0  en=1
marketing   req=0  en=1
```

De `analytics`-categorie is een harde voorwaarde voor stap 3.5: zonder die rij mapt
`analytics-head.blade.php` nooit `analytics_storage` naar granted en vuurt GA4 niet, hoe
correct je GTM-id ook is.

### 3.4 CMP: domein toevoegen

`cmp:seed-channels` voegt het live channel-domein **niet** toe; het command zegt dat zelf
ook. Standaard staat alleen `betergeregeld.com` in `cmp_domains`.

`CmpService::saveConsent()` resolvet `domain` naar een `domain_id` en laat die op `null`
als het domein ontbreekt. De consent wordt dan wél opgeslagen, maar zonder
domeinkoppeling, dus je consent-administratie is onbruikbaar zodra je hem nodig hebt.

CMP-admin → tenant `channels` → domeinen → `jouw-bedrijfswebsite.nl` toevoegen, status
`active`. Of rechtstreeks:

```sql
INSERT INTO cmp_domains (tenant_key, domain, is_primary, status, created_at, updated_at)
VALUES ('channels', 'jouw-bedrijfswebsite.nl', 0, 'active', NOW(), NOW());
```

### 3.5 Analytics-id in de env

```ini
ANALYTICS_GTM_ID=GTM-XXXXXXX
```

Geen van de drie analytics-sleutels staat in `.env.example`; leeg betekent dat er niets
laadt (bewust, maar bij go-live wil je dit wel).

| Sleutel | Env | Vorm |
|---------|-----|------|
| `analytics.gtm_id` | `ANALYTICS_GTM_ID` | `GTM-XXXXXXX` |
| `analytics.ga4_id` | `ANALYTICS_GA4_ID` | `G-XXXXXXXXXX` |
| `analytics.ads_id` | `ANALYTICS_ADS_ID` | `AW-123456789` |

Vul `ANALYTICS_GA4_ID` **niet** in als je GTM draait. Beide gevuld betekent dubbel getelde
pageviews; hang GA4 in de GTM-container. Controleer ook het prefix, een verkeerd geplakt
id faalt volledig stil in de browser.

Na het wijzigen: `config:clear` + `config:cache`.

### 3.6 Nav-CTA: de header-kolom bijwerken

De CTA rechtsboven op elke pagina komt uit de `header`-kolom van de site-rij. Werk die
gericht bij, dus **niet** via een re-seed (die reset ook `brand`, `meta`, `theme` en
`status`, zie 2.3).

Huidige waarde controleren:

```powershell
php artisan tinker --execute="echo json_encode(\App\Models\Channel\Site::where('key','bedrijfswebsite')->first()->header);"
```

Verwacht:

```json
{"pitch_strip":false,"cta":{"label":"Maak gratis voorbeeld","href":"voorbeeld-maken"}}
```

Gericht aanpassen:

```powershell
php artisan tinker --execute="\$s = \App\Models\Channel\Site::where('key','bedrijfswebsite')->first(); \$s->header = ['pitch_strip' => false, 'cta' => ['label' => 'Maak gratis voorbeeld', 'href' => 'voorbeeld-maken']]; \$s->save();"
```

De CTA moet naar de self-service tool wijzen (`voorbeeld-maken`), niet naar het
contactformulier onderaan. De tool is de sterkste hook in de funnel; de lead-wizard is de
fallback.

### 3.7 IIS/FastCGI request-timeout naar 60 seconden

De voorbeeld-tool doet een Claude-call van circa 35 seconden en daarna nog
beeldgeneratie. De standaard FastCGI-timeout kapt dat af, en de bezoeker ziet een
504 na een halve minuut wachten: precies op het punt waar hij het meest geïnvesteerd is.

Plesk → Domains → **PHP Settings**:

- `max_execution_time = 60` (of hoger)

IIS-kant (FastCGI-instellingen voor de PHP-handler):

- `activityTimeout` en `requestTimeout` op minimaal `60`

Zet het niet onnodig hoog; 60 tot 90 seconden is genoeg en beschermt je tegen hangende
workers.

### 3.8 Site van draft naar live

Dit is de scherpste check van allemaal: de site staat op dit moment op **`draft`**.

`ChannelSite::isLive()` eist twee dingen: `status === 'live'` **en** een niet-leeg
`domain`. `baseUrl()` hangt daaraan: is de site niet live, dan genereert hij
`/_site/{key}`-URL's in canonicals en sitemap. De status bepaalt ook de `noindex`, dus
zolang je op draft staat wordt de site niet geïndexeerd.

Voorkeursroute (regelt ook DNS, Plesk-alias, SSL en Search Console):

```powershell
php artisan channel:go-live bedrijfswebsite
```

Alleen de status omzetten, als de rest al staat:

```powershell
php artisan tinker --execute="\$s = \App\Models\Channel\Site::where('key','bedrijfswebsite')->first(); \$s->status = 'live'; \$s->save(); echo \$s->status;"
```

Controleer daarna dat beide voorwaarden kloppen:

```powershell
php artisan tinker --execute="\$s = app(\App\Services\ChannelSiteResolver::class)->byKey('bedrijfswebsite'); var_dump(\$s->isLive(), \$s->baseUrl());"
```

Verwacht `true` en `https://jouw-bedrijfswebsite.nl`. Krijg je `true` met een
`/_site/`-URL, dan is het domein leeg.

### 3.9 Beschikbaarheid in availability_rules

`availability_rules` is leeg. De `SlotEngine` valt dan terug op
`config('scheduling.default_hours')`, dus maandag tot en met vrijdag 09:00 tot 17:00.
Dat is een prima default, maar het is wel een default en geen keuze: klopt hij niet met
de werkelijkheid, dan boeken klanten je vol op momenten dat je er niet bent.

Let op deze valkuil: `SlotEngine` bepaalt de fallback met
`AvailabilityRule::where('active', true)`. Staan er wél regels maar allemaal op
`active = 0`, dan is de collectie leeg en valt hij **terug op default_hours**. Wie alle
regels uitvinkt om de agenda te sluiten, zet hem juist open op ma-vr 9-17. Sluiten doe je
dus niet met uitvinken.

```powershell
php artisan tinker --execute="echo \App\Models\AvailabilityRule::count(), ' regels, ', \App\Models\AvailabilityRule::where('active', true)->count(), ' actief';"
```

Weekdagen zijn ISO: 1 is maandag, 7 is zondag.

Aanvullend, uit `config/scheduling.php`: controleer de tijdzone (`Europe/Amsterdam`),
`organizer_email` (het Reply-To dat de klant terugmailt, `SCHEDULING_ORGANIZER_EMAIL`) en
`notify_email` (waar de afspraakmelding en de storingsalarmen heen gaan,
`SCHEDULING_NOTIFY_EMAIL`).

`organizer_email` bepaalt **niet** in welke agenda de afspraak landt — dat doet uitsluitend
het Google-account waarmee je koppelt (zie 3.10). Verwar die twee niet: eerder suggereerde
dit runbook dat `organizer_email` de bestemming was, terwijl de waarde toen door geen enkele
regel code werd gelezen.

### 3.10 Agenda-koppeling

`GOOGLE_CALENDAR_ENABLED` staat standaard op `false`. De binding in `AppServiceProvider`
valt dan terug op `StubCalendarGateway`: afspraken worden bevestigd naar de klant maar
landen in géén enkele agenda, en de site meldt gewoon "je afspraak staat". Dat is te
overzien zolang je het wéét.

Zet je hem aan, dan moet de koppeling ook echt staan. `GoogleCalendarGateway::isConnected()`
eist een `client_id`, een opgeslagen tokenbestand én een `refresh_token` daarin. Is dat
niet compleet, dan valt de provider **stil** terug op de stub: `enabled=true` is dus geen
garantie.

**Koppelen met het juiste account.** De afspraken horen in de agenda van
`GOOGLE_CALENDAR_ACCOUNT` (standaard `info@bouwsteenwinkel.nl`). Ga naar
`/admin/google-agenda`, klik verbinden en log in met **dat** account. Koppel je per ongeluk
met een ander Google-account, dan weigert `exchangeCode()` de koppeling en draait hij 'm
terug: zonder die controle stonden alle afspraken in een privé-agenda en merkte je dat pas
als er iemand voor een lege kamer zat.

**Laat `GOOGLE_CALENDAR_ID` op `primary` staan.** Dat is de hoofdagenda van het gekoppelde
account, en dus al de agenda van `info@bouwsteenwinkel.nl`. Vul hem alleen in voor een
bewust gedeelde, niet-primaire agenda.

```powershell
php artisan channels:preflight --channel=bedrijfswebsite
```

De agenda-check toont nu het **echt gekoppelde account** en faalt als dat afwijkt van
`GOOGLE_CALENDAR_ACCOUNT`. Eerder toonde hij alleen `calendar_id` (`primary`), en dat is
dezelfde tekst of je nu met de zakelijke of met een privé-agenda gekoppeld bent.

Wil je alleen weten welke provider actief is:

```powershell
php artisan tinker --execute="echo get_class(app(\App\Services\Scheduling\Contracts\CalendarGateway::class));"
```

Verwacht `GoogleCalendarGateway` als je gekoppeld bent, `StubCalendarGateway` als je dat
bewust niet bent.

**Fail-closed.** Kan de agenda niet gelezen worden (token ingetrokken, Google traag, storing),
dan geeft `/afspraak/beschikbaarheid` een 503 en weigert `/afspraak/boeken`. Dat is bewust:
zacht terugvallen betekende hier "de agenda lijkt leeg", en dan boekt een bezoeker dwars
over een bestaande afspraak heen. Een tijdelijk onboekbare widget is minder erg dan een
dubbele boeking. De widgets tonen in dat geval "we kunnen de agenda nu even niet uitlezen",
niet "geen momenten beschikbaar".

**Het tokenbestand.** Het refresh-token staat in `storage/app/private/google-agenda.json`.
Dat is geen onderdeel van de git-pull, maar het overleeft een release-swap of een schone
checkout **niet** vanzelf: neem `storage/` mee in je rollback-plan, anders val je na een
rollback stil terug op de stub-agenda.

### 3.10b Afspraak-herinneringen

De cadans is **2 dagen vooraf** en **de ochtend van de afspraak zelf** (standaard 08:00),
in te stellen met `SCHEDULING_REMINDER_LEAD_DAYS` en `SCHEDULING_REMINDER_DAY_OF_HOUR`.

Het command `appointments:send-reminders` draait elk kwartier via de scheduler. Zonder een
werkende `schedule:run`-cron (zie DEPLOY.md §6) gaat er **geen enkele** herinnering uit, en
dat is van buitenaf onzichtbaar: je ziet het pas aan de no-shows. Het staat daarom sinds
deze release onder de cron-monitor (`config/monitor.php` → `internal_crons`), die alarm
slaat als het stilvalt.

```powershell
php artisan appointments:send-reminders   # veilig: idempotent, stuurt alleen wat toe is
```

Wie binnen beide verzendmomenten boekt (vanochtend geboekt voor vanmiddag) krijgt géén
herinnering meer. Dat is opzet: de bevestigingsmail is dan nog vers.

### 3.11 AI-sleutels

| Dienst | Sleutel | Zonder sleutel |
|--------|---------|----------------|
| Tekst (Claude) | `ANTHROPIC_API_KEY` of `ANTHROPIC_KEY_PATH` | `RuntimeException`, `/voorbeeld-maken` is stuk |
| Beeld (OpenAI) | `CHANNEL_IMAGES_OPENAI_KEY`, anders `OPENAI_API_KEY` | fake-mode, SVG-placeholders met de prompt erin |

De tekstkant is hard: geen sleutel betekent een exception en een dode funnel. De beeldkant
faalt zachter maar zichtbaar lelijk. `ChannelImageGenerator::isFake()` beschouwt ook een
sleutel die begint met `fake` of `xxxx` als nep, dus een meegereisde testwaarde valt hier
door de mand.

```powershell
php artisan tinker --execute="echo app(\App\Services\ChannelSites\ChannelImageGenerator::class)->isFake() ? 'FAKE' : 'ECHT';"
```

**Bekende beperking:** `PreviewSiteGenerator::model()` leest `ANTHROPIC_MODEL_PREVIEW` via
een kale `env()` buiten een config-bestand. Na `config:cache` geeft die `null` en valt de
generator stil terug op het vertaalmodel. Zet je die env-variabele op prod, ga er dan van
uit dat hij **niet** werkt tot dit naar `config/` verhuisd is.

### 3.12 Scheduler

De platform-cron staat beschreven in DEPLOY.md hoofdstuk 6. Voor dit kanaal draaien er
twee extra taken via `routes/console.php`:

| Tijd | Command | Doel |
|------|---------|------|
| ieder uur | `channel:previews-cleanup` | verlopen, niet-opgeëiste previews opruimen |
| 09:00 | `previews:send-reminders` | herinnering naar bewaarde voorbeelden |

Beide draaien met `onOneServer()` en `withoutOverlapping()`, maar zijn **niet** bewaakt
via `CronMonitorPinger::watch()`, in tegenstelling tot de bookkeeping- en blog-jobs.
Vallen ze stil, dan merkt niemand het. Controleer dus actief dat ze geregistreerd zijn:

```powershell
php artisan schedule:list | Select-String "previews"    # verwacht 2 regels
```

## 4. Verificatie na de deploy

Doorloop dit in deze volgorde en in een **incognitovenster** (anders vertroebelen oude
consent-cookies en sessies het beeld).

### 4.1 App leeft

```powershell
curl https://jouw-bedrijfswebsite.nl/up          # verwacht 200
```

### 4.2 Homepage

Open `https://jouw-bedrijfswebsite.nl/`

- [ ] De kop is **niet** meer "Zie jouw nieuwe website in 60 seconden". Staat die er nog, dan draait de oude code: de pull of de FastCGI-restart is niet aangekomen.
- [ ] De CTA rechtsboven leest "Maak gratis voorbeeld" en linkt naar `/voorbeeld-maken` (stap 3.6).
- [ ] Geen `/_site/`-URL in de broncode: `view-source:` en zoek op `rel="canonical"`. Staat daar `/_site/bedrijfswebsite`, dan is `isLive()` false (stap 3.8).
- [ ] Geen `noindex` in de broncode.

### 4.3 Branchepagina's: {plaats} is weg

Open een branchepagina en zoek letterlijk op `{plaats}`:

```powershell
curl -s https://jouw-bedrijfswebsite.nl/loodgieter | Select-String "{plaats}"   # verwacht: niets
```

Één regel output betekent dat `0f3c458` niet live is. Controleer meerdere branches, de
placeholder zat niet overal.

### 4.4 De voorbeeld-tool, end-to-end

Dit is de belangrijkste test, want dit is precies wat `6c25ef6` repareert. Doe hem
helemaal, niet half.

1. Open `https://jouw-bedrijfswebsite.nl/voorbeeld-maken`.
2. Vul de vier velden met een échte bedrijfsnaam en verstuur.
3. Wacht de circa 35 seconden uit. **Geen 504** betekent dat de timeout uit 3.7 goed staat.
4. Je landt op `https://jouw-bedrijfswebsite.nl/_site/preview-xxxxxxxx`. Let op het domein: de preview hoort op het **channel-domein** te openen, niet op `betergeregeld.com`. Een 404 hier betekent dat `6c25ef6` niet live is of dat `route:cache` niet opnieuw is gedraaid.
5. Bekijk de beelden: echte foto's, geen SVG-placeholder met de prompt erin (stap 3.11).
6. Klik **Bewaar dit voorbeeld** en vul een e-mailadres in.
7. Controleer dat de `WebsiteLead` is aangekomen:

```powershell
php artisan tinker --execute="echo \App\Models\WebsiteLead::latest()->first()?->created_at;"
```

8. Controleer dat de interne melding écht verstuurd is en niet stil is overgeslagen (stap 3.1). Kijk in de inbox van `MAIL_FROM_ADDRESS`, niet alleen in het log.

### 4.5 Afspraak maken

1. Open `https://jouw-bedrijfswebsite.nl/afspraak`.
2. Er staan slots. Geen enkel slot betekent dat `min_notice_hours` (4) of `horizon_days` (21) je blokkeert, of dat de beschikbaarheid niet klopt (stap 3.9).
3. Controleer dat de getoonde tijden kloppen met je werkelijke beschikbaarheid. Zie je precies ma-vr 09:00-17:00, dan draait de config-fallback en niet jouw regels.
4. Boek een afspraak op je eigen adres.
5. De bevestigingsmail komt aan en staat **niet** in spam (stap 3.2). Antwoord er één keer op: het antwoord hoort op `SCHEDULING_ORGANIZER_EMAIL` te landen, niet op de no-reply-afzender.
6. De interne melding komt aan op `SCHEDULING_NOTIFY_EMAIL`, mét tijdstip en Meet-link in de tekst.
7. De afspraak staat in de agenda van `GOOGLE_CALENDAR_ACCOUNT`, met een Meet-link. Staat hij er niet, dan draait 3.10 nog op de stub — controleer met `channels:preflight`, die toont nu het echt gekoppelde account.
8. De klant (jij) heeft óók een Google-uitnodiging gekregen; dat is `GOOGLE_CALENDAR_SEND_UPDATES=all`.
9. Controleer de kolommen `calendar_synced_at` (gevuld) en `calendar_error` (leeg) op de afspraak. Staat er een fout, dan hoort er ook een alarmmail op `SCHEDULING_NOTIFY_EMAIL` te liggen:

```powershell
php artisan tinker --execute="\$a=\App\Models\Appointment::latest('id')->first(); echo \$a->calendar_synced_at, ' | ', \$a->calendar_error ?: 'geen fout', ' | ', \$a->meet_url;"
```

10. Annuleer de testafspraak via de link uit de mail. Het agenda-item verdwijnt uit je agenda **en** de klant krijgt een afzegging van Google.
11. Ruim de testafspraak daarna op.

### 4.5b Herinneringen

De cadans is niet in één sessie na te spelen (2 dagen vooruit), dus controleer hem
gericht. Zet een testafspraak precies op het verzendmoment en draai het command:

```powershell
php artisan appointments:send-reminders
```

Verwacht: "Afspraak-herinneringen verstuurd: 1". Draai hem daarna nog eens; dan hoort er
0 uit te komen (idempotent). Controleer ook dat de cron-monitor de nieuwe entry heeft:

```powershell
php artisan cron:provision-internal
php artisan tinker --execute="echo \App\Models\Monitor\CronMonitor::where('source_key','appointments:send-reminders')->exists() ? 'bewaakt' : 'ONBEWAAKT';"
```

### 4.6 Cookiebanner

1. Incognito, open de homepage. De banner verschijnt.
2. Netwerktab: `/cmp/loader.js?tenant=channels&lang=nl` geeft **200**, geen 404. Een 404 betekent dat de tenant `channels` niet bestaat (stap 3.3).
3. Klik **Alles accepteren**. Controleer dat er een marketing-optie zichtbaar wás; ontbreekt die, dan staat de categorie uit (stap 3.3).
4. Controleer in de netwerktab dat GTM nu laadt en dat de consent-update `analytics_storage: granted` en `ad_storage: granted` meldt.
5. Controleer de consent-registratie in de database. `domain_id` mag **niet** `null` zijn:

```sql
SELECT id, tenant_key, domain_id, status, first_seen_at FROM cmp_consents ORDER BY id DESC LIMIT 1;
```

Is `domain_id` leeg, dan ontbreekt het domein in `cmp_domains` (stap 3.4).

### 4.7 Preflight opnieuw

```powershell
php artisan channels:preflight --channel=bedrijfswebsite
```

Na alle stappen hoort dit schoon te zijn. Blijven er waarschuwingen staan, noteer dan
bewust waarom je die accepteert (de stub-agenda uit 3.10 is een legitiem voorbeeld).

## 5. Rollback per stap

Het algemene principe (vorige release-map op schijf houden, IIS-documentroot terugzetten)
staat in DEPLOY.md hoofdstuk 12. Per stap uit dit runbook:

| Stap | Terugdraaien | Aandachtspunt |
|------|--------------|----------------|
| 2.2 Git pull | `git reset --hard <vorige-sha>` op de server, of documentroot terug naar de vorige release | Noteer de vorige SHA **vóór** de pull: `git log --oneline -1` |
| 2.3 Migraties | Dump terugzetten uit 2.1 | Niet met `migrate:rollback` proberen; niet alle migraties hebben een werkende `down()`. Terugzetten van de dump kost je alle data van ná de dump: eerst leads en afspraken uit de tussentijd wegschrijven |
| 2.4 Caches | `config:clear`, `route:clear`, `view:clear` en daarna opnieuw cachen | Een rollback van code zonder cache-clear geeft een mengsel van oud en nieuw: altijd samen doen |
| 2.5 FastCGI | Nogmaals herstarten | Bij `validate_timestamps=0` is de restart de enige manier om bytecode te verversen |
| 3.1/3.2 Mail | Oude waarde terug in `.env` + `config:cache` | Houd de vorige `.env` bij de hand, Plesk overschrijft hem niet maar jij wel |
| 3.3/3.4 CMP | Categorie weer uitzetten, of `DELETE` op de toegevoegde `cmp_domains`-rij | Reeds vastgelegde consents blijven staan; dat is bewust en juridisch gewenst |
| 3.5 Analytics | `ANALYTICS_GTM_ID` leeghalen + `config:cache` | Leeg is een geldige stand: er laadt dan niets |
| 3.6 Header | `header`-kolom terugzetten via tinker | Kopieer de oude JSON vóór de wijziging |
| 3.7 Timeout | Timeout terug naar de oude waarde | Doe dit alleen als de tool weg is; anders keert de 504 terug |
| 3.8 Status | `status` terug op `draft` | **Snelste noodrem van allemaal.** De site is dan direct uit de index en de funnel dicht, zonder dat je code hoeft terug te rollen. Doe dit als eerste bij twijfel en zoek daarna rustig uit wat er mis is |
| 3.9 Beschikbaarheid | Regels weer op `active = 1`, of verwijderen | Verwijderen betekent terug naar ma-vr 9-17, niet naar "gesloten" (zie de valkuil in 3.9) |

Bij een rollback van de code (2.2) blijven de handmatige stappen uit hoofdstuk 3 gewoon
staan; die zitten in `.env` en de database, niet in git. Dat is meestal wat je wilt.
Uitzondering: gaat de site terug naar de oude code, zet dan ook `status` op `draft`,
anders staat de kapotte voorbeeld-tool wél geïndexeerd live.
