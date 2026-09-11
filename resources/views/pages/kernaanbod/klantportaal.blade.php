@extends('layouts.app')

@php
	$hreflangLocales = ['nl'];
	$p = [
		'slug'    => 'klantportaal-laten-maken',
		'topic'   => 'klantportaal',
		'kruimel' => 'Klantportaal',
		'pill'    => 'Klantportaal',
		'h1a'     => 'Een klantportaal waar klanten',
		'h1b'     => 'zelf alles vinden.',
		'lead'    => 'Status, documenten, facturen en verzoeken op één plek, achter een eigen login. Minder telefoontjes en mail over vragen die uw klant eigenlijk zelf kan beantwoorden.',

		'herken_titel' => 'Veel vragen, steeds dezelfde.',
		'intro' => [
			'Een groot deel van het contact met klanten gaat over informatie die u al heeft: hoe staat het ervoor, waar is dat document, is de factuur betaald.',
			'Elke keer zoekt een medewerker het op en stuurt het door. Een klantportaal draait dat om: de klant kijkt zelf, op het moment dat het hem uitkomt.',
		],
		'herken' => [
			'Klanten bellen of mailen om te vragen hoe het met hun order of dossier staat.',
			'Documenten gaan heen en weer als bijlage, en raken kwijt in mailboxen.',
			'Medewerkers zoeken informatie op, alleen om die daarna door te sturen.',
			'Klanten willen iets regelen of opzoeken als u gesloten bent.',
			'Het is niet zichtbaar welke klantvragen er openstaan en bij wie.',
		],

		'wat_pill'  => 'Wat erin kan',
		'wat_titel' => 'Een portaal rond wat uw klanten zoeken.',
		'wat_intro' => 'Geen standaardlijst met functies, maar wat uw klanten echt nodig hebben. Dit zijn onderdelen die vaak terugkomen.',
		'wat' => [
			['icon' => '👤', 't' => 'Eigen login per klant',    'b' => 'Elke klant ziet alleen zijn eigen gegevens. Meerdere medewerkers per klant, met eigen rechten, kan ook.'],
			['icon' => '📍', 't' => 'Status en voortgang',      'b' => 'Waar een order, aanvraag of dossier staat, zonder dat iemand hoeft te bellen.'],
			['icon' => '📄', 't' => 'Documenten',               'b' => 'Certificaten, rapporten, contracten of offertes downloaden, alleen na inloggen.'],
			['icon' => '🧾', 't' => 'Facturen en betalingen',   'b' => 'Openstaande facturen inzien en direct betalen.'],
			['icon' => '📨', 't' => 'Verzoeken indienen',       'b' => 'Een aanvraag, wijziging of vraag die meteen bij de juiste persoon terechtkomt.'],
			['icon' => '🔗', 't' => 'Altijd actueel',           'b' => 'Gekoppeld aan uw eigen systemen, zodat niemand het portaal met de hand hoeft bij te houden.', 'link' => ['/nl/api-koppelingen', 'Over koppelingen']],
		],

		'eerlijk_titel' => 'Een portaal bevat klantgegevens. Dat vraagt zorg.',
		'eerlijk' => [
			'Wachtwoorden slaan we versleuteld op, het inlogscherm is beschermd tegen herhaald raden, en documenten zijn alleen te downloaden na inloggen. Waar het past, komt er tweestapsverificatie bij.',
			'En een portaal werkt alleen als klanten er iets aan hebben. Staat er verouderde informatie in, dan bellen ze alsnog. Daarom beginnen we bij de vraag welke gegevens actueel te houden zijn, en hoe.',
		],
		'eerlijk_link' => ['/nl/diensten/2fa-implementeren', 'Over tweestapsverificatie'],

		'stappen' => [
			['t' => 'Welke vragen komen binnen', 'b' => 'We kijken welke vragen klanten nu stellen en welke informatie ze zelf zouden kunnen vinden.'],
			['t' => 'Waar de gegevens staan',    'b' => 'We brengen in kaart in welke systemen die informatie nu zit en hoe het portaal eraan komt.'],
			['t' => 'Eerste versie live',         'b' => 'We beginnen met de onderdelen die de meeste vragen wegnemen, en laten een paar klanten meekijken.'],
			['t' => 'Uitbreiden',                 'b' => 'Op basis van hoe klanten het gebruiken, bouwen we verder.'],
		],

		'praktijk_titel' => 'Portalen die dagelijks gebruikt worden.',
		'praktijk' => [
			[
				'soort' => 'Eigen platform · klantaccount',
				'naam'  => 'Bouwsteenwinkel',
				'wat'   => 'Klanten van Bouwsteenwinkel regelen hun huur en aankopen in hun eigen account, zonder daarvoor contact op te hoeven nemen.',
				'feiten' => [
					'Bestellingen en retouren inzien',
					'Lidmaatschap, spaarpunten en kortingscodes beheren',
					'Favorieten bewaren en adresgegevens aanpassen',
				],
			],
			[
				'soort' => 'Eigen product · portaal bij de AI Telefoniste',
				'naam'  => 'AI Telefoniste',
				'wat'   => 'Wie onze AI Telefoniste gebruikt, beheert die zelf in een eigen portaal, met een login die alleen de gesprekken van het eigen telefoonnummer toont.',
				'feiten' => [
					'Alle gesprekken en terugbelverzoeken terugzien',
					'Zelf de kennisbank bijhouden waaruit de telefoniste antwoordt',
					'Een dagbericht klaarzetten dat de telefoniste binnen twee minuten gebruikt',
				],
				'link' => ['/nl/ai-telefoniste', 'Over de AI Telefoniste'],
			],
			[
				// Met naam, akkoord Dennis 11-09-2026. Noem nooit iets over de beveiliging van
				// het oude systeem: dat staat in de projectdocumentatie en hoort daar te blijven.
				// Cijfers: versteeg ocs kopie/PROJECT-NOTES.md (import klaar 15-06-2026).
				'soort' => 'Klantproject · certificatenportaal',
				'naam'  => 'Versteeg Testing',
				'wat'   => 'Voor dit keurings- en inspectiebedrijf bouwen we een nieuw portaal waarin klanten hun certificaten en keuringsgegevens terugvinden, in plaats van erom te moeten vragen.',
				'feiten' => [
					'20.571 bestaande certificaten automatisch uit PDF\'s ingelezen, zonder fouten',
					'Elk certificaat gekoppeld aan de juiste klant',
					'In het nieuwe portaal zijn certificaten alleen na inloggen te downloaden',
				],
			],
		],

		'prijs_factoren' => [
			'Welke onderdelen het portaal krijgt, en in welke volgorde',
			'Uit hoeveel systemen de gegevens komen, en hoe die gekoppeld worden',
			'Hoeveel rollen en rechten er zijn (klant, medewerker van de klant, beheerder)',
			'Of bestaande documenten of gegevens eerst overgezet moeten worden',
		],

		'faq' => [
			['v' => 'Moeten klanten een app installeren?', 'a' => 'Nee. Het portaal werkt in de browser, op computer, tablet en telefoon.'],
			['v' => 'Kan het portaal in onze eigen huisstijl?', 'a' => 'Ja. Het wordt voor u gebouwd, dus logo, kleuren en taalgebruik sluiten aan bij de rest van uw organisatie.'],
			['v' => 'Hoe komen de gegevens in het portaal?', 'a' => 'Bij voorkeur automatisch, via een koppeling met de systemen waar ze nu al staan. Waar dat niet kan, beheert u ze in het portaal zelf.'],
			['v' => 'Hoe zit het met privacy en beveiliging?', 'a' => 'Elke klant ziet alleen zijn eigen gegevens. Wachtwoorden worden versleuteld opgeslagen, het inlogscherm is beschermd tegen misbruik en documenten zijn alleen na inloggen te openen. Tweestapsverificatie kan erbij.'],
			['v' => 'Kunnen we klein beginnen?', 'a' => 'Ja, en dat raden we meestal aan. Begin met het onderdeel dat de meeste vragen wegneemt en bouw verder op wat klanten echt gebruiken.'],
		],

		'verder' => [
			['/nl/maatwerk-webapplicatie',  'Maatwerk webapplicatie',  'Software die past bij hoe u werkt'],
			['/nl/api-koppelingen',          'API-koppelingen',         'Systemen die met elkaar praten'],
			['/nl/ai-telefoniste',           'AI Telefoniste',          'De telefoon altijd opgenomen'],
			['/nl/diensten/2fa-implementeren', '2FA implementeren',     'Extra beveiliging op de login'],
		],

		'cta_a' => 'Welke vraag wilt u',
		'cta_b' => 'nooit meer beantwoorden?',
		'cta_tekst' => 'Vertel ons welke klantvragen nu steeds terugkomen. We laten zien welk deel een portaal kan overnemen en waar we zouden beginnen.',
	];
@endphp

@section('title', \App\Support\PaginaTitel::met('Klantportaal laten maken'))
@section('description', 'Een klantportaal waar klanten zelf status, documenten en facturen vinden, achter een eigen login. Minder telefoontjes, gekoppeld aan uw systemen. Vaste prijs na een gratis gesprek.')

@push('head')
	@include('pages.kernaanbod._schema', ['schemaNaam' => 'Klantportaal laten maken'])
@endpush

@section('content')
	@include('pages.kernaanbod._inhoud')
@endsection
