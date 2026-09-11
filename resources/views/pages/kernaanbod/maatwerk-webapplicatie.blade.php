@extends('layouts.app')

@php
	$hreflangLocales = ['nl'];
	$p = [
		'slug'    => 'maatwerk-webapplicatie',
		'topic'   => 'maatwerk-webapplicatie',
		'kruimel' => 'Maatwerk webapplicatie',
		'pill'    => 'Maatwerk software',
		'h1a'     => 'Software die past bij',
		'h1b'     => 'hoe u werkt.',
		'lead'    => 'Een webapplicatie op maat voor het werk dat standaardpakketten niet goed afdekken. Gebouwd rond uw proces, gekoppeld aan wat u al gebruikt, en na de oplevering blijven we bereikbaar.',

		'herken_titel' => 'Wanneer standaardsoftware knelt.',
		'intro' => [
			'De meeste organisaties beginnen terecht met standaardpakketten. Maar naarmate het werk specifieker wordt, groeit er omheen een verzameling Excel-lijsten, losse tools en handwerk die het pakket aanvult.',
			'Op dat punt kost het pakket meer tijd dan het oplevert. Dan is het de moeite waard om te kijken of een eigen applicatie het werk eenvoudiger maakt.',
		],
		'herken' => [
			'Het werk loopt via Excel-lijsten en mail, en niemand weet zeker welke versie klopt.',
			'Het pakket dwingt u om uw werkwijze om de software heen te bouwen, in plaats van andersom.',
			'Medewerkers voeren dezelfde gegevens in twee of drie systemen in.',
			'Afdelingen werken met eigen tools die niet met elkaar praten.',
			'Belangrijke kennis over hoe iets moet zit in het hoofd van één collega.',
		],

		'wat_pill'  => 'Wat we bouwen',
		'wat_titel' => 'Van intern systeem tot platform.',
		'wat_intro' => 'Een webapplicatie draait in de browser, dus zonder installatie op elke computer, en werkt ook op telefoon en tablet.',
		'wat' => [
			['icon' => '🗂️', 't' => 'Interne systemen',           'b' => 'Planning, voorraad, dossiers, orderverwerking of inkoop: het systeem waar uw team de hele dag in werkt.'],
			['icon' => '🔐', 't' => 'Portalen voor klanten',       'b' => 'Een afgeschermde omgeving waar klanten of partners zelf hun gegevens, documenten en status zien.', 'link' => ['/nl/klantportaal-laten-maken', 'Over klantportalen']],
			['icon' => '🏢', 't' => 'Eén systeem, meerdere omgevingen', 'b' => 'Voor meerdere merken, vestigingen of klanten, elk met een eigen afgeschermde omgeving in dezelfde applicatie.'],
			['icon' => '📱', 't' => 'Apps voor onderweg',          'b' => 'Een webapp die u op de telefoon installeert, bijvoorbeeld om in het magazijn orders te picken.'],
			['icon' => '📊', 't' => 'Overzichten en rapportages',  'b' => 'Cijfers die nu handmatig uit meerdere bronnen worden samengesteld, altijd actueel op één scherm.'],
			['icon' => '🔗', 't' => 'Gekoppeld aan wat u al heeft', 'b' => 'Boekhouding, betalingen, verzending of CRM blijven gewoon in gebruik en wisselen gegevens uit.', 'link' => ['/nl/api-koppelingen', 'Over koppelingen']],
		],

		'eerlijk_titel' => 'Maatwerk is niet altijd het antwoord.',
		'eerlijk' => [
			'Doet een standaardpakket het grootste deel van wat u nodig heeft, dan is het meestal verstandiger dat pakket te houden en alleen de ontbrekende stap te koppelen of te automatiseren. Dat is sneller en goedkoper.',
			'Maatwerk loont als uw werkwijze juist uw onderscheid is, als meerdere pakketten aan elkaar geplakt worden om één proces te dragen, of als de licentiekosten harder groeien dan uw organisatie. In het adviesgesprek zeggen we het eerlijk als u geen maatwerk nodig heeft.',
		],
		'eerlijk_link' => ['/nl/processen-automatiseren', 'Soms is automatiseren genoeg'],

		'stappen' => [
			['t' => 'Proces in kaart',          'b' => 'We kijken hoe het werk nu echt loopt, wie wat doet en welke systemen er al zijn.'],
			['t' => 'Eerst het belangrijkste deel', 'b' => 'We bouwen eerst het deel dat het meeste oplevert, zodat u vroeg ziet of het klopt.'],
			['t' => 'Uitbouwen in stappen',     'b' => 'Daarna breiden we uit op basis van hoe het in de praktijk gebruikt wordt.'],
			['t' => 'Beheer en doorontwikkeling', 'b' => 'Na de oplevering blijven we bereikbaar voor onderhoud, beveiliging en nieuwe wensen.'],
		],

		'praktijk_titel' => 'Twee platforms die we zelf bouwden en dagelijks gebruiken.',
		'praktijk' => [
			[
				'soort' => 'Eigen platform · voorraad en orders',
				'naam'  => 'Studloop',
				'wat'   => 'Studloop is een systeem voor verkopers van losse LEGO-onderdelen. Voorraad, orders, orderpicken, inkoop, verzending, betalingen en facturatie zitten in één applicatie, die een extern pakket vervangt.',
				'feiten' => [
					'Gebouwd voor meerdere winkels, elk met een eigen afgeschermde omgeving',
					'Orderpicken in het magazijn met een app op de telefoon',
					'Orders en berichten van BrickLink en BrickOwl lopen automatisch binnen',
					'Verzendlabels via MyParcel, betalingen via Mollie, Stripe en PayPal',
				],
			],
			[
				'soort' => 'Eigen platform · webshop, verhuur en administratie',
				'naam'  => 'Bouwsteenwinkel',
				'wat'   => 'Achter Bouwsteenwinkel draait één applicatie die een webshop, verhuur per week, abonnementen en meerdere andere merken en websites bedient. Ook de eigen administratie en klantaccounts zitten erin.',
				'feiten' => [
					'Meerdere merken en websites op eigen domeinen vanuit één systeem',
					'Verhuur, abonnementen met maandelijkse incasso en een webshop naast elkaar',
					'Eigen administratie, inclusief btw-aangifte',
					'Klantaccounts met bestellingen, retouren en lidmaatschap',
				],
			],
		],

		'prijs_factoren' => [
			'Hoeveel verschillende schermen en werkstromen de applicatie moet dragen',
			'Hoeveel gebruikersrollen en rechten er zijn',
			'Met welke bestaande systemen gekoppeld moet worden',
			'Of er bestaande gegevens overgezet moeten worden',
			'Of het in stappen kan, zodat u klein begint',
		],

		'faq' => [
			['v' => 'Wat is het verschil tussen maatwerk en een standaardpakket?', 'a' => 'Een standaardpakket is gebouwd voor veel organisaties tegelijk, dus u past uw werk aan het pakket aan. Maatwerk wordt gebouwd rond uw eigen proces. Het vraagt meer voorbereiding, maar het systeem doet wat uw werk nodig heeft en groeit met u mee.'],
			['v' => 'Hoe lang duurt het voordat we iets kunnen gebruiken?', 'a' => 'Dat hangt af van de omvang. We beginnen bewust met het deel dat het meeste oplevert, zodat er vroeg iets werkt dat u kunt beoordelen, in plaats van maanden te wachten op één grote oplevering.'],
			['v' => 'Kan de applicatie samenwerken met onze bestaande systemen?', 'a' => 'Meestal wel. De meeste moderne pakketten voor boekhouding, betalingen, verzending en CRM hebben een koppeling (API). We zoeken vooraf uit wat er kan, voordat we iets beloven.'],
			['v' => 'Wie beheert de applicatie na de oplevering?', 'a' => 'Wij blijven bereikbaar voor onderhoud, beveiligingsupdates en doorontwikkeling. Software die gebruikt wordt, verandert mee met de organisatie.'],
			['v' => 'Werkt het ook op telefoon en tablet?', 'a' => 'Ja. Een webapplicatie draait in de browser. Waar het handig is, kan hij ook als app op de telefoon worden geïnstalleerd, zonder app store.'],
		],

		'verder' => [
			['/nl/klantportaal-laten-maken', 'Klantportaal',            'Klanten zien zelf status en documenten'],
			['/nl/api-koppelingen',          'API-koppelingen',         'Systemen die met elkaar praten'],
			['/nl/processen-automatiseren',  'Processen automatiseren', 'Werk dat zichzelf afhandelt'],
			['/nl/ai-telefoniste',           'AI Telefoniste',          'De telefoon altijd opgenomen'],
		],

		'cta_a' => 'Vertel ons waar',
		'cta_b' => 'het nu knelt.',
		'cta_tekst' => 'Beschrijf kort welk werk nu te veel tijd of handwerk kost. We komen terug met een eerlijk advies: maatwerk, een standaardpakket met een koppeling, of iets daartussenin.',
	];
@endphp

@section('title', \App\Support\PaginaTitel::met('Maatwerk webapplicatie laten bouwen'))
@section('description', 'Een webapplicatie op maat, gebouwd rond uw proces en gekoppeld aan wat u al gebruikt. Eerlijk advies, vaste prijs na een gratis adviesgesprek. Voorbeelden: Studloop en Bouwsteenwinkel.')

@push('head')
	@include('pages.kernaanbod._schema', ['schemaNaam' => 'Maatwerk webapplicatie laten bouwen'])
@endpush

@section('content')
	@include('pages.kernaanbod._inhoud')
@endsection
