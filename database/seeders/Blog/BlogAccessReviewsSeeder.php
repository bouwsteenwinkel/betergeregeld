<?php

namespace Database\Seeders\Blog;

use Illuminate\Database\Seeder;

class BlogAccessReviewsSeeder extends Seeder
{
	public function run(): void
	{
		BlogSeedHelper::seedCluster(
			[
				'slug' => 'access-reviews',
				'name' => 'Access reviews',
				'pillar_title' => 'Periodieke access reviews — van Excel naar audit-klaar',
				'intro' => 'Waarom, hoe vaak, door wie en met welk bewijs. Reviews zijn het saaiste deel van toegangsbeheer — en tegelijk wat de meeste ISO-punten oplevert.',
				'sort_order' => 40,
			],
			self::posts(),
		);
	}

	private static function posts(): array
	{
		return [
			[
				// Move from AccessGuard to its own cluster.
				'slug' => 'periodieke-access-reviews-proces',
				'title' => 'Periodieke access reviews: proces, frequentie, bewijsvoering',
				'excerpt' => 'Een access review is een audit-vereiste waar bijna elk MKB mee worstelt. De tweede keer hoef je er geen week meer voor uit te trekken — als je het de eerste keer goed opzet.',
				'is_pillar' => true,
				'featured' => true,
				'tags' => ['access-review', 'audit', 'iso-27001', 'governance'],
				'published_offset_days' => 78,
				'body' => <<<'HTML'
<p>Access reviews — het periodiek doorlopen van wie waar toegang heeft en waarom — zijn voor ISO 27001 Annex A.9 niet optioneel. Ook zonder audit-druk is het de enige manier om je <a href="/nl/blog/eerste-toegangsmatrix-in-een-middag">toegangsmatrix</a> up-to-date te houden.</p>

<h2>Frequentie: kwartaal of half jaar</h2>
<p>MKB-norm: per kwartaal. Heb je minder dan 20 mensen en lage turnover? Half jaar mag ook. Jaarlijks is niet genoeg voor ISO-doelen. Zie ook <a href="/nl/blog/iso-27001-annex-a9-toegangsbeheer">ISO 27001 Annex A.9</a>.</p>

<h2>De zes stappen</h2>
<ol>
  <li><strong>Snapshot.</strong> Bevries een kopie van de huidige matrix.</li>
  <li><strong>Scope bepalen.</strong> Zie <a href="/nl/blog/access-review-scope-afbakenen">scope afbakenen</a>.</li>
  <li><strong>Per rij besluiten.</strong> Keep, revoke, of change. Betrek managers, zie <a href="/nl/blog/access-review-managers-betrekken">managers betrekken</a>.</li>
  <li><strong>Bulk-acties voor zekere gevallen.</strong> 80% is "keep". Zie <a href="/nl/blog/bulk-beslissingen-access-review">bulk-beslissingen</a>.</li>
  <li><strong>Follow-up-acties.</strong> Elke "revoke" en "change" wordt een concrete actie voor IT.</li>
  <li><strong>Bewijs bewaren.</strong> Zie <a href="/nl/blog/bewijsvoering-access-review-audit">bewijsvoering voor audit</a>.</li>
</ol>

<h2>Valkuilen</h2>
<ul>
  <li><strong>"Er is tijd nog niet."</strong> Plan hem in Outlook, anders gaat hij niet door.</li>
  <li><strong>Review door één persoon.</strong> Missen = meeste eigen toegang wordt onterecht goedgekeurd.</li>
  <li><strong>Besluiten NIET uitvoeren.</strong> Revoke op papier is niks.</li>
</ul>

<h2>Automatisering</h2>
<p>Onze <a href="/nl/tools/accessguard">AccessGuard-tool</a> maakt een review-snapshot in 1 klik. De <a href="/nl/accessguard/demo">demo</a> toont een lopende cyclus.</p>

<p>Zie ook: <a href="/nl/blog/access-review-kwartaal-cadans">kwartaal-cadans</a>, <a href="/nl/blog/ai-bij-access-reviews">AI bij reviews</a>, <a href="/nl/blog/steekproef-of-volledig-access-review">steekproef of volledig</a>.</p>
HTML,
			],

			[
				'slug' => 'access-review-kwartaal-cadans',
				'title' => 'Kwartaal-cadans voor access reviews: planning en ritme',
				'excerpt' => 'Vier keer per jaar een review klinkt veel. In praktijk kost het 3-4 uur per kwartaal bij goede opzet. Hier de cadans die werkt voor een 40-mans MKB.',
				'tags' => ['access-review', 'cadans', 'planning'],
				'published_offset_days' => 86,
				'body' => <<<'HTML'
<p>Een kwartaal-cadans is de natuurlijke ritme voor access reviews bij een MKB tot ±100 medewerkers. Minder is niet ISO-compliant, meer is onnodig werk.</p>

<h2>De 3-weken-cyclus per kwartaal</h2>
<ul>
  <li><strong>Week 1:</strong> snapshot + scope (30 min). Vaak op de eerste werkdag van het kwartaal.</li>
  <li><strong>Week 2:</strong> managers beslissen over hun teams (30 min per manager).</li>
  <li><strong>Week 3:</strong> IT voert revoke/change uit. Rapport wordt getekend en gearchiveerd.</li>
</ul>

<h2>Jaarkalender</h2>
<ul>
  <li>Q1 review: 2e week januari, rapport eind januari.</li>
  <li>Q2: 2e week april.</li>
  <li>Q3: 2e week juli.</li>
  <li>Q4: 2e week oktober — tevens input voor <a href="/nl/blog/management-review-iso">management review</a>.</li>
</ul>

<h2>Wie doet de coördinatie?</h2>
<p>Eén persoon is "review-owner". Vaak de security-officer of operations lead. Niet de CEO — dat schaalt niet.</p>

<h2>Escalatie</h2>
<p>Manager reageert niet binnen 5 werkdagen: escalatie naar directie. Consistentie hier is belangrijker dan de inhoud — als managers merken dat deadlines niet serieus worden genomen, raakt het hele proces achterop.</p>

<p>Zie ook: <a href="/nl/blog/periodieke-access-reviews-proces">review-pillar</a>, <a href="/nl/blog/access-review-managers-betrekken">managers betrekken</a>.</p>
HTML,
			],

			[
				'slug' => 'steekproef-of-volledig-access-review',
				'title' => 'Steekproef of volledige access review: wat accepteert de auditor?',
				'excerpt' => 'Op grotere schaal is een volledige review onwerkbaar. Risk-based steekproeven kunnen — mits je goed kunt uitleggen hoe je hebt bemonsterd.',
				'tags' => ['access-review', 'steekproef', 'audit'],
				'published_offset_days' => 94,
				'body' => <<<'HTML'
<p>Voor een 200-mans MKB met 40 systemen zijn 8.000 rijen per review. Dat werkt niet. Een risk-based steekproef is dan het antwoord — maar alleen als je hem kunt verdedigen.</p>

<h2>Wanneer volledig?</h2>
<ul>
  <li>Minder dan 500 cellen totaal (bij ±15 personen × ±10 systemen).</li>
  <li>Jaarlijkse "grote" review — ook als kwartalen steekproef zijn, eens per jaar alles door.</li>
  <li>Na een major incident.</li>
</ul>

<h2>Wanneer steekproef?</h2>
<ul>
  <li>Meer dan 1.000 cellen.</li>
  <li>Kwartaal-reviews bij grotere organisaties.</li>
</ul>

<h2>Hoe bemonster je risk-based?</h2>
<ol>
  <li><strong>Prioriteer privileged access.</strong> 100%.</li>
  <li><strong>Inactieve of net-actieve gebruikers.</strong> 100%.</li>
  <li><strong>Hoog-risico systemen (boekhouding, HR, klant-data).</strong> 100% van de access-rijen.</li>
  <li><strong>Rest:</strong> 20% steekproef per rol, gericht op rolwissels afgelopen kwartaal + ouderdom van last-verified.</li>
</ol>

<h2>Documentatie</h2>
<p>Auditor wil je bemonsterings-strategie zien — geschreven beleid, niet ad-hoc bij elke review anders. Zie <a href="/nl/blog/bewijsvoering-access-review-audit">bewijsvoering</a>.</p>

<p>Zie ook: <a href="/nl/blog/periodieke-access-reviews-proces">review-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'access-review-managers-betrekken',
				'title' => 'Managers betrekken bij access reviews zonder weerstand',
				'excerpt' => 'Een security-officer kan niet de sales-toegang beoordelen — dat moet de sales-manager. Hoe maak je dat een natuurlijk onderdeel van hun werk in plaats van een jaarlijkse hoofdpijn?',
				'tags' => ['access-review', 'managers', 'governance'],
				'published_offset_days' => 102,
				'body' => <<<'HTML'
<p>Zonder managers werkt geen access review. Zij zien wie echt werkt met wat. Jij ziet alleen of het aan staat of niet. Hoe maak je het geen drama?</p>

<h2>Maak het kort</h2>
<p>De manager moet binnen 20 minuten klaar zijn per kwartaal. Geef alleen de rijen voor zijn team. Pre-filter "keep"-voorstellen zodat de manager alleen de uitzonderingen hoeft te bekijken.</p>

<h2>Maak het zakelijk relevant</h2>
<p>Niet "dit moet van IT" — wel "we hebben vastgesteld dat 8 accounts in jouw team meer SaaS-kosten opleveren dan nodig. Kun je snel doorkijken welke we kunnen opzeggen?" Budget is een betere motivator dan compliance.</p>

<h2>Maak het makkelijk te doen</h2>
<ul>
  <li>1 link in een e-mail, geen inlog-route.</li>
  <li>Toetsenbord-vriendelijk (k voor keep, r voor revoke, c voor change).</li>
  <li>Bulk-acties voor gelijksoortige rijen.</li>
  <li>Mobile-first — veel managers doen dit tussen vergaderingen.</li>
</ul>

<h2>Begeleiding</h2>
<p>Eerste keer: 15 min walkthrough per manager. Tweede keer: kort refresher-mailtje. Derde keer: ze doen het zonder hulp.</p>

<h2>Verantwoording</h2>
<p>Rapport na de review toont: welke managers hebben op tijd hun deel gedaan, welke niet. Transparantie zonder naming-and-shaming werkt.</p>

<p>Zie ook: <a href="/nl/blog/periodieke-access-reviews-proces">review-pillar</a>, <a href="/nl/blog/access-review-kwartaal-cadans">kwartaal-cadans</a>.</p>
HTML,
			],

			[
				'slug' => 'bewijsvoering-access-review-audit',
				'title' => 'Bewijsvoering voor access reviews: wat bewaar je en waar?',
				'excerpt' => 'Een review zonder bewijs is voor de auditor een review die niet heeft plaatsgevonden. Hier wat je bewaart, in welk formaat, en hoe lang.',
				'tags' => ['access-review', 'audit', 'bewijsvoering', 'iso-27001'],
				'published_offset_days' => 110,
				'body' => <<<'HTML'
<p>Bij een ISO-audit vragen auditors om bewijs. "Wij doen reviews" is niet voldoende — ze willen zien wat er gebeurd is, door wie, wanneer.</p>

<h2>Per review bewaren</h2>
<ul>
  <li><strong>Snapshot-export:</strong> wie had waar toegang op het reviewmoment.</li>
  <li><strong>Beslissingen per rij:</strong> keep/revoke/change + eventuele motivatie.</li>
  <li><strong>Betrokkenen:</strong> wie heeft welke beslissingen genomen.</li>
  <li><strong>Tijdlijn:</strong> start, sluiting, uitvoering van acties.</li>
  <li><strong>Uitvoering:</strong> bevestiging dat revoke-acties daadwerkelijk zijn uitgevoerd.</li>
  <li><strong>Eindrapport:</strong> 2-pagina PDF met samenvatting, namen, handtekening.</li>
</ul>

<h2>Formaat</h2>
<p>PDF is ideaal voor audit-overdracht. Veel tools genereren dit automatisch — zie de <a href="/nl/accessguard/demo">AccessGuard-demo</a> voor een voorbeeld-rapport.</p>

<h2>Bewaartermijn</h2>
<p>Minimaal 3 jaar voor ISO-doeleinden. Praktijk: bewaar zolang je ISO-gecertificeerd bent + 1 jaar.</p>

<h2>Opslaglocatie</h2>
<p>Eén centrale plek in je wiki/documentensysteem, met ACL op "alleen compliance-team + directie". Niet verspreid over mailboxen.</p>

<h2>Wat auditors afwijzen</h2>
<ul>
  <li>Losse screenshots zonder context.</li>
  <li>Rapport zonder datum of namen.</li>
  <li>Geen koppeling tussen beslissing en uitvoering.</li>
  <li>Review-datum in toekomst ("gepland voor volgende week").</li>
</ul>

<p>Zie ook: <a href="/nl/blog/iso-27001-pre-audit-checklist">pre-audit checklist</a>, <a href="/nl/blog/periodieke-access-reviews-proces">review-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'bulk-beslissingen-access-review',
				'title' => 'Bulk-beslissingen bij access reviews: sneller zonder slordig',
				'excerpt' => '80% van de rijen in een review is routine. Die wil je in één klik kunnen afhandelen. Hoe doe je dat zonder dat je per ongeluk een kritieke rij mist?',
				'tags' => ['access-review', 'bulk', 'efficiency'],
				'published_offset_days' => 118,
				'body' => <<<'HTML'
<p>Als je elke rij individueel beoordeelt, duurt een review 10 uur in plaats van 2. Bulk-acties zijn de oplossing — mits je ze verantwoord inzet.</p>

<h2>Wat mag bulk?</h2>
<ul>
  <li>Gelijksoortige "keep"-rijen: bijvoorbeeld "iedereen in rol X heeft has_access op systeem Y, verwacht patroon."</li>
  <li>Rol-drift-cleanups: iedereen die door een rol-wissel een access heeft die de nieuwe rol niet omvat.</li>
  <li>Inactieve users: één klik op "revoke all" voor alle has_access-cellen van een persoon op inactive.</li>
</ul>

<h2>Wat mag GEEN bulk?</h2>
<ul>
  <li>Privileged access. Altijd één-voor-één.</li>
  <li>Uitzonderingen op rol-patronen. Die zijn per definitie de reden waarom een review bestaat.</li>
  <li>Externe partijen. Context verschilt per persoon.</li>
</ul>

<h2>Veiligheidsmechanismen</h2>
<ul>
  <li>Bulk-actie toont preview voor ze wordt toegepast.</li>
  <li>Log vermeldt dat het een bulk-beslissing was.</li>
  <li>Undo-optie binnen 1 uur zit ingebouwd.</li>
</ul>

<h2>UI-ontwerp dat helpt</h2>
<p>Een review-tool moet ondersteunen: filter op rol, filter op systeem, keyboard-shortcuts (k/r/c), "select all" met zichtbare count. Zie hoe <a href="/nl/tools/accessguard">AccessGuard</a> dit doet.</p>

<p>Zie ook: <a href="/nl/blog/periodieke-access-reviews-proces">review-pillar</a>, <a href="/nl/blog/ai-bij-access-reviews">AI bij reviews</a>.</p>
HTML,
			],

			[
				'slug' => 'access-review-scope-afbakenen',
				'title' => 'Access review scope: wat valt erin, wat niet?',
				'excerpt' => 'Niet elke gebruiker, niet elk systeem hoeft in elke review. Hier hoe je scope afbakent zodat het behapbaar blijft én audit-verdedigbaar.',
				'tags' => ['access-review', 'scope', 'governance'],
				'published_offset_days' => 125,
				'body' => <<<'HTML'
<p>Review-scope bepalen is de eerste stap van het <a href="/nl/blog/periodieke-access-reviews-proces">review-proces</a>. Fout hier = of te veel werk, of mist je iets belangrijks.</p>

<h2>Personen binnen scope</h2>
<ul>
  <li>Actieve medewerkers: altijd.</li>
  <li>Scheduled-in: nog niet begonnen, geen scope yet.</li>
  <li>Scheduled-out: wel in scope — laatste check voor vertrek.</li>
  <li>Inactief sinds de vorige review: één-keer-nog in scope om te verifiëren dat toegang echt is ingetrokken.</li>
  <li>Contractors en externe partijen: apart review-spoor met eigen cadans (vaak maandelijks).</li>
</ul>

<h2>Systemen binnen scope</h2>
<ul>
  <li>Alle tier-1 (kritiek): altijd.</li>
  <li>Tier-2: standaard elke review.</li>
  <li>Tier-3 (nice-to-have SaaS): half-jaarlijks, tenzij specifieke aanleiding.</li>
</ul>

<h2>Documenteer je scope-keuze</h2>
<p>Scope-beleid in je ISMS. Per review: scope-snapshot die zegt wat erin zat. Zo kan een auditor vergelijken tussen reviews en zien dat je consistent bent.</p>

<p>Zie ook: <a href="/nl/blog/steekproef-of-volledig-access-review">steekproef of volledig</a>, <a href="/nl/blog/saas-inventaris-opstellen">SaaS-inventaris</a>.</p>
HTML,
			],

			[
				'slug' => 'oude-medewerkers-in-access-review',
				'title' => 'Omgaan met "oude medewerkers" in je review — de cleanup-ronde',
				'excerpt' => 'Je eerste review vind je 8 accounts van mensen die al jaren weg zijn. Dat is geen probleem — dat is vooruitgang. Hoe ga je ermee om zonder dat het een blame-sessie wordt.',
				'tags' => ['access-review', 'cleanup', 'offboarding'],
				'published_offset_days' => 133,
				'body' => <<<'HTML'
<p>De eerste echte access review legt onvermijdelijk oude fouten bloot: ex-medewerkers met nog actieve accounts. Behandel dit als schoonmaak, niet als verwijt.</p>

<h2>Bucket de bevindingen</h2>
<ul>
  <li><strong>Echt inactief:</strong> geen login &gt; 180 dagen, persoon niet meer in HR. Direct disable + 30-dagen-regel.</li>
  <li><strong>Recent-vertrokken:</strong> &lt; 90 dagen weg, offboarding deels uitgevoerd. Complete waar gaps zijn.</li>
  <li><strong>Onduidelijk:</strong> persoon staat nog in HR maar heeft lang niet ingelogd. Check met manager.</li>
</ul>

<h2>Proces-patch tegelijk</h2>
<p>Elke ex-account die je vindt is een signaal dat je <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding-proces</a> iets heeft gemist. Gebruik de bevindingen om het proces te verbeteren — niet om de vorige verantwoordelijke aan de schandpaal te nagelen.</p>

<h2>Documenteer voor audit</h2>
<p>"We hebben bij deze review 12 orphaned accounts gevonden en geofboard" is een goed verhaal voor de auditor — het toont dat je review-proces werkt. Daar wordt je niet op afgerekend mits je aantoont hoe je herhaling voorkomt.</p>

<p>Zie ook: <a href="/nl/blog/verweesde-accounts-opsporen">verweesde accounts opsporen</a>, <a href="/nl/blog/periodieke-access-reviews-proces">review-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'global-admin-rol-review',
				'title' => 'De Global Admin-rol reviewen: de zwaarste categorie',
				'excerpt' => 'Als er één categorie is waar review-discipline cruciaal is, is het Global Admin en vergelijkbare rol-rollen. Hier de afzonderlijke procedure die hier bovenop de standaard review staat.',
				'tags' => ['access-review', 'global-admin', 'privileged-access'],
				'published_offset_days' => 140,
				'body' => <<<'HTML'
<p>Global Admin, AWS root, Salesforce system admin, GitHub org owner — deze rollen krijgen aparte behandeling in elke review. Geen bulk, geen steekproef, volle zichtbaarheid.</p>

<h2>Wat review je per Global Admin?</h2>
<ul>
  <li>Nog in functie?</li>
  <li>Daadwerkelijk gebruikt afgelopen kwartaal? (log-check)</li>
  <li>Voldoet aan <a href="/nl/blog/privileged-access-management">PAM-beleid</a> — dedicated admin account, hardware MFA, niet voor dagelijks werk?</li>
  <li>Is er een back-up persoon die dezelfde toegang kan overnemen? (minstens 2, niet 1)</li>
  <li>Wachtwoord sinds rol-toekenning geroteerd?</li>
</ul>

<h2>Wat als iemand het 90 dagen niet gebruikt?</h2>
<p>Red flag. Ofwel ze hebben het niet nodig — revoke. Ofwel ze doen admin-werk stiekem via hun normale user-account (policy-violation). Hoe dan ook: conversation nodig.</p>

<h2>Just-in-time als volwassen antwoord</h2>
<p>In Entra ID kun je PIM inzetten: Global Admin-rechten staan niet permanent aan, iemand activeert ze voor max. 8 uur met collega-approval. Elke activatie is bewijs. Zie <a href="/nl/blog/m365-entra-id-governance-mkb">M365 governance</a> voor de setup.</p>

<h2>Rapportage</h2>
<p>Aparte sectie in het review-rapport — "Privileged Access Review" — getekend door directie. Ook als er geen wijzigingen zijn.</p>

<p>Zie ook: <a href="/nl/blog/privileged-access-management">PAM-artikel</a>, <a href="/nl/blog/periodieke-access-reviews-proces">review-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'service-accounts-reviewen',
				'title' => 'Service accounts reviewen — de onzichtbare meerderheid',
				'excerpt' => 'Naast echte medewerkers heb je service accounts: API-integraties, scheduled jobs, automation. Vaak zijn dit er meer dan menselijke users. Wie eigent ze en hoe review je ze?',
				'tags' => ['service-accounts', 'automation', 'access-review'],
				'published_offset_days' => 148,
				'body' => <<<'HTML'
<p>Service accounts zijn non-menselijke identiteiten: de Zapier-integratie, de GitHub Actions-bot, de nightly backup-runner. Ze leven lang, hebben vaak brede rechten, en worden zelden gereviewd.</p>

<h2>Inventariseer ze</h2>
<ul>
  <li>Alle M365/Entra-app-registraties: wat doen ze, wie heeft ze gemaakt?</li>
  <li>GitHub deploy-keys en app-installations.</li>
  <li>API-users in CRM, boekhouding, customer-portal.</li>
  <li>Database-users buiten je gebruikers-tabel.</li>
  <li>CI/CD-credentials.</li>
</ul>

<h2>Per service account leg je vast</h2>
<ul>
  <li>Menselijke eigenaar (naam, bij vertrek overdracht).</li>
  <li>Doel / wat doet dit account.</li>
  <li>Scope / rechten.</li>
  <li>Credentials-opslag-locatie.</li>
  <li>Einddatum (liefst) of review-datum.</li>
</ul>

<h2>Review-cadans</h2>
<p>Minstens jaarlijks, liefst halfjaarlijks. Per item: nog nodig? Rechten nog minimaal? Credentials recent geroteerd?</p>

<h2>Bij eigenaar-vertrek</h2>
<p>Elk service account krijgt nieuwe menselijke eigenaar — anders sterft het in de offboarding zonder dat iemand merkt wat ermee samenhangt. Zie <a href="/nl/blog/vault-overdracht-bij-vertrek">vault-overdracht</a>.</p>
HTML,
			],
		];
	}
}
