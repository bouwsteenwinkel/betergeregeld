<?php

namespace Database\Seeders\Blog;

use Illuminate\Database\Seeder;

class BlogPrivacySeeder extends Seeder
{
	public function run(): void
	{
		BlogSeedHelper::seedCluster(
			[
				'slug' => 'avg-privacy',
				'name' => 'AVG & privacy',
				'pillar_title' => 'AVG-compliance voor MKB zonder eigen jurist',
				'intro' => 'Verwerkersregister, bewaartermijnen, DPIA, datalek-melding, cookies, marketing-consent. Alles wat een MKB praktisch moet regelen zonder dat het een dagtaak wordt.',
				'sort_order' => 70,
			],
			self::posts(),
		);
	}

	private static function posts(): array
	{
		return [
			[
				'slug' => 'avg-compliance-mkb',
				'title' => 'AVG-compliance voor MKB: het praktische minimum',
				'excerpt' => 'AVG kost niet €10.000 en vereist geen FG tot een bepaalde grootte. Dit is wat elk MKB minimaal heeft staan — gebaseerd op wat de AP daadwerkelijk controleert.',
				'is_pillar' => true,
				'featured' => true,
				'tags' => ['avg', 'privacy', 'compliance', 'mkb'],
				'published_offset_days' => 20,
				'body' => <<<'HTML'
<p>De AVG (GDPR) geldt voor elk bedrijf dat persoonsgegevens verwerkt. Voor een MKB betekent dat: iedereen. Maar het hoeft geen €10k-consultancy-traject te worden.</p>

<h2>De acht dingen die je nodig hebt</h2>
<ol>
  <li><a href="/nl/blog/verwerkersregister-avg">Verwerkersregister</a> — wat verwerk je, waarvoor, met welke grondslag.</li>
  <li><a href="/nl/blog/verwerkersovereenkomsten-mkb">Verwerkersovereenkomsten (DPA's)</a> met elke leverancier die data voor jou raakt.</li>
  <li><a href="/nl/blog/privacyverklaring-mkb">Privacyverklaring</a> op je website.</li>
  <li>Procedure voor <a href="/nl/blog/rechten-betrokkenen-avg">rechten van betrokkenen</a> (inzage, rectificatie, verwijdering).</li>
  <li><a href="/nl/blog/cookies-consent-mkb">Cookie-consent</a> op je website.</li>
  <li>Procedure voor <a href="/nl/blog/datalek-melden-avg">datalek-melding</a> (72-uurs-termijn).</li>
  <li><a href="/nl/blog/bewaartermijnen-mkb">Bewaartermijnen</a> per data-categorie gedocumenteerd.</li>
  <li>Basis-training voor medewerkers over phishing, wachtwoorden, gegevensdeling.</li>
</ol>

<h2>Wanneer heb je een FG (functionaris gegevensbescherming) nodig?</h2>
<ul>
  <li>Overheidsorganisaties — altijd.</li>
  <li>Kern-activiteit is grootschalige monitoring (cameratoezicht-bedrijven, marketing-trackers).</li>
  <li>Kern-activiteit is verwerking van "bijzondere categorieën" (zorg, strafrechtelijk).</li>
</ul>
<p>De meeste MKB's zitten niet in deze categorieën. Je kunt een externe FG op uurbasis inhuren als je wel onder de plicht valt.</p>

<h2>DPIA: wanneer?</h2>
<p>Een <a href="/nl/blog/dpia-wanneer-hoe">DPIA</a> is nodig bij hoog-risico-verwerkingen. Voor typisch MKB zelden, tenzij je met gezondheidsgegevens, biometrie, of grootschalige profilering werkt.</p>

<h2>Sanctie-risico</h2>
<p>Boetes gaan tot 4% van wereldwijde omzet. In de praktijk: de AP is strenger bij kleine MKB's geworden sinds 2023. Meest voorkomende bevindingen: geen verwerkersregister, geen DPA's, te brede cookies.</p>

<h2>Overlap met ISO 27001</h2>
<p>Ben je bezig met <a href="/nl/blog/iso-27001-mkb-zonder-consultants">ISO 27001</a>? Dan is 40% van het AVG-werk al gedaan. Andersom geldt evenzo.</p>
HTML,
			],

			[
				'slug' => 'verwerkersregister-avg',
				'title' => 'Verwerkersregister opstellen: wat erin moet en wat juist niet',
				'excerpt' => 'Elk MKB met medewerkers heeft een verwerkersregister nodig. De AP controleert hier bij bijna elke inspectie. Hier het sjabloon + wat erin moet.',
				'tags' => ['verwerkersregister', 'avg', 'documentatie'],
				'published_offset_days' => 30,
				'body' => <<<'HTML'
<p>Het verwerkersregister (artikel 30 AVG) is het centrale document. Als de AP komt kijken is dit meestal de eerste vraag: "laat me uw register zien."</p>

<h2>Per verwerking leg je vast</h2>
<ul>
  <li>Doel (bijv. "personeelsadministratie").</li>
  <li>Categorieën betrokkenen (medewerkers, sollicitanten, klanten).</li>
  <li>Categorieën persoonsgegevens (naam, e-mail, BSN, bankrekening).</li>
  <li>Juridische grondslag (contract, toestemming, gerechtvaardigd belang, wettelijke plicht).</li>
  <li>Ontvangers (wie krijgt dit — interne rollen, externe partijen).</li>
  <li>Bewaartermijn (zie <a href="/nl/blog/bewaartermijnen-mkb">bewaartermijnen</a>).</li>
  <li>Beveiligingsmaatregelen (tech + organisatorisch).</li>
  <li>Indien van toepassing: doorgifte buiten EER.</li>
</ul>

<h2>Voorbeelden van verwerkingen in een MKB</h2>
<ul>
  <li>Personeelsadministratie.</li>
  <li>Klantdossiers / CRM.</li>
  <li>Facturatie-administratie.</li>
  <li>Nieuwsbrief-lijst.</li>
  <li>Website-bezoekersgegevens (analytics).</li>
  <li>Cookies / tracking.</li>
  <li>Cameratoezicht bij kantoor.</li>
  <li>Sollicitanten-administratie.</li>
</ul>

<h2>Formaat</h2>
<p>Spreadsheet of Notion-database is prima. Geen vereiste rond specifieke tooling. Centraal, actueel, per categorie één rij.</p>

<h2>Onderhoud</h2>
<p>Elk kwartaal quick-check: nieuwe verwerkingen toegevoegd? Zijn er stopgezette verwerkingen? Jaarlijks volledige review.</p>

<p>Zie ook: <a href="/nl/blog/avg-compliance-mkb">AVG-pillar</a>, <a href="/nl/blog/verwerkersovereenkomsten-mkb">verwerkersovereenkomsten</a>.</p>
HTML,
			],

			[
				'slug' => 'verwerkersovereenkomsten-mkb',
				'title' => 'Verwerkersovereenkomsten (DPA\'s): wie, wanneer, en niet overdoen',
				'excerpt' => 'Elke SaaS die persoonsgegevens voor jou verwerkt heeft een DPA nodig. Meestal staat hij klaar op hun site. Hier de check zodat je niet na een jaar 40 losse PDF\'s hebt.',
				'tags' => ['dpa', 'verwerkersovereenkomst', 'avg', 'leveranciers'],
				'published_offset_days' => 38,
				'body' => <<<'HTML'
<p>Een verwerkersovereenkomst (Data Processing Agreement, DPA) is verplicht tussen jou (verwerkingsverantwoordelijke) en elke leverancier die persoonsgegevens namens jou verwerkt.</p>

<h2>Met wie moet je een DPA?</h2>
<ul>
  <li>Je boekhoudpakket (verwerkt klanten- en personeelsdata).</li>
  <li>Je CRM.</li>
  <li>Je HR-systeem.</li>
  <li>Je e-mail marketing (MailChimp, Mailerlite, ActiveCampaign).</li>
  <li>Je hosting + cloudopslag (M365, Google Workspace, AWS).</li>
  <li>Je CDN / security (Cloudflare).</li>
  <li>Je klantsupport-tool (Intercom, Zendesk, Help Scout).</li>
  <li>Je accountantskantoor (als zij jouw data verwerken).</li>
</ul>

<h2>Met wie NIET?</h2>
<ul>
  <li>Je internetprovider (zij zijn geen verwerker).</li>
  <li>Je telefoonleverancier.</li>
  <li>Je betaalprovider (bank is "third controller", geen verwerker).</li>
</ul>

<h2>Inhoud DPA</h2>
<p>De meeste grote leveranciers hebben een voor-opgestelde DPA online. Download, onderteken digitaal. Inhoud:</p>
<ul>
  <li>Doel en duur van verwerking.</li>
  <li>Categorieën data en betrokkenen.</li>
  <li>Beveiligingsmaatregelen leverancier.</li>
  <li>Sub-verwerkers (welke AWS-regio, welke externe).</li>
  <li>Meldplicht bij incidenten.</li>
  <li>Assistentie bij rechten betrokkenen.</li>
</ul>

<h2>Archivering</h2>
<p>Alle DPA's in één folder, met datum ondertekening en versie. Bij leveranciers-wissel: DPA met nieuwe partij, oude blijft in archief voor bewaartermijn.</p>

<p>Zie ook: <a href="/nl/blog/avg-compliance-mkb">AVG-pillar</a>, <a href="/nl/blog/sub-verwerkers-buiten-eu">sub-verwerkers buiten EU</a>.</p>
HTML,
			],

			[
				'slug' => 'privacyverklaring-mkb',
				'title' => 'Privacyverklaring op je website: welke sjablonen werken?',
				'excerpt' => 'Je privacyverklaring hoeft geen 15 pagina\'s juridisch koeterwaals te zijn. Leesbaar, eerlijk, compleet — hier de structuur.',
				'tags' => ['privacyverklaring', 'avg', 'website', 'transparantie'],
				'published_offset_days' => 46,
				'body' => <<<'HTML'
<p>Een privacyverklaring op je website moet bezoekers vertellen wat je met hun data doet. In mensentaal. De AP heeft expliciet gezegd dat juridisch jargon geen doel is.</p>

<h2>Structuur die werkt</h2>
<ol>
  <li><strong>Wie zijn we?</strong> Bedrijfsnaam, KvK, contactgegevens, FG (als je die hebt).</li>
  <li><strong>Welke data verzamelen we?</strong> Per kanaal (website, nieuwsbrief, contactform, klantportaal).</li>
  <li><strong>Waarom?</strong> Grondslag per verwerking.</li>
  <li><strong>Hoe lang bewaren we het?</strong> Per categorie.</li>
  <li><strong>Met wie delen we het?</strong> Leveranciers, overheden, derden.</li>
  <li><strong>Gaan we buiten EER?</strong> Ja/nee, zo ja, met welke waarborgen.</li>
  <li><strong>Je rechten.</strong> Inzage, rectificatie, verwijdering, bezwaar, dataportabiliteit.</li>
  <li><strong>Hoe beveiligen we het?</strong> TLS, toegangsbeheer, ISO, enz.</li>
  <li><strong>Cookies.</strong> Link naar cookie-statement.</li>
  <li><strong>Klachten.</strong> AP-contactgegevens.</li>
  <li><strong>Wijzigingen.</strong> Hoe je wijzigingen communiceert.</li>
</ol>

<h2>Wat doe je niet</h2>
<ul>
  <li>€99-sjabloon van een template-site klakkeloos kopiëren — past waarschijnlijk niet bij jouw verwerkingen.</li>
  <li>"Wij verkopen uw data niet" zonder uit te leggen wat je WEL doet.</li>
  <li>Verwijzen naar een 20-pagina's PDF zonder webpage.</li>
</ul>

<h2>Update bij wijzigingen</h2>
<p>Nieuwe verwerker, nieuwe data-categorie, nieuwe cookie? Privacyverklaring actualiseren. Versie-nummer en datum bovenaan.</p>

<p>Zie ook: <a href="/nl/blog/avg-compliance-mkb">AVG-pillar</a>, <a href="/nl/blog/cookies-consent-mkb">cookies-consent</a>.</p>
HTML,
			],

			[
				'slug' => 'cookies-consent-mkb',
				'title' => 'Cookie-consent in 2026: wat is veranderd, wat mag nog, wat moet weg?',
				'excerpt' => 'De cookie-wetgeving is sinds 2023 strikt gehandhaafd. Veel oude cookie-banners voldoen niet meer. Hier de actuele regels en het drie-kolommen-model.',
				'tags' => ['cookies', 'consent', 'avg', 'website'],
				'published_offset_days' => 54,
				'body' => <<<'HTML'
<p>De regels rond cookies zijn sinds de handhavingsgolf 2023-2024 strak. Oude "alles op accept zetten"-banners werken niet meer.</p>

<h2>Het drie-kolommen-model</h2>
<ol>
  <li><strong>Noodzakelijke cookies:</strong> geen consent nodig. Session-cookies, winkelmandje, login.</li>
  <li><strong>Functionele cookies:</strong> cruciaal voor user-experience (taal-voorkeur, dark mode). Consent is vaak niet nodig als ze echt niet tracken.</li>
  <li><strong>Analytische / marketing:</strong> consent VERPLICHT. Opt-in, geen opt-out. "Weiger alles" moet even prominent zijn als "Accepteer alles".</li>
</ol>

<h2>Wat mag geen cookie-banner meer?</h2>
<ul>
  <li>Alleen een "Accept"-knop.</li>
  <li>Door-scrollen = consent.</li>
  <li>Pre-checked marketing-categorieën.</li>
  <li>Onbeperkte "legitiem belang" voor tracking-cookies.</li>
  <li>Cookies plaatsen VOORDAT consent gegeven is.</li>
</ul>

<h2>Wat moet in de cookie-banner?</h2>
<ul>
  <li>Categorieën toegelicht.</li>
  <li>Keuze per categorie (minimum: noodzakelijk aan, marketing/analytics apart).</li>
  <li>"Accept All", "Reject All", en "Manage" met gelijke visuele prominence.</li>
  <li>Link naar volledige cookie-statement.</li>
  <li>Mogelijkheid om later consent te wijzigen (bijv. via footer-link).</li>
</ul>

<h2>Tools</h2>
<p>Google Analytics 4, Meta Pixel, LinkedIn Insight — allemaal consent-required. Gebruik Google Consent Mode V2 als je GA4 draait.</p>

<h2>Sanctie-risico</h2>
<p>De AP heeft 2023-2024 verschillende boetes uitgeschreven voor cookie-banners. Typisch €25.000-100.000 voor MKB's die zijn opgevallen. Niet meer iets om te negeren.</p>

<p>Zie ook: <a href="/nl/blog/avg-compliance-mkb">AVG-pillar</a>, <a href="/nl/blog/privacyverklaring-mkb">privacyverklaring</a>.</p>
HTML,
			],

			[
				'slug' => 'datalek-melden-avg',
				'title' => 'Datalek: wanneer melden, wanneer niet, binnen 72 uur',
				'excerpt' => 'Niet elk incident is een datalek. Niet elk datalek hoeft naar de AP. Hier de beslisboom en een voorbeeld-meldingstemplate.',
				'tags' => ['datalek', 'incident', 'avg', 'ap'],
				'published_offset_days' => 62,
				'body' => <<<'HTML'
<p>Een datalek (breach) is een inbreuk op de beveiliging waarbij persoonsgegevens zijn vernietigd, verloren, gewijzigd, openbaar gemaakt of toegankelijk voor onbevoegden. Wanneer melden?</p>

<h2>Beslisboom</h2>
<ol>
  <li><strong>Is het een datalek?</strong> Bij twijfel: ja.</li>
  <li><strong>Risico voor betrokkenen?</strong> Gegevens publiek? Financiële schade mogelijk? Identiteitsfraude risico?</li>
  <li><strong>Laag risico (bijv. encrypted device verloren):</strong> niet naar AP melden, wel intern registreren in <a href="/nl/blog/incidentenlog-opzetten">incidenten-log</a>.</li>
  <li><strong>Aanzienlijk risico:</strong> binnen 72 uur melden bij AP.</li>
  <li><strong>Hoog risico voor betrokkenen:</strong> ook betrokkenen direct informeren.</li>
</ol>

<h2>Wat melden?</h2>
<ul>
  <li>Aard van het datalek.</li>
  <li>Categorieën en aantal betrokkenen.</li>
  <li>Categorieën en aantal persoonsgegevens.</li>
  <li>Gevolgen van het datalek.</li>
  <li>Genomen of voorgestelde maatregelen.</li>
  <li>Contactgegevens FG of contactpersoon.</li>
</ul>

<h2>De 72-uurs-klok</h2>
<p>Vanaf het moment dat je er kennis van kreeg — niet vanaf het moment dat het gebeurde. Weekend telt mee. Gedeeltelijke melding mag met aanvulling later.</p>

<h2>Waar meld je?</h2>
<p>autoriteitpersoonsgegevens.nl — online meldformulier datalekken. Bewaar een kopie.</p>

<h2>Wat te doen binnen de organisatie</h2>
<ul>
  <li>Team in actie: security-lead, directeur, communicatie.</li>
  <li>Onderzoek: wat is er precies gebeurd?</li>
  <li>Containment: stop de lek.</li>
  <li>Forensische vastlegging: evidence voor onderzoek.</li>
  <li>Communicatie voorbereiden: intern, extern, media.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/incidentenlog-opzetten">incidentenlog opzetten</a>, <a href="/nl/blog/incident-response-mkb">incident response</a>.</p>
HTML,
			],

			[
				'slug' => 'dpia-wanneer-hoe',
				'title' => 'DPIA — Data Protection Impact Assessment: wanneer wel, wanneer overslaan?',
				'excerpt' => 'Een DPIA klinkt als iets voor grote enterprises. Voor een MKB is hij zelden nodig, maar wél bij een paar specifieke situaties. Hier de beslisboom.',
				'tags' => ['dpia', 'avg', 'risico-analyse'],
				'published_offset_days' => 70,
				'body' => <<<'HTML'
<p>Een DPIA is een gestructureerde risico-analyse bij verwerkingen met hoog privacy-risico. De AP heeft een lijst van situaties waar een DPIA verplicht is.</p>

<h2>Wanneer verplicht?</h2>
<ul>
  <li>Grootschalige verwerking van "bijzondere categorieën" (medische data, etnische afkomst, religie).</li>
  <li>Stelselmatige observatie van openbaar toegankelijke plaats (cameratoezicht centrum-lokatie).</li>
  <li>Profilering met rechtsgevolgen voor betrokkenen (automatische besluitvorming).</li>
  <li>Biometrie voor identificatie (vingerafdruk-lock, gezichtsherkenning).</li>
  <li>Grootschalige locatietracking.</li>
  <li>Verbindingen tussen persoonsgegevens uit verschillende bronnen (data-matching).</li>
</ul>

<h2>Wanneer waarschijnlijk niet?</h2>
<ul>
  <li>Standaard HR-administratie.</li>
  <li>Standaard klant-CRM.</li>
  <li>Facturatie.</li>
  <li>Nieuwsbrief met expliciete consent.</li>
</ul>

<h2>Hoe voer je een DPIA uit?</h2>
<ol>
  <li>Beschrijving van de verwerking: waarom, hoe, welke data.</li>
  <li>Beoordeling noodzakelijkheid en proportionaliteit.</li>
  <li>Risico-identificatie voor betrokkenen.</li>
  <li>Maatregelen die het risico beperken.</li>
  <li>Restrisico en beoordeling.</li>
</ol>

<h2>Met wie overleg je?</h2>
<ul>
  <li>FG indien aanwezig.</li>
  <li>Betrokkenen (werknemersvertegenwoordiging bij HR-verwerkingen).</li>
  <li>Directie tekent af.</li>
</ul>

<h2>Vooroverleg AP</h2>
<p>Bij significant restrisico na DPIA: verplicht de AP te raadplegen VOOR je met de verwerking begint. Typisch 6-10 weken doorlooptijd.</p>

<p>Zie ook: <a href="/nl/blog/avg-compliance-mkb">AVG-pillar</a>, <a href="/nl/blog/risicomanagement-iso-27001">risicomanagement</a>.</p>
HTML,
			],

			[
				'slug' => 'sub-verwerkers-buiten-eu',
				'title' => 'Sub-verwerkers buiten de EU: wat de Schrems-II-uitspraak nog steeds vraagt',
				'excerpt' => 'Gebruik je AWS, Google, of Microsoft? Dan gaat een deel van je data door de US. Sinds Schrems II is dat niet zomaar meer mag. Hier wat er nu wel werkt.',
				'tags' => ['sub-verwerkers', 'schrems-ii', 'avg', 'internationaal'],
				'published_offset_days' => 78,
				'body' => <<<'HTML'
<p>Data van EU-burgers buiten de EER laten verwerken mag alleen met expliciete waarborgen. De Schrems II-uitspraak (2020) heeft de oude Privacy Shield afgeschaft.</p>

<h2>De huidige paden</h2>
<ul>
  <li><strong>EU-US Data Privacy Framework (2023):</strong> vervanger van Privacy Shield. Geldt voor US-leveranciers die zich onder dit framework hebben gecertificeerd.</li>
  <li><strong>Standaard Contractuele Bepalingen (SCC's):</strong> Europese sjabloon-clausules tussen EU- en niet-EU-partij. Vaak in combinatie met Transfer Impact Assessment.</li>
  <li><strong>Bindende Bedrijfsregels (BCR's):</strong> binnen grote concerns, niet voor typische MKB-situatie.</li>
</ul>

<h2>Microsoft, Google, AWS — hoe zit het?</h2>
<ul>
  <li>Microsoft 365: EU Data Boundary betekent dat EU-klant-data in EU blijft voor de meeste services. Microsoft heeft DPF-certificering voor waar het wel USA raakt.</li>
  <li>Google Workspace: vergelijkbaar — EU-data blijft in EU, DPF voor US-onderdelen.</li>
  <li>AWS: per regio configureerbaar. Kies EU-regio (Frankfurt, Amsterdam, Ierland). Voor managed services let op waar de control-plane zit.</li>
  <li>Cloudflare: data kan door global edge lopen. Business-tier heeft "EU-only" optie.</li>
</ul>

<h2>Kleinere US-SaaS (Slack, Notion, Intercom)</h2>
<p>Meeste hebben nu DPF-certificering. Check hun DPA of trust-pagina. Voor echt gevoelige data overweeg een EU-gebaseerd alternatief.</p>

<h2>Documenteren</h2>
<p>In je <a href="/nl/blog/verwerkersregister-avg">verwerkersregister</a>: per verwerking aangeven of data buiten EU gaat en met welke grondslag.</p>

<p>Zie ook: <a href="/nl/blog/avg-compliance-mkb">AVG-pillar</a>, <a href="/nl/blog/verwerkersovereenkomsten-mkb">DPA's</a>.</p>
HTML,
			],

			[
				'slug' => 'rechten-betrokkenen-avg',
				'title' => 'Rechten van betrokkenen: inzage, rectificatie, verwijdering — een werkbare procedure',
				'excerpt' => 'Een klant wil zijn data zien, of juist verwijderen. Je hebt 30 dagen. Hier de procedure die werkt zonder dat een request een halve week kost.',
				'tags' => ['rechten-betrokkenen', 'avg', 'procedure'],
				'published_offset_days' => 86,
				'body' => <<<'HTML'
<p>De AVG geeft betrokkenen concrete rechten. Een verzoek moet je binnen 30 dagen afhandelen (één keer verlengbaar met 60). Hier wat je vooraf moet regelen.</p>

<h2>De vijf belangrijkste rechten</h2>
<ol>
  <li><strong>Recht op inzage:</strong> welke data heb je van mij?</li>
  <li><strong>Recht op rectificatie:</strong> verander deze data want het klopt niet.</li>
  <li><strong>Recht op verwijdering:</strong> verwijder alles van mij (met uitzonderingen).</li>
  <li><strong>Recht op dataportabiliteit:</strong> geef me mijn data in standaard-formaat zodat ik naar een andere leverancier kan.</li>
  <li><strong>Recht op bezwaar:</strong> tegen specifieke verwerking (vaak marketing).</li>
</ol>

<h2>Wat heb je nodig om dit te leveren?</h2>
<ul>
  <li>Centraal postbus/formulier waar verzoeken binnenkomen.</li>
  <li>Identiteitsverificatie-procedure (niet zomaar data vrijgeven).</li>
  <li>Data-mapping: waar staat welke klant-data? (Koppelt terug naar <a href="/nl/blog/verwerkersregister-avg">verwerkersregister</a>.)</li>
  <li>Export-functie in je tools (CRM, boekhouding, ticket-systeem).</li>
  <li>Verwijder-procedure per systeem, inclusief back-ups.</li>
</ul>

<h2>Uitzonderingen op verwijdering</h2>
<ul>
  <li>Fiscale bewaarplicht (7 jaar voor financiële data).</li>
  <li>Juridische verdediging van lopende zaken.</li>
  <li>Vitaal belang betrokkenen of derden.</li>
  <li>Historisch onderzoek in het algemeen belang.</li>
</ul>

<h2>Rapportage aan betrokkene</h2>
<p>Altijd schriftelijk antwoord binnen 30 dagen. Ook bij weigering, met motivatie en verwijzing naar AP-klachtrecht.</p>

<p>Zie ook: <a href="/nl/blog/avg-compliance-mkb">AVG-pillar</a>, <a href="/nl/blog/bewaartermijnen-mkb">bewaartermijnen</a>.</p>
HTML,
			],

			[
				'slug' => 'marketing-consent-avg',
				'title' => 'Marketing consent: e-mail, WhatsApp, retargeting — wat mag je nog?',
				'excerpt' => 'Je nieuwsbrief, je aanbiedingsmails, je retargeting-pixels — allemaal hebben ze een consent-basis nodig. Hier de concrete regels per kanaal.',
				'tags' => ['marketing', 'consent', 'avg', 'email-marketing'],
				'published_offset_days' => 94,
				'body' => <<<'HTML'
<p>Marketing en AVG raken elkaar op drie punten: hoe je contact opneemt, op basis waarvan, en hoe iemand zich kan afmelden.</p>

<h2>E-mail</h2>
<ul>
  <li>Opt-in verplicht voor niet-bestaande klanten. Pre-checked box telt niet.</li>
  <li>Bestaande klanten ("soft opt-in"): wel mogelijk voor soortgelijke producten. 12 maanden na laatste aankoop is de grens.</li>
  <li>Afmelding in elke mail. 1-klik moet werken.</li>
  <li>Log van consent: wanneer, op welke URL, met welke tekst.</li>
</ul>

<h2>WhatsApp / SMS</h2>
<ul>
  <li>Expliciete opt-in, geen aannames.</li>
  <li>Kanaal-specifieke consent — WhatsApp-opt-in betekent niet e-mail-opt-in.</li>
  <li>Afmelding eenvoudig mogelijk.</li>
</ul>

<h2>Telefoon / cold calling B2C</h2>
<p>Bel-me-niet-register checken verplicht. Boete bij niet-checken: €10.000+.</p>

<h2>Telefoon / cold calling B2B</h2>
<p>Mag in beperkte mate. Na afmelding: registreren en niet meer bellen.</p>

<h2>Retargeting / cookies</h2>
<p>Zie <a href="/nl/blog/cookies-consent-mkb">cookie-consent</a>. Geen pixels voor consent.</p>

<h2>Social-media targeting</h2>
<p>Custom audiences uit e-mail-lijst: mag alleen met consent voor die verwerking. Lookalikes zijn een ander verhaal (aggregated).</p>

<h2>Wat niet werkt</h2>
<ul>
  <li>Lijst kopen en mailen.</li>
  <li>"Je kunt je afmelden" zonder opt-in.</li>
  <li>Cold-mail naar B2B met "legitiem belang" ongefundeerd.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/avg-compliance-mkb">AVG-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'bewaartermijnen-mkb',
				'title' => 'Bewaartermijnen per data-categorie in het MKB',
				'excerpt' => 'Hoe lang bewaar je klantdata, sollicitanten, facturen, CCTV-beelden? Hier de belangrijkste categorieën in een overzichtstabel, met bron per termijn.',
				'tags' => ['bewaartermijnen', 'avg', 'retentie'],
				'published_offset_days' => 102,
				'body' => <<<'HTML'
<p>Bewaren mag niet langer dan nodig. Wat "nodig" is, is per categorie verschillend. Hier de richtlijnen voor MKB-data-categorieën.</p>

<h2>Financieel (fiscaal)</h2>
<ul>
  <li>Facturen, bonnen, jaarrekeningen: 7 jaar (fiscaal).</li>
  <li>Onroerend goed-gegevens: 10 jaar (fiscaal).</li>
  <li>Loonadministratie: 5 jaar (na einde dienstverband).</li>
</ul>

<h2>Klant-data</h2>
<ul>
  <li>Klantdossier lopend contract: duur contract + juridische bewaarplicht.</li>
  <li>Klantdossier na einde contract: 2 jaar (garantie-claims, follow-up), dan anonimiseren.</li>
  <li>Klanthistorie facturen: 7 jaar (fiscaal).</li>
  <li>Support-tickets: 1-2 jaar.</li>
  <li>CRM-notities: 2 jaar na laatste contact.</li>
</ul>

<h2>HR</h2>
<ul>
  <li>Salarisgegevens: 7 jaar (fiscaal).</li>
  <li>Arbeidscontract: 7 jaar na einde.</li>
  <li>Functioneringsgesprekken: 2 jaar na einde dienstverband.</li>
  <li>Verzuim/ziekte: 2 jaar na einde dienstverband.</li>
  <li>Sollicitanten afgewezen: max 4 weken, met consent 1 jaar.</li>
  <li>Zie <a href="/nl/blog/bewaartermijnen-personeelsdossier">personeelsdossier</a>.</li>
</ul>

<h2>Website / marketing</h2>
<ul>
  <li>Nieuwsbrief-inschrijvingen: zolang opt-in actief is.</li>
  <li>Analytics-logs (GA): 14 maanden standaard, aanpasbaar.</li>
  <li>Webformulieren: 30 dagen tenzij concreet verwerkingsdoel.</li>
  <li>Cookie-consent-log: 3 jaar.</li>
</ul>

<h2>CCTV / cameratoezicht</h2>
<ul>
  <li>Maximaal 4 weken, tenzij concreet incident.</li>
</ul>

<h2>Incidenten</h2>
<ul>
  <li>Datalek-registratie: 3 jaar.</li>
  <li>Incident-log: 3-5 jaar (zie <a href="/nl/blog/incidentenlog-opzetten">incidentenlog</a>).</li>
</ul>

<p>Zie ook: <a href="/nl/blog/avg-compliance-mkb">AVG-pillar</a>, <a href="/nl/blog/verwerkersregister-avg">verwerkersregister</a>.</p>
HTML,
			],

			[
				'slug' => 'ip-adres-logs-avg',
				'title' => 'IP-adressen loggen onder AVG: pseudoniem, persoonsgegeven, en wat mag?',
				'excerpt' => 'Een IP-adres is een persoonsgegeven onder AVG. Security-logs hebben ze vaak weken of maanden nodig. Hoe verenig je dat met bewaarprincipes?',
				'tags' => ['ip-adres', 'logs', 'avg', 'security'],
				'published_offset_days' => 110,
				'body' => <<<'HTML'
<p>Een IP-adres wordt onder de AVG gezien als persoonsgegeven. Security-logs die IP-adressen vastleggen vallen onder verwerking.</p>

<h2>Toegestane grondslagen</h2>
<ul>
  <li><strong>Gerechtvaardigd belang:</strong> security, fraudebestrijding, troubleshooting. Meest gebruikt.</li>
  <li><strong>Wettelijke plicht:</strong> bij specifieke wetgeving (financieel toezicht).</li>
  <li><strong>Toestemming:</strong> zelden nodig voor security-logs.</li>
</ul>

<h2>Bewaartermijn</h2>
<ul>
  <li>Web-access logs: 30 dagen standaard, tot 90 voor forensische doeleinden.</li>
  <li>Auth-logs (login attempts): 90 dagen - 1 jaar.</li>
  <li>Firewall logs: 30-90 dagen.</li>
  <li>Audit-logs voor compliance: 3 jaar (ISO-vereist).</li>
</ul>
<p>Langer bewaren = betere motivatie in verwerkingsregister én risico-analyse waarom het nodig is.</p>

<h2>Pseudonimisering</h2>
<p>Kun je de laatste octet anonimiseren (192.168.1.x → 192.168.1.0/24)? Voor analytics voldoende. Voor security meestal niet — je wilt een specifiek IP kunnen correleren.</p>

<h2>Rechten van betrokkenen</h2>
<p>Iemand vraagt "welke logs heb je van mij?" — je moet kunnen zoeken op hun IP én hun account-ID. Leg in je systeem vast hoe je dat doet.</p>

<p>Zie ook: <a href="/nl/blog/avg-compliance-mkb">AVG-pillar</a>, <a href="/nl/blog/bewaartermijnen-mkb">bewaartermijnen</a>.</p>
HTML,
			],
		];
	}
}
