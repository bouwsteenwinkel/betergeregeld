<?php

namespace Database\Seeders\Blog;

use Illuminate\Database\Seeder;

class BlogAccessGuardSeeder extends Seeder
{
	public function run(): void
	{
		BlogSeedHelper::seedCluster(
			[
				'slug' => 'toegangsbeheer',
				'name' => 'Toegangsbeheer',
				'pillar_title' => 'Toegangsbeheer voor het MKB — zonder IT-afdeling',
				'intro' => 'Alles over wie waartoe toegang heeft: van eenvoudige Excel-matrix tot periodieke reviews en automatische sync met M365 of Google Workspace.',
				'sort_order' => 10,
			],
			self::posts(),
		);
	}

	private static function posts(): array
	{
		return [
			// ───────── PILLAR ─────────
			[
				'slug' => 'toegangsbeheer-mkb-complete-gids',
				'title' => 'Toegangsbeheer voor het MKB: de complete gids (2026)',
				'excerpt' => 'Van de eerste toegangsmatrix tot periodieke reviews en directory-sync — alles wat je moet weten als je bedrijf groeit voorbij 10 mensen maar nog geen IT-afdeling heeft.',
				'is_pillar' => true,
				'featured' => true,
				'tags' => ['iam', 'access-matrix', 'mkb', 'governance', 'start-hier'],
				'published_offset_days' => 10,
				'body' => <<<'HTML'
<p>Toegangsbeheer — in het Engels <em>Identity &amp; Access Management</em> of kortweg IAM — is het geheel van afspraken, processen en tools dat bepaalt <strong>wie in jouw organisatie waartoe toegang heeft, waarom, en voor hoe lang</strong>. Voor multinationals zijn dat kostbare systemen met eigen IAM-teams. Voor het MKB — zeg 10 tot 150 medewerkers zonder IT-afdeling — is het vaak een hoofdpijn die zich opstapelt tot er iets fout gaat.</p>

<p>Deze gids loopt het volledige onderwerp door in zes lagen. Elke laag heeft een verdiepend artikel; klik daarop als je op die laag bent aanbeland. Top-down of je eigen pad — je kunt het zo inrichten als je wil.</p>

<h2>1. Waarom is toegangsbeheer belangrijk?</h2>
<p>Drie redenen, in volgorde van urgentie:</p>
<ul>
  <li><strong>Ex-medewerkers hebben nog toegang.</strong> Dit is statistisch gezien het meest voorkomende datalek in het MKB. Iemand vertrekt, niemand zet zijn Dropbox uit, twee maanden later gebeurt er iets. Lees meer over <a href="/nl/blog/waterdichte-offboarding-stappen">waterdichte offboarding</a>.</li>
  <li><strong>Je kunt een audit niet doorstaan.</strong> ISO 27001 Annex A.9 eist expliciet bewijs van periodieke access reviews. Zonder registratie geen certificaat. Zie onze <a href="/nl/blog/iso-27001-annex-a9-toegangsbeheer">ISO 27001 Annex A.9 gids</a>.</li>
  <li><strong>Je gaat meer licenties betalen dan nodig.</strong> Gemiddeld 18% van M365- en Salesforce-licenties in het MKB gaan naar mensen die al weg zijn of ze niet meer gebruiken.</li>
</ul>

<h2>2. De toegangsmatrix — je startpunt</h2>
<p>Voordat je denkt aan sync, rollen of automatisering: je eerste stap is de <a href="/nl/blog/eerste-toegangsmatrix-in-een-middag">toegangsmatrix</a>. Dat is een simpele grid met medewerkers op de ene as en systemen op de andere. Cellen zeggen: heeft toegang, geen toegang, moet gecheckt. Je eerste versie past in een spreadsheet; later groeit hij mee naar een tool. Het punt is vooral: <em>je schrijft het voor het eerst op</em>.</p>

<h2>3. Rollen, profielen, en birthright access</h2>
<p>Na een paar maanden merk je: elke nieuwe sales-medewerker krijgt dezelfde 6 systemen. Dat patroon vang je in een <a href="/nl/blog/rbac-rollen-voor-mkb">rol (RBAC)</a>. Combineer dat met een <a href="/nl/blog/birthright-access-beleid">birthright-beleid</a> (wat krijgt <em>iedereen</em>?) en de onboarding-IT-checklist is klaar in plaats van elke keer ad-hoc.</p>

<h2>4. Privileged access en least privilege</h2>
<p>Global Admin-rollen, AWS-root, "ik maak hem even admin in Salesforce zodat hij kan debuggen" — dat is <a href="/nl/blog/privileged-access-management">privileged access</a> en daar gaat meestal de meeste schade mee. Het <a href="/nl/blog/least-privilege-beginsel">least-privilege-beginsel</a> zegt: geef altijd zo min mogelijk, voor zo kort mogelijk. Simpel in theorie, in praktijk moet je er een proces van maken.</p>

<h2>5. Periodieke access reviews</h2>
<p>Eens per kwartaal — of elke zes maanden als je klein bent — loop je de matrix na en markeer je per rij: keep, revoke, change. Dit is waar auditors naar vragen. Zie de <a href="/nl/blog/periodieke-access-reviews-proces">aparte gids over access reviews</a> voor het proces, de valkuilen en wat je als bewijs bewaart.</p>

<h2>6. Directory-sync en automatisering</h2>
<p>Zodra je consistent Microsoft 365 of Google Workspace gebruikt, ga je niet meer handmatig een matrix bijhouden. Je koppelt het: de directory wordt de bron van waarheid, je IAM-tool trekt users + groepen automatisch in. Zie de <a href="/nl/blog/m365-entra-id-governance-mkb">M365 governance-gids</a> voor hoe dat werkt met Entra ID security-groups.</p>

<h2>Wat nu?</h2>
<p>Als je hieronder moet beginnen met iets concreets: bouw eerst de matrix (<a href="/nl/blog/eerste-toegangsmatrix-in-een-middag">in een middag</a>), dan de offboarding-checklist, dan je eerste review-cyclus. De rest volgt vanzelf als je daar eenmaal grip op hebt.</p>

<p>Elke laag van dit artikel heeft onderliggende supporting-artikelen. Scroll naar de "Verwant leesmateriaal"-blok onderaan voor de aanbevolen volgorde.</p>
HTML,
			],

			// ───────── SUPPORTING ─────────
			[
				'slug' => 'eerste-toegangsmatrix-in-een-middag',
				'title' => 'Je eerste toegangsmatrix bouw je in een middag',
				'excerpt' => 'De simpelste stap in toegangsbeheer is meteen ook de belangrijkste: schrijf eens op wie waar toegang toe heeft. Hier is het receptje voor een werkbare eerste versie binnen vier uur.',
				'tags' => ['access-matrix', 'iam', 'mkb', 'start-hier', 'getting-started'],
				'published_offset_days' => 20,
				'body' => <<<'HTML'
<p>Veel MKB-bedrijven beginnen aan toegangsbeheer door meteen een tool te vergelijken. Dat is de verkeerde eerste stap. Begin met een matrix: een simpele grid waarop je in één oogopslag ziet wie waar toegang toe heeft. Bouwtijd: één middag als je het volgende recept volgt.</p>

<h2>Stap 1: Lijst van personen (20 minuten)</h2>
<p>Open een spreadsheet. Zet in kolom A iedereen op de loonlijst. Voeg een kolom "type" toe: employee, contractor, of external (bijv. accountant die toegang tot Exact heeft). Voeg status toe: actief, ingepland, inactief.</p>
<p>Tip: start vanuit je HR-systeem of — als je geen HR hebt — de lijst uit je boekhoudpakket. Vergeet oud-medewerkers van het laatste jaar niet. Die komen we later tegen als "verweesde toegang".</p>

<h2>Stap 2: Lijst van systemen (30 minuten)</h2>
<p>Nu de andere as: welke systemen verwerken gevoelige of bedrijfskritische data? Begin niet met alles. Begin met het volgende rijtje:</p>
<ul>
  <li>E-mail / Microsoft 365 / Google Workspace</li>
  <li>Je CRM (Salesforce, Pipedrive, HubSpot)</li>
  <li>Boekhouding (Exact, Moneybird, Twinfield, TeamLeader)</li>
  <li>Cloud-infrastructuur (AWS, Azure, GCP)</li>
  <li>Code-repositories (GitHub, GitLab, Bitbucket)</li>
  <li>Password vault (1Password, Bitwarden)</li>
  <li>Communicatie (Slack, Teams)</li>
  <li>Bestandsopslag (Dropbox, OneDrive, Google Drive)</li>
</ul>
<p>Later breid je uit naar tier-2 systemen (design-tools, marketing-tools, specifieke SaaS). Zie ook: <a href="/nl/blog/saas-inventaris-opstellen">SaaS-inventaris opstellen</a>.</p>

<h2>Stap 3: Vul de cellen in (2 uur)</h2>
<p>Per persoon × systeem schrijf je één van vier waardes:</p>
<ul>
  <li><strong>has_access</strong> — heeft toegang, je weet het zeker</li>
  <li><strong>no_access</strong> — geen toegang, je weet het zeker</li>
  <li><strong>needs_review</strong> — twijfelgeval, moet gecheckt</li>
  <li><strong>unknown</strong> — nooit over nagedacht</li>
</ul>
<p>Probeer eerst vanuit je eigen hoofd. Loop daarna per systeem na wat de admin-interface zegt (zie <a href="/nl/blog/m365-entra-id-governance-mkb">M365 governance</a> voor hoe je dat in Entra doet). Waar je het niet weet: <em>needs_review</em>. Daar vul je later een hele middag mee.</p>

<h2>Stap 4: Markeer de afwijkingen (30 minuten)</h2>
<p>Loop de matrix één keer horizontaal af. Vraag jezelf per rij: "heeft deze persoon toegang tot systemen die ik raar vind?" Typische vondsten in een eerste matrix:</p>
<ul>
  <li>Iemand die al 8 maanden weg is staat nog op has_access voor Dropbox — <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding gat</a></li>
  <li>De marketeer is Global Admin in M365 "omdat het makkelijk was" — zie <a href="/nl/blog/least-privilege-beginsel">least privilege</a></li>
  <li>Je externe accountant heeft toegang tot HR-mailbox die niet in scope hoort — classificeer dit als <a href="/nl/blog/privileged-access-management">privileged access</a></li>
</ul>

<h2>Van spreadsheet naar tool</h2>
<p>Na een paar maanden merk je dat de spreadsheet uit de hand loopt: versies in e-mail, tabbladen per jaar, niemand weet welke de laatste is. Dat is het moment om een tool te nemen. Begin met iets dat dezelfde structuur heeft (persoon × systeem × status + notitie) — zoals <a href="/nl/tools/accessguard">AccessGuard</a> — zodat je niet opnieuw hoeft te beginnen.</p>

<p>Je kunt je eerste matrix in de <a href="/nl/accessguard/demo">publieke demo</a> live zien staan, met 6 medewerkers × 6 systemen en 2 automatisch gevlagde risico's.</p>
HTML,
			],

			[
				'slug' => 'rbac-rollen-voor-mkb',
				'title' => 'RBAC: rollen definiëren in het MKB zonder bureaucratie',
				'excerpt' => 'Role-Based Access Control klinkt als een IT-architectuur-feest, maar in het MKB is het gewoon: schrijf op welke 4–6 typerollen je hebt en welke systemen elke rol standaard krijgt.',
				'tags' => ['rbac', 'rollen', 'iam', 'onboarding'],
				'published_offset_days' => 28,
				'body' => <<<'HTML'
<p>Na je eerste <a href="/nl/blog/eerste-toegangsmatrix-in-een-middag">toegangsmatrix</a> valt iets op: elke nieuwe sales-medewerker krijgt precies dezelfde 6 systemen. Elke nieuwe developer ook. Dat patroon vang je in een <strong>rol</strong>. Dat heet RBAC — Role-Based Access Control. In het MKB hoef je er niks moeilijks van te maken.</p>

<h2>Hoeveel rollen heb je nodig?</h2>
<p>Minder dan je denkt. Voor een MKB van 20–60 mensen kom je zelden boven de 8 rollen uit:</p>
<ol>
  <li>Sales (AE + SDR)</li>
  <li>Engineering / Developer</li>
  <li>Operations / Support</li>
  <li>Finance / Boekhouding</li>
  <li>Marketing</li>
  <li>HR / People Ops</li>
  <li>Management / Leadership</li>
  <li>External / Consultant (vaak ingeperkte toegang)</li>
</ol>
<p>Tip: match je rollen op je <em>bestaande afdelings-structuur</em>, niet op abstracte IAM-categorieën. Als er intern "Sales" op staat, noem het dan ook Sales in je toegangsbeheer.</p>

<h2>Wat hoort bij een rol?</h2>
<p>Per rol leg je vast:</p>
<ul>
  <li><strong>Birthright-toegang</strong>: systemen die iedereen in deze rol standaard krijgt. Zie ook <a href="/nl/blog/birthright-access-beleid">birthright-beleid</a>.</li>
  <li><strong>Optionele toegang</strong>: systemen die vaak wél nodig zijn maar niet automatisch. Wordt per persoon aangevraagd.</li>
  <li><strong>Verboden toegang</strong>: systemen waar deze rol expliciet geen toegang toe mag hebben (bijv. Sales geen toegang tot HR-mailbox).</li>
</ul>

<h2>Een voorbeeld: rol "Sales"</h2>
<blockquote>Birthright: M365 E3, Slack, Salesforce, LinkedIn Sales Nav, 1Password, Calendly. Optioneel: HubSpot (content), Loom (video's). Verboden: Exact (boekhouding), AWS.</blockquote>
<p>Dat is het. Schrijf dit op, zet hem in je <a href="/nl/tools/accessguard">toegangsbeheer-tool</a>, en elke nieuwe sales-hire krijgt binnen 10 minuten de juiste spullen via één klik.</p>

<h2>De valkuil: rol-creep</h2>
<p>Na twee jaar zie je soms 18 rollen staan waarvan er 11 ooit voor één uitzondering zijn aangemaakt. Voorkom dit door:</p>
<ul>
  <li>Elke rol jaarlijks te reviewen: is er meer dan 1 persoon met deze rol? Nee → opheffen.</li>
  <li>Uitzonderingen NIET als nieuwe rol maar als individuele aanpassing te loggen.</li>
  <li>Rol-namen kort en generiek te houden (Sales, niet "Sales-Team-NL-Q4-2025").</li>
</ul>

<h2>Hoe zit dit met M365 security-groups?</h2>
<p>Prima combinatie. In de praktijk definieer je je rollen als security-groups in Entra ID; onze <a href="/nl/blog/m365-entra-id-governance-mkb">M365-governance-gids</a> laat zien hoe je die automatisch als AccessProfiles binnenhaalt via directory-sync. Dan zijn rol-definities en lidmaatschap hetzelfde systeem.</p>

<p>Hoe rollen samenwerken met onboarding-processen lees je in de <a href="/nl/blog/onboarding-it-checklist-mkb">onboarding IT-checklist</a>.</p>
HTML,
			],

			[
				'slug' => 'birthright-access-beleid',
				'title' => 'Birthright access: wat krijgt iedereen automatisch?',
				'excerpt' => 'Birthright toegang is de verzameling systemen die elke medewerker vanaf dag 1 zou moeten hebben. Klein, duidelijk, bijna altijd hetzelfde — en een enorme tijdwinst bij onboarding.',
				'tags' => ['birthright', 'onboarding', 'rollen', 'iam'],
				'published_offset_days' => 35,
				'body' => <<<'HTML'
<p>Een nieuwe medewerker begint maandag. Om 9:00 zit hij aan zijn laptop. Wat moet minimaal werken voordat hij zijn koffie heeft gehaald? Dat antwoord is je <strong>birthright access</strong>.</p>

<h2>Wat hoort op de birthright-lijst?</h2>
<p>Het systemen die iederéén bij jou in dienst krijgt, ongeacht rol:</p>
<ul>
  <li>E-mail account (M365 / Google Workspace met juiste licentie)</li>
  <li>Password manager-account (1Password / Bitwarden)</li>
  <li>Intranet of company wiki (Notion, Confluence, SharePoint)</li>
  <li>HR-systeem voor eigen data (vakantiedagen, salarisstrook)</li>
  <li>Communicatie (Slack/Teams — zelfde workspace, juiste channels)</li>
  <li>Agenda / vergaderruimtes</li>
  <li>VPN als je die gebruikt</li>
</ul>

<h2>Wat hoort NIET op de birthright-lijst?</h2>
<p>Alles wat rol-specifiek is. Zie <a href="/nl/blog/rbac-rollen-voor-mkb">RBAC</a> voor hoe je die per rol vastlegt.</p>
<p>En alles waarvoor een zakelijke rechtvaardiging nodig is. Een nieuwe sales-hire krijgt GEEN automatische toegang tot payroll-data, ook al werkt hij hier vanaf dag 1.</p>

<h2>Waarom dit onderscheid echt uitmaakt</h2>
<ul>
  <li><strong>Snelheid:</strong> birthright kan automatisch op dag -1, rol-specifiek vereist validatie van de manager.</li>
  <li><strong>Duidelijkheid:</strong> iedereen weet wat er moet staan vanaf dag 1 — geen "had ik nog moeten krijgen?"</li>
  <li><strong>Audit:</strong> je kunt aantonen dat toegang gerechtvaardigd was, omdat ze óf birthright zijn óf goedgekeurd per rol.</li>
</ul>

<h2>Leg het schriftelijk vast</h2>
<p>Eén pagina op je wiki is genoeg. Sectie 1: de birthright-lijst. Sectie 2: per rol de extra's. Sectie 3: de uitzonderingenprocedure.</p>
<p>Die pagina review je elk half jaar. Zie ook <a href="/nl/blog/periodieke-access-reviews-proces">periodieke access reviews</a> voor hoe dat past in je review-cadans.</p>

<p>Tip: je kunt birthright en rol-bundels als <strong>AccessProfiles</strong> vastleggen in je toegangsbeheer-tool en ze in één klik toepassen op een nieuwe medewerker. Onze <a href="/nl/accessguard/demo">demo</a> laat dit werkend zien.</p>
HTML,
			],

			[
				'slug' => 'privileged-access-management',
				'title' => 'Privileged access management voor het MKB',
				'excerpt' => 'Global Admin, AWS root, Salesforce system admin — dat zijn de rollen waar de meeste ellende vandaan komt. Wat je kunt doen zonder een dure PAM-tool te kopen.',
				'tags' => ['pam', 'privileged-access', 'admin', 'iam', 'security'],
				'published_offset_days' => 42,
				'body' => <<<'HTML'
<p><em>Privileged Access Management</em> (PAM) klinkt als iets voor banken en overheden. Voor het MKB vertaal ik het simpel: welke accounts kunnen, als ze gecompromitteerd worden, je hele bedrijf platleggen? Dat zijn er in de praktijk 5 tot 15, en die verdienen een aparte behandeling.</p>

<h2>Inventariseer je privileged accounts</h2>
<p>Loop dit rijtje langs:</p>
<ul>
  <li>M365 / Entra Global Administrator(s)</li>
  <li>Google Workspace super admin</li>
  <li>AWS root user</li>
  <li>Boekhoudpakket admin (Exact, Moneybird enz.)</li>
  <li>Salesforce system admin</li>
  <li>GitHub/GitLab org owner</li>
  <li>Domein-registrar (TransIP, Hover, Namecheap)</li>
  <li>DNS-provider (Cloudflare)</li>
  <li>Password manager admin</li>
  <li>Hostingpaneel (Plesk, cPanel)</li>
  <li>Bankrekening ("rechten hebben om te betalen")</li>
</ul>
<p>Schrijf deze op met per account: wie heeft de credentials, in welke vault staan ze, wie is backup. Zie ook <a href="/nl/blog/saas-inventaris-opstellen">SaaS-inventaris opstellen</a>.</p>

<h2>De drie regels van privileged access</h2>
<ol>
  <li><strong>Niet voor dagelijks werk.</strong> Je Global Admin mailt en vergadert met een gewone user-account. Alleen voor admin-taken wissel je naar het privileged account. <em>Dedicated admin accounts</em> noemen we dat.</li>
  <li><strong>MFA is verplicht, geen optie.</strong> Voor normale users is MFA sterk aanbevolen, voor privileged accounts is het niet onderhandelbaar. Gebruik bij voorkeur een hardware token (YubiKey) voor deze set.</li>
  <li><strong>Minstens twee personen kennen de root-credentials.</strong> Eén persoon = single point of failure. Drie = te veel. Twee is goud.</li>
</ol>

<h2>Just-in-time access: het volwassen patroon</h2>
<p>In Entra ID kun je PIM (Privileged Identity Management) configureren: Global Admin-rechten zijn NIET standaard actief, iemand moet ze activeren voor een sessie van max. 8 uur, met goedkeuring van een collega. Dat reduceert het aanvalsoppervlak enorm. Zie de <a href="/nl/blog/m365-entra-id-governance-mkb">M365 governance-gids</a> voor de setup.</p>

<h2>Review en rotatie</h2>
<p>Elk kwartaal: loop de privileged-lijst na. Twee vragen per regel: "heeft deze persoon dit nog nodig?" en "wanneer was het laatste gebruik?" Rekening: als een Global Admin 95 dagen niet heeft ingelogd, is dat een risico (<a href="/nl/blog/periodieke-access-reviews-proces">access review</a>).</p>

<p>Van alle categorieën in je <a href="/nl/blog/toegangsbeheer-mkb-complete-gids">toegangsbeheer</a> is dit de categorie waar je het minste van maar de meeste aandacht aan besteedt. Zo hoort het ook.</p>
HTML,
			],

			[
				'slug' => 'least-privilege-beginsel',
				'title' => 'Het least-privilege-beginsel uitgelegd voor ondernemers',
				'excerpt' => 'Geef zo min mogelijk toegang voor zo kort mogelijk. Dat klinkt alsof het productiviteit kost — in de praktijk bespaart het je van een datalek dat je maanden kost om uit te leggen.',
				'tags' => ['least-privilege', 'security', 'iam', 'governance'],
				'published_offset_days' => 48,
				'body' => <<<'HTML'
<p>"Geef mensen de toegang die ze nodig hebben, niet meer." Dat is het <strong>least-privilege-beginsel</strong> in één zin. Het lijkt een no-brainer maar in de praktijk is de verleiding groot om "iets meer" te geven: want anders krijg je weer een helpdesk-ticket als hij het nodig heeft.</p>

<h2>Waarom het ertoe doet</h2>
<p>Wanneer een account gecompromitteerd wordt (phishing, hergebruikt wachtwoord, gestolen cookie), gaat de aanvaller zo diep als dat account toelaat. Een marketeer met Global Admin = hele tenant over. Een marketeer met alleen Marketing-app-toegang = beperkte schade.</p>

<p>Datzelfde geldt intern: een ontevreden medewerker in zijn laatste 2 weken met meer toegang dan nodig = een probleem dat je niet wil hebben.</p>

<h2>Hoe implementeer je het zonder bureaucratie?</h2>
<ol>
  <li><strong>Default = minimaal.</strong> Bij onboarding alleen birthright + rol (<a href="/nl/blog/birthright-access-beleid">uitleg</a>). Alles daarbuiten moet aangevraagd en goedgekeurd.</li>
  <li><strong>Tijdgebonden toegang waar mogelijk.</strong> "Ik heb een week AWS-toegang nodig voor de migratie" → geef het voor een week, niet permanent. Zet een herinnering om weer in te trekken. Zie <a href="/nl/blog/tijdelijke-toegang-workflow">tijdelijke toegang-workflow</a>.</li>
  <li><strong>Downgrade automatisch bij rol-wissel.</strong> Van sales naar customer-success? CRM-admin-rechten weg. Dit is waar je <a href="/nl/blog/periodieke-access-reviews-proces">reviews</a> voor dienen.</li>
  <li><strong>"Maak me even admin" mag niet.</strong> De zin alleen al is een red flag. Vraag: wat probeer je te doen? Waarom heb je daar admin voor nodig?</li>
</ol>

<h2>Uitzonderingen zijn OK — als je ze logt</h2>
<p>Soms IS tijdelijke admin-toegang gewoon de praktische oplossing. Dan doe je het, maar je noteert: wie, wanneer, waarom, tot wanneer. Dat log is je audit-bewijs dat het niet <em>zomaar</em> gebeurde.</p>

<h2>Least privilege en privileged access</h2>
<p>Het principe is het sterkst bij <a href="/nl/blog/privileged-access-management">privileged accounts</a>. Een extra recht op een gewone user is een ongemak; een extra recht op een admin is een ramp-in-wording.</p>

<p>Dit beginsel is de rode draad door de hele <a href="/nl/blog/toegangsbeheer-mkb-complete-gids">toegangsbeheer-gids</a>. Als je 1 regel onthoudt uit al onze artikelen, is het deze.</p>
HTML,
			],

			[
				'slug' => 'tijdelijke-toegang-workflow',
				'title' => 'Tijdelijke toegang: hoe je hem geeft én weer intrekt',
				'excerpt' => 'Een consultant voor 6 weken, een developer die alleen de migratie moet doen, een vervanger tijdens zwangerschapsverlof. Tijdelijke toegang is makkelijk geven — moeilijk intrekken.',
				'tags' => ['tijdelijke-toegang', 'contractor', 'iam', 'governance'],
				'published_offset_days' => 55,
				'body' => <<<'HTML'
<p>Tijdelijke toegang geven is makkelijk. Tijdelijke toegang intrekken is waar bijna elk MKB faalt. De consultant die in februari 6 weken heeft geholpen, heeft in september nog steeds toegang tot je Salesforce. Dat is het patroon.</p>

<h2>Vier ingrediënten voor een werkend proces</h2>
<ol>
  <li><strong>Einddatum verplicht bij aanvraag.</strong> Geen "tot we het niet meer nodig hebben" — er staat een datum in het systeem.</li>
  <li><strong>Herinnering naar verantwoordelijke.</strong> 5 dagen voor de einddatum krijgt de interne manager een mail: "Toegang X van Y loopt af op …. Verlengen of laten aflopen?"</li>
  <li><strong>Automatische intrekking op einddatum.</strong> Geen menselijke actie nodig — de tool zet het account op inactive en zet alle has_access-cellen op needs_review.</li>
  <li><strong>Log als audit-trail.</strong> Wie is het aangevraagd voor, door wie goedgekeurd, tot wanneer actief, wanneer daadwerkelijk ingetrokken.</li>
</ol>

<h2>Patronen die wel werken</h2>
<ul>
  <li><strong>Default 30 dagen.</strong> Tenzij expliciet verlengd. Moet je elke maand verlengen? Ja. Is dat irritant? Ja. Is het effectief? Bijzonder.</li>
  <li><strong>Quarterly cleanup van contractors.</strong> Zelfs als ze nog lopen: elke 3 maanden een vraag aan de opdrachtgever: "moeten we deze nog verlengen?"</li>
  <li><strong>Gescheiden accounts voor externen.</strong> consultant.name@jouwbedrijf.nl, niet in je gewone medewerkerslijst. Visueel duidelijk dat dit tijdelijk is.</li>
</ul>

<h2>Integratie met offboarding</h2>
<p>Zie ook de <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding-checklist</a>. Voor contractors is offboarding eigenlijk "verloopt op einddatum" — het aparte ding daarbij is dat je ook <a href="/nl/blog/klantoverdracht-bij-consultant-vertrek">klantoverdracht</a> moet regelen.</p>

<p>Onze <a href="/nl/tools/accessguard">AccessGuard-tool</a> laat je einddata vastleggen op persoon en op individuele systeem-toegang; de risk-scanner vlagt automatisch tijdelijke toegang die verstreken is maar nog actief staat.</p>
HTML,
			],

			[
				'slug' => 'onboarding-it-checklist-mkb',
				'title' => 'Onboarding IT-checklist: wat moet er klaar staan op dag 1?',
				'excerpt' => 'De beste eerste werkdag is een saaie. Laptop werkt, accounts staan klaar, mappen zijn gedeeld. Deze checklist dekt het dichte MKB-scenario van 12 systemen en 4 rollen.',
				'tags' => ['onboarding', 'checklist', 'iam', 'hr'],
				'published_offset_days' => 62,
				'body' => <<<'HTML'
<p>Goede onboarding-IT is onzichtbaar: de nieuwe medewerker merkt geen frictie. Slechte onboarding-IT is een stapel helpdesk-tickets in week 1. Hier de checklist die werkt voor een typisch MKB-scenario.</p>

<h2>Dag -2: voorbereiding</h2>
<ul>
  <li>HR stuurt de toegangsaanvraag door met: naam, rol, startdatum, manager</li>
  <li>IT / office-manager kiest de <a href="/nl/blog/rbac-rollen-voor-mkb">rol-bundel</a></li>
  <li>Laptop wordt voorbereid (Windows/Mac image, bedrijfs-enrollment in Intune/Jamf)</li>
  <li>E-mail-account en licenties worden aangemaakt (maar nog niet actief)</li>
</ul>

<h2>Dag -1: accounts actief</h2>
<ul>
  <li>Activeer M365 of Google-account om 17:00 de vorige dag</li>
  <li>Pas de <a href="/nl/blog/birthright-access-beleid">birthright-profiel</a> toe</li>
  <li>Pas het rol-specifieke profiel toe</li>
  <li>Add to Slack/Teams channels die bij de rol horen</li>
  <li>Zet tijdelijk MFA-registratie verplicht op eerste login</li>
  <li>Stuur welcome-e-mail met temp-password en 1Password-invite naar privé-mail</li>
</ul>

<h2>Dag 1: ochtend (9:00–12:00)</h2>
<ul>
  <li>Manager ontvangt collega</li>
  <li>Eerste login + MFA-setup</li>
  <li>Introductie-afspraak met IT / office-manager (30 min): vault, MFA, wachtwoord-policy, security-basics</li>
  <li>Kennismaking met het team</li>
</ul>

<h2>Dag 1: middag</h2>
<ul>
  <li>Toegang tot tool-specifieke trainingen (LinkedIn Learning, YouTube-playlist, interne docs)</li>
  <li>Lees-middag: bedrijfs-wiki, security-policy</li>
  <li>Eerste commit in praktijk-repo (voor dev's)</li>
</ul>

<h2>Week 1: volgen</h2>
<ul>
  <li>Dag 3: check-in met manager en IT. Iets niet werkt? Nu oplossen.</li>
  <li>Dag 5: "heb je alles wat je nodig hebt?" — vaak blijken er 1-2 systemen vergeten</li>
  <li>Dag 14: review welke access daadwerkelijk gebruikt is. Ongebruikt? Revoke.</li>
</ul>

<h2>De grote valkuilen</h2>
<ul>
  <li><strong>Accounts pas op dag 1 aanmaken.</strong> Garantie dat dag 1 een stressdag wordt.</li>
  <li><strong>Alle managers kunnen alle rol-bundels kiezen.</strong> Leidt tot toegang die niet bij de functie past.</li>
  <li><strong>Geen uitgewerkte rol — ad-hoc vullen.</strong> Leidt tot inconsistentie en ondoorzoekbare matrices.</li>
</ul>

<p>Zie verder: <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding</a>, <a href="/nl/blog/onboarding-offboarding-parity">onboarding-offboarding parity</a>.</p>
HTML,
			],

			[
				'slug' => 'waterdichte-offboarding-stappen',
				'title' => 'Waterdichte offboarding in 12 stappen',
				'excerpt' => 'Iemand gaat weg. In het MKB is dit waar de meeste datalekken ontstaan. Hier is een checklist die dekt wat je écht moet doen — met termijnen, verantwoordelijken, en valkuilen.',
				'tags' => ['offboarding', 'checklist', 'iam', 'governance'],
				'published_offset_days' => 70,
				'body' => <<<'HTML'
<p>Voor onboarding vinden ondernemers het normaal om 3 uur in te plannen. Voor offboarding vaak 20 minuten op de laatste werkdag. Dat is waar het misgaat.</p>

<h2>De 12 stappen — in volgorde van belangrijkheid</h2>
<ol>
  <li><strong>Disable eerst, verwijder later.</strong> Op de laatste werkdag: account op <em>disabled</em> zetten, niet verwijderen. Je wilt e-mail nog kunnen lezen, bestanden nog kunnen terughalen.</li>
  <li><strong>Trek privileged access direct in.</strong> Global Admin, AWS root, boekhoud-admin — die gaan meteen weg, niet op einddag.</li>
  <li><strong>Verander gedeelde wachtwoorden.</strong> Alles in je password-vault waar deze persoon bij kon. Ja, allemaal. Zie <a href="/nl/blog/gedeelde-wachtwoorden-beheer">gedeelde wachtwoorden beheer</a>.</li>
  <li><strong>Zet e-mail forwarding aan.</strong> Naar manager of opvolger, voor 30 dagen.</li>
  <li><strong>Autoreply instellen.</strong> "Ik werk hier niet meer. Neem contact op met X."</li>
  <li><strong>Laptop innemen.</strong> Check op persoonlijke bestanden. Wipe via MDM.</li>
  <li><strong>Zet MFA-tokens uit.</strong> Authenticator-apps, hardware-tokens, SMS-nummers. Anders blijft een deactiveerd account nog "valide" voor een aanvaller.</li>
  <li><strong>Revoke van individuele SaaS accounts.</strong> Alles wat niet via SSO loopt moet je handmatig revoke'n: LinkedIn Sales Nav, Calendly, losse AI-tools. Check je <a href="/nl/blog/saas-inventaris-opstellen">SaaS-inventaris</a>.</li>
  <li><strong>Klant- en projectoverdracht.</strong> Wie is vanaf nu het aanspreekpunt voor klant X?</li>
  <li><strong>Vault-items overzetten.</strong> Die ene M365 customer admin-login die alleen zij kende → naar de collega die het overneemt.</li>
  <li><strong>30 dagen later: e-mail archiveren en account verwijderen.</strong> Licentie komt vrij, forward wordt uitgezet.</li>
  <li><strong>Loggen wat je hebt gedaan.</strong> Eén pagina per offboarding, bewaard voor audit-bewijs.</li>
</ol>

<h2>Wie doet wat?</h2>
<p>HR triggert. IT / office-manager voert uit. Manager doet de klant/project-overdracht. Directie tekent af op stappen 1-3 (de high-impact).</p>

<h2>Maak er een proces van, niet een checklist</h2>
<p>Een checklist die je elke keer opnieuw handmatig afwerkt is kwetsbaar. Zet het als <a href="/nl/blog/offboarding-proces-in-tool">proces in je tool</a>, met per stap: verantwoordelijke, SLA, bewijs. Zo loop je niets meer over.</p>

<p>Zie voor de juridische kant: <a href="/nl/blog/offboarding-juridisch-kader">juridisch kader</a>. Voor wat er GOED kan gaan: <a href="/nl/blog/onboarding-offboarding-parity">onboarding-offboarding parity</a>.</p>
HTML,
			],

			[
				'slug' => 'periodieke-access-reviews-proces',
				'title' => 'Periodieke access reviews: proces, frequentie, bewijsvoering',
				'excerpt' => 'Een access review is een audit-vereiste waar bijna elk MKB mee worstelt. De tweede keer hoef je er geen week meer voor uit te trekken — als je het de eerste keer goed opzet.',
				'tags' => ['access-review', 'audit', 'iso-27001', 'governance'],
				'published_offset_days' => 78,
				'body' => <<<'HTML'
<p>Access reviews — het periodiek doorlopen van wie waar toegang heeft en waarom — zijn voor ISO 27001 Annex A.9 niet optioneel. Ook zonder audit-druk is het de enige manier om je <a href="/nl/blog/eerste-toegangsmatrix-in-een-middag">toegangsmatrix</a> up-to-date te houden. Hier is het proces dat in 2–4 uur per kwartaal werkt.</p>

<h2>Frequentie: kwartaal of half jaar</h2>
<p>MKB-norm: per kwartaal. Heb je minder dan 20 mensen en lage turnover? Half jaar mag ook. Jaarlijks is niet genoeg voor ISO-doelen. Zie ook <a href="/nl/blog/iso-27001-annex-a9-toegangsbeheer">ISO 27001 Annex A.9</a>.</p>

<h2>De zes stappen</h2>
<ol>
  <li><strong>Snapshot.</strong> Bevries een kopie van de huidige matrix. Dit is je "stand per 1 oktober 2026".</li>
  <li><strong>Scope bepalen.</strong> Alle medewerkers? Alleen actieve? Ook externen? Leg vast zodat de volgende review dezelfde scope heeft.</li>
  <li><strong>Per rij besluiten.</strong> Keep, revoke, of change. Houd manager-verantwoordelijkheid erbij: sales-reviews doet de sales-manager, niet IT.</li>
  <li><strong>Bulk-acties voor zekere gevallen.</strong> 80% is "keep", 15% is "revoke" (meestal oud), 5% vraagt discussie. Bulk de eerste twee om tijd te besparen voor de laatste.</li>
  <li><strong>Follow-up-acties.</strong> Elke "revoke" en "change" wordt een concrete actie voor IT: intrekken, aanpassen.</li>
  <li><strong>Bewijs bewaren.</strong> Snapshot + beslissingen + wie heeft besloten + datum → PDF-rapport. Dit is je audit-evidence.</li>
</ol>

<h2>AI-assistentie: nuttig, geen magie</h2>
<p>Een AI-model kan per rij suggesties geven ("deze persoon heeft dit al 8 maanden niet gebruikt → revoke"). Dat scheelt soms 40% van de tijd. Maar de finale beslissing blijft menselijk — zeker bij privileged access. Zie <a href="/nl/blog/ai-bij-access-reviews">AI bij access reviews</a>.</p>

<h2>Valkuilen</h2>
<ul>
  <li><strong>"Er is tijd nog niet."</strong> Klopt nooit. Plan hem in Outlook, anders gaat hij niet door.</li>
  <li><strong>Review door één persoon.</strong> Missen = meeste eigen toegang wordt onterecht goedgekeurd. Betrek altijd de verantwoordelijke manager.</li>
  <li><strong>Besluiten NIET uitvoeren.</strong> Revoke op papier is niks. Binnen 2 weken moet alles verwerkt zijn.</li>
</ul>

<h2>Automatisering scheelt enorm</h2>
<p>Onze <a href="/nl/tools/accessguard">AccessGuard-tool</a> maakt een review-snapshot in 1 klik, bulk-besluiten zijn toetsenbord-vriendelijk, AI geeft suggesties, follow-up-acties worden automatisch aangemaakt, en het audit-rapport valt als PDF uit. De <a href="/nl/accessguard/demo">demo</a> toont een lopende cyclus met echte beslissingen.</p>
HTML,
			],

			[
				'slug' => 'saas-inventaris-opstellen',
				'title' => 'SaaS-inventaris opstellen: wat draait er eigenlijk in je bedrijf?',
				'excerpt' => 'Het gemiddelde MKB heeft 47 actieve SaaS-abonnementen. De helft weet niemand van. Zonder inventaris kun je geen toegangsbeheer doen, want je weet niet welke deuren je moet controleren.',
				'tags' => ['saas', 'shadow-it', 'inventaris', 'iam'],
				'published_offset_days' => 85,
				'body' => <<<'HTML'
<p>Toegangsbeheer zonder inventaris is dweilen met de kraan open. Je kunt pas reviewen wie ergens toegang heeft als je weet welke "ergens" er allemaal zijn.</p>

<h2>Hoe je tot een werkbare lijst komt</h2>
<ol>
  <li><strong>Start vanuit je creditcard- en bankafschriften.</strong> Filter op terugkerende afschrijvingen van SaaS-leveranciers. Dit dekt je bekende abonnementen.</li>
  <li><strong>Vraag elke afdelingsleider.</strong> "Welke tools gebruikt je team die niet via IT zijn geregeld?" Schrokken momentje.</li>
  <li><strong>Check je DNS-logs (of gebruik Cloudflare).</strong> Welke domeinen worden het meest bezocht vanaf bedrijfsdevices? *.slack.com, *.notion.so, *.hubspot.com …</li>
  <li><strong>SSO-logs.</strong> Als je Microsoft 365 of Google Workspace als SSO gebruikt, staan alle verbonden apps in de admin-console.</li>
  <li><strong>Inventariseer de <a href="/nl/blog/shadow-it-opruimen">shadow IT</a>.</strong> Tools die mensen met privé-account en privé-mail hebben gekocht — die wil je formaliseren of vervangen.</li>
</ol>

<h2>Wat leg je per SaaS vast?</h2>
<ul>
  <li>Naam + URL</li>
  <li>Zakelijk doel (in 1 zin)</li>
  <li>Data-classificatie: welke gevoelige data staat erin?</li>
  <li>Aantal gebruikers + €/maand</li>
  <li>Verantwoordelijke (interne eigenaar)</li>
  <li>Admin-account: waar staan de credentials?</li>
  <li>MFA: aan / uit / per-user</li>
  <li>SSO: wel / niet / mogelijk-maar-niet-ingezet</li>
  <li>Einddatum contract</li>
</ul>

<h2>Frequentie: elk kwartaal bijwerken</h2>
<p>Koppel dit aan je <a href="/nl/blog/periodieke-access-reviews-proces">access review</a>. Eén review-moment, twee doelen: bestaande toegang checken én inventaris actualiseren.</p>

<h2>Bespaar terwijl je opruimt</h2>
<p>Bij elke inventaris-ronde vind je 2–5 abonnementen die je kunt opzeggen of afbouwen. Dat betaalt het proces ruimschoots terug.</p>

<p>Zie ook: <a href="/nl/blog/shadow-it-opruimen">shadow IT opruimen</a> voor de politieke kant van het verhaal (collega's vinden het niet leuk als je hun favoriete tool inruilt).</p>
HTML,
			],

			[
				'slug' => 'shadow-it-opruimen',
				'title' => 'Shadow IT opruimen zonder revolutie',
				'excerpt' => 'De marketeer betaalt Canva Pro privé. Sales heeft een eigen LinkedIn-scraper. Dev gebruikt ChatGPT Team via privé-mail. Dat is shadow IT — en het is bijna nooit kwaadwillend.',
				'tags' => ['shadow-it', 'saas', 'governance'],
				'published_offset_days' => 92,
				'body' => <<<'HTML'
<p>Shadow IT is tools die in gebruik zijn binnen het bedrijf zonder dat IT of management er weet van hebben. In het MKB is het de regel, niet de uitzondering — en meestal niet kwaadwillend. Mensen willen hun werk doen, de officiële tool kan niet of is te langzaam, er is een alternatief voor €15/maand, ze klikken op "abonneren".</p>

<h2>De schade</h2>
<ul>
  <li><strong>Data lekt.</strong> Klant-data die in een privé-account staat is niet onder bedrijfs-beheer, overleeft offboarding niet, kan niet worden geëxporteerd of gewist.</li>
  <li><strong>Geen MFA, geen password-policy.</strong> Accounts die buiten SSO vallen zijn het meest kwetsbaar.</li>
  <li><strong>Dubbele kosten.</strong> Je betaalt al voor HubSpot maar sales gebruikt Pipedrive. Nu betaal je voor beide.</li>
  <li><strong>Bij audit of incident ken je de scope niet.</strong> "Welke systemen hebben klant-X data?" → geen antwoord.</li>
</ul>

<h2>Opruimen zonder confrontatie</h2>
<p>Shadow IT is bijna altijd een signaal dat je officiële stack iets niet biedt. Ga dus niet meteen verbieden — begrijp eerst waarom het is ontstaan.</p>
<ol>
  <li><strong>Amnestie-ronde.</strong> "We gaan inventariseren. Geen straf, iedereen krijgt 2 weken om zijn tools te melden."</li>
  <li><strong>Maak een SaaS-inventaris.</strong> Zie <a href="/nl/blog/saas-inventaris-opstellen">stappenplan</a>.</li>
  <li><strong>Per tool beslis:</strong> formaliseren (upgrade naar team-abonnement + SSO), vervangen (door iets in je bestaande stack), of accepteren als exception.</li>
  <li><strong>Biedt alternatieven aan.</strong> Als je Canva Pro ontmoedigt, zorg dan dat er een werkbaar alternatief is. Mensen kiezen tools voor een reden.</li>
  <li><strong>Maak aanvragen makkelijk.</strong> Ik wil een nieuwe SaaS → formulier → binnen 3 werkdagen antwoord. Zo voorkom je dat shadow IT weer groeit.</li>
</ol>

<h2>Tooling voor detectie</h2>
<p>Zero-trust gateways (Cloudflare Access, Zscaler) laten zien welke domeinen worden bezocht. Voor het MKB is dat vaak overkill — een kwartaalenquête werkt ook. Zie <a href="/nl/blog/saas-inventaris-opstellen">SaaS-inventaris</a> voor de praktische aanpak.</p>
HTML,
			],

			[
				'slug' => 'access-matrix-versus-rbac',
				'title' => 'Access matrix of RBAC: wat past bij jouw groeifase?',
				'excerpt' => 'Een directe matrix (persoon × systeem) werkt tot ±30 medewerkers. Daarna ga je rol-gebaseerd. Hier zie je wanneer je de switch maakt en hoe je hem zonder big-bang doet.',
				'tags' => ['access-matrix', 'rbac', 'iam', 'groei'],
				'published_offset_days' => 100,
				'body' => <<<'HTML'
<p>Een <a href="/nl/blog/eerste-toegangsmatrix-in-een-middag">toegangsmatrix</a> is je startpunt. <a href="/nl/blog/rbac-rollen-voor-mkb">RBAC</a> is waar je eindigt. Wanneer maak je de overstap?</p>

<h2>Tekenen dat je matrix uit zijn voegen groeit</h2>
<ul>
  <li>Je hebt &gt; 25 medewerkers — elke spreadsheet-update duurt 20 minuten.</li>
  <li>Nieuwe hires krijgen telkens dezelfde set — je kopieert per rol.</li>
  <li>Bij een review besluit je 80% van de tijd "keep, zoals bij anderen in Sales".</li>
  <li>Er komt een compliance-audit aan en je wil patronen kunnen laten zien.</li>
</ul>
<p>Als 2+ van deze gelden: tijd voor rol-gebaseerd werken.</p>

<h2>Hoe switch je zonder disruptie?</h2>
<ol>
  <li>Houd de bestaande matrix actief. Je gooit hem niet weg.</li>
  <li>Definieer je rollen op basis van wat je al ziet in de matrix — niet op basis van hoe het theoretisch zou moeten.</li>
  <li>Koppel mensen aan rollen. Verschil tussen wat de rol zegt en wat ze nu hebben = uitzondering, expliciet gedocumenteerd.</li>
  <li>Nieuwe hires → via rol. Bestaande mensen → via review-cyclus alignen (niet in één keer, over 2 kwartalen).</li>
</ol>

<h2>Wat als je M365 gebruikt?</h2>
<p>Dan heeft je rollen al een natuurlijke plek: security-groups in Entra ID. Zie <a href="/nl/blog/m365-entra-id-governance-mkb">M365 governance</a> voor hoe je groups ↔ AccessProfiles koppelt via directory-sync. Dat is de beste combinatie: mensen in Entra-groups zetten = automatische toegang toepassen.</p>

<h2>Matrix blijft de validatie-laag</h2>
<p>Ook na RBAC houd je de matrix. Rollen zijn <em>bedoelde</em> toegang; de matrix toont <em>actuele</em> toegang. Bij review vergelijk je ze: verschillen zijn waar onderzoek nodig is.</p>
HTML,
			],

			[
				'slug' => 'ai-bij-access-reviews',
				'title' => 'AI bij access reviews: wat werkt wel, wat niet',
				'excerpt' => 'AI-assistentie scheelt tot 40% tijd bij een review, mits je weet waar je het voor gebruikt. Niet als beslisser, wel als voorfilter en als uitlegger.',
				'tags' => ['ai', 'access-review', 'automation'],
				'published_offset_days' => 108,
				'body' => <<<'HTML'
<p>AI kan per access-review-regel een aanbeveling genereren: keep, revoke, change, met motivatie. Dat klinkt als magie. In de praktijk is het nuttig voor routinegevallen, niet voor beslissingen.</p>

<h2>Waar AI echt helpt</h2>
<ul>
  <li><strong>Voorfilter op het saaie werk.</strong> 80% van de rijen zijn "blijkbaar ongebruikt": laatst geverifieerd 6 maanden geleden, geen login-activiteit, lijkt op standaard sales-rol-drift. AI kan in 30 seconden aangeven: "deze 24 rijen zijn high-confidence keep, deze 8 zijn high-confidence revoke, deze 12 verdienen aandacht."</li>
  <li><strong>Uitleg genereren.</strong> "Waarom stelt AI revoke voor?" → "Laatste sign-in 127 dagen geleden, functietitel wijst niet op behoefte aan dit systeem, collega's in dezelfde rol hebben deze toegang niet." Die motivatie is je audit-bewijs.</li>
  <li><strong>Patroondetectie.</strong> "Iedereen in Sales heeft X, behalve deze 2 personen" → mogelijk een gat, mogelijk een reden.</li>
</ul>

<h2>Waar AI juist niet moet beslissen</h2>
<ul>
  <li><strong>Privileged access.</strong> Global Admin-besluit moet altijd menselijk. AI geeft context, mens beslist.</li>
  <li><strong>Externe partijen.</strong> Contractors, partners, klant-logins — context die AI niet altijd heeft.</li>
  <li><strong>Recentelijk vertrokken mensen.</strong> Offboarding-acties moeten expliciet zijn, niet AI-gedelegeerd.</li>
</ul>

<h2>Privacy: wat stuur je naar een LLM?</h2>
<p>Zet zo min mogelijk naar de AI: functietitel, afdeling, laatst geverifieerd, aantal cellen in matrix. Geen namen, geen e-mailadressen als dat kan. Onze <a href="/nl/tools/accessguard">AccessGuard</a> werkt met een fake-mode als je geen AI-key hebt — de flow werkt zonder dat er data naar OpenAI gaat.</p>

<p>AI is een versneller in je <a href="/nl/blog/periodieke-access-reviews-proces">review-proces</a>, geen vervanger voor oordeel. Als je daar nuchter mee omgaat haal je er veel waarde uit.</p>
HTML,
			],

			[
				'slug' => 'gedeelde-wachtwoorden-beheer',
				'title' => 'Gedeelde wachtwoorden: hoe je ze beheert zonder nachtmerrie',
				'excerpt' => 'Die ene admin-login voor de domeinregistrar, de social-media-accounts, de customer-portal. Die wachtwoorden ken je met 3 mensen, delen via een Excel is fout — dit is de rechtzetting.',
				'tags' => ['wachtwoordbeheer', 'vault', 'security'],
				'published_offset_days' => 115,
				'body' => <<<'HTML'
<p>Er zijn accounts die geen "eigenaar" hebben: de hoofdaccount van je domein-registrar, de Twitter/X-company-login, de customer-support-inbox. Die accounts moeten door 2–5 mensen te gebruiken zijn. Hoe doe je dat veilig?</p>

<h2>Regel 1: zet ze in een vault</h2>
<p>Nooit in een spreadsheet, nooit in een e-mail, nooit in een Notion-pagina. Wel in 1Password, Bitwarden, of een in-app vault zoals <a href="/nl/tools/accessguard">AccessGuard Vault</a> die per-user ACL ondersteunt plus audit-log van elke ontsleuteling.</p>

<h2>Regel 2: expliciete toegangslijst</h2>
<p>Per gedeeld account leg je vast: wie mag het zien. Minder is meer. Een social-media-account kan prima door 2 personen beheerd worden; er is geen reden waarom de hele company daar bij moet kunnen.</p>

<h2>Regel 3: rotatie bij wisseling</h2>
<p>Zodra iemand uit de toegangslijst gaat, verandert het wachtwoord. Zie <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding stap 3</a>. Geen uitzonderingen. Anders heb je een account met een "ex-collega kent het wachtwoord nog"-footprint.</p>

<h2>Regel 4: MFA waar maar enigszins kan</h2>
<p>Ook shared accounts hebben MFA nodig. Moderne MFA-apps (Authy, 1Password) ondersteunen gedeelde tokens. Hardware-tokens kunnen ook, maar dan moet er een fysieke overdracht zijn.</p>

<h2>Regel 5: audit-log</h2>
<p>Wie heeft wanneer het wachtwoord opgevraagd? Als je dat niet kunt aantonen, kun je bij een incident niet bepalen of er misbruik is geweest. Elke serieuze vault logt dit.</p>

<h2>Wat NIET werkt</h2>
<ul>
  <li>Eén persoon die alle wachtwoorden kent + "back-up" in een kluis op papier.</li>
  <li>Een Excel op SharePoint met "alle credentials" — ook met password erop.</li>
  <li>Per-se-every-one-can-see-everything-vaults. ACL is je vriend.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/privileged-access-management">privileged access management</a>, <a href="/nl/blog/password-manager-kiezen">een password manager kiezen</a>.</p>
HTML,
			],

			[
				'slug' => 'externe-partijen-en-consultants-toegang',
				'title' => 'Externe partijen toegang geven zonder de deur open te laten',
				'excerpt' => 'Consultant, boekhouder, freelance dev, partner-bedrijf. Allemaal mensen die geen medewerker zijn maar wel ergens in moeten. Hier een patroon dat werkt zonder dat je na 2 jaar 47 spook-accounts hebt.',
				'tags' => ['externe-partijen', 'contractor', 'iam', 'offboarding'],
				'published_offset_days' => 125,
				'body' => <<<'HTML'
<p>Externe partijen zijn de categorie waar access-hygiene het meest uit de hand loopt. Ze staan niet in je HR-systeem, hun onboarding is informeel, hun vertrek merk je vaak pas als iemand zegt "oh die doen we al een half jaar niet meer".</p>

<h2>Zes regels die het voorkomen</h2>
<ol>
  <li><strong>Aparte naming-conventie.</strong> <code>ext.jan.dejong@jouwbedrijf.nl</code> of via de naam van de externe partij. Visueel direct duidelijk.</li>
  <li><strong>Einddatum verplicht.</strong> Zie <a href="/nl/blog/tijdelijke-toegang-workflow">tijdelijke toegang</a>. Niet "onbekend" invullen — een datum, eventueel over 12 maanden, maar een datum.</li>
  <li><strong>Minimale scope.</strong> Niet hun eigen M365-account met volle rechten; liever guest-access op specifieke SharePoint-sites of één gedeelde mailbox.</li>
  <li><strong>Geen privileged access.</strong> Consultant mag dingen bekijken, niet admin-beslissingen doorvoeren (of alleen met expliciete scope + einddatum).</li>
  <li><strong>Aparte password-vault-sectie.</strong> Niet mengen met interne shared credentials.</li>
  <li><strong>Eigenaar bij naam.</strong> Per externe: wie binnen jouw bedrijf is verantwoordelijk? Bij diens vertrek is er iemand nodig om het over te pakken.</li>
</ol>

<h2>Het review-patroon</h2>
<p>Elk kwartaal bij je <a href="/nl/blog/periodieke-access-reviews-proces">review</a>: sectie "externe partijen" apart. Vraag de interne contactpersoon: "nog relevant? einddatum nog actueel?" Pending antwoord = inactiveer tot er reactie is.</p>

<h2>Wat met accountants en boekhouders?</h2>
<p>Die vallen onder "externe partij" maar met een langlopende relatie. Patroon: alleen toegang tot boekhouding-app, geen e-mail-account in jouw tenant, jaarlijkse review tegen de contract-duur. Zie ook <a href="/nl/blog/externe-accountant-toegang-boekhouding">externe accountant toegang regelen</a>.</p>
HTML,
			],

			[
				'slug' => 'externe-accountant-toegang-boekhouding',
				'title' => 'Externe accountant toegang geven tot je boekhouding',
				'excerpt' => 'Je accountant moet in Exact of Moneybird kunnen. Hoe geef je dat veilig, blijvend, en zonder dat de toegang na twee accountantswisselingen niemand meer kent?',
				'tags' => ['accountant', 'boekhouding', 'externe-partijen', 'exact', 'moneybird'],
				'published_offset_days' => 132,
				'body' => <<<'HTML'
<p>Accountantstoegang tot je boekhoudpakket is een terugkerend vraagstuk. De verleiding is groot om het even snel via de admin-login te doen. Dat is 99% van de gevallen waar het uit de hand loopt.</p>

<h2>Juiste manier, ongeveer gelijk voor elk pakket</h2>
<ol>
  <li><strong>Eigen account voor de accountant.</strong> Niet je admin delen. Gebruik de ingebouwde accountant-rol als die er is (Exact en Moneybird hebben beide).</li>
  <li><strong>Beperk scope per boekjaar.</strong> Meeste pakketten laten toe om alleen het afgelopen + lopende boekjaar te ontsluiten.</li>
  <li><strong>Zet MFA aan voor het accountants-account.</strong> Hun bedrijf zou dat intern moeten eisen, maar dubbele belt-and-braces kan geen kwaad.</li>
  <li><strong>Leg de relatie vast.</strong> Wie van het accountantskantoor heeft welke rechten? Bij wisseling moet je dit kunnen updaten.</li>
  <li><strong>Jaarlijkse review.</strong> Jaarafsluiting is het natuurlijke moment: werkt X nog bij het accountantskantoor? Heeft dezelfde nog toegang nodig?</li>
</ol>

<h2>Wachtwoord-beheer aan hun kant</h2>
<p>Hun accountantskantoor heeft zijn eigen password-policy. Wat jij kunt doen: zorgen dat de wachtwoord-reset-mail naar een gecontroleerde mailbox gaat, niet naar een persoonlijke account van één accountant. Zo overleef je een accountant-wisseling.</p>

<h2>Data-exports: wel of niet?</h2>
<p>Accountants willen vaak exports in Excel of CSV. Dat moet kunnen, maar documenteer het: wie heeft welke export wanneer gemaakt? Moderne boekhoudpakketten loggen dit; AVG-vereiste als er persoonsgegevens in zitten.</p>

<p>Zie ook <a href="/nl/blog/externe-partijen-en-consultants-toegang">externe partijen toegang</a> en de <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding-checklist</a> — die laatste geldt ook als je van accountant wisselt.</p>
HTML,
			],
		];
	}
}
