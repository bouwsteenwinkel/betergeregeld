<?php

namespace App\Services\Blog;

use App\Models\Blog\BlogCategory;
use App\Models\Blog\BlogPost;
use App\Models\Blog\BlogTag;
use App\Services\Ai\AnthropicClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Schrijft een nieuwe Nederlandse blog-post via Claude (twee keer per week,
 * zie routes/console.php).
 *
 * Topic-keuze: Claude bedenkt het zelf gegeven de bestaande categorieën,
 * de afgelopen 30 posts (om duplicates te vermijden), en de positionering
 * van Beter Geregeld ICT: maatwerk, koppelingen en automatisering, voor de
 * beslisser die overweegt iets te laten bouwen. Geen externe topic-queue nodig.
 *
 * Output-shape (Claude moet exact deze JSON teruggeven):
 *   {
 *     "category_slug": "...",
 *     "slug":          "kebab-case-slug",
 *     "title":         "...",
 *     "meta_title":    "... (zonder merknaam, max 55 tekens)",
 *     "excerpt":       "1-2 zin samenvatting",
 *     "body_html":     "<p>...</p><h2>...</h2><p>...</p>",
 *     "tags":          ["tag-slug-1", "tag-slug-2"],
 *     "reading_time_min": 4,
 *     "is_pillar":     false
 *   }
 */
class BlogGenerator
{
	public function __construct(private readonly AnthropicClient $ai) {}

