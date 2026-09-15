@extends('layouts.app')

{{--
	Backup en monitoring voor het MKB (15-09-2026).

	Waarom deze pagina bestaat: in Search Console vingen we een heel cluster zoektermen op
	één blogpost — "backup mkb" (50 vertoningen/28 d), "backup cloud mkb", "backup oplossing
	mkb", "backup strategie bedrijf", "backup controleren", "backup monitoring mkb" — samen
	≈120 vertoningen op positie 15-50, en 0 klikken. Er bestond geen pagina die dat verkoopt;
	de dienst "website-backup-en-herstel" gaat alleen over de website zelf.

	Zelfde regel als de vijf kernaanbod-pagina's van 11-09: alleen wat aantoonbaar is. De
	praktijkvoorbeelden komen uit ons eigen platform (nachtelijke databaseback-up met een
	uitleescontrole, schijf-waakhond, servermonitoring met uptime per 24 u en 30 d).
	Geen Microsoft 365-beloftes: daar adviseren we over, we beheren het niet.
--}}

@php
	$hreflangLocales = ['nl'];
	$p = [
		'slug'    => 'backup-en-monitoring',
		'topic'   => 'backup-en-monitoring',
		'kruimel' => 'Backup en monitoring',
		'pill'    => 'Backup & monitoring',
		'h1a'     => 'Een backup die u',
		'h1b'     => 'ook echt terugkrijgt.',
		'lead'    => 'Backups inrichten, elke dag controleren of ze er echt staan, en bewaken dat uw website, webshop of server bereikbaar blijft. Zodat u het merkt vóór uw klanten.',

		'herken_titel' => '"We hebben backups" is geen antwoord.',
		'intro' => [
			'Bijna elk bedrijf heeft ergens een backup. De vraag is of die van gisteren is, of hij compleet is, en of iemand ooit heeft geprobeerd hem terug te zetten. Dat blijkt meestal pas op de dag dat het moet.',
			'Wij richten backups in, controleren ze automatisch en bewaken de servers en sites erachter. Niet één keer, maar elke dag.',
		],
		'herken' => [
			'Niemand weet precies wat er wordt geback-upt, hoe vaak, en waar de kopie staat.',
			'De backup draait al maanden, maar er is nog nooit een bestand uit teruggezet.',
			'Een volle schijf of een gestopte taak wordt pas ontdekt als de site plat ligt.',
			'De webshop was een nacht onbereikbaar en u hoorde het de volgende ochtend van een klant.',
			'Uw leverancier zegt "dat regelen wij", maar u heeft nooit een rapport gezien.',
		],

		'wat_pill'  => 'Wat we doen',
		'wat_titel' => 'Inrichten, controleren, bewaken.',
		'wat_intro' => 'Drie lagen die bij elkaar horen. Een backup zonder controle is hoop; bewaking zonder backup is een alarm zonder uitweg.',
		'wat' => [
			['icon' => '🗂️', 't' => 'Backup-inrichting',       'b' => 'Wat moet bewaard worden, hoe vaak, hoe lang, en waar de kopie naartoe gaat. Database, bestanden en configuratie, op een tweede locatie.'],
			['icon' => '✅', 't' => 'Dagelijkse controle',      'b' => 'Elke backup wordt na afloop gecontroleerd: bestaat hij, heeft hij de verwachte grootte, en is hij uit te lezen. Faalt dat, dan krijgt u bericht.'],
			['icon' => '🔁', 't' => 'Hersteltest',              'b' => 'Periodiek zetten we een backup echt terug in een testomgeving. Dan weet u hoe lang herstel duurt, in plaats van dat te hopen.'],
			['icon' => '📈', 't' => 'Servermonitoring',         'b' => 'Processor, geheugen, schijfruimte en bereikbaarheid, elke minuut gemeten. Uptime per 24 uur en per 30 dagen, zichtbaar in uw eigen overzicht.'],
			['icon' => '🔔', 't' => 'Waarschuwingen die kloppen', 'b' => 'Een melding als de schijf volloopt, een geplande taak niet draait of een site niet meer antwoordt. Geen ruis, wel de dingen die ertoe doen.'],
			['icon' => '🧭', 't' => 'Backup-check van uw huidige situatie', 'b' => 'Heeft u al een leverancier of een eigen oplossing? Dan controleren we wat er echt wordt bewaard en wat er terug te zetten is, en leggen dat vast.'],
		],

		'eerlijk_titel' => 'Een backup is pas een backup als hij is teruggezet.',
		'eerlijk' => [
			'Veel backup-oplossingen melden "geslaagd" terwijl het bestand leeg, onvolledig of onleesbaar is. Wij vertrouwen dat niet op de melding, maar controleren het bestand zelf. En we zetten hem af en toe echt terug, want dat is de enige test die telt.',
			'Wat we niet doen: Microsoft 365, Google Workspace of andere clouddiensten beheren. We kunnen wel uitzoeken wat uw abonnement daar wel en niet bewaart, en of u een aparte backup nodig heeft. Dat zeggen we eerlijk, ook als het antwoord "nee" is.',
		],
		'eerlijk_link' => ['/nl/blog/backup-strategie-mkb', 'Lees: een backup-strategie die u ook echt test'],

		'stappen' => [
			['t' => 'Wat er nu is',            'b' => 'We brengen in kaart wat er nu wordt bewaard, waar, hoe vaak, en wie een melding krijgt als het misgaat.'],
			['t' => 'Wat er moet zijn',        'b' => 'Per soort gegevens spreken we af hoe lang u terug moet kunnen en hoeveel uur uitval acceptabel is.'],
			['t' => 'Inrichten en controleren', 'b' => 'We richten de backups en de bewaking in, en laten de dagelijkse controle meedraaien vanaf dag één.'],
			['t' => 'Terugzetten en bijhouden', 'b' => 'We doen een hersteltest, leggen de uitkomst vast, en herhalen dat periodiek. Verandert er iets aan uw systemen, dan past de backup mee.'],
		],

		'praktijk_titel' => 'Zo draait het bij ons eigen platform.',
		'praktijk' => [
			[
				'soort' => 'Eigen platform · webshop en verhuur',
				'naam'  => 'Bouwsteenwinkel',
				'wat'   => 'Het platform achter Bouwsteenwinkel maakt elke nacht een backup van de database en stuurt die naar een tweede locatie. Een waakhond houdt bij of dat gelukt is, en een aparte controle leest de backup daadwerkelijk uit.',
				'feiten' => [
					'Nachtelijke databaseback-up, ingepakt en naar Google Drive gestuurd',
					'Een controle die het backupbestand helemaal uitleest en de tabellen telt',
					'Een waakhond die alarm slaat als de laatste geslaagde backup ouder is dan 48 uur, of als een nachtelijke taak stilvalt',
					'Meldingen per mail, met het onderdeel en sinds wanneer het misgaat',
				],
			],
			[
				'soort' => 'Eigen product · servermonitoring',
				'naam'  => 'Beter Geregeld Monitoring',
				'wat'   => 'Een kleine agent op de server meldt elke minuut processor, geheugen, schijfruimte en bereikbaarheid. Klanten zien de status en de uptime van hun eigen server in een eigen overzicht.',
				'feiten' => [
					'Meting elke minuut, zonder poorten open te zetten op de server',
					'Uptime per 24 uur en per 30 dagen',
					'Eigen overzicht per klant; wij zien het geheel',
					'Gebouwd en in gebruik sinds juni 2026',
				],
			],
		],

		'prijs_factoren' => [
			'Hoeveel servers, sites en databases er bewaakt en geback-upt moeten worden',
			'Hoe vaak er een backup moet zijn en hoe lang die bewaard blijft',
			'Of er een hersteltest per kwartaal of per maand nodig is',
			'Of er al een oplossing staat die we controleren, of dat we vanaf nul inrichten',
			'Wie de meldingen krijgt en of wij zelf moeten ingrijpen',
		],

		'faq' => [
			['v' => 'Wat is het verschil met de backup van mijn hostingpartij?', 'a' => 'Die is er vaak wel, maar u weet meestal niet wat erin zit, hoe lang hij bewaard blijft en of u hem zelf kunt terugzetten. Wij controleren dat, of richten een eigen backup in die u wél in de hand heeft.'],
			['v' => 'Hoe vaak moet een backup worden gemaakt?', 'a' => 'Dat hangt af van hoeveel werk u kwijt mag raken. Voor een webshop met bestellingen is dat meestal elke nacht of vaker; voor een informatieve website kan wekelijks genoeg zijn. Dat spreken we per soort gegevens af.'],
			['v' => 'Wat is het 3-2-1-principe?', 'a' => 'Drie kopieën van uw gegevens, op twee verschillende soorten opslag, waarvan één op een andere locatie. Het is een goede vuistregel, maar hij zegt niets over of de backup werkt. Daarom hoort de controle en de hersteltest erbij.'],
			['v' => 'Doen jullie ook backups van Microsoft 365 of Google Workspace?', 'a' => 'Nee, die diensten beheren we niet. We kunnen wel met u nagaan wat uw abonnement bewaart en hoe lang, en of een aparte backup voor u zinvol is.'],
			['v' => 'Krijg ik een melding als er iets misgaat?', 'a' => 'Ja. De dagelijkse controle en de bewaking sturen een bericht zodra een backup ontbreekt of niet uit te lezen is, een schijf volloopt of een site niet meer antwoordt. Wie dat bericht krijgt, spreken we af.'],
			['v' => 'Wat als ik alleen wil weten of mijn huidige backup deugt?', 'a' => 'Dan doen we een backup-check: we kijken wat er werkelijk wordt bewaard, proberen iets terug te zetten en leggen de uitkomst vast. Daarna beslist u zelf wat u ermee doet.'],
		],

		'verder' => [
			['/nl/diensten/website-backup-en-herstel', 'Website backup & herstel', 'Backup en herstel van uw website zelf'],
			['/nl/diensten/website-beveiligen',        'Website beveiligen',       'Minder kans dat u een backup nodig heeft'],
			['/nl/processen-automatiseren',            'Processen automatiseren',  'Controles die vanzelf lopen'],
			['/nl/api-koppelingen',                    'API-koppelingen',          'Systemen die met elkaar praten'],
		],

		'cta_a' => 'Wanneer is uw backup',
		'cta_b' => 'voor het laatst teruggezet?',
		'cta_tekst' => 'Vertel wat u nu heeft, of dat u het niet zeker weet. We kijken mee wat er echt bewaard wordt en wat er nodig is om rustig te slapen.',
	];
@endphp

@section('title', \App\Support\PaginaTitel::met('Backup en monitoring voor het MKB'))
@section('description', 'Backups inrichten, dagelijks controleren en periodiek echt terugzetten. Plus bewaking van servers en sites: schijf, taken en bereikbaarheid, met uptime per 24 uur en 30 dagen.')

@push('head')
	@include('pages.kernaanbod._schema', ['schemaNaam' => 'Backup en monitoring'])
@endpush

@section('content')
	@include('pages.kernaanbod._inhoud')
@endsection
