<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ToolsIndexController extends Controller
{
	public function show(string $locale): View
	{
		$sections = [
			[
				'key' => 'finance',
				'title_nl' => 'Financieel & compliance',
				'title_en' => 'Finance & compliance',
				'subtitle_nl' => 'Checks op klant- en betaalgegevens.',
				'subtitle_en' => 'Checks on customer and payment data.',
				'tools' => [
					[
						'slug' => 'bookkeeping',
						'route' => 'tools.bookkeeping.index',
						'title_nl' => 'Boekhouden', 'title_en' => 'Bookkeeping',
						'desc_nl' => 'Eenvoudige administratie: transacties met BTW, categorieën en filterbare overzichten.',
						'desc_en' => 'Simple bookkeeping: VAT-aware transactions, categories and filterable overviews.',
						'badge_nl' => 'Pro', 'badge_en' => 'Pro',
						'icon' => 'book',
					],
					[
						'slug' => 'iban-check',
						'route' => 'tools.iban-check',
						'title_nl' => 'IBAN-check', 'title_en' => 'IBAN check',
						'desc_nl' => 'Technische validatie + heuristische risico-analyse op basis van naam en structuur.',
						'desc_en' => 'Technical validation plus heuristic risk analysis on name and structure.',
						'badge_nl' => 'Gratis', 'badge_en' => 'Free',
						'icon' => 'credit-card',
					],
					[
						'slug' => 'vat-check',
						'route' => 'tools.vat-check',
						'title_nl' => 'BTW-check (VIES)', 'title_en' => 'VAT check (VIES)',
						'desc_nl' => 'Valideer EU-BTW-nummers rechtstreeks via VIES. Inclusief naam en adres van de houder.',
						'desc_en' => 'Validate EU VAT numbers directly via VIES. Includes holder name and address.',
						'badge_nl' => 'Gratis', 'badge_en' => 'Free',
						'icon' => 'check-badge',
					],
					[
						'slug' => 'postcode-check',
						'route' => 'tools.postcode-check',
						'title_nl' => 'Postcode sanity check', 'title_en' => 'Postcode sanity check',
						'desc_nl' => 'Checkt NL/BE/DE-postcodes op formaat + opvallende invoer — handig voor exports en fulfilment.',
						'desc_en' => 'Checks NL/BE/DE postcodes on format + unusual input — useful for exports and fulfilment.',
						'badge_nl' => 'Gratis', 'badge_en' => 'Free',
						'icon' => 'map-pin',
					],
					[
						'slug' => 'shipping-rates',
						'route' => 'tools.shipping-rates',
						'title_nl' => 'Verzendtarieven', 'title_en' => 'Shipping rates',
						'desc_nl' => 'Vergelijk PostNL en DHL tarieven per zone en gewicht. Bereken de goedkoopste match incl. handling.',
						'desc_en' => 'Compare PostNL and DHL rates by zone and weight. Calculate the cheapest match incl. handling.',
						'badge_nl' => 'Gratis', 'badge_en' => 'Free',
						'icon' => 'truck',
					],
				],
			],
			[
				'key' => 'text',
				'title_nl' => 'Tekst & data',
				'title_en' => 'Text & data',
				'subtitle_nl' => 'Ontwikkelaars- en administratie-hulpjes.',
				'subtitle_en' => 'Developer and admin helpers.',
				'tools' => [
					[
						'slug' => 'json-formatter',
						'route' => 'tools.json-formatter',
						'title_nl' => 'JSON formatter', 'title_en' => 'JSON formatter',
						'desc_nl' => 'Valideer, formatteer of minify JSON. Fouten worden met regel- en kolomnummer aangegeven.',
						'desc_en' => 'Validate, format or minify JSON. Errors pinpointed by line and column.',
						'badge_nl' => 'Gratis', 'badge_en' => 'Free',
						'icon' => 'braces',
					],
					[
						'slug' => 'diff',
						'route' => 'tools.diff',
						'title_nl' => 'Diff checker', 'title_en' => 'Diff checker',
						'desc_nl' => 'Vergelijk twee teksten regel-voor-regel. Toevoegingen, verwijderingen en ongewijzigde regels gemarkeerd.',
						'desc_en' => 'Compare two texts line by line. Additions, removals and unchanged lines marked.',
						'badge_nl' => 'Gratis', 'badge_en' => 'Free',
						'icon' => 'compare',
					],
				],
			],
			[
				'key' => 'network',
				'title_nl' => 'Netwerk & analyse',
				'title_en' => 'Network & analysis',
				'subtitle_nl' => 'Wat zien externe diensten van jouw verbinding?',
				'subtitle_en' => 'What do external services see about your connection?',
				'tools' => [
					[
						'slug' => 'ip-lookup',
						'route' => 'tools.ip-lookup',
						'title_nl' => 'Wat is mijn IP', 'title_en' => 'What is my IP',
						'desc_nl' => 'Toont jouw publieke IP + land, stad, provider en netwerk-type (mobiel / datacenter / wifi).',
						'desc_en' => 'Shows your public IP + country, city, provider and network type (mobile / datacenter / wifi).',
						'badge_nl' => 'Gratis', 'badge_en' => 'Free',
						'icon' => 'globe',
					],
					[
						'slug' => 'speedtest',
						'route' => 'tools.speedtest',
						'title_nl' => 'Internet snelheidstest', 'title_en' => 'Internet speed test',
						'desc_nl' => 'Meet ping, download en upload vanuit je browser. Geen app, geen account nodig.',
						'desc_en' => 'Measures ping, download and upload from your browser. No app, no account.',
						'badge_nl' => 'Gratis', 'badge_en' => 'Free',
						'icon' => 'gauge',
					],
				],
			],
			[
				'key' => 'hobby',
				'title_nl' => 'Hobby',
				'title_en' => 'Hobby',
				'subtitle_nl' => 'Speciale lookups voor verzamelaars.',
				'subtitle_en' => 'Specialist lookups for collectors.',
				'tools' => [
					[
						'slug' => 'lego-lookup',
						'route' => 'tools.lego-lookup',
						'title_nl' => 'LEGO Element Finder', 'title_en' => 'LEGO Element Finder',
						'desc_nl' => 'Zoek een LEGO-onderdeel op Element ID. Toont part, kleur, afbeelding en links naar Rebrickable en BrickLink.',
						'desc_en' => 'Look up a LEGO part by Element ID. Shows the part, colour, image and links to Rebrickable and BrickLink.',
						'badge_nl' => 'Gratis', 'badge_en' => 'Free',
						'icon' => 'blocks',
					],
				],
			],
			[
				'key' => 'files',
				'title_nl' => 'Bestanden & media',
				'title_en' => 'Files & media',
				'subtitle_nl' => 'Bewerk documenten en afbeeldingen snel.',
				'subtitle_en' => 'Quick document and image edits.',
				'tools' => [
					[
						'slug' => 'pdf-merge',
						'route' => 'tools.pdf-merge',
						'title_nl' => 'PDF merge', 'title_en' => 'PDF merge',
						'desc_nl' => 'Voeg meerdere PDF\'s samen tot één bestand. Sleep om volgorde aan te passen.',
						'desc_en' => 'Combine multiple PDFs into one file. Drag to reorder.',
						'badge_nl' => 'Gratis', 'badge_en' => 'Free',
						'icon' => 'files',
					],
					[
						'slug' => 'pdf-redact',
						'route' => 'tools.pdf-redact',
						'title_nl' => 'PDF redact', 'title_en' => 'PDF redact',
						'desc_nl' => 'Zwart balken tekenen op PDF-pagina\'s; content wordt gerasteriseerd zodat tekst niet meer te extraheren is.',
						'desc_en' => 'Draw black bars on PDF pages; content is rasterised so text cannot be extracted.',
						'badge_nl' => 'Pro', 'badge_en' => 'Pro',
						'icon' => 'shield',
					],
					[
						'slug' => 'favicon-generator',
						'route' => 'tools.favicon-generator',
						'title_nl' => 'Favicon generator', 'title_en' => 'Favicon generator',
						'desc_nl' => 'Upload een logo, krijg alle standaard favicon-formaten + .ico in één ZIP.',
						'desc_en' => 'Upload a logo, get every standard favicon size + .ico in a single ZIP.',
						'badge_nl' => 'Gratis', 'badge_en' => 'Free',
						'icon' => 'image',
					],
				],
			],
		];

		return view('pages.tools-index', ['sections' => $sections]);
	}
}
