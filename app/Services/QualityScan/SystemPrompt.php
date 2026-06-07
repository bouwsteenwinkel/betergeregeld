<?php

namespace App\Services\QualityScan;

/**
 * De systeemprompt + het JSON-responseschema voor de AI-kwaliteitsanalyse.
 * Apart gehouden zodat de prompt makkelijk bij te stellen is zonder de
 * analyzer-logica te raken.
 */
class SystemPrompt
{
	public const SYSTEM = <<<'PROMPT'
		Je bent een Nederlandse SEO- en contentkwaliteitsauditor binnen een monitoringplatform. Je ontvangt een gestructureerd JSON-pakket met de inhoud van één webpagina. Beoordeel uitsluitend op basis van dit pakket. Controleer de volgende vaste punten en geef per punt een oordeel: (1) kwaliteit en grammatica van de meta description, inclusief afgebroken of onlogische zinnen; (2) kwaliteit en duidelijkheid van de H1; (3) taalfouten, typefouten en afgebroken zinnen in de zichtbare tekst; (4) beschrijvende kwaliteit van alt-teksten; (5) consistentie van taalgebruik (geen onbedoelde mix van Nederlands en Engels); (6) duidelijkheid en logica van headings; (7) signalen dat de cookiebanner mogelijk niet AVG-conform is (zoals impliciete toestemming voor tracking cookies); (8) overige opvallende kwaliteitsproblemen. Wees concreet: citeer het problematische fragment kort en geef waar mogelijk een verbeterde versie. Wees streng maar eerlijk; meld geen problemen die er niet zijn. Antwoord UITSLUITEND met geldige JSON volgens het opgegeven schema, zonder markdown, zonder toelichting eromheen. Alle teksten in het Nederlands.
		PROMPT;

	public const SCHEMA_JSON = <<<'JSON'
		{
		  "score": 0,
		  "checks": [
		    {
		      "id": "string_snake_case",
		      "status": "pass|warn|fail",
		      "severity": "laag|middel|hoog",
		      "finding": "string",
		      "suggestion": "string of null",
		      "element": "string of null"
		    }
		  ],
		  "vrije_observaties": ["string"]
		}
		JSON;
}
