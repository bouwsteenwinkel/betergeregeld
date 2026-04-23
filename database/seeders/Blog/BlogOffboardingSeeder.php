<?php

namespace Database\Seeders\Blog;

use Illuminate\Database\Seeder;

class BlogOffboardingSeeder extends Seeder
{
	public function run(): void
	{
		BlogSeedHelper::seedCluster(
			[
				'slug' => 'offboarding',
				'name' => 'Offboarding',
				'pillar_title' => 'Offboarding: het belangrijkste security-proces waar niemand over praat',
				'intro' => 'Alles over een medewerker zorgvuldig uit je systemen laten glijden — checklist, termijnen, juridisch kader, device retrieval, en de klant-overdracht.',
				'sort_order' => 30,
			],
			self::posts(),
		);
	}

	private static function posts(): array
	{
		return [
			[
				// Move this to offboarding category — it was initially in AccessGuard.
				'slug' => 'waterdichte-offboarding-stappen',
				'title' => 'Waterdichte offboarding in 12 stappen',
				'excerpt' => 'Iemand gaat weg. In het MKB is dit waar de meeste datalekken ontstaan. Hier is een checklist die dekt wat je écht moet doen — met termijnen, verantwoordelijken, en valkuilen.',
				'is_pillar' => true,
				'featured' => true,
				'tags' => ['offboarding', 'checklist', 'iam', 'governance'],
				'published_offset_days' => 70,
				'body' => <<<'HTML'
<p>Voor onboarding vinden ondernemers het normaal om 3 uur in te plannen. Voor offboarding vaak 20 minuten op de laatste werkdag. Dat is waar het misgaat.</p>

<h2>De 12 stappen — in volgorde van belangrijkheid</h2>
<ol>
  <li><strong>Disable eerst, verwijder later.</strong> Op de laatste werkdag: account op <em>disabled</em> zetten, niet verwijderen. Je wilt e-mail nog kunnen lezen, bestanden nog kunnen terughalen.</li>
  <li><strong>Trek privileged access direct in.</strong> Global Admin, AWS root, boekhoud-admin — die gaan meteen weg, niet op einddag. Zie <a href="/nl/blog/privileged-access-management">privileged access</a>.</li>
  <li><strong>Verander gedeelde wachtwoorden.</strong> Alles in je password-vault waar deze persoon bij kon. Ja, allemaal. Zie <a href="/nl/blog/gedeelde-wachtwoorden-beheer">gedeelde wachtwoorden beheer</a>.</li>
  <li><strong>Zet e-mail forwarding aan.</strong> Naar manager of opvolger, voor 30 dagen. Zie <a href="/nl/blog/email-forwarding-na-vertrek">e-mail forwarding na vertrek</a>.</li>
  <li><strong>Autoreply instellen.</strong> "Ik werk hier niet meer. Neem contact op met X."</li>
  <li><strong>Laptop innemen.</strong> Zie <a href="/nl/blog/device-retrieval-offboarding">device retrieval</a>.</li>
  <li><strong>Zet MFA-tokens uit.</strong> Authenticator-apps, hardware-tokens, SMS-nummers.</li>
  <li><strong>Revoke van individuele SaaS accounts.</strong> Alles wat niet via SSO loopt (<a href="/nl/blog/saas-inventaris-opstellen">SaaS-inventaris</a>).</li>
  <li><strong>Klant- en projectoverdracht.</strong> Zie <a href="/nl/blog/klantoverdracht-bij-consultant-vertrek">klantoverdracht</a>.</li>
  <li><strong>Vault-items overzetten.</strong></li>
  <li><strong>30 dagen later: e-mail archiveren en account verwijderen.</strong> Zie <a href="/nl/blog/30-dagen-regel-offboarding">30-dagen-regel</a>.</li>
  <li><strong>Loggen wat je hebt gedaan.</strong> Eén pagina per offboarding, bewaard voor audit-bewijs.</li>
</ol>

<h2>Wie doet wat?</h2>
<p>HR triggert. IT / office-manager voert uit. Manager doet de klant/project-overdracht. Directie tekent af op stappen 1-3 (de high-impact).</p>

<h2>Maak er een proces van, niet een checklist</h2>
<p>Zet het als <a href="/nl/blog/offboarding-proces-in-tool">proces in je tool</a>, met per stap: verantwoordelijke, SLA, bewijs.</p>

<p>Verder: <a href="/nl/blog/offboarding-juridisch-kader">juridisch kader</a>, <a href="/nl/blog/onboarding-offboarding-parity">onboarding-offboarding parity</a>, <a href="/nl/blog/laatste-werkdag-offboarding-script">script voor de laatste werkdag</a>.</p>
HTML,
			],

			[
				'slug' => 'laatste-werkdag-offboarding-script',
				'title' => 'Het laatste-werkdag-script: minuut voor minuut',
				'excerpt' => 'De laatste werkdag is waar veel offboardings ontsporen. Hier een exacte tijdlijn: 09:00 exit-gesprek, 10:00 vault-overdracht, 12:00 inloggen geblokkeerd, 17:00 borrel.',
				'tags' => ['offboarding', 'laatste-werkdag', 'hr'],
				'published_offset_days' => 78,
				'body' => <<<'HTML'
<p>De laatste werkdag hoort een rustige dag te zijn — voor de vertrekker én voor wie achterblijft. Dat werkt alleen als er een script is.</p>

<h2>09:00 — exit-interview</h2>
<p>30-45 min met HR. Feedback, vragen, afscheidswoorden. Geen IT-onderwerpen.</p>

<h2>10:00 — kennis- en vault-overdracht</h2>
<p>Met opvolger of manager. 1 uur. Welke klanten, welke projecten, welke gedeelde logins worden overgedragen. Maak een shared document met bevindingen.</p>

<h2>11:00 — device-check</h2>
<p>IT of office-manager loopt langs. Persoonlijke bestanden op aparte USB overzetten (als contract dat toestaat). Controleer dat licentie-keys en certificaten op bedrijfs-storage staan, niet lokaal.</p>

<h2>12:00 — lunchpauze — accounts gedisabled</h2>
<p>Terwijl de vertrekker luncht doet IT de technische offboarding: M365/GW disable, MFA-tokens verwijderen, SaaS-accounts revoke, vault-ACL's intrekken. Doe dit niet in aanwezigheid van de vertrekker.</p>

<h2>13:00 — laatste-werk-middag</h2>
<p>E-mail inbox dichtzetten via autoreply. Geen nieuwe taken meer. Ruimte om de laatste losse eindjes af te handelen via manager/opvolger.</p>

<h2>16:00 — laptop en badge inleveren</h2>
<p>Check off op de <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding-checklist</a>, foto van ingeleverde spullen voor je archief.</p>

<h2>17:00 — afscheid / borrel</h2>

<h2>Volgende 30 dagen</h2>
<p>E-mail forwarding naar manager. Zie <a href="/nl/blog/30-dagen-regel-offboarding">30-dagen-regel</a> voor wat daarna.</p>
HTML,
			],

			[
				'slug' => '30-dagen-regel-offboarding',
				'title' => 'Waarom je 30 dagen wacht voordat je een account verwijdert',
				'excerpt' => 'Disable is onmiddellijk. Verwijderen pas na 30 dagen. Dat is geen willekeurige termijn — hier de redenen, de risico\'s, en wat er in die 30 dagen moet gebeuren.',
				'tags' => ['offboarding', '30-dagen-regel', 'archivering'],
				'published_offset_days' => 85,
				'body' => <<<'HTML'
<p>Op de laatste werkdag: account disabled. Pas 30 dagen later: verwijderd. Wat gebeurt er in die 30 dagen?</p>

<h2>Doelen van de 30-dagen-periode</h2>
<ul>
  <li><strong>E-mail forwarding actief.</strong> Klanten die nog naar het oude adres mailen komen bij de opvolger terecht. Zie <a href="/nl/blog/email-forwarding-na-vertrek">e-mail forwarding</a>.</li>
  <li><strong>Bestanden terugvinden.</strong> Soms blijkt er een gedeelde OneDrive-folder verder te lezen, of een draft van een voorstel dat niemand anders heeft.</li>
  <li><strong>Licenties recupereren.</strong> Pas na 30 dagen komt de M365-licentie vrij, of de tijdelijke oplossing verloopt.</li>
  <li><strong>Juridische periode.</strong> Sommige CAO's of NDA's bepalen een minimum-bewaartermijn.</li>
</ul>

<h2>Wat je NIET doet in die 30 dagen</h2>
<ul>
  <li>Account weer activeren om "even iets te checken". Elke reactivering is een audit-finding.</li>
  <li>Het wachtwoord geven aan de opvolger zodat hij kan inloggen. Gebruik delegatie of forwarding.</li>
  <li>Bestanden zomaar kopiëren voor privé-doeleinden van de vertrekker. AVG-probleem.</li>
</ul>

<h2>Na 30 dagen</h2>
<ul>
  <li>E-mail archiveren volgens beleid (meestal 1-7 jaar, afhankelijk van sector).</li>
  <li>OneDrive / Google Drive content overzetten naar team-locatie of archiveren.</li>
  <li>Account permanent verwijderen.</li>
  <li>Licentie herbestemmen of opzeggen.</li>
  <li>Incidenten-log-entry: offboarding succesvol afgerond, datum, bewijs-link.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding-pillar</a>, <a href="/nl/blog/offboarding-juridisch-kader">juridisch kader</a>.</p>
HTML,
			],

			[
				'slug' => 'email-forwarding-na-vertrek',
				'title' => 'E-mail forwarding na vertrek: welke regels zijn er?',
				'excerpt' => 'Een bedrijfsmailbox forwarden naar een manager lijkt triviaal. Er zitten valkuilen in: GDPR, oude contacten, verwarring bij klanten. Hier het recept.',
				'tags' => ['offboarding', 'email', 'avg'],
				'published_offset_days' => 92,
				'body' => <<<'HTML'
<p>Na het vertrek van een medewerker forward je zijn zakelijke e-mail. Drie configuratie-opties, elk met afwegingen.</p>

<h2>Optie 1: volledig forward naar opvolger</h2>
<p>Alle mail gaat door naar de collega. Simpel, maar de opvolger krijgt ook spam, nieuwsbrieven van niet-zakelijk-relevante diensten, en privé-achtige mail. Maximaal 30 dagen aanbevolen.</p>

<h2>Optie 2: forward met autoreply</h2>
<p>Mail gaat door + automatische reply "[naam] is niet meer werkzaam bij [bedrijf]. Voor uw vraag wend u zich tot [opvolger]". Geeft de afzender duidelijkheid. Preferred voor 90% van de gevallen.</p>

<h2>Optie 3: autoreply only, geen forward</h2>
<p>Alleen een autoreply zonder doorsturen. Voor wie expliciet privacy-gevoelig is (bijv. vertrokken HR-medewerker).</p>

<h2>Wat je moet instellen</h2>
<ul>
  <li>Forward duur: maximaal 30 dagen (standaard) of 90 dagen (uitzondering).</li>
  <li>Autoreply tekst: zakelijk, kort, met opvolger-contactgegevens.</li>
  <li>Log-entry: wanneer ingesteld, door wie, tot wanneer.</li>
  <li>Pas op met <em>delegatie</em>: technisch ander concept, meer rechten dan forward. Niet per ongeluk gebruiken.</li>
</ul>

<h2>AVG-kader</h2>
<p>Je mag zakelijke e-mail lezen die na vertrek binnenkomt voor zakelijke doeleinden. Je mag GEEN persoonlijke e-mail lezen. In de praktijk: autoreply + forward naar functionaris, geen bulk-doorlezen.</p>

<p>Zie ook: <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding-pillar</a>, <a href="/nl/blog/30-dagen-regel-offboarding">30-dagen-regel</a>.</p>
HTML,
			],

			[
				'slug' => 'device-retrieval-offboarding',
				'title' => 'Laptop terughalen: logistiek en techniek',
				'excerpt' => 'Remote-only medewerkers, flex-kantoor, internationale hires — laptop-retrieval is complexer dan vroeger. Hier de patronen die werken zonder dat er €2000 aan hardware verdwijnt.',
				'tags' => ['offboarding', 'device-retrieval', 'mdm', 'hardware'],
				'published_offset_days' => 100,
				'body' => <<<'HTML'
<p>Vroeger liep de vertrekker op laatste werkdag langs kantoor en leverde hij zijn laptop in. Nu zit hij misschien in Málaga of Rotterdam en werkt hij nooit op het kantoor. Hoe halen we die laptop terug?</p>

<h2>Patronen per scenario</h2>
<ul>
  <li><strong>Kantoor-medewerker:</strong> laatste werkdag op kantoor, inlevering voor de lunch. Standaard.</li>
  <li><strong>Remote, binnen Nederland:</strong> DHL retour-label vooraf toesturen, laptop binnen 3 dagen na laatste werkdag retour. Track-and-trace bewaar je.</li>
  <li><strong>Remote, EU:</strong> koerier op bedrijfskosten of retour-label met double-boxed. Typisch €20-50 kosten per device.</li>
  <li><strong>Remote, buiten EU:</strong> BTW/douane-gedoe. Vaak makkelijker laptop uitkopen tegen restwaarde dan terughalen.</li>
</ul>

<h2>MDM als veiligheidsnet</h2>
<p>Je moet kunnen vertrouwen op Intune / Jamf / Kandji om een device op afstand te wissen als het niet binnenkomt. Factory-reset via MDM, en als het gekoppeld is aan je bedrijfs-Apple-ID of Microsoft-tenant, kan het pas weer worden gebruikt na ontkoppeling.</p>

<h2>Waarborgsom-clausule in contract</h2>
<p>Handig: in het arbeidscontract een clausule over device-retrieval + aansprakelijkheid bij niet-retour. Voor contractors: expliciet mee opnemen. Dit scheelt discussie.</p>

<h2>Wat doe je met een oude laptop?</h2>
<ul>
  <li>Wipe (MDM of handmatig via Surface/Apple Configurator).</li>
  <li>Inspecteren voor fysieke schade.</li>
  <li>Opnieuw imagen en klaarzetten voor volgende hire.</li>
  <li>Of: donate aan goed doel / verkopen als surplus.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding-pillar</a>, <a href="/nl/blog/laptop-diefstal-response">reageren op laptop-diefstal</a>.</p>
HTML,
			],

			[
				'slug' => 'offboarding-juridisch-kader',
				'title' => 'Offboarding: het juridisch kader in Nederland',
				'excerpt' => 'Welke wet zegt wat over data-toegang, e-mail-lezen, device-retrieval en bewaartermijnen? Geen juridisch advies, wel een begrijpelijke oriëntatie.',
				'tags' => ['offboarding', 'juridisch', 'avg', 'arbeidsrecht'],
				'published_offset_days' => 108,
				'body' => <<<'HTML'
<p>Juridisch gezien kom je bij offboarding op drie velden tegelijk: arbeidsrecht, AVG, en eventueel sector-specifieke regelgeving (zorg, financieel). Dit is geen juridisch advies — raadpleeg een arbeidsjurist voor jouw specifieke geval.</p>

<h2>Arbeidsrecht</h2>
<ul>
  <li>Geheimhoudingsbeding in contract — blijft geldig na vertrek.</li>
  <li>Non-concurrentie / relatiebeding — geldig indien schriftelijk vastgelegd, aangepaste interpretatie sinds de wetswijziging 2025.</li>
  <li>Device- en materialen-teruggave — hoort in het contract te staan.</li>
  <li>Final paystub en vakantiedagen-afrekening — bij vertrek afrekenen.</li>
</ul>

<h2>AVG</h2>
<ul>
  <li>Persoonsgegevens van ex-medewerker: je mag alleen wat nodig is bewaren (salarisadministratie, fiscaal bewijs — typisch 5-7 jaar).</li>
  <li>E-mail lezen na vertrek: beperkt tot zakelijke doeleinden en minimale omvang.</li>
  <li>Wachtwoorden reset op shared accounts: verplichting onder redelijkheidsnorm.</li>
  <li>Foto en data uit HR-systemen: na vertrek gericht opruimen.</li>
</ul>

<h2>Sector-specifiek</h2>
<ul>
  <li>Zorg (NEN 7510 + AVG): strikter, patiëntdata-logging vereist.</li>
  <li>Financieel (DORA): incidenten-meldplicht als iets misgaat in offboarding-proces van medewerker met toegang.</li>
  <li>Overheid: specifieke veiligheidsregels BIO.</li>
</ul>

<h2>Documenteer</h2>
<p>Elke offboarding-gerelateerde beslissing (e-mail lezen, device uitkopen, relatiebeding handhaven) documenteren in HR-dossier. Dit is je verdediging bij latere discussie.</p>

<p>Zie ook: <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding-pillar</a>, <a href="/nl/blog/bewaartermijnen-personeelsdossier">bewaartermijnen personeelsdossier</a>.</p>
HTML,
			],

			[
				'slug' => 'klantoverdracht-bij-consultant-vertrek',
				'title' => 'Klantoverdracht bij vertrek: doe dit niet per e-mail',
				'excerpt' => 'Een medewerker die klanten beheert gaat weg. De klant is 5 jaar meegegroeid met die ene persoon. Hoe voorkom je dat de klant-relatie tegelijk vertrekt?',
				'tags' => ['offboarding', 'klantoverdracht', 'sales'],
				'published_offset_days' => 115,
				'body' => <<<'HTML'
<p>Bij een sales of account-manager is de klant-relatie vaak even waardevol als de cash. Verliezen bij offboarding is het échte risico.</p>

<h2>Drie weken voor de laatste werkdag</h2>
<ul>
  <li>Interne lijst: welke klanten beheert deze persoon. Bij &gt; 20 klanten, prioriteer de top.</li>
  <li>Opvolger toewijzen per klant (of meerdere opvolgers voor verschillende segments).</li>
  <li>Intern kick-off: beknopte kennisoverdracht per klant (context, openstaande zaken, valkuilen).</li>
</ul>

<h2>Twee weken voor</h2>
<p>Warm introductie-e-mail of -belletje naar top-klanten, <em>door de vertrekker</em>: "Ik ga [bedrijf] verlaten. Je nieuwe aanspreekpunt is [opvolger]. Zij/hij is op de hoogte en neemt binnenkort contact op."</p>

<h2>Eén week voor</h2>
<p>Opvolger neemt zelf proactief contact op — telefonisch, niet per e-mail. 15 min per klant. Doel: laten voelen dat het doorloopt, geen harde verkoop.</p>

<h2>Na de laatste werkdag</h2>
<ul>
  <li>E-mail-forwarding van de oude medewerker naar de opvolger, 90 dagen (hier mag langer dan de standaard 30).</li>
  <li>CRM-eigenaarschap formeel overgedragen.</li>
  <li>Review na 60 dagen: zijn er klanten die sinds het vertrek niet meer hebben gereageerd? Red flag.</li>
</ul>

<h2>Wat niet werkt</h2>
<ul>
  <li>Bulk-mail "onze nieuwe contactpersoon is…" zonder persoonlijke follow-up.</li>
  <li>Klanten pas na 2 maanden horen dat iemand weg is.</li>
  <li>Geen warme handover — de klant voelt zich een nummer.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding-pillar</a>, <a href="/nl/blog/externe-partijen-en-consultants-toegang">externe partijen</a>.</p>
HTML,
			],

			[
				'slug' => 'offboarding-proces-in-tool',
				'title' => 'Offboarding-checklist als proces in je tool — niet als Word-document',
				'excerpt' => 'Een Word-checklist overleeft 2 offboardings, dan zit de versie die nog gebruikt wordt op iemand\'s laptop. Bouw het als proces in je tool zodat het niet meer kan ontsnappen.',
				'tags' => ['offboarding', 'proces', 'automation', 'tool'],
				'published_offset_days' => 122,
				'body' => <<<'HTML'
<p>Een statische checklist werkt tot er iets verandert. Nieuwe SaaS toegevoegd? Checklist nog oud. Nieuwe medewerker doet offboarding voor het eerst? Raakt de <em>laatste</em> versie kwijt.</p>

<h2>Eisen aan een proces-gebaseerde offboarding</h2>
<ul>
  <li>Centraal beheerde lijst met stappen.</li>
  <li>Per stap: verantwoordelijke, SLA, bewijs-veld.</li>
  <li>Status per offboarding: todo / in progress / done / blocked / NA.</li>
  <li>Bewijs-upload (screenshot, e-mail, logentry).</li>
  <li>Audit-trail — wie heeft wanneer wat afgevinkt.</li>
</ul>

<h2>Koppeling met toegangsbeheer</h2>
<p>Een modern tool (zoals <a href="/nl/tools/accessguard">AccessGuard</a>) combineert offboarding-proces met toegangs-intrekking: één klik triggert alle accounts op disable, plus checklist voor de handmatige stappen.</p>

<h2>Standaard-templates, tenant-specifieke overrides</h2>
<p>Basis-template: 12 stappen (zie <a href="/nl/blog/waterdichte-offboarding-stappen">pillar</a>). Per bedrijf voeg je toe: "inleveren klantenkaarten", "toegang X-systeem afmelden" enzovoort. Templates evolueren mee; elke offboarding heeft de snapshot van toen-ie startte.</p>

<p>Zie ook: <a href="/nl/blog/onboarding-offboarding-parity">onboarding-offboarding parity</a>.</p>
HTML,
			],

			[
				'slug' => 'onboarding-offboarding-parity',
				'title' => 'Onboarding-offboarding parity: de beste test voor je IAM',
				'excerpt' => 'Als onboarding iets doet, doet offboarding het omgekeerde. Als die pariteit niet klopt, blijven er weeshuizen over — vaak jarenlang onopgemerkt.',
				'tags' => ['onboarding', 'offboarding', 'iam', 'governance'],
				'published_offset_days' => 130,
				'body' => <<<'HTML'
<p>Een goed offboarding-proces is de spiegel van je onboarding. Elke stap bij indiensttreding heeft een tegenhanger bij vertrek. Als die symmetrie ontbreekt, heb je weeshuizen.</p>

<h2>Voorbeelden van pariteit</h2>
<ul>
  <li>Onboarding: M365-account aanmaken → Offboarding: disable + 30 dagen later verwijderen.</li>
  <li>Onboarding: toegang tot SharePoint-team "Sales" → Offboarding: uit Sales-group halen.</li>
  <li>Onboarding: hardware-toekenning → Offboarding: hardware-retour.</li>
  <li>Onboarding: access profile "Sales rol" toepassen → Offboarding: profile revoke.</li>
  <li>Onboarding: toevoegen aan shared vaults → Offboarding: ACL verwijderen, wachtwoorden wijzigen.</li>
</ul>

<h2>De pariteit-test</h2>
<p>Loop je onboarding-checklist af en vraag per regel: "wat is het tegenovergestelde bij offboarding, en staat dat expliciet in onze procedure?" Waar je "euh" zegt, is een gap.</p>

<h2>Waar pariteit verbroken wordt</h2>
<ul>
  <li>SaaS die tijdens de rit is toegevoegd, alleen in onboarding maar niet in offboarding-checklist verwerkt.</li>
  <li>Tijdelijke toegang ("ik maak je even admin voor 1 dag") die nooit werd ingetrokken.</li>
  <li>Ad-hoc shared folders die bij onboarding werden gedeeld maar niet in een groep zitten.</li>
</ul>

<h2>Hoe dicht je gaps?</h2>
<p>Jaarlijkse walkthrough: doorloop het onboarding-proces alsof je net begint, en let specifiek op waar er geen tegenovergestelde bestaat. Documenteer, automatiseer waar mogelijk.</p>

<p>Zie ook: <a href="/nl/blog/onboarding-it-checklist-mkb">onboarding IT-checklist</a>, <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'vault-overdracht-bij-vertrek',
				'title' => 'Vault-overdracht: voorkom dat credentials met de vertrekker verdwijnen',
				'excerpt' => 'Gedeelde logins die alleen één persoon kende, API-keys die in zijn persoonlijke vault stonden, 2FA-tokens gekoppeld aan zijn privé-telefoon. Hoe voorkom je de "oh nee"-momenten.',
				'tags' => ['offboarding', 'vault', 'credentials'],
				'published_offset_days' => 138,
				'body' => <<<'HTML'
<p>Het klassieke offboarding-drama: iemand vertrekt en een week later ontdek je dat alleen hij het wachtwoord kende voor de bedrijfsaccount van service X. Hij neemt niet op. Probleem.</p>

<h2>Preventie op drie niveaus</h2>
<ol>
  <li><strong>Geen persoonlijke vaults voor bedrijfs-credentials.</strong> Alles wat zakelijk is gaat in een shared vault (team of vault-systeem). Persoonlijke 1Password-accounts zijn voor privé.</li>
  <li><strong>Minstens 2 kenners per kritieke credential.</strong> Zie <a href="/nl/blog/privileged-access-management">privileged access</a> — regel dat minstens 2 mensen admin-rechten hebben.</li>
  <li><strong>2FA niet aan privé-telefoon.</strong> Gebruik shared-token-oplossingen (1Password TOTP, Keeper) of bedrijfs-hardware-tokens.</li>
</ol>

<h2>Bij de offboarding zelf</h2>
<ol>
  <li>Lijst: welke vault-items gaan met deze persoon verloren? Die moeten overgedragen.</li>
  <li>Per item: expliciete overdracht aan opvolger + wachtwoordwissel direct daarna (voor zekerheid).</li>
  <li>2FA-tokens: opnieuw uitdelen of migreren naar shared/team-systeem.</li>
  <li>Log entry met datum en items.</li>
</ol>

<h2>Na de offboarding</h2>
<p>Review in het eerstvolgende kwartaal: zijn er credentials waar we nog tegenaan lopen die ontbreken? Meestal vind je binnen een maand nog 1-2 "oh, en dit ook."</p>

<p>Zie ook: <a href="/nl/blog/gedeelde-wachtwoorden-beheer">gedeelde wachtwoorden beheer</a>, <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'verweesde-accounts-opsporen',
				'title' => 'Verweesde accounts opsporen: hoe kraak je 3 jaar aan slordige offboarding?',
				'excerpt' => 'Dat je vandaag beter wil offboarden helpt niet tegen de 23 actieve accounts van ex-medewerkers die er al staan. Hier hoe je die groep opruimt zonder wekenlang werk.',
				'tags' => ['offboarding', 'orphan-access', 'cleanup'],
				'published_offset_days' => 145,
				'body' => <<<'HTML'
<p>Iedereen heeft ze: verweesde accounts. Mensen die jaren geleden vertrokken maar nog op has_access staan in je CRM, cloud-storage, of random SaaS. Dit is de opruim-operatie.</p>

<h2>De drie hoofdbronnen</h2>
<ol>
  <li><strong>M365 / Google Workspace:</strong> filter op "last sign-in &gt; 90 dagen" — dat is meestal de aanvang van je verdwenen-accounts-lijst.</li>
  <li><strong>Individuele SaaS:</strong> logs per tool. Moeilijker omdat er niet altijd een standaard-filter is.</li>
  <li><strong>Shared spaces:</strong> Dropbox, SharePoint, Drive — mensen die nog als editor zijn toegevoegd.</li>
</ol>

<h2>De cleanup-sprint</h2>
<ol>
  <li>Dag 1: per systeem een lijst trekken van accounts waarvan je twijfelt.</li>
  <li>Dag 2-3: cross-check met HR: wie is hier nog wel / niet in dienst?</li>
  <li>Dag 4: bulk-disable van actuele ex-medewerkers, 30 dagen wachten voor verwijderen.</li>
  <li>Dag 5: documenteren en een formeel offboarding-record maken, ook voor ex-medewerkers van jaren terug.</li>
</ol>

<h2>Risk-flag als blijvende oplossing</h2>
<p>Implementeer een terugkerende check die wekelijks of maandelijks rapporteert: "deze persoon is inactive volgens HR maar heeft nog access-cellen op has_access". In <a href="/nl/tools/accessguard">AccessGuard</a> heet dat <em>orphan_access</em> risk (sev 5) — de scanner doet dit automatisch elke nacht.</p>

<p>Zie ook: <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding pillar</a>, <a href="/nl/blog/periodieke-access-reviews-proces">periodieke access reviews</a>.</p>
HTML,
			],

			[
				'slug' => 'bewaartermijnen-personeelsdossier',
				'title' => 'Bewaartermijnen personeelsdossier: wat bewaar je, hoe lang?',
				'excerpt' => 'Salarisstroken 7 jaar, functioneringsgesprekken 2 jaar, afwijzingsbrieven 4 weken. Hier de Nederlandse bewaartermijnen op een rij — zonder onnodig juridisch jargon.',
				'tags' => ['offboarding', 'bewaartermijnen', 'hr', 'avg'],
				'published_offset_days' => 153,
				'body' => <<<'HTML'
<p>Na vertrek moet je weten wat je mag bewaren en wat niet. Te lang bewaren = AVG-risico. Te kort bewaren = bewijsrisico bij arbeidsconflicten. Hier de richtlijnen.</p>

<h2>Fiscaal — 7 jaar</h2>
<p>Alles wat de Belastingdienst kan vragen: salarisstroken, contract, arbeidsvoorwaarden, opleidingskosten-declaraties, afrekeningen, jaaropgaven. Fiscale bewaarplicht is 7 jaar na einde dienstverband.</p>

<h2>CAO/arbeidsrecht — varieert</h2>
<ul>
  <li>Contract + wijzigingen: 7 jaar (overlap met fiscaal).</li>
  <li>Functioneringsverslagen en beoordelingen: 2 jaar.</li>
  <li>Verzuim- en ziekteregistratie: 2 jaar vanaf einde.</li>
  <li>Verklaringen omtrent gedrag (VOG): zo lang als nodig voor de functie (meestal korter).</li>
</ul>

<h2>Sollicitanten — kort</h2>
<ul>
  <li>Afgewezen sollicitanten: max 4 weken, tenzij toestemming voor werven langer (1 jaar).</li>
  <li>Aangenomen sollicitanten: wordt onderdeel van personeelsdossier.</li>
</ul>

<h2>AVG-gerichte gegevens</h2>
<ul>
  <li>Foto's: tijdens dienstverband, bij vertrek verwijderen tenzij goede zakelijke reden.</li>
  <li>BSN: alleen voor salarisadministratie.</li>
  <li>Medische gegevens: alleen bedrijfsarts, niet in HR-dossier.</li>
  <li>Gespreksaantekeningen leidinggevenden: niet structureel, zeker niet 7 jaar.</li>
</ul>

<h2>Offboarding-actiepunt</h2>
<p>Maak een retentie-proces: direct na vertrek categoriseer je het dossier, items die niet onder bewaarplicht vallen verwijder je. Items die onder 7-jaars-plicht vallen gaan naar een archief met toegangsbeperking.</p>

<p>Zie ook: <a href="/nl/blog/offboarding-juridisch-kader">juridisch kader offboarding</a>, <a href="/nl/blog/verwerkersregister-avg">verwerkersregister</a>.</p>
HTML,
			],
		];
	}
}
