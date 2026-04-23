<?php

namespace Database\Seeders\Blog;

use Illuminate\Database\Seeder;

/**
 * Minimum viable blog seed — one category with one pillar and one supporting
 * post. Exists so the blog section has content from day one even before the
 * full 100+ library is published. Cluster-specific seeders (AccessGuard,
 * Compliance, etc.) layer on top via the same BlogSeedHelper.
 */
class BlogIntroSeeder extends Seeder
{
	public function run(): void
	{
		BlogSeedHelper::seedCluster(
			[
				'slug' => 'betergeregeld',
				'name' => 'Over Betergeregeld',
				'pillar_title' => 'Waarom MKB-software anders gebouwd moet worden',
				'intro' => 'Onze visie op software voor ondernemers zonder IT-afdeling — en de bouwkeuzes die daarbij horen.',
				'sort_order' => 999,
			],
			[
				[
					'slug' => 'welkom-op-het-blog',
					'title' => 'Welkom op het Betergeregeld-blog',
					'excerpt' => 'Vanaf nu verschijnen hier praktische gidsen over toegangsbeheer, compliance, offboarding en administratie — geschreven voor ondernemers en kantoormanagers, niet voor IT-architecten.',
					'body' => <<<'HTML'
<p>Welkom op het Betergeregeld-blog. We openen dit platform omdat we merken dat er veel MKB-specifieke kennis rondzwerft in Slack-kanalen, LinkedIn-posts en half-gedeelde PDFs — maar nergens goed ontsloten is voor de mensen die er dagelijks mee te maken hebben.</p>

<h2>Voor wie schrijven we?</h2>
<p>Niet voor IT-consultants met €150 uurtarief. Wel voor de office-manager die donderdag om 14:00 moet weten waarom de nieuwe marketeer nog niet in de Salesforce kan. Voor de CFO die bij de ISO-audit niet meer wil worstelen met Excel-sheets. Voor de mede-eigenaar die toevallig ook de IT-beheerder is geworden — hopelijk tijdelijk.</p>

<h2>Waar gaat het over?</h2>
<p>Vijf rode draden komen steeds terug:</p>
<ul>
  <li><strong>Toegangsbeheer</strong> — wie heeft waartoe toegang, en hoe hou je dat bij zonder dat het een tweede baan wordt.</li>
  <li><strong>Compliance zonder bureaucratie</strong> — wat ISO 27001 en NEN 7510 écht van je vragen in het MKB.</li>
  <li><strong>Offboarding die waterdicht is</strong> — het belangrijkste onderdeel van IT-governance waar bijna niemand over praat.</li>
  <li><strong>MKB-administratie</strong> — facturatie, abonnementen, BTW, herinneringen, zonder €80/maand aan SaaS.</li>
  <li><strong>Praktische security</strong> — wat je dit kwartaal kunt doen, niet wat de theoretisch beste setup zou zijn.</li>
</ul>

<h2>Hoe ziet een artikel eruit?</h2>
<p>Korter dan een hoofdstuk, langer dan een tweet. Altijd met een concrete handgreep: een template, een volgorde, een checklist, een screenshot uit onze eigen tools. Waar het past verwijzen we naar gerelateerde gidsen zodat je zelf het pad door de stof kunt kiezen.</p>

<p>Onderaan elk artikel zie je verwante gidsen op basis van dezelfde onderwerpen. Zo wordt het blog al snel een <em>kennisnetwerk</em> in plaats van een lineaire feed.</p>

<h2>Begin hier</h2>
<p>Niet zeker waar te beginnen? De <strong>pillar-gidsen</strong> (herkenbaar aan het ★-symbool) geven per thema een compleet overzicht. Daaronder hangen supporting-artikelen die elk één onderdeel uitdiepen. Je kunt top-down lezen, of vanuit een specifieke vraag inprikken en via de gerelateerde links verder navigeren.</p>

<p>Veel leesplezier — en neem contact op als je een onderwerp mist dat je wel zou willen lezen. We schrijven liever over jouw echte probleem dan over een onderwerp dat lekker rankt.</p>
HTML,
					'tags' => ['over-betergeregeld', 'mkb', 'start-hier'],
					'featured' => true,
					'published_offset_days' => 220,
				],
			],
		);
	}
}
