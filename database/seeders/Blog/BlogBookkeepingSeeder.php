<?php

namespace Database\Seeders\Blog;

use Illuminate\Database\Seeder;

class BlogBookkeepingSeeder extends Seeder
{
	public function run(): void
	{
		BlogSeedHelper::seedCluster(
			[
				'slug' => 'boekhouding',
				'name' => 'Boekhouding & facturatie',
				'pillar_title' => 'Facturatie en boekhouding voor MKB — van quote tot betaling',
				'intro' => 'Terugkerende facturen, herinneringen, BTW, UBL, buitenlandse klanten. Alles wat een kleine onderneming zelf moet kunnen zonder duur boekhoudpakket-abonnement.',
				'sort_order' => 60,
			],
			self::posts(),
		);
	}

	private static function posts(): array
	{
		return [
			[
				'slug' => 'mkb-facturatie-complete-gids',
				'title' => 'MKB-facturatie van quote tot betaling: de complete gids',
				'excerpt' => 'Offerte, factuur, herinnering, aanmaning, boeking, BTW-aangifte. De hele keten uitgelegd voor ondernemers die het zelf doen of met minimale accountantshulp.',
				'is_pillar' => true,
				'featured' => true,
				'tags' => ['facturatie', 'mkb', 'boekhouding'],
				'published_offset_days' => 18,
				'body' => <<<'HTML'
<p>Facturatie is voor veel ondernemers het saaiste maar belangrijkste proces van hun bedrijf. Deze gids loopt de hele keten af, met pointers naar verdieping per onderwerp.</p>

<h2>1. Offerte / quote</h2>
<p>Een goede offerte is juridisch een aanbod. Accepteert de klant, dan is er een overeenkomst. Let op geldigheidstermijn, betaalcondities en of BTW wel of niet is inbegrepen.</p>

<h2>2. Factuur</h2>
<p>De Belastingdienst heeft vaste eisen (zie <a href="/nl/blog/factuurvereisten-nederland">factuurvereisten in Nederland</a>). De must-haves: factuurnummer, datum, BTW-nummer, BTW-specificatie.</p>

<h2>3. Verzending en formaat</h2>
<p>PDF via e-mail is standaard. Voor overheidsopdrachten is <a href="/nl/blog/ubl-peppol-facturen">UBL/PEPPOL</a> verplicht. Voor grote zakelijke klanten wordt het steeds vaker gevraagd.</p>

<h2>4. Betaaltermijn en opvolging</h2>
<p>30 dagen standaard in NL, 14 of 7 dagen voor kleinere klanten. Automatische <a href="/nl/blog/betalingsherinneringen-mkb">betalingsherinneringen</a> op dag 7 na vervaldatum bespaart veel telefoontjes.</p>

<h2>5. BTW-aangifte</h2>
<p>Kwartaal of maandelijks, afhankelijk van omzet. Check <a href="/nl/blog/btw-9-procent-21-procent">BTW 9% vs 21%</a> als je twijfelt welke percentages.</p>

<h2>6. Oninbare vorderingen</h2>
<p>Klant betaalt niet. Na aanmaning, incasso, uiteindelijk afboeken. Zie <a href="/nl/blog/oninbare-vorderingen-mkb">oninbare vorderingen</a>.</p>

<h2>7. Terugkerende facturen</h2>
<p>Als je abonnementen of SaaS verkoopt, moet je <a href="/nl/blog/terugkerende-facturen-automatiseren">recurring facturatie</a> automatiseren. Dat scheelt 80% van je facturatie-tijd.</p>

<h2>8. Internationaal</h2>
<p>Buitenlandse klanten brengen BTW-reverse-charge, andere valuta, en andere eisen. Zie <a href="/nl/blog/buitenlandse-klanten-facturen">internationaal factureren</a>.</p>

<p>Verder: <a href="/nl/blog/creditnota-hoe-wanneer">creditnota's</a>, <a href="/nl/blog/factuurnummer-beleid">factuurnummer-beleid</a>, <a href="/nl/blog/auto-incasso-vs-handmatig">auto-incasso vs handmatig</a>, <a href="/nl/blog/factuur-in-nederlands-en-engels">factuur in NL + EN</a>.</p>
HTML,
			],

			[
				'slug' => 'terugkerende-facturen-automatiseren',
				'title' => 'Terugkerende facturen automatiseren: van handwerk naar 3 min/maand',
				'excerpt' => 'Als je abonnementen, servicecontracten of SaaS levert, is handmatige maand-facturatie pure tijdsverspilling. Hier de setup die werkt zonder dat je €80/maand aan MRR-software betaalt.',
				'tags' => ['facturatie', 'recurring', 'automation', 'saas'],
				'published_offset_days' => 27,
				'body' => <<<'HTML'
<p>Handmatig maandelijks 30 facturen maken kost 4-6 uur. Automatisch: 3 minuten reviewen. Dat is 40+ uur per jaar bespaard.</p>

<h2>Wat moet de tool kunnen?</h2>
<ul>
  <li>Templates per klant-abonnement (prijs, frequentie, omschrijving).</li>
  <li>Scheduled creation: op vaste dag van de maand de facturen aanmaken.</li>
  <li>Auto-verzending per e-mail.</li>
  <li>Koppeling met betaalmethode (iDEAL-link, SEPA auto-incasso).</li>
  <li>Rapportage welke facturen zijn verstuurd en welke betaald.</li>
</ul>

<h2>Instellen</h2>
<ol>
  <li>Per klant: abonnement activeren met start-datum, frequentie (maand/kwartaal/jaar), bedrag, BTW.</li>
  <li>Scheduler draait dagelijks, kijkt welke abonnementen vandaag toe zijn aan een nieuwe factuur.</li>
  <li>Factuur wordt gegenereerd + gemaild.</li>
  <li>Als er een betaling binnenkomt (of iDEAL-klik registreert): factuur op paid.</li>
</ol>

<h2>Reviewen voor verzenden: ja of nee?</h2>
<ul>
  <li>Bij &lt; 10 abonnementen: geen review, direct verzenden.</li>
  <li>Bij 10-50: steekproef-review van nieuwe/gewijzigde.</li>
  <li>Bij 50+: accountant-akkoord per maand.</li>
</ul>

<h2>Belangrijke edge cases</h2>
<ul>
  <li>Prijswijziging per 1 januari: test de prorata-berekening.</li>
  <li>Klant vraagt pauze (bijv. vakantie): support voor pause + auto-resume.</li>
  <li>Klant zegt op: abonnement eindigen zonder nog één factuur extra te genereren.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/betalingsherinneringen-mkb">betalingsherinneringen</a>, <a href="/nl/blog/auto-incasso-vs-handmatig">auto-incasso vs handmatig</a>.</p>
HTML,
			],

			[
				'slug' => 'betalingsherinneringen-mkb',
				'title' => 'Betalingsherinneringen: 3 niveaus van beleefd tot aanmaning',
				'excerpt' => 'Als je niks doet, betaalt 15-20% van je klanten te laat. Met een drie-niveau-aanpak (vriendelijk, formeel, aanmaning) krijgt 95% binnen de termijn.',
				'tags' => ['facturatie', 'herinneringen', 'cashflow'],
				'published_offset_days' => 35,
				'body' => <<<'HTML'
<p>De grootste cashflow-winst voor een MKB zit in betaaldiscipline. Een drie-niveau-herinneringsproces houdt het zakelijk maar doeltreffend.</p>

<h2>Niveau 1: vriendelijke herinnering — dag 3 na vervaldatum</h2>
<p>"Hallo, onze factuur nr X was verschenen op [datum] en vervalt op [datum]. Mogelijk heb je hem over het hoofd gezien — link naar factuur hier." 80% betaalt binnen 5 dagen.</p>

<h2>Niveau 2: formele aanmaning — dag 14</h2>
<p>"Wij hebben nog geen betaling ontvangen voor factuur X. Wij verzoeken u binnen 7 dagen te voldoen. Vermeld factuurnummer bij betaling." 15% van de eerste groep volgt.</p>

<h2>Niveau 3: laatste aanmaning — dag 28</h2>
<p>"Dit is onze laatste aanmaning. Bij uitblijven van betaling vóór [datum] zullen wij administratieve kosten in rekening brengen conform de wet (€40 minimum) en de vordering overdragen aan incasso." 4% van de totale groep.</p>

<h2>De laatste 1%</h2>
<p>Incasso of afboeken. Zie <a href="/nl/blog/oninbare-vorderingen-mkb">oninbare vorderingen</a>.</p>

<h2>Automatiseer</h2>
<p>Elke stap moet automatisch — mail op dag 3, dag 14, dag 28. Manager krijgt notificatie als laatste aanmaning is verstuurd (dan wil je soms bellen). Zie <a href="/nl/blog/terugkerende-facturen-automatiseren">recurring facturatie</a> voor de technische setup.</p>

<h2>Tone-of-voice per klanttype</h2>
<ul>
  <li>Vaste klant: wat meer ruimte (dag 5-10-21 ipv 3-14-28).</li>
  <li>Nieuwe klant: strak (3-14-28) zodat de standaard meteen duidelijk is.</li>
  <li>Grote klant met payment-cyclus: afstemmen op hun cycle, niet je defaults.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/mkb-facturatie-complete-gids">facturatie-pillar</a>, <a href="/nl/blog/cashflow-mkb-tips">cashflow-tips</a>.</p>
HTML,
			],

			[
				'slug' => 'btw-9-procent-21-procent',
				'title' => 'BTW 9% of 21%: wanneer welke?',
				'excerpt' => 'De meeste MKB-ondernemers factureren met 21% BTW. Maar er zijn uitzonderingen: tijdschriften, cultuur, bepaalde voedingsmiddelen, dienstverlening in zorg. Hier de regels.',
				'tags' => ['btw', 'facturatie', 'belastingdienst'],
				'published_offset_days' => 43,
				'body' => <<<'HTML'
<p>Nederland heeft drie BTW-tarieven: 0%, 9% (verlaagd) en 21% (algemeen). Voor bijna al het B2B-werk geldt 21%. Maar een paar categorieën vallen onder 9% of 0%.</p>

<h2>21% — de standaard</h2>
<p>Consultancy, software, SaaS, ontwerp, advies, marketing, onderhoud, licenties. Als je twijfelt: 21%.</p>

<h2>9% — verlaagd</h2>
<ul>
  <li>Boeken, kranten, tijdschriften (in papier én digitaal sinds 2020).</li>
  <li>Voedingsmiddelen en horeca-consumpties.</li>
  <li>Kappers, fietsreparatie (arbeidskosten).</li>
  <li>Cultuur: toegangskaartjes, musea, theaters.</li>
  <li>Personenvervoer, hotels.</li>
  <li>Bepaalde medicijnen.</li>
</ul>

<h2>0% — specifiek</h2>
<ul>
  <li>Export naar buiten de EU.</li>
  <li>ICL naar BTW-plichtige bedrijven binnen de EU (reverse charge).</li>
  <li>Schepen, luchtvaart, internationale transport.</li>
</ul>

<h2>Vrijgesteld (geen BTW, geen aftrek)</h2>
<ul>
  <li>Medische zorg (tandarts, huisarts, fysiotherapie).</li>
  <li>Onderwijs.</li>
  <li>Financiële dienstverlening (verzekeringen).</li>
  <li>Sociaal-cultureel werk.</li>
</ul>
<p>Bij vrijgestelde omzet mag je de BTW op inkoop NIET terugvorderen. Dit is wezenlijk anders dan 0%.</p>

<h2>Check met Belastingdienst bij twijfel</h2>
<p>Voor niche-gevallen: vooraf schriftelijke bevestiging aanvragen bij de Belastingdienst. Kost niks en geeft juridische zekerheid.</p>

<p>Zie ook: <a href="/nl/blog/factuurvereisten-nederland">factuurvereisten</a>, <a href="/nl/blog/buitenlandse-klanten-facturen">buitenlandse klanten</a>.</p>
HTML,
			],

			[
				'slug' => 'factuurvereisten-nederland',
				'title' => 'Factuurvereisten in Nederland: wat móét erop?',
				'excerpt' => 'De Belastingdienst heeft een vaste lijst eisen. Als je die mist kan de klant de BTW niet terugvragen én loop je bij een controle risico. Hier de checklist.',
				'tags' => ['facturatie', 'belastingdienst', 'compliance'],
				'published_offset_days' => 51,
				'body' => <<<'HTML'
<p>Een factuur die niet aan de wettelijke eisen voldoet is juridisch wel een factuur, maar bij BTW-aftrek of een controle loopt de klant tegen problemen aan. Hier de checklist.</p>

<h2>Verplicht op elke factuur</h2>
<ol>
  <li>Het woord "factuur" (of "invoice" bij Engelstalige).</li>
  <li>Factuurnummer (opeenvolgend, zie <a href="/nl/blog/factuurnummer-beleid">factuurnummer-beleid</a>).</li>
  <li>Factuurdatum.</li>
  <li>Leveringsdatum (indien anders dan factuurdatum).</li>
  <li>Bedrijfsgegevens leverancier: naam, adres, KvK-nummer, BTW-ID.</li>
  <li>Bedrijfsgegevens afnemer: naam, adres, BTW-ID bij B2B.</li>
  <li>Omschrijving van de prestatie.</li>
  <li>Aantal en eenheid.</li>
  <li>Bedrag exclusief BTW.</li>
  <li>BTW-tarief per regel.</li>
  <li>Totaal BTW-bedrag.</li>
  <li>Totaal inclusief BTW.</li>
  <li>Betalingstermijn (of vervaldatum).</li>
  <li>Eventueel: "BTW verlegd" bij reverse charge.</li>
</ol>

<h2>Extra aanbevolen (niet verplicht)</h2>
<ul>
  <li>IBAN + BIC.</li>
  <li>Betaalwijzen (iDEAL-link, bankoverschrijving).</li>
  <li>Verwijzing naar offerte / inkooporder bij zakelijke klanten.</li>
  <li>Leveringsvoorwaarden / kleine lettertjes op achterkant of via URL.</li>
</ul>

<h2>Veelgemaakte fouten</h2>
<ul>
  <li>BTW-nummer mist op klant-factuur bij een ICL (reverse charge).</li>
  <li>Factuurnummer heeft een gat of duplicaat.</li>
  <li>Factuurdatum &gt; 14 dagen na levering (mag wettelijk wel, maar wringt met betaaltermijnen).</li>
  <li>"Verschotten" onduidelijk gescheiden van eigen diensten.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/mkb-facturatie-complete-gids">facturatie-pillar</a>, <a href="/nl/blog/factuurnummer-beleid">factuurnummer-beleid</a>.</p>
HTML,
			],

			[
				'slug' => 'factuurnummer-beleid',
				'title' => 'Factuurnummer-beleid: wat werkt, wat veroorzaakt chaos?',
				'excerpt' => 'Nummer-reeksen in 2026 klinkt futuristisch maar in praktijk zie ik elke week een MKB dat met gemiste of dubbele factuurnummers worstelt. Hier de drie patronen die werken.',
				'tags' => ['facturatie', 'factuurnummer', 'administratie'],
				'published_offset_days' => 59,
				'body' => <<<'HTML'
<p>De Belastingdienst eist "doorlopend genummerd" per facturen-reeks. In praktijk geeft dat drie werkbare patronen.</p>

<h2>Patroon 1: één lopende reeks per jaar</h2>
<p>2026-0001, 2026-0002, 2026-0003… Reset bij begin kalenderjaar.</p>
<p>Voor: simpel, fiscaal prima.</p>
<p>Tegen: bij jaarwisseling is er een sprong in nummering die voor klanten verwarrend kan lijken.</p>

<h2>Patroon 2: één eeuwigdurende reeks</h2>
<p>000001, 000002, …, 003847, 003848. Nooit reset.</p>
<p>Voor: geen jaarwisseling-bump, visueel consistent.</p>
<p>Tegen: wordt onleesbaar bij hoge nummers. En na een paar jaar zie je uit de hand gelopen nummers.</p>

<h2>Patroon 3: reeks per product-type</h2>
<p>SUB-2026-0001 voor subscription, PROJ-2026-0001 voor project-werk, CON-2026-0001 voor consult-uren.</p>
<p>Voor: analytics per reeks is makkelijker.</p>
<p>Tegen: complexer bij fiscale controle — je moet per reeks kunnen aantonen dat hij doorlopend is.</p>

<h2>Wat je niet doet</h2>
<ul>
  <li>Per klant eigen nummering. Niet toegestaan.</li>
  <li>Willekeurige nummers of codes zonder chronologische logica.</li>
  <li>Nummers handmatig typen — altijd automatisch genereren.</li>
</ul>

<h2>Bij fouten</h2>
<p>Factuur verstuurd met verkeerd nummer? Maak een <a href="/nl/blog/creditnota-hoe-wanneer">creditnota</a> op die factuur en een nieuwe met correct nummer. Nooit een nummer overslaan of dubbel gebruiken.</p>

<p>Zie ook: <a href="/nl/blog/mkb-facturatie-complete-gids">facturatie-pillar</a>.</p>
HTML,
			],

			[
				'slug' => 'ubl-peppol-facturen',
				'title' => 'UBL en PEPPOL: wanneer is digitale factuur verplicht voor jou?',
				'excerpt' => 'Per 2026 zit Nederland in het EU-traject naar verplichte e-invoicing. Voor overheidsklanten is UBL nu al verplicht, voor B2B volgt. Hier wat je nu al moet regelen.',
				'tags' => ['ubl', 'peppol', 'facturatie', 'e-invoicing'],
				'published_offset_days' => 67,
				'body' => <<<'HTML'
<p>E-invoicing is geen PDF per mail — het is een gestructureerd XML-formaat (UBL of CII) verstuurd via een netwerk (PEPPOL). Waar ben je aan toe?</p>

<h2>Nu al verplicht</h2>
<ul>
  <li>Leveringen aan Nederlandse overheden: UBL via PEPPOL of platform.</li>
  <li>Leveringen aan sommige EU-overheden (Italië, Polen, Frankrijk).</li>
</ul>

<h2>In aantocht (2027-2030)</h2>
<p>EU VIDA-voorstel wil EU-brede B2B e-invoicing verplichten. Nederland volgt hierin. Belastingdienst verwacht dat het in 2028-2030 in Nederland actief is.</p>

<h2>Wat doe je nu?</h2>
<ol>
  <li>Check: krijg je overheidsopdrachten? Dan UBL nu regelen.</li>
  <li>Kies een boekhoudpakket dat UBL-export en PEPPOL-send kan. Bijna alle grote pakketten kunnen dit (Exact, Moneybird, TeamLeader Focus, Visma).</li>
  <li>Registreer je bij een PEPPOL access point (vaak via je boekhoudpakket).</li>
  <li>Test met een kleine overheidsklant voordat je het breder inzet.</li>
</ol>

<h2>PEPPOL access points</h2>
<p>Logius (overheid), Storecove, SnelStart, Basware. Meestal €10-30/maand. Je boekhoudpakket heeft er vaak al een integratie mee.</p>

<h2>B2B-klanten die het vragen</h2>
<p>Steeds vaker wordt UBL gevraagd, ook bij B2B. Klanten met een inkoop-automatisering (SAP, Oracle) willen XML. Wees voorbereid — anders verlies je die klant aan concurrenten die het wel kunnen.</p>

<p>Zie ook: <a href="/nl/blog/mkb-facturatie-complete-gids">facturatie-pillar</a>, <a href="/nl/blog/factuurvereisten-nederland">factuurvereisten</a>.</p>
HTML,
			],

			[
				'slug' => 'creditnota-hoe-wanneer',
				'title' => 'Creditnota\'s: wanneer, hoe, en wat NIET doen',
				'excerpt' => 'Een factuur corrigeren doe je niet door hem te verwijderen — dat mag niet. Je maakt een creditnota. Hier de drie situaties waarin je hem gebruikt en de valkuilen.',
				'tags' => ['creditnota', 'facturatie', 'correctie'],
				'published_offset_days' => 75,
				'body' => <<<'HTML'
<p>Een creditnota is een "negatieve factuur" die een eerdere factuur corrigeert. Fiscaal en juridisch is dit de ENIGE correcte manier om een factuur te wijzigen.</p>

<h2>Drie situaties</h2>
<ol>
  <li><strong>Volledige annulering:</strong> klant heeft product geretourneerd of dienst wordt geannuleerd. Creditnota voor het volledige bedrag.</li>
  <li><strong>Gedeeltelijke correctie:</strong> je hebt te veel gefactureerd, of klant heeft korting onderhandeld na verzending. Creditnota voor het verschil.</li>
  <li><strong>Administratieve fout:</strong> verkeerd BTW-tarief, verkeerd bedrag, verkeerde klant. Creditnota op origineel + nieuwe factuur met correcte gegevens.</li>
</ol>

<h2>Wat staat op een creditnota?</h2>
<ul>
  <li>Eigen creditnota-nummer (eigen reeks of in facturen-reeks).</li>
  <li>Verwijzing naar originele factuur ("Creditnota bij factuur [nr]").</li>
  <li>Reden van creditering.</li>
  <li>Bedragen met minus-teken (of "te crediteren" label).</li>
  <li>BTW-specificatie (ook negatief).</li>
</ul>

<h2>Fiscaal</h2>
<p>Door de creditnota daalt je omzet in de BTW-aangifte van de betreffende periode. Bij een correctie met reeds ontvangen betaling moet je het geld terugstorten (of met een nieuwe factuur verrekenen).</p>

<h2>NIET doen</h2>
<ul>
  <li>Een factuur verwijderen/corrigeren zonder creditnota.</li>
  <li>Factuurnummer overslaan — alle nummers moeten doorlopen.</li>
  <li>"Informeel" korting geven zonder creditnota.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/factuurvereisten-nederland">factuurvereisten</a>, <a href="/nl/blog/factuurnummer-beleid">factuurnummer-beleid</a>.</p>
HTML,
			],

			[
				'slug' => 'buitenlandse-klanten-facturen',
				'title' => 'Buitenlandse klanten factureren: EU, VK, VS in één overzicht',
				'excerpt' => 'B2B EU met reverse charge, B2B VK post-Brexit, B2B VS met of zonder sales tax-implicaties. Drie patronen met hun eigen regels.',
				'tags' => ['internationaal', 'facturatie', 'btw', 'export'],
				'published_offset_days' => 83,
				'body' => <<<'HTML'
<p>Zodra je over de grens factureert verandert het BTW-verhaal. Drie hoofdpatronen voor een Nederlandse MKB.</p>

<h2>Patroon 1: B2B binnen de EU</h2>
<ul>
  <li>BTW-tarief: 0% (reverse charge).</li>
  <li>Eisen: geldig BTW-ID klant, vermelding "BTW verlegd" of "reverse charge" op factuur.</li>
  <li>Check BTW-ID via VIES.</li>
  <li>Rapporteren: ICP-opgave (Intracommunautaire Prestaties) per kwartaal.</li>
</ul>

<h2>Patroon 2: B2B buiten de EU (inclusief VK)</h2>
<ul>
  <li>Diensten: meestal 0% BTW voor zakelijke afnemers buiten EU. Verifieer per land.</li>
  <li>Goederen: 0% voor export, inklaringsdocumenten bewaren.</li>
  <li>Geen ICP-opgave nodig.</li>
</ul>

<h2>Patroon 3: B2C buiten NL</h2>
<ul>
  <li>Diensten: vaak BTW van het land van de consument (MOSS/OSS-regeling).</li>
  <li>Goederen &gt; €10k: drempel overschreden = BTW land afnemer.</li>
  <li>Goederen &lt; €10k: NL-BTW toegestaan.</li>
</ul>

<h2>Valuta</h2>
<ul>
  <li>NL-bedrijven rapporteren in EUR. Factureer je in USD of GBP? Bepaal wisselkoers op factuurdatum.</li>
  <li>Verwacht wisselkoersverschil bij betaling — boek dit als valuta-koersresultaat.</li>
</ul>

<h2>Compliance</h2>
<ul>
  <li>Bewaar handelsbewijzen (B/L, airway bill, verzendbewijs) 7 jaar.</li>
  <li>BTW-ID-check met VIES: bewaar screenshot of datetimestamp.</li>
  <li>Bij controle moet je kunnen laten zien dat je het 0%-tarief terecht hebt toegepast.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/mkb-facturatie-complete-gids">facturatie-pillar</a>, <a href="/nl/blog/btw-9-procent-21-procent">BTW-tarieven</a>.</p>
HTML,
			],

			[
				'slug' => 'oninbare-vorderingen-mkb',
				'title' => 'Oninbare vorderingen: afboeken, incasso, of nog een ronde?',
				'excerpt' => 'Klant betaalt niet, reageert niet, en je hebt alle herinneringen gestuurd. Wat dan? Hier de drie paden: afboeken, incasso, of juridische stappen.',
				'tags' => ['oninbare-vorderingen', 'incasso', 'cashflow'],
				'published_offset_days' => 91,
				'body' => <<<'HTML'
<p>Ongeveer 1-3% van je facturen wordt uiteindelijk niet betaald. Hoe je dit behandelt heeft fiscale én cashflow-impact.</p>

<h2>Pad 1: Afboeken als oninbaar</h2>
<p>Voor kleine bedragen (&lt; €500-1.000) is incasso economisch niet zinvol. Boek af, vraag BTW terug.</p>
<ul>
  <li>Interne beslissing: vordering is oninbaar.</li>
  <li>In boekhouding: debit "Oninbare vorderingen" (kosten), credit "Debiteuren".</li>
  <li>BTW: de BTW op een onverhaalbare vordering mag je terugvragen bij de Belastingdienst.</li>
  <li>Bewaar het dossier 7 jaar.</li>
</ul>

<h2>Pad 2: Incasso</h2>
<p>Voor bedragen €500-10.000. Kies tussen:</p>
<ul>
  <li><strong>Minnelijk traject:</strong> incassobureau (Bierens, Graydon, Intrum). Kosten 15-25% van het geïnde bedrag.</li>
  <li><strong>No cure no pay:</strong> veel incassobureaus werken op deze basis. Lage barrière.</li>
  <li><strong>Gerechtelijk:</strong> als minnelijk niet werkt → dagvaarding. Kost juridische vergoeding.</li>
</ul>

<h2>Pad 3: Juridische stappen</h2>
<p>Voor grote bedragen (€10.000+) of principiële kwesties. Advocaat, kantongerecht (tot €25.000) of rechtbank. Niet voor het gemiddelde MKB-geschil.</p>

<h2>Preventie is beter</h2>
<ul>
  <li>Credit-check nieuwe zakelijke klanten (via Creditsafe, Graydon).</li>
  <li>Voor grote orders: aanbetaling vragen (30-50%).</li>
  <li>Kredietverzekering bij structureel hoog debiteurenrisico.</li>
  <li>Strakke <a href="/nl/blog/betalingsherinneringen-mkb">betalingsherinneringen</a>.</li>
</ul>

<p>Zie ook: <a href="/nl/blog/mkb-facturatie-complete-gids">facturatie-pillar</a>, <a href="/nl/blog/cashflow-mkb-tips">cashflow-tips</a>.</p>
HTML,
			],

			[
				'slug' => 'auto-incasso-vs-handmatig',
				'title' => 'Auto-incasso (SEPA) of handmatige betaling — welke wanneer?',
				'excerpt' => 'SEPA-incasso bespaart je betalingsrisico maar vraagt een mandaat. Handmatig is makkelijker op te zetten maar cashflow-wisselvalliger. Hier de afweging.',
				'tags' => ['sepa', 'auto-incasso', 'facturatie', 'cashflow'],
				'published_offset_days' => 99,
				'body' => <<<'HTML'
<p>Betaalt je klant per handmatig overmaken of trek je het automatisch af? De keuze raakt je cashflow-voorspelbaarheid én je klant-relatie.</p>

<h2>SEPA-incasso: voordelen</h2>
<ul>
  <li>Jij initieert betaling — no-show van klant is geen risico.</li>
  <li>Betaaldatum is voorspelbaar (cashflow-voordeel).</li>
  <li>Minder herinneringen nodig.</li>
  <li>Gratis-lage transactiekosten (&lt; €0,10 per incasso).</li>
</ul>

<h2>SEPA-incasso: nadelen</h2>
<ul>
  <li>Klant moet mandaat tekenen (digitaal of papier).</li>
  <li>Klant kan binnen 8 weken storneren zonder reden — geld terug van je rekening.</li>
  <li>Niet-betwiste claim: tot 13 maanden kan klant terugvragen met reden.</li>
  <li>Setup-kosten bij sommige banken voor incassodiensten.</li>
</ul>

<h2>Wanneer SEPA-incasso?</h2>
<ul>
  <li>Abonnementen / recurring omzet — essentieel.</li>
  <li>Lange-termijn-klanten waar vertrouwen is.</li>
  <li>Kleine maandelijkse bedragen (&lt; €200) waar handmatig te veel friction is.</li>
</ul>

<h2>Wanneer handmatig?</h2>
<ul>
  <li>Nieuwe klant — eerst betaalgedrag zien.</li>
  <li>Eenmalige projecten (niet-recurring).</li>
  <li>B2B-klanten die per inkooporder betalen.</li>
  <li>Internationaal (SEPA-incasso werkt binnen SEPA-zone; daarbuiten is het ingewikkelder).</li>
</ul>

<h2>Combinatie werkt goed</h2>
<p>Setup-kosten handmatig, recurring via SEPA. Of: SEPA voor het basis-bedrag, extra uren handmatig. Communiceer duidelijk wat er wanneer wordt afgeschreven.</p>

<p>Zie ook: <a href="/nl/blog/mkb-facturatie-complete-gids">facturatie-pillar</a>, <a href="/nl/blog/terugkerende-facturen-automatiseren">terugkerende facturen</a>.</p>
HTML,
			],

			[
				'slug' => 'factuur-in-nederlands-en-engels',
				'title' => 'Factuur in Nederlands en Engels tegelijk — praktische tips',
				'excerpt' => 'Internationale klanten lezen vaak geen Nederlands. Maar de fiscus wil NL-compliante factuur. Hier de praktische oplossingen zonder 2x werk.',
				'tags' => ['facturatie', 'internationaal', 'tweetalig'],
				'published_offset_days' => 107,
				'body' => <<<'HTML'
<p>Als je aan buitenlandse klanten factureert zijn ze vaak niet Nederlands-sprekend. Maar je moet wel aan NL-factuurvereisten voldoen.</p>

<h2>Optie 1: alleen Engels</h2>
<p>Mag. Geen enkele NL-wet zegt dat een factuur in het Nederlands moet. Hou wel vast aan de verplichte elementen uit <a href="/nl/blog/factuurvereisten-nederland">factuurvereisten</a>.</p>
<p>"Factuur" → "Invoice" (of beide). "BTW" → "VAT".</p>

<h2>Optie 2: tweetalig — NL + EN</h2>
<p>Elke label in beide talen ("Invoice / Factuur", "Total / Totaal"). Past op een A4. Werkt voor zowel NL- als internationale klanten.</p>

<h2>Optie 3: Engels + NL bijlage voor fiscus</h2>
<p>Je klant krijgt de Engelse factuur, je boekhouding houdt een NL-versie voor de administratie. In moderne pakketten genereert dat parallel.</p>

<h2>Waar moet je op letten</h2>
<ul>
  <li>BTW-terminologie: "VAT reverse charge" of "BTW verlegd" moet letterlijk in de taal die de klant begrijpt, anders kan hij het in eigen aangifte niet verwerken.</li>
  <li>Betaalgegevens: IBAN, BIC, referentienummer, muntenheid — in internationaal formaat.</li>
  <li>Datumformaat: gebruik ISO (2026-04-24) om verwarring te vermijden.</li>
</ul>

<h2>Praktisch</h2>
<p>Je boekhoudpakket heeft meestal een "taal per klant"-instelling. Stel het per klant in; volgende factuur komt automatisch in de juiste taal. Minimaal werk.</p>

<p>Zie ook: <a href="/nl/blog/buitenlandse-klanten-facturen">buitenlandse klanten factureren</a>, <a href="/nl/blog/ubl-peppol-facturen">UBL/PEPPOL</a>.</p>
HTML,
			],

			[
				'slug' => 'cashflow-mkb-tips',
				'title' => 'Cashflow-tips voor MKB: de facturatie-kant van het verhaal',
				'excerpt' => 'Cashflow-problemen bij MKB zijn zelden een omzetprobleem — het is meestal een inning-probleem. Zeven interventies die direct effect hebben.',
				'tags' => ['cashflow', 'facturatie', 'mkb'],
				'published_offset_days' => 115,
				'body' => <<<'HTML'
<p>Veel MKB-cashflow-issues zijn geen omzet-probleem maar een inning-probleem. Facturen zijn laat, herinneringen blijven uit, betaaltermijnen te lang. Zeven verbeteringen.</p>

<h2>1. Factureer direct na levering</h2>
<p>Niet "einde maand allemaal tegelijk". Klant krijgt de factuur op de dag dat ze waarde hebben ontvangen — maximum kans dat ze akkoord gaan.</p>

<h2>2. Korte betaaltermijnen voor nieuwe klanten</h2>
<p>14 dagen voor eerste 3 facturen. Daarna optioneel naar 30. Zo test je betaalgedrag voordat je commitment aangaat.</p>

<h2>3. Automatische <a href="/nl/blog/betalingsherinneringen-mkb">herinneringen</a></h2>
<p>Dag 3, dag 14, dag 28. Geen enkele manuele stap.</p>

<h2>4. iDEAL-link op elke factuur</h2>
<p>Drempel tot betalen verlagen. Mollie, Stripe, of je bank levert een directe betaal-QR of -link. Test: conversie op betaling stijgt typisch 15-25%.</p>

<h2>5. Aanbetaling voor grote projecten</h2>
<p>30-50% aanbetaling bij start. Rest in tranches. Je draagt nooit meer dan 2 maanden werk-in-uitvoering.</p>

<h2>6. Vroegbetaal-korting</h2>
<p>2% korting bij betaling binnen 10 dagen. Rekenkundig een hoge "rente" maar vaak psychologisch effectief — klanten voelen zich slim.</p>

<h2>7. Analyseer je debiteuren maandelijks</h2>
<p>DSO (Days Sales Outstanding) per maand tracken. Stijgt het? Investigate. Per klant: wie betaalt structureel laat? Misschien meer inzet op preventie.</p>

<p>Zie ook: <a href="/nl/blog/oninbare-vorderingen-mkb">oninbare vorderingen</a>, <a href="/nl/blog/auto-incasso-vs-handmatig">auto-incasso</a>.</p>
HTML,
			],
		];
	}
}
