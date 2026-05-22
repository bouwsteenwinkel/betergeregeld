<?php

namespace App\Services\Blog;

use App\Models\Blog\BlogPost;
use App\Services\Ai\AnthropicClient;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Vertaalt een bestaande BlogPost naar een andere taal en slaat 'm op
 * als nieuwe BlogPost (locale-veld + translation_of_post_id).
 *
 * Idempotent op (source_post, target_locale): als er al een vertaling
 * bestaat → returnt die zonder opnieuw te vertalen.
 */
class BlogTranslator
{
	public function __construct(private readonly AnthropicClient $ai) {}

	public function translate(BlogPost $source, string $targetLocale = 'en'): BlogPost
	{
		// Idempotent: bestaande vertaling?
		$existing = BlogPost::where('translation_of_post_id', $source->id)
			->where('locale', $targetLocale)
			->first();
		if ($existing) {
			return $existing;
		}

		$sourceLang = $source->locale === 'nl' ? 'Dutch' : ($source->locale === 'en' ? 'English' : $source->locale);
		$targetLang = $targetLocale === 'en' ? 'English' : ($targetLocale === 'nl' ? 'Dutch' : $targetLocale);

		$payload = json_encode([
			'title'      => $source->title,
			'meta_title' => $source->meta_title,
			'excerpt'    => $source->excerpt,
			'body_html'  => $source->body,
		], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		$system = <<<TXT
You are a professional translator for a Dutch SMB-focused tech consultancy (Beter Geregeld ICT, Bussum). You translate marketing-light, practical blog posts from $sourceLang to $targetLang.

RULES:
1. Return ONLY a valid JSON object with the same keys as the input. No markdown fences, no commentary.
2. Preserve HTML structure exactly: same tags, same attributes, same nesting. Only translate the text content inside tags.
3. Keep <a href="..."> URLs unchanged, but translate any /nl/ path segments to /en/ if the link is internal (e.g., /nl/tools/vat-check → /en/tools/vat-check).
4. Do NOT translate brand names, product names, proper nouns (Beter Geregeld ICT, VIES, IBAN, SEPA, AVG/GDPR, M365, etc.). Use locally-appropriate equivalents where common: BTW → VAT, AVG → GDPR.
5. Keep the same tone: clear, practical, no jargon-for-jargon's-sake. Native $targetLang phrasing — not literal word-for-word.
6. meta_title: keep ≤ 65 chars total. End with " — Beter Geregeld ICT".
7. excerpt: 1-2 sentences, same hook-quality as source.
TXT;

		$user = "Translate this blog-post JSON from $sourceLang to $targetLang. Return ONLY the translated JSON object.\n\n$payload";

		$data = $this->ai->structuredCall([
			'model'             => $this->ai->translatorModel(),
			'system'            => $system,
			'user'              => $user,
			'max_tokens'        => 16000,
			'tool_name'         => 'submit_translation',
			'tool_description'  => 'Submit the translated blog post via this tool. Always use this tool.',
			'tool_input_schema' => [
				'type' => 'object',
				'properties' => [
					'title'      => ['type' => 'string'],
					'meta_title' => ['type' => 'string'],
					'excerpt'    => ['type' => 'string'],
					'body_html'  => ['type' => 'string'],
				],
				'required' => ['title', 'excerpt', 'body_html'],
			],
		]);

		if (!$data) {
			throw new RuntimeException('BlogTranslator: Claude gaf geen tool_use-response — ' . ($this->ai->lastError ?? 'unknown'));
		}

		// Slug — vertaal de source-slug naar een Engelse variant.
		// Pragmatisch: gebruik dezelfde slug (werkt prima want unique
		// is (locale, slug)). Voor commerciële posts kun je later
		// een aparte EN-slug genereren.
		$slug = $source->slug;
		if (BlogPost::where('locale', $targetLocale)->where('slug', $slug)->exists()) {
			$slug .= '-' . $targetLocale;
		}

		$translated = BlogPost::create([
			'category_id'            => $source->category_id,
			'locale'                 => $targetLocale,
			'translation_of_post_id' => $source->id,
			'slug'                   => $slug,
			'title'                  => mb_substr((string) $data['title'], 0, 255),
			'meta_title'             => mb_substr((string) ($data['meta_title'] ?? $data['title']), 0, 65),
			'excerpt'                => mb_substr((string) $data['excerpt'], 0, 500),
			'body'                   => (string) $data['body_html'],
			'reading_time_min'       => $source->reading_time_min,
			'is_pillar'              => $source->is_pillar,
			'featured'               => false,
			'published_at'           => $source->published_at,
		]);

		// Sync tags 1-op-1 (zelfde tag-IDs — tags zijn niet locale-aware)
		$translated->tags()->sync($source->tags()->pluck('blog_tags.id'));

		return $translated;
	}

	private function extractJson(string $raw): ?array
	{
		$text = trim($raw);
		$text = preg_replace('/^```(?:json)?\s*/i', '', $text);
		$text = preg_replace('/\s*```\s*$/', '', $text);
		$start = strpos($text, '{');
		$end   = strrpos($text, '}');
		if ($start === false || $end === false || $end <= $start) return null;
		$json = substr($text, $start, $end - $start + 1);
		$arr = json_decode($json, true);
		return is_array($arr) ? $arr : null;
	}
}
