<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
<meta charset="UTF-8">
<title>AccessGuard {{ $isEn ? 'manual' : 'handleiding' }}</title>
<style>
	/* dompdf-friendly CSS, matches the /accessguard landing palette */
	@page { margin: 24mm 20mm; }
	body {
		font-family: DejaVu Sans, sans-serif;
		font-size: 10pt;
		color: #0f172a;
		line-height: 1.55;
		margin: 0;
	}

	/* ---------- Cover ---------- */
	.cover {
		padding: 80mm 0 0 0;
		height: 250mm;
		page-break-after: always;
		position: relative;
	}
	.cover-kicker {
		font-size: 10pt;
		font-weight: bold;
		letter-spacing: 3px;
		color: #ff7a18;
		text-transform: uppercase;
	}
	.cover h1 {
		font-size: 40pt;
		font-weight: bold;
		line-height: 1.05;
		margin: 12mm 0 8mm;
		letter-spacing: -0.02em;
		color: #0f172a;
	}
	.cover h1 .accent { color: #ff7a18; }
	.cover-lead {
		font-size: 13pt;
		color: rgba(15,23,42,.72);
		margin: 0 0 40mm 0;
		max-width: 140mm;
		line-height: 1.5;
	}
	.cover-meta {
		position: absolute;
		bottom: 10mm;
		left: 0;
		right: 0;
		font-size: 9pt;
		color: rgba(15,23,42,.55);
		border-top: 1px solid rgba(15,23,42,.12);
		padding-top: 8mm;
	}
	.cover-meta-grid { width: 100%; }
	.cover-meta-grid td { padding: 0; font-size: 9pt; vertical-align: top; }
	.cover-meta-label { font-weight: bold; color: rgba(15,23,42,.70); text-transform: uppercase; letter-spacing: 1px; font-size: 8pt; }

	/* ---------- Chapter headers ---------- */
	h2.chapter {
		font-size: 22pt;
		font-weight: bold;
		letter-spacing: -0.02em;
		margin: 0 0 4mm 0;
		color: #0f172a;
		page-break-before: always;
	}
	h2.chapter .num {
		display: inline-block;
		color: #ff7a18;
		font-weight: bold;
		margin-right: 6mm;
	}
	.chapter-intro {
		color: rgba(15,23,42,.72);
		font-size: 10.5pt;
		margin: 0 0 8mm 0;
		line-height: 1.55;
		padding-bottom: 6mm;
		border-bottom: 1px solid rgba(15,23,42,.10);
	}

	h3 {
		font-size: 13pt;
		font-weight: bold;
		color: #0f172a;
		margin: 8mm 0 3mm 0;
	}
	h4 {
		font-size: 11pt;
		font-weight: bold;
		color: #0f172a;
		margin: 6mm 0 2mm 0;
	}

	p { margin: 0 0 3mm 0; }
	ul, ol { margin: 2mm 0 4mm 0; padding-left: 7mm; }
	li { margin: 0 0 2mm 0; line-height: 1.5; }

	.muted { color: rgba(15,23,42,.62); }
	.small { font-size: 9pt; }

	/* ---------- TOC ---------- */
	.toc h2 {
		font-size: 20pt;
		font-weight: bold;
		margin: 0 0 8mm 0;
		letter-spacing: -0.02em;
	}
	.toc-list { list-style: none; padding: 0; margin: 0; }
	.toc-list li {
		padding: 3mm 0;
		border-bottom: 1px dotted rgba(15,23,42,.20);
		font-size: 11pt;
	}
	.toc-num { color: #ff7a18; font-weight: bold; display: inline-block; width: 12mm; }
	.toc-title { font-weight: bold; }

	/* ---------- Info boxes ---------- */
	.info-box {
		background: #f5f7fb;
		border-left: 3pt solid #ff7a18;
		border-radius: 2mm;
		padding: 4mm 5mm;
		margin: 4mm 0;
	}
	.info-box-title {
		font-weight: bold;
		font-size: 9pt;
		text-transform: uppercase;
		letter-spacing: 1.5px;
		color: #ff7a18;
		margin-bottom: 2mm;
	}

	.warning-box {
		background: #fef3c7;
		border-left: 3pt solid #b45309;
		border-radius: 2mm;
		padding: 4mm 5mm;
		margin: 4mm 0;
	}
	.warning-box-title {
		font-weight: bold;
		font-size: 9pt;
		text-transform: uppercase;
		letter-spacing: 1.5px;
		color: #b45309;
		margin-bottom: 2mm;
	}

	/* ---------- State chips ---------- */
	.chip {
		display: inline-block;
		padding: 1mm 2.5mm;
		border-radius: 3mm;
		font-size: 8pt;
		font-weight: bold;
		border: 1px solid;
	}
	.chip-ok { background: #dcfce7; color: #15803d; border-color: #86efac; }
	.chip-no { background: #e2e8f0; color: #475569; border-color: #cbd5e1; }
	.chip-flag { background: #fef3c7; color: #b45309; border-color: #fcd34d; }
	.chip-unknown { background: #f1f5f9; color: #94a3b8; border-color: #e2e8f0; }

	/* ---------- Tables ---------- */
	table.data {
		width: 100%;
		border-collapse: collapse;
		margin: 3mm 0 5mm;
		font-size: 9.5pt;
	}
	table.data th {
		background: #f5f7fb;
		text-align: left;
		padding: 2.5mm 3mm;
		font-weight: bold;
		border-bottom: 1pt solid rgba(15,23,42,.15);
		font-size: 9pt;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		color: rgba(15,23,42,.70);
	}
	table.data td {
		padding: 2.5mm 3mm;
		border-bottom: 1px solid rgba(15,23,42,.08);
		vertical-align: top;
	}

	/* ---------- Step list ---------- */
	.steps { margin: 4mm 0; }
	.steps-item {
		padding: 3mm 0;
		border-bottom: 1px solid rgba(15,23,42,.08);
	}
	.steps-item:last-child { border-bottom: none; }
	.steps-n {
		display: inline-block;
		width: 10mm;
		color: #ff7a18;
		font-weight: bold;
		font-size: 11pt;
	}
	.steps-title { font-weight: bold; }

	/* ---------- Footer stamp ---------- */
	.footnote {
		font-size: 8pt;
		color: rgba(15,23,42,.50);
		margin-top: 8mm;
		padding-top: 4mm;
		border-top: 1px solid rgba(15,23,42,.10);
	}

	/* Keep headings with their following paragraph */
	h2, h3, h4 { page-break-after: avoid; }
</style>
</head>
<body>

{{-- ============ COVER ============ --}}
<div class="cover">
	<div class="cover-kicker">{{ $isEn ? 'Manual · v1.0' : 'Handleiding · v1.0' }}</div>
	<h1>
		Access<span class="accent">Guard</span><br>
		<span style="font-size: 20pt; font-weight: 600; color: rgba(15,23,42,.72); letter-spacing: 0;">
			{{ $isEn ? 'Know who has access to what.' : 'Zeker weten wie waar toegang heeft.' }}
		</span>
	</h1>
	<p class="cover-lead">
		{{ $isEn
			? 'Complete reference for using AccessGuard, from first matrix to periodic reviews, from onboarding new staff to offboarding leavers without any access staying open.'
			: 'Volledige referentie voor het gebruik van AccessGuard, van je eerste matrix tot periodieke reviews, van het onboarden van nieuwe medewerkers tot het waterdicht afsluiten bij uitdiensttreding.' }}
	</p>

	<div class="cover-meta">
		<table class="cover-meta-grid">
			<tr>
				<td style="width: 33%;">
					<div class="cover-meta-label">{{ $isEn ? 'Product' : 'Product' }}</div>
					<div>AccessGuard</div>
				</td>
				<td style="width: 33%;">
					<div class="cover-meta-label">{{ $isEn ? 'Published by' : 'Uitgegeven door' }}</div>
					<div>Beter Geregeld ICT</div>
				</td>
				<td style="width: 34%; text-align: right;">
					<div class="cover-meta-label">{{ $isEn ? 'Generated' : 'Gegenereerd' }}</div>
					<div>{{ $generatedAt }}</div>
				</td>
			</tr>
		</table>
	</div>
</div>

{{-- ============ TOC ============ --}}
<div class="toc">
	<h2>{{ $isEn ? 'Contents' : 'Inhoud' }}</h2>
	<ul class="toc-list">
		<li><span class="toc-num">01</span><span class="toc-title">{{ $isEn ? 'Introduction' : 'Introductie' }}</span></li>
		<li><span class="toc-num">02</span><span class="toc-title">{{ $isEn ? 'Getting started' : 'Aan de slag' }}</span></li>
		<li><span class="toc-num">03</span><span class="toc-title">{{ $isEn ? 'The Access Matrix' : 'De Access Matrix' }}</span></li>
		<li><span class="toc-num">04</span><span class="toc-title">{{ $isEn ? 'Fine-grained access items' : 'Fijnmazige access items' }}</span></li>
		<li><span class="toc-num">05</span><span class="toc-title">{{ $isEn ? 'Review cycles' : 'Review-cycli' }}</span></li>
		<li><span class="toc-num">06</span><span class="toc-title">{{ $isEn ? 'Actions queue' : 'Acties-queue' }}</span></li>
		<li><span class="toc-num">07</span><span class="toc-title">{{ $isEn ? 'Onboarding & offboarding processes' : 'Onboarding- en offboarding-processen' }}</span></li>
		<li><span class="toc-num">08</span><span class="toc-title">{{ $isEn ? 'Risk detection' : 'Risico-detectie' }}</span></li>
		<li><span class="toc-num">09</span><span class="toc-title">{{ $isEn ? 'Reminders' : 'Reminders' }}</span></li>
		<li><span class="toc-num">10</span><span class="toc-title">{{ $isEn ? 'Vault (encrypted credentials)' : 'Vault (versleutelde credentials)' }}</span></li>
		<li><span class="toc-num">11</span><span class="toc-title">{{ $isEn ? 'AI-powered explanations' : 'AI-aangedreven uitleg' }}</span></li>
		<li><span class="toc-num">12</span><span class="toc-title">{{ $isEn ? 'Plans & limits' : 'Plannen & limieten' }}</span></li>
		<li><span class="toc-num">13</span><span class="toc-title">{{ $isEn ? 'Glossary' : 'Begrippenlijst' }}</span></li>
	</ul>
</div>

{{-- ============ 01 INTRODUCTION ============ --}}
<h2 class="chapter"><span class="num">01</span>{{ $isEn ? 'Introduction' : 'Introductie' }}</h2>
<p class="chapter-intro">
	{{ $isEn
		? 'AccessGuard is access management for SMBs without an IT department. This manual covers everything from the first setup to daily operation.'
		: 'AccessGuard is toegangsbeheer voor MKB zonder IT-afdeling. Deze handleiding behandelt alles, van de eerste opzet tot dagelijks gebruik.' }}
</p>

<h3>{{ $isEn ? 'What problem does this solve?' : 'Welk probleem lost dit op?' }}</h3>
<p>
	{{ $isEn
		? 'In most SMBs, access management is scattered across Excel sheets, emails and people\'s memory. Nobody has a reliable answer to "who has access to Salesforce?" or "did we revoke everything when Lisa left?". This chaos is invisible, until there\'s an audit, an incident, or a former employee who still has access to sensitive data.'
		: 'In de meeste MKB-bedrijven is toegangsbeheer verspreid over Excel-sheets, e-mails en geheugens van mensen. Niemand heeft een betrouwbaar antwoord op "wie heeft toegang tot Salesforce?" of "hebben we alles ingetrokken toen Lisa vertrok?". Deze chaos zie je niet, tot er een audit is, een incident, of een ex-medewerker die nog steeds bij gevoelige data kan.' }}
</p>

<h3>{{ $isEn ? 'Who is this for?' : 'Voor wie is dit?' }}</h3>
<ul>
	<li>{{ $isEn ? 'Organisations of 10-200 staff' : 'Organisaties van 10-200 medewerkers' }}</li>
	<li>{{ $isEn ? 'No dedicated IT or IAM team' : 'Geen dedicated IT-afdeling of IAM-team' }}</li>
	<li>{{ $isEn ? 'Using 5-30 SaaS systems (M365, Slack, Salesforce, Exact, etc.)' : 'Gebruik van 5-30 SaaS-systemen (M365, Slack, Salesforce, Exact, etc.)' }}</li>
	<li>{{ $isEn ? 'Compliance requirements (ISO 27001, NEN 7510, GDPR audits)' : 'Compliance-eisen (ISO 27001, NEN 7510, GDPR-audits)' }}</li>
	<li>{{ $isEn ? 'External suppliers and temporary staff that also get access' : 'Externe leveranciers en tijdelijke krachten die ook toegang krijgen' }}</li>
</ul>

<h3>{{ $isEn ? 'Core concepts' : 'Kernbegrippen' }}</h3>
<table class="data">
	<tr><th>{{ $isEn ? 'Term' : 'Begrip' }}</th><th>{{ $isEn ? 'Meaning' : 'Betekenis' }}</th></tr>
	<tr>
		<td><strong>{{ $isEn ? 'Person' : 'Persoon' }}</strong></td>
		<td>{{ $isEn ? 'An employee, contractor or external party whose access you track. Has a status: active, scheduled_in, scheduled_out or inactive.' : 'Een medewerker, inhuur-kracht of externe partij wiens toegang je bijhoudt. Heeft een status: active, scheduled_in, scheduled_out of inactive.' }}</td>
	</tr>
	<tr>
		<td><strong>{{ $isEn ? 'System' : 'Systeem' }}</strong></td>
		<td>{{ $isEn ? 'An app or service someone might have access to. Categorised as SaaS, on-prem, infra, finance, security, comm or other.' : 'Een app of service waar iemand toegang tot kan hebben. Gecategoriseerd als SaaS, on-prem, infrastructuur, financieel, security, communicatie of overig.' }}</td>
	</tr>
	<tr>
		<td><strong>Cell</strong></td>
		<td>{{ $isEn ? 'The intersection of a person and a system in the Access Matrix. Holds one of four states.' : 'Het kruispunt van een persoon en een systeem in de Access Matrix. Bevat één van vier statussen.' }}</td>
	</tr>
	<tr>
		<td><strong>{{ $isEn ? 'Access item' : 'Access item' }}</strong></td>
		<td>{{ $isEn ? 'A fine-grained permission within a system (role, licence, account). Optional; only used when the system has them.' : 'Een fijnmazige permissie binnen een systeem (rol, licentie, account). Optioneel; alleen gebruikt als het systeem ze heeft.' }}</td>
	</tr>
	<tr>
		<td><strong>{{ $isEn ? 'Review cycle' : 'Review-cyclus' }}</strong></td>
		<td>{{ $isEn ? 'A periodic exercise where every cell (or item) gets a keep/revoke/change decision from a reviewer.' : 'Een periodieke exercitie waarbij elke cel (of item) een keep/revoke/change beslissing krijgt van een reviewer.' }}</td>
	</tr>
	<tr>
		<td><strong>{{ $isEn ? 'Process' : 'Proces' }}</strong></td>
		<td>{{ $isEn ? 'An onboarding or offboarding workflow for a single person, with checklist, evidence uploads and automatic access-effects.' : 'Een onboarding- of offboarding-workflow voor één persoon, met checklist, bewijs-uploads en automatische toegang-effecten.' }}</td>
	</tr>
</table>

{{-- ============ 02 GETTING STARTED ============ --}}
<h2 class="chapter"><span class="num">02</span>{{ $isEn ? 'Getting started' : 'Aan de slag' }}</h2>
<p class="chapter-intro">
	{{ $isEn
		? 'In about 15 minutes you have your first matrix filled in and can start deciding what to clean up.'
		: 'In ongeveer 15 minuten heb je je eerste matrix ingevuld en kun je beginnen met beslissen wat je gaat opruimen.' }}
</p>

<div class="info-box">
	<div class="info-box-title">{{ $isEn ? 'Prerequisites' : 'Vereisten' }}</div>
	{{ $isEn
		? 'AccessGuard is available on the Pro plan (€12/month) and higher. Sign up, choose Pro, and you\'re in.'
		: 'AccessGuard is beschikbaar vanaf het Pro-plan (€12/maand). Registreer je, kies Pro, en je bent binnen.' }}
</div>

<div class="steps">
	<div class="steps-item">
		<span class="steps-n">01.</span><strong class="steps-title">{{ $isEn ? 'Add your people' : 'Voeg je personen toe' }}</strong>
		<p class="muted small" style="margin-top: 1mm;">
			{{ $isEn
				? 'Go to AccessGuard → Personen. Add every employee, contractor and external party whose access you want to track. You don\'t need to import from HR, a handful of rows to start with is fine.'
				: 'Ga naar AccessGuard → Personen. Voeg elke medewerker, inhuur-kracht en externe partij toe wiens toegang je wilt bijhouden. Je hoeft niet vanuit HR te importeren, een handvol rijen is prima om mee te starten.' }}
		</p>
	</div>
	<div class="steps-item">
		<span class="steps-n">02.</span><strong class="steps-title">{{ $isEn ? 'Add your systems' : 'Voeg je systemen toe' }}</strong>
		<p class="muted small" style="margin-top: 1mm;">
			{{ $isEn
				? 'AccessGuard → Systemen. Everything your staff might have access to: M365, Slack, Salesforce, Exact, 1Password, AWS, domain admin, the physical alarm code. Think broadly.'
				: 'AccessGuard → Systemen. Alles waar je mensen toegang toe kunnen hebben: M365, Slack, Salesforce, Exact, 1Password, AWS, domeinbeheer, de fysieke alarmcode. Denk breed.' }}
		</p>
	</div>
	<div class="steps-item">
		<span class="steps-n">03.</span><strong class="steps-title">{{ $isEn ? 'Fill in the matrix' : 'Vul de matrix in' }}</strong>
		<p class="muted small" style="margin-top: 1mm;">
			{{ $isEn
				? 'AccessGuard → Access Matrix. Click a cell to cycle the status: unknown → has_access → no_access → needs_review. Don\'t aim for perfection; fill in what you already know.'
				: 'AccessGuard → Access Matrix. Klik op een cel om door de statussen te rouleren: onbekend → heeft toegang → geen toegang → heroverwegen. Streef niet naar perfectie; vul in wat je al weet.' }}
		</p>
	</div>
	<div class="steps-item">
		<span class="steps-n">04.</span><strong class="steps-title">{{ $isEn ? 'Start your first review cycle' : 'Start je eerste review-cyclus' }}</strong>
		<p class="muted small" style="margin-top: 1mm;">
			{{ $isEn
				? 'AccessGuard → Reviews → Nieuwe cyclus. The current matrix is snapshotted; you decide keep/revoke/change per row. On completion, IT gets an actions list.'
				: 'AccessGuard → Reviews → Nieuwe cyclus. De huidige matrix wordt gesnapshot; jij beslist per rij behouden/intrekken/wijzigen. Bij afronding krijgt IT een acties-lijst.' }}
		</p>
	</div>
</div>

{{-- ============ 03 ACCESS MATRIX ============ --}}
<h2 class="chapter"><span class="num">03</span>{{ $isEn ? 'The Access Matrix' : 'De Access Matrix' }}</h2>
<p class="chapter-intro">
	{{ $isEn
		? 'The matrix is the heart of AccessGuard. A 2D grid where the rows are people and the columns are systems. Each cell shows one of four states.'
		: 'De matrix is het hart van AccessGuard. Een 2D-rooster waarin de rijen personen zijn en de kolommen systemen. Elke cel toont één van vier statussen.' }}
</p>

<h3>{{ $isEn ? 'The four cell states' : 'De vier cel-statussen' }}</h3>
<table class="data">
	<tr><th style="width: 25mm;">{{ $isEn ? 'State' : 'Status' }}</th><th>{{ $isEn ? 'Meaning' : 'Betekenis' }}</th></tr>
	<tr>
		<td><span class="chip chip-ok">✓ has_access</span></td>
		<td>{{ $isEn ? 'The person currently has access to the system. Status is confirmed.' : 'De persoon heeft momenteel toegang tot het systeem. Status is bevestigd.' }}</td>
	</tr>
	<tr>
		<td><span class="chip chip-no">× no_access</span></td>
		<td>{{ $isEn ? 'The person has no access, and that\'s deliberate. Confirmed negative.' : 'De persoon heeft geen toegang, en dat is bewust. Bevestigd negatief.' }}</td>
	</tr>
	<tr>
		<td><span class="chip chip-flag">? needs_review</span></td>
		<td>{{ $isEn ? 'Not sure, flag for the next review. Used when state is unclear or suspicious.' : 'Niet zeker, markeer voor volgende review. Gebruikt als de status onduidelijk of verdacht is.' }}</td>
	</tr>
	<tr>
		<td><span class="chip chip-unknown">, unknown</span></td>
		<td>{{ $isEn ? 'Never decided. The default state for new cells. Aim to reduce this to zero.' : 'Nooit beslist. De standaardstatus voor nieuwe cellen. Streef ernaar dit naar nul te brengen.' }}</td>
	</tr>
</table>

<h3>{{ $isEn ? 'How to click through the matrix' : 'Hoe klik je door de matrix' }}</h3>
<p>
	{{ $isEn
		? 'Click any cell without items to cycle through the four states in order. Click again to continue. Every click is timestamped in last_verified_at, useful for the review cycle to see "when did we last confirm this?".'
		: 'Klik op elke cel zonder items om door de vier statussen te rouleren in volgorde. Klik nog eens om door te gaan. Elke klik wordt geregistreerd in last_verified_at, nuttig voor de review-cyclus om te zien "wanneer hebben we dit voor het laatst bevestigd?".' }}
</p>

<div class="info-box">
	<div class="info-box-title">{{ $isEn ? 'Tip' : 'Tip' }}</div>
	{{ $isEn
		? 'Start by filling in only has_access cells, the people who definitely have access. That alone already shows you where access is excessive. Everything else can stay "unknown" until the first review cycle.'
		: 'Vul eerst alleen has_access cellen in, de mensen die zeker toegang hebben. Dat alleen al laat zien waar toegang te ruim is. De rest mag "unknown" blijven tot de eerste review-cyclus.' }}
</div>

{{-- ============ 04 ACCESS ITEMS ============ --}}
<h2 class="chapter"><span class="num">04</span>{{ $isEn ? 'Fine-grained access items' : 'Fijnmazige access items' }}</h2>
<p class="chapter-intro">
	{{ $isEn
		? 'Sometimes "access to Salesforce" is too coarse. Is this person a global admin, a standard user, or read-only? Access items let you track per role/licence/account within a system.'
		: 'Soms is "toegang tot Salesforce" te grof. Is deze persoon global admin, standaard gebruiker of read-only? Access items laten je per rol/licentie/account binnen een systeem bijhouden.' }}
</p>

<h3>{{ $isEn ? 'When to use items' : 'Wanneer gebruik je items' }}</h3>
<ul>
	<li>{{ $isEn ? 'Systems with multiple licence tiers (M365: Basic / E3 / E5)' : 'Systemen met meerdere licentie-niveaus (M365: Basic / E3 / E5)' }}</li>
	<li>{{ $isEn ? 'Systems with role hierarchies (Salesforce: Admin / Standard / Read-only)' : 'Systemen met rol-hiërarchieën (Salesforce: Admin / Standard / Read-only)' }}</li>
	<li>{{ $isEn ? 'Systems where different accounts matter (Google Workspace: personal / service account)' : 'Systemen waar verschillende accounts uitmaken (Google Workspace: persoonlijk / service-account)' }}</li>
	<li>{{ $isEn ? 'Where compliance reports require role-level detail' : 'Waar compliance-rapportages rol-niveau detail vereisen' }}</li>
</ul>

<h3>{{ $isEn ? 'How the matrix behaves with items' : 'Hoe de matrix zich gedraagt met items' }}</h3>
<p>
	{{ $isEn
		? 'Once a system has one or more active items, the cell on the matrix becomes a drill-down link instead of a click-to-cycle button. The cell-state is derived automatically:'
		: 'Zodra een systeem één of meer actieve items heeft, wordt de cel op de matrix een drill-down link i.p.v. een klik-knop. De cel-status wordt automatisch afgeleid:' }}
</p>
<table class="data">
	<tr><th>{{ $isEn ? 'Item combination' : 'Item-combinatie' }}</th><th>{{ $isEn ? 'Cell state' : 'Cel-status' }}</th></tr>
	<tr><td>{{ $isEn ? '≥1 item has_access, all same' : '≥1 item has_access, alle gelijk' }}</td><td><span class="chip chip-ok">has_access</span></td></tr>
	<tr><td>{{ $isEn ? 'Any needs_review or mix of states' : 'Enige needs_review of mix van statussen' }}</td><td><span class="chip chip-flag">needs_review</span></td></tr>
	<tr><td>{{ $isEn ? 'All items no_access' : 'Alle items no_access' }}</td><td><span class="chip chip-no">no_access</span></td></tr>
	<tr><td>{{ $isEn ? 'No items set yet' : 'Nog geen items gezet' }}</td><td><span class="chip chip-unknown">unknown</span></td></tr>
</table>

<h3>{{ $isEn ? 'Managing items' : 'Items beheren' }}</h3>
<p>
	{{ $isEn
		? 'AccessGuard → Systemen → click "Items" next to the system. Add / edit / deactivate items. Each item has a type (role, licence, account, key, badge, group, other) and a sort order. Deactivated items disappear from the drill-down but their history stays in the audit log.'
		: 'AccessGuard → Systemen → klik "Items" naast het systeem. Voeg toe / bewerk / deactiveer items. Elk item heeft een type (rol, licentie, account, sleutel, pas, groep, overig) en een sorteervolgorde. Gedeactiveerde items verdwijnen uit de drill-down maar hun historie blijft in de audit-log.' }}
</p>

{{-- ============ 05 REVIEW CYCLES ============ --}}
<h2 class="chapter"><span class="num">05</span>{{ $isEn ? 'Review cycles' : 'Review-cycli' }}</h2>
<p class="chapter-intro">
	{{ $isEn
		? 'A review cycle is a periodic exercise, quarterly or annually, where someone goes through every matrix cell and decides: keep this access, revoke it, or change it.'
		: 'Een review-cyclus is een periodieke exercitie, per kwartaal of jaarlijks, waarin iemand elke matrix-cel doorloopt en beslist: deze toegang behouden, intrekken of wijzigen.' }}
</p>

<h3>{{ $isEn ? 'Cycle lifecycle' : 'Cyclus-levensloop' }}</h3>
<p><code>planned → active → completed</code> {{ $isEn ? '(or cancelled)' : '(of cancelled)' }}</p>

<h3>{{ $isEn ? 'Starting a cycle' : 'Een cyclus starten' }}</h3>
<p>
	{{ $isEn
		? 'AccessGuard → Reviews → Nieuwe cyclus. Pick a title ("Q1 2026 access review"), a scope (active people, or everyone including inactive), an optional deadline, and notes for the reviewer. On save, the current matrix is snapshotted into review_items, one row per cell (or per item when the system has items).'
		: 'AccessGuard → Reviews → Nieuwe cyclus. Kies een titel ("Q1 2026 access review"), een scope (actieve personen, of iedereen inclusief inactief), een optionele deadline, en notities voor de reviewer. Bij opslaan wordt de huidige matrix gesnapshot in review_items, één rij per cel (of per item als het systeem items heeft).' }}
</p>

<div class="warning-box">
	<div class="warning-box-title">{{ $isEn ? 'Important' : 'Belangrijk' }}</div>
	{{ $isEn
		? 'After the snapshot is taken, later changes to the matrix do NOT affect the cycle. The snapshot freezes what was being decided. This keeps the audit trail clean.'
		: 'Nadat de snapshot is genomen, beïnvloeden latere matrix-wijzigingen de cyclus NIET. De snapshot bevriest wat er werd besloten. Dit houdt het audit-spoor schoon.' }}
</div>

<h3>{{ $isEn ? 'Making decisions' : 'Beslissingen nemen' }}</h3>
<table class="data">
	<tr><th>{{ $isEn ? 'Decision' : 'Beslissing' }}</th><th>{{ $isEn ? 'What happens at completion' : 'Wat gebeurt er bij afronding' }}</th></tr>
	<tr><td><strong>keep</strong></td><td>{{ $isEn ? 'The cell last_verified_at is bumped. No further action needed.' : 'De cel last_verified_at wordt bijgewerkt. Geen verdere actie nodig.' }}</td></tr>
	<tr><td><strong>revoke</strong></td><td>{{ $isEn ? 'An open revoke_access action is created. IT has to act on it.' : 'Een open revoke_access actie wordt aangemaakt. IT moet erop handelen.' }}</td></tr>
	<tr><td><strong>change</strong></td><td>{{ $isEn ? 'An open review_level action is created (e.g. downgrade admin to read-only).' : 'Een open review_level actie wordt aangemaakt (bv. admin naar read-only degraderen).' }}</td></tr>
	<tr><td>{{ $isEn ? '(empty)' : '(leeg)' }}</td><td>{{ $isEn ? 'Undecided items default to "keep" when the cycle is closed.' : 'Niet-besliste items worden standaard "keep" bij cyclus-afronding.' }}</td></tr>
</table>

<h3>{{ $isEn ? 'Bulk decisions' : 'Bulk-beslissingen' }}</h3>
<p>
	{{ $isEn
		? 'Use the checkboxes to select multiple review items and apply the same decision in one click. Handy for large cycles where most decisions are "keep".'
		: 'Gebruik de checkboxes om meerdere review-items te selecteren en dezelfde beslissing in één klik toe te passen. Handig bij grote cycli waar de meeste beslissingen "keep" zijn.' }}
</p>

{{-- ============ 06 ACTIONS QUEUE ============ --}}
<h2 class="chapter"><span class="num">06</span>{{ $isEn ? 'Actions queue' : 'Acties-queue' }}</h2>
<p class="chapter-intro">
	{{ $isEn
		? 'When a review cycle completes (or an offboarding completes), revoke and change decisions materialise as open actions. The actions queue is what IT works through.'
		: 'Als een review-cyclus afrondt (of een offboarding), materialiseren revoke- en change-beslissingen als open acties. De acties-queue is wat IT afwerkt.' }}
</p>

<h3>{{ $isEn ? 'Two action kinds' : 'Twee actie-soorten' }}</h3>
<ul>
	<li><strong>revoke_access</strong>, {{ $isEn ? 'Remove this access. On "mark done", the matrix cell flips to no_access (or the individual item if item-scoped).' : 'Verwijder deze toegang. Bij "afronden" flipt de matrix-cel naar no_access (of het individuele item als item-scoped).' }}</li>
	<li><strong>review_level</strong>, {{ $isEn ? 'Change the level (e.g. admin → read-only). On "mark done", only the verified timestamp is bumped; you handle the actual level change externally and note it here.' : 'Wijzig het niveau (bv. admin → read-only). Bij "afronden" wordt alleen de verified timestamp bijgewerkt; je handelt de daadwerkelijke niveau-wijziging extern af en noteert het hier.' }}</li>
</ul>

<h3>{{ $isEn ? 'Typical flow' : 'Typische flow' }}</h3>
<ol>
	<li>{{ $isEn ? 'Reviewer closes a cycle with 6 revoke decisions' : 'Reviewer sluit een cyclus met 6 revoke-beslissingen' }}</li>
	<li>{{ $isEn ? '6 revoke_access actions appear in the queue with status=open' : '6 revoke_access acties verschijnen in de queue met status=open' }}</li>
	<li>{{ $isEn ? 'IT disables the accounts in the actual systems (M365 admin centre, Slack, etc.)' : 'IT deactiveert de accounts in de daadwerkelijke systemen (M365 admin centre, Slack, etc.)' }}</li>
	<li>{{ $isEn ? 'IT clicks "Afronden" on each action, the corresponding matrix cell flips to no_access' : 'IT klikt "Afronden" op elke actie, de bijbehorende matrix-cel flipt naar no_access' }}</li>
	<li>{{ $isEn ? 'The audit log records who marked done + when + what was applied' : 'De audit-log registreert wie afgerond heeft + wanneer + wat is toegepast' }}</li>
</ol>

{{-- ============ 07 PROCESSES ============ --}}
<h2 class="chapter"><span class="num">07</span>{{ $isEn ? 'Onboarding & offboarding processes' : 'Onboarding- en offboarding-processen' }}</h2>
<p class="chapter-intro">
	{{ $isEn
		? 'A process is a per-person workflow: a checklist of things that need to happen, with optional evidence uploads per item. Two kinds: onboarding (new hire) and offboarding (leaver).'
		: 'Een proces is een per-persoon workflow: een checklist van dingen die moeten gebeuren, met optionele bewijs-uploads per item. Twee soorten: onboarding (nieuwe medewerker) en offboarding (vertrekker).' }}
</p>

<h3>{{ $isEn ? 'Checklist item states' : 'Checklist-item statussen' }}</h3>
<p><code>todo → in_progress → done</code> / <code>blocked</code> / <code>na</code></p>
<ul>
	<li><strong>todo</strong>: {{ $isEn ? 'default, not started' : 'standaard, niet gestart' }}</li>
	<li><strong>in_progress</strong>: {{ $isEn ? 'being worked on' : 'wordt aan gewerkt' }}</li>
	<li><strong>done</strong>: {{ $isEn ? 'completed successfully' : 'succesvol afgerond' }}</li>
	<li><strong>blocked</strong>: {{ $isEn ? 'waiting for something external, reason required' : 'wacht op iets externs, reden verplicht' }}</li>
	<li><strong>na</strong>: {{ $isEn ? 'not applicable for this case, reason required' : 'niet van toepassing voor dit geval, reden verplicht' }}</li>
</ul>

<h3>{{ $isEn ? 'Offboarding → automatic revokes' : 'Offboarding → automatische revokes' }}</h3>
<p>
	{{ $isEn
		? 'Completing an offboarding process has a powerful side-effect: every has_access cell or item of the subject person automatically becomes a revoke_access action. This means you physically cannot "forget" to revoke access, the system surfaces every open door.'
		: 'Het afronden van een offboarding heeft een krachtig neveneffect: elke has_access cel of item van de betreffende persoon wordt automatisch een revoke_access actie. Hierdoor kun je fysiek niet "vergeten" om toegang in te trekken, het systeem toont elke open deur.' }}
</p>

<div class="info-box">
	<div class="info-box-title">{{ $isEn ? 'Evidence uploads' : 'Bewijs-uploads' }}</div>
	{{ $isEn
		? 'Per checklist item you can upload PDF / JPG / PNG files up to 15 MB. Useful for: signed hardware-return form, screenshot of disabled M365 account, photo of returned access badge. Each file is stored with a UUID, SHA-256 hashed for integrity, and only accessible within the tenant.'
		: 'Per checklist-item kun je PDF / JPG / PNG bestanden uploaden tot 15 MB. Handig voor: getekend hardware-retour formulier, screenshot van gedeactiveerd M365 account, foto van teruggegeven toegangspas. Elk bestand wordt opgeslagen met een UUID, SHA-256 gehasht voor integriteit, en alleen toegankelijk binnen de tenant.' }}
</div>

{{-- ============ 08 RISK DETECTION ============ --}}
<h2 class="chapter"><span class="num">08</span>{{ $isEn ? 'Risk detection' : 'Risico-detectie' }}</h2>
<p class="chapter-intro">
	{{ $isEn
		? 'A scheduled scan runs every night at 03:00 and surfaces risky patterns as open risk flags. Seven detection rules cover the most common SMB access-control failures.'
		: 'Een geplande scan draait elke nacht om 03:00 en brengt risicovolle patronen naar boven als open risk-flags. Zeven detectie-regels dekken de meest voorkomende MKB access-control-problemen.' }}
</p>

<h3>{{ $isEn ? 'The seven detection rules' : 'De zeven detectie-regels' }}</h3>
<table class="data">
	<tr><th>{{ $isEn ? 'Rule' : 'Regel' }}</th><th>{{ $isEn ? 'Severity' : 'Ernst' }}</th><th>{{ $isEn ? 'Trigger' : 'Trigger' }}</th></tr>
	<tr><td><strong>stale_admin</strong></td><td>4</td><td>{{ $isEn ? 'Admin-like item with has_access but no verification for 90+ days' : 'Admin-achtig item met has_access maar geen verificatie voor 90+ dagen' }}</td></tr>
	<tr><td><strong>orphan_access</strong></td><td>5</td><td>{{ $isEn ? 'Inactive person still has active has_access cells or items' : 'Inactieve persoon heeft nog actieve has_access cellen of items' }}</td></tr>
	<tr><td><strong>excessive_access</strong></td><td>3</td><td>{{ $isEn ? 'Person has has_access on ≥10 different systems' : 'Persoon heeft has_access op ≥10 verschillende systemen' }}</td></tr>
	<tr><td><strong>overdue_review</strong></td><td>4-5</td><td>{{ $isEn ? 'Open cycle past its due_at (5 if >30 days overdue)' : 'Open cyclus voorbij due_at (5 bij >30 dagen over)' }}</td></tr>
	<tr><td><strong>overdue_action</strong></td><td>3-4</td><td>{{ $isEn ? 'Open action older than 14 days (4 if >30 days)' : 'Open actie ouder dan 14 dagen (4 bij >30 dagen)' }}</td></tr>
	<tr><td><strong>pending_onboarding</strong></td><td>3</td><td>{{ $isEn ? 'Person scheduled_in but no active onboarding process' : 'Persoon scheduled_in maar geen actief onboarding-proces' }}</td></tr>
	<tr><td><strong>stale_credential</strong></td><td>4-5</td><td>{{ $isEn ? 'Vault credential past expires_at (5) or rotation overdue (4)' : 'Vault credential voorbij expires_at (5) of rotatie overdue (4)' }}</td></tr>
</table>

<h3>{{ $isEn ? 'Working risks' : 'Risico\'s afhandelen' }}</h3>
<p>{{ $isEn ? 'For each open risk flag you have three actions:' : 'Voor elke open risk-flag heb je drie acties:' }}</p>
<ul>
	<li><strong>{{ $isEn ? 'Acknowledge' : 'Bevestig' }}</strong>, {{ $isEn ? 'I\'ve seen this, will act on it. Moves to "acknowledged" status.' : 'Ik heb dit gezien, ga erop handelen. Gaat naar "acknowledged" status.' }}</li>
	<li><strong>{{ $isEn ? 'Resolve' : 'Opgelost' }}</strong>, {{ $isEn ? 'The underlying problem is fixed (account revoked, review completed, etc.). Moves to "resolved".' : 'Het onderliggende probleem is opgelost (account ingetrokken, review afgerond, etc.). Gaat naar "resolved".' }}</li>
	<li><strong>{{ $isEn ? 'Reopen' : 'Heropen' }}</strong>, {{ $isEn ? 'If something was resolved prematurely, you can reopen it.' : 'Als iets te vroeg opgelost is, kun je het heropenen.' }}</li>
</ul>

{{-- ============ 09 REMINDERS ============ --}}
<h2 class="chapter"><span class="num">09</span>{{ $isEn ? 'Reminders' : 'Reminders' }}</h2>
<p class="chapter-intro">
	{{ $isEn
		? 'A second scheduled job (daily at 03:15) scans for upcoming deadlines and creates reminders. Reminders are in-app notifications, lightweight compared to risk flags.'
		: 'Een tweede geplande job (dagelijks 03:15) scant op aankomende deadlines en maakt reminders aan. Reminders zijn in-app notificaties, lichter dan risk-flags.' }}
</p>

<table class="data">
	<tr><th>{{ $isEn ? 'Reminder kind' : 'Reminder-soort' }}</th><th>{{ $isEn ? 'Triggers when' : 'Triggert wanneer' }}</th></tr>
	<tr><td><strong>cycle_due</strong></td><td>{{ $isEn ? 'Open review cycle\'s due_at within 7 days or overdue' : 'Open review-cyclus due_at binnen 7 dagen of overdue' }}</td></tr>
	<tr><td><strong>process_due</strong></td><td>{{ $isEn ? 'Active onboarding/offboarding due_at within 7 days or overdue' : 'Actieve onboarding/offboarding due_at binnen 7 dagen of overdue' }}</td></tr>
	<tr><td><strong>action_overdue</strong></td><td>{{ $isEn ? 'Open action older than 14 days' : 'Open actie ouder dan 14 dagen' }}</td></tr>
	<tr><td><strong>person_starting</strong></td><td>{{ $isEn ? 'Person scheduled_in with start_date within 7 days' : 'Persoon scheduled_in met start_date binnen 7 dagen' }}</td></tr>
	<tr><td><strong>person_leaving</strong></td><td>{{ $isEn ? 'Person scheduled_out with end_date within 7 days' : 'Persoon scheduled_out met end_date binnen 7 dagen' }}</td></tr>
</table>

<p>
	{{ $isEn
		? 'Dismissing a reminder marks it permanently dismissed, the next scheduled run will not resurrect it. Marking "Klaar" closes it cleanly.'
		: 'Een reminder wegklikken markeert hem permanent dismissed, de volgende geplande run zal hem niet terugbrengen. "Klaar" sluit hem schoon af.' }}
</p>

{{-- ============ 10 VAULT ============ --}}
<h2 class="chapter"><span class="num">10</span>{{ $isEn ? 'Vault (encrypted credentials)' : 'Vault (versleutelde credentials)' }}</h2>
<p class="chapter-intro">
	{{ $isEn
		? 'Store passwords, tokens, API keys, SSH keys and certificates, linked to systems or access items. Secrets are encrypted with AES-256 + HMAC; every view or decrypt is logged.'
		: 'Bewaar wachtwoorden, tokens, API keys, SSH keys en certificaten, gekoppeld aan systemen of access items. Secrets worden versleuteld met AES-256 + HMAC; elke view of decrypt wordt gelogd.' }}
</p>

<h3>{{ $isEn ? 'Access model' : 'Access-model' }}</h3>
<ul>
	<li>{{ $isEn ? 'The creator is implicit admin (view + decrypt + rotate + delete). No ACL row needed.' : 'De maker is impliciet admin (view + decrypt + rotate + delete). Geen ACL-row nodig.' }}</li>
	<li>{{ $isEn ? 'Anyone else needs an explicit ACL grant with the appropriate flags.' : 'Iedereen anders heeft een expliciete ACL-grant nodig met de juiste flags.' }}</li>
	<li>{{ $isEn ? 'Four permission levels: can_view, can_decrypt, can_rotate, can_admin.' : 'Vier rechten-niveaus: can_view, can_decrypt, can_rotate, can_admin.' }}</li>
</ul>

<h3>{{ $isEn ? 'Reveal-on-click workflow' : 'Reveal-on-click workflow' }}</h3>
<p>
	{{ $isEn
		? 'On the detail page, the secret is masked as bullets. Click "Toon" → the JSON endpoint decrypts and returns the plaintext. The UI shows it for 30 seconds with a ticking countdown, then auto-hides. You can click the copy button to put it on the clipboard.'
		: 'Op de detail-pagina is de secret gemaskeerd als bolletjes. Klik "Toon" → het JSON endpoint decrypt en geeft de plaintext terug. De UI toont het 30 seconden met een aflopende teller, verbergt dan automatisch. Je kunt op de kopieer-knop klikken om het op het klembord te zetten.' }}
</p>

<div class="warning-box">
	<div class="warning-box-title">{{ $isEn ? 'Audit trail' : 'Audit-trail' }}</div>
	{{ $isEn
		? 'Every action on a credential is logged: created, updated, viewed (metadata), decrypted, rotated, deleted, and ACL grants/revokes. Each log row includes the user and a hashed IP. Keep this in mind, secret access is NOT silent.'
		: 'Elke actie op een credential wordt gelogd: aangemaakt, bijgewerkt, bekeken (metadata), gedecrypteerd, geroteerd, verwijderd, en ACL-grants/revokes. Elke log-rij bevat de gebruiker en een gehashed IP-adres. Houd dit in gedachten, secret-toegang is NIET stil.' }}
</div>

<h3>{{ $isEn ? 'Offboarding → automatic ACL revoke' : 'Offboarding → automatische ACL-revoke' }}</h3>
<p>
	{{ $isEn
		? 'When an offboarding process completes for a Person whose email matches a User in the tenant, every ACL row that User holds is automatically revoked. One email-match, zero credentials left accessible.'
		: 'Als een offboarding-proces afrondt voor een Persoon wiens e-mailadres matcht met een Gebruiker in de tenant, wordt elke ACL-row die die Gebruiker heeft automatisch ingetrokken. Eén email-match, nul credentials blijven toegankelijk.' }}
</p>

{{-- ============ 11 AI EXPLANATIONS ============ --}}
<h2 class="chapter"><span class="num">11</span>{{ $isEn ? 'AI-powered explanations' : 'AI-aangedreven uitleg' }}</h2>
<p class="chapter-intro">
	{{ $isEn
		? 'Not everyone on your team is a security person. The AI-explain widget translates a risk flag into plain-language advice, what it is, why it matters, and three or four concrete next steps.'
		: 'Niet iedereen in je team is een security-persoon. De AI-explain widget vertaalt een risk-flag naar begrijpelijke taal, wat het is, waarom het belangrijk is, en drie of vier concrete vervolgstappen.' }}
</p>

<h3>{{ $isEn ? 'How it works' : 'Hoe het werkt' }}</h3>
<p>{{ $isEn ? 'Click the 🤖 Uitleg button on any risk flag. A modal opens with:' : 'Klik op de 🤖 Uitleg knop bij een risk-flag. Een modal opent met:' }}</p>
<ul>
	<li><strong>{{ $isEn ? 'Summary' : 'Samenvatting' }}</strong>, {{ $isEn ? 'two-three sentences describing the risk' : 'twee-drie zinnen die het risico beschrijven' }}</li>
	<li><strong>{{ $isEn ? 'Why it matters' : 'Waarom dit ertoe doet' }}</strong>, {{ $isEn ? 'business + security context' : 'business + security context' }}</li>
	<li><strong>{{ $isEn ? 'Recommended steps' : 'Aanbevolen stappen' }}</strong>, {{ $isEn ? 'three or four concrete actions in AccessGuard' : 'drie of vier concrete acties in AccessGuard' }}</li>
	<li><strong>{{ $isEn ? 'Warnings' : 'Waarschuwingen' }}</strong>, {{ $isEn ? 'what to watch out for' : 'waar je op moet letten' }}</li>
</ul>

<div class="info-box">
	<div class="info-box-title">{{ $isEn ? 'Privacy' : 'Privacy' }}</div>
	{{ $isEn
		? 'Only the risk flag\'s metadata is sent to OpenAI, never secrets, never full email addresses, never vault content. The payload is whitelisted to safe keys (ids, counts, dates). Every AI call is logged with tokens used. Rate-limited to 10 calls per tenant per hour; identical prompts are cached 24 hours.'
		: 'Alleen de risk-flag metadata wordt naar OpenAI gestuurd, nooit secrets, nooit volledige e-mailadressen, nooit vault-inhoud. De payload is whitelist-gebaseerd op veilige keys (ids, counts, datums). Elke AI-call wordt gelogd met het aantal tokens. Rate-limited op 10 calls per tenant per uur; identieke prompts worden 24 uur gecached.' }}
</div>

{{-- ============ 12 PLANS ============ --}}
<h2 class="chapter"><span class="num">12</span>{{ $isEn ? 'Plans & limits' : 'Plannen & limieten' }}</h2>
<p class="chapter-intro">
	{{ $isEn
		? 'AccessGuard is included in the Pro plan and up. Business adds multi-user access and removes the AI rate limit.'
		: 'AccessGuard is inbegrepen vanaf het Pro-plan. Business voegt multi-user-toegang toe en verwijdert de AI rate-limit.' }}
</p>

<table class="data">
	<tr>
		<th>{{ $isEn ? 'Feature' : 'Functie' }}</th>
		<th>Free</th>
		<th>Pro<br><span class="muted small">€12/mo</span></th>
		<th>Business<br><span class="muted small">€39/mo</span></th>
	</tr>
	<tr><td>{{ $isEn ? 'Access Matrix + items' : 'Access Matrix + items' }}</td><td>, </td><td>✓</td><td>✓</td></tr>
	<tr><td>{{ $isEn ? 'Review cycles' : 'Review-cycli' }}</td><td>, </td><td>✓</td><td>✓</td></tr>
	<tr><td>{{ $isEn ? 'Onboarding / offboarding' : 'Onboarding / offboarding' }}</td><td>, </td><td>✓</td><td>✓</td></tr>
	<tr><td>{{ $isEn ? 'Risk flags + reminders' : 'Risk flags + reminders' }}</td><td>, </td><td>✓</td><td>✓</td></tr>
	<tr><td>Vault</td><td>, </td><td>✓</td><td>✓</td></tr>
	<tr><td>{{ $isEn ? 'AI explanations' : 'AI-uitleg' }}</td><td>, </td><td>{{ $isEn ? '10/h' : '10/u' }}</td><td>{{ $isEn ? 'unlimited' : 'unlimited' }}</td></tr>
	<tr><td>{{ $isEn ? 'Multi-user access' : 'Multi-user toegang' }}</td><td>, </td><td>, </td><td>✓</td></tr>
</table>

{{-- ============ 13 GLOSSARY ============ --}}
<h2 class="chapter"><span class="num">13</span>{{ $isEn ? 'Glossary' : 'Begrippenlijst' }}</h2>
<p class="chapter-intro">
	{{ $isEn
		? 'Quick reference for the terms and abbreviations used throughout AccessGuard.'
		: 'Snelle referentie voor de termen en afkortingen in AccessGuard.' }}
</p>

<table class="data">
	<tr><th>{{ $isEn ? 'Term' : 'Begrip' }}</th><th>{{ $isEn ? 'Meaning' : 'Betekenis' }}</th></tr>
	<tr><td><strong>ACL</strong></td><td>{{ $isEn ? 'Access Control List, the per-user permission rows on a vault credential.' : 'Access Control List, de per-gebruiker permissie-rijen op een vault credential.' }}</td></tr>
	<tr><td><strong>{{ $isEn ? 'Access cell' : 'Access cel' }}</strong></td><td>{{ $isEn ? 'The intersection of a person and a system in the matrix.' : 'Het kruispunt van persoon en systeem in de matrix.' }}</td></tr>
	<tr><td><strong>{{ $isEn ? 'Access item' : 'Access item' }}</strong></td><td>{{ $isEn ? 'A fine-grained permission within a system (role, licence, account).' : 'Een fijnmazige permissie binnen een systeem (rol, licentie, account).' }}</td></tr>
	<tr><td><strong>{{ $isEn ? 'Cycle' : 'Cyclus' }}</strong></td><td>{{ $isEn ? 'A review cycle: a time-bound exercise of reviewing every cell in a snapshot.' : 'Een review-cyclus: een tijdgebonden exercitie waarbij elke cel in een snapshot wordt beoordeeld.' }}</td></tr>
	<tr><td><strong>{{ $isEn ? 'has_access' : 'has_access' }}</strong></td><td>{{ $isEn ? 'Cell state: person currently has confirmed access.' : 'Cel-status: persoon heeft momenteel bevestigde toegang.' }}</td></tr>
	<tr><td><strong>IAM</strong></td><td>{{ $isEn ? 'Identity and Access Management, the broader discipline AccessGuard sits in.' : 'Identity and Access Management, de bredere discipline waar AccessGuard onder valt.' }}</td></tr>
	<tr><td><strong>{{ $isEn ? 'last_verified_at' : 'last_verified_at' }}</strong></td><td>{{ $isEn ? 'Timestamp of when a cell/item was last confirmed. Drives stale-admin detection.' : 'Tijdstip waarop een cel/item voor het laatst is bevestigd. Drijft stale-admin detectie.' }}</td></tr>
	<tr><td><strong>{{ $isEn ? 'needs_review' : 'needs_review' }}</strong></td><td>{{ $isEn ? 'Cell state: status unclear, flag for next cycle.' : 'Cel-status: status onduidelijk, markeer voor volgende cyclus.' }}</td></tr>
	<tr><td><strong>{{ $isEn ? 'no_access' : 'no_access' }}</strong></td><td>{{ $isEn ? 'Cell state: person deliberately has no access.' : 'Cel-status: persoon heeft bewust geen toegang.' }}</td></tr>
	<tr><td><strong>{{ $isEn ? 'Offboarding' : 'Offboarding' }}</strong></td><td>{{ $isEn ? 'The process for someone leaving the organisation. Completion triggers automatic revoke actions.' : 'Het proces voor iemand die de organisatie verlaat. Afronding triggert automatische revoke-acties.' }}</td></tr>
	<tr><td><strong>{{ $isEn ? 'Onboarding' : 'Onboarding' }}</strong></td><td>{{ $isEn ? 'The process for someone joining the organisation. Checklist-based with evidence uploads.' : 'Het proces voor iemand die bij de organisatie komt. Checklist-gebaseerd met bewijs-uploads.' }}</td></tr>
	<tr><td><strong>{{ $isEn ? 'Person' : 'Persoon' }}</strong></td><td>{{ $isEn ? 'Someone whose access you track, employee, contractor or external.' : 'Iemand wiens toegang je bijhoudt, medewerker, inhuur of extern.' }}</td></tr>
	<tr><td><strong>{{ $isEn ? 'Risk flag' : 'Risk flag' }}</strong></td><td>{{ $isEn ? 'A detected risk pattern. Has severity 1-5 and states open / acknowledged / resolved.' : 'Een gedetecteerd risico-patroon. Heeft severity 1-5 en statussen open / acknowledged / resolved.' }}</td></tr>
	<tr><td><strong>{{ $isEn ? 'Scope' : 'Scope' }}</strong></td><td>{{ $isEn ? 'A cycle scope: "active people" (default) or "everyone incl. inactive" (for yearly audit).' : 'Een cyclus scope: "actieve personen" (default) of "iedereen incl. inactief" (voor jaarlijkse audit).' }}</td></tr>
	<tr><td><strong>{{ $isEn ? 'Snapshot' : 'Snapshot' }}</strong></td><td>{{ $isEn ? 'A frozen copy of the matrix at cycle-start. Later changes don\'t affect the cycle.' : 'Een bevroren kopie van de matrix bij cyclus-start. Latere wijzigingen beïnvloeden de cyclus niet.' }}</td></tr>
	<tr><td><strong>{{ $isEn ? 'System' : 'Systeem' }}</strong></td><td>{{ $isEn ? 'An app or service where people can have access. Can have items (for fine-grained tracking).' : 'Een app of service waar mensen toegang kunnen hebben. Kan items hebben (voor fijnmazige tracking).' }}</td></tr>
	<tr><td><strong>Tenant</strong></td><td>{{ $isEn ? 'Your organisation\'s isolated space. All AccessGuard data is scoped to a single tenant.' : 'De geïsoleerde ruimte van jouw organisatie. Alle AccessGuard-data is gescoped per tenant.' }}</td></tr>
	<tr><td><strong>{{ $isEn ? 'unknown' : 'unknown' }}</strong></td><td>{{ $isEn ? 'Cell state: never decided. Default for new cells.' : 'Cel-status: nooit beslist. Standaard voor nieuwe cellen.' }}</td></tr>
	<tr><td><strong>Vault</strong></td><td>{{ $isEn ? 'Encrypted credential storage. Per-user ACL; every access is logged.' : 'Versleutelde credential-opslag. Per-gebruiker ACL; elke toegang wordt gelogd.' }}</td></tr>
</table>

<div class="footnote">
	{{ $isEn
		? 'AccessGuard is a product of Beter Geregeld ICT. This manual is generated automatically, the digital version on the website is always authoritative.'
		: 'AccessGuard is een product van Beter Geregeld ICT. Deze handleiding wordt automatisch gegenereerd, de digitale versie op de website is altijd leidend.' }}
	<br>
	{{ $isEn ? 'Contact:' : 'Contact:' }} info@betergeregeld.com · {{ $isEn ? 'Manual generated:' : 'Handleiding gegenereerd:' }} {{ $generatedAt }}
</div>

</body>
</html>
