<?php

namespace Database\Seeders\Blog;

use Illuminate\Database\Seeder;

/**
 * Bereikbaar voor klanten (08-10-2026). Tot die dag stond alles over bereikbaarheid alleen op de channel-sites
 * (branchestukjes: de loodgieter onder het aanrecht, de advocaat in de zitting); de hoofdsite had er niets over,
 * terwijl de AI-telefoniste een kernproduct is. Over de telefoniste staat hier alleen wat ze aantoonbaar doet
 * (zie resources/views/pages/kernaanbod/ai-telefoniste.blade.php): geen agenda, geen opnames, geen prijs.
 *
 *   php artisan db:seed --class="Database\Seeders\Blog\BlogBereikbaarheidSeeder" --force
 */
class BlogBereikbaarheidSeeder extends Seeder
{
	public function run(): void
	{
		BlogSeedHelper::seedCluster(
			[
				'slug' => 'bereikbaarheid',
				'name' => 'Bereikbaar voor klanten',
				'pillar_title' => 'Telefonisch bereikbaar met een klein team: vijf manieren naast elkaar',
				'intro' => 'Hoe je bereikbaar blijft voor klanten en patiënten als er niet altijd iemand kan opnemen. Met de voor- en nadelen van elke oplossing.',
				'sort_order' => 60,
			],
			self::posts(),
		);
	}

