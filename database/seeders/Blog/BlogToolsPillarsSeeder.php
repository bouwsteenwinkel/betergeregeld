<?php

namespace Database\Seeders\Blog;

use Illuminate\Database\Seeder;

/**
 * Drie pillar-blog-posts voor de tool-pagina's met de meeste GSC-impressies
 * (status 2026-05-22): vat-check, iban-check, postcode-check. Doel:
 * dieper-doorlinken op informationele intent zodat we naast tool-traffic
 * ook lezers binnenhalen die nog niet wisten dat ze de tool nodig hadden.
 *
 * Categorie: 'tools-uitleg' (nieuw — apart van security/compliance).
 */
class BlogToolsPillarsSeeder extends Seeder
{
	public function run(): void
	{
		BlogSeedHelper::seedCluster(
			[
				'slug'         => 'tools-uitleg',
				'name'         => 'Tools & checks uitgelegd',
				'pillar_title' => 'Onze gratis tools — wanneer gebruik je ze',
				'intro'        => 'Korte uitleg per tool: waar is het voor, wanneer is het nuttig, en waar ligt de grens van wat een quick-check je kan vertellen.',
				'sort_order'   => 20,
			],
			self::posts(),
		);
	}

	private static function posts(): array
	{
		return [
			[
				'slug' => 'vies-btw-nummer-controleren-uitleg',
				'title' => 'VIES BTW-nummer controleren: wat is het, waarom moet het en hoe doe je het snel',
				'excerpt' => 'Als je een factuur stuurt naar een ondernemer in een ander EU-land, ben je vaak verplicht om het BTW-nummer te verifiëren via VIES. Wat is VIES, wat controleert het wel en niet, en hoe houd je een audit-trail bij?',
				'is_pillar' => true,
				'featured' => true,
				'tags' => ['vies', 'btw', 'factuur', 'eu', 'compliance', 'start-hier'],
				'published_offset_days' => 1,
				'body' => <<<'HTML'
<p>Als je vanuit Nederland een factuur stuurt naar een ondernemer in een ander EU-land, ben je in veel situaties verplicht om <strong>vooraf</strong> te controleren of het opgegeven BTW-nummer geldig is. Doe je dat niet en gaat het later mis — fictieve bedrijven, ingetrokken BTW-nummers, typefouten — dan kan de Belastingdienst je het 0%-BTW-tarief alsnog ontzeggen en de niet-afgedragen BTW bij <em>jou</em> in rekening brengen.</p>

<p>Voor die controle bestaat één Europese bron: het <strong>VIES-systeem</strong> (VAT Information Exchange System). Deze gids legt uit wat VIES is, wat het wel en niet kan, en hoe je de check praktisch organiseert — inclusief audit-trail voor je administratie.</p>

<h2>Wat is VIES precies?</h2>
<p>VIES is een gratis dienst van de Europese Commissie waarop de BTW-administraties van alle EU-lidstaten zijn aangesloten. Je voert een BTW-nummer in, kiest de lidstaat, en VIES vraagt direct bij de nationale belastingdienst van dat land na of het nummer op dit moment geldig is. Vaak krijg je ook de naam en het adres dat bij dat nummer hoort terug.</p>
<p>Belangrijk om te begrijpen: <strong>VIES is een look-up, geen registratie</strong>. Het beantwoordt alleen de vraag "bestaat dit BTW-nummer op dit moment?". Het bewijst <em>niet</em> dat de aankoop daadwerkelijk een zakelijke transactie was, en het zegt niets over de financiële gezondheid van de partij.</p>

<h2>Wanneer ben je verplicht om te checken?</h2>
<ul>
<li><strong>Bij een intracommunautaire goederenlevering</strong> — als je goederen verkoopt naar een ondernemer in een ander EU-land en je het 0%-BTW-tarief wil toepassen, moet je het BTW-nummer van de koper geldig hebben op het moment van levering.</li>
<li><strong>Bij intracommunautaire diensten (B2B)</strong> — de BTW wordt verlegd naar de afnemer; daarvoor moet diens BTW-nummer geldig zijn.</li>
<li><strong>Bij periodieke opgave</strong> — de Opgaaf intracommunautaire prestaties wordt afgewezen als er ongeldige BTW-nummers in staan.</li>
</ul>
<p>De Belastingdienst kijkt naar één concrete vraag: <strong>kon je redelijkerwijs weten dat het BTW-nummer ongeldig was?</strong> Als je een VIES-check hebt gedaan op het moment van factureren en een geldig antwoord kreeg, ben je gevrijwaard — ook als het nummer later wordt ingetrokken.</p>

<h2>Wat is een goede audit-trail?</h2>
<p>De wet eist niet één specifieke manier van vastleggen, maar de praktijk laat zien dat de Belastingdienst tevreden is met:</p>
<ul>
<li>Het BTW-nummer dat je controleerde</li>
<li>De datum en het tijdstip van de check</li>
<li>De uitkomst (geldig / ongeldig)</li>
<li>De naam en adresgegevens die VIES teruggaf</li>
<li>Een referentie naar de factuur of opdracht</li>
</ul>
<p>Een screenshot van de VIES-website is OK, maar handig is dat niet. Praktischer: een tool die de check doet en het resultaat direct opslaat in een logboek. Onze <a href="/nl/tools/vat-check">VIES-check</a> doet precies dat: per controle bewaar je naam, adres, uitkomst en tijdstip in een exporteerbaar overzicht.</p>

<h2>Veelgemaakte fouten</h2>
<ol>
<li><strong>Eénmalig checken bij eerste factuur</strong>. BTW-nummers worden ingetrokken bij faillissement, fraude of fiscale opheffing. Bij langdurige klanten is het verstandig om periodiek (kwartaal of jaarlijks) opnieuw te controleren.</li>
<li><strong>Vertrouwen op een typefout-tolerant antwoord</strong>. Sommige typfouten passeren VIES per ongeluk (bv. dezelfde lengte als een geldig ander nummer). Controleer of de teruggegeven naam ook past bij de klant.</li>
<li><strong>Het Britse BTW-nummer (GB...) via VIES proberen te checken</strong> — sinds Brexit is het VK niet meer aangesloten op VIES. Voor GB-nummers heb je <em>HMRC's</em> aparte check-systeem nodig.</li>
<li><strong>Het nummer alleen voor de eigen administratie noteren</strong>. Bewaar ook de uitkomst — de Belastingdienst kijkt niet alleen of het destijds geldig was, maar of jíj dat destijds wist.</li>
</ol>

<h2>Wanneer hoeft het juist niet?</h2>
<p>Verkoop je aan een particuliere consument in een ander EU-land? Dan reken je gewoon Nederlands BTW (of, vanaf een omzetdrempel, BTW van het land van de consument via de One Stop Shop-regeling). Geen VIES nodig. VIES is uitsluitend voor B2B-transacties met het 0%-tarief of verlegging.</p>

<h2>Samenvatting</h2>
<p>VIES is gratis, snel en wettelijk de juiste manier om EU-BTW-nummers te valideren voordat je factureert met 0%-BTW. Doe het op het moment van factureren, bewaar de uitkomst (naam + adres + datum), en check periodiek opnieuw bij vaste klanten. Het scheelt later veel uitleg richting de Belastingdienst — en in het ergste geval voorkomt het dat je BTW moet nabetalen die je niet hebt ontvangen.</p>

<p><strong>Direct controleren?</strong> Onze <a href="/nl/tools/vat-check">VIES BTW-check</a> doet de validatie en houdt automatisch een logboek bij van iedere controle.</p>
HTML,
			],

			[
				'slug' => 'iban-check-met-naam-uitleg',
				'title' => 'IBAN op naam checken: waarom de bank het niet meer doet, en hoe je het zelf regelt',
				'excerpt' => 'Sinds 2024 controleren Nederlandse banken de naam bij een IBAN niet meer automatisch op alle betalingen. Voor zakelijke administraties is dat een risico — hier hoe je het zelf praktisch oppakt.',
				'is_pillar' => true,
				'tags' => ['iban', 'sepa', 'fraude', 'administratie', 'naamcheck'],
				'published_offset_days' => 2,
				'body' => <<<'HTML'
<p>Tot een paar jaar terug controleerde je bank automatisch bij iedere overboeking of de opgegeven naam paste bij de IBAN. Sinds de invoering van <strong>SEPA Instant</strong> en de bredere uitrol van directe betalingen is dat in veel scenario's verdwenen. Voor consumenten is het ongemak meestal beperkt; voor zakelijke administraties is het een serieus risico — vooral bij <strong>incassobatches, masseringen van crediteuren-bestanden, en factuur-fraude</strong>.</p>

<h2>Wat ging er veranderen?</h2>
<p>De Europese betaalstandaard SEPA Instant verplicht banken om overboekingen binnen 10 seconden af te wikkelen. Een uitgebreide naam-IBAN-match — die historisch vooral werkte voor <em>Nederlandse</em> rekeningen omdat de bank toegang heeft tot de tegenpartij's administratie — past niet meer binnen dat tijdsvenster voor alle scenario's. Vanaf 2024 is daarom een Europees alternatief verplicht: <strong>Verification of Payee (VoP)</strong>. VoP doet wel een match, maar geeft minder rijk antwoord ("close match" vs "exact match" vs "no match") en het detail verschilt per bank.</p>

<h2>Waarom is dit voor MKB-administratie een probleem?</h2>
<ul>
<li><strong>Crediteurenmutaties</strong> — een leverancier mailt een nieuw rekeningnummer; je administratie wijzigt het zonder verdere check; volgende factuur gaat naar een fraudeur. Bekend onder de naam "CEO-fraude" of "factuur-spoof".</li>
<li><strong>Massa-uploads in boekhoudpakketten</strong> — bij een import van 100 nieuwe debiteuren wordt vaak alleen syntactisch gecheckt (klopt het IBAN-formaat?), niet of de naam erbij past.</li>
<li><strong>Loonbatches en eenmalige uitbetalingen</strong> — een typo in een IBAN kan een betaling op een onbedoelde rekening laten landen. Terughalen is mogelijk maar tijdrovend (rekeninghouder moet meewerken).</li>
</ul>

<h2>Wat kun je zelf doen?</h2>
<p>De Belastingdienst, Kamer van Koophandel en de banken zelf adviseren <strong>drie lagen</strong>:</p>
<ol>
<li><strong>Syntactische validatie</strong> — controleer of het IBAN voldoet aan het formaat (lengte, controlegetallen). Dit vangt alleen typefouten af, niet fraude.</li>
<li><strong>Naam-IBAN-match (heuristisch)</strong> — voor Nederlandse IBANs zijn er publieke services en commerciële diensten die op basis van eerdere betalingen + KvK-data een match-score teruggeven. Niet 100% sluitend, maar wel een signaal.</li>
<li><strong>Verification of Payee bij overboeking</strong> — als je bank dit ondersteunt: gebruik het. Bij twijfel: bel de leverancier op een nummer dat je <em>al</em> kent (niet uit de verdachte e-mail).</li>
</ol>

<h2>Praktisch beleid voor een MKB-administratie</h2>
<p>Vier afspraken die het verschil maken:</p>
<ul>
<li>Wijziging van een crediteur-IBAN gaat <strong>nooit</strong> per e-mail door zonder bevestiging via een ander kanaal (telefoon, in persoon).</li>
<li>Bij de eerste factuur aan een nieuwe leverancier wordt het IBAN <strong>visueel en automatisch</strong> gecontroleerd op naam-match.</li>
<li>Voor incassobatches geldt: een batch-bestand wordt voor verzending gevalideerd op syntactische correctheid van alle IBANs.</li>
<li>Alle controles worden gelogd zodat je bij een eventueel incident kunt aantonen welke check is uitgevoerd.</li>
</ul>

<h2>Wat onze tool doet</h2>
<p>Onze <a href="/nl/tools/iban-check">IBAN-check</a> doet de syntactische validatie plus een heuristische naam-IBAN-match voor Nederlandse rekeningen. Het is een hulpmiddel — geen bankgarantie — maar voorkomt de meest voorkomende fouten in administratie-imports. Voor zakelijk gebruik kun je een batch-bestand uploaden en krijg je per regel een match-score met uitleg.</p>

<p>De combinatie van een tool plus heldere interne afspraken is in de praktijk waar fraude wordt gestopt. Eén van beide alleen is niet genoeg.</p>
HTML,
			],

			[
				'slug' => 'postcode-check-nl-be-de-uitleg',
				'title' => 'Postcode checken in NL, BE en DE: waarom een quick-check de helft van je fulfilment-problemen voorkomt',
				'excerpt' => 'Een typefout in de postcode kost gemiddeld een paar tientjes per geretourneerd pakket. Een snelle sanity-check op postcode + huisnummer scheelt vaak meer dan vier euro per order.',
				'tags' => ['postcode', 'fulfilment', 'logistiek', 'verzending'],
				'published_offset_days' => 3,
				'body' => <<<'HTML'
<p>In bijna elke webshop-administratie die wij langs zien komen, zit een onderschatte kostenpost: <strong>retouren door foute adresgegevens</strong>. Een paar procent van alle pakketten komt onbestelbaar retour, vaak door een typefout in de postcode of een ontbrekend huisnummer. Per geretourneerd pakket loopt het op tot €4–€10 (verzending heen + retour + opnieuw verzenden + handling), én je klant ervaart vertraging.</p>

<p>De meeste van die fouten zijn met een <strong>simpele formaat-check</strong> al af te vangen — en je hoeft geen externe API met API-key te raadplegen voor verreweg het grootste deel.</p>

<h2>De drie formaten</h2>
<ul>
<li><strong>Nederland</strong> — vier cijfers, één spatie, twee hoofdletters: <code>1403 SL</code>. De letters mogen niet S, A, D, F, I, O, Q, U, Y zijn (officiële uitsluitingen door PostNL ter voorkoming van verwarring). De eerste cijfer mag geen 0 zijn.</li>
<li><strong>België</strong> — vier cijfers, geen letters: <code>1000</code> (Brussel). Range loopt van 1000 t/m 9999.</li>
<li><strong>Duitsland</strong> — vijf cijfers, geen letters: <code>10115</code> (Berlijn). Range loopt van 01067 t/m 99998.</li>
</ul>

<h2>Wat een goede check wél doet</h2>
<p>Een sanity-check op postcode + huisnummer kan:</p>
<ol>
<li><strong>Formaat-validatie</strong> — past de invoer in het patroon van de gekozen land?</li>
<li><strong>Range-check</strong> — valt het cijfer binnen de geldige range voor dat land? (Geen <code>0123 AB</code> voor NL, geen <code>00000</code> voor DE.)</li>
<li><strong>Letter-uitsluitingen</strong> — voor NL: signaleer postcodes met onmogelijke letter-combinaties.</li>
<li><strong>Patroon-detectie</strong> — vlag invoer die er verdacht uitziet (alleen nullen, herhalingen als <code>1111 AA</code>, of identiek aan een veelgebruikte placeholder).</li>
</ol>

<h2>Wat een check niet kan (zonder externe API)</h2>
<ul>
<li>Bevestigen dat een combinatie <em>bestaat</em> als adres — daarvoor heb je een postcode-database nodig (PostNL, BAG, Bpost).</li>
<li>Aangeven of de combinatie postcode + huisnummer + plaats een geldig adres oplevert.</li>
<li>Detecteren of het adres residentieel of zakelijk is.</li>
</ul>
<p>Voor de meeste webshop-fouten zit de winst echter <em>vóór</em> dat niveau. Een formaat-check filtert in onze ervaring 60–80% van de echte verzendfouten eruit, zonder afhankelijkheid van een externe service.</p>

<h2>Waar plak je de check in?</h2>
<ul>
<li><strong>In het checkout-proces</strong> — direct na invoer van het adres. Vlag een waarschuwing, blokkeer niet (anders raak je klanten kwijt die per ongeluk een vreemde combinatie hebben getypt).</li>
<li><strong>Op CSV-imports</strong> — bij het importeren van klantenbestanden uit andere systemen. Filter en log de afwijkers <em>vóórdat</em> je ermee gaat factureren.</li>
<li><strong>In een interne admin-tool</strong> — voor je medewerkers die met de hand adresgegevens corrigeren, zodat ze direct zien of de wijziging zelf wel klopt.</li>
</ul>

<h2>Een snelle DIY-validator</h2>
<p>Onze <a href="/nl/tools/postcode-check">Postcode sanity check</a> ondersteunt NL, BE en DE en flagt naast formaatfouten ook opvallende patronen (alleen nullen, herhalingen, onmogelijke combinaties). Geen API-key nodig, geen registratie — handig voor een steekproef of voor het eenmalig opschonen van een adressenbestand.</p>

<p>Verzending verbeteren begint zelden bij de verzender. Het begint bij de data <em>vóór</em> de verzending — en daar is dit een goedkope, snelle eerste stap.</p>
HTML,
			],
		];
	}
}
