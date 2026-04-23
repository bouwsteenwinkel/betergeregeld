<?php

namespace Database\Seeders\Blog;

use Illuminate\Database\Seeder;

class BlogSecuritySeeder extends Seeder
{
	public function run(): void
	{
		BlogSeedHelper::seedCluster(
			[
				'slug' => 'security',
				'name' => 'Security zonder IT-afdeling',
				'pillar_title' => 'Security voor MKB zonder IT-afdeling: wat doe je dit kwartaal?',
				'intro' => 'Praktische security-acties die een ondernemer zonder IT-team zelf kan uitvoeren — zonder eerst een €40k-traject op te tuigen.',
				'sort_order' => 80,
			],
			self::posts(),
		);
	}

	private static function posts(): array
	{
		return [
			[
				'slug' => 'mkb-security-zonder-it-afdeling',
				'title' => 'Security voor MKB zonder IT-afdeling: wat doe je dit kwartaal?',
				'excerpt' => 'Geen IT-team, wel verantwoordelijkheid. Deze pillar geeft een prioriteit-stack: doe eerst dit, dan dat, dan het minder urgente. Elk onderdeel heeft een diepere gids.',
				'is_pillar' => true,
				'featured' => true,
				'tags' => ['security', 'mkb', 'start-hier'],
				'published_offset_days' => 22,
				'body' => <<<'HTML'
<p>Je hebt geen IT-afdeling. Toch ben je verantwoordelijk voor bedrijfs-security. Dat klinkt overweldigend, tot je de prioriteiten kent.</p>

<h2>Kwartaal 1: de basis</h2>
<ol>
  <li>MFA op alles. Zie <a href="/nl/blog/mfa-uitrollen-m365">MFA uitrollen in M365</a>.</li>
  <li>Password manager voor iedereen. Zie <a href="/nl/blog/password-manager-kiezen">een password manager kiezen</a>.</li>
  <li>Disk encryption aan op alle laptops.</li>
  <li>Backup-strategie die je test. Zie <a href="/nl/blog/backup-strategie-mkb">backup-strategie</a>.</li>
</ol>

<h2>Kwartaal 2: de context</h2>
<ol>
  <li>SaaS-inventaris op orde. Zie <a href="/nl/blog/saas-inventaris-opstellen">SaaS-inventaris</a>.</li>
  <li>Offboarding-proces gedefinieerd. Zie <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding</a>.</li>
  <li>Phishing-training voor medewerkers. Zie <a href="/nl/blog/phishing-herkennen-mkb">phishing herkennen</a>.</li>
  <li>Incident-response-plan op papier. Zie <a href="/nl/blog/incident-response-mkb">incident response</a>.</li>
</ol>

<h2>Kwartaal 3: governance</h2>
<ol>
  <li><a href="/nl/blog/periodieke-access-reviews-proces">Access reviews</a>.</li>
  <li>ISO 27001 traject starten als je klanten het vragen. Zie <a href="/nl/blog/iso-27001-mkb-zonder-consultants">ISO 27001</a>.</li>
  <li>Shadow IT ontmantelen. Zie <a href="/nl/blog/shadow-it-opruimen">shadow IT opruimen</a>.</li>
</ol>

<h2>Kwartaal 4: volwassenheid</h2>
<ol>
  <li>Vendor risk management. Zie <a href="/nl/blog/vendor-risk-management-mkb">vendor risk</a>.</li>
  <li>Security-awareness verankerd in onboarding.</li>
  <li>Periodieke phishing-tests.</li>
</ol>

<h2>Wat nooit doet?</h2>
<ul>
  <li>Denken dat je "niet interessant genoeg" bent voor aanvallers. 90% van aanvallen is opportunistisch, gericht op kwetsbare systemen ongeacht bedrijfsnaam.</li>
  <li>Security uitsluitend afdoen met een virus-scanner.</li>
  <li>Wachten op "de grote security-refactor" — doe elke week één ding.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/toegangsbeheer-mkb-complete-gids">toegangsbeheer-pillar</a>, <a href="/nl/blog/avg-compliance-mkb">AVG-pillar</a>, <a href="/nl/blog/iso-27001-mkb-zonder-consultants">ISO 27001-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'phishing-herkennen-mkb',
				'title' => 'Phishing herkennen: wat leer je je team in 20 minuten?',
				'excerpt' => 'Phishing is niet meer die slecht-gespelde Nigerian prince. Moderne phishing is gepersonaliseerd, op maat, in ons eigen bedrijfsnetwerk ingelogd. Hier wat iedereen moet weten.',
				'tags' => ['phishing', 'awareness', 'security'],
				'published_offset_days' => 30,
				'body' => <<<'HTML'
<p>Phishing in 2026 is ingenieus: vrijwel perfect Nederlands, domeinen die precies lijken, geschreven door AI. Toch zijn er signalen die je kunt leren herkennen.</p>

<h2>De 5 rode vlaggen</h2>
<ol>
  <li><strong>Urgentie.</strong> "Als je niet binnen 4 uur klikt, vervalt je toegang." Echt interne communicatie heeft dit nooit.</li>
  <li><strong>Afwijkende afzender.</strong> <em>ceo@bed3rgeregeld.com</em> (3 voor e). <em>noreply@microsoft-security.com</em> (legitiem domein is microsoft.com, niet microsoft-security). Klik op de naam om het echte adres te zien.</li>
  <li><strong>Link hover-check.</strong> Muisover de link. Komt de URL overeen met wat er staat? <em>office365login.com</em> is niet Microsoft.</li>
  <li><strong>Ongewoonlijk verzoek.</strong> "Je CEO vraagt je spoed-cadeaubonnen te kopen." Echte CEO doet dit niet per e-mail.</li>
  <li><strong>Onverwachte bijlage.</strong> Facturen die je niet verwacht, CV's uit het niets. Niet openen zonder verificatie.</li>
</ol>

<h2>Wat train je concreet?</h2>
<ul>
  <li>Gebruik een phishing-simulatie-platform (KnowBe4, Cofense) — kwartaal-campagne.</li>
  <li>Rapporteer-knop in Outlook / Gmail zodat meldingen direct bij IT binnenkomen.</li>
  <li>Geen blame-culture: wie op een phishing-test klikt krijgt extra training, geen afrekening.</li>
  <li>Korte refreshers (5-min-video) elk half jaar.</li>
</ul>

<h2>Wat je NIET doet</h2>
<ul>
  <li>Mensen ontslaan omdat ze erin tuinden. Dat voorkomt melding van echte incidenten.</li>
  <li>E-mail-filters volledig vertrouwen. Moderne phishing komt door de meeste filters heen.</li>
  <li>Alleen "zoek naar typfouten"-regels leren. Moderne phishing heeft geen typfouten.</li>
</ul>

<h2>Bij klik: wat nu?</h2>
<p>Meteen IT/security-verantwoordelijke melden. Wachtwoord wijzigen. MFA-sessies intrekken. Wachtwoord van enige bijkomende geraakte accounts wijzigen. Zie <a href="/nl/blog/incident-response-mkb">incident response</a>.</p>

<p>Zie ook: <a href="/nl/blog/mkb-security-zonder-it-afdeling">security-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'password-manager-kiezen',
				'title' => 'Password manager kiezen voor het MKB: 1Password, Bitwarden, anders?',
				'excerpt' => 'Zonder password manager werkt geen enkele security-strategie. Hier de vergelijking van de drie opties die voor MKB relevant zijn, met concrete afwegingen.',
				'tags' => ['password-manager', '1password', 'bitwarden', 'security'],
				'published_offset_days' => 38,
				'body' => <<<'HTML'
<p>Een password manager is de goedkoopste security-tool die grote impact heeft. Per user €3-8/maand, preventie van 80% van de credential-stuffing-aanvallen.</p>

<h2>1Password Business</h2>
<ul>
  <li>Prijs: €7-8/user/maand.</li>
  <li>Sterkte: UX, admin-dashboard, shared vaults met ACL, audit-log.</li>
  <li>Zwakte: duurste optie.</li>
  <li>Voor wie: sterk marketing/sales-bedrijf, UX belangrijk.</li>
</ul>

<h2>Bitwarden Business / Enterprise</h2>
<ul>
  <li>Prijs: €3-5/user/maand.</li>
  <li>Sterkte: open source, zelf-hostbaar, betaalbaar, geavanceerde enterprise-features.</li>
  <li>Zwakte: UX iets minder dan 1Password.</li>
  <li>Voor wie: tech-bedrijven, prijsgevoelig MKB, compliance-focus.</li>
</ul>

<h2>Keeper Business</h2>
<ul>
  <li>Prijs: €4-6/user/maand.</li>
  <li>Sterkte: sterke enterprise-security-features, compliance-rapportage.</li>
  <li>Zwakte: mindere community, minder integraties.</li>
  <li>Voor wie: compliance-heavy industrieën.</li>
</ul>

<h2>Wat je nodig hebt (minimaal)</h2>
<ul>
  <li>Per-user vault + gedeelde bedrijfs-vaults.</li>
  <li>ACL op gedeelde items.</li>
  <li>Audit log wie heeft wanneer een wachtwoord opgevraagd.</li>
  <li>MFA-authenticator ingebouwd (TOTP naast wachtwoord).</li>
  <li>Browser extensie, mobiele app, desktop-app.</li>
  <li>Offboarding-flow: individuele vault transfer bij vertrek.</li>
  <li>SSO-integratie (optioneel maar bij &gt; 50 users waardevol).</li>
</ul>

<h2>Implementatie in 2 weken</h2>
<ol>
  <li>Week 1: account setup, admin-training, shared vaults inrichten voor bedrijfs-credentials.</li>
  <li>Week 2: uitrol per team, 15-min-demo per team, vanilla-password-migratie begeleiden.</li>
</ol>

<h2>Wat NIET te gebruiken</h2>
<ul>
  <li>Browser-ingebouwde wachtwoord-kluis zonder bedrijfs-controls.</li>
  <li>Spreadsheets.</li>
  <li>Notion-database met wachtwoorden in tekstveld.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/gedeelde-wachtwoorden-beheer">gedeelde wachtwoorden beheer</a>, <a href="/nl/blog/mkb-security-zonder-it-afdeling">security-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'backup-strategie-mkb',
				'title' => 'Backup-strategie voor MKB die je daadwerkelijk test',
				'excerpt' => 'Een backup die je nooit hebt getest is geen backup. 3-2-1-principe, periodieke restore-tests, en welke data prioriteit heeft — het recept voor een werkbaar plan.',
				'tags' => ['backup', 'disaster-recovery', 'security'],
				'published_offset_days' => 46,
				'body' => <<<'HTML'
<p>De waarheid die elke IT-veteraan kent: "we hebben backups" betekent niks. "We hebben backups die we maandelijks testen" is een heel ander verhaal.</p>

<h2>3-2-1-principe</h2>
<ul>
  <li><strong>3 kopieën</strong> van belangrijke data.</li>
  <li><strong>2 verschillende media.</strong></li>
  <li><strong>1 kopie off-site</strong> (andere fysieke locatie, of cloud-opslag in andere regio).</li>
</ul>

<h2>Data-prioritisering</h2>
<ul>
  <li><strong>Tier 1 (moet blijven, kritiek):</strong> boekhouding, CRM-data, klant-documenten. Dagelijkse backup, off-site kopie, restore binnen 4 uur.</li>
  <li><strong>Tier 2 (belangrijk):</strong> e-mail-archief, project-documenten. Dagelijkse backup, restore binnen 24 uur.</li>
  <li><strong>Tier 3 (handig):</strong> oude archieven, marketing-materiaal. Wekelijkse backup.</li>
</ul>

<h2>Bronnen om te back-uppen</h2>
<ul>
  <li>M365 / Google Workspace: gebruik een specifieke M365-backup-tool (Veeam, Backupify). Microsoft backupt niet alles voor je.</li>
  <li>Boekhouding-pakket: export-functie of automatische cloud-backup.</li>
  <li>Server-schijven indien on-prem: Acronis, Veeam.</li>
  <li>Laptops: OneDrive sync + extra backup van user-folders.</li>
</ul>

<h2>Maandelijkse restore-test</h2>
<p>Pak een willekeurig bestand uit een backup van 2 dagen geleden. Kun je het teruggevolgen? Doe dit elke maand. Documenteer de test in je <a href="/nl/blog/incidentenlog-opzetten">log</a>.</p>

<h2>Ransomware-scenario</h2>
<p>Backups die aan je netwerk hangen kunnen mee-encryptie bij ransomware. Air-gapped backups (offline of in een apart account/tenant) zijn je verzekering. Minstens 1 off-site kopie moet niet online bereikbaar zijn voor de aanvaller.</p>

<p>Zie ook: <a href="/nl/blog/incident-response-mkb">incident response</a>, <a href="/nl/blog/mkb-security-zonder-it-afdeling">security-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'incident-response-mkb',
				'title' => 'Incident response plan voor MKB op 2 A4\'tjes',
				'excerpt' => 'Een incident response plan hoeft geen 50-pagina\'s-document te zijn. 2 A4\'tjes met wie doet wat wanneer, is genoeg — als iedereen het kent.',
				'tags' => ['incident-response', 'mkb', 'security'],
				'published_offset_days' => 54,
				'body' => <<<'HTML'
<p>Als er iets gebeurt — ransomware, datalek, verloren laptop met klant-data — heb je 15 minuten om de juiste stappen te zetten. Een plan helpt.</p>

<h2>Inhoud (2 A4)</h2>
<ul>
  <li><strong>Contactgegevens (pagina 1):</strong>
    <ul>
      <li>Incident response-lead (naam + telefoon + back-up).</li>
      <li>Directie / eigenaar.</li>
      <li>AVG/FG indien aanwezig.</li>
      <li>Externe partijen: hosting, accountant, verzekeraar, evt. extern security-bureau.</li>
      <li>Communicatie-lead voor extern (PR).</li>
    </ul>
  </li>
  <li><strong>Triage (pagina 2, bovenste helft):</strong>
    <ol>
      <li>Wat is er gebeurd? (1 zin)</li>
      <li>Containment: kan de schade worden beperkt nu?</li>
      <li>Scope: welke systemen? welke data?</li>
      <li>Ernst: laag / middel / hoog.</li>
    </ol>
  </li>
  <li><strong>Actielijst (pagina 2, onderste helft):</strong>
    <ol>
      <li>Lead aanwijzen (of zelf oppakken).</li>
      <li>Relevante tech-acties (wachtwoorden wissen, systemen afkoppelen, backups isoleren).</li>
      <li>Interne communicatie: wie weet wat.</li>
      <li>Log bijhouden (tijd, actie, wie).</li>
      <li>Externe partijen informeren: klanten, AP (bij datalek), verzekeraar.</li>
      <li>Post-mortem binnen 2 weken.</li>
    </ol>
  </li>
</ul>

<h2>Oefen</h2>
<p>Eén keer per jaar een tabletop-oefening. 1 uur. Scenario voorlezen, iedereen zegt wat ze zouden doen. Vind de gaps in je plan.</p>

<h2>Documentatie tijdens incident</h2>
<p>Op papier (niet in het systeem dat mogelijk gecompromitteerd is). Chronologisch. Maakt post-mortem én verzekeringsclaim veel makkelijker.</p>

<h2>Post-incident</h2>
<ul>
  <li>Lessons learned geplenaard.</li>
  <li>Preventieve maatregelen gepland.</li>
  <li>Update in <a href="/nl/blog/risicomanagement-iso-27001">risk register</a>.</li>
  <li>Incident toegevoegd aan <a href="/nl/blog/incidentenlog-opzetten">incident-log</a>.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/mkb-security-zonder-it-afdeling">security-pillar</a>, <a href="/nl/blog/datalek-melden-avg">datalek melden</a>.</p>
HTML,
			],

			[
				'slug' => 'laptop-diefstal-response',
				'title' => 'Laptop gestolen: de eerste 30 minuten',
				'excerpt' => 'Iemand belt: laptop gestolen uit de auto. Klok tikt. Hier de 10 stappen die je MOET doen in de eerste 30 minuten, in volgorde.',
				'tags' => ['incident-response', 'diefstal', 'mdm', 'security'],
				'published_offset_days' => 62,
				'body' => <<<'HTML'
<p>Elke minuut dat een verloren of gestolen laptop online kan zijn is een risico. Deze 10 stappen moeten in de eerste 30 minuten gebeuren.</p>

<h2>Minuut 0-10: containment</h2>
<ol>
  <li><strong>Meld het bij IT / security-lead.</strong> Dag en tijd vastleggen.</li>
  <li><strong>Remote wipe triggeren</strong> via Intune / Jamf / Kandji. Direct, niet later.</li>
  <li><strong>Account van gebruiker vergrendelen</strong> in M365 / Entra. Force sign-out alle sessies.</li>
  <li><strong>MFA-tokens intrekken</strong> — device is niet meer trusted.</li>
  <li><strong>Wachtwoorden wijzigen</strong> voor de primair gebruikte accounts van deze user.</li>
</ol>

<h2>Minuut 10-20: scope</h2>
<ol start="6">
  <li><strong>Wat stond erop?</strong> Check OneDrive-sync, lokale bestanden, vault-cache.</li>
  <li><strong>Klant-data aan boord?</strong> Als ja: mogelijk een datalek — zie <a href="/nl/blog/datalek-melden-avg">datalek-melding</a>.</li>
  <li><strong>Crypto-mitigatie:</strong> was disk encryption aan? Meestal ja (BitLocker / FileVault) — dan is fysiek device onbereikbaar.</li>
</ol>

<h2>Minuut 20-30: aangifte en communicatie</h2>
<ol start="9">
  <li><strong>Aangifte politie</strong> voor verzekering en eventuele terugkeer.</li>
  <li><strong>Log-entry</strong> in <a href="/nl/blog/incidentenlog-opzetten">incidenten-log</a>. Update later met wat gevonden wordt.</li>
</ol>

<h2>Preventie (vooraf geregeld)</h2>
<ul>
  <li>Disk encryption verplicht op elk device (Intune compliance policy).</li>
  <li>MDM enrollment voor remote wipe.</li>
  <li>Geen wachtwoorden in plain text op laptop.</li>
  <li>Password manager, geen browser-password-kluis.</li>
  <li>Minimal data on device — OneDrive/cloud-first werken.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/mkb-security-zonder-it-afdeling">security-pillar</a>, <a href="/nl/blog/intune-basics-mkb">Intune basics</a>.</p>
HTML,
			],

			[
				'slug' => 'social-engineering-mkb',
				'title' => 'Social engineering: hoe herken je CEO-fraude en vishing?',
				'excerpt' => 'Niet elke aanval komt via e-mail. Telefoon, SMS, LinkedIn-bericht — moderne social engineering gebruikt alle kanalen. Drie patronen en wat je tegenzet.',
				'tags' => ['social-engineering', 'ceo-fraude', 'vishing', 'security'],
				'published_offset_days' => 70,
				'body' => <<<'HTML'
<p>Phishing is e-mail. Vishing is telefoon. SMishing is SMS. Social engineering is de overkoepelende term — mensen manipuleren om iets te doen dat ze normaal niet zouden doen.</p>

<h2>CEO-fraude</h2>
<p>"Dit is de CEO. Spoed — ik heb voor een deal cadeaubonnen nodig, €5000, nu meteen. Houd het stil tot morgen, PR-moment." Altijd fake. Altijd.</p>
<p>Tegenzet: proces — bonnen/uitgaves boven drempel moeten via goedkeuring via officieel kanaal. Geen exceptions, geen urgency.</p>

<h2>Vishing-CEO-fraude</h2>
<p>Telefoontje (kan deep-fake stem zijn in 2026). "Dit is [bedrijf]'s bank — we hebben verdachte transacties, kun je even je inloggegevens bevestigen?" Banken vragen dit nooit.</p>
<p>Tegenzet: hang op, bel terug via officieel nummer. Nooit inloggegevens door de telefoon.</p>

<h2>LinkedIn / Help-request</h2>
<p>"Ik ben net nieuw bij [jouw bedrijf], kun je me helpen inloggen? Mijn onboarding-mail kwam niet aan." Kan van een ex-medewerker of concurrent zijn die gewoon probeert.</p>
<p>Tegenzet: verifieer via HR. Geen hulp geven zonder verificatie.</p>

<h2>Invoice-fraude</h2>
<p>"Uw leverancier heeft een nieuw rekeningnummer. Stort de openstaande factuur daar naartoe." Meestal via (vervalste) e-mail vanuit de echte leverancier-mailbox.</p>
<p>Tegenzet: rekeningnummer-wijziging altijd telefonisch verifieren via bekend nummer (niet uit de mail).</p>

<h2>Training</h2>
<p>Zie <a href="/nl/blog/phishing-herkennen-mkb">phishing herkennen</a> — maar ook simulaties via telefoon en SMS, niet alleen mail.</p>

<p>Zie ook: <a href="/nl/blog/mkb-security-zonder-it-afdeling">security-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'veilig-reizen-security',
				'title' => 'Veilig reizen met bedrijfs-laptop: de reischecklist',
				'excerpt' => 'Voor naar EU-bestemming, of verre buitenland? Sommige landen hebben stricte regels over encryption of data-scanning. Hier waar je op let.',
				'tags' => ['reizen', 'security', 'travel', 'encryption'],
				'published_offset_days' => 78,
				'body' => <<<'HTML'
<p>Reizen met zakelijke apparatuur heeft extra overwegingen: borders waar je device mogelijk wordt gescand, onbetrouwbare WiFi, risico op diefstal.</p>

<h2>Vóór vertrek</h2>
<ul>
  <li>Laptop-backup verse. Disk-encryption aan (hoort altijd aan te zijn, check).</li>
  <li>Vertrouwelijke klant-data die niet op reis hoeft: offline halen, in cloud laten.</li>
  <li>Screen-privacy-filter als je veel op het vliegveld of hotel-lobby werkt.</li>
  <li>VPN geconfigureerd en getest.</li>
  <li>Voor hoog-risico-bestemmingen: overweeg reis-device ("burner laptop") met minimale access-configuratie.</li>
</ul>

<h2>Tijdens de reis</h2>
<ul>
  <li>Publieke WiFi: gebruik VPN of tether via mobiel.</li>
  <li>Laptop nooit onbeheerd laten, ook niet in hotelkamer-safe (niet altijd echt safe).</li>
  <li>Screens niet laten staan in vergadering-pauzes.</li>
  <li>USB-poorten: geen onbekende USB insteken (zelfs niet "voor gebruik in presentatie").</li>
</ul>

<h2>Border-crossings</h2>
<ul>
  <li>Sommige landen (VS, Rusland, China) kunnen verzoeken om device-ontgrendeling. Ken je bedrijfs-beleid.</li>
  <li>Voor risico-landen: cleaned burner-laptop zonder bedrijfs-data is beter.</li>
  <li>Neem geen gevoelige klant-data mee die niet per se nodig is.</li>
</ul>

<h2>Bij verlies / diefstal</h2>
<p>Direct <a href="/nl/blog/laptop-diefstal-response">response</a> triggeren. Vooral op reis: je hebt geen gemakkelijk fysieke toegang tot IT — remote wipe is je lijn.</p>

<p>Zie ook: <a href="/nl/blog/mkb-security-zonder-it-afdeling">security-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'wifi-gastennetwerk-kantoor',
				'title' => 'Gastennetwerk op kantoor: hoe scheid je gasten van je bedrijfsnetwerk?',
				'excerpt' => 'Bezoekers op je WiFi is normaal. Dat ze op dezelfde netwerk-broadcast als je NAS zitten is niet normaal. De eenvoudige segmentatie zet je in 10 minuten op.',
				'tags' => ['wifi', 'netwerk', 'gastentoegang', 'security'],
				'published_offset_days' => 86,
				'body' => <<<'HTML'
<p>Veel MKB-kantoren hebben één WiFi-netwerk. Bezoeker krijgt het wachtwoord. Probleem: bezoeker zit nu in je "trusted" netwerk-laag — potentiele toegang tot printers, NAS, IP-camera's, interne servers.</p>

<h2>Twee-netwerk-minimum</h2>
<ul>
  <li><strong>Bedrijfsnetwerk:</strong> medewerkers, bedrijfs-laptops, bedrijfs-printers. Authenticatie via WPA2-Enterprise of 802.1X als je groter wordt.</li>
  <li><strong>Gastennetwerk:</strong> bezoekers, onbekende devices, IoT (smart TV, thermostaat). Geïsoleerd van bedrijfsnetwerk via VLAN of client-isolation.</li>
</ul>

<h2>Setup in 10 minuten</h2>
<ol>
  <li>Login op je router/access-point admin.</li>
  <li>Enable "Guest network" (ubiquiti, Ruckus, zelfs consumer-routers kunnen dit).</li>
  <li>Zet client-isolation aan: gast-clients kunnen elkaar niet zien.</li>
  <li>Bandwidth-throttling (bijv. max 10 Mbps per client) voorkomt misbruik.</li>
  <li>Apart wachtwoord, bord in ontvangst of QR-code.</li>
</ol>

<h2>Wat zit aan welk netwerk?</h2>
<ul>
  <li>Bedrijfs: laptops medewerkers, bedrijfs-printer.</li>
  <li>Gasten: bezoeker-apparaten, smart-TV in vergaderruimte, IP-camera's (IoT vaak ongepatched).</li>
</ul>

<h2>Volgende stap: 3-netwerk</h2>
<p>Bij &gt; 30 medewerkers of security-gevoelige sector: splits in bedrijfs / IoT / gasten. IoT krijgt eigen VLAN omdat deze devices zelden geüpdatet worden.</p>

<p>Zie ook: <a href="/nl/blog/mkb-security-zonder-it-afdeling">security-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'vendor-risk-management-mkb',
				'title' => 'Vendor risk management voor MKB: pragmatisch onderscheid',
				'excerpt' => 'Elk SaaS-abonnement is een stukje risico dat je uitbesteedt. Hoe beoordeel je welke van je 30 leveranciers extra aandacht verdienen?',
				'tags' => ['vendor-risk', 'third-party', 'security', 'compliance'],
				'published_offset_days' => 94,
				'body' => <<<'HTML'
<p>Vendor-risk (of third-party risk) is het risico dat een leverancier bij jou problemen veroorzaakt: datalek, uitval, compliance-issue. In het MKB kun je niet alle 30 leveranciers gelijk behandelen.</p>

<h2>Tier-classificatie</h2>
<ul>
  <li><strong>Tier 1 — kritiek:</strong> verwerkt persoonsgegevens of is bedrijfskritisch. M365, boekhouding, CRM, hosting.</li>
  <li><strong>Tier 2 — belangrijk:</strong> ondersteunt belangrijke processen, heeft toegang tot bedrijfsdata. Slack, design-tools, payroll.</li>
  <li><strong>Tier 3 — laag risico:</strong> standalone tools zonder klant-data of essentiële rol. LinkedIn Premium, video-editing-tool.</li>
</ul>

<h2>Eisen per tier</h2>
<ul>
  <li><strong>Tier 1:</strong> ISO 27001 of SOC 2-rapport (jaarlijks reviewen), DPA, uptime-SLA, incident-melding-afspraak.</li>
  <li><strong>Tier 2:</strong> DPA, basis security-attestatie.</li>
  <li><strong>Tier 3:</strong> alleen privacyverklaring-check.</li>
</ul>

<h2>Jaarlijkse vendor-review</h2>
<ol>
  <li>Actualiseer de vendor-lijst uit je <a href="/nl/blog/saas-inventaris-opstellen">SaaS-inventaris</a>.</li>
  <li>Review tier-1 vendors: nog actief gecertificeerd? Nieuwe sub-verwerkers? Incident-geschiedenis?</li>
  <li>Spot-check tier-2: DPA nog actueel?</li>
  <li>Rapportage aan directie.</li>
</ol>

<h2>Bij nieuwe leverancier</h2>
<ul>
  <li>Tier bepalen voor ondertekening contract.</li>
  <li>Tier 1-leveranciers: due-diligence vooraf (trust-pagina, security-questionnaire, references).</li>
  <li>Tier 2-3: standaard DPA + privacy-check.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/saas-inventaris-opstellen">SaaS-inventaris</a>, <a href="/nl/blog/verwerkersovereenkomsten-mkb">DPA's</a>.</p>
HTML,
			],

			[
				'slug' => 'security-awareness-training-mkb',
				'title' => 'Security awareness training: wat werkt, wat verspilling van tijd is',
				'excerpt' => 'Jaarlijkse 60-minuten security-video is verspilling. Driemaandelijks 10 minuten specifiek materiaal werkt wél. Hier het programma dat doeltreffend blijkt.',
				'tags' => ['training', 'awareness', 'security', 'mkb'],
				'published_offset_days' => 102,
				'body' => <<<'HTML'
<p>Mensen vergeten binnen 2 weken 80% van wat ze in een jaarlijkse training hebben gehoord. Daarom is een andere aanpak beter dan "video kijken en klaar".</p>

<h2>Wat werkt wel</h2>
<ul>
  <li><strong>Kort en vaak:</strong> 10-15 minuten per kwartaal, niet 60 minuten per jaar.</li>
  <li><strong>Contextueel:</strong> nieuwe phishing-voorbeelden die intern zijn waargenomen, niet generieke voorbeelden uit 2019.</li>
  <li><strong>Interactief:</strong> phishing-simulaties die ze zelf tegenkomen. Knowbe4, Cofense, KnowBe4.</li>
  <li><strong>Direct feedback:</strong> wie klikt krijgt meteen (vriendelijk) feedback, geen collectieve blame-email.</li>
  <li><strong>Rolgericht:</strong> finance krijgt invoice-fraud-training, HR krijgt social-engineering-voor-new-hires-training.</li>
</ul>

<h2>Wat werkt niet</h2>
<ul>
  <li>Jaarlijks verplicht een 60-minuten-video "kijken voor Q4".</li>
  <li>Tests die voornamelijk gericht zijn op naleving ("laat zien dat we training hebben gedaan") ipv leren.</li>
  <li>Blame-culture na phishing-tests.</li>
  <li>Generieke content zonder link naar eigen bedrijfs-context.</li>
</ul>

<h2>Onboarding</h2>
<p>Elke nieuwe medewerker krijgt een 30-min intro-sessie met security-basics + korte herhaling na 30 dagen. Beter onthouden dan grote dose op dag 1.</p>

<h2>Meetbaar</h2>
<ul>
  <li>Phishing-click rate: startwaarde meten, doel na 6 maanden halveren.</li>
  <li>Melding-rate: hoeveel phishing-tests meldt men? Doel: &gt; 70% meldt binnen 2 uur.</li>
  <li>Incident-response-tijd: bij echt incident, hoe snel melden mensen? Betere metric dan alleen tests.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/phishing-herkennen-mkb">phishing herkennen</a>, <a href="/nl/blog/mkb-security-zonder-it-afdeling">security-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'mfa-voor-alle-saas',
				'title' => 'MFA voor álle SaaS, niet alleen M365: de achterhoede-aanpak',
				'excerpt' => 'M365 + Google hebben MFA gemakkelijk. Dropbox, Slack, GitHub, Trello ook. Maar die losse SaaS-tools? Daar ontbreekt vaak MFA. Hier de inhaal-operatie.',
				'tags' => ['mfa', 'saas', 'security', 'multi-factor'],
				'published_offset_days' => 110,
				'body' => <<<'HTML'
<p>Je hebt M365 MFA afgedwongen. Geweldig. Wat doe je met de 35 andere SaaS-abonnementen? Die zijn waarschijnlijk niet-MFA'd.</p>

<h2>Inventariseer</h2>
<p>Vanuit je <a href="/nl/blog/saas-inventaris-opstellen">SaaS-inventaris</a>: per tool kolom "MFA mogelijk?" en "MFA aan voor alle users?".</p>

<h2>Prioriteer</h2>
<ol>
  <li>Tools met klant-data (CRM, customer-portal).</li>
  <li>Tools met financiële data (boekhouding, facturatie, banking).</li>
  <li>Tools met broncode (GitHub, GitLab).</li>
  <li>Tools met admin-rechten over andere tools (SSO-provider, password manager).</li>
  <li>Rest.</li>
</ol>

<h2>Per tool patronen</h2>
<ul>
  <li><strong>SSO waar mogelijk:</strong> tool aan je M365/Google SSO hangen. Dan heb je de MFA van de SSO automatisch.</li>
  <li><strong>Tool-eigen MFA:</strong> enable in account settings. Vaak afdwingbaar voor alle team-users.</li>
  <li><strong>Tool zonder MFA:</strong> overweeg vervangen, of accepteer het risico + sterkere wachtwoord-policy + kortere review-cyclus.</li>
</ul>

<h2>Recovery-codes</h2>
<p>Bij elke MFA-setup krijg je recovery-codes. Die MOETEN in een vault (<a href="/nl/blog/password-manager-kiezen">password manager</a>) of zelfs fysieke kluis. Als je telefoon stukgaat en je hebt geen recovery-codes, ben je uitgesloten.</p>

<h2>MFA bij shared accounts</h2>
<p>Zie <a href="/nl/blog/gedeelde-wachtwoorden-beheer">gedeelde wachtwoorden beheer</a>. Gebruik password-manager met shared TOTP of YubiKey die je fysiek kunt uitgeven.</p>

<p>Zie ook: <a href="/nl/blog/mfa-uitrollen-m365">MFA uitrollen in M365</a>, <a href="/nl/blog/mkb-security-zonder-it-afdeling">security-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'patch-management-mkb',
				'title' => 'Patch management voor MKB zonder MDM-spierballen',
				'excerpt' => 'Patches moeten erop. Maar hoe dwing je dat af als je geen Intune of Jamf hebt? Hier de pragmatische minimum-setup.',
				'tags' => ['patch-management', 'updates', 'security'],
				'published_offset_days' => 118,
				'body' => <<<'HTML'
<p>Niet-gepatchte systemen zijn de grootste voedingsbodem voor aanvallen. Bij grote organisaties doet een MDM dit. Zonder MDM moet je meer op beleid en monitoring leunen.</p>

<h2>De drie lagen</h2>
<ol>
  <li><strong>OS-updates:</strong> Windows Update, macOS Software Update. Automatisch aan, deferral max 2 weken. Bij enterprise-editie: via Windows Update for Business.</li>
  <li><strong>Browser:</strong> Chrome, Edge, Firefox update zichzelf. Enforce via Chrome Enterprise policy als het niet gebeurt.</li>
  <li><strong>Applicaties:</strong> Office, Acrobat, Teams via native updaters. VPN-clients. Password manager. Dit is waar veel MKB'ers achterlopen.</li>
</ol>

<h2>Monitoring zonder MDM</h2>
<ul>
  <li>Kwartaal-enquête: "stuur screenshot van About → version" per kritieke app.</li>
  <li>Bij Entra ID aangemelde devices: check via Security → Devices compliance report.</li>
  <li>Chrome Enterprise policy rapporteert versies centraal (gratis met Google Workspace).</li>
</ul>

<h2>Verplichting communiceren</h2>
<p>Eens per kwartaal een "patch-dag" — iedereen runt updates. Manager ziet voltooiing. Niet persoonlijk maar als team-moment.</p>

<h2>Naar Intune toe?</h2>
<p>Business Premium inclusief Intune is €19/user/maand en automatiseert dit grotendeels. Bij &gt; 20 medewerkers begint dat zich snel terug te verdienen. Zie <a href="/nl/blog/intune-basics-mkb">Intune basics</a>.</p>

<p>Zie ook: <a href="/nl/blog/mkb-security-zonder-it-afdeling">security-pillar</a>.</p>
HTML,
			],
		];
	}
}
