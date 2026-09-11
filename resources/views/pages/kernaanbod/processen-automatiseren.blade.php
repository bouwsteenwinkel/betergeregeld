@extends('layouts.app')

@php
	// NAAST, NIET IN PLAATS VAN DE AI-PAGINA. /nl/slimmer-werken-met-ai gaat over
	// werk waarbij een systeem tekst moet lezen, beoordelen of schrijven. Deze pagina
	// gaat over werk dat met vaste regels af kan: als dit gebeurt, doe dan dat. Houd
	// dat onderscheid in de tekst vast, anders concurreren de twee pagina's om
	// dezelfde zoekvraag (besluit Dennis 11-09-2026 om ze allebei te hebben).
	$hreflangLocales = ['nl'];
	$p = [
		'slug'    => 'processen-automatiseren',
		'topic'   => 'processen-automatiseren',
		'kruimel' => 'Processen automatiseren',
		'pill'    => 'Automatisering',
		'h1a'     => 'Werk dat zichzelf',
		'h1b'     => 'afhandelt.',
		'lead'    => 'Herinneringen, opvolging, controles en overdrachten die nu handwerk zijn, lopen automatisch volgens vaste regels. Zonder dat iemand eraan hoeft te denken.',

		'herken_titel' => 'Taken die alleen gebeuren als iemand eraan denkt.',
		'intro' => [
			'In elke organisatie zijn er taken die niet moeilijk zijn, maar wel precies op tijd moeten gebeuren: een herinnering, een controle, een bericht aan de klant.',
			'Zolang iemand eraan denkt, gaat het goed. Tot iemand ziek is of op vakantie. Voor dit soort werk is geen slimme technologie nodig, alleen een vaste regel die altijd wordt uitgevoerd.',
		],
		'herken' => [
			'Betalingsherinneringen worden verstuurd als iemand er tijd voor heeft.',
			'Klanten horen pas iets als ze zelf bellen om te vragen hoe het staat.',
			'Elke maandag wordt dezelfde rapportage met de hand in elkaar gezet.',
			'Verlengingen en deadlines worden bijgehouden in een agenda of een Excel-lijst.',
			'Controles, zoals een back-up, gebeuren "meestal wel".',
		],

		'wat_pill'  => 'Wat er te automatiseren valt',
		'wat_titel' => 'Als dit gebeurt, doe dan dat.',
		'wat_intro' => 'Dit zijn voorbeelden van processen die zich goed lenen voor automatisering met vaste regels.',
		'wat' => [
			['icon' => '⏰', 't' => 'Herinneringen op tijd',     'b' => 'Voor afspraken, verlengingen, retouren of deadlines, een vast aantal dagen van tevoren.'],
			['icon' => '💶', 't' => 'Betalingen opvolgen',        'b' => 'Van een vriendelijke herinnering tot een aanmaning, in vaste stappen, en stoppen zodra er betaald is.'],
			['icon' => '📬', 't' => 'Klanten op de hoogte houden', 'b' => 'Een bericht bij elke statuswijziging, zodat klanten niet hoeven te bellen.'],
			['icon' => '📈', 't' => 'Terugkerende rapportages',  'b' => 'Cijfers die op een vast moment worden verzameld en klaargezet of verstuurd.'],
			['icon' => '🚨', 't' => 'Controles en signalering',  'b' => 'Een melding als iets niet klopt of niet is gebeurd, in plaats van dat het ongemerkt blijft.'],
			['icon' => '🗃️', 't' => 'Bulkverwerking',            'b' => 'Grote hoeveelheden bestanden of gegevens in één keer verwerken, waar dat nu weken handwerk zou zijn.'],
		],

		'eerlijk_titel' => 'Vaste regels, of AI?',
		'eerlijk' => [
			'Veel werk heeft geen AI nodig. Een herinnering drie dagen voor een afspraak, een aanmaning na veertien dagen, een melding als een back-up niet is gelukt: dat zijn vaste regels. Die zijn voorspelbaar, goedkoop en goed te controleren.',
			'Moet een systeem tekst begrijpen, zoals een e-mail lezen, een document uitlezen of een antwoord formuleren, dan is AI de volgende stap. Dat werk beschrijven we op onze pagina over slimmer werken met AI. Vaak werken die twee samen.',
		],
		'eerlijk_link' => ['/nl/slimmer-werken-met-ai', 'Slimmer werken met AI'],

		'stappen' => [
			['t' => 'Het proces zoals het nu loopt', 'b' => 'We brengen stap voor stap in kaart wat er gebeurt, wie het doet en waar het blijft liggen.'],
			['t' => 'De regels vastleggen',          'b' => 'Wanneer moet wat gebeuren, en wanneer juist niet. Ook de uitzonderingen.'],
			['t' => 'Automatiseren en testen',       'b' => 'We bouwen het, laten het eerst naast het handwerk meelopen en controleren de uitkomst.'],
			['t' => 'Bewaken',                       'b' => 'Een automatisering die stopt, moet dat melden. Dat bouwen we er standaard in.'],
		],

		'praktijk_titel' => 'Wat er bij ons zelf elke dag vanzelf gebeurt.',
		'praktijk' => [
			[
				'soort' => 'Eigen platform · verhuur en webshop',
				'naam'  => 'Bouwsteenwinkel',
				'wat'   => 'Bij Bouwsteenwinkel draait elke dag een reeks automatische taken die anders een medewerker zou moeten doen.',
				'feiten' => [
					'Herinneringen om gehuurde sets op tijd te retourneren',
					'Bij te laat retourneren: een factuur na 14 dagen coulance, daarna een vaste reeks aanmaningen',
					'Herinneringen 7 dagen en 1 dag voor een kinderfeestje, en daarna een verzoek om een review',
					'Verlengherinneringen voor lidmaatschappen 30, 7 en 0 dagen van tevoren',
					'Maandelijkse incasso voor abonnementen en een nachtelijke back-up',
				],
			],
			[
				'soort' => 'Eigen platform · voorraad en orders',
				'naam'  => 'Studloop',
				'wat'   => 'Studloop voert op vaste momenten de taken uit die een winkel draaiende houden.',
				'feiten' => [
					'Betalingsherinneringen elke ochtend om 09:00',
					'Elke nacht een back-up, die 14 dagen wordt bewaard',
					'Een wekelijkse controle van de btw bij verkoop in andere EU-landen',
				],
			],
			[
				// Met naam, akkoord Dennis 11-09-2026. Niets over de beveiliging van het oude
				// systeem noemen. Cijfers: versteeg ocs kopie/PROJECT-NOTES.md.
				'soort' => 'Klantproject · bulkverwerking',
				'naam'  => 'Versteeg Testing',
				'wat'   => 'Voor dit keurings- en inspectiebedrijf lazen we de bestaande certificaten automatisch in vanuit PDF-bestanden, zodat ze in één systeem doorzoekbaar werden.',
				'feiten' => [
					'20.571 certificaten ingelezen, van negen verschillende soorten, elk met een eigen opmaak',
					'Nul fouten, gecontroleerd tegen certificaten waarvan de gegevens al bekend waren',
					'Elk certificaat gekoppeld aan de juiste klant',
				],
			],
		],

		'prijs_factoren' => [
			'Hoeveel stappen en uitzonderingen het proces heeft',
			'In welke systemen de gegevens staan, en of die gekoppeld moeten worden',
			'Of de automatisering alleen berichten verstuurt, of ook gegevens wijzigt',
			'Hoe er bewaakt moet worden dat alles goed is gegaan',
		],

		'faq' => [
			['v' => 'Welke processen lenen zich voor automatisering?', 'a' => 'Processen die vaak voorkomen, steeds volgens dezelfde stappen lopen en waarvan duidelijk is wanneer ze goed zijn afgerond. Herinneringen, opvolging, statusberichten en controles zijn de bekendste voorbeelden.'],
			['v' => 'Wat als er een uitzondering is?', 'a' => 'Dan gaat het naar een mens. Een goede automatisering weet wanneer hij moet stoppen, bijvoorbeeld zodra een klant heeft betaald of contact heeft opgenomen.'],
			['v' => 'Merken we het als een automatisering niet werkt?', 'a' => 'Ja, dat hoort erbij. Een taak die niet draait of mislukt, geeft een melding, zodat het niet weken ongemerkt blijft.'],
			['v' => 'Wat is het verschil met AI?', 'a' => 'Automatisering volgt vaste regels die u vooraf vastlegt. AI is nodig als een systeem zelf tekst moet lezen, beoordelen of schrijven. Veel processen hebben alleen het eerste nodig; sommige de combinatie.'],
			['v' => 'Moeten we daarvoor nieuwe software aanschaffen?', 'a' => 'Niet per se. Vaak kan de automatisering werken met de systemen die u al heeft, via een koppeling.'],
		],

		'verder' => [
			['/nl/slimmer-werken-met-ai',    'Slimmer werken met AI',  'Als regels niet genoeg zijn'],
			['/nl/api-koppelingen',          'API-koppelingen',        'Systemen die met elkaar praten'],
			['/nl/ai-telefoniste',           'AI Telefoniste',         'De telefoon altijd opgenomen'],
			['/nl/maatwerk-webapplicatie',   'Maatwerk webapplicatie', 'Software die past bij hoe u werkt'],
		],

		'cta_a' => 'Welke taak gebeurt nu alleen',
		'cta_b' => 'als iemand eraan denkt?',
		'cta_tekst' => 'Beschrijf het proces dat nu handwerk is. We laten zien wat er met vaste regels te automatiseren valt, en waar een mens nodig blijft.',
	];
@endphp

@section('title', \App\Support\PaginaTitel::met('Processen automatiseren'))
@section('description', 'Herinneringen, betalingsopvolging, statusberichten en controles die automatisch lopen volgens vaste regels. Met bewaking, en eerlijk advies over wanneer AI nodig is.')

@push('head')
	@include('pages.kernaanbod._schema', ['schemaNaam' => 'Processen automatiseren'])
@endpush

@section('content')
	@include('pages.kernaanbod._inhoud')
@endsection
