<?php

namespace Database\Seeders\Blog;

use Illuminate\Database\Seeder;

class BlogM365Seeder extends Seeder
{
	public function run(): void
	{
		BlogSeedHelper::seedCluster(
			[
				'slug' => 'm365-entra',
				'name' => 'Microsoft 365 & Entra ID',
				'pillar_title' => 'Microsoft 365 governance voor het MKB',
				'intro' => 'Licenties, MFA, Conditional Access, Intune, SharePoint, Teams-gasten, retention — alles wat een 30-mans MKB zonder IT-team zou moeten regelen in hun tenant.',
				'sort_order' => 50,
			],
			self::posts(),
		);
	}

	private static function posts(): array
	{
		return [
			[
				'slug' => 'm365-entra-id-governance-mkb',
				'title' => 'Microsoft 365 governance voor MKB — pragmatisch, niet perfectionistisch',
				'excerpt' => 'M365 is het grootste stuk SaaS in de meeste MKB\'s. Deze gids loopt de governance-lagen af: identity, licentie, MFA, Conditional Access, data, retention — met wat écht moet en wat pas later.',
				'is_pillar' => true,
				'featured' => true,
				'tags' => ['m365', 'entra-id', 'governance', 'mkb'],
				'published_offset_days' => 15,
				'body' => <<<'HTML'
<p>Microsoft 365 raakt bij een typisch MKB alles — e-mail, bestanden, agenda, Teams, SharePoint. Goede governance is geen optie maar een voorwaarde. Gelukkig is er een MKB-werkbare subset.</p>

<h2>Wat moét je hebben?</h2>
<ol>
  <li><strong>MFA op alle accounts.</strong> Zie <a href="/nl/blog/mfa-uitrollen-m365">MFA uitrollen</a>.</li>
  <li><strong>Bedrijfs-eigenaarschap van devices.</strong> Intune-basic opent toegang-mogelijkheden. Zie <a href="/nl/blog/intune-basics-mkb">Intune basics</a>.</li>
  <li><strong>Licenties matchen met rollen.</strong> Geen E3 voor stagiairs, geen E1 voor sales. Zie <a href="/nl/blog/m365-licentiebeheer">licentiebeheer</a>.</li>
  <li><strong>Global Admin discipline.</strong> 2-3 mensen, dedicated admin-accounts. Zie <a href="/nl/blog/privileged-access-management">PAM-artikel</a>.</li>
  <li><strong>Conditional Access voor kritieke apps.</strong> Zie <a href="/nl/blog/conditional-access-uitleg">Conditional Access uitleg</a>.</li>
</ol>

<h2>Wat is nice-to-have?</h2>
<ul>
  <li>PIM (just-in-time admin) — overweeg bij &gt; 30 medewerkers.</li>
  <li>DLP (Data Loss Prevention) — na bekend patroon van lek-risico.</li>
  <li>Retention policies — wanneer iemand zegt "het mag nooit weg" of "het moet juist weg" op tenant-niveau.</li>
  <li>Sensitivity labels — als klanten eisen dat documenten geklassificeerd zijn.</li>
</ul>

<h2>Identity als bron van waarheid</h2>
<p>Entra ID is de ene-plek waar je bij klaar wilt zijn. Users hier komen overal terecht, security-groups koppelen rollen aan licenties en apps, Conditional Access werkt erbovenop. Integreer <a href="/nl/blog/m365-groepen-als-access-profielen">security-groups met je toegangsbeheer-tool</a>.</p>

<h2>SharePoint en Teams: aparte verhalen</h2>
<p>SharePoint-permissies raken de inhoud van bestanden, niet meer alleen "wie kan inloggen". Zie <a href="/nl/blog/sharepoint-permissies-uitleg">SharePoint permissies</a>. Teams-gast access heeft eigen regels — zie <a href="/nl/blog/teams-externe-gasten">Teams externe gasten</a>.</p>

<p>Verder: <a href="/nl/blog/guest-access-m365">guest access</a>, <a href="/nl/blog/mailbox-delegatie-m365">mailbox-delegatie</a>, <a href="/nl/blog/onedrive-sharing-policy">OneDrive sharing policy</a>, <a href="/nl/blog/m365-retention-policies">retention policies</a>, <a href="/nl/blog/m365-admin-rollen">M365 admin-rollen uitgelegd</a>.</p>
HTML,
			],

			[
				'slug' => 'mfa-uitrollen-m365',
				'title' => 'MFA uitrollen in M365: van 50% naar 100% in twee weken',
				'excerpt' => 'MFA is de goedkoopste security-upgrade die je kunt doen — en de meest onderschatte. Hier het uitrol-plan dat weerstand minimaliseert en compleetheid maximaliseert.',
				'tags' => ['mfa', 'm365', 'security'],
				'published_offset_days' => 25,
				'body' => <<<'HTML'
<p>Als je één security-ding moet kiezen voor dit jaar: MFA voor iedereen. 99% van wachtwoord-gebaseerde aanvallen wordt geblokkeerd door MFA. De uitrol is de tricky bit.</p>

<h2>Week 1: voorbereiding</h2>
<ul>
  <li>Decide: Microsoft Authenticator app (gratis, best) of YubiKey (€30/stuk, sterkst). Voor MKB is Authenticator standaard, YubiKey voor privileged accounts.</li>
  <li>Stel Security Defaults of een Conditional Access policy in die MFA afdwingt voor alle users.</li>
  <li>Communiceer: 1 all-hands van 15 min waarin je uitlegt waarom, wanneer, hoe.</li>
</ul>

<h2>Week 2: uitrol</h2>
<ul>
  <li>Dag 1-3: self-enrollment open. Mensen registreren hun app via aka.ms/mfasetup.</li>
  <li>Dag 4: IT helpt bij struikelaars. Vaak ouder-mensen die geen smartphone willen gebruiken — overweeg SMS of een bedrijfs-FIDO-key als alternatief.</li>
  <li>Dag 7: enforcement gaat live. Iedereen die nog niet heeft geregistreerd wordt bij eerste login gedwongen.</li>
</ul>

<h2>Privileged accounts: extra stap</h2>
<p>Global Admins krijgen hardware-token of number-matching MFA. Geen SMS (SIM-swap risk). Zie <a href="/nl/blog/privileged-access-management">PAM-artikel</a>.</p>

<h2>De ex-medewerker-uitdaging</h2>
<p>Bij <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding</a>: registraties worden gewist. Anders blijft een ex-medewerker's telefoon "geldig" bij een heractivatie of phishing-incident.</p>

<p>Zie ook: <a href="/nl/blog/m365-entra-id-governance-mkb">M365-pillar</a>, <a href="/nl/blog/conditional-access-uitleg">Conditional Access</a>.</p>
HTML,
			],

			[
				'slug' => 'conditional-access-uitleg',
				'title' => 'Conditional Access voor MKB: wat, wanneer, hoe?',
				'excerpt' => 'Conditional Access is het "als dit, dan dat" van M365-security. Klinkt groot — is eigenlijk 5 policies die 80% van de risico\'s afdekken. Hier de minimum-set.',
				'tags' => ['conditional-access', 'm365', 'security'],
				'published_offset_days' => 33,
				'body' => <<<'HTML'
<p>Conditional Access (CA) laat je policies maken als "vereis MFA wanneer X". Klinkt enterprise, maar de minimum-set is behapbaar voor MKB.</p>

<h2>5 policies die je wil hebben</h2>
<ol>
  <li><strong>Block legacy authentication.</strong> Oude e-mail-protocollen kunnen geen MFA. Als je ze niet gebruikt, block alles.</li>
  <li><strong>Require MFA for all users.</strong> De basis-policy.</li>
  <li><strong>Require MFA for privileged roles.</strong> Met stricter controls (geen app-password, geen trusted device remember).</li>
  <li><strong>Require compliant device for admin portals.</strong> Alleen bedrijfs-beheerde laptops mogen in admin.microsoft.com of Azure portal.</li>
  <li><strong>Block sign-in from risky countries.</strong> Als je bedrijf geen Aziatische business heeft: block logins uit dat gebied. Lage kosten, hoge blocker voor credential-stuffing.</li>
</ol>

<h2>Licentie-vereisten</h2>
<p>Conditional Access zit in Azure AD Premium P1 (of in Business Premium). Als je alleen Business Basic hebt: upgrade minstens de 2-3 privileged-account-licenties naar P1.</p>

<h2>Test-modus eerst</h2>
<p>Elke nieuwe policy start in "report only". Je ziet 1 week wat er geblokkeerd zou zijn zonder dat er daadwerkelijk iets mis gaat. Pas daarna "on".</p>

<h2>Break-glass account</h2>
<p>Maak één account waar CA NIET op van toepassing is. Sterke random-wachtwoord, geprint in een kluis. Als je CA-config je uitsluit, is dit je redding.</p>

<p>Zie ook: <a href="/nl/blog/m365-entra-id-governance-mkb">M365-pillar</a>, <a href="/nl/blog/mfa-uitrollen-m365">MFA uitrollen</a>.</p>
HTML,
			],

			[
				'slug' => 'm365-admin-rollen',
				'title' => 'M365 admin-rollen uitgelegd: je hoeft niet iedereen Global Admin te maken',
				'excerpt' => 'M365 heeft ±70 admin-rollen. De meeste MKB\'s gebruiken er 2 (Global Admin + User Admin). Hier de rollen die je echt moet kennen en wanneer je ze inzet.',
				'tags' => ['m365', 'admin-rollen', 'privileged-access'],
				'published_offset_days' => 41,
				'body' => <<<'HTML'
<p>Niet elke admin-taak vereist Global Admin. M365 heeft een rijke rol-structuur — inzetten ervan is het verschil tussen "iedereen kan alles" en gericht least-privilege.</p>

<h2>De rollen die je echt moet kennen</h2>
<ul>
  <li><strong>Global Administrator:</strong> alles. 2-3 personen max.</li>
  <li><strong>User Administrator:</strong> users aanmaken, password resets, licenties toewijzen. Voor office-manager.</li>
  <li><strong>Helpdesk Administrator:</strong> password resets voor non-admin users. Junior helpdesk.</li>
  <li><strong>Exchange Administrator:</strong> mailbox-beheer, distributie-lists. Voor iemand die dat regelt.</li>
  <li><strong>SharePoint Administrator:</strong> SharePoint-site-beheer en permissies.</li>
  <li><strong>Teams Administrator:</strong> Teams-instellingen, meetings-policies.</li>
  <li><strong>Security Administrator:</strong> security-instellingen, Defender, CA-policies. Voor security-officer.</li>
  <li><strong>Global Reader:</strong> read-only Global Admin. Voor auditors of compliance-officer.</li>
</ul>

<h2>Toewijzing: patronen</h2>
<ul>
  <li>Office-manager / HR: User Administrator (voldoende voor onboarding/offboarding van users).</li>
  <li>Security-officer: Security Administrator + Global Reader.</li>
  <li>Accountant/compliance: Global Reader.</li>
  <li>IT-partner: afhankelijk van scope — vaak breder maar tijdelijk.</li>
</ul>

<h2>PIM</h2>
<p>Premium P2 licentie biedt Privileged Identity Management — admin-rollen staan niet permanent aan, je activeert ze voor een sessie. Voor MKB: overweeg bij &gt; 30 medewerkers voor je Global Admin-rol.</p>

<p>Zie ook: <a href="/nl/blog/m365-entra-id-governance-mkb">M365-pillar</a>, <a href="/nl/blog/privileged-access-management">PAM-artikel</a>.</p>
HTML,
			],

			[
				'slug' => 'guest-access-m365',
				'title' => 'Guest access in M365: veilig klanten en partners toegang geven',
				'excerpt' => 'Teams-channels delen met een partner, SharePoint-site voor een klant-project. Dat is guest access. Hier hoe je het beheert zonder dat je na een jaar 200 gasten hebt.',
				'tags' => ['guest-access', 'm365', 'externe-partijen'],
				'published_offset_days' => 49,
				'body' => <<<'HTML'
<p>Guest access = iemand buiten je tenant (gmail-account, andere bedrijfs-tenant) die toegang heeft tot specifieke resources. Krachtig, maar lekt snel als je het niet beheert.</p>

<h2>Hoe werkt het?</h2>
<p>Je nodigt een externe e-mail uit in een Team, SharePoint-site, of directe file-share. M365 maakt een guest-user aan in je tenant. Die guest kan bij wat jij deelt, niets anders.</p>

<h2>Beheer-discipline</h2>
<ul>
  <li><strong>Leg inventaris aan.</strong> Portal.office.com → Users → Guest users. Zie wie er is, sinds wanneer, laatst actief.</li>
  <li><strong>Review elk kwartaal</strong> — samen met je <a href="/nl/blog/periodieke-access-reviews-proces">normale access review</a>.</li>
  <li><strong>Verloop-datum vooraf</strong> — via Access Review-feature in Entra Premium P2, of handmatig getriggerd op projecteinde.</li>
  <li><strong>Geen gasten in Global Admin-rol.</strong> Nooit.</li>
</ul>

<h2>Settings op tenant-niveau</h2>
<ul>
  <li>Wie mag uitnodigen? Alleen admins, of alle users? Veel MKB: all users toestaan + gast-review kwartaal.</li>
  <li>Welke gasten mag men uitnodigen? Block problematische domeinen.</li>
  <li>Moeten gasten MFA doen? Ja.</li>
</ul>

<h2>Bij klant-projecteinde</h2>
<p>Verwijder de guest-user in plaats van alleen "uit Team halen". Anders blijft hij in je user-lijst hangen als potentiële achterdeur.</p>

<p>Zie ook: <a href="/nl/blog/teams-externe-gasten">Teams externe gasten</a>, <a href="/nl/blog/externe-partijen-en-consultants-toegang">externe partijen toegang</a>.</p>
HTML,
			],

			[
				'slug' => 'teams-externe-gasten',
				'title' => 'Teams externe gasten: welke instellingen bepalen de risk-profielen?',
				'excerpt' => 'Teams-guest-access heeft drie lagen tegelijk: organization settings, team settings, en chat settings. Hier het mentale model zodat je niet per ongeluk alles openzet.',
				'tags' => ['teams', 'guest-access', 'm365'],
				'published_offset_days' => 57,
				'body' => <<<'HTML'
<p>Teams-gasten zijn tegelijk de meest praktische en de meest risicovolle M365-feature. Drie lagen bepalen wat zij kunnen:</p>

<h2>1. Organization-level (Teams admin center)</h2>
<ul>
  <li>Guest access aan/uit globaal.</li>
  <li>Per-capability toggles: meetings, chat, calling, channels.</li>
  <li>Aanbevolen: alles aan, maar review wat je niet gebruikt (kan uit).</li>
</ul>

<h2>2. Team-level</h2>
<ul>
  <li>Team owner kan per team besluiten of gasten erin mogen.</li>
  <li>Public teams staan open voor iedereen in je tenant — overweeg of dat echt de bedoeling is.</li>
</ul>

<h2>3. Chat-level (external access)</h2>
<ul>
  <li>1-op-1 chat met externe users die Teams ook gebruiken.</li>
  <li>Whitelisted of blacklisted domeinen mogelijk.</li>
  <li>Aanbevolen: whitelist van partner-domeinen, blokkeer generieke mail-providers.</li>
</ul>

<h2>Shared channels (aparte feature)</h2>
<p>Sinds 2022 kun je kanalen delen met externe Teams-tenants zonder gastkoppeling. Gaat via Teams Connect. Review-implicatie: check welke shared channels bestaan en met wie.</p>

<h2>Praktijk-tip</h2>
<p>Zet een maandelijkse alert op "nieuwe guest-users". In moderne tenants is het gewoon via Entra audit logs trackbaar. Plotselinge toename is reden om te checken wat er speelt.</p>

<p>Zie ook: <a href="/nl/blog/guest-access-m365">guest access basis</a>, <a href="/nl/blog/m365-entra-id-governance-mkb">M365-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'sharepoint-permissies-uitleg',
				'title' => 'SharePoint-permissies: waarom ze uit de hand lopen en hoe je ze temt',
				'excerpt' => 'SharePoint is waar MKB-bedrijven de meeste onbedoelde data-lekken krijgen: mappen die "iedereen in het bedrijf" kunnen zien terwijl ze dachten intern waren. Hier de mentale modellen.',
				'tags' => ['sharepoint', 'permissies', 'data-lekken', 'm365'],
				'published_offset_days' => 65,
				'body' => <<<'HTML'
<p>SharePoint-permissies zijn gelaagd — wat op site-niveau staat verschilt van wat op bibliotheek, map, of individueel bestand geldt. Dat is tegelijk de kracht én waar het fout gaat.</p>

<h2>De 3 lagen</h2>
<ol>
  <li><strong>Site-level:</strong> wie heeft toegang tot de site überhaupt. Drie rollen: Owner, Member, Visitor.</li>
  <li><strong>Library/list-level:</strong> afwijkende permissies per document-bibliotheek.</li>
  <li><strong>Item-level:</strong> individuele bestanden of mappen met afwijkende permissies.</li>
</ol>

<h2>Waarom loopt het uit de hand</h2>
<ul>
  <li>Mensen klikken "Anyone with the link" in Share-dialog. Dat creëert een anonymous link.</li>
  <li>"Everyone in your organization" lijkt intern — maar inclusief gasten in sommige configs.</li>
  <li>Inherited permissions: afwijkingen op item-niveau die niemand bijhoudt.</li>
  <li>Orphaned permissions: iemand is weg maar permissies staan er nog.</li>
</ul>

<h2>Hygiëne</h2>
<ul>
  <li>Stel als default "only people you specify" in bij sharing. "Anyone with link" alleen op verzoek.</li>
  <li>Review elk kwartaal: sites met &gt; 50 members. Nog actueel?</li>
  <li>Zoek naar inherited-permission-afwijkingen: "View permissions" → "Advanced".</li>
  <li>Overweeg DLP-policies voor gevoelige bestandstypen (salarisstroken, contracten).</li>
</ul>

<h2>Retention + permissies</h2>
<p>Retention policies bepalen hoe lang iets staat. Permissies bepalen wie het kan zien. Beide apart — een document dat volgens retention bewaard moet blijven maar zichtbaar is voor iedereen is nog steeds een lek. Zie <a href="/nl/blog/m365-retention-policies">retention policies</a>.</p>

<p>Zie ook: <a href="/nl/blog/onedrive-sharing-policy">OneDrive sharing policy</a>, <a href="/nl/blog/m365-entra-id-governance-mkb">M365-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'onedrive-sharing-policy',
				'title' => 'OneDrive sharing policy: hoe voorkom je per-bestand-share-chaos?',
				'excerpt' => 'Mensen delen bestanden vanuit OneDrive de hele dag door. Hoe stel je tenant-wide policies in die veilig gedrag faciliteren zonder productiviteit te doden?',
				'tags' => ['onedrive', 'sharing', 'm365', 'data-loss-prevention'],
				'published_offset_days' => 73,
				'body' => <<<'HTML'
<p>OneDrive is de persoonlijke bestandenopslag in M365. Elke user heeft zijn eigen OneDrive en kan van daaruit delen. Tenant-wide sharing policies bepalen wat mag.</p>

<h2>Drie standaard-niveaus</h2>
<ul>
  <li><strong>Only people in your organization:</strong> strikt, vaak te strikt voor sales/consultants.</li>
  <li><strong>New and existing guests:</strong> externe mag, maar ze moeten MFA/account.</li>
  <li><strong>Anyone (anonymous links):</strong> open voor iedereen met de link. Risk.</li>
</ul>

<h2>De MKB-configuratie</h2>
<ul>
  <li>Default op "new and existing guests". Dat laat consultants en klanten toe, eist wel een account.</li>
  <li>Anonymous links: aan, maar met verloop-datum (30 dagen) en lees-only default.</li>
  <li>Download-blok op anonymous links voor gevoelige bestandstypen.</li>
  <li>Auto-expire na 90 dagen inactiviteit van een gedeelde link.</li>
</ul>

<h2>Notification setup</h2>
<p>User krijgt notificatie wanneer iemand op hun gedeelde bestand klikt. Verhoogt awareness en helpt bij verrassingen.</p>

<h2>DLP voor belangrijke types</h2>
<p>Data Loss Prevention regels voor: creditcardnummers, BSN's, salarisdata-sheets. Blokkeer extern delen als de DLP-engine het opmerkt. Licentie vereist: Business Premium of E3.</p>

<p>Zie ook: <a href="/nl/blog/sharepoint-permissies-uitleg">SharePoint permissies</a>, <a href="/nl/blog/m365-entra-id-governance-mkb">M365-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'mailbox-delegatie-m365',
				'title' => 'Mailbox delegatie in M365: gedelegeerd vs. shared vs. full access',
				'excerpt' => 'Secretaresse-aan-CEO-mailbox, shared inbox support@, gedelegeerde agenda. Drie technisch verschillende mechanismen met elk eigen permissie-model.',
				'tags' => ['mailbox', 'delegatie', 'exchange', 'm365'],
				'published_offset_days' => 81,
				'body' => <<<'HTML'
<p>Drie manieren om iemand in jouw mailbox te laten: delegation, send-on-behalf, en shared mailbox. Ze zijn niet uitwisselbaar.</p>

<h2>Delegation</h2>
<p>Rechten: view, edit, manage. Delegate opent de mailbox als "gedelegeerde". Gebruikt voor: secretaresse-aan-directeur. Mail blijft van de ene eigenaar.</p>

<h2>Send-on-behalf</h2>
<p>Delegeerde stuurt namens iemand. Mail toont "[delegate] namens [eigenaar]". Voor transparantie. Ontvanger weet wie er daadwerkelijk heeft geklikt.</p>

<h2>Full Access</h2>
<p>Volledige rechten incl. send-as. Delegeerde stuurt mail die eruit ziet alsof de eigenaar hem zelf heeft verstuurd. Gevaarlijk tenzij goed afgesproken. Log van zo'n send-as is wel aanwezig in audit-log.</p>

<h2>Shared mailbox</h2>
<p>Geen eigenaar, meerdere users erop. support@, info@. Geen extra licentie nodig tot 50GB. Meest gebruikt en meest misbruikt — "iedereen kan erbij" wordt snel "niemand voelt zich verantwoordelijk".</p>

<h2>Governance</h2>
<ul>
  <li>Elke shared mailbox heeft een menselijke eigenaar (verantwoordelijke voor opruimen van delegatie bij vertrek).</li>
  <li>Delegatie-lijst per mailbox jaarlijks reviewen.</li>
  <li>Send-as NIET gebruiken tussen mensen zonder expliciete afspraak.</li>
  <li>Bij <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding</a>: delegations specifiek controleren, niet vergeten.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/m365-entra-id-governance-mkb">M365-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'intune-basics-mkb',
				'title' => 'Intune basics voor MKB: device management zonder overengineering',
				'excerpt' => 'Intune is Microsoft\'s MDM-platform. Voor MKB\'s heb je er 20% van nodig om 80% van de waarde te halen. Hier wat je daadwerkelijk instelt.',
				'tags' => ['intune', 'mdm', 'device-management', 'm365'],
				'published_offset_days' => 89,
				'body' => <<<'HTML'
<p>Intune (onderdeel van Microsoft 365 Business Premium) is de MDM-laag voor je bedrijfs-laptops en -telefoons. Uitgebreid, maar voor MKB is er een minimale set die genoeg waarde geeft.</p>

<h2>Wat je minimaal doet</h2>
<ol>
  <li><strong>Enrollment verplichten.</strong> Bedrijfs-laptops moeten bij Intune aangemeld zijn voordat ze bij bedrijfsdata kunnen (via <a href="/nl/blog/conditional-access-uitleg">Conditional Access</a>).</li>
  <li><strong>Compliance policies.</strong> Minimaal: disk encryption aan, wachtwoord-lockscreen, OS-versie binnen 6 maanden.</li>
  <li><strong>Remote wipe.</strong> Bij verlies of <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding</a>: device factory-reset op afstand.</li>
  <li><strong>Basic-software-push.</strong> Office, browser, Slack/Teams, 1Password automatisch installeerbaar.</li>
</ol>

<h2>Wat later kan</h2>
<ul>
  <li>App protection policies (mobile-specific: hoe gaat Outlook op een privé-telefoon met zakelijke data om).</li>
  <li>Windows Autopilot (zero-touch provisioning van nieuwe laptops).</li>
  <li>Defender policies (endpoint security afgedwongen vanuit Intune).</li>
</ul>

<h2>BYOD: wat moet je regelen?</h2>
<p>Privé-laptop, zakelijke mail: App Protection Policies zijn dan nodig. Je kunt Outlook configureren zodat bij app-niveau data-rebeoordeling gebeurt zonder de hele device te beheren. Dat is vaak de werkbare middenweg.</p>

<p>Zie ook: <a href="/nl/blog/m365-entra-id-governance-mkb">M365-pillar</a>, <a href="/nl/blog/laptop-diefstal-response">laptop-diefstal response</a>.</p>
HTML,
			],

			[
				'slug' => 'm365-licentiebeheer',
				'title' => 'M365 licentiebeheer: tot 20% besparen zonder functionaliteitsverlies',
				'excerpt' => 'De meeste MKB\'s hebben 15-20% meer licenties dan nodig — vertrokken medewerkers, verkeerd plan, dubbele abonnementen. Hier een review-aanpak die betaalt voor zichzelf.',
				'tags' => ['licentiebeheer', 'm365', 'kosten'],
				'published_offset_days' => 97,
				'body' => <<<'HTML'
<p>Bij een typisch 40-mans MKB is €2.000-4.000/maand aan M365-licenties niet ongewoon. Daar valt makkelijk 15-20% vanaf als je licentie-reviews doet.</p>

<h2>Waar gaat licentie-verspilling zitten?</h2>
<ul>
  <li>Vertrokken medewerkers wiens licentie nog toegewezen is ("vergeten vrij te maken").</li>
  <li>Users met een E3 die alleen Basic gebruiken.</li>
  <li>Users met Business Premium waar ze Business Standard-werkzaamheden hebben.</li>
  <li>Dubbel gekochte add-ons (Teams Phone terwijl men al E5).</li>
  <li>Licenties voor shared mailboxes &lt; 50GB (zijn gratis — geen licentie nodig).</li>
</ul>

<h2>Kwartaal-review</h2>
<ol>
  <li>Export gebruikers + licenties uit Admin Center.</li>
  <li>Cross-check met HR: wie is nog in dienst?</li>
  <li>Check gebruik: last sign-in &gt; 60 dagen = reviewen.</li>
  <li>Check feature-usage (M365 Usage Reports): E3 maar alleen Word/Excel? Downgrade mogelijk.</li>
  <li>Ontkoppel niet-gebruikte licenties binnen 2 weken.</li>
</ol>

<h2>Koppeling met offboarding</h2>
<p>Direct bij <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding</a>: licentie bewaren nog 30 dagen voor mailbox-access, daarna vrijgeven. Anders blijft hij €12-35/maand "verhuren".</p>

<h2>Wat levert het op?</h2>
<p>Bij 40 users: gemiddeld €300-600/maand bespaard. Jaarlijks €3.600-7.200. De review zelf kost 2 uur per kwartaal. Rendement is obvious.</p>

<p>Zie ook: <a href="/nl/blog/m365-entra-id-governance-mkb">M365-pillar</a>, <a href="/nl/blog/periodieke-access-reviews-proces">periodieke access reviews</a>.</p>
HTML,
			],

			[
				'slug' => 'm365-retention-policies',
				'title' => 'Retention policies in M365: bewaren of wissen, wie beslist?',
				'excerpt' => 'Sommige data móet je bewaren (fiscale verplichtingen), andere moét je juist wissen (AVG). Retention policies bepalen het automatisch — als je ze goed inricht.',
				'tags' => ['retention', 'm365', 'avg', 'compliance'],
				'published_offset_days' => 105,
				'body' => <<<'HTML'
<p>Retention policies in Microsoft Purview bepalen hoe lang content in M365 blijft staan. Zonder policies is alles oneindig — wat meestal AVG-onvriendelijk en storage-kostbaar is.</p>

<h2>Categorieën policies</h2>
<ul>
  <li><strong>Retain:</strong> content mag niet worden verwijderd, minstens X jaar bewaren.</li>
  <li><strong>Delete:</strong> content wordt automatisch verwijderd na X jaar.</li>
  <li><strong>Retain then delete:</strong> bewaar X jaar, dan verwijderen. Meest gebruikt.</li>
</ul>

<h2>Voorbeelden voor MKB</h2>
<ul>
  <li>E-mail: 7 jaar retain then delete (matcht fiscale bewaarplicht).</li>
  <li>Teams-chat: 2 jaar retain then delete (matcht ISO/AVG-richtlijn).</li>
  <li>SharePoint-sites voor klant-projecten: 5 jaar na project-einde.</li>
  <li>OneDrive van ex-medewerker: 90 dagen na offboarding (matcht <a href="/nl/blog/30-dagen-regel-offboarding">30-dagen + archief</a>).</li>
</ul>

<h2>Licentie</h2>
<p>Basis retention zit in Business Premium. Advanced (auto-applied via machine learning) vereist E5. Voor MKB is Basis ruim voldoende.</p>

<h2>Risico's</h2>
<ul>
  <li>Te agressieve delete-policies kunnen legal hold verhinderen bij rechtszaak.</li>
  <li>Te conservatieve retain kunnen AVG-probleem worden (te lang persoonsgegevens bewaren).</li>
  <li>Policies hebben 1-7 dagen nodig om tenant-wide actief te worden — test in klein-scope eerst.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/bewaartermijnen-personeelsdossier">bewaartermijnen personeelsdossier</a>, <a href="/nl/blog/avg-compliance-mkb">AVG-compliance pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'm365-groepen-als-access-profielen',
				'title' => 'Entra security-groups als access profielen in je IAM-tool',
				'excerpt' => 'Je hebt al groepen in Entra ID ingericht voor SharePoint-permissies. Die groepen kun je 1-op-1 gebruiken als AccessProfiles — dubbel werk vermeden.',
				'tags' => ['entra-id', 'security-groups', 'iam', 'directory-sync'],
				'published_offset_days' => 113,
				'body' => <<<'HTML'
<p>Als je M365 goed gebruikt heb je security-groups — per rol, per project, per team. Die groepen kun je direct inzetten als <a href="/nl/blog/rbac-rollen-voor-mkb">RBAC-rollen</a> in je toegangsbeheer-tool.</p>

<h2>Het patroon</h2>
<ol>
  <li>Je AccessGuard-tool verbindt met Entra via OAuth (delegated User.Read.All + Directory.Read.All).</li>
  <li>Tool pullt security-groups nachtelijk.</li>
  <li>Elke group wordt een AccessProfile.</li>
  <li>Leden van de group worden leden van het profile.</li>
  <li>Wijzigingen in Entra → volgende sync → automatisch verwerkt in AG.</li>
</ol>

<h2>Welke groepen lenen zich goed?</h2>
<ul>
  <li>Rol-groups ("Sales Team", "Engineering", "HR").</li>
  <li>Security-groepen voor SharePoint-sites.</li>
  <li>Conditional-Access-target-groepen.</li>
</ul>

<h2>Niet geschikt</h2>
<ul>
  <li>M365 Groups (die zijn voor Teams-membership, niet voor IAM).</li>
  <li>Dynamic groups met complexe regels — voor IAM liever expliciete membership.</li>
  <li>Groepen met gasten (die horen in een aparte guest-review).</li>
</ul>

<h2>Apply-to-members</h2>
<p>Koppel aan elke AccessProfile welke systemen+items erbij horen. Bij één klik "apply" wordt elk lid van de group in systemen X, Y, Z toegevoegd met de juiste state. Zie <a href="/nl/blog/birthright-access-beleid">birthright access</a> voor hoe je dat combineert.</p>

<p>Zie ook: <a href="/nl/blog/m365-entra-id-governance-mkb">M365-pillar</a>, <a href="/nl/blog/rbac-rollen-voor-mkb">RBAC</a>.</p>
HTML,
			],
		];
	}
}
