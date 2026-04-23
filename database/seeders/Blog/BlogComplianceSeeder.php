<?php

namespace Database\Seeders\Blog;

use Illuminate\Database\Seeder;

class BlogComplianceSeeder extends Seeder
{
	public function run(): void
	{
		BlogSeedHelper::seedCluster(
			[
				'slug' => 'compliance',
				'name' => 'Compliance',
				'pillar_title' => 'ISO 27001 en NEN 7510 voor het MKB — zonder consultants',
				'intro' => 'Wat certificeringen écht van je vragen, hoe je je voorbereidt op een audit, en waarom 80% van de winst in de eerste 20% van het werk zit.',
				'sort_order' => 20,
			],
			self::posts(),
		);
	}

	private static function posts(): array
	{
		return [
			[
				'slug' => 'iso-27001-mkb-zonder-consultants',
				'title' => 'ISO 27001 voor het MKB zonder €50k aan consultants',
				'excerpt' => 'ISO 27001 is behapbaar als je de structuur snapt. Hier het minimale werk dat een 30-man MKB nodig heeft om door een Stage 2-audit te komen, wat het kost, en waar consultants wél waarde toevoegen.',
				'is_pillar' => true,
				'featured' => true,
				'tags' => ['iso-27001', 'compliance', 'audit', 'isms', 'mkb'],
				'published_offset_days' => 12,
				'body' => <<<'HTML'
<p>ISO 27001 heeft een prijslijst-reputatie van €30–80k aan consultancy. Voor een MKB van 15–60 medewerkers is dat overkill. De echte noodzaak is: een <strong>Information Security Management System</strong> (ISMS) dat past bij je grootte, bewijsbaar werkt, en je door de audit krijgt.</p>

<h2>Wat heb je écht nodig?</h2>
<ol>
  <li>Een <strong>security policy</strong> (±10 pagina's). Niet 80.</li>
  <li>Een <strong>risk register</strong> dat realistisch is voor jouw bedrijf.</li>
  <li>Een set <strong>geïmplementeerde controls</strong> uit Annex A — niet allemaal, wel de relevante.</li>
  <li>Bewijs dat je de controls uitvoert: logs, reviews, incident-registraties.</li>
  <li>Een jaarlijkse <strong>management review</strong>.</li>
  <li>Interne audits (1× per jaar, zelf of extern).</li>
</ol>

<h2>De Annex A-subset die voor MKB matter</h2>
<p>Annex A heeft 93 controls. Voor een typisch MKB zijn er 30-40 relevant. De anderen beargumenteer je als "Not Applicable" in je Statement of Applicability. De kerngebieden:</p>
<ul>
  <li><a href="/nl/blog/iso-27001-annex-a9-toegangsbeheer">A.9 Access Control</a> — grootste categorie, grootste risico.</li>
  <li>A.6 Organisation of Information Security — rol- en verantwoordelijkheidsmatrix.</li>
  <li><a href="/nl/blog/incidentenlog-opzetten">A.16 Incident Management</a> — logboek + procedure.</li>
  <li>A.12 Operations Security — backups, malware, monitoring.</li>
  <li>A.13 Communications Security — netwerk-segmentatie, encryptie-in-transit.</li>
  <li>A.18 Compliance — AVG, DPIA, contractuele vereisten.</li>
</ul>

<h2>Certificeringskosten — realistisch</h2>
<p>Voor 30-mans MKB bij een Nederlandse certificerende instelling (Kiwa, DEKRA, LRQA):</p>
<ul>
  <li>Stage 1 + Stage 2 audit: €4.500–8.000</li>
  <li>Jaarlijkse surveillance audits (jaar 2 en 3): €2.000–3.500</li>
  <li>Herijking elk jaar 3: vergelijkbaar met Stage 2</li>
</ul>
<p>Zie <a href="/nl/blog/iso-27001-certificeringskosten">volledige kostenoverzicht</a>.</p>

<h2>Tijdlijn: realistisch</h2>
<p>Van nul naar certificaat: 5-8 maanden. Met bestaande security-basis: 3-5 maanden.</p>
<ul>
  <li>Maand 1: gap-analyse + scope</li>
  <li>Maand 2-3: policy + risk register + SoA</li>
  <li>Maand 3-5: controls implementeren en evidence verzamelen</li>
  <li>Maand 5: interne audit + management review</li>
  <li>Maand 6: Stage 1 (documenten)</li>
  <li>Maand 7-8: Stage 2 (implementatie)</li>
</ul>

<h2>Wanneer is een consultant wél de moeite waard?</h2>
<p>Voor de gap-analyse en de eerste policy-structuur (2-5 dagen inzet). Niet voor dagelijkse uitvoering — dat moet intern.</p>

<p>Zie ook: <a href="/nl/blog/iso-27001-pre-audit-checklist">pre-audit checklist</a>, <a href="/nl/blog/isms-voor-mkb">wat is een ISMS?</a>, en <a href="/nl/blog/iso-27001-vs-soc-2">ISO vs SOC 2</a> voor internationale klanten.</p>
HTML,
			],

			[
				'slug' => 'iso-27001-annex-a9-toegangsbeheer',
				'title' => 'ISO 27001 Annex A.9: wat de auditor écht wil zien',
				'excerpt' => 'Annex A.9 — Access Control — is de zwaarste van de 14 secties. Hier een concrete uitleg per sub-control: A.9.1 t/m A.9.4, met wat in het MKB werkt als bewijs.',
				'tags' => ['iso-27001', 'annex-a9', 'audit', 'toegangsbeheer'],
				'published_offset_days' => 22,
				'body' => <<<'HTML'
<p>Annex A.9 is wat Access Control heet in ISO 27001. Het bevat 14 sub-controls verdeeld over 4 doelen. Voor het MKB zijn niet alle 14 even zwaar — maar je Statement of Applicability moet per stuk zeggen "implemented" of "not applicable met reden".</p>

<h2>A.9.1 Business requirements for access control</h2>
<p>Je moet een <em>access control policy</em> hebben. Eén document dat zegt: hoe kennen we toegang toe, wie keurt goed, hoe reviewen we. 2–4 pagina's volstaat.</p>
<p>Bewijs: de policy zelf + changelog die laat zien dat hij wordt bijgehouden.</p>

<h2>A.9.2 User access management</h2>
<ul>
  <li><strong>A.9.2.1 User registration:</strong> elke user-creatie heeft een aanvraag en goedkeuring. Bewijs: logs of tickets.</li>
  <li><strong>A.9.2.2 Privilege management:</strong> <a href="/nl/blog/privileged-access-management">privileged access</a> is apart geregistreerd.</li>
  <li><strong>A.9.2.3 Secret authentication information:</strong> hoe geef je tijdelijke wachtwoorden? Hoe reset je MFA?</li>
  <li><strong>A.9.2.5 Review of user access rights:</strong> <a href="/nl/blog/periodieke-access-reviews-proces">periodieke access reviews</a>. Hier komt meestal het meeste audit-vuur naartoe.</li>
  <li><strong>A.9.2.6 Removal of access rights:</strong> <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding</a> met bewijsbare verwijderingstermijn.</li>
</ul>

<h2>A.9.3 User responsibilities</h2>
<p>Bewustwording: medewerkers weten dat ze verantwoordelijk zijn voor hun credentials. Bewijs: onboarding-training-log, acceptable use policy met handtekening.</p>

<h2>A.9.4 System and application access control</h2>
<ul>
  <li><strong>A.9.4.1 Information access restriction:</strong> need-to-know, zie <a href="/nl/blog/least-privilege-beginsel">least privilege</a>.</li>
  <li><strong>A.9.4.2 Secure log-on procedures:</strong> MFA waar mogelijk, logging van login-pogingen.</li>
  <li><strong>A.9.4.3 Password management system:</strong> wachtwoord-complexiteit, rotatie (of niet-rotatie, mits MFA).</li>
  <li><strong>A.9.4.5 Access control to program source code:</strong> wie kan wijzigingen aan productiecode maken?</li>
</ul>

<h2>Wat auditors het vaakst afwijzen</h2>
<ul>
  <li>"Wij doen reviews" zonder bewijsrapport per cyclus — zo weet de auditor niet of het waar is.</li>
  <li>Privileged access inventaris niet actueel — "Global Admin is X" maar X is 4 maanden geleden vertrokken.</li>
  <li>Offboarding-bewijs incompleet — wel disable, niet licentie opgezegd.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/iso-27001-pre-audit-checklist">pre-audit checklist</a> voor de volledige lijst waar auditors op letten.</p>
HTML,
			],

			[
				'slug' => 'isms-voor-mkb',
				'title' => 'Wat is een ISMS en waar begin je?',
				'excerpt' => 'Information Security Management System — het klinkt groter dan het is. Voor een MKB is het een set documenten en routines, geen platform dat je ergens op installeert.',
				'tags' => ['isms', 'iso-27001', 'governance'],
				'published_offset_days' => 32,
				'body' => <<<'HTML'
<p>ISMS = Information Security Management System. In essentie: je manier van werken aan informatiebeveiliging, beschreven en onderhouden. Het is geen software. Het is hoe je denkt en doet.</p>

<h2>De vijf bouwstenen</h2>
<ol>
  <li><strong>Scope.</strong> Wat valt er onder je ISMS? Hele bedrijf, specifieke business-unit, alleen bepaalde data? Dit staat vast vóór je iets anders doet.</li>
  <li><strong>Policy-laag.</strong> 8-12 documenten (security policy, access control, incident response, acceptable use, …). Totaal ~40-80 pagina's.</li>
  <li><strong>Risk management.</strong> Risk register met risico's, eigenaar, maatregel, restrisico. Zie <a href="/nl/blog/risicomanagement-iso-27001">risicomanagement</a>.</li>
  <li><strong>Controls.</strong> De Annex A-subset die je implementeert. Statement of Applicability (SoA) is het overzicht.</li>
  <li><strong>PDCA-cyclus.</strong> Plan, Do, Check, Act. Klinkt dramatisch, is: reviews, audits, management-review, lessons learned.</li>
</ol>

<h2>Waar begin je?</h2>
<ol>
  <li>Scope vastleggen (1 dag).</li>
  <li>Gap-analyse tegen Annex A (1-3 dagen).</li>
  <li>Risk register, eerste versie (2 dagen).</li>
  <li>Access control policy + <a href="/nl/blog/iso-27001-annex-a9-toegangsbeheer">A.9</a> (1 week werkend naar evidence).</li>
  <li>Rest van de policies (2-4 weken, parallel met evidence verzamelen).</li>
</ol>

<h2>De valkuil: perfectie</h2>
<p>Je ISMS hoeft niet perfect te zijn. Hij moet <em>werken</em> en bewijsbaar zijn. Versie 1 van je policies mag 80% zijn. Verbetering is onderdeel van de PDCA-cyclus — het hoort erbij dat je ze elk jaar aanpast.</p>

<p>Zie ook: <a href="/nl/blog/iso-27001-mkb-zonder-consultants">ISO 27001-pillar</a>, <a href="/nl/blog/pdca-cyclus-iso">PDCA-cyclus uitgelegd</a>.</p>
HTML,
			],

			[
				'slug' => 'risicomanagement-iso-27001',
				'title' => 'Een ISO-risk-register dat werkt (en er niet uitziet als een consultant-export)',
				'excerpt' => 'Een risk register hoeft geen 300-regelige spreadsheet te zijn. Voor een MKB zijn 30-60 risico\'s realistisch. Hier het format dat een audit overleeft én dagelijks bruikbaar is.',
				'tags' => ['risk-register', 'iso-27001', 'risicomanagement'],
				'published_offset_days' => 40,
				'body' => <<<'HTML'
<p>Het risk register is de centrale lijst van "wat kan er misgaan". Consultants leveren soms 300-regelige sheets met theoretische risico's. Voor het MKB is 30-60 realistisch. Elk risico moet concreet kunnen worden uitgelegd aan een niet-technische CFO.</p>

<h2>Per risico leg je vast</h2>
<ul>
  <li>Korte omschrijving ("verlies van laptop met klant-data")</li>
  <li>Welk asset of proces raakt het</li>
  <li>Dreiging (diefstal) en kwetsbaarheid (geen disk-encryption)</li>
  <li>Kans (1-5) × impact (1-5) = score</li>
  <li>Huidige maatregelen</li>
  <li>Eigenaar (naam)</li>
  <li>Restrisico-score</li>
  <li>Verdere actie (optioneel) + deadline</li>
</ul>

<h2>Realistische scoring</h2>
<p>Kans: 1 = zeer zeldzaam, 3 = kan eens per jaar, 5 = dagelijkse realiteit. Impact: 1 = ongemak, 3 = dagwerk verlies, 5 = bedrijf in gevaar. Een score 15+ is actie-vereist.</p>

<h2>Review-cadans</h2>
<ul>
  <li>Elk kwartaal: lopend nalopen, nieuwe risico's toevoegen.</li>
  <li>Jaarlijks: volledige review in de <a href="/nl/blog/management-review-iso">management review</a>.</li>
  <li>Na elk incident: risk-register bijwerken met de lesson learned.</li>
</ul>

<h2>Voorbeelden van realistische MKB-risico's</h2>
<ol>
  <li>Ransomware op een medewerker-laptop via phishing-mail</li>
  <li>Ex-medewerker heeft nog toegang tot klant-data (<a href="/nl/blog/waterdichte-offboarding-stappen">offboarding-gap</a>)</li>
  <li>Boekhoud-admin wordt maandenlang niet gereviewd (<a href="/nl/blog/periodieke-access-reviews-proces">review-lag</a>)</li>
  <li>Cloud-provider valt uit tijdens hoogseizoen</li>
  <li>Medewerker klikt op phishing-mail en MFA-fatigue-attack slaagt</li>
  <li>Gedeeld wachtwoord lekt via Slack-paste</li>
</ol>

<p>Zie voor incident-registratie: <a href="/nl/blog/incidentenlog-opzetten">incidentenlog opzetten</a>.</p>
HTML,
			],

			[
				'slug' => 'iso-27001-pre-audit-checklist',
				'title' => 'ISO 27001 pre-audit checklist: 2 weken voor Stage 2',
				'excerpt' => 'Stage 2 is over twee weken. Deze 22-punts checklist loopt alles na dat auditors typisch vragen — als één hokje mist, fix het nu.',
				'tags' => ['iso-27001', 'audit', 'checklist'],
				'published_offset_days' => 48,
				'body' => <<<'HTML'
<p>Twee weken voor Stage 2. Tijd om niet meer wezenlijk dingen te bouwen — wel om checken dat alles klopt. Loop deze 22 punten af.</p>

<h2>Documentatie</h2>
<ol>
  <li>Security policy versie-actueel, eigenaar benoemd, jaarlijks review-datum ingepland.</li>
  <li>Statement of Applicability actueel (alle Annex A-controls: implemented / NA + reden).</li>
  <li>Alle 8-12 policies op correct versienummer, changelog zichtbaar.</li>
  <li>Risk register review-date in het laatste kwartaal.</li>
  <li>Asset-lijst actueel (inclusief <a href="/nl/blog/saas-inventaris-opstellen">SaaS-inventaris</a>).</li>
</ol>

<h2>Operationele bewijsvoering</h2>
<ol start="6">
  <li>Laatste <a href="/nl/blog/periodieke-access-reviews-proces">access review</a> uitgevoerd, PDF-rapport met beslissingen en handtekeningen.</li>
  <li><a href="/nl/blog/privileged-access-management">Privileged access</a> inventaris actueel, laatste review binnen 3 maanden.</li>
  <li>Laatste 2 offboardings volledig gedocumenteerd.</li>
  <li>Onboarding-proces voor minstens 1 recente hire compleet.</li>
  <li>Backup-test uitgevoerd, bewijs van restore.</li>
  <li>Laatste kwartaal aan incident-register (ook "niks gebeurd" moet daarin).</li>
</ol>

<h2>Governance</h2>
<ol start="12">
  <li>Laatste <a href="/nl/blog/management-review-iso">management review</a> binnen 12 maanden, notulen aanwezig.</li>
  <li>Interne audit afgerond, report aanwezig, bevindingen afgesloten of in plan.</li>
  <li>Trainings-log: iedereen heeft security-awareness dit jaar gedaan.</li>
  <li>Rollen en verantwoordelijkheden document (CISO-rol ingevuld, ook al is dat de directeur zelf).</li>
</ol>

<h2>Techniek</h2>
<ol start="16">
  <li>MFA afdwingbaar op kritieke systemen (M365 admin, boekhouding, cloud-infra).</li>
  <li>Wachtwoord-policy afdwingbaar (minstens lengte + common-password-blocking).</li>
  <li>Patches: laatste kwartaal OS/browser-patches op &gt; 95% devices.</li>
  <li>Anti-malware op alle endpoints.</li>
  <li>Disk encryption aan op alle bedrijfs-laptops.</li>
</ol>

<h2>Contracten</h2>
<ol start="21">
  <li>Verwerkers-overeenkomsten met kern-leveranciers (zie <a href="/nl/blog/verwerkersregister-avg">verwerkersregister</a>).</li>
  <li>Geheimhoudings- en security-clausules in arbeidscontracten.</li>
</ol>

<p>Alles afgevinkt → je bent klaar voor Stage 2.</p>
HTML,
			],

			[
				'slug' => 'incidentenlog-opzetten',
				'title' => 'Een incidentenlog opzetten dat auditors geloven',
				'excerpt' => 'Een lege incident-log is een rode vlag voor auditors. Het betekent niet dat er geen incidenten zijn — het betekent dat je ze niet logt. Hier hoe je een werkbaar log opzet.',
				'tags' => ['incident-management', 'iso-27001', 'governance'],
				'published_offset_days' => 55,
				'body' => <<<'HTML'
<p>"Wij hebben geen incidenten gehad het afgelopen jaar" is een zin die auditors met een opgetrokken wenkbrauw aanhoren. Vrijwel elke organisatie heeft incidenten — variërend van een phishing-mail die ontdekt werd tot een gedeeld wachtwoord dat per ongeluk in Slack werd geplakt. Ze te loggen betekent dat je ermee bezig bent.</p>

<h2>Wat is een incident?</h2>
<p>Elke gebeurtenis die de vertrouwelijkheid, integriteit of beschikbaarheid van bedrijfsinformatie (potentieel) schaadt. Incidenten zijn niet hetzelfde als datalekken — veel incidenten blijven klein en worden snel opgelost, maar ze verdienen registratie.</p>

<h2>Wat leg je per incident vast?</h2>
<ul>
  <li>Datum + tijd van detectie</li>
  <li>Korte beschrijving ("phishing-mail afkomstig van ogenschijnlijke CEO")</li>
  <li>Categorie (phishing, verloren device, malware, onbedoelde openbaring, datalek, overig)</li>
  <li>Ernst (low/medium/high)</li>
  <li>Wie heeft het gemeld</li>
  <li>Wie heeft het opgepakt</li>
  <li>Status (open, in behandeling, gesloten)</li>
  <li>Wat is gedaan</li>
  <li>Root cause + lesson learned (bij sluiten)</li>
  <li>Datalek-melding AVG nodig? Ja/Nee + motivatie</li>
</ul>

<h2>Waar log je het?</h2>
<p>Een Airtable, Notion-database, of aparte sheet is prima als het maar gestructureerd is. Voor 50+ incidenten per jaar loont een dedicated tool.</p>

<h2>Koppeling met risk register</h2>
<p>Elk hoog-impact-incident hoort terug te komen in het <a href="/nl/blog/risicomanagement-iso-27001">risk register</a>: verhoog de kans-score, update de maatregel, verlaag het restrisico nadat je iets hebt gedaan.</p>

<h2>Datalek-melding (AVG)</h2>
<p>Binnen 72 uur na ontdekking moet een datalek gemeld bij de AP. Zie <a href="/nl/blog/datalek-melden-avg">AVG datalek-melding</a>. Je incidenten-log is het primaire bewijs dat je dit tijdig deed.</p>
HTML,
			],

			[
				'slug' => 'pdca-cyclus-iso',
				'title' => 'De PDCA-cyclus uitgelegd voor bestuurders',
				'excerpt' => 'Plan-Do-Check-Act klinkt bureaucratisch. In de praktijk is het: schrijf op wat je doet, doe het, kijk of het werkt, pas het aan. Hier de kortste bruikbare uitleg.',
				'tags' => ['pdca', 'iso-27001', 'governance', 'management'],
				'published_offset_days' => 62,
				'body' => <<<'HTML'
<p>PDCA is de rode draad door elk ISMS. ISO 27001 verwacht dat je deze cyclus hanteert. In de praktijk gebeurt dit op drie niveaus: strategisch (jaar), tactisch (kwartaal), operationeel (continu).</p>

<h2>Strategisch: jaarlijks</h2>
<ul>
  <li><strong>Plan:</strong> jaarplan met 2-5 security-doelen.</li>
  <li><strong>Do:</strong> uitvoering in het jaar.</li>
  <li><strong>Check:</strong> interne audit + <a href="/nl/blog/management-review-iso">management review</a>.</li>
  <li><strong>Act:</strong> volgend jaarplan neemt bevindingen mee.</li>
</ul>

<h2>Tactisch: kwartaalritme</h2>
<ul>
  <li>Planning: welke controls vragen deze periode aandacht?</li>
  <li>Uitvoering: <a href="/nl/blog/periodieke-access-reviews-proces">access reviews</a>, backups-test, policy-updates.</li>
  <li>Check: bevindingen verzamelen, risk-register bijwerken.</li>
  <li>Act: corrigerende maatregelen plannen.</li>
</ul>

<h2>Operationeel: continu</h2>
<p>Incidenten loggen, phishing-tests uitvoeren, vulnerability-scans, patches. Elke week is er wel iets.</p>

<h2>Eén gemeenschappelijke regel</h2>
<p>Leg alle cycli (jaar, kwartaal, week) vast in dezelfde kalender/tool. Een ISMS dat in hoofden leeft is een ISMS dat door de eerste auditor-drukte sneuvelt.</p>

<p>Zie ook <a href="/nl/blog/isms-voor-mkb">wat is een ISMS</a>.</p>
HTML,
			],

			[
				'slug' => 'management-review-iso',
				'title' => 'De management review: wat moet erin en wie doet mee?',
				'excerpt' => 'Eén van de sectie-9-eisen van ISO 27001. Jaarlijks, met de directie, 2 uur. Hier de agenda die een auditor accepteert én die voor jou als oefening bruikbaar is.',
				'tags' => ['management-review', 'iso-27001', 'governance'],
				'published_offset_days' => 70,
				'body' => <<<'HTML'
<p>De management review is geen ceremonie — het is de enige verplichte moment per jaar waar de directie zich expliciet committeert aan het ISMS. 2 uur, één keer per jaar.</p>

<h2>Wie is erbij?</h2>
<ul>
  <li>Directie (eindverantwoordelijk voor ISMS)</li>
  <li>CISO / security-verantwoordelijke (die kan ook de directeur zelf zijn in een MKB)</li>
  <li>Data protection officer (indien relevant voor AVG)</li>
  <li>Compliance-officer (indien van toepassing)</li>
</ul>

<h2>Agenda (vast)</h2>
<ol>
  <li>Terugblik op vorige review — wat was besloten, wat is uitgevoerd?</li>
  <li>Wijzigingen in externe context (wetgeving, klantvereisten, dreigingslandschap).</li>
  <li>Wijzigingen in interne context (reorganisatie, nieuwe systemen, groei).</li>
  <li>Risk management — status <a href="/nl/blog/risicomanagement-iso-27001">risk register</a>, top-5 restrisico's.</li>
  <li>Resultaten interne audits en externe audits (Stage-audits, surveillance).</li>
  <li>Incidenten-overzicht — aantal, categorieën, lessen.</li>
  <li>Status controls en <a href="/nl/blog/periodieke-access-reviews-proces">access reviews</a>.</li>
  <li>Beoordeling objective — zijn security-doelen gehaald?</li>
  <li>Resources — is het ISMS voldoende bemenst en gefinancierd?</li>
  <li>Verbeteringsinitiatieven voor komend jaar.</li>
  <li>Actie- en besluitenlijst.</li>
</ol>

<h2>Bewijs</h2>
<p>Notulen met duidelijke besluiten, namen, data en acties. Getekend of digitaal bevestigd door de directie. Dit is de kernvindplaats die elke auditor opvraagt.</p>

<p>Zie ook: <a href="/nl/blog/pdca-cyclus-iso">PDCA-cyclus</a>, <a href="/nl/blog/iso-27001-pre-audit-checklist">pre-audit checklist</a>.</p>
HTML,
			],

			[
				'slug' => 'nen-7510-zorg-mkb',
				'title' => 'NEN 7510 voor zorgondernemers: extra bovenop ISO 27001',
				'excerpt' => 'Werk je in of met de zorg? Dan is NEN 7510 naast (of in plaats van) ISO 27001 realiteit. De overlap is groot, de verschillen zitten in patiëntgegevens en specifieke Annex-controls.',
				'tags' => ['nen-7510', 'zorg', 'iso-27001', 'compliance'],
				'published_offset_days' => 78,
				'body' => <<<'HTML'
<p>NEN 7510 is de Nederlandse norm voor informatiebeveiliging in de zorg. Als je klant-data verwerkt die herleidbaar is tot patiënten — EPD-ontwikkelaars, zorg-SaaS, consultancy die met zorgaanbieders werkt — is certificering vaak contractueel verplicht.</p>

<h2>Relatie met ISO 27001</h2>
<p>NEN 7510 is gebouwd op ISO 27001 + ISO 27002 maar met extra eisen specifiek voor patiëntgegevens. Als je al ISO hebt, is NEN 7510 een delta. Als je nieuw begint, kun je gelijk beide doen bij één audit.</p>

<h2>Wat zijn de extra's?</h2>
<ul>
  <li>Classificatie van patiëntgegevens apart.</li>
  <li>Strengere logging van toegang tot patiëntdata — wie keek wanneer naar welk dossier.</li>
  <li>Specifieke verwerkers-overeenkomsten richting zorgaanbieders.</li>
  <li>Retentie- en vernietigingsbeleid voor patiëntgegevens (langer bewaren dan AVG-basis).</li>
  <li>Incident-escalatie met meldplicht richting zorginspectie in sommige gevallen.</li>
</ul>

<h2>Audits</h2>
<p>Veel zorg-leveranciers kiezen een auditor die zowel ISO 27001 als NEN 7510 kan certificeren (Kiwa, DEKRA, Brand Compliance). Gezamenlijke audit, één traject, lagere kosten dan twee separaat.</p>

<p>Zie ook: <a href="/nl/blog/iso-27001-mkb-zonder-consultants">ISO 27001-pillar</a>, <a href="/nl/blog/iso-27001-certificeringskosten">certificeringskosten</a>.</p>
HTML,
			],

			[
				'slug' => 'iso-27001-vs-soc-2',
				'title' => 'ISO 27001 of SOC 2? Welke past bij jouw Nederlandse MKB?',
				'excerpt' => 'ISO 27001 is Europees-geörienteerd, SOC 2 Amerikaans. Welke hebben je klanten nodig? En kun je ze combineren? Hier het praktijk-verschil voor een MKB.',
				'tags' => ['iso-27001', 'soc-2', 'compliance', 'internationaal'],
				'published_offset_days' => 85,
				'body' => <<<'HTML'
<p>De korte versie: heb je EU-klanten? ISO 27001. Heb je Amerikaanse klanten? SOC 2. Heb je beide? Overweeg ISO 27001 + SOC 2 Type II — ze overlappen voor 70-80%.</p>

<h2>Verschillen in filosofie</h2>
<ul>
  <li><strong>ISO 27001:</strong> certificaat, drie jaar geldig, jaarlijks surveillance-audit. Toont dat je ISMS werkt.</li>
  <li><strong>SOC 2:</strong> rapport, jaarlijks of half-jaarlijks. Toont dat je controls werkten over een specifieke periode.</li>
</ul>

<h2>Trust Service Criteria (SOC 2)</h2>
<p>SOC 2 heeft 5 criteria: Security (altijd), Availability, Confidentiality, Processing Integrity, Privacy. Je kiest welke je wil toetsen. Meest MKB: Security + Confidentiality.</p>

<h2>Welke kopen klanten?</h2>
<ul>
  <li>US tech-klanten: vragen bijna altijd om SOC 2 Type II.</li>
  <li>EU enterprise: ISO 27001 is de standaard-vraag.</li>
  <li>EU overheid: ISO 27001 + soms BIO.</li>
  <li>Financial services: beide + specifieke sector-eisen.</li>
</ul>

<h2>Combineren</h2>
<p>Veel MKB's die internationaal groeien doen eerst ISO 27001 (makkelijker om met één certificaat te "bouwen") en daarna SOC 2 op de bestaande control-set. Overlap &gt; 70%, dubbele werk klein.</p>

<p>Zie ook: <a href="/nl/blog/iso-27001-mkb-zonder-consultants">ISO 27001-pillar</a>, <a href="/nl/blog/iso-27001-certificeringskosten">certificeringskosten</a>.</p>
HTML,
			],

			[
				'slug' => 'iso-27001-certificeringskosten',
				'title' => 'ISO 27001 kosten: van eerste gap-analyse tot certificaat',
				'excerpt' => 'Realistische budget-plaatje voor een 30-mans MKB. Interne uren, externe audit, consultancy (zo min mogelijk), jaarlijkse onderhoud. Geen marketingpraatjes.',
				'tags' => ['iso-27001', 'kosten', 'budget'],
				'published_offset_days' => 92,
				'body' => <<<'HTML'
<p>Budget voor een 30-mans MKB, eerste certificering, 2026-niveau.</p>

<h2>Eenmalig</h2>
<ul>
  <li>Consultant gap-analyse (optioneel): €2.500 - €6.000</li>
  <li>Policy-templates (opensource of kopen): €0 - €1.500</li>
  <li>Intern werk: 150-300 uur verdeeld over 6 maanden</li>
  <li>Stage 1 + Stage 2 audit: €4.500 - €8.000 (afhankelijk van grootte en complexiteit)</li>
  <li>Eventuele nonconformity-remediation: €1.000 - €3.000</li>
</ul>

<h2>Jaarlijks</h2>
<ul>
  <li>Surveillance audit (jaar 2, 3): €2.000 - €3.500 per jaar</li>
  <li>Onderhoud ISMS (intern): 10-30 uur per maand</li>
  <li>Interne audit (extern uitbesteed of intern): €1.500 - €3.500</li>
  <li>Security-trainingen voor medewerkers: €500 - €2.000</li>
</ul>

<h2>Elke 3 jaar: re-certificering</h2>
<p>Vergelijkbaar met initiële Stage 2: €4.000 - €6.500.</p>

<h2>Totale kostenberekening over 3 jaar</h2>
<p>Voor een 30-mans MKB: €18.000 - €35.000 externe kosten + ±400 intern-uren. Verdeeld over 3 jaar.</p>

<h2>Waar kun je geld besparen?</h2>
<ul>
  <li>Geen €80k consultant — gebruik die alleen voor gap-analyse en eerste policy-set.</li>
  <li>Opensource policy-templates (bijv. van IASME of ENISA).</li>
  <li>Software die evidence automatisch verzamelt — bv. <a href="/nl/tools/accessguard">AccessGuard</a> voor access-reviews en audit-trail.</li>
  <li>Intern doen wat intern kan. Auditor wil bewijs, geen uitbestede-documentatie.</li>
</ul>
HTML,
			],

			[
				'slug' => 'dora-mkb-financiele-sector',
				'title' => 'DORA voor MKB-leveranciers aan financiële instellingen',
				'excerpt' => 'Vanaf januari 2025 verwacht elke bank, verzekeraar of beleggingsfonds DORA-compatibel te zijn. Ben je een MKB-leverancier? Dan krijg je het via hun contracten bij je.',
				'tags' => ['dora', 'financiele-sector', 'compliance', 'europa'],
				'published_offset_days' => 100,
				'body' => <<<'HTML'
<p>DORA (Digital Operational Resilience Act) is een EU-verordening die banken, verzekeraars, beleggingsfondsen en betaaldienstverleners verplicht tot striktere IT-resilience. Als je als MKB-software-leverancier aan die sector levert, krijg je de eisen via contracten bij je.</p>

<h2>Wat betekent het concreet voor leveranciers?</h2>
<ul>
  <li>Contractuele security-requirements worden strikter. Klanten in de sector zullen ISO 27001 of SOC 2 Type II vragen, plus aanvullende clausules.</li>
  <li>Incident-meldplicht: je moet zelf incidenten binnen vaste termijnen aan je financiële klanten melden.</li>
  <li>Risicomanagement richting third-party suppliers — inclusief jou als leverancier — moet expliciet.</li>
  <li>Testregime: je klanten kunnen verwachten dat ze je mogen pen-testen of resilience-testen uitvoeren.</li>
</ul>

<h2>Actie</h2>
<ol>
  <li>ISO 27001 als basis (<a href="/nl/blog/iso-27001-mkb-zonder-consultants">pillar</a>).</li>
  <li>Incident-meldprocedure die onderscheid maakt tussen algemene meldplicht en sector-specifieke DORA-meldplicht.</li>
  <li>Subcontractor-register — wie van jouw leveranciers raakt jouw financiële klanten indirect.</li>
  <li>Herziening contract-templates voor financiële klanten.</li>
</ol>

<p>Raakvlak met <a href="/nl/blog/iso-27001-vs-soc-2">ISO vs SOC 2</a> en <a href="/nl/blog/verwerkersregister-avg">verwerkersregister</a>.</p>
HTML,
			],

			[
				'slug' => 'nis2-mkb-kritische-sector',
				'title' => 'NIS2 en het MKB: wanneer val je onder de richtlijn?',
				'excerpt' => 'NIS2 is de opvolger van NIS1 en trekt de scope flink open. Veel MKB-bedrijven in "gewone" sectoren vallen nu ineens onder essential of important entities.',
				'tags' => ['nis2', 'compliance', 'cybersecurity', 'europa'],
				'published_offset_days' => 108,
				'body' => <<<'HTML'
<p>NIS2 (Network and Information Security Directive 2) is van kracht sinds oktober 2024. Nederland heeft de implementatie-wet in 2025 afgerond. Voor MKB-bedrijven is de hoofdvraag: val ik eronder?</p>

<h2>De scope in 2 lagen</h2>
<ul>
  <li><strong>Essential entities:</strong> sectoren als energie, water, banken, zorg, digitale infrastructuur. Meestal organisaties &gt; 250 medewerkers of &gt; €50M omzet.</li>
  <li><strong>Important entities:</strong> breder — post, chemie, voedselsector, maakindustrie, ICT-diensten, onderzoek, digitale providers. Vanaf &gt; 50 medewerkers of &gt; €10M omzet.</li>
</ul>

<h2>Wat moet je concreet?</h2>
<ul>
  <li>Risicoanalyse en ISMS (overlap met ISO 27001).</li>
  <li>Incident-meldplicht: early warning &lt; 24u, incident report &lt; 72u, eindrapport &lt; 1 maand.</li>
  <li>Supply-chain security — je leveranciers moeten ook adequaat beveiligd zijn.</li>
  <li>Training van personeel, in het bijzonder directie.</li>
  <li>Verplichte cyberhygiëne-maatregelen (MFA, backups, patches, segmentatie).</li>
</ul>

<h2>Handhaving</h2>
<p>Boetes tot 2% van wereldwijde omzet of €10M (afhankelijk van wat hoger is). Bestuurders-aansprakelijkheid is nadrukkelijk onderdeel.</p>

<h2>Waar begin je?</h2>
<p>Als je al <a href="/nl/blog/iso-27001-mkb-zonder-consultants">ISO 27001</a> hebt, zit je voor 70-80% goed. De delta zit vooral in incident-meldtermijnen en supply-chain security. Zonder ISO is ISO-certificering of een gedocumenteerde ISMS de aan te raden basis.</p>
HTML,
			],
		];
	}
}
