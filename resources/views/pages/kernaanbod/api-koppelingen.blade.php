@extends('layouts.app')

@php
	$hreflangLocales = ['nl'];
	$p = [
		'slug'    => 'api-koppelingen',
		'topic'   => 'api-koppelingen',
		'kruimel' => 'API-koppelingen',
		'pill'    => 'Koppelingen',
		'h1a'     => 'Systemen die',
		'h1b'     => 'met elkaar praten.',
		'lead'    => 'Koppelingen tussen uw webshop, boekhouding, CRM, planning, betalingen en verzending. Gegevens één keer invoeren, en overal kloppen ze.',

		'herken_titel' => 'Overtypen is geen werk.',
		'intro' => [
			'Bijna elke organisatie gebruikt een handvol goede pakketten. Het probleem zit tussen die pakketten: wat in het ene systeem binnenkomt, moet met de hand in het volgende.',
			'Een koppeling laat de systemen die gegevens zelf uitwisselen. Dat scheelt tijd, maar vooral fouten.',
		],
		'herken' => [
			'Orders worden met de hand overgezet van de webshop naar de administratie of het magazijn.',
			'Elke week gaat er een export uit het ene systeem en een import in het andere.',
			'De voorraad klopt niet tussen uw eigen webshop en de marktplaatsen waarop u verkoopt.',
			'Klantgegevens staan in drie systemen, en ze zijn in alle drie net anders.',
			'Een fout bij het overtypen komt pas aan het licht als de klant belt.',
		],

		'wat_pill'  => 'Wat we koppelen',
		'wat_titel' => 'Van betaling tot verzendlabel.',
		'wat_intro' => 'Dit zijn de soorten koppelingen die we het vaakst bouwen. Welk pakket u ook gebruikt: als het gegevens kan uitwisselen, zoeken we uit hoe.',
		'wat' => [
			['icon' => '🛒', 't' => 'Webshop en marktplaatsen', 'b' => 'Orders, voorraad en berichten gelijk houden tussen uw eigen webshop en platforms waarop u verkoopt.'],
			['icon' => '💳', 't' => 'Betalingen',              'b' => 'Betaalstatus automatisch verwerken, ook als een betaalbericht een keer niet aankomt.'],
			['icon' => '📦', 't' => 'Verzending',              'b' => 'Verzendlabels aanmaken en de status van zendingen automatisch bijwerken.'],
			['icon' => '📇', 't' => 'CRM, ERP en boekhouding', 'b' => 'Klanten, orders en facturen op één plek invoeren en doorzetten naar de rest.'],
			['icon' => '📅', 't' => 'Agenda en e-mail',        'b' => 'Afspraken in de agenda zetten en berichten uit een mailbox verwerken.'],
			['icon' => '🔌', 't' => 'Uw eigen API',            'b' => 'Een beveiligde koppeling waarmee partners of klanten zelf gegevens met u uitwisselen.'],
		],

		'eerlijk_titel' => 'Een koppeling die stil faalt, is erger dan geen koppeling.',
		'eerlijk' => [
			'Of een koppeling kan, hangt af van wat een pakket toelaat. De meeste moderne pakketten hebben een API. Waar dat niet zo is, kan het soms via een export, een beveiligde bestandsuitwisseling of een mailbox. We zoeken eerst uit wat er kan, voordat we iets beloven.',
			'En we bouwen controles in. Komt een bericht niet door of lopen twee systemen uit elkaar, dan wordt dat opgemerkt en hersteld, in plaats van dat u het weken later ontdekt in een verschil in de cijfers.',
		],

		'stappen' => [
			['t' => 'Welke gegevens, welke kant op', 'b' => 'We brengen in kaart welke informatie waar ontstaat en waar hij naartoe moet.'],
			['t' => 'Wat de pakketten toelaten',     'b' => 'We onderzoeken per systeem wat er via een API of anders mogelijk is.'],
			['t' => 'Bouwen en testen',              'b' => 'We bouwen de koppeling en testen hem met echte voorbeelden, inclusief wat er gebeurt als iets misgaat.'],
			['t' => 'Bewaken en bijhouden',          'b' => 'Pakketten veranderen. We houden de koppeling in de gaten en passen hem aan waar nodig.'],
		],

		'praktijk_titel' => 'Koppelingen die elke dag draaien.',
		'praktijk' => [
			[
				'soort' => 'Eigen platform · webshop en verhuur',
				'naam'  => 'Bouwsteenwinkel',
				'wat'   => 'Het platform achter Bouwsteenwinkel praat met een lange rij externe systemen. Betalingen, verzending, marktplaatsen, e-mail en Google-diensten lopen allemaal automatisch.',
				'feiten' => [
					'Betalingen via Mollie, PayPal en Stripe',
					'Verzendlabels en track & trace via MyParcel (PostNL en DHL)',
					'Voorraad en orders met BrickLink, BrickOwl en Studloop',
					'WhatsApp, Gmail, Google Agenda, Google Drive en Search Console',
				],
			],
			[
				'soort' => 'Eigen platform · voorraad en orders',
				'naam'  => 'Studloop',
				'wat'   => 'Studloop haalt orders en berichten van marktplaatsen automatisch binnen en controleert of de voorraad en orders daar nog overeenkomen met het eigen systeem.',
				'feiten' => [
					'Orders en berichten van BrickLink en BrickOwl automatisch gesynchroniseerd',
					'Een controle op verschillen tussen de marktplaatsen en de eigen administratie',
					'Een catalogus van LEGO-onderdelen die elke nacht wordt bijgewerkt vanuit Rebrickable',
					'Verzending via MyParcel, PostNL en DHL; betalingen via Mollie, Stripe en PayPal',
				],
			],
		],

		'prijs_factoren' => [
			'Hoeveel systemen er gekoppeld worden',
			'Of de pakketten een goede API hebben, of dat er een omweg nodig is',
			'Of gegevens één kant op gaan of in beide richtingen gelijk moeten blijven',
			'Hoeveel uitzonderingen er in het proces zitten',
			'Wat er gebeurt als een koppeling een keer faalt, en wie dat moet merken',
		],

		'faq' => [
			['v' => 'Kunt u ons pakket koppelen?', 'a' => 'Als het pakket een API heeft, vrijwel altijd. Heeft het die niet, dan kijken we naar een export, een bestandsuitwisseling of een mailbox. In het adviesgesprek zoeken we dat voor uw pakketten uit.'],
			['v' => 'Wat als een pakket geen API heeft?', 'a' => 'Dan zijn er vaak nog andere wegen, zoals een automatische export, een beveiligde map waarin bestanden worden klaargezet, of het uitlezen van een vaste e-mail. Niet elke omweg is even betrouwbaar; dat bespreken we eerlijk.'],
			['v' => 'Wat gebeurt er als een van de pakketten een update krijgt?', 'a' => 'Dan kan een koppeling moeten worden aangepast. Daarom horen bewaking en onderhoud erbij: we willen het merken voordat u het merkt.'],
			['v' => 'Zijn Zapier of Make niet genoeg?', 'a' => 'Voor eenvoudige stappen vaak wel, en dan raden we dat ook aan. Zodra er veel uitzonderingen zijn, grote aantallen, of gegevens in twee richtingen gelijk moeten blijven, is een eigen koppeling meestal betrouwbaarder.'],
			['v' => 'Is het veilig om systemen aan elkaar te koppelen?', 'a' => 'Ja, mits goed gedaan: elke koppeling krijgt alleen de rechten die hij nodig heeft, sleutels worden niet in de code bewaard en het verkeer is versleuteld.'],
		],

		'verder' => [
			['/nl/processen-automatiseren',  'Processen automatiseren', 'Werk dat zichzelf afhandelt'],
			['/nl/maatwerk-webapplicatie',   'Maatwerk webapplicatie',  'Software die past bij hoe u werkt'],
			['/nl/klantportaal-laten-maken', 'Klantportaal',            'Klanten zien zelf status en documenten'],
			['/nl/slimmer-werken-met-ai',    'Slimmer werken met AI',   'Waar AI echt tijd bespaart'],
		],

		'cta_a' => 'Welke gegevens typt u',
		'cta_b' => 'nu nog over?',
		'cta_tekst' => 'Noem de pakketten die u gebruikt en waar het handwerk zit. We laten weten wat er te koppelen valt, en wat niet.',
	];
@endphp

@section('title', \App\Support\PaginaTitel::met('API-koppelingen tussen uw systemen'))
@section('description', 'Koppelingen tussen webshop, boekhouding, CRM, betalingen en verzending. Geen dubbele invoer meer, met bewaking als er iets misgaat. Voorbeelden uit Bouwsteenwinkel en Studloop.')

@push('head')
	@include('pages.kernaanbod._schema', ['schemaNaam' => 'API-koppelingen'])
@endpush

@section('content')
	@include('pages.kernaanbod._inhoud')
@endsection
