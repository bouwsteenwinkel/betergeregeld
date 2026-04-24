<?php

namespace Database\Seeders\Blog;

use Illuminate\Database\Seeder;

class BlogPdfRedactSeeder extends Seeder
{
	public function run(): void
	{
		BlogSeedHelper::seedCluster(
			[
				'slug' => 'pdf-redaction',
				'name' => 'PDF redactie',
				'pillar_title' => 'PDF redactie voor MKB — gevoelige data onzichtbaar maken zonder Adobe-abonnement',
				'intro' => 'Waarom zwart arceren niet werkt, hoe je CV\'s, contracten en rechterlijke documenten veilig leesbaar maakt voor derden, en wat AVG van je vraagt.',
				'sort_order' => 90,
			],
			self::posts(),
		);
	}

	private static function posts(): array
	{
		return [
			[
				'slug' => 'pdf-redactie-voor-mkb-complete-gids',
				'title' => 'PDF redactie voor MKB: de complete gids',
				'excerpt' => 'Een PDF redigeren betekent: gevoelige data permanent onzichtbaar maken. Niet een zwart vlak eroverheen slepen — dat kun je er in 30 seconden weer uit halen. Deze gids legt het echte proces uit.',
				'is_pillar' => true,
				'featured' => true,
				'tags' => ['pdf-redactie', 'avg', 'privacy', 'mkb'],
				'published_offset_days' => 25,
				'body' => <<<'HTML'
<p>PDF-redactie is het permanent verwijderen van tekst, afbeeldingen of metadata uit een document zodat ze niet meer te recupereren zijn. Klinkt simpel — is het niet. Elk jaar staan er in kranten verhalen over organisaties die een "gecensureerde" PDF publiceerden waar de gecensureerde tekst in 10 seconden weer zichtbaar was.</p>

<h2>Waarom "zwarte balken" niet werken</h2>
<p>Een zwarte rechthoek over tekst slepen (in Word, Preview, of zelfs Acrobat zonder redact-functie) doet twee dingen: het legt een object over de originele tekst. De tekst zelf blijft in het document staan. Copy-paste, of de rechthoek verwijderen, en de data is zichtbaar. Zie <a href="/nl/blog/zwarte-balken-pdf-waarom-niet">waarom zwarte balken niet werken</a>.</p>

<h2>Wat is echte redactie?</h2>
<ol>
  <li><strong>Tekst wordt vervangen</strong> door een vlak + placeholder (bijv. "[geredigeerd]").</li>
  <li><strong>Metadata wordt gestript</strong>: auteur, revisies, commentaren, track-changes.</li>
  <li><strong>Onderliggende bestandsstructuur</strong> wordt opgeschoond — oude objecten die niet meer zichtbaar zijn maar technisch nog in de PDF staan, worden weggegooid.</li>
  <li><strong>Resultaat is een nieuwe PDF</strong> waar de oorspronkelijke data niet in voorkomt.</li>
</ol>

<h2>Use-cases in het MKB</h2>
<ul>
  <li><a href="/nl/blog/cv-redactie-avg">CV-redactie</a> voordat je ze deelt met een klant of doorstuurt voor een project.</li>
  <li><a href="/nl/blog/contract-redactie-voor-referentie">Contracten voor referentie</a> — bedragen en klantnamen weg voor sales-pitches.</li>
  <li><a href="/nl/blog/gerechtelijke-stukken-redactie">Gerechtelijke stukken</a> — BSN, geboortedata, contactgegevens weg.</li>
  <li>Subsidieaanvragen met interne bedragen verwijderd.</li>
  <li>Externe audit-rapporten met interne code-verwijzingen geanonimiseerd.</li>
  <li>Medische verslagen voor andere zorgverleners.</li>
</ul>

<h2>Juridisch kader (AVG)</h2>
<p>Pseudonimiseren en anonimiseren staan expliciet in AVG art. 4. Anonimiseren (=redactie waarbij identificatie onmogelijk is) haalt data buiten de AVG. Pseudonimiseren verminderd het risico maar blijft onder AVG. Zie <a href="/nl/blog/avg-redactie-pseudonimiseren">AVG-implicaties van redactie</a>.</p>

<h2>Wat je vergelijkt bij tool-keuze</h2>
<ul>
  <li>Permanent verwijderd (niet slepen / niet tekst-vervangen-door-sterren)?</li>
  <li>Metadata en hidden content worden gestript?</li>
  <li><a href="/nl/blog/redactie-audit-trail">Audit trail</a> — kun je later aantonen wat er is geredigeerd?</li>
  <li>OCR — werkt het op gescande (image-based) PDF's?</li>
  <li>Bulk / pattern mode — meerdere documenten tegelijk, of kun je patroon "alle BSN's" toepassen?</li>
  <li>Prijs / licentiemodel — zie <a href="/nl/blog/pdf-redactie-tools-vergelijking">tool-vergelijking</a>.</li>
</ul>

<p>Onze tool <a href="/nl/prijzen">PDF Redact</a> (Pro- en Business-plan) doet dit zonder €20/maand Adobe Pro-abonnement; Business-plan voegt pattern-mode + audit-log toe voor compliance-gedreven redactie.</p>

<p>Verder: <a href="/nl/blog/pdf-metadata-strippen">metadata strippen</a>, <a href="/nl/blog/ocr-redactie-gescande-pdf">OCR voor gescande PDF's</a>, <a href="/nl/blog/bsn-in-documenten-herkennen">BSN herkennen</a>, <a href="/nl/blog/redactie-na-offboarding">redactie na offboarding</a>.</p>
HTML,
			],

			[
				'slug' => 'zwarte-balken-pdf-waarom-niet',
				'title' => 'Waarom zwarte balken in PDF\'s niet werken (met voorbeeld)',
				'excerpt' => 'Twee seconden copy-paste maakt de "gecensureerde" tekst weer leesbaar. Hier de technische uitleg met concreet voorbeeld en wat je in plaats daarvan doet.',
				'tags' => ['pdf-redactie', 'security', 'data-lek'],
				'published_offset_days' => 33,
				'body' => <<<'HTML'
<p>PDF's hebben een lagen-model. Tekst en afbeeldingen zijn aparte objecten, overelkaar gestapeld. Wanneer je een rechthoek tekent over tekst, voeg je simpelweg een nieuw object bovenop toe — de tekst daaronder blijft volledig intact.</p>

<h2>De demonstratie</h2>
<ol>
  <li>Open een PDF waar iemand "zwarte balken" heeft gebruikt om tekst te verbergen.</li>
  <li>Selecteer het hele document (Ctrl+A).</li>
  <li>Kopieer naar klembord (Ctrl+C).</li>
  <li>Plak in een tekst-editor.</li>
  <li>Alle "gecensureerde" tekst is leesbaar.</li>
</ol>

<h2>Variaties die ook niet werken</h2>
<ul>
  <li><strong>Witte tekst op wit:</strong> zelfde probleem, tekst staat er nog.</li>
  <li><strong>Ondoorzichtige afbeelding over tekst:</strong> copy-paste haalt tekst onder de afbeelding vandaan.</li>
  <li><strong>Snipping-tool screenshot + zwart vlak:</strong> de screenshot is goed, maar als je die screenshot in een PDF exporteert verliest hij precisie, en metadata blijft staan.</li>
  <li><strong>Tekst markeren + achtergrondkleur zwart:</strong> text highlight, niet redactie.</li>
</ul>

<h2>Historische incidenten</h2>
<ul>
  <li>Diverse rechterlijke instanties publiceerden in 2010-2020 "gecensureerde" vonnissen die in secondes herleesbaar waren.</li>
  <li>TNO-rapport met gecensureerde namen — zelfde patroon.</li>
  <li>Wikileaks / Panama Papers — voorbeelden waar redactie-fouten tot lekken leidden.</li>
</ul>

<h2>Wat wel werkt</h2>
<p>Echte redactie-software verwijdert de onderliggende tekst-objecten uit de PDF, niet alleen bedek ze. Zie <a href="/nl/blog/pdf-redactie-voor-mkb-complete-gids">redactie-pillar</a> voor het proces.</p>

<p>Zie ook: <a href="/nl/blog/pdf-metadata-strippen">metadata strippen</a>, <a href="/nl/blog/redactie-audit-trail">audit trail</a>.</p>
HTML,
			],

			[
				'slug' => 'cv-redactie-avg',
				'title' => 'CV-redactie: wat haal je weg voor je hem doorstuurt?',
				'excerpt' => 'Een CV delen met een klant voor inzet? Check wat er weg moet volgens AVG, privacy en common sense. Plus een checklist om niet per ongeluk de geboortedatum te laten staan.',
				'tags' => ['cv-redactie', 'recruitment', 'avg', 'externe-partijen'],
				'published_offset_days' => 41,
				'body' => <<<'HTML'
<p>Je werkt met een freelancer of detacheringsbureau. Klant wil een CV zien. Voor je het PDF doorstuurt: wat moet eraf?</p>

<h2>Wat weg moet (meestal verplicht onder AVG)</h2>
<ul>
  <li>Adres (straat + huisnummer).</li>
  <li>Privé-telefoonnummer.</li>
  <li>Privé-mail.</li>
  <li>Geboortedatum (tenzij relevant voor de functie, zeldzaam).</li>
  <li>Foto (tenzij uitdrukkelijk gevraagd en gerechtvaardigd).</li>
  <li>BSN (mag sowieso nooit op een CV staan — zie <a href="/nl/blog/bsn-in-documenten-herkennen">BSN herkennen</a>).</li>
  <li>Burgerlijke staat.</li>
</ul>

<h2>Wat vaak weg moet (commercieel)</h2>
<ul>
  <li>Namen van huidige/recente werkgevers (concurrentiegevoelig).</li>
  <li>Specifieke klantnamen uit vorige projecten.</li>
  <li>Uurtarief of verwachte compensatie.</li>
  <li>LinkedIn-URL (tenzij juist de bedoeling).</li>
</ul>

<h2>Wat blijft</h2>
<ul>
  <li>Voornaam of initialen.</li>
  <li>Functietitel.</li>
  <li>Skills en technologieën.</li>
  <li>Ervaring in abstracte termen ("Nederlandse bank", "internationaal consultancy-bureau").</li>
  <li>Opleidingen.</li>
  <li>Certificeringen.</li>
</ul>

<h2>Hoe doe je het snel?</h2>
<ol>
  <li>Open PDF in <a href="/nl/prijzen">PDF Redact</a> of een andere tool met echte redactie.</li>
  <li>Gebruik patroon-matching voor telefoonnummers en e-mails.</li>
  <li>Selecteer handmatig naam-verwijzingen.</li>
  <li>Strip metadata (zie <a href="/nl/blog/pdf-metadata-strippen">metadata strippen</a>).</li>
  <li>Export als nieuw bestand.</li>
</ol>

<h2>Hoe bouw je een proces?</h2>
<ul>
  <li>Shared template voor redactie-patronen zodat iedereen hetzelfde doet.</li>
  <li>Twee-ogen-principe bij gevoelige gevallen.</li>
  <li>Bewaar origineel + geredigeerde versie met audit-link.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/pdf-redactie-voor-mkb-complete-gids">redactie-pillar</a>, <a href="/nl/blog/externe-partijen-en-consultants-toegang">externe partijen toegang</a>.</p>
HTML,
			],

			[
				'slug' => 'contract-redactie-voor-referentie',
				'title' => 'Contracten redigeren voor sales-referentie: wat blijft in, wat eruit?',
				'excerpt' => 'Je wilt een getekend contract laten zien aan een prospect als "kijk, bedrijf X werkt met ons". Wat mag, wat moet weg, en hoe voorkom je dat een prospect uitleest wat de vorige betaalde.',
				'tags' => ['contract-redactie', 'sales', 'nda'],
				'published_offset_days' => 49,
				'body' => <<<'HTML'
<p>Social proof met echte contracten werkt beter dan testimonials. Maar je mag geen contract zomaar delen. Hier wat erin moet blijven om geloofwaardig te zijn, en wat eruit moet.</p>

<h2>Wat blijft in</h2>
<ul>
  <li>Je eigen bedrijfsnaam (uiteraard).</li>
  <li>Algemene scope / type dienst ("levering van consulting rondom access management").</li>
  <li>Duur in termen ("12 maanden").</li>
  <li>Handtekening-blok (eventueel verzwart maar herkenbaar genoeg).</li>
</ul>

<h2>Wat eruit moet</h2>
<ul>
  <li>Naam van de klant (tenzij expliciet geschreven toestemming).</li>
  <li>Contactgegevens van klant-medewerkers.</li>
  <li>Bedragen en tarieven.</li>
  <li>SLA-specifieke bepalingen die concurrentiegevoelig zijn.</li>
  <li>Boeteclausules en -hoogtes.</li>
  <li>NDA-specifieke bepalingen.</li>
  <li>IP / eigendomsbepalingen (tenzij generiek).</li>
</ul>

<h2>NDA-check vooraf</h2>
<p>De meeste B2B-contracten bevatten een geheimhoudingsbepaling die ook de bestaansgegevens van het contract omvat. Dat betekent: je mag vaak niet eens zeggen dát je met klant X werkt, laat staan contract-scans delen. Check je NDA voor je gaat redigeren.</p>

<h2>Toestemming beter dan redactie</h2>
<p>Vraag je klant of ze als referentie ingezet mogen worden. Formele bevestiging per e-mail. Dan hoef je minder te redigeren en is de social proof sterker.</p>

<h2>Practicum</h2>
<ul>
  <li>Open origineel in PDF Redact.</li>
  <li>Pattern-mode: filter op klantnaam (ook in footers, page-numbers).</li>
  <li>Handmatig per pagina: bedragen, specifieke contactpersonen.</li>
  <li>Strip metadata — je wilt niet dat "Opgesteld door Advocatenkantoor X" ernaast staat.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/pdf-redactie-voor-mkb-complete-gids">redactie-pillar</a>, <a href="/nl/blog/pdf-metadata-strippen">metadata strippen</a>.</p>
HTML,
			],

			[
				'slug' => 'gerechtelijke-stukken-redactie',
				'title' => 'Redactie van gerechtelijke stukken: de specifieke regels',
				'excerpt' => 'Als je als advocaat, partij of persbureau rechtelijke stukken deelt, gelden er specifieke redactie-regels. Namen van minderjarigen, medische data, rechterlijke overwegingen — hier hoe je het doet.',
				'tags' => ['gerechtelijke-stukken', 'pdf-redactie', 'juridisch'],
				'published_offset_days' => 57,
				'body' => <<<'HTML'
<p>Rechterlijke stukken bevatten vaak hoog-gevoelige persoonsgegevens. Specifieke regels gelden voor welke partijen wel en niet geïdentificeerd mogen worden.</p>

<h2>Wat moet altijd weg</h2>
<ul>
  <li>Namen van minderjarigen (incl. initialen die te herleiden zijn).</li>
  <li>BSN en vergelijkbare identifiers.</li>
  <li>Medische gegevens van partijen (tenzij bepalend voor oordeel).</li>
  <li>Strafrechtelijke veroordelingen buiten dit dossier.</li>
  <li>Namen van slachtoffers (in strafrechtelijke procedures).</li>
</ul>

<h2>Wat afhankelijk is van procedure-type</h2>
<ul>
  <li><strong>Civiele procedure:</strong> partijnamen vaak publiek, maar kinderen en medische details geredigeerd.</li>
  <li><strong>Strafrecht:</strong> verdachten met initialen tenzij voldaan aan publicatiecriteria (ernstige feiten, hoger beroep, enz.).</li>
  <li><strong>Familierecht:</strong> alle partijnamen en kinderen vervangen door initialen of rollen ("moeder", "vader").</li>
  <li><strong>Bestuursrecht:</strong> wisselend, check rechtspraak-publicatiebeleid.</li>
</ul>

<h2>De rechtspraak.nl-aanpak</h2>
<p>De Hoge Raad en lagere instanties gebruiken een vast anonimiseringsprotocol. Wil je consistent zijn: kopieer hun format. Zie "anonimiseringsrichtlijn" op rechtspraak.nl.</p>

<h2>Tools-keuze</h2>
<p>Pattern-mode is essentieel voor volume-werk (advocatenkantoor met veel stukken). Kijk naar tools die regex-patterns ondersteunen voor namen, BSN's, postcodes. Onze <a href="/nl/prijzen">PDF Redact Business-plan</a> levert dit plus audit-trail.</p>

<p>Zie ook: <a href="/nl/blog/bsn-in-documenten-herkennen">BSN herkennen</a>, <a href="/nl/blog/pattern-mode-bulk-redactie">pattern mode bulk-redactie</a>.</p>
HTML,
			],

			[
				'slug' => 'pdf-metadata-strippen',
				'title' => 'PDF metadata strippen: waarom en wat erin zit',
				'excerpt' => 'Een PDF bevat vaak 10× meer data dan wat je ziet. Auteur, software-versie, edit-historie, revisies, commentaren. Hier hoe je het opschoont.',
				'tags' => ['pdf-metadata', 'privacy', 'redactie'],
				'published_offset_days' => 65,
				'body' => <<<'HTML'
<p>Naast de zichtbare content bevat een PDF metadata en "verborgen" content die je bij distribueren niet wilt meegeven.</p>

<h2>Wat staat er allemaal in?</h2>
<ul>
  <li><strong>Document-properties:</strong> auteur, titel, onderwerp, keywords.</li>
  <li><strong>Creation/modification dates:</strong> wanneer is dit bewerkt.</li>
  <li><strong>Producer-software:</strong> welke tool heeft dit gemaakt (bijv. "Microsoft Word 2019" of "Adobe Acrobat Pro DC 2023").</li>
  <li><strong>Embedded fonts:</strong> meestal geen issue maar soms pad-informatie.</li>
  <li><strong>Commentaren en annotaties:</strong> ook als ze visueel verborgen zijn.</li>
  <li><strong>Form field-data:</strong> waarden die in formulieren zijn ingevuld.</li>
  <li><strong>Attachments:</strong> bestanden aan de PDF gekoppeld.</li>
  <li><strong>JavaScript:</strong> PDF's kunnen scripts bevatten.</li>
  <li><strong>Digital signatures info.</strong></li>
</ul>

<h2>Waarom het je interesseert</h2>
<ul>
  <li>Auteur-naam in metadata kan je werkgever verraden bij "anonieme" documenten.</li>
  <li>Modification-history kan tonen wanneer je iets last-minute hebt gewijzigd.</li>
  <li>Eerdere revisies kunnen in de PDF zitten (Word-saved-as-PDF bewaart vaak track-changes).</li>
  <li>Attachments kunnen per ongeluk meegaan met de distributie.</li>
</ul>

<h2>Hoe strip je het?</h2>
<ol>
  <li>Redactie-tool gebruikt die metadata verwijderen ondersteunt.</li>
  <li>Alternatief: "Print to PDF" vanuit PDF-viewer — resulteert in een nieuwe PDF zonder oude metadata.</li>
  <li>Controle: open geredigeerde PDF, ga naar File → Properties, check velden.</li>
</ol>

<h2>Voorzichtig</h2>
<p>Als je een ondertekend document hebt, stript metadata-verwijdering ook de digitale handtekening. Verifieer dat dit gewenst is voordat je het doet.</p>

<p>Zie ook: <a href="/nl/blog/pdf-redactie-voor-mkb-complete-gids">redactie-pillar</a>, <a href="/nl/blog/zwarte-balken-pdf-waarom-niet">waarom zwarte balken niet werken</a>.</p>
HTML,
			],

			[
				'slug' => 'ocr-redactie-gescande-pdf',
				'title' => 'OCR-redactie: gescande PDF\'s bewerkbaar krijgen voor redactie',
				'excerpt' => 'Een gescande PDF is een serie afbeeldingen, geen tekst. Zoeken en redigeren werkt niet zonder OCR. Hier de workflow.',
				'tags' => ['ocr', 'gescande-pdf', 'redactie'],
				'published_offset_days' => 73,
				'body' => <<<'HTML'
<p>Veel documenten komen binnen als gescande PDF — ondertekende contracten, oude archiefstukken, doctors-brieven. Tekst is voor mensen leesbaar, voor software zijn het afbeeldingen. OCR zet het om.</p>

<h2>Het verschil</h2>
<ul>
  <li><strong>Tekst-PDF:</strong> onderliggend zijn er text-objecten. Zoeken werkt, copy-paste werkt, redactie werkt.</li>
  <li><strong>Image-PDF:</strong> alleen raster-afbeeldingen. Zoeken werkt niet, tekst is niet selecteerbaar. Moet eerst door OCR.</li>
</ul>

<h2>OCR-kwaliteit varieert</h2>
<ul>
  <li>Goed gescand papier (300+ DPI): 98-99% karakter-accuratie met moderne OCR (Tesseract 5, Azure Read, Google Vision).</li>
  <li>Slecht gescand / gekreukeld / vieze kopie: 70-90%. Handmatig verifiëren nodig.</li>
  <li>Handgeschreven: apart model nodig, 60-85% bij goed leesbare handschriften.</li>
</ul>

<h2>Workflow voor redactie met OCR</h2>
<ol>
  <li>OCR toepassen op PDF (Acrobat heeft dit, open-source Tesseract ook).</li>
  <li>Zoeken op gevoelige patronen (BSN, e-mail, telefoon).</li>
  <li>Geïdentificeerde regio's redigeren.</li>
  <li>Output als <em>text-plus-image</em> of alleen <em>image</em> afhankelijk van doel.</li>
  <li>Verificatie: scan de output nogmaals met OCR, check of gevoelige tekst niet meer te vinden is.</li>
</ol>

<h2>Specifieke valkuil</h2>
<p>Als je OCR doet en daarna redigeert op tekst-niveau, maar het resultaat als afbeelding exporteert, kun je toch nog gevoelige data in de achtergrond-tekstlaag hebben. Zorg dat je de tekstlaag óók redigeert, niet alleen de visuele.</p>

<p>Zie ook: <a href="/nl/blog/pdf-redactie-voor-mkb-complete-gids">redactie-pillar</a>, <a href="/nl/blog/pattern-mode-bulk-redactie">pattern mode</a>.</p>
HTML,
			],

			[
				'slug' => 'pattern-mode-bulk-redactie',
				'title' => 'Pattern mode: bulk-redactie voor terugkerende patronen',
				'excerpt' => 'Als je 200 documenten hebt waar telkens BSN\'s, e-mails of IBAN\'s moeten verdwijnen, is per-document-klikken moordend. Pattern mode automatiseert dit.',
				'tags' => ['pattern-mode', 'bulk', 'redactie', 'automation'],
				'published_offset_days' => 81,
				'body' => <<<'HTML'
<p>Pattern-gebaseerde redactie herkent automatisch patronen zoals telefoonnummers, e-mailadressen, BSN, IBAN, datums — en past er redactie op toe zonder dat je elke match handmatig aanklikt.</p>

<h2>Typische patronen voor MKB</h2>
<ul>
  <li><strong>BSN:</strong> 9 cijfers, met of zonder streepjes. <a href="/nl/blog/bsn-in-documenten-herkennen">BSN herkennen</a>.</li>
  <li><strong>IBAN:</strong> NL + 2 cijfers + 4 letters + 10 cijfers.</li>
  <li><strong>E-mailadressen:</strong> standaard regex.</li>
  <li><strong>Telefoonnummers:</strong> NL-formaat (+31... of 06... of 010...).</li>
  <li><strong>Postcodes:</strong> 4-cijfers + 2-letters.</li>
  <li><strong>Datums:</strong> diverse formaten.</li>
  <li><strong>Credit card-nummers:</strong> groepjes van 4 of 16 cijfers met Luhn-check.</li>
  <li><strong>Eigennamen:</strong> lastiger — statistiek + named-entity-recognition nodig.</li>
</ul>

<h2>Valkuilen</h2>
<ul>
  <li><strong>False positives:</strong> een factuurnummer kan op een BSN lijken. Check de matches.</li>
  <li><strong>False negatives:</strong> patroon met spaties ("123 456 789") matcht niet altijd. Test met voorbeelden.</li>
  <li><strong>Context:</strong> een e-mail in header "Van: noreply@bedrijf.nl" wil je misschien behouden, een klant-e-mail in body niet.</li>
</ul>

<h2>Werkwijze</h2>
<ol>
  <li>Patronen definiëren / uit preset kiezen.</li>
  <li>Tool toont preview: welke matches zijn er?</li>
  <li>Valideer op sample van 10 documenten.</li>
  <li>Pas toe op bulk.</li>
  <li>Steekproefsgewijs controleren.</li>
  <li>Audit-log: welke patterns welke documenten hebben geraakt (zie <a href="/nl/blog/redactie-audit-trail">audit trail</a>).</li>
</ol>

<h2>Tools</h2>
<p>Adobe Acrobat Pro heeft dit. Onze <a href="/nl/prijzen">PDF Redact Business-plan</a> levert pattern mode met Nederlandse defaults (BSN, IBAN, NL-telefoonformaat) en audit-logging voor AVG-compliance.</p>

<p>Zie ook: <a href="/nl/blog/pdf-redactie-voor-mkb-complete-gids">redactie-pillar</a>, <a href="/nl/blog/redactie-audit-trail">audit trail</a>.</p>
HTML,
			],

			[
				'slug' => 'bsn-in-documenten-herkennen',
				'title' => 'BSN in documenten herkennen en verwijderen',
				'excerpt' => 'BSN mag vrijwel nooit worden gedeeld met derden. In praktijk zit het verborgen in scans, salarisstroken en oude contracten. Hier hoe je het systematisch vindt en verwijdert.',
				'tags' => ['bsn', 'avg', 'redactie', 'privacy'],
				'published_offset_days' => 89,
				'body' => <<<'HTML'
<p>Het BSN (Burgerservicenummer) is een van de meest gevoelige persoonsgegevens. Delen met derden buiten specifieke wettelijke grondslag is een AVG-overtreding van kaliber.</p>

<h2>Waar komt het voor in documenten?</h2>
<ul>
  <li>Salarisstroken (verplicht, mag intern).</li>
  <li>Jaaropgaven en loonbelasting-verklaringen.</li>
  <li>Oude arbeidscontracten.</li>
  <li>Sollicitatie-formulieren (zou niet moeten).</li>
  <li>Facturen van bepaalde zzp'ers naar klanten (zou niet moeten).</li>
  <li>Scans van ID-bewijzen (niet zomaar opslaan).</li>
</ul>

<h2>Waarom moet het weg?</h2>
<p>BSN kan misbruikt worden voor identiteitsfraude. Als delen gevraagd worden (accountant, Belastingdienst): gerechtvaardigde grondslag. Anders niet. Zie <a href="/nl/blog/avg-compliance-mkb">AVG-pillar</a>.</p>

<h2>Herkenning</h2>
<ul>
  <li><strong>Format:</strong> 9 cijfers. Vaak geformatteerd als XXX-XX-XXXX of XXXXXXXXX.</li>
  <li><strong>Validatie:</strong> 11-proef. Niet elk 9-cijferig nummer is een geldig BSN — de 11-proef-algoritme valideert het.</li>
  <li><strong>Context:</strong> "BSN", "Sofi", "SoFi-nummer", "Burgerservicenummer" in de buurt vergroot zekerheid.</li>
</ul>

<h2>Workflow</h2>
<ol>
  <li>Pattern-match op 9-cijfer-blokken (zie <a href="/nl/blog/pattern-mode-bulk-redactie">pattern mode</a>).</li>
  <li>11-proef toepassen om false positives te filteren.</li>
  <li>Context-check als extra laag (woord "BSN" binnen 50 karakters).</li>
  <li>Redigeer vervangen door "[BSN]" of gewoon blanco vlakje.</li>
</ol>

<h2>11-proef algoritme (korte uitleg)</h2>
<p>Neem de 9 cijfers. Vermenigvuldig 1e met 9, 2e met 8, 3e met 7, ..., 8e met 2, 9e met -1. Tel op. Deelbaar door 11? → Geldig BSN. Meeste pattern-redactie-tools hebben dit ingebouwd als optie.</p>

<p>Zie ook: <a href="/nl/blog/pdf-redactie-voor-mkb-complete-gids">redactie-pillar</a>, <a href="/nl/blog/pattern-mode-bulk-redactie">pattern mode</a>.</p>
HTML,
			],

			[
				'slug' => 'redactie-audit-trail',
				'title' => 'Audit trail voor redactie: wat log je, waarom, hoe lang?',
				'excerpt' => 'Een auditor komt langs en vraagt: "laat zien hoe jullie klant-data hebben geanonimiseerd voor rapport X." Zonder audit trail sta je met lege handen. Hier wat je logt.',
				'tags' => ['audit-trail', 'redactie', 'compliance', 'iso-27001'],
				'published_offset_days' => 97,
				'body' => <<<'HTML'
<p>Bij geautomatiseerde of grootschalige redactie is een audit trail meer dan nice-to-have: het is je verdediging bij AVG-claims en ISO-audits.</p>

<h2>Wat leg je per redactie-actie vast</h2>
<ul>
  <li>Gebruiker die de redactie uitvoerde.</li>
  <li>Tijdstip.</li>
  <li>Origineel bestand (naam + hash).</li>
  <li>Geredigeerd bestand (naam + hash).</li>
  <li>Toegepaste patterns of handmatige regio's (samenvatting, niet de originele tekst).</li>
  <li>Aantal matches geredigeerd.</li>
  <li>Redigeer-reden (optioneel free-text).</li>
  <li>Eventueel: wie het resulterende document heeft ontvangen.</li>
</ul>

<h2>Wat je NIET logt</h2>
<ul>
  <li>De originele tekst die is geredigeerd — dat zou de hele oefening ondermijnen.</li>
  <li>Afbeeldingen van geredigeerde content.</li>
</ul>

<h2>Bewaartermijn</h2>
<ul>
  <li>Voor ISO 27001-doeleinden: 3 jaar minimum.</li>
  <li>Voor AVG-verantwoording: 3-5 jaar afhankelijk van context.</li>
  <li>Langer als het redactie-werk betrekking heeft op fiscale documenten (7 jaar).</li>
</ul>

<h2>Opslag</h2>
<p>Centrale log, beperkt toegankelijk. Niet in de PDF zelf (contradict-ion-in-terms), wel in een database of log-bestand met toegangscontrole.</p>

<h2>Verificatie</h2>
<p>Periodieke steekproef: kies 10% van recente redacties, open log-entry, vergelijk met bestand: klopt wat er gelogd staat?</p>

<p>Tools: onze <a href="/nl/prijzen">PDF Redact Business-plan</a> levert een audit-log die per redactie vastlegt wat nodig is voor ISO-doeleinden. Zie ook de <a href="/nl/blog/pdf-redactie-voor-mkb-complete-gids">redactie-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'pdf-redactie-tools-vergelijking',
				'title' => 'PDF-redactie tools vergeleken: Acrobat, PDF Redact, open-source',
				'excerpt' => 'Wat zijn de reële opties voor veilige PDF-redactie in 2026? Adobe is de veteraan, maar duur. Open source mist features. Hier de eerlijke vergelijking.',
				'tags' => ['pdf-redactie', 'tools', 'vergelijking'],
				'published_offset_days' => 105,
				'body' => <<<'HTML'
<p>De redactie-markt is een driedeling: Adobe, webgebaseerde alternatieven, en open-source.</p>

<h2>Adobe Acrobat Pro</h2>
<ul>
  <li>Prijs: €19-22/gebruiker/maand.</li>
  <li>Sterkte: volwassen tool, pattern mode, OCR, goede UX.</li>
  <li>Zwakte: prijs, desktop-gebonden (Cloud-editie heeft beperkingen), zwaar qua installatie.</li>
  <li>Voor wie: organisaties die hoeveel redactie doen én andere PDF-taken.</li>
</ul>

<h2>Web-based alternatieven (inclusief onze tool)</h2>
<ul>
  <li>Prijs: €5-15/maand, vaak inbegrepen in een breder platform.</li>
  <li>Sterkte: geen installatie, snelle turn-around, goedkoper.</li>
  <li>Zwakte: minder feature-rijk dan Acrobat, afhankelijk van internet.</li>
  <li>Voor wie: MKB met normaal volume (5-50 redacties/maand).</li>
</ul>

<h2>Open source (qpdf, PDFBox, pdf-tools.cli)</h2>
<ul>
  <li>Prijs: gratis.</li>
  <li>Sterkte: volledig controleerbaar, automatiseerbaar via scripts.</li>
  <li>Zwakte: geen UX, vaak alleen programmatische API, pattern mode moet je zelf bouwen.</li>
  <li>Voor wie: tech-teams met specifieke bulk-automation-eisen.</li>
</ul>

<h2>Beslissingsmatrix</h2>
<ul>
  <li>&lt; 10 redacties/maand, gemengd gebruik: browser-based tool.</li>
  <li>50+ redacties/maand, handmatig werk: Adobe.</li>
  <li>Hoog-volume, geautomatiseerd: open source + eigen wrapper.</li>
  <li>Gevoelig voor compliance (ISO, AVG-audit): tool met audit-logging. Onze <a href="/nl/prijzen">PDF Redact Business</a>.</li>
</ul>

<h2>Wat je altijd eist</h2>
<ul>
  <li>Echte redactie (geen zwarte balken-truc). Zie <a href="/nl/blog/zwarte-balken-pdf-waarom-niet">waarom zwarte balken niet werken</a>.</li>
  <li>Metadata-strippen.</li>
  <li>Output-PDF-versie is recent (1.7 of hoger).</li>
  <li>Kan omgaan met gescande PDF (OCR-integratie).</li>
</ul>

<p>Zie ook: <a href="/nl/blog/pdf-redactie-voor-mkb-complete-gids">redactie-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'avg-redactie-pseudonimiseren',
				'title' => 'AVG: redactie, pseudonimiseren en anonimiseren — welke wanneer?',
				'excerpt' => 'Deze drie termen verwarren mensen. AVG behandelt ze anders. Hier de verschillen en wanneer welke van toepassing is.',
				'tags' => ['avg', 'pseudonimiseren', 'anonimiseren', 'redactie'],
				'published_offset_days' => 113,
				'body' => <<<'HTML'
<p>AVG gebruikt "pseudonimiseren" en "anonimiseren" als wezenlijk verschillende termen. Redactie is een techniek om een van beide te bereiken. Waar zit het verschil?</p>

<h2>Anonimiseren</h2>
<p>Data wordt zodanig onherleidbaar gemaakt dat de persoon niet meer te identificeren is — ook niet met aanvullende informatie. Eenmaal anoniem: valt niet meer onder AVG.</p>
<p>Criterium: redelijkerwijs kan niemand de persoon meer achterhalen, ook niet met veel moeite of aanvullende databronnen.</p>

<h2>Pseudonimiseren</h2>
<p>Data wordt verminderd identificerend. Iemand die de vertaaltabel of sleutel heeft, kan nog wel identificeren. Pseudonieme data valt onder AVG, maar verminderde verwerkingsrisico.</p>

<h2>Voorbeelden</h2>
<ul>
  <li><strong>"Kees Janssen, 123 Mainstraat" → "K.J., [geredigeerd]":</strong> pseudoniem (collega kan nog herkennen).</li>
  <li><strong>Volledige naam + alle metadata verwijderd, geen terugkoppelingsmogelijkheid:</strong> anoniem.</li>
  <li><strong>Statistisch geaggregeerde data (gemiddelde leeftijd team):</strong> anoniem.</li>
  <li><strong>Hashed e-mailadres met bekende hash-functie:</strong> pseudoniem (dezelfde hash maakt vergelijking mogelijk).</li>
</ul>

<h2>Waarom maakt het uit?</h2>
<ul>
  <li>Bij anonimiseren: geen AVG-verplichtingen meer. Je mag data vrij delen, bewaren, verwerken.</li>
  <li>Bij pseudonimiseren: nog onder AVG, met verlaagd risico. Wel informatieplicht, wel rechten betrokkenen.</li>
  <li>Volledige identificatie (geen redactie): volledig AVG.</li>
</ul>

<h2>Praktijk-advies</h2>
<ul>
  <li>Documenten bestemd voor publiek: anonimiseren (echte redactie + metadata + geen hash-sleutel).</li>
  <li>Interne verwerking waar "stel we ontdekken iets verdachts": pseudonimiseren zodat herleiding mogelijk is.</li>
  <li>Onderzoek / statistiek: anonimiseren is vaak voldoende en lost veel governance-vragen op.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/pdf-redactie-voor-mkb-complete-gids">redactie-pillar</a>, <a href="/nl/blog/avg-compliance-mkb">AVG-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'redactie-na-offboarding',
				'title' => 'Redactie in het offboarding-proces: welke documenten moet je opschonen?',
				'excerpt' => 'Ex-medewerker-data moet opgeruimd volgens AVG-bewaartermijnen. Redactie helpt bij documenten die je wel wilt bewaren maar zonder persoons-identificatie.',
				'tags' => ['offboarding', 'redactie', 'avg', 'bewaartermijnen'],
				'published_offset_days' => 121,
				'body' => <<<'HTML'
<p>Na <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding</a> blijven er documenten bestaan waar de ex-medewerker in voorkomt. Fiscale bewaarplicht zegt: bewaren. AVG zegt: minimaliseer identificeerbaarheid. Redactie lost het op.</p>

<h2>Categorieën om te overwegen</h2>
<ul>
  <li><strong>Salarisstroken (7 jaar fiscaal):</strong> blijven in originele vorm — BSN en bedragen zijn onderdeel van de fiscale bewijsvoering.</li>
  <li><strong>Functioneringsverslagen (2 jaar):</strong> na termijn volledig verwijderen, tenzij dispuut lopend.</li>
  <li><strong>Project-documenten waar ex-medewerker in staat (bewaren voor klanthistorie):</strong> naam vervangen door rol of initialen.</li>
  <li><strong>Interne memo's en verslagen:</strong> afwegen per document.</li>
  <li><strong>E-mail-archief:</strong> na 30 dagen forwarding, archief bewaren per retentie-beleid; de meeste e-mail-data is na 1-2 jaar te anonimiseren of te verwijderen.</li>
</ul>

<h2>Proces</h2>
<ol>
  <li>Identificeer documenten die behouden moeten blijven.</li>
  <li>Beoordeel per document: is persoonsidentificatie nog nodig, of mag naam/bedrijfsfunctie volstaan?</li>
  <li>Redigeer de niet-noodzakelijke identificatie.</li>
  <li>Log de redactie (wanneer, door wie, welk document) — zie <a href="/nl/blog/redactie-audit-trail">audit trail</a>.</li>
  <li>Plan herzieningsmoment (bijv. jaarlijks).</li>
</ol>

<h2>Automatiseren voor volume</h2>
<p>Bij &gt; 50 ex-medewerkers/jaar loont pattern-based bulk-redactie: naam-detectie en vervanging. Onze <a href="/nl/prijzen">PDF Redact Business</a> heeft hier tooling voor.</p>

<p>Zie ook: <a href="/nl/blog/waterdichte-offboarding-stappen">offboarding-pillar</a>, <a href="/nl/blog/bewaartermijnen-personeelsdossier">bewaartermijnen</a>.</p>
HTML,
			],

			[
				'slug' => 'foto-redactie-privacy',
				'title' => 'Foto en afbeeldings-redactie: gezichten, kentekens, bordjes',
				'excerpt' => 'Niet alle gevoelige data is tekst. Gezichten van omstanders in foto\'s, kentekens op parkeerterrein-scans, whiteboard-foto\'s met bedrijfsnamen. Hier wat mag en hoe.',
				'tags' => ['foto-redactie', 'afbeeldingen', 'privacy'],
				'published_offset_days' => 129,
				'body' => <<<'HTML'
<p>PDF's met embedded afbeeldingen bevatten vaak meer persoonsgegevens dan de tekst alleen. Whiteboard-foto's, event-foto's in presentaties, camera-beelden.</p>

<h2>Types gevoelige content in afbeeldingen</h2>
<ul>
  <li>Gezichten van mensen die niet expliciet hebben ingestemd met publicatie.</li>
  <li>Kentekens van auto's.</li>
  <li>Naamplaatjes bij conferenties.</li>
  <li>Whiteboard-inhoud met klant- of projectnamen.</li>
  <li>Schermen met zichtbare data (teams-chat, dashboards).</li>
  <li>Locatie-identifiers (straatnamen, bedrijfsgebouwen).</li>
</ul>

<h2>Werkwijze</h2>
<ol>
  <li>Lijst op welke afbeeldingen in je PDF zijn ingebed.</li>
  <li>Per afbeelding: welke gevoelige content is erop te zien?</li>
  <li>Bewerken: blur, zwarte rechthoek, vervangen door placeholder.</li>
  <li>Her-export naar PDF.</li>
  <li>Verifieer: zoom in op de geredigeerde regio's. Nog leesbaar?</li>
</ol>

<h2>Blur versus zwarte rechthoek</h2>
<p>Een blur is visueel rustiger dan een zwarte balk, maar: bij lichte blur kan deblurring-software nog iets reconstrueren. Voor hoge gevoeligheid: volledige zwarte rechthoek of vervangen door placeholder-tekst.</p>

<h2>AVG-kader</h2>
<p>Gezichten zijn persoonsgegevens. Publicatie zonder grondslag is een overtreding. Bij twijfel: blur.</p>

<p>Zie ook: <a href="/nl/blog/pdf-redactie-voor-mkb-complete-gids">redactie-pillar</a>, <a href="/nl/blog/avg-compliance-mkb">AVG-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'automatiseren-redactie-workflow',
				'title' => 'Redactie-workflow automatiseren: van ad hoc naar gestroomlijnd',
				'excerpt' => 'Als je per maand 5+ documenten redigeert, is het tijd voor een workflow. Hier de fasen: intake, redactie, verificatie, release, audit.',
				'tags' => ['workflow', 'automation', 'redactie', 'proces'],
				'published_offset_days' => 137,
				'body' => <<<'HTML'
<p>Ad-hoc-redactie ("even snel iets eruit halen") leidt tot fouten. Bij volume loont een gestroomlijnde workflow met duidelijke checkpoints.</p>

<h2>De vijf fasen</h2>
<ol>
  <li><strong>Intake:</strong> waar komt het document vandaan? Wie vraagt de redactie? Waarvoor?</li>
  <li><strong>Classificatie:</strong> welke data-typen staan erin (BSN? Namen? Bedragen?)? Welke moeten weg volgens AVG / beleid / klant-NDA?</li>
  <li><strong>Redactie:</strong> toepassen patterns + handmatige review.</li>
  <li><strong>Verificatie:</strong> tweede persoon of geautomatiseerde check.</li>
  <li><strong>Release + audit:</strong> uitleveren aan aanvrager, loggen in <a href="/nl/blog/redactie-audit-trail">audit trail</a>.</li>
</ol>

<h2>Wie doet wat?</h2>
<ul>
  <li>Aanvrager: filt intake-formulier.</li>
  <li>Redactie-operator: voert 3 en 4 uit.</li>
  <li>Verifier (ander persoon of automatisch): controleert.</li>
  <li>Compliance/privacy officer: steekproeven op audit-log.</li>
</ul>

<h2>Tooling</h2>
<ul>
  <li>Intake via formulier (Microsoft Forms, Google Forms, Notion, ticket-systeem).</li>
  <li>Redactie via dedicated tool met pattern mode.</li>
  <li>Output naar secure-share-locatie met limited retention.</li>
  <li>Logging via audit-log-functie van je tool.</li>
</ul>

<h2>SLA</h2>
<p>Standaard 24 uur, urgent 4 uur. Klanten / aanvragers weten wat te verwachten.</p>

<h2>Review-cyclus</h2>
<p>Kwartaal: steekproef van recente redacties door privacy-officer. Verbeteringen terug in workflow.</p>

<p>Zie ook: <a href="/nl/blog/pdf-redactie-voor-mkb-complete-gids">redactie-pillar</a>, <a href="/nl/blog/redactie-audit-trail">audit trail</a>.</p>
HTML,
			],
		];
	}
}