	private static function posts(): array
	{
		return [
			[
				'slug' => 'telefonisch-bereikbaar-met-een-klein-team',
				'title' => 'Telefonisch bereikbaar met een klein team: vijf manieren naast elkaar',
				'meta_title' => 'Telefonisch bereikbaar met een klein team: 5 opties vergeleken',
				'excerpt' => 'Voicemail, doorschakelen, een vast belmoment, een telefoniste of een AI-telefoniste. Wat elke oplossing goed doet, waar hij tekortschiet en hoe je kiest.',
				'is_pillar' => true,
				'featured' => true,
				'tags' => ['bereikbaarheid', 'telefonie', 'ai-telefoniste'],
				'published_offset_days' => 396,
				'body' => <<<'HTML'
<p>In een klein team gaat de telefoon altijd op het verkeerde moment. Iemand staat aan de balie, een ander zit in een gesprek, en de derde is vandaag vrij. De telefoon blijft overgaan, en wie belt hangt op en probeert het ergens anders.</p>

<p>Daar zijn verschillende oplossingen voor. Geen enkele is voor iedereen de beste. Hieronder zetten we de vijf meest gebruikte naast elkaar, met wat ze goed doen en waar ze tekortschieten.</p>

<h2>1. Voicemail</h2>
<p>De eenvoudigste oplossing, en vaak al aanwezig. De beller spreekt een bericht in en jij belt later terug.</p>
<ul>
  <li><strong>Goed:</strong> kost niets extra, werkt altijd, ook 's nachts.</li>
  <li><strong>Minder goed:</strong> veel mensen spreken niets in. Ze hangen op en bellen de volgende in hun lijstje. En wie wel iets inspreekt, zegt vaak alleen "wilt u terugbellen", zonder te vertellen waar het over gaat.</li>
</ul>
<p>Voicemail werkt redelijk voor bellers die jou per se willen spreken, zoals vaste klanten. Voor nieuwe klanten werkt het slecht.</p>

<h2>2. Doorschakelen naar een mobiel nummer</h2>
<p>Gaat er op kantoor niemand op, dan schakelt de lijn door naar de telefoon van een collega of van jezelf.</p>
<ul>
  <li><strong>Goed:</strong> de beller krijgt vaker een mens aan de lijn.</li>
  <li><strong>Minder goed:</strong> je verplaatst het probleem. Wie de doorgeschakelde telefoon heeft, wordt gestoord tijdens ander werk, of neemt alsnog niet op. Buiten werktijd wil je dit meestal niet.</li>
</ul>

<h2>3. Een vast belmoment</h2>
<p>Sommige praktijken en kantoren werken met een telefonisch spreekuur: tussen 8:00 en 10:00 wordt er altijd opgenomen, daarna niet.</p>
<ul>
  <li><strong>Goed:</strong> rust in het team, en bellers weten waar ze aan toe zijn.</li>
  <li><strong>Minder goed:</strong> het spreekuur is precies het moment waarop iedereen belt. De lijn is dan vaak bezet, en buiten dat uur sta je voor een dichte deur.</li>
</ul>

<h2>4. Een telefoniste of telefonische receptie</h2>
<p>Een medewerker of een extern bureau neemt de telefoon aan, noteert berichten en verbindt door.</p>
<ul>
  <li><strong>Goed:</strong> een mens aan de lijn, die kan inschatten wat er speelt.</li>
  <li><strong>Minder goed:</strong> een eigen medewerker is duur voor alleen de telefoon. Een extern bureau kent je bedrijf vaak niet goed, en kan dan weinig meer dan een bericht noteren.</li>
</ul>

<h2>5. Een AI-telefoniste</h2>
<p>Een AI-telefoniste neemt op, luistert naar de vraag en antwoordt uit de informatie die jij vastlegt: openingstijden, werkwijze, veelgestelde vragen. Kan ze het niet beantwoorden, dan noteert ze een terugbelverzoek of bericht, of verbindt ze door. Na elk gesprek krijg je een samenvatting.</p>
<ul>
  <li><strong>Goed:</strong> neemt altijd op, ook als het druk is of als je dicht bent, en beantwoordt de standaardvragen zelf. Wat overblijft, krijg je overzichtelijk binnen.</li>
  <li><strong>Minder goed:</strong> ze weet alleen wat jij haar vertelt. Een slordige of verouderde kennisbank geeft slechte antwoorden. En niet elke beller vindt het prettig om met een digitale assistent te praten, daarom hoort ze zich ook eerlijk voor te stellen.</li>
</ul>
<p>Wat een AI-telefoniste precies wel en niet kan, lees je in <a href="/nl/blog/wat-kan-een-ai-telefoniste-wel-en-niet">wat kan een AI-telefoniste wel, en wat niet</a>.</p>

<h2>Hoe kies je?</h2>
<p>Begin met drie vragen over je eigen telefoonverkeer:</p>
<ol>
  <li><strong>Waarover bellen mensen?</strong> Schrijf een week lang op wat bellers willen. Vaak blijkt de helft over dezelfde vijf vragen te gaan: openingstijden, de status van iets, hoe iets werkt.</li>
  <li><strong>Wanneer bellen ze?</strong> Zijn er vaste drukke momenten, of gaat de telefoon de hele dag door? En hoeveel wordt er buiten openingstijden gebeld?</li>
  <li><strong>Wat kost een gemiste beller?</strong> Bij een vaste klant valt dat mee: die belt terug. Bij een nieuwe klant of een spoedvraag kan het een opdracht zijn.</li>
</ol>
<p>Gaan de meeste gesprekken over dezelfde vragen, en mis je vooral bellers tijdens drukke momenten of buiten openingstijden, dan levert een AI-telefoniste het meeste op. Gaat bijna elk gesprek over maatwerk dat alleen jij kunt beantwoorden, dan is een goed geregeld terugbelproces belangrijker dan wie er opneemt. Daarover gaat <a href="/nl/blog/terugbelverzoeken-afhandelen">terugbelverzoeken afhandelen zonder dat er iets blijft liggen</a>.</p>

<h2>Combineren mag</h2>
<p>De meeste organisaties combineren. Tijdens openingstijden neemt een collega op en gaat de AI-telefoniste alleen aan als de lijn bezet is. Buiten openingstijden neemt zij het over. Wat ze niet kan oplossen, staat de volgende ochtend als terugbelverzoek klaar.</p>

<p>Wil je horen hoe zo'n gesprek klinkt? Op <a href="/nl/ai-telefoniste">de pagina over de AI-telefoniste</a> staat een demonummer van een verzonnen apotheek, dat je gewoon kunt bellen.</p>
HTML,
			],
			[
				'slug' => 'wat-kan-een-ai-telefoniste-wel-en-niet',
				'title' => 'Wat kan een AI-telefoniste wel, en wat niet?',
				'excerpt' => 'Opnemen, luisteren, vragen beantwoorden uit je eigen informatie, doorverbinden en een verslag sturen. En wat ze niet doet, zodat je weet waar je aan begint.',
				'tags' => ['ai-telefoniste', 'telefonie', 'bereikbaarheid'],
				'published_offset_days' => 397,
				'body' => <<<'HTML'
<p>Over AI aan de telefoon wordt veel beloofd. Dat maakt het lastig om in te schatten wat je er in de praktijk aan hebt. Daarom hier zonder omhaal wat onze AI-telefoniste doet, en wat ze niet doet.</p>

<h2>Wat ze doet</h2>

<h3>Ze neemt op en luistert eerst</h3>
<p>Er is geen keuzemenu met "toets 1 voor ...". De beller vertelt waarvoor hij belt, en de telefoniste vat samen wat ze begrepen heeft. Klopt dat niet, dan kan de beller het meteen verbeteren.</p>

<h3>Ze stelt zich eerlijk voor</h3>
<p>Aan het begin van elk gesprek zegt ze dat ze de digitale assistent van jouw organisatie is. De beller weet dus waar hij aan toe is.</p>

<h3>Ze antwoordt uit jouw informatie</h3>
<p>Openingstijden, werkwijze, veelgestelde vragen: ze antwoordt uit de kennisbank die jij zelf bijhoudt. Staat het antwoord er niet in, dan zegt ze dat ze het niet weet. Ze verzint niets. Hoe je zo'n kennisbank opzet, lees je in <a href="/nl/blog/kennisbank-voor-je-telefoniste">welke vragen moet je telefoniste kunnen beantwoorden</a>.</p>

<h3>Ze kent je openingstijden en feestdagen</h3>
<p>Ze weet hoe laat het is, of je open bent en welke feestdagen eraan komen. Is er een dag iets anders, bijvoorbeeld een middag dicht door een cursus, dan zet je een dagbericht klaar. Dat gebruikt ze binnen twee minuten.</p>

<h3>Ze verbindt door als het moet</h3>
<p>Wil de beller een mens spreken, of gaat het over een onderwerp dat jij altijd zelf wilt afhandelen, dan verbindt ze door. Dat gebeurt tijdens openingstijden.</p>

<h3>Ze noteert wat ze niet zelf kan oplossen</h3>
<p>De beller kiest: teruggebeld worden, het antwoord per e-mail krijgen of een bericht achterlaten. Een e-mailadres leest ze ter controle terug, zodat het antwoord ook echt aankomt.</p>

<h3>Ze spreekt meer talen</h3>
<p>Standaard Nederlands. Merkt ze dat de beller liever een andere taal spreekt, dan biedt ze Engels, Duits of Arabisch aan. Ze kan ook langzamer en duidelijker gaan praten.</p>

<h3>Je krijgt na elk gesprek een verslag</h3>
<p>Na elk gesprek krijg je een samenvatting per mail, en in je eigen portaal zie je alle gesprekken terug.</p>

<h3>Ze kan gekoppeld worden aan je eigen systeem</h3>
<p>Met een koppeling kan ze bijvoorbeeld de status van een bestelling opzoeken, nadat ze de beller heeft gecontroleerd met een postcode. Dat is maatwerk en hangt af van het systeem dat je gebruikt.</p>

<h2>Wat ze niet doet</h2>
<ul>
  <li><strong>Geen afspraken in je agenda zetten.</strong> Ze noteert een terugbelverzoek of bericht, of verbindt door. Het inplannen doe je zelf.</li>
  <li><strong>Geen gesprekken opnemen.</strong> Gesprekken worden niet opgenomen. Je krijgt een samenvatting van wat er besproken is.</li>
  <li><strong>Geen antwoorden verzinnen.</strong> Weet ze iets niet, dan zegt ze dat. Dat is soms minder handig dan een gok, maar wel eerlijker.</li>
  <li><strong>Geen oordeel geven waar een vakmens voor nodig is.</strong> Een medische vraag, een juridische inschatting of een offerte op maat hoort bij een mens. Zulke onderwerpen kun je zo instellen dat ze direct doorverbindt of een terugbelverzoek noteert.</li>
</ul>

<h2>Voor wie werkt het goed?</h2>
<p>Een AI-telefoniste levert het meeste op als een groot deel van de gesprekken over dezelfde vragen gaat, en als je bellers mist omdat iedereen bezig is of omdat je dicht bent. Denk aan een apotheek, een praktijk, een garage of een kantoor met een balie. Gaat bijna elk gesprek over maatwerk, dan is ze vooral een goede plek om terugbelverzoeken netjes binnen te krijgen.</p>

<p>Vergelijk je liever eerst alle opties, van voicemail tot telefoniste? Lees dan <a href="/nl/blog/telefonisch-bereikbaar-met-een-klein-team">telefonisch bereikbaar met een klein team</a>. Of bel het demonummer op <a href="/nl/ai-telefoniste">de pagina over de AI-telefoniste</a> en hoor zelf hoe een gesprek gaat.</p>
HTML,
			],
			[
				'slug' => 'kennisbank-voor-je-telefoniste',
				'title' => 'Welke vragen moet je telefoniste kunnen beantwoorden? Zo maak je de kennisbank',
				'meta_title' => 'Een kennisbank voor je (AI-)telefoniste opzetten',
				'excerpt' => 'Een telefoniste, mens of AI, is zo goed als de informatie die ze heeft. Zo verzamel je de vragen die echt gesteld worden en schrijf je antwoorden die werken aan de telefoon.',
				'tags' => ['ai-telefoniste', 'kennisbank', 'bereikbaarheid'],
				'published_offset_days' => 398,
				'body' => <<<'HTML'
<p>Een telefoniste kan alleen goede antwoorden geven als ze de juiste informatie heeft. Dat geldt voor een nieuwe medewerker aan de balie, en net zo goed voor een AI-telefoniste. Die laatste weet zelfs helemaal niets, behalve wat jij haar vertelt.</p>

<p>Een goede kennisbank hoeft niet groot te zijn. Hij moet vooral de vragen bevatten die echt gesteld worden, met antwoorden die aan de telefoon werken.</p>

<h2>Stap 1: verzamel de vragen die echt binnenkomen</h2>
<p>Begin niet met wat jij denkt dat mensen vragen. Houd een of twee weken bij wat bellers werkelijk willen weten. Een simpel lijstje naast de telefoon is genoeg: per gesprek een streepje bij de vraag.</p>
<p>Na twee weken zie je meestal hetzelfde patroon: een handvol vragen komt steeds terug. Bij een apotheek is dat bijvoorbeeld "ligt mijn medicijn klaar?", "tot hoe laat zijn jullie open?" en "hoe vraag ik een herhaalrecept aan?". Bij een garage gaat het vaak over "is mijn auto klaar?" en "kan ik morgen langskomen voor de APK?".</p>
<p>Kijk ook in je mailbox en bij je contactformulier. Wat daar gevraagd wordt, wordt ook gebeld.</p>

<h2>Stap 2: schrijf antwoorden voor het oor</h2>
<p>Een antwoord dat op een website prima staat, werkt aan de telefoon vaak niet. Een paar regels die helpen:</p>
<ul>
  <li><strong>Kort.</strong> Twee of drie zinnen. Wie meer wil weten, vraagt door.</li>
  <li><strong>Eerst het antwoord, dan de uitleg.</strong> "Ja, dat kan. U kunt dat doen via ..." in plaats van eerst de achtergrond.</li>
  <li><strong>Geen lijstjes en tabellen.</strong> Die kun je niet voorlezen. Schrijf de belangrijkste optie uit en zeg dat de rest per mail kan worden toegestuurd.</li>
  <li><strong>Concreet.</strong> Niet "neem contact met ons op", maar wat de beller nu kan doen: teruggebeld worden, een mail krijgen, langskomen tussen 9:00 en 12:00.</li>
</ul>

<h2>Stap 3: leg vast wat altijd naar een mens moet</h2>
<p>Minstens zo belangrijk als de antwoorden is de lijst met onderwerpen waar de telefoniste zich niet aan moet wagen. Denk aan klachten, medische of juridische inschattingen, prijsafspraken en alles wat met spoed te maken heeft.</p>
<p>Leg per onderwerp vast wat er dan moet gebeuren: direct doorverbinden tijdens openingstijden, of een terugbelverzoek met hoge prioriteit. Hoe je zorgt dat zo'n verzoek ook echt wordt opgepakt, lees je in <a href="/nl/blog/terugbelverzoeken-afhandelen">terugbelverzoeken afhandelen</a>.</p>

<h2>Stap 4: houd het bij</h2>
<p>Een kennisbank veroudert snel. Openingstijden veranderen rond de feestdagen, een dienst stopt, er komt een nieuwe werkwijze. Een paar gewoontes die helpen:</p>
<ul>
  <li><strong>Maak één persoon verantwoordelijk.</strong> Niet "iedereen", want dan doet niemand het.</li>
  <li><strong>Lees de gespreksverslagen.</strong> Zie je een vraag vaak terugkomen waar geen antwoord op was, dan hoort die in de kennisbank.</li>
  <li><strong>Gebruik dagberichten voor tijdelijke dingen.</strong> Een middag dicht of een storing is geen nieuw stuk kennisbank, maar een bericht voor vandaag. Bij onze <a href="/nl/ai-telefoniste">AI-telefoniste</a> zet je zo'n dagbericht in je portaal klaar, en ze gebruikt het binnen twee minuten.</li>
  <li><strong>Kijk elk kwartaal alles één keer door.</strong> Klopt het nog? Mist er iets? Staat er iets in wat niet meer geldt?</li>
</ul>

<h2>Wat je niet in de kennisbank zet</h2>
<p>Zet er geen gegevens van klanten of patiënten in. De kennisbank gaat over je organisatie: tijden, werkwijze, prijzen die vastliggen, procedures. Moet de telefoniste iets over één specifieke klant opzoeken, zoals de status van een bestelling, dan hoort dat via een koppeling met je eigen systeem, met een controle wie er belt.</p>

<h2>Een goed begin</h2>
<p>Je hoeft niet te wachten tot alles af is. Begin met de tien vragen die het vaakst gesteld worden en de lijst met onderwerpen die altijd naar een mens moeten. De rest groeit vanzelf aan de hand van de gespreksverslagen.</p>
HTML,
			],
			[
				'slug' => 'ai-telefoniste-en-privacy',
				'title' => 'Een AI-telefoniste en de privacy van je bellers: waar let je op?',
				'excerpt' => 'Wie belt, deelt persoonsgegevens. Vijf punten om na te lopen voordat je een AI-telefoniste inzet: eerlijk voorstellen, opnemen of niet, verslagen, bewaartermijn en afspraken met je leverancier.',
				'tags' => ['ai-telefoniste', 'avg', 'privacy'],
				'published_offset_days' => 399,
				'body' => <<<'HTML'
<p>Wie belt, deelt persoonsgegevens: een naam, een telefoonnummer, soms een adres of een vraag over iets persoonlijks. Zet je een AI-telefoniste in, dan wil je weten wat er met die gegevens gebeurt. Hieronder vijf punten om na te lopen. Dit is geen juridisch advies, wel een lijst om het gesprek met je leverancier en eventueel je adviseur goed te voeren.</p>

<h2>1. Weet de beller dat hij met een AI praat?</h2>
<p>Een beller mag weten met wie hij spreekt. Een AI-telefoniste die zich voordoet als mens, is geen goed idee. Ook niet als het technisch kan. Onze AI-telefoniste stelt zich aan het begin van elk gesprek voor als de digitale assistent van jouw organisatie. Wil de beller liever een mens, dan kan dat: ze verbindt door tijdens openingstijden, of noteert een terugbelverzoek.</p>

<h2>2. Worden gesprekken opgenomen?</h2>
<p>Een opname is een stuk gevoeliger dan een samenvatting. Er staat alles in, ook wat de beller terloops zegt. Vraag je leverancier dus of er wordt opgenomen, waar die opnames staan en wie erbij kan.</p>
<p>Bij onze AI-telefoniste worden gesprekken niet opgenomen. Je krijgt een samenvatting van wat er besproken is.</p>

<h2>3. Wat staat er in de verslagen, en wie leest ze?</h2>
<p>Ook een samenvatting bevat persoonsgegevens: de naam van de beller, zijn nummer en zijn vraag. Behandel die verslagen zoals je ander klantcontact behandelt:</p>
<ul>
  <li>Stuur ze naar een mailbox die alleen de mensen lezen die ermee moeten werken. Niet naar een algemene groep waar ook stagiairs en oud-medewerkers in zitten.</li>
  <li>Zet ze niet door naar privéadressen.</li>
  <li>Kijk wie er toegang heeft tot het portaal waarin de gesprekken staan, en haal mensen weg die er niets meer mee doen. Hoe je dat bijhoudt, lees je in <a href="/nl/blog/gedeelde-mappen-wie-heeft-nog-toegang">wie heeft nog toegang tot je gedeelde mappen</a>.</li>
</ul>

<h2>4. Hoe lang worden gegevens bewaard?</h2>
<p>Bewaar gespreksverslagen niet langer dan nodig. Spreek met je leverancier af hoe lang ze in het systeem blijven staan, en of je ze zelf kunt verwijderen. Leg vast hoe lang je ze in je eigen mailbox of administratie bewaart. Een terugbelverzoek dat is afgehandeld, hoeft meestal geen jaren te blijven staan.</p>

<h2>5. Is er een verwerkersovereenkomst?</h2>
<p>Verwerkt een leverancier persoonsgegevens voor jou, dan hoort daar een verwerkersovereenkomst bij. Daarin staat onder meer welke gegevens worden verwerkt, waar ze worden opgeslagen, welke andere partijen meekijken (bijvoorbeeld voor de spraaktechniek) en wat er gebeurt als er iets misgaat. Vraag erom als je hem niet automatisch krijgt.</p>

<h2>En je kennisbank?</h2>
<p>Zet in de kennisbank van je telefoniste geen gegevens van klanten of patiënten. De kennisbank gaat over je organisatie: openingstijden, werkwijze, veelgestelde vragen. Moet ze iets over één persoon opzoeken, zoals de status van een bestelling, dan hoort dat via een koppeling met je eigen systeem, met een controle wie er belt. Meer daarover in <a href="/nl/blog/kennisbank-voor-je-telefoniste">zo maak je de kennisbank voor je telefoniste</a>.</p>

<h2>Samengevat</h2>
<ul>
  <li>De beller weet dat hij met een digitale assistent praat, en kan een mens vragen.</li>
  <li>Je weet of er wordt opgenomen, en zo ja waar dat staat.</li>
  <li>Verslagen gaan naar een beperkte groep mensen.</li>
  <li>Er is een afspraak over hoe lang alles bewaard blijft.</li>
  <li>Er ligt een verwerkersovereenkomst.</li>
</ul>
<p>Wil je weten hoe dit bij onze <a href="/nl/ai-telefoniste">AI-telefoniste</a> geregeld is? Dan nemen we het in een kennismakingsgesprek met je door.</p>
HTML,
			],
			[
				'slug' => 'terugbelverzoeken-afhandelen',
				'title' => 'Terugbelverzoeken afhandelen zonder dat er iets blijft liggen',
				'excerpt' => 'Een terugbelverzoek is pas iets waard als er ook echt wordt teruggebeld. Zo regel je wie het oppakt, binnen welke tijd, en wat er gebeurt als iemand niet opneemt.',
				'tags' => ['bereikbaarheid', 'terugbellen', 'telefonie'],
				'published_offset_days' => 400,
				'body' => <<<'HTML'
<p>Wie niet direct iemand aan de lijn krijgt, laat een terugbelverzoek achter. Bij de voicemail, bij een collega, bij een telefoniste. En dan gebeurt het: het briefje raakt zoek, iedereen denkt dat een ander het oppakt, of er wordt teruggebeld als de klant al ergens anders heeft gekozen.</p>

<p>Een terugbelverzoek is pas iets waard als er ook echt wordt teruggebeld. Dat vraagt geen ingewikkeld systeem, wel een paar duidelijke afspraken.</p>

<h2>1. Alles komt op één plek binnen</h2>
<p>Verzoeken komen vaak via verschillende wegen binnen: een briefje aan de balie, een voicemail, een mail van de telefoniste, een bericht in een appgroep. Spreek af dat alles op één plek terechtkomt, bijvoorbeeld in een gedeelde mailbox of in het systeem waarin je ook klanten bijhoudt. Wat niet op die plek staat, bestaat niet.</p>

<h2>2. Een goed verzoek bevat genoeg om meteen te kunnen bellen</h2>
<p>"Bel Jansen terug" is geen bruikbaar verzoek. Zorg dat elk verzoek dit bevat:</p>
<ul>
  <li>naam en telefoonnummer;</li>
  <li>waar het over gaat, in één zin;</li>
  <li>of het spoed heeft;</li>
  <li>wanneer de beller bereikbaar is.</li>
</ul>
<p>Met die informatie kan wie terugbelt zich voorbereiden, en hoeft de klant zijn verhaal niet opnieuw te vertellen. Een <a href="/nl/ai-telefoniste">AI-telefoniste</a> vraagt dit standaard uit en stuurt na het gesprek een samenvatting.</p>

<h2>3. Er is altijd één eigenaar</h2>
<p>De belangrijkste afspraak: elk verzoek heeft één naam erbij. Niet "het team", maar Sanne of Mehmet. Wie het oppakt, zet zijn naam erbij. Ligt een verzoek na een uur nog zonder naam, dan pakt de vaste achtervang het op.</p>

<h2>4. Afgesproken reactietijd</h2>
<p>Spreek af binnen welke tijd er wordt teruggebeld. Bijvoorbeeld: spoed binnen een uur, de rest dezelfde werkdag, verzoeken van na sluitingstijd de volgende ochtend vóór 10:00. Zeg dat ook tegen de beller, of laat je telefoniste het zeggen. Een klant die weet dat hij vóór 10:00 wordt gebeld, belt niet ondertussen de concurrent.</p>

<h2>5. Wat als de klant niet opneemt?</h2>
<p>Terugbellen en geen gehoor krijgen gebeurt vaak. Leg vast wat dan de volgende stap is:</p>
<ol>
  <li>Spreek een kort bericht in met je naam en een moment waarop je het opnieuw probeert.</li>
  <li>Stuur een sms of mail als je een adres hebt.</li>
  <li>Probeer het nog één keer op een ander moment van de dag.</li>
  <li>Noteer het resultaat bij het verzoek, zodat een collega weet waar het staat.</li>
</ol>

<h2>6. Afsluiten is ook een stap</h2>
<p>Een verzoek is pas klaar als het is afgesloten, met een korte notitie wat er is afgesproken. Zo blijft de lijst met openstaande verzoeken klein en betrouwbaar. Een lijst waar afgehandelde en open verzoeken door elkaar staan, wordt binnen een paar weken niet meer gelezen.</p>

<h2>Eén keer per week kijken</h2>
<p>Kijk eens per week naar de verzoeken van die week. Waar ging het over? Kwamen dezelfde vragen vaak terug? Dan kun je die vragen misschien al aan de telefoon beantwoorden, door ze aan de kennisbank van je telefoniste toe te voegen. Hoe dat werkt, lees je in <a href="/nl/blog/kennisbank-voor-je-telefoniste">zo maak je de kennisbank</a>.</p>

<p>Twijfel je nog welke manier van bereikbaar zijn bij jouw organisatie past? In <a href="/nl/blog/telefonisch-bereikbaar-met-een-klein-team">telefonisch bereikbaar met een klein team</a> zetten we vijf manieren naast elkaar.</p>
HTML,
			],
		];
	}
}
