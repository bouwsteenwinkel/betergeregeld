@extends('layouts.app')

@php
	$l  = (array) config('legal', []);
	$op = $l['operator'] ?? 'Betergeregeld ICT';
@endphp

@section('title', 'Algemene voorwaarden | Beter Geregeld ICT')
@section('description', 'De algemene voorwaarden van ' . $op . '.')
@section('robots', 'noindex,follow')

@include('pages.legal._prose-head')

@section('content')
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1100px] mx-auto px-6 py-16">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-5 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">Home</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Algemene voorwaarden</span>
		</nav>
		<span class="pill pill-dark mb-4">Juridisch</span>
		<h1 class="display-1 mb-3">Algemene voorwaarden</h1>
		<p class="text-[color:var(--color-on-dark-muted)]">Laatst bijgewerkt: {{ $l['updated'] ?? '' }}</p>
	</div>
</section>

<section class="py-16">
	<div class="legal-prose px-6">
		<p>Deze algemene voorwaarden zijn van toepassing op alle aanbiedingen, diensten en overeenkomsten van <strong>{{ $op }}</strong>@if (!empty($l['kvk'])) (KvK {{ $l['kvk'] }})@endif.</p>

		<h2>1. Definities</h2>
		<p>Onder "wij"/"ons" verstaan we {{ $op }}. Onder "klant" verstaan we de ondernemer met wie wij een overeenkomst aangaan. Onder "diensten" verstaan we het maken en onderhouden van websites, webshops, klantenportalen, automatisering en aanverwante online oplossingen.</p>

		<h2>2. Toepasselijkheid</h2>
		<p>Deze voorwaarden gelden voor elk aanbod en elke overeenkomst. Afwijkingen gelden alleen als we die schriftelijk hebben bevestigd. Eventuele voorwaarden van de klant wijzen we uitdrukkelijk van de hand.</p>

		<h2>3. Aanbod</h2>
		<p>Al ons aanbod is vrijblijvend tenzij anders vermeld. Kennelijke vergissingen of fouten binden ons niet.</p>

		<h2>4. Offertes en totstandkoming</h2>
		<p>Je ontvangt een duidelijke offerte met een vaste prijs. De overeenkomst komt tot stand zodra je de offerte (schriftelijk of digitaal) akkoord geeft.</p>

		<h2>5. Prijzen en betaling</h2>
		<p>Prijzen zijn exclusief btw, tenzij anders vermeld. De op de offerte genoemde prijs is de prijs die je betaalt — geen verrassingen achteraf. Facturen voldoe je binnen de op de factuur genoemde termijn. Bij een abonnement (per maand) blijft de dienst actief zolang het abonnement loopt.</p>

		<h2>6. Uitvoering en oplevering</h2>
		<p>We voeren de opdracht naar beste inzicht en vermogen uit. Opgegeven termijnen zijn indicatief en geen fatale termijnen. We stemmen het resultaat met je af voordat het live gaat.</p>

		<h2>7. Medewerking van de klant</h2>
		<p>Voor een goede uitvoering lever je tijdig de benodigde informatie en materialen aan (zoals teksten, foto's en logo's) en zorg je dat je gerechtigd bent die te gebruiken. Vertraging in aanlevering kan de oplevering vertragen.</p>

		<h2>8. Duur en opzegging</h2>
		<p>Doorlopende diensten (zoals hosting, onderhoud en abonnementen) worden aangegaan voor onbepaalde tijd en zijn maandelijks opzegbaar, tenzij schriftelijk anders overeengekomen. Geen lange contracten.</p>

		<h2>9. Intellectueel eigendom</h2>
		<p>Zolang je aan je betalingsverplichtingen voldoet, mag je het opgeleverde werk gebruiken voor je onderneming. Door jou aangeleverde content blijft van jou. Onderliggende systemen, sjablonen en broncomponenten blijven ons eigendom of dat van onze leveranciers.</p>

		<h2>10. Aansprakelijkheid</h2>
		<p>Onze aansprakelijkheid is beperkt tot het bedrag dat voor de betreffende opdracht (of in de voorgaande drie maanden bij een abonnement) is betaald. We zijn niet aansprakelijk voor indirecte schade, zoals gederfde omzet of gevolgschade. Deze beperking geldt niet bij opzet of bewuste roekeloosheid van onze kant.</p>

		<h2>11. Overmacht</h2>
		<p>Bij overmacht (zoals storingen bij leveranciers of uitval van internet) mogen we onze verplichtingen opschorten zolang de overmacht duurt, zonder tot schadevergoeding gehouden te zijn.</p>

		<h2>12. Klachten</h2>
		<p>Ben je ergens niet tevreden over? Laat het ons binnen redelijke termijn weten, dan zoeken we samen naar een oplossing. Je kunt ons bereiken via de gegevens in ons <a href="{{ route('legal.privacy') }}">privacybeleid</a>.</p>

		<h2>13. Wijzigingen</h2>
		<p>We kunnen deze voorwaarden aanpassen. De meest recente versie staat altijd op deze pagina en geldt voor nieuwe overeenkomsten.</p>

		<h2>14. Toepasselijk recht</h2>
		<p>Op onze overeenkomsten is Nederlands recht van toepassing. Geschillen leggen we voor aan de bevoegde rechter in het arrondissement van onze vestiging.</p>

		<p style="margin-top:2rem"><a href="{{ route('contact') }}" class="btn btn-primary">Neem contact op</a></p>
	</div>
</section>
@endsection
