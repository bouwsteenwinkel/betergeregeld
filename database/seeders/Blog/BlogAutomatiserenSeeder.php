<?php

namespace Database\Seeders\Blog;

use Illuminate\Database\Seeder;

/**
 * Werk automatiseren (08-10-2026). Aanvulling op wat er al stond (welk proces eerst, terugkerende facturen,
 * betalingsherinneringen, rapportages): klanten op de hoogte houden en deadlines bewaken ontbraken op de
 * hoofdsite. Zelfde onderscheid als /nl/processen-automatiseren: vaste regels, geen AI.
 *
 *   php artisan db:seed --class="Database\Seeders\Blog\BlogAutomatiserenSeeder" --force
 */
class BlogAutomatiserenSeeder extends Seeder
{
	public function run(): void
	{
		BlogSeedHelper::seedCluster(
			[
				'slug' => 'automatiseren',
				'name' => 'Werk automatiseren',
				'pillar_title' => null,
				'intro' => 'Taken die nu alleen gebeuren als iemand eraan denkt, volgens vaste regels laten lopen. Zonder dat het een groot IT-project wordt.',
				'sort_order' => 70,
			],
			self::posts(),
		);
	}

	private static function posts(): array
	{
		return [
			[
				'slug' => 'klanten-automatisch-op-de-hoogte-houden',
				'title' => 'Klanten automatisch op de hoogte houden van hun opdracht',
				'excerpt' => '"Hoe staat het ermee?" is een van de meest gestelde vragen aan de telefoon. Met een bericht bij elke stap in je proces hoeft de klant niet meer te bellen.',
				'tags' => ['automatiseren', 'klantcontact', 'statusupdates'],
				'published_offset_days' => 401,
				'body' => <<<'HTML'
<p>Een groot deel van de telefoontjes en mails die bij kleine bedrijven binnenkomen, gaat over één vraag: hoe staat het ermee? Is mijn auto klaar? Is mijn bestelling verstuurd? Is mijn aanvraag al bekeken? Elk van die vragen kost een collega tijd, en de klant ergert zich omdat hij moet vragen wat hij eigenlijk had willen horen.</p>

<p>Dat is op te lossen met een simpele regel: bij elke stap in je proces krijgt de klant automatisch een bericht.</p>

<h2>Begin bij je eigen stappen</h2>
<p>Schrijf de stappen op die een opdracht bij jou doorloopt. Houd het grof, vijf tot acht stappen is genoeg. Bij een garage bijvoorbeeld:</p>
<ol>
  <li>Afspraak gemaakt</li>
  <li>Auto binnen</li>
  <li>Onderdelen besteld (als dat nodig is)</li>
  <li>Klaar voor ophalen</li>
  <li>Opgehaald en betaald</li>
</ol>
<p>Bij een kantoor dat aanvragen behandelt, ziet het er anders uit: ontvangen, in behandeling, aanvullende informatie nodig, afgerond. Het principe is hetzelfde.</p>

<h2>Kies bij welke stappen de klant iets hoort</h2>
<p>Niet elke stap is interessant voor de klant. Een goede vuistregel: stuur een bericht als er iets verandert waar de klant op wacht, of als de klant zelf iets moet doen. Bij de garage zijn dat "auto binnen", "onderdelen besteld, verwacht op donderdag" en "klaar voor ophalen". De interne stappen houd je voor jezelf.</p>
<p>Te veel berichten werkt averechts. Na het vijfde mailtje leest niemand ze meer, en dan mist de klant juist het bericht dat ertoe doet.</p>

<h2>Wat staat er in het bericht?</h2>
<p>Een goed statusbericht is kort en beantwoordt drie vragen:</p>
<ul>
  <li><strong>Wat is er gebeurd?</strong> "Uw auto is klaar."</li>
  <li><strong>Wat betekent dat voor de klant?</strong> "U kunt hem ophalen tot 17:30."</li>
  <li><strong>Moet hij iets doen?</strong> "Neem uw kenteken- of afspraaknummer mee." Of: "U hoeft niets te doen, wij melden ons als de onderdelen er zijn."</li>
</ul>
<p>Zet er altijd bij hoe de klant je bereikt als er iets niet klopt. En stuur het bericht via het kanaal dat de klant het meest leest. Voor de meeste mensen is dat sms of WhatsApp eerder dan e-mail.</p>

<h2>Hoe het automatisch gaat</h2>
<p>De regel is eenvoudig: als de status in je systeem verandert, gaat het bijbehorende bericht naar de klant. Dat werkt als je stappen op één plek bijhoudt, in je planning, je kassasysteem of je dossiersysteem. Houd je ze in iemands hoofd of in een Excel-lijst bij, dan is dat de eerste stap om te veranderen.</p>
<p>Veel pakketten kunnen zelf al berichten versturen bij een statuswijziging. Kijk dus eerst wat je huidige software al kan. Kan het daar niet, dan is een koppeling vaak voldoende: het systeem geeft door dat de status is veranderd, en een kleine automatisering stuurt het bericht.</p>

<h2>Waar je op let</h2>
<ul>
  <li><strong>Status moet kloppen.</strong> Een automatisch bericht is zo betrouwbaar als de status in je systeem. Wordt een auto pas 's avonds op "klaar" gezet, dan staat de klant 's middags voor niets aan de balie. Spreek af wie de status wanneer bijwerkt.</li>
  <li><strong>Niet sturen bij uitzonderingen.</strong> Moet er iets uitgelegd worden, zoals een onverwachte extra reparatie, dan belt een mens. Zet zo'n opdracht op een status die geen automatisch bericht stuurt.</li>
  <li><strong>Afmelden kan.</strong> Wil een klant geen berichten, dan moet dat kunnen. Voor reclame gelden strengere regels dan voor een bericht over een lopende opdracht, dus houd een statusbericht ook echt bij de status.</li>
</ul>

<h2>Wat het oplevert</h2>
<p>Minder "hoe staat het ermee"-telefoontjes, klanten die weten waar ze aan toe zijn, en collega's die kunnen doorwerken. Het is vaak een van de eerste processen die het automatiseren waard zijn, omdat het weinig kost en je het effect direct merkt. Twijfel je welk proces je als eerste aanpakt? Lees dan <a href="/nl/blog/welk-proces-eerst-automatiseren">welk proces automatiseer je als eerste</a>.</p>

<p>Belt er toch iemand met een statusvraag terwijl niemand kan opnemen? Dan kan een <a href="/nl/ai-telefoniste">AI-telefoniste</a>, gekoppeld aan je systeem, de status opzoeken nadat ze heeft gecontroleerd wie er belt. Wil je weten wat er bij jou automatisch kan, kijk dan bij <a href="/nl/processen-automatiseren">processen automatiseren</a>.</p>
HTML,
			],
			[
				'slug' => 'deadlines-en-verlengingen-automatisch-bewaken',
				'title' => 'Verlengingen en deadlines bewaken zonder Excel-lijstje',
				'excerpt' => 'Contracten, certificaten, keuringen, abonnementen: wat afloopt, wordt vaak bijgehouden in een agenda of een lijstje van één collega. Zo laat je het bewaken volgens vaste regels.',
				'tags' => ['automatiseren', 'deadlines', 'herinneringen'],
				'published_offset_days' => 402,
				'body' => <<<'HTML'
<p>In elke organisatie lopen dingen af. Een huurcontract, een domeinnaam, een certificaat van de website, een keuring, een verzekering, de licentie van je boekhoudpakket, het VCA-diploma van een medewerker. Meestal houdt één collega dat bij in een agenda of een Excel-lijst. Dat gaat goed, tot die collega ziek is, vertrekt of het gewoon druk heeft.</p>

<p>De gevolgen zijn soms klein, zoals een abonnement dat stilzwijgend een jaar doorloopt. Soms zijn ze groter, zoals een website die offline gaat omdat het certificaat is verlopen, of een domeinnaam die vrijkomt.</p>

<h2>Stap 1: maak één lijst met alles wat afloopt</h2>
<p>Begin met inventariseren. Loop met een paar collega's na wat er in jullie organisatie een einddatum of verlengdatum heeft. Een paar categorieën om aan te denken:</p>
<ul>
  <li><strong>Contracten:</strong> huur, lease, leveranciers, onderhoudscontracten.</li>
  <li><strong>Abonnementen en licenties:</strong> software, telefonie, internet, vakbladen.</li>
  <li><strong>Online:</strong> domeinnamen, certificaten van je website, hosting.</li>
  <li><strong>Keuringen en certificaten:</strong> brandblussers, apparatuur, voertuigen, diploma's en vergunningen van medewerkers.</li>
  <li><strong>Verzekeringen</strong> en jaarlijkse aangiftes of rapportages.</li>
</ul>
<p>Noteer per regel: wat het is, de einddatum, de opzegtermijn, wie verantwoordelijk is, en wat er moet gebeuren (verlengen, opzeggen, opnieuw keuren).</p>

<h2>Stap 2: rekenen vanaf de opzegtermijn, niet vanaf de einddatum</h2>
<p>De meest gemaakte fout: een herinnering zetten op de einddatum. Dan ben je vaak al te laat. Een contract met drie maanden opzegtermijn moet je drie maanden van tevoren opzeggen. De herinnering hoort dus ruim daarvoor te komen.</p>
<p>Een praktische regel: de eerste herinnering op het moment dat je nog alle opties hebt (opzegtermijn plus een paar weken om te overleggen), en een tweede als de opzegtermijn bijna verstreken is.</p>

<h2>Stap 3: laat de herinnering naar een rol gaan, niet naar een persoon</h2>
<p>Stuur herinneringen niet naar het mailadres van één collega, maar naar een gedeelde mailbox of naar een rol: de office manager, de beheerder van de wagens. Vertrekt iemand, dan blijven de herinneringen gewoon aankomen. Leg vast wie de achtervang is als de eerste persoon er niet is.</p>

<h2>Stap 4: een herinnering moet opgevolgd worden</h2>
<p>Een herinnering die wordt weggeklikt, helpt niet. Een paar manieren om te zorgen dat er iets mee gebeurt:</p>
<ul>
  <li>De herinnering blijft terugkomen tot iemand aangeeft wat er is gedaan: verlengd, opgezegd of bewust laten lopen.</li>
  <li>Na de tweede herinnering zonder reactie gaat er een melding naar een leidinggevende.</li>
  <li>Elke maand komt er een kort overzicht van wat de komende drie maanden afloopt.</li>
</ul>

<h2>Stap 5: laat wat gecontroleerd kan worden ook gecontroleerd worden</h2>
<p>Sommige dingen kun je niet alleen herinneren, maar ook automatisch controleren. Het certificaat van je website bijvoorbeeld: in plaats van een datum in een lijst kun je de site zelf laten controleren. Met onze gratis <a href="/nl/tools/ssl-check">SSL-check</a> zie je in een paar seconden wanneer je certificaat verloopt. Hetzelfde geldt voor back-ups: kijk niet of het lijstje zegt dat er een back-up is, maar of hij er echt staat. Daarover gaat <a href="/nl/blog/back-up-getest-of-alleen-gemaakt">back-up gemaakt, maar ook getest?</a></p>

<h2>Excel is prima om te beginnen</h2>
<p>Je hoeft niet meteen nieuwe software aan te schaffen. Een gedeelde lijst met de kolommen hierboven en een agenda-afspraak per regel is al een grote stap vooruit. Wordt de lijst langer, werken er meer mensen mee, of wil je dat herinneringen terugkomen tot iemand reageert, dan is het tijd om het te automatiseren. Wat er dan mogelijk is, lees je op <a href="/nl/processen-automatiseren">processen automatiseren</a>.</p>

<p>Andere taken die vaak de moeite waard zijn om te automatiseren: <a href="/nl/blog/terugkerende-facturen-automatiseren">terugkerende facturen</a> en <a href="/nl/blog/betalingsherinneringen-mkb">betalingsherinneringen</a>.</p>
HTML,
			],
		];
	}
}