	/**
	 * Bedenkt onderwerp + schrijft NL-post + slaat op. Returnt de
	 * opgeslagen BlogPost, of throws bij fout.
	 */
	public function generate(): BlogPost
	{
		$categories = BlogCategory::query()
			->orderBy('sort_order')
			->get(['slug', 'name'])
			->map(fn ($c) => "- {$c->slug}: {$c->name}")
			->implode("\n");

		$recent = BlogPost::query()
			->where('locale', 'nl')
			->orderByDesc('published_at')
			->limit(30)
			->pluck('title')
			->map(fn ($t) => '- ' . $t)
			->implode("\n");

		$diensten = collect(array_keys(config('services_catalog', [])))
			->map(fn ($slug) => "- /nl/diensten/{$slug}")
			->implode("\n");

		// WIE WE AANSPREKEN. Tot 11-09-2026 schreef deze prompt voor "ondernemers en
		// kantoormanagers zonder IT-afdeling" over losse beheertaken. Dat leverde 572 posts
		// op over M365-instellingen, patch management en bankafschriften zwartlakken, met
		// in 90 dagen 30 klikken en vrijwel geen lezer die iets wil laten bouwen: 73% van de
		// posts had nul vertoningen, de dienstenpagina's samen 45. Wat Beter Geregeld
		// verkoopt is maatwerk, koppelingen en automatisering; daar schrijven we nu over,
		// voor de persoon die daarover beslist.
		$systemPrompt = <<<TXT
Je bent een ervaren redacteur voor Beter Geregeld ICT in Bussum (sinds 1989). Wij bouwen maatwerk webapplicaties, klantportalen, API-koppelingen en procesautomatisering, en helpen organisaties AI zinnig in hun werk in te zetten.

VOOR WIE: de beslisser in een MKB-organisatie van zo'n 5 tot 250 mensen. Een directeur-eigenaar, operationeel manager of office manager die merkt dat het werk vastloopt: dubbele invoer, Excel-lijsten die niemand meer vertrouwt, systemen die niet met elkaar praten, klanten die bellen voor informatie die ze zelf zouden moeten kunnen zien. Zo iemand overweegt iets te laten bouwen of koppelen, en wil weten wat dat inhoudt.

WAAROVER: vragen die zo iemand heeft vóór en tijdens zo'n keuze. Bijvoorbeeld:
- wanneer een standaardpakket volstaat en wanneer maatwerk loont
- wat een klantportaal oplost, en wat erbij komt kijken
- koppelingen tussen pakketten (boekhouding, CRM, planning, webshop): wat kan, wat valt tegen
- welke processen zich lenen voor automatisering, en welke niet
- AI in het dagelijkse werk: waar het tijd scheelt, waar het risico's heeft
- hoe een bouwtraject verloopt, wat je zelf moet aanleveren, hoe je een bouwer kiest
- beveiliging, toegang en websitebeheer, uitsluitend vanuit de vraag "wat moet ik als beslisser regelen of uitbesteden"

NIET: stap-voor-stap handleidingen voor systeembeheerders (instellingen in M365, PowerShell, configuratiemenu's), algemene kantoortips, consumentenonderwerpen. Noem geen prijzen of doorlooptijden van Beter Geregeld: die volgen na een adviesgesprek. Verzin geen klantcases, cijfers of onderzoeken.

Toon: helder, eerlijk, geen jargon waar dat vermijdbaar is. Benoem ook wanneer maatwerk NIET de oplossing is. Geen marketing-frasen, geen "in een wereld waar...". Nederlands van mensen voor mensen.

Pagina's waar de tekst naar MOET verwijzen (minstens één, in de lopende tekst waar het past). Kies bij voorkeur een van de eerste zes:
- /nl/maatwerk-webapplicatie (maatwerk software laten bouwen)
- /nl/klantportaal-laten-maken (klantportaal)
- /nl/api-koppelingen (systemen koppelen)
- /nl/processen-automatiseren (automatiseren met vaste regels)
- /nl/slimmer-werken-met-ai (AI in werkprocessen)
- /nl/ai-telefoniste (AI die de telefoon aanneemt)
- /nl/contact
$diensten

Tools mogen alleen als ze echt bij het onderwerp horen: /nl/tools/iban-check, /nl/tools/vat-check, /nl/tools/postcode-check, /nl/tools/json-formatter, /nl/tools/mail-auth-check, /nl/tools/ssl-check.

Beschikbare categorieën:
$categories

REGELS:
1. Geef ALLEEN een geldig JSON-object terug, geen tekst eromheen, geen markdown-fences.
2. body_html moet schone HTML zijn: <p>, <h2>, <h3>, <ul>/<ol>/<li>, <strong>, <em>, <a href>. GEEN <h1> (de title is al h1). GEEN <html>/<head>/<body>.
3. Lengte: 800-1400 woorden.
4. excerpt: 1-2 zinnen die nieuwsgierig maken zonder clickbait.
5. meta_title: de zoekvraag in gewone woorden, ZONDER merknaam, maximaal 55 tekens. De site zet er zelf " | Beter Geregeld" achter.
6. slug: lowercase kebab-case, geen accenten, max 80 chars.
7. tags: 3-6 stuks, lowercase kebab-case, herbruikbaar (geen one-off-tags).
8. Sluit af met een korte, niet-marketinge alinea die naar de best passende pagina uit de lijst hierboven linkt.
9. Vermijd onderwerpen die we recent al schreven (zie lijst).
TXT;

		$userPrompt = "Schrijf een nieuwe blog-post voor vandaag ({datum}).\n\nWe schreven recent over (vermijd herhaling):\n{recent}\n\nKies een onderwerp dat 1) past in een bestaande categorie, 2) een vraag beantwoordt die een beslisser heeft die overweegt iets te laten bouwen, koppelen of automatiseren, 3) een natuurlijke aanleiding biedt om naar een van onze dienst- of landingspagina's te verwijzen.\n\nGeef ALLEEN het JSON-object terug.";
		$userPrompt = strtr($userPrompt, [
			'{datum}'  => Carbon::now()->translatedFormat('d F Y'),
			'{recent}' => $recent !== '' ? $recent : '(nog niets — eerste post)',
		]);

		$data = $this->ai->structuredCall([
			'model'             => $this->ai->writerModel(),
			'system'            => $systemPrompt,
			'user'              => $userPrompt,
			'max_tokens'        => 8000,
			'tool_name'         => 'publish_blog_post',
			'tool_description'  => 'Submit the newly written Dutch blog post via this tool. Always use this tool — do not reply with chat text.',
			'tool_input_schema' => [
				'type' => 'object',
				'properties' => [
					'category_slug'    => ['type' => 'string', 'description' => 'slug van een bestaande categorie'],
					'slug'             => ['type' => 'string', 'description' => 'kebab-case slug, max 80 chars'],
					'title'            => ['type' => 'string'],
					'meta_title'       => ['type' => 'string', 'description' => 'zonder merknaam, max 55 tekens'],
					'excerpt'          => ['type' => 'string', 'description' => '1-2 zin samenvatting'],
					'body_html'        => ['type' => 'string', 'description' => 'volledige HTML body, geen <h1>'],
					'tags'             => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => '3-6 kebab-case tags'],
					'reading_time_min' => ['type' => 'integer', 'description' => 'geschatte leestijd 2-10 minuten'],
					'is_pillar'        => ['type' => 'boolean'],
				],
				'required' => ['category_slug', 'slug', 'title', 'excerpt', 'body_html', 'tags'],
			],
		]);

		if (!$data) {
			throw new RuntimeException('BlogGenerator: Claude gaf geen tool_use-response — ' . ($this->ai->lastError ?? 'unknown'));
		}

		// Claude wisselt soms tussen 'category_slug' en 'category' — accepteer beide.
		if (empty($data['category_slug']) && !empty($data['category'])) {
			$data['category_slug'] = $data['category'];
		}
		// Body kan ook 'body' of 'body_html' heten.
		if (empty($data['body_html']) && !empty($data['body'])) {
			$data['body_html'] = $data['body'];
		}

		$this->validateShape($data);

		$category = BlogCategory::where('slug', $data['category_slug'])->first();
		if (!$category) {
			throw new RuntimeException('BlogGenerator: onbekende category_slug "' . $data['category_slug'] . '"');
		}

		// Slug uniqueness binnen NL — bij collision suffix met datum
		$slug = Str::slug((string) $data['slug']);
		$slug = $this->ensureUniqueSlug($slug, 'nl');

		$post = BlogPost::create([
			'category_id'          => $category->id,
			'locale'               => 'nl',
			'slug'                 => $slug,
			'title'                => mb_substr((string) $data['title'], 0, 255),
			// Merknaam eraf als Claude hem toch meegaf; die zet de view er zelf achter
			// (App\Support\PaginaTitel). Niet afkappen: een halve zin leest slechter.
			'meta_title'           => mb_substr(\App\Support\PaginaTitel::zonderMerk((string) ($data['meta_title'] ?? $data['title'])), 0, 255),
			'excerpt'              => mb_substr((string) $data['excerpt'], 0, 500),
			'body'                 => (string) $data['body_html'],
			'reading_time_min'     => (int) ($data['reading_time_min'] ?? max(2, ceil(str_word_count(strip_tags($data['body_html'])) / 220))),
			'is_pillar'            => (bool) ($data['is_pillar'] ?? false),
			'featured'             => false,
			'published_at'         => Carbon::now(),
		]);

		// Tags
		$tagIds = [];
		foreach ((array) ($data['tags'] ?? []) as $t) {
			$tagSlug = Str::slug((string) $t);
			if ($tagSlug === '') continue;
			$tag = BlogTag::firstOrCreate(
				['slug' => $tagSlug],
				['name' => Str::headline(str_replace('-', ' ', $tagSlug))]
			);
			$tagIds[] = $tag->id;
		}
		if (!empty($tagIds)) {
			$post->tags()->sync($tagIds);
		}

		return $post;
	}

