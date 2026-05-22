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
 * Schrijft elke dag een nieuwe Nederlandse blog-post via Claude.
 *
 * Topic-keuze: Claude bedenkt het zelf gegeven de bestaande categorieën,
 * de afgelopen 30 posts (om duplicates te vermijden), en de positionering
 * van Beter Geregeld ICT (MKB-tools/-checks/-diensten). Geen externe
 * topic-queue nodig.
 *
 * Output-shape (Claude moet exact deze JSON teruggeven):
 *   {
 *     "category_slug": "...",
 *     "slug":          "kebab-case-slug",
 *     "title":         "...",
 *     "meta_title":    "... — Beter Geregeld ICT",
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

		$systemPrompt = <<<TXT
Je bent een ervaren technisch redacteur voor Beter Geregeld ICT, een Nederlands MKB-georiënteerd technisch consultancy- en tools-bedrijf in Bussum (sinds 1989).

Onze blog richt zich op ondernemers en kantoormanagers ZONDER IT-afdeling. Toon: helder, praktisch, geen jargon waar dat vermijdbaar is. Geen marketing-frasen. Geen "in een wereld waar...". We schrijven Nederlands van mensen voor mensen.

Diensten waar onze tekst naar kan verwijzen: SEO-check, website-snelheid verbeteren, mail-beveiliging (SPF/DKIM/DMARC), 2FA implementeren, toegang-check, cookie-banner-instellen, website meertalig maken, WordPress opschonen, website-beveiliging, website-backup-en-herstel, website-migratie, website-structuur-check.

Tools waar onze tekst naar kan verwijzen: /nl/tools/iban-check, /nl/tools/vat-check, /nl/tools/postcode-check, /nl/tools/json-formatter, /nl/tools/diff, /nl/tools/ip-lookup, /nl/tools/favicon-generator, /nl/tools/speedtest, /nl/tools/pdf-merge, /nl/tools/shipping-rates, /nl/tools/lego-lookup.

Beschikbare categorieën:
$categories

REGELS:
1. Geef ALLEEN een geldig JSON-object terug, geen tekst eromheen, geen markdown-fences.
2. body_html moet schone HTML zijn: <p>, <h2>, <h3>, <ul>/<ol>/<li>, <strong>, <em>, <a href>. GEEN <h1> (de title is al h1). GEEN <html>/<head>/<body>.
3. Lengte: 600-1200 woorden, ~4-7 minuten leestijd.
4. excerpt: 1-2 zinnen die nieuwsgierig maken zonder clickbait.
5. meta_title eindigt op " — Beter Geregeld ICT" (max 65 chars totaal).
6. slug: lowercase kebab-case, geen accenten, max 80 chars.
7. tags: 3-6 stuks, lowercase kebab-case, herbruikbaar (geen one-off-tags).
8. Sluit af met een korte, niet-marketinge call-to-action die naar een relevante tool of dienst-pagina linkt.
9. Vermijd onderwerpen die we recent al schreven (zie lijst). Kies iets verfrissends maar binnen onze niche.
TXT;

		$userPrompt = "Schrijf een nieuwe blog-post voor vandaag ({datum}).\n\nWe schreven recent over (vermijd herhaling):\n{recent}\n\nKies een onderwerp dat 1) past in een bestaande categorie, 2) actueel en praktisch is voor het MKB, 3) een natuurlijke aanleiding biedt om naar een van onze tools of diensten te verwijzen.\n\nGeef ALLEEN het JSON-object terug.";
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
					'meta_title'       => ['type' => 'string', 'description' => 'eindigt op " — Beter Geregeld ICT", max 65 chars'],
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
			'meta_title'           => mb_substr((string) ($data['meta_title'] ?? $data['title']), 0, 65),
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