	private function extractJson(string $raw): ?array
	{
		$text = trim($raw);
		// Strip code-fences
		$text = preg_replace('/^```(?:json)?\s*/i', '', $text);
		$text = preg_replace('/\s*```\s*$/', '', $text);
		// Pak van eerste { tot laatste }
		$start = strpos($text, '{');
		$end   = strrpos($text, '}');
		if ($start === false || $end === false || $end <= $start) {
			return null;
		}
		$json = substr($text, $start, $end - $start + 1);
		$arr = json_decode($json, true);
		return is_array($arr) ? $arr : null;
	}

	private function validateShape(array $d): void
	{
		$required = ['category_slug', 'slug', 'title', 'excerpt', 'body_html'];
		foreach ($required as $k) {
			if (empty($d[$k])) {
				throw new RuntimeException("BlogGenerator: veld '{$k}' ontbreekt of leeg.");
			}
		}
		if (mb_strlen($d['body_html']) < 800) {
			throw new RuntimeException('BlogGenerator: body_html is te kort (< 800 chars). Mogelijk lege of placeholder content.');
		}
		// Een post zonder link naar iets wat we verkopen publiceren we niet: dat is precies
		// de blog van vóór 11-09-2026, met lezers die nergens heen konden. /nl/prijzen telt
		// bewust NIET mee: die pagina gaat over de tool-abonnementen, niet over maatwerk.
		if (!preg_match('~href="(?:https://betergeregeld\.com)?/nl/(?:diensten/|slimmer-werken-met-ai|maatwerk-webapplicatie|klantportaal-laten-maken|api-koppelingen|processen-automatiseren|ai-telefoniste|contact)~', $d['body_html'])) {
			throw new RuntimeException('BlogGenerator: body_html linkt niet naar een dienst-, contact-, AI- of kernaanbodpagina.');
		}
	}

	private function ensureUniqueSlug(string $slug, string $locale): string
	{
		$base = $slug;
		$try  = $base;
		$i = 1;
		while (BlogPost::where('locale', $locale)->where('slug', $try)->exists()) {
			$i++;
			$try = $base . '-' . $i;
			if ($i > 10) {
				$try = $base . '-' . Carbon::now()->format('Y-m-d');
				break;
			}
		}
		return $try;
	}
}
